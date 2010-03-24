<?php

/*=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2010  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr> 
                     Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	fileManageLib.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.3 licensed under GPL
	     and Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: fileManageLib.inc.php Revision: 1.3
     extra porting from: fileManage.lib.php Revision 1.49.2.3
	      
	Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
                      Hugues Peeters    <peeters@ipm.ucl.ac.be>
                      Christophe Gesche <gesche@ipm.ucl.ac.be>
==============================================================================        
*/

/*
 * Update the file or directory path in the document db document table
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - action (string) - action type require : 'delete' or 'update'
 * @param  - oldPath (string) - old path info stored to change
 * @param  - newPath (string) - new path info to substitute
 * @desc Update the file or directory path in the document db document table
 *
 */

function update_db_info($dbTable, $action, $oldPath, $newPath = "")
{
	if ($action == "delete") {
		mysql_query("DELETE FROM ".$dbTable." 
			WHERE path LIKE \"".$oldPath."%\""); 
	} elseif ($action = "update") {
		mysql_query("UPDATE $dbTable SET path = CONCAT('$newPath', SUBSTRING(path, LENGTH('$oldPath')+1))
			WHERE path LIKE '$oldPath%'");
	}
}


/*
 * Cheks a file or a directory actually exist at this location
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - filePath (string) - path of the presume existing file or dir
 * @return - boolean TRUE if the file or the directory exists
 *           boolean FALSE otherwise.
 */

function check_name_exist($filePath)
{
        return file_exists($filePath);
}


/*
 * Delete a file or a directory 
 *
 * @author - Hugues Peeters
 * @param  - $file (String) - the path of file or directory to delete
 * @return - bolean - true if the delete succeed 
 *           bolean - false otherwise.
 * @see    - delete() uses check_name_exist() and removeDir() functions
 */

function my_delete($file)
{
	if (check_name_exist($file))
	{
		if (is_file($file)) // FILE CASE
		{
			unlink($file);
			return true;
		}

		elseif (is_dir($file)) // DIRECTORY CASE
		{
			removeDir($file);
			return true;
		}
	}
	else
	{
		return false; // no file or directory to delete
	}
	
}



/*
 * Delete a directory and its whole content
 *
 * @author - Hugues Peeters
 * @param  - $dirPath (String) - the path of the directory to delete
 * @return - no return !
 */
function removeDir($dirPath)
{

	/* Try to remove the directory. If it can not manage to remove it,
	 * it's probable the directory contains some files or other directories,
	 * and that we must first delete them to remove the original directory.
	 */

	if (!@rmdir($dirPath)) // If PHP can not manage to remove the dir...
	{
                $cwd = getcwd();
		chdir($dirPath);
		$handle = opendir($dirPath) ;

		while ($element = readdir($handle) )
		{
			if ( $element == "." || $element == "..")
			{
				continue;	// skip current and parent directories
			}
			elseif ( is_file($element) )
			{
				unlink($element);
			}
			elseif (is_dir ($element) )
			{
				$dirToRemove[] = $dirPath."/".$element;
			}
		}

		closedir ($handle) ;
                chdir($cwd);

		if (isset($dirToRemove) and sizeof($dirToRemove) > 0)
		{
			foreach($dirToRemove as $j) removedir($j) ; // recursivity
		}

		rmdir( $dirPath ) ;
	}
}


/*
 * Rename a file or a directory
 * 
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $filePath (string) - complete path of the file or the directory
 * @param  - $newFileName (string) - new name for the file or the directory
 * @return - boolean - true if succeed
 *         - boolean - false otherwise
 * @see    - rename() uses the check_name_exist() and php2phps() functions
 */

function my_rename($filePath, $newFileName)
{
	$path = @$baseWorkDir.dirname($filePath);
	$oldFileName = my_basename($filePath);

	if (check_name_exist($path."/".$newFileName)
		&& $newFileName != $oldFileName)
	{
		return false;
	}
	else
	{
		/*** check if the new name has an extension ***/
		if ((!preg_match('/[^.]+\.[[:alnum:]]+$/', $newFileName))
			and preg_match('/\.([[:alnum:]]+)$/', $oldFileName, $extension))
		{
			$newFileName .= '.' . $extension[1];
		}
		
		/*** Prevent file name with php extension ***/
		$newFileName = php2phps($newFileName);
		$newFileName = replace_dangerous_char($newFileName);
		chdir($path);
		rename($oldFileName, $newFileName);
		return true;
	}
}


/*
 * Move a file or a directory to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $source (String) - the path of file or directory to move
 * @param  - $target (String) - the path of the new area
 * @return - bolean - true if the move succeed 
 *           bolean - false otherwise.
 * @see    - move() uses check_name_exist() and copyDirTo() functions
 */

function move($source, $target)
{
	if(check_name_exist($source))
	{
		$fileName = my_basename($source);
		if (check_name_exist($target."/".$fileName))
		{
			return false; 
		}
		else
		{	/*** File case ***/
			if (is_file($source)) 
			{
				copy($source , $target."/".$fileName);
				unlink($source);
				return true;
			}
			/*** Directory case ***/
			elseif (is_dir($source))
			{
				// check to not copy the directory inside itself
                                if (preg_match('/^'.$source.'/', $target))
				{
					return false;
				}
				else
				{
					copyDirTo($source, $target);
					return true;
				}
			}
		}
	}
	else
	{
		return false;
	}
	
}


/*
 * Move a directory and its content to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new directory
 */

function move_dir($src, $dest)
{
	if (file_exists($dest)) {
		if (!is_dir($dest)) {
			die("<br>Error! a file named $dest already exists\n");
		}
	} else {
		mkdir ($dest, 0775);
	}

        $handle = opendir($src);
	if (!$handle) {
		die ("Unable to read $src!");
	}
        while ($element = readdir($handle)) {
		$file = "$src/$element";
                if ( $element == "." || $element == "..") {
                        continue; // skip the current and parent directories
                } elseif (is_file($file)) {
                        copy($file, "$dest/$element") or
			die ("Error copying $src/$element to $dest");
			unlink($file);
                } elseif (is_dir($file)) {
                        move_dir($file, "$dest/$element");
			rmdir($file);
                }
        }
        closedir($handle) ;
}

/*
 * Move a directory and its content to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @return - no return !!
 */

function copyDirTo($origDirPath, $destination)
{
	// extract directory name - create it at destination - update destination trail
	$dirName = my_basename($origDirPath);
	mkdir ($destination."/".$dirName, 0775);
	$destinationTrail = $destination."/".$dirName;

	chdir ($origDirPath) ;
	$handle = opendir($origDirPath);

	while ($element = readdir($handle) )
	{
		if ( $element == "." || $element == "..")
		{
			continue; // skip the current and parent directories
		}
		elseif ( is_file($element) )
		{
			copy($element, $destinationTrail."/".$element);
			unlink($element) ;
		}
		elseif ( is_dir($element) )
		{
			$dirToCopy[] = $origDirPath."/".$element;
		}
	}

	closedir($handle) ;

	if (isset($dirToCopy) and sizeof($dirToCopy) > 0)
	{
		foreach($dirToCopy as $thisDir)
		{
			copyDirTo($thisDir, $destinationTrail);	// recursivity
		}
	}

	rmdir ($origDirPath) ;

}



/* NOTE: These functions batch is used to automatically build HTML forms
 * with a list of the directories contained on the course Directory.
 *
 * From a thechnical point of view, form_dir_lists calls sort_dir wich calls index_dir
 */

/*
 * Indexes all the directories and subdirectories
 * contented in a given directory
 * 
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - path (string) - directory path of the one to index
 * @return - an array containing the path of all the subdirectories
 */

function index_dir($path)
{
	chdir($path);
	$handle = opendir($path);

	// reads directory content end record subdirectoies names in $dir_array
	while ($element = readdir($handle) )
	{
		if ( $element == "." || $element == "..") continue;	// skip the current and parent directories
		if ( is_dir($element) )	 $dirArray[] = $path."/".$element;
	}

	closedir($handle) ;

	// recursive operation if subdirectories exist
	$dirNumber = sizeof ($dirArray);
	if ( $dirNumber > 0 )
	{
		for ($i = 0 ; $i < $dirNnumber ; $i++ )
		{
			$subDirArray = index_dir( $dirArray [$i] ) ;			// function recursivity
			$dirArray  =  array_merge( $dirArray , $subDirArray ) ;	// data merge
		}
	}

	chdir("..") ;

	return $dirArray ;

}


/*
 * Indexes all the directories and subdirectories
 * contented in a given directory, and sort them alphabetically
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - path (string) - directory path of the one to index
 * @return - an array containing the path of all the subdirectories sorted
 *           false, if there is no directory
 * @see    - index_and_sort_dir uses the index_dir() function
 */

function index_and_sort_dir($path)
{
	$dir_list = index_dir($path);

	if ($dir_list)
	{
		sort($dir_list);
		return $dir_list;
	}
	else
	{
		return false;
	}
}


/*
 * build an html form listing all directories of a given directory
 *
 */
//afth h function dhmiourgei mia lista se combo box me tous fakelous enos path. sth sygkekrimenh exei prostethei to orisma $entryToExclude prokeimenou na mhn emfanizetai mia eggrafh
function form_dir_list_exclude($dbTable, $sourceType, $sourceComponent, $command, $baseWorkDir, $entryToExclude)
{
	global $langParentDir, $langTo, $langMoveFrom, $langMove, $moveFileNameAlias;
	global $tool_content, $userGroupId;

        if (isset($userGroupId)) {
                $groupset = '?userGroupId=' . $userGroupId;
        } else {
                $groupset = '';
        }
	$dirList = index_and_sort_dir($baseWorkDir);
	$dialogBox .= "<form action='$_SERVER[PHP_SELF]$groupset' method='post'>\n";
	$dialogBox .= "<input type='hidden' name='".$sourceType."' value='".$sourceComponent."'>\n";
	$dialogBox .="<table class='FormData' width='99%'>
        	<tbody><tr><th class='left' width='200'>$langMove:</th>
          	<td class='left'>$langMoveFrom <em>$moveFileNameAlias</em> $langTo:</td><td class='left'>";
	$dialogBox .= "<select name='".$command."' class='auth_input'>\n" ;
	$dialogBox .= "<option value='' style='color:#999999'>".$langParentDir."\n";
	$bwdLen = strlen($baseWorkDir) ;
	
	/* build html form inputs */
	if ($dirList)
	{
		while (list( , $pathValue) = each($dirList))
		{
			$pathValue = substr($pathValue , $bwdLen);
			$dirname = basename($pathValue);
			$sql = db_query("SELECT path, filename FROM $dbTable 
				WHERE path LIKE '%/$dirname%'"); 
			while ($r = mysql_fetch_array($sql)) {
				$filename = $r['filename'];
				$path = $r['path']; 
				$tab = "";	
				$depth = substr_count($pathValue, "/");
				for ($h=0; $h<$depth; $h++)
				{
					$tab .= "&nbsp;&nbsp;";
				}
			
//			$tool_content .= $baseWorkDir.$path;
			if ($pathValue != $entryToExclude and (!is_file($baseWorkDir.$path)))
				$dialogBox .= "<option value='$path'>$tab>$filename</option>";
			}
		}
	}

	$dialogBox .= "</select></td><td class='left'><input type=\"submit\" value=\"$langMove\"></td></tr>
        	</tbody></table><br/>";
	$dialogBox .= "</form>";
	return $dialogBox;
}

//------------------------------------------------------------------------------
/* --------------- backported functions from Claroline 1.7.x --------------- */

/*
 * Delete a file or a directory (and its whole content)
 *
 * @param  - $filePath (String) - the path of file or directory to delete
 * @return - boolean - true if the delete succeed
 *           boolean - false otherwise.
 */

function claro_delete_file($filePath)
{
    if( is_file($filePath) )
    {
        return unlink($filePath);
    }
    elseif( is_dir($filePath) )
    {
        $dirHandle = opendir($filePath);

        if ( ! $dirHandle ) return false;

        $removableFileList = array();
        while ( $file = readdir($dirHandle) )
        {
            if ( $file == '.' || $file == '..') continue;

            $removableFileList[] = $filePath . '/' . $file;
        }

        closedir($dirHandle); // impossible to test, closedir return void ...

        if ( sizeof($removableFileList) > 0)
        {
            foreach($removableFileList as $thisFile)
            {
                if ( ! claro_delete_file($thisFile) ) return false;
            }
        }
        return rmdir($filePath);

    } // end elseif is_dir()
}

/*
 * Rename a file or a directory
 *
 * @param  - $filePath (string) - complete path of the file or the directory
 * @param  - $newFileName (string) - new name for the file or the directory
 * @return - string  - new file path if it succeeds
 *         - boolean - false otherwise
 * @see    - rename() uses the check_name_exist() and php2phps() functions
 */

function claro_rename_file($oldFilePath, $newFilePath)
{
    if (realpath($oldFilePath) == realpath($newFilePath) ) return true;

    /* CHECK IF THE NEW NAME HAS AN EXTENSION */

    if (!preg_match('/[[:print:]]+\.[[:alnum:]]+$/', $newFilePath)
        and preg_match('/[[:print:]]+\.([[:alnum:]]+)$/', $oldFilePath, $extension))
    {
        $newFilePath .= '.' . $extension[1];
    }

    /* PREVENT FILE NAME WITH PHP EXTENSION */

    $newFilePath = get_secure_file_name($newFilePath);

    /* REPLACE CHARACTER POTENTIALY DANGEROUS FOR THE SYSTEM */

    $newFilePath = dirname($newFilePath).'/'
                  .replace_dangerous_char(my_basename($newFilePath));

    if (check_name_exist($newFilePath)
        && $newFilePath != $oldFilePath)
    {
        return false;
    }
    else
    {
        if ( rename($oldFilePath, $newFilePath) )
        {
            return $newFilePath;
        }
        else
        {
            return false;
        }
    }
}

/*
 * Copy a a file or a directory and its content to an other area
 *
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @param  - $delete (bool) - move or copy the file
 * @return - void no return !!
 */

function claro_copy_file($sourcePath, $targetPath)
{
    $fileName = my_basename($sourcePath);

    if ( is_file($sourcePath) )
    {
        return copy($sourcePath , $targetPath . '/' . $fileName);
    }
    elseif ( is_dir($sourcePath) )
    {
        // check to not copy the directory inside itself
        if (preg_match('|^' . $sourcePath . '/|', $targetPath . '/')) return false;

        if (!claro_mkdir($targetPath . '/' . $fileName, CLARO_FILE_PERMISSIONS)) return false;

        $dirHandle = opendir($sourcePath);

        if (!$dirHandle) return false;

        $copiableFileList = array();

        while ($element = readdir($dirHandle) ) {
            if ( $element == '.' || $element == '..') continue;
            $copiableFileList[] = $sourcePath . '/' . $element;
        }

        closedir($dirHandle);

        if ( count($copiableFileList) > 0 )
        {
            foreach($copiableFileList as $thisFile)
            {
                if ( ! claro_copy_file($thisFile, $targetPath . '/' . $fileName) ) return false;
            }
        }

        return true;
    } // end elseif is_dir()
}

/*
 * create directory
 *
 * @param string  $pathname
 * @param int     $mode directory permission (optional)
 * @param boolean $recursive (optional)
 * @return boolean TRUE if succeed, false otherwise
 */

function claro_mkdir($pathName, $mode = 0777, $recursive = false)
{
    global $webDir;

    if ($recursive)
    {
        if ( strstr($pathName,$webDir) !== false )
        {
            /* Remove rootSys path from pathName for system with safe_mode or open_basedir restrictions
               Functions (like file_exists, mkdir, ...) return false for files inaccessible with these restrictions
            */

            $pathName = str_replace($webDir,'',$pathName);
            $dirTrail = $webDir ;
        }
        else
        {
            $dirTrail = '';
        }

        $dirList = explode( '/', str_replace('\\', '/', $pathName) );
        $dirList[0] = empty($dirList[0]) ? '/' : $dirList[0];
        foreach($dirList as $thisDir)
        {
            $dirTrail .= empty($dirTrail) ? $thisDir : '/'.$thisDir;

            if ( file_exists($dirTrail) ) 
            {
                if ( is_dir($dirTrail) ) continue;
                else                     return false;
            }
            else
            {
                 if (!mkdir($dirTrail , $mode) ) return false;
            }

        }
        return true;
    }
    else
    {
        return mkdir($pathName, $mode);
    }
}

/* ----------- end of backported functions from Claroline 1.7.x ----------- */
