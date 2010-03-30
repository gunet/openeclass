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
	forcedownload.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
    @Description: Support functions used by document, document_upgrade, video.php

    This script contains the function that forces the web browser to donwload
    a file instead of just opening it. This happens for security reasons.
    
    The function's arguments go as follow:
    
    $real_filename : the real path and filename that the file uses in the
    actual filesystem
    
    $filename : the filename the user sees in his browser (in the download file
    window)

    $send_inline : if true, sends file with Content-Disposition: inline, else
    as attachment, for certain MIME types
    
    If the file does not exist the function returns FALSE
    
==============================================================================*/

function send_file_to_client($real_filename, $filename, $send_inline = false, $send_name = false)
{
        if(!file_exists($real_filename))
        {
                return false;
        }
        $content_type = get_mime_type($filename);

        if ($content_type == 'text/html') {
                $charset = '; charset=' . html_charset($real_filename);
        } elseif ($content_type == 'text/plain') {
                $charset = '; charset=' . text_charset($real_filename);
        } else {
                $charset = '';
        }
        
        $disposition = $send_inline? 'inline': 'attachment';
	if ($send_name) {
                // Add quotes to filename if it contains spaces
                if (strpos($filename, ' ') !== false) {
                        $filename = '"' . $filename . '"';
                }
                $filenameattr = '; filename=' . $filename;
	} else {
                $filenameattr = '';
        }
        header("Content-type: $content_type$charset");
        header("Content-Disposition: $disposition$filenameattr");

        // IE cannot download from sessions without a cache
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        {
                header('Pragma: public');
                header('Cache-Control: no-store, no-cache, no-transform, must-revalidate, private');
                header('Expires: 0');
        }

        header('Content-length: ' . filesize($real_filename));
        $fp = fopen($real_filename, 'r');
        fpassthru($fp);
        return true;
}


function get_file_extension($filename)
{
	$matches = array();
	if (preg_match('/\.(tar\.(z|gz|bz|bz2))$/i', $filename, $matches)) {
                return strtolower($matches[1]);
        } elseif (preg_match('/\.([a-zA-Z0-9_-]{1,8})$/i', $filename, $matches)) {
		return strtolower($matches[1]);
	} else {
		return '';
	}
}


