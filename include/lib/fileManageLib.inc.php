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

		while ($element = readdir($handle)) {
			if ( $element == "." || $element == "..") {
				continue;	// skip current and parent directories
			} elseif (is_file($element)) {
				unlink($element);
			} elseif (is_dir($element)) {
				$dirToRemove[] = $dirPath."/".$element;
			}
		}

		closedir ($handle) ;
                chdir($cwd);

		if (isset($dirToRemove) and sizeof($dirToRemove) > 0) {
			foreach($dirToRemove as $j) removeDir($j) ; // recursivity
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
                                if (strpos($target, $source) === 0)
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

        $cwd = getcwd();
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
        chdir ($cwd);
}



// Return a list of all directories
function directory_list()
{
        global $group_sql;

	$dirArray = array();

        $r = db_query("SELECT filename, path FROM document WHERE $group_sql AND format = '.dir' ORDER BY path");
	while ($row = mysql_fetch_array($r)) {
                $dirArray[$row['path']] = $row['filename'];
	}
	return $dirArray ;
}

/*
 * Returns HTML form select element listing all directories in current course documents
 * excluding the one with path $entryToExclude
 */
function directory_selection($source_value, $command, $entryToExclude)
{
	global $langParentDir, $langTo, $langMoveFrom, $langMove, $moveFileNameAlias;
	global $tool_content, $groupset;

        if (!empty($groupset)) {
                $groupset = '?' . $groupset;
        }
	$dirList = directory_list();
	$dialogBox = "
	<form action='$_SERVER[PHP_SELF]$groupset' method='post'>
	<fieldset>
	<input type='hidden' name='source' value='$source_value'>
        <table class='tbl' width='99%'>
        <tr>
          <td>$langMoveFrom &nbsp;&nbsp;<b>$moveFileNameAlias</b>&nbsp;&nbsp; $langTo:";
	$dialogBox .= "
            <select name='$command'>" ;
        if ($entryToExclude != '/') {
                $dialogBox .= "<option value=''>$langParentDir</option>\n";
        }
	
	/* build html form inputs */
        foreach ($dirList as $path => $filename) {
                $depth = substr_count($path, '/');
                $tab = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
                if ($path != $entryToExclude) {
                        $dialogBox .= "<option value='$path'>$tab$filename</option>\n";
                }
	}

	$dialogBox .= "
            </select>
          </td>
          <td class='right'><input type=\"submit\" value=\"$langMove\"></td>
        </tr>
        </table>
        </fieldset>
	</form>";
	return $dialogBox;
}

// Create a zip file with the contents of documents path $downloadDir
function zip_documents_directory($zip_filename, $downloadDir)
{
        global $basedir, $group_sql;

        $GLOBALS['map_filenames'] = map_to_real_filename($downloadDir);

        $cwd = getcwd();
        chdir($basedir);

        $zipfile = new PclZip($zip_filename);
        $v = $zipfile->create(substr($downloadDir, 1),
                              PCLZIP_CB_PRE_ADD, 'convert_to_real_filename');
        if (!$v) {
                die("error: ".$zipfile->errorInfo(true));
        }

        // delete invisible files from zip file
        $files_to_exclude = array();
        $sql = db_query("SELECT filename FROM document
                                WHERE $group_sql AND
                                      path LIKE '%$downloadDir%' AND
                                      visibility='i'");
        while ($files = mysql_fetch_array($sql, MYSQL_ASSOC)) {
                array_push($files_to_exclude, $files['filename']);
        }
        foreach ($files_to_exclude as $files_to_delete) {
                $zipfile->delete(PCLZIP_OPT_BY_NAME, greek_to_latin($files_to_delete));
        }
        chdir($cwd);
}

// Creates mapping between encoded filenames and real filenames
function map_to_real_filename($downloadDir) {

        global $group_sql;

	$prefix = strlen(preg_replace('|[^/]*$|', '', $downloadDir))-1;
        $encoded_filenames = $decoded_filenames = $filename = array();

        $sql = db_query("SELECT path, filename FROM document
                                WHERE $group_sql AND
                                      path LIKE '%$downloadDir%'");
        while ($files = mysql_fetch_array($sql)) {
                array_push($encoded_filenames, $files['path']);
                array_push($filename, $files['filename']);
        }
        $decoded_filenames = $encoded_filenames;
        foreach ($encoded_filenames as $position => $name) {
                $last_name_component = substr(strrchr($name, "/"), 1);
                foreach ($decoded_filenames as &$newname) {
                        $newname = str_replace($last_name_component, $filename[$position], $newname);
                }
                unset($newname);
        }
	foreach($decoded_filenames as &$s) {
		$s = substr($s, $prefix);
	}

        // create array with mappings
        $map_filenames = array_combine($encoded_filenames, $decoded_filenames);

	return($map_filenames);
}

// PclZip callback function to store filenames with real filenames
function convert_to_real_filename($p_event, &$p_header)
{
        global $map_filenames;

        $filename = '/' . $p_header['filename'];
        foreach ($p_header as $real_name) {
                $p_header['stored_filename'] = substr(greek_to_latin($map_filenames[$filename]), 1);
        }
        return 1;
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
