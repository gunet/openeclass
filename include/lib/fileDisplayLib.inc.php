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

/* ==========================================================================
	fileDisplayLib.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.3 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: fileDisplayLib.inc.php Revision: 1.2
	      
	Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
                      Hugues Peeters    <peeters@ipm.ucl.ac.be>
                      Christophe Gesche <gesche@ipm.ucl.ac.be>
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
*/

/*****************************************
   GENERIC FUNCTION :STRIP SUBMIT VALUE
*****************************************/

function stripSubmitValue(&$submitArray)
{
	while($array_element = each($submitArray))
	{
		$name = $array_element['key'] ;
		$GLOBALS[$name] = stripslashes ( $GLOBALS [$name] ) ;
		$GLOBALS[$name] = str_replace ("\"", "'", $GLOBALS [$name] ) ;
	}
}


/*
 * Define the image to display for each file extension
 * This needs an existing image repository to works
 *
 * @author - Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) - name of a file
 * @retrun - the gif image to chose
 */

function choose_image($fileName)
{
	static $type, $image;

	/*** Tables initiliasation ***/
	if (!$type || !$image)
	{
		$type['word'      ] = array('doc', 'dot', 'rtf', 'mcw', 'wps', 'docx');
		$type['web'       ] = array('htm', 'html', 'htx', 'xml', 'xsl', 'php', 'phps');
		$type['css'       ] = array('css');
		$type['image'     ] = array('gif', 'jpg', 'png', 'bmp', 'jpeg');
		$type['audio'     ] = array('wav', 'mp2', 'mp3', 'mp4', 'vqf');
		$type['midi'      ] = array('midi', 'mid');
		$type['video'     ] = array('avi', 'mpg', 'mpeg', 'mov', 'divx', 'wmv', 'asf', 'asx');
		$type['real'      ] = array('ram', 'rm');
		$type['flash'     ] = array('swf', 'flv');
		$type['excel'     ] = array('xls', 'xlt', 'xlsx');
		$type['compressed'] = array('zip', 'tar', 'gz', 'bz2', 'tar.gz', 'tar.bz2', '7z');
		$type['rar'       ] = array('rar');
		$type['code'      ] = array('js', 'cpp', 'c', 'java');
		$type['acrobat'   ] = array('pdf');
		$type['powerpoint'] = array('ppt', 'pptx', 'pps', 'ppsx');
		$type['text'      ] = array('txt');

		$image['word'      ] = 'doc.gif';
		$image['web'       ] = 'html.gif';
		$image['css'       ] = 'css.gif';
		$image['image'     ] = 'gif.gif';
		$image['audio'     ] = 'wav.gif';
		$image['midi'      ] = 'midi.gif';
		$image['video'     ] = 'mpg.gif';
		$image['rar'       ] = 'ram.gif';
		$image['flash'     ] = 'flash.gif';
		$image['excel'     ] = 'xls.gif';
		$image['compressed'] = 'zip.gif';
		$image['rar'       ] = 'rar.gif';
		$image['code'      ] = 'js.gif';
		$image['acrobat'   ] = 'pdf.gif';
		$image['powerpoint'] = 'ppt.gif';
		$image['text'      ] = 'txt.gif';

	}

	/*** function core ***/
	if (preg_match('/\.([[:alnum:]]+)$/', $fileName, $extension))
	{
		$ext = strtolower($extension[1]);
		foreach( $type as $genericType => $typeList)
		{
			if (in_array($ext, $typeList))
			{
				return $image[$genericType];
			}
		}
	}

	return "default.gif";
}


/*
 * Transform the file size in a human readable format
 * 
 * @author - ???
 * @param  - fileSize (int) - size of the file in bytes
 */

function format_file_size($fileSize)
{
	if($fileSize >= 1073741824)
	{
		$fileSize = round($fileSize / 1073741824 * 100) / 100 . " GB";
	}
	elseif($fileSize >= 1048576)
	{
		$fileSize = round($fileSize / 1048576 * 100) / 100 . " MB";
	}
	elseif($fileSize >= 1024)
	{
		$fileSize = round($fileSize / 1024 * 100) / 100 . " KB";
	}
	else
	{
		$fileSize = $fileSize . " B";
	}

	return $fileSize;
}


/*
 * Transform a UNIX time stamp in human readable format date
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param - date - UNIX time stamp 
 */

function format_date($fileDate)
{
	return date("d.m.Y", $fileDate);
}


/*
 * Transform the file path in a url
 * 
 * @param - filePaht (string) - relative local path of the file on the Hard disk
 * @return - relative url
 */

function format_url($filePath)
{
	$stringArray = explode("/", $filePath);

	for ($i = 0; $i < sizeof($stringArray); $i++)
	{
		$stringArray[$i] = rawurlencode($stringArray[$i]);
	}

	return implode("/",$stringArray);
}

function file_url_escape($name)
{
        return str_replace(array('%2F', '%2f'),
                           array('//', '//'),
                           rawurlencode($name));
}

function file_url($path, $filename = null)
{
	global $currentCourseID, $urlServer;
	static $oldpath = '', $dirname;

	$dirpath = dirname($path);
	if ($dirpath == '/') {
		$oldpath = $dirpath = '';
		$dirname = '';
	} else {
		if ($dirpath != $oldpath) {
			$components = explode('/', $dirpath);
			array_shift($components);
			$partial_path = '';
			$dirname = '';
			foreach ($components as $c) {
				$partial_path .= '/' . $c;
				$q = db_query("SELECT filename FROM document WHERE path = '$partial_path'",
					      $currentCourseID);
				list($name) = mysql_fetch_row($q);
				$dirname .= '/' . file_url_escape($name);
			}
		}
        }
        if (!isset($filename)) {
                $q = db_query("SELECT filename FROM document WHERE path = '$path'");
                list($filename) = mysql_fetch_row($q);
        }
	return htmlspecialchars($urlServer . "modules/document/file.php/$currentCourseID$dirname/" . file_url_escape($filename), ENT_QUOTES);
}