function get_mime_type($filename)
{
        $f=array(
                'manifest' => 'application/manifest',
                'xaml' => 'application/xaml+xml',
                'application' => 'application/x-ms-application',
                'deploy' => 'application/octet-stream',
                'xbap' => 'application/x-ms-xbap',
                'docm' => 'application/vnd.ms-word.document.macroEnabled.12', 
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                'dotm' => 'application/vnd.ms-word.template.macroEnabled.12', 
                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 
                'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12', 
                'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template', 
                'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12', 
                'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12', 
                'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 
                'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
                'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12', 
                'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 
                'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12', 
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12', 
                'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'vsd' => 'application/vnd.visio',
                'vss' => 'application/vnd.visio',
                'vst' => 'application/vnd.visio',
                'vsw' => 'application/vnd.visio',
                'doc' => 'application/msword',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pps' => 'application/vnd.ms-pps',
                'ez' => 'application/andrew-inset',
                'hqx' => 'application/mac-binhex40',
                'cpt' => 'application/mac-compactpro',
                'bin' => 'application/octet-stream',
                'dms' => 'application/octet-stream',
                'lha' => 'application/octet-stream',
                'lzh' => 'application/octet-stream',
                'exe' => 'application/octet-stream',
                'class' => 'application/octet-stream',
                'so' => 'application/octet-stream',
                'dll' => 'application/octet-stream',
                'oda' => 'application/oda',
                'pdf' => 'application/pdf',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',
                'smi' => 'application/smil',
                'smil' => 'application/smil',
                'wbxml' => 'application/vnd.wap.wbxml',
                'wmlc' => 'application/vnd.wap.wmlc',
                'wmlsc' => 'application/vnd.wap.wmlscriptc',
                'bcpio' => 'application/x-bcpio',
                'vcd' => 'application/x-cdlink',
                'pgn' => 'application/x-chess-pgn',
                'cpio' => 'application/x-cpio',
                'csh' => 'application/x-csh',
                'dcr' => 'application/x-director',
                'dir' => 'application/x-director',
                'dxr' => 'application/x-director',
                'dvi' => 'application/x-dvi',
                'spl' => 'application/x-futuresplash',
                'gtar' => 'application/x-gtar',
                'hdf' => 'application/x-hdf',
                'js' => 'application/x-javascript',
                'skp' => 'application/x-koan',
                'skd' => 'application/x-koan',
                'skt' => 'application/x-koan',
                'skm' => 'application/x-koan',
                'latex' => 'application/x-latex',
                'nc' => 'application/x-netcdf',
                'cdf' => 'application/x-netcdf',
                'sh' => 'application/x-sh',
                'shar' => 'application/x-shar',
                'swf' => 'application/x-shockwave-flash',
                'sit' => 'application/x-stuffit',
                'sv4cpio' => 'application/x-sv4cpio',
                'sv4crc' => 'application/x-sv4crc',
                'tar' => 'application/x-tar',
                'tcl' => 'application/x-tcl',
                'tex' => 'application/x-tex',
                'texinfo' => 'application/x-texinfo',
                'texi' => 'application/x-texinfo',
                't' => 'application/x-troff',
                'tr' => 'application/x-troff',
                'roff' => 'application/x-troff',
                'man' => 'application/x-troff-man',
                'me' => 'application/x-troff-me',
                'ms' => 'application/x-troff-ms',
                'ustar' => 'application/x-ustar',
                'src' => 'application/x-wais-source',
                'xhtml' => 'application/xhtml+xml',
                'xht' => 'application/xhtml+xml',
                'zip' => 'application/zip',
                'au' => 'audio/basic',
                'snd' => 'audio/basic',
                'mid' => 'audio/midi',
                'midi' => 'audio/midi',
                'kar' => 'audio/midi',
                'mpga' => 'audio/mpeg',
                'mp2' => 'audio/mpeg',
                'mp3' => 'audio/mpeg',
                'aif' => 'audio/x-aiff',
                'aiff' => 'audio/x-aiff',
                'aifc' => 'audio/x-aiff',
                'm3u' => 'audio/x-mpegurl',
                'ram' => 'audio/x-pn-realaudio',
                'rm' => 'audio/x-pn-realaudio',
                'rpm' => 'audio/x-pn-realaudio-plugin',
                'ra' => 'audio/x-realaudio',
                'wav' => 'audio/x-wav',
                'pdb' => 'chemical/x-pdb',
                'xyz' => 'chemical/x-xyz',
                'svg' => 'image/svg+xml',
                'bmp' => 'image/bmp',
                'gif' => 'image/gif',
                'ief' => 'image/ief',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'jpe' => 'image/jpeg',
                'png' => 'image/png',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'djvu' => 'image/vnd.djvu',
                'djv' => 'image/vnd.djvu',
                'wbmp' => 'image/vnd.wap.wbmp',
                'ras' => 'image/x-cmu-raster',
                'pnm' => 'image/x-portable-anymap',
                'pbm' => 'image/x-portable-bitmap',
                'pgm' => 'image/x-portable-graymap',
                'ppm' => 'image/x-portable-pixmap',
                'rgb' => 'image/x-rgb',
                'xbm' => 'image/x-xbitmap',
                'xpm' => 'image/x-xpixmap',
                'xwd' => 'image/x-windowdump',
                'ico' => 'image/x-icon',
                'igs' => 'model/iges',
                'iges' => 'model/iges',
                'msh' => 'model/mesh',
                'mesh' => 'model/mesh',
                'silo' => 'model/mesh',
                'wrl' => 'model/vrml',
                'vrml' => 'model/vrml',
                'css' => 'text/css',
                'htm' => 'text/html',
                'html' => 'text/html',
                'asc' => 'text/plain',
                'txt' => 'text/plain',
                'rtx' => 'text/richtext',
                'rtf' => 'text/rtf',
                'sgml' => 'text/sgml',
                'sgm' => 'text/sgml',
                'tsv' => 'text/tab-seperated-values',
                'wml' => 'text/vnd.wap.wml',
                'wmls' => 'text/vnd.wap.wmlscript',
                'etx' => 'text/x-setext',
                'xml' => 'text/xml',
                'xsl' => 'text/xml',
                'mpeg' => 'video/mpeg',
                'mpg' => 'video/mpeg',
                'mpe' => 'video/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',
                'mxu' => 'video/vnd.mpegurl',
                'avi' => 'video/x-msvideo',
                'movie' => 'video/x-sgi-movie',
                'ice' => 'x-conference-xcooltalk',
                'asx' => 'video/x-ms-asf',
                'wma' => 'audio/x-ms-wma',
                'wax' => 'audio/x-ms-wax',
                'wmv' => 'video/x-ms-wmv',
                'wvx' => 'video/x-ms-wvx',
                'wm' => 'video/x-ms-wm',
                'wmx' => 'video/x-ms-wmx',
                'wmz' => 'application/x-ms-wmz',
                'wmd' => 'application/x-ms-wmd');
        $ext = get_file_extension($filename);
        if (isset($f[$ext])) {
                return $f[$ext];
        } else {
                return "application/octet-stream";
        }
}


function html_charset($filename)
{
        $f = fopen($filename, 'r');
        $contents = fread($f, 2048);
        fclose($f);
        if (preg_match('#text/\w+;\scharset=([^\'"]+)"#i', $contents, $matches)) {
                $matches[1];
        } else {
                return mb_detect_encoding($contents, 'UTF-8,ISO-8859-7,ISO-8859-1');
        }
}


function text_charset($filename)
{
        $f = fopen($filename, 'r');
        $contents = fread($f, 2048);
        fclose($f);
        return mb_detect_encoding($contents, 'ASCII,UTF-8,ISO-8859-7,ISO-8859-1');
}
