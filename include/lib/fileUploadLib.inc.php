<?php

/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*===========================================================================
	fileUploadLib.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.3 licensed under GPL
	     and Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: fileUploadLib.inc.php Revision: 1.3
     extra porting from: fileUpload.lib.php Revision 1.29.2.4
     extra porting from: claro_main.lib.php Revision 1.164.2.4
	      
	Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
                      Hugues Peeters    <peeters@ipm.ucl.ac.be>
                      Christophe Gesche <gesche@ipm.ucl.ac.be>
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
*/

/*
 * replaces some dangerous character in a string for HTML use
 * currently: ?*<>\/"|:.
 */

function replace_dangerous_char($string)
{
	return preg_replace('/[?*<>\\/\\\\"|:\'\.]/', '_', $string);
}

//------------------------------------------------------------------------------

/*
 * change the file name extension from .php to .phps
 * Useful to secure a site !!
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - the filenam phps'ized
 */

function php2phps($fileName)
{
	$fileName = preg_replace('/\.(php[0-9]?|phtml)$/', '.phps', $fileName);
	return $fileName;
}


/* 
 * Compute the size already occupied by a directory and is subdirectories
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - dirPath (string) - size of the file in byte
 * @return - int - return the directory size in bytes
 */

function dir_total_space($dirPath)
{
	$sumSize = 0;
	$handle = opendir($dirPath);
	while ($element = readdir($handle)) {
                $file = $dirPath . '/' . $element;
		if ($element == '.' or $element == '..') {
			continue; // skip the current and parent directories
		}
		if (is_file($file)) {
			$sumSize += filesize($file);
		}
		if (is_dir($file)) {
			$sumSize += dir_total_space($file);
		}
	}
	closedir($handle) ;
	return $sumSize;
}


/* 
 * Try to add an extension to files witout extension
 * Some applications on Macintosh computers don't add an extension to the files.
 * This subroutine try to fix this on the basis of the MIME type send 
 * by the browser.
 *
 * Note : some browsers don't send the MIME Type (e.g. Netscape 4).
 *        We don't have solution for this kind of situation
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) - Name of the file
 * @return - fileName (string)
 *
 */

function add_ext_on_mime($fileName, $userFile = 'userFile')
{
	/*** check if the file has an extension AND if the browser has send a MIME Type ***/

	if(!preg_match('/\.[[:alnum:]]+$/', $fileName) and @$_FILES[$userFile]['type']) {
		/*** Build a "MIME-types/extensions" connection table ***/

		static $mimeType = array();

		$mimeType[] = "application/msword";
		$extension[] =".doc";

		$mimeType[] = "application/rtf";
		$extension[] =".rtf";

		$mimeType[] = "application/vnd.ms-powerpoint";
        	$extension[] =".ppt";

		$mimeType[] = "application/vnd.ms-excel";
		$extension[] =".xls";

		$mimeType[] = "application/pdf";
                $extension[] =".pdf";

		$mimeType[] = "application/postscript";
		$extension[] =".ps";

		$mimeType[] = "application/mac-binhex40";
		$extension[] =".hqx";

		$mimeType[] = "application/x-gzip";
		$extension[] ="tar.gz";

		$mimeType[] = "application/x-shockwave-flash";
        	$extension[] =".swf";

		$mimeType[] = "application/x-stuffit";
		$extension[] =".sit";

		$mimeType[] = "application/x-tar";
		$extension[] =".tar";

		$mimeType[] = "application/zip";
		$extension[] =".zip";

		$mimeType[] = "application/x-tar";
		$extension[] =".tar";

		$mimeType[] = "text/html";
		$extension[] =".htm";

		$mimeType[] = "text/plain";
		$extension[] =".txt";

		$mimeType[] = "text/rtf";
		$extension[] =".rtf";

		$mimeType[] = "image/gif";
        	$extension[] =".gif";

		$mimeType[] = "image/jpeg";
		$extension[] =".jpg";

		$mimeType[] = "image/png";
		$extension[] =".png";

		$mimeType[] = "audio/midi";
		$extension[] =".mid";

		$mimeType[] = "audio/mpeg";
		$extension[] =".mp3";

		$mimeType[] = "audio/x-aiff";
		$extension[] =".aif";

		$mimeType[] = "audio/x-pn-realaudio";
		$extension[] =".rm";

		$mimeType[] = "audio/x-pn-realaudio-plugin";
        	$extension[] =".rpm";

		$mimeType[] = "audio/x-wav";
		$extension[] =".wav";

		$mimeType[] = "video/mpeg";
		$extension[] =".mpg";

		$mimeType[] = "video/quicktime";
		$extension[] =".mov";

		$mimeType[] = "video/x-msvideo";
		$extension[] =".avi";


		/*** Check if the MIME type send by the browser is in the table ***/

		foreach($mimeType as $key=>$type) {
			if ($type == $_FILES[$userFile]['type']) {
				$fileName .=  $extension[$key];
				break;
			}
		}

		unset($mimeType, $extension, $type, $key); // Delete to eschew possible collisions
	}

	return $fileName;
}

