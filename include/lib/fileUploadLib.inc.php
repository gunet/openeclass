<?php
/*

      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 Lib for file Upload $Revision$         |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |    $Id$  |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+

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

	if(!ereg("\.[[:alnum:]]+$", $fileName)
		&& $_FILES['userFile']['type'])
	{
		/*** Build a "MIME-types/extensions" connection table ***/

		static $mimeType = array();

		$mimeType[] = "application/msword";				$extension[] =".doc";
		$mimeType[] = "application/rtf";				$extension[] =".rtf";
		$mimeType[] = "application/vnd.ms-powerpoint";	$extension[] =".ppt";
		$mimeType[] = "application/vnd.ms-excel";		$extension[] =".xls";
		$mimeType[] = "application/pdf";				$extension[] =".pdf";
		$mimeType[] = "application/postscript";			$extension[] =".ps";
		$mimeType[] = "application/mac-binhex40";		$extension[] =".hqx";
		$mimeType[] = "application/x-gzip";				$extension[] ="tar.gz";
		$mimeType[] = "application/x-shockwave-flash";	$extension[] =".swf";
		$mimeType[] = "application/x-stuffit";			$extension[] =".sit";
		$mimeType[] = "application/x-tar";				$extension[] =".tar";
		$mimeType[] = "application/zip";				$extension[] =".zip";
		$mimeType[] = "application/x-tar";				$extension[] =".tar";
		$mimeType[] = "text/html";						$extension[] =".htm";
		$mimeType[] = "text/plain";						$extension[] =".txt";
		$mimeType[] = "text/rtf";						$extension[] =".rtf";
		$mimeType[] = "image/gif";						$extension[] =".gif";
		$mimeType[] = "image/jpeg";						$extension[] =".jpg";
		$mimeType[] = "image/png";						$extension[] =".png";
		$mimeType[] = "audio/midi";						$extension[] =".mid";
		$mimeType[] = "audio/mpeg";						$extension[] =".mp3";
		$mimeType[] = "audio/x-aiff";					$extension[] =".aif";
		$mimeType[] = "audio/x-pn-realaudio";			$extension[] =".rm";
		$mimeType[] = "audio/x-pn-realaudio-plugin";	$extension[] =".rpm";
		$mimeType[] = "audio/x-wav";					$extension[] =".wav";
		$mimeType[] = "video/mpeg";						$extension[] =".mpg";
		$mimeType[] = "video/quicktime";				$extension[] =".mov";
		$mimeType[] = "video/x-msvideo";				$extension[] =".avi";


		/*** Check if the MIME type send by the browser is in the table ***/

		foreach($mimeType as $key=>$type)
		{
			if ($type == $_FILES['userFile']['type'])
			{
				$fileName .=  $extension[$key];
				break;
			}
		}

		unset($mimeType, $extension, $type, $key); // Delete to eschew possible collisions
	}

	return $fileName;
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

?>
