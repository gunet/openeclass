<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
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

/**===========================================================================
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

/**
 * replaces some dangerous character in a string for HTML use
 * currently: ?*<>\/"|:
 */

function replace_dangerous_char($string)
{
	return preg_replace('/[?*<>\\/\\\\"|:]/', '-', $string);
}

//------------------------------------------------------------------------------

/**
 * change the file name extension from .php to .phps
 * Useful to secure a site !!
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - the filenam phps'ized
 */

function php2phps ($fileName)
{
	$fileName = ereg_replace(".php$", ".phps", $fileName);
	return $fileName;
}

//------------------------------------------------------------------------------


/** 
 * Compute the size already occupied by a directory and is subdirectories
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - dirPath (string) - size of the file in byte
 * @return - int - return the directory size in bytes
 */

function dir_total_space($dirPath)
{
	$sumSize=0;
	chdir ($dirPath) ;
	$handle = opendir($dirPath);

	while ($element = readdir($handle) )
	{
		if ( $element == "." || $element == "..")
		{
			continue; // skip the current and parent directories
		}
		if ( is_file($element) )
		{
			@$sumSize += filesize($element);
		}
		if ( is_dir($element) )
		{
			$dirList[] = $dirPath.'/'.$element;
		}
	}

	closedir($handle) ;

	if ( isset($dirList) && sizeof($dirList) > 0)
	{
		foreach($dirList as $j)
		{
			$sizeDir = dir_total_space($j);	// recursivity
			$sumSize += $sizeDir;
		}
	}

	return $sumSize;
}


//------------------------------------------------------------------------------

/** 
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

function add_ext_on_mime($fileName)
{
	/*** check if the file has an extension AND if the browser has send a MIME Type ***/

	if(!ereg("\.[[:alnum:]]+$", $fileName) and @$_FILES['userFile']['type']) {
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
			if ($type == $_FILES['userFile']['type']) {
				$fileName .=  $extension[$key];
				break;
			}
		}

		unset($mimeType, $extension, $type, $key); // Delete to eschew possible collisions
	}

	return $fileName;
}

/* --------------- backported functions from Claroline 1.7.x --------------- */

/**
 * change the file named .htacess in htacess.txt
 * Useful to secure a site working on Apache.
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - string 'Apache safe' file name
 */


function htaccess2txt($fileName)
{
    $fileName = str_ireplace('.htaccess', 'htaccess.txt', $fileName);
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

/**
 * change the file named .htacess in htacess.txt
 * Useful to secure a site working on Apache.
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - string innocuous filename
 * @see    - htaccess2txt and php2phps
 */


function get_secure_file_name($fileName)
{
    $fileName = php2phps($fileName);
    $fileName = htaccess2txt($fileName);
    return $fileName;
}

/** 
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

/**
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

/* ----------- end of backported functions from Claroline 1.7.x ----------- */

?>
