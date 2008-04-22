<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
  =============================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
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
	document_upgrade.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: Main script to upgrade the documents section

 	This script creates DB records in every course DB for every document that lies in
	the /document folder of each course

 	The script does not return any feedback
==============================================================================*/

include '../include/lib/fileUploadLib.inc.php';
include '../include/lib/forcedownload.php';

// encode files
function encode_file($filename)  {
	global $baseFolder, $tool_content;

        $ext = get_file_extention($filename);
	$safe_fileName = date("mdGi").randomkeys('5').".".$ext;
	$newfilename = preg_replace('|/[^/]+$|', '/'.$safe_fileName, $filename);
	$b = db_query("SELECT unique_filename FROM doc_tmp 
		WHERE old_path ='/".substr($filename, strlen($baseFolder))."'");
	$u = mysql_fetch_array($b);
	// rename
	if (!(rename($filename, $newfilename)))  {
	  	$tool_content .= "Σφάλμα κατά την μετονομασία του $filename σε $newfilename !";
	} else {
	// fill doc_tmp table	
		$query = "UPDATE doc_tmp SET new_filename = '".preg_replace('|^.*/|', '', $newfilename)."'
    			WHERE unique_filename = '$u[unique_filename]'";
		db_query($query);
	}
}


// fill an array with directory names
function array_dir($dirname)  {
	global $dirnames;

	$end = strlen($dirname)-2;
	$dir = substr($dirname,$end);
	$end2 = strlen($dirname)-1;
	$dir2 = substr($dirname,$end2);
	if (($dir2 != '.') and ($dir != '..')) {
		$dirnames[] = $dirname;
	}
}


// fill an array with old directory names
function array_old_dir($dirname)  {
	global $oldfilenames;

	$end = strlen($dirname)-2;
	$dir = substr($dirname,$end);
	$end2 = strlen($dirname)-1;
	$dir2 = substr($dirname,$end2);
	if (($dir2 != '.') and ($dir != '..')) {
		$oldfilenames[] = $dirname;
	}
}


// fill an array with new encoded directory names
function array_enc_dir($dirname)  {
	global $encdirnames;

	$end = strlen($dirname)-2;
	$dir = substr($dirname,$end);
	$end2 = strlen($dirname)-1;
	$dir2 = substr($dirname,$end2);
	if (($dir2 != '.') and ($dir != '..')) {
		$encdirnames[] = $dirname;
	}
}

// fill an array with new encoded file names
function array_enc_file($filename)  {
	global $encfilenames;
	
	$encfilenames[] = $filename;
}


// fill an array with old file names
function array_old_file($filename)  {
	global $oldfilenames;

	$oldfilenames[] = $filename;
	
}


// generic function to traverse the directory tree
function traverseDirTree($base, $fileFunc, $dirFunc=null, $afterDirFunc=null) {
  $subdirectories=opendir($base);
  while (($subdirectory=readdir($subdirectories))!==false){
    $path=$base.$subdirectory;
    if (is_file($path)){
      if ($fileFunc!==null) $fileFunc($path);
    }else{
      if ($dirFunc!==null) $dirFunc($path);
      if (($subdirectory!='.') && ($subdirectory!='..')){
        traverseDirTree($path.'/',$fileFunc,$dirFunc,$afterDirFunc);
      }
      if ($afterDirFunc!==null) $afterDirFunc($path);
    }
  }
	closedir($subdirectories);
}




function upgrade_file($file)  {

	$fileName = trim($file);
	$result = mysql_query ("SELECT filename FROM document WHERE path == '$fileName'");
	$row = mysql_fetch_array($result);

	if (empty($row['filename'])) //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
	{
            /**** Check for no desired characters ***/
            $fileName = replace_dangerous_char($fileName);
            /*** Try to add an extension to files witout extension ***/
            $fileName = add_ext_on_mime($fileName);
            /*** Handle PHP files ***/
            $fileName = php2phps($fileName);
            //ypologismos onomatos arxeiou me date + time + 3 tyxaia alfarithmitika psifia.
            //to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
	    $ext = get_file_extention($fileName);
	    if ($ext == '') 
		$safe_fileName = date("YmdGis")."_".randomkeys('3');
	    else 
	       	$safe_fileName = date("YmdGis")."_".randomkeys('3').".".$ext;
            //san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
            $file_date = date("Y\-m\-d G\:i\:s");
            //arxikopoihsh timwn twn metavlhtwn gia to upgrade twn eggrafwn
            $file_comment = "";
            $file_category = "";
            $file_title = "";
            $file_creator = "";
            $file_subject = "";
            $file_description = "";
            $file_author = "";
            $file_format = "";
            $file_language = "";
            $file_copyrighted = "";
	    //san file format vres to extension tou arxeiou	
            $file_format = get_file_extention($fileName);

            $query = "INSERT INTO document SET 
            	path = '$safe_fileName',
            	filename = '$fileName',
            	visibility = 'v',
            	comment	= '$file_comment',
            	category = '$file_category',
            	title =	'$file_title',
            	creator	= '$file_creator',
            	date = '$file_date',
            	date_modified =	'$file_date',
            	subject	= '$file_subject',
            	description = '$file_description',
            	author = '$file_author',
            	format	= '$file_format',
            	language = '$file_language',
            	copyrighted = '$file_copyrighted'";
            //rename(getcwd().$existinguploadPath, getcwd().$uploadPath2);
            mysql_query($query);
	} //telos if(empty($row['filename']))
}




//anadromikos algorithmos scannarismatos arxeiwn kai fakelwmn - einai h kyria function gia to scannarisma olwn twn arxeiwn kai twn fakelwn 
function RecurseDir($directory, $baseFolder)
{
	//$thisdir = array("name", "struct");
	//$thisdir['name'] = $directory;
	if ($dir = opendir($directory))
	{
	  $i = 0;
		while ($file = readdir($dir))
		{
			if (($file != ".")&&($file != ".."))
			{
		//		$tempDir = $directory."\\".$file;
				$tempDir = $directory."/".$file;
				if (is_dir($tempDir)) {
				  	$NewDir = upgrade_dir($tempDir);
					//rewinddir($dir);
					closedir($dir);
				//die($NewDir);
				//$thisdir['struct'][] = RecurseDir($tempDir, $baseFolder);
	//				$thisdir['struct'][] = RecurseDir($NewDir, $baseFolder);
					RecurseDir($NewDir, $baseFolder);
				} else {			
				  //$thisdir['struct'][] = $file;
				  $tmp = $directory;
				  $tmp = substr($tmp, strlen($baseFolder));
				  //$tmp = str_replace("\\", "/", $tmp);
				  if (empty($tmp)) 
				  {
				  	upgrade_document("/", $file);
				  }
				  else
				  {
				  	upgrade_document("/".$tmp."/", $file);
				  }
				} 
		  	$i++;
			} 			
		} 		
		if ($i == 0)
		{
		  // empty directory
		  //$thisdir['struct'] = -2;
		}
		
	} else
	{
	  // directory could not be accessed
		//$thisdir['struct'] = -1;
	}
	return true;
	//return $thisdir;
} //telos anadromikou algorithmou scannarismatos arxeiwn kai fakelwn

//leitourgeia pou pairnei san orismata $uploadPath = sxetikos fakelos pou vrisketai to arxeio (p.x. "/" h' "/folder/") kai $file = pragmatiko_onoma_arxeiou
//h leitourgia metonomazei to onoma tou arxeiou se kapoio me safe_filename xrhsimopoiwntas enan genhtora monadikou arithmou kai prosthetei ston pinaka document ths vashs tou kathe mathimatos (h vash exei proepilegei apo prohgoumena vhmata) ta anagkaia metadedomena
function upgrade_document ($uploadPath, $file)
{
	
	$fileName = trim($file);
	/* elegxos ean to "path" tou arxeiou pros upload vrisketai hdh se eggrafh ston pinaka documents
    	(aftos einai ousiastika o elegxos if_exists dedomenou tou oti to onoma tou arxeiou sto filesystem
    	einai monadiko) */

	$result = mysql_query ("SELECT filename FROM document WHERE path LIKE '%".$uploadPath.$fileName."%'");
	$row = mysql_fetch_array($result);

	if (empty($row['filename'])) //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
	{
            /**** Check for no desired characters ***/
            $fileName = replace_dangerous_char($fileName);
            /*** Try to add an extension to files witout extension ***/
            $fileName = add_ext_on_mime($fileName);
            /*** Handle PHP files ***/
            $fileName = php2phps($fileName);
            //ypologismos onomatos arxeiou me date + time + 3 tyxaia alfarithmitika psifia.
            //to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
	     $ext = get_file_extention($fileName);
	    if ($ext == "")
            	$safe_fileName = date("YmdGis")."_".randomkeys('3')."";
	     else
		$safe_fileName = date("YmdGis")."_".randomkeys('3').".".$ext;
            //prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
            if ($uploadPath == ".")
            {
            	$uploadPath2 = "/".$safe_fileName;
//            	$existinguploadPath =  "/".str_replace("/", "\\", $fileName);
		$existinguploadPath =  "/".$fileName;
            }
            else
            {
            	$uploadPath2 = $uploadPath.$safe_fileName;
            	$existinguploadPath = $uploadPath.$fileName;
            }
            //san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
            $file_date = date("Y\-m\-d G\:i\:s");
            //arxikopoihsh timwn twn metavlhtwn gia to upgrade twn eggrafwn
            $file_comment = "";
            $file_category = "";
            $file_title = "";
            $file_creator = "";
            $file_subject = "";
            $file_description = "";
            $file_author = "";
            $file_format = "";
            $file_language = "";
            $file_copyrighted = "";
	    //san file format vres to extension tou arxeiou	
            $file_format = get_file_extention($fileName);

            $query = "INSERT INTO document SET 
            	path = '$uploadPath2',
            	filename = '$fileName',
            	visibility = 'v',
            	comment	= '$file_comment',
            	category = '$file_category',
            	title =	'$file_title',
            	creator	= '$file_creator',
            	date = '$file_date',
            	date_modified =	'$file_date',
            	subject	= '$file_subject',
            	description = '$file_description',
            	author = '$file_author',
            	format	= '$file_format',
            	language = '$file_language',
            	copyrighted = '$file_copyrighted'";
            rename(getcwd().$existinguploadPath, getcwd().$uploadPath2);
            mysql_query($query);
	} //telos if(empty($row['filename']))
}


// -------------------------------------
// function for upgrading video files
// -------------------------------------
function upgrade_video($file, $id, $code)
{
	global $webDir;

	$fileName = trim($file);
        $fileName = replace_dangerous_char($fileName);
        $fileName = add_ext_on_mime($fileName);
	$fileName = php2phps($fileName);
        $safe_filename = date("YmdGis")."_".randomkeys('3').".".get_file_extention($fileName);
	$path_to_video = $webDir.'video/'.$code.'/';
        if (rename($path_to_video.$file, $path_to_video.$safe_filename))
        	db_query("UPDATE video SET path = '$safe_filename'
	        	WHERE id = '$id'", $code);
	else 
		die("error");
}
