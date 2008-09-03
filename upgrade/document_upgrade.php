<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/*==============================================================================
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

// -----------------------------------
// functions for document ------------
// -----------------------------------

// Returns a random filename with the same extension as $filename
function random_filename($filename)  {
        $ext = get_file_extention($filename);
        if (!empty($ext)) {
                $ext = '.' . $ext;
        }
        return date("mdGi") . randomkeys('5') . $ext;
}

// Rename a file and insert its information in the database, if needed
// Returns the new file path or false if file wasn't renamed
function document_upgrade_file($path, $data)
{
        if ($data == 'document') {
                $table = 'document';
        } else {
                $table = 'group_documents';
        }

        // Filenames in older versions of eClass were in ISO-8859-7
        // No need to conver them, we're assuming "SET NAMES greek"
        $db_path = trim_path($path);
        $old_filename = preg_replace('|^.*/|', '', $db_path);
        $new_filename = random_filename($old_filename);
        $new_path = preg_replace('|/[^/]*$|', "/$new_filename", $db_path);
        $r = db_query("SELECT * FROM $table WHERE path = ".quote($db_path));
        if (mysql_num_rows($r) > 0) {
                $current_filename = mysql_fetch_array($r);
                if (empty($current_filename['filename'])) {
                        // File exists in database, hasn't been upgraded
                        db_query("UPDATE $table
                                        SET filename = " . quote($old_filename) . ",
                                        path = " . quote($new_path) . "
                                        WHERE path= " . quote($db_path));
                        rename($path, $data.$new_path);
                } else {
                        // File wasn't renamed
                        $new_path = false;
                }
        } else {
                // File doesn't exist in database
                $file_date = quote(date('c', filemtime($path)));
                if ($table == 'document') {
                        db_query("INSERT INTO document
                                  SET path = " . quote($new_path) . ",
                                      filename = " . quote($old_filename) . ",
                                      visibility = 'v', 
                                      comment = '', category = '',
                                      title = '', creator = '',
                                      date = $file_date, date_modified = $file_date,
                                      subject = '', description = '',
                                      author = '', format = '',
                                      language = '', copyrighted = ''");
                } else {
                        db_query("INSERT INTO group_documents
                                  SET path = " . quote($new_path) . ",
                                  filename = " . quote($old_filename));
                }
echo("<div style='background-color: green'>rename($path, $data$new_path);</div>");
                rename($path, $data.$new_path);
        }
        return $new_path;
}

// Upgrade a directory, and if it was renamed, fix its contents'
// database records to point to the new path
function document_upgrade_dir($path, $data)
{
        if ($data == 'document') {
                $table = 'document';
        } else {
                $table = 'group_documents';
        }

        $db_path = trim_path($path);
        $new_path = document_upgrade_file($path, $data);
        if ($new_path) {
                // Directory was renamed - need to update contents' entries
                db_query("UPDATE $table
                          SET path = CONCAT(".quote($new_path).',
                                SUBSTRING(path FROM '. (1+strlen($db_path)) .'))
                          WHERE path LIKE '.quote("$db_path%"));
        }
}


// Remove the first component from beginning of $path, return the rest starting with '/'
function trim_path($path)
{
        return preg_replace('|^[^/]*/|', '/', $path);
}


// Upgrades 'group_documents' table and encodes all filenames to be pure ASCII
// Database selected should be the current course database
function encode_group_documents($course_code, $group_id, $secret_directory)
{
        chdir($GLOBALS['webDir'].'courses/'.$course_code.'/group');
        traverseDirTree($secret_directory, 'document_upgrade_file', 'document_upgrade_dir', $secret_directory);
}


// Upgrades 'document' table and encodes all filenames to be pure ASCII
// Database selected should be the current course database
function encode_documents($course_code)
{
        chdir($GLOBALS['webDir'].'courses/'.$course_code);
        traverseDirTree('document', 'document_upgrade_file', 'document_upgrade_dir', 'document');
}


// -----------------------------------------------------------
// generic function to traverse the directory tree depth first
// -----------------------------------------------------------
function traverseDirTree($base, $fileFunc, $dirFunc, $data) {
        $subdirectories = opendir($base);
        // First process all directories
        while (($subdirectory = readdir($subdirectories)) !== false){
                $path = $base.'/'.$subdirectory;
                if ($subdirectory != '.' and $subdirectory != '..' and is_dir($path)) {
                        traverseDirTree($path, $fileFunc, $dirFunc, $data);
                        $dirFunc($path, $data);
                }
        }
        // Then process all files
        rewinddir($subdirectories);
        while (($filename = readdir($subdirectories)) !== false){
                $path = $base.'/'.$filename;
                if (is_file($path)) {
                        $fileFunc($path, $data);
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
        if (rename($path_to_video.$file, $path_to_video.$safe_filename)) {
        	db_query("UPDATE video SET path = '$safe_filename'
	        	WHERE id = '$id'", $code);
	} else {
		echo "Προσοχή: το αρχείο video $path_to_video.$file δεν υπάρχει!<br>";
                db_query("DELETE FROM video WHERE id = '$id'", $code);
        }
}
