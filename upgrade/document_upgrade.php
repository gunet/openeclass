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
==============================================================================

	@authors list: Originally written by Agorastos Sakis <th_agorastos@hotmail.com>
	and later rewritten 
		    by Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr>
==============================================================================
        @Description: Main script to upgrade the documents section
 	This script creates DB records in every course DB for every document that lies in
	the /document folder of each course 	
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

// -------------------------------------------------
// generic function to traverse the directory tree
// -------------------------------------------------
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