/**
 * Replace str_ireplace()
 * Backported from Claroline 1.7.x claro_main.lib.php
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_ireplace
 * @author      Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision$
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 * @note        count not by returned by reference, to enable
 *              change '$count = null' to '&$count'
 */

if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        // Sanity check
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        // If search isn't an array, make it one
        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        // If replace isn't an array, make it one, and pad it to the length of search
        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {
                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        // Check the replace array is padded to the correct length
        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        // If subject is not an array, make it one
        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        // Loop through each subject
        $count = 0;
        foreach ($subject as $subject_key => $subject_value) {
            // Loop through each search
            foreach ($search as $search_key => $search_value) {
                // Split the array into segments, in between each part is our search
                $segments = explode(strtolower($search_value), strtolower($subject_value));

                // The number of replacements done is the number of segments minus the first
                $count += count($segments) - 1;
                $pos = 0;

                // Loop through each segment
                foreach ($segments as $segment_key => $segment_value) {
                    // Replace the lowercase segments with the upper case versions
                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));
                    // Increase the position relative to the initial string
                    $pos += strlen($segment_value) + strlen($search_value);
                }

                // Put our original string back together
                $subject_value = implode($replace[$search_key], $segments);
            }

            $result[$subject_key] = $subject_value;
        }

        // Check if subject was initially a string and return it as a string
        if ($was_array === true) {
            return $result[0];
        }

        // Otherwise, just return the array
        return $result;
    }
}

/* 
 * Check if there is enough place to add a file on a directory
 * on the base of a maximum directory size allowed
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileSize (int) - size of the file in byte
 * @param  - dir (string) - Path of the directory
 *           whe the file should be added
 * @param  - maxDirSpace (int) - maximum size of the diretory in byte
 * @return - boolean true if there is enough space
 * @return - false otherwise
 *
 * @see    - enough_size() uses  dir_total_space() function
 */

function enough_size($fileSize, $dir, $maxDirSpace)
{
	if ($maxDirSpace)
	{
		$alreadyFilledSpace = dir_total_space($dir);

		if ( ($fileSize + $alreadyFilledSpace) > $maxDirSpace)
		{
			return false;
		}
	}
	return true;
}

/*
 * Determine the maximum size allowed to upload. This size is based on
 * the tool $maxFilledSpace regarding the space already opccupied
 * by previous uploaded files, and the php.ini upload_max_filesize
 * and post_max_size parameters. This value is diplayed on the upload
 * form.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int local max allowed file size e.g. remaining place in
 *	an allocated course directory	
 * @return int lower value between php.ini values of upload_max_filesize and 
 *	post_max_size and the claroline value of size left in directory 
 * @see    - get_max_upload_size() uses  dir_total_space() function
 */
