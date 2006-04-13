<?php
/**
 * Dropbox module for Claroline
 * handles downloads of files. Direct downloading is prevented because of an .htaccess file in the
 * dropbox directory. So everything goes through this script.
 * 
 * 1. Initialising vars
 * 2. Authorisation 
 * 3. Sanity check of get data & file
 * 4. Send headers
 * 5. Send file
 * 
 * @version 1.20
 * @copyright 2004
 * @author Jan Bols <jan@ivpv.UGent.be>
 * with contributions by René Haentjens <rene.haentjens@UGent.be> (see RH)
 */
/**
 * +----------------------------------------------------------------------
 *    |   This program is free software; you can redistribute it and/or      
 *    |   modify it under the terms of the GNU General Public License        
 *    |   as published by the Free Software Foundation; either version 2     
 *    |   of the License, or (at your option) any later version.             
 *    |                                                                      
 *    |   This program is distributed in the hope that it will be useful,    
 *    |   but WITHOUT ANY WARRANTY; without even the implied warranty of     
 *    |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      
 *    |   GNU General Public License for more details.                       
 *    |                                                                      
 *    |   You should have received a copy of the GNU General Public License  
 *    |   along with this program; if not, write to the Free Software        
 *    |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          
 *    |   02111-1307, USA. The GNU GPL license is also available through     
 *    |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         
 *    +----------------------------------------------------------------------
 * | Authors: Jan Bols          <jan@ivpv.UGent.be>                       |
 *    +----------------------------------------------------------------------
 */
 

require_once("dropbox_init1.inc.php");	//only call init1 because init2 outputs data
require_once("dropbox_class.inc.php");


/**
 * ========================================
 * AUTHORISATION SECTION
 * ========================================
 */
if (!isset($uid))
{
    exit();
}

if (isset($_GET['mailing'])) 
{
	checkUserOwnsThisMailing($_GET['mailing'], $uid);
}


/**
 * ========================================
 * SANITY CHECKS OF GET DATA & FILE
 * ========================================
 */
if (!isset( $_GET['id']) || ! is_numeric( $_GET['id'])) die($dropbox_lang["generalError"]);

$work = new Dropbox_work($_GET['id']);

$path = $dropbox_cnf["sysPath"] . "/" . $work -> filename; //path to file as stored on server
$file = $work->title;

// check that this file exists and that it doesn't include any special characters
if ( !is_file( $path))
{
    die($dropbox_lang["generalError"]);
}


/**
 * ========================================
 * SEND HEADERS
 * ========================================
 */
require_once("mime.inc.php"); //import mimetype array with mimetype info	


$fileparts = explode( '.', $file);
$filepartscount = count( $fileparts);

if (($filepartscount > 1) && isset($mimetype[$fileparts [$filepartscount - 1]]))
{ 
    // give hint to browser about filetype
    header( "Content-type: " . $mimetype[$fileparts [$filepartscount - 1]] . "\n");
    header( "Content-Disposition: inline; filename=$file\n");
}
else
{ 
	//no information about filetype: force a download dialog window in browser
	header( "Content-type: application/octet-stream\n");
	header( "Content-Disposition: inline; filename=$file\n");
}



/**
 * Note that if you use these two headers from a previous example:
 * header('Cache-Control: no-cache, must-revalidate');
 * header('Pragma: no-cache');
 * before sending a file to the browser, the "Open" option on Internet Explorer's file download dialog will not work properly. If the user clicks "Open" instead of "Save," the target application will open an empty file, because the downloaded file was not cached. The user will have to save the file to their hard drive in order to use it. 
 * Make sure to leave these headers out if you'd like your visitors to be able to use IE's "Open" option.
 */
header("Pragma: \n");
header("Cache-Control: \n");
header("Cache-Control: public\n"); // IE cannot download from sessions without a cache

header("Content-Description: " . trim(htmlentities($file)) . "\n");
header("Content-Transfer-Encoding: binary\n");
header("Content-Length: " . filesize( $path)."\n" );


/**
 * ========================================
 * SEND FILE
 * ========================================
 */
$fp = fopen($path, "rb");
fpassthru($fp);
exit( );

?>