function get_max_upload_size($maxFilledSpace, $baseWorkDir)
{
        $php_uploadMaxFile = ini_get('upload_max_filesize');
        if (strstr($php_uploadMaxFile, 'M')) $php_uploadMaxFile = intval($php_uploadMaxFile) * 1048576;
        $php_postMaxFile  = ini_get('post_max_size');
        if (strstr($php_postMaxFile, 'M')) $php_postMaxFile     = intval($php_postMaxFile) * 1048576;
        $docRepSpaceAvailable  = $maxFilledSpace - dir_total_space($baseWorkDir);

        $fileSizeLimitList = array( $php_uploadMaxFile, $php_postMaxFile , $docRepSpaceAvailable );
        sort($fileSizeLimitList);
        list($maxFileSize) = $fileSizeLimitList;

	return $maxFileSize;
}

/*
	function s1howquota()
	param - quota 
	param - used , how much disp space is used
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>

    @Description: A page that shows a table with statistic data and a
    gauge bar. The statistical data are transfered here with GET in
    $diskQuotaDocument and $diskUsed

    This scipt uses the 'gaugebar.php' class for the graphic gauge bar
===============================================================*/

function showquota($quota, $used) {

	global $langQuotaUsed, $langQuotaPercentage, $langQuotaTotal, $langBack, 
               $code_cours, $subsystem, $group_id, $ebook_id;
	include 'gaugebar.php';

	$retstring = '';
	
        // diamorfwsh ths grafikhs mparas xrhsimopoioumenou kai eleftherou xwrou (me vash ta quotas) 
        // kai ypologismos statistikwn stoixeiwn
        $oGauge = new myGauge();
	$oGauge->MaxVal = $quota; //maximum value
	$oGauge->CurVal = $used; //current value

	// pososto xrhsimopoioumenou xorou se %
	$diskUsedPercentage = round(($used / $quota) * 100)."%";
	// morfopoihsh tou synolikou diathesimou megethous tou quota
	$quota = format_bytesize($quota / 1024);
	// morfopoihsh tou synolikou megethous pou xrhsimopoieitai
	$used = format_bytesize($used / 1024);

	// telos diamorfwshs ths grafikh mparas kai twn arithmitikwn statistikwn stoixeiwn
	// ektypwsh pinaka me arithmitika stoixeia + thn grafikh bara
        if ($subsystem == GROUP) {
                $link = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;group_id=$group_id";
        } elseif ($subsystem == EBOOK) {
                $link = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;ebook_id=$ebook_id";
        } else {
                $link = "$_SERVER[SCRIPT_NAME]?course=$code_cours";
        }
        $retstring .= "
               <div id='operations_container'>
                 <ul id='opslist'>
                   <li><a href=$link>" . $langBack . "</a></li>
                 </ul>
               </div>";

         $retstring .= "
                <table class='tbl_border'>
                <tr>
                  <th>$langQuotaUsed:</th>
              <td>$used</td>
                </tr>
                <tr>
                  <th>$langQuotaPercentage:</th>
              <td align='center'>";
                    $retstring .= $oGauge->display();
              $retstring .= "$diskUsedPercentage</td>
                </tr>
                <tr>
                  <th>$langQuotaTotal:</th>
              <td>$quota</td>
                </tr>
                </table>";
	$tmp_cwd = getcwd();
	
	return $retstring;
}

// check for dangerous extensions and file types
function unwanted_file($filename)
{
        return preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
                              'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .
                              'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $filename);
}


// Actions to do before extracting file from zip archive
// Create database entries and set extracted file path to
// a new safe filename
function process_extracted_file($p_event, &$p_header) {

        global $uploadPath, $realFileSize, $basedir, $cours_id,
               $subsystem, $subsystem_id, $uploadPath, $group_sql;

        $replace = isset($_POST['replace']);

        if (!isset($uploadPath)) {
                $uploadPath = '';
        }
        $realFileSize += $p_header['size'];
        $stored_filename = $p_header['stored_filename'];
        if (invalid_utf8($stored_filename)) {
                $stored_filename = cp737_to_utf8($stored_filename);
        }
        $path_components = explode('/', $stored_filename);
        $filename = php2phps(array_pop($path_components));
        if (unwanted_file($filename)) {
                $filename .= '.bin';
        }
        $file_date = date("Y\-m\-d G\:i\:s", $p_header['mtime']);
        $path = make_path($uploadPath, $path_components);
        if ($p_header['folder']) {
                // Directory has been created by make_path(),
                // no need to do anything else
                return 0;
        } else {
                // Check if file already exists
                $result = db_query("SELECT id, path, visibility FROM document
                                           WHERE $group_sql AND
                                                 path REGEXP '^$path/[^/]+$' AND
                                                 filename = " . quote($filename) . " LIMIT 1");
                $format = get_file_extension($filename);
                if (mysql_num_rows($result)) {
                        list($old_id, $file_path, $vis) = mysql_fetch_row($result);
                        if ($replace) {
                                // Overwrite existing file
                                $p_header['filename'] = $basedir . $file_path;
                                db_query("UPDATE document
                                                 SET date_modified = " . quote($file_date) . "
                                                 WHERE $group_sql AND
                                                       id = $old_id");
                                return 1;
                        } else {
                                // Rename existing file
                                $backup_n = 1;
                                do {
                                        $backup = preg_replace('/\.[a-zA-Z0-9_-]+$/', '', $filename) .
                                                  '_backup_' . $backup_n . '.' . $format;
                                        $q = db_query("SELECT COUNT(*) FROM document
                                                              WHERE $group_sql AND
                                                                    path REGEXP '^$path/[^/]+$' AND
                                                                    filename = " . quote($backup) . " LIMIT 1");
                                        list($n) = mysql_fetch_row($q);
                                        $backup_n++;
                                } while ($n > 0);
                                db_query("UPDATE document SET filename = " . quote($backup) . "
                                                 WHERE $group_sql AND
                                                       path = " . quote($file_path));
                        }
                }

                $path .= '/' . safe_filename($format);
                db_query("INSERT INTO document SET
                                 course_id = $cours_id,
                                 subsystem = $subsystem,
                                 subsystem_id = $subsystem_id,
                                 path = '$path',
                                 filename = " . quote($filename) .",
                                 visibility = 'v',
                                 comment = " . quote($_POST['file_comment']) . ",
                                 category = " . intval($_POST['file_category']) . ",
                                 title = '',
                                 creator = " . quote($_POST['file_creator']) . ",
                                 date = " . quote($file_date) . ",
                                 date_modified = " . quote($file_date) . ",
                                 subject = " . quote($_POST['file_subject']) . ",
                                 description = " . quote($_POST['file_description']) . ",
                                 author = " . quote($_POST['file_author']) . ",
                                 format = '$format',
                                 language = " . quote($_POST['file_language']) . ",
                                 copyrighted = " . intval($_POST['file_copyrighted']));
                // File will be extracted with new encoded filename
                $p_header['filename'] = $basedir . $path;
                return 1;
        }
}


// Create a path with directory names given in array $path_components
// under base path $path, inserting the appropriate entries in 
// document table.
// Returns the full encoded path created.
function make_path($path, $path_components)
{
        global $basedir, $nom, $prenom, $path_already_exists, $cours_id, $group_sql, $subsystem, $subsystem_id;

        $path_already_exists = true;
        $depth = 1 + substr_count($path, '/');
        foreach ($path_components as $component) {
                $q = db_query("SELECT path, visibility, format,
                                      (LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) AS depth
                                      FROM document
                                      WHERE $group_sql AND
                                            filename = " . quote($component) . " AND
                                            path LIKE '$path%' HAVING depth = $depth");
                if (mysql_num_rows($q) > 0) {
                        // Path component already exists in database
                        $r = mysql_fetch_array($q);
                        $path = $r['path'];
                        $depth++;
                } else {
                        // Path component must be created
                        $path .= '/' . safe_filename();
                        mkdir($basedir . $path, 0775);
                        db_query("INSERT INTO document SET
                                          course_id = $cours_id,
					  subsystem = $subsystem,
                                          subsystem_id = $subsystem_id,
                                          path='$path',
                                          filename=" . quote($component) . ",
                                          visibility='v',
                                          creator=" . quote($prenom." ".$nom) . ",
                                          date=NOW(),
                                          date_modified=NOW(),
                                          format='.dir'");
                        $path_already_exists = false;
                }
        }
        return $path;
}



/**
 * Validate a given uploaded filename against the whitelist and error if necessary.
 * 
 * @param string  $filename   - The given filename.
 * @param integer $menuTypeID - The menu type to display in case of error.
 */
function validateUploadedFile($filename, $menuTypeID = 2)
{
    global $tool_content, $head_content, $langBack, $langUploadedFileNotAllowed;

    if (!isWhitelistAllowed($filename))
    {
        $tool_content .= "<p class='caution'>$langUploadedFileNotAllowed<br/><a href='javascript:history.go(-1)'>$langBack</a></p><br/>";
        draw($tool_content, $menuTypeID, null, $head_content);
        exit;
    }
}


/**
 * Validate a given renamed filename against the whitelist and error if necessary.
 *
 * @param string  $filename   - The given filename.
 * @param integer $menuTypeID - The menu type to display in case of error.
 */
function validateRenamedFile($filename, $menuTypeID = 2)
{
	global $tool_content, $head_content, $langBack, $langRenamedFileNotAllowed;

	if (!isWhitelistAllowed($filename))
	{
		$tool_content .= "<p class='caution'>$langRenamedFileNotAllowed<br/><a href='javascript:history.go(-1)'>$langBack</a></p><br/>";
		draw($tool_content, $menuTypeID, null, $head_content);
		exit;
	}
}


/**
 * Validate a given uploaded zip archive contents against the whitelist and error if necessary.
 * 
 * @param array   $listContent - The list contents of the zip arhive, preferably by directly wiring PclZip::listContent().
 * @param integer $menuTypeID  - The menu type to display in case of error.
 */
function validateUploadedZipFile($listContent, $menuTypeID = 2)
{
    global $tool_content, $head_content, $langBack, $langUploadedZipFileNotAllowed;
    
    foreach ($listContent as $key => $entry)
    {
        if ($entry['folder'] == 1)
            continue;
        
        $filename = basename($entry['filename']);
        
        if (!isWhitelistAllowed($filename))
        {
            $tool_content .= "<p class='caution'>$langUploadedZipFileNotAllowed<br/><a href='javascript:history.go(-1)'>$langBack</a></p><br/>";
            draw($tool_content, $menuTypeID, null, $head_content);
            exit;
        }
    }
}


/**
 * Check whether a filename is allowed by the whitelist or not.
 * 
 * @param  string  $filename - The filename to check against the whitelist.
 * @return boolean           - Whether the whitelist allows the specific filename extension or not.
 */
function isWhitelistAllowed($filename)
{
    global $is_editor, $uid;

    $wh  = get_config('student_upload_whitelist');
    $wh2 = ($is_editor) ? get_config('teacher_upload_whitelist') : '';
    $wh3 = fetchUserWhitelist($uid);
    
    $wh .= (strlen($wh2) > 0) ? ', '. $wh2 : '';
    $wh .= (strlen($wh3) > 0) ? ', '. $wh3 : '';
    
    $whitelist = explode(',', preg_replace('/\s+/', '', $wh)); // strip any whitespace

    if (in_array('*', $whitelist))
        return true;

    $ext = getPureFileExtension($filename);
    return in_array($ext, $whitelist);
}


/**
 * Fetch a user's whitelist.
 * 
 * @param  integer $uid - The userId whose whitelist we want.
 * @return string       - The given user's whitelist.
 */
function fetchUserWhitelist($uid)
{
    global $mysqlMainDb;
    $q = db_query("SELECT whitelist FROM `$mysqlMainDb`.user WHERE user_id = ". intval($uid));
    $r = mysql_fetch_array($q);
    return $r['whitelist'];
}


/**
 * Mimic get_file_extension from main lib.
 * 
 * @param  string $filename - The filename whose extension we want. 
 * @return string           - The given filename's extension. 
 */
function getPureFileExtension($filename)
{
    $matches = array();
    if (preg_match('/\.([a-zA-Z0-9_-]{1,8})$/i', $filename, $matches))
        return strtolower($matches[1]);
    else
        return '';
}
