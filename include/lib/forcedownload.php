<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/* ===========================================================================
  forcedownload.php
  @last update: 18-07-2006 by Sakis Agorastos
  @authors list: Agorastos Sakis <th_agorastos@hotmail.com>
  ==============================================================================
  @Description: Support functions used by document, document_upgrade, video/index.php

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

  ============================================================================== */

function send_file_to_client($real_filename, $filename, $disposition = null, $send_name = false, $delete = false) {
    if (!file_exists($real_filename)) {
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
    if ($send_name) {
        if (preg_match('/[^\x20-\x7E]/', $filename) and
                strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            $filename = urlencode($filename);
        }
        // Add quotes to filename if it contains spaces
        if (strpos($filename, ' ') !== false) {
            $filename = '"' . $filename . '"';
        }
        $filenameattr = '; filename=' . $filename;
        if (!isset($disposition)) {
            $disposition = 'attachment';
        }
    } else {
        $filenameattr = '';
    }
    header("Content-type: $content_type$charset");
    if (isset($disposition)) {
        header("Content-Disposition: $disposition$filenameattr");
    }

    header('Pragma:');
    header('Cache-Control: public');

    $mtime = filemtime($real_filename);
    $mdate = gmdate('D, d M Y H:i:s', $mtime);
    $etag = md5($real_filename . $mdate . $filename . filesize($real_filename));
    header('Last-Modified: ' . $mdate . ' GMT');
    header("Etag: $etag");

    if ((array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) and
            strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'])) >= $mtime) or
            (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) and
            trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)) {
        header("HTTP/1.0 304 Not Modified");
    } else {
        stop_output_buffering();
        if ($delete) {
            register_shutdown_function('unlink', $real_filename);
        }
        
        $size = filesize($real_filename);
        
        if(isset($_SERVER['HTTP_RANGE'])) {
            // Parse the range header to get the byte offset
            $ranges = array_map(
                'intval', // Parse the parts into integer
                explode(
                    '-', // The range separator
                    substr($_SERVER['HTTP_RANGE'], 6) // Skip the `bytes=` part of the header
                )
            );
            
            // If the last range param is empty, it means EOF
            if (!$ranges[1]) {
                $ranges[1] = $size - 1;
            }
            
            // Send the appropriate headers
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . ($ranges[1] - $ranges[0])); // The size of the range
            
            // Send the ranges we offered
            header(
                sprintf(
                    'Content-Range: bytes %d-%d/%d', // The header format
                    $ranges[0], // The start range
                    $ranges[1], // The end range
                    $size // Total size of the file
                )
            );
            
            $f = fopen($real_filename, 'rb'); // binary file output
            $chunkSize = 8192;
            fseek($f, $ranges[0]); // Seek to the requested start range
            
            // Data Output
            while (true) {
                // Check if we have outputted all the data requested
                if (ftell($f) >= $ranges[1]) {
                    break;
                }

                echo fread($f, $chunkSize);

                // (Optional) flush
                //@ob_flush();
                //flush();
            }
        } else {
            header('Content-length: ' . $size);
            readfile($real_filename);
        }
    }

    return true;
}

function get_mime_type($filename) {
    $f = array(
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
        'wmd' => 'application/x-ms-wmd',
        'mp4' => 'video/mp4',
        'flv' => 'video/x-flv',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg');
    $ext = get_file_extension($filename);
    if (isset($f[$ext])) {
        return $f[$ext];
    } else {
        return "application/octet-stream";
    }
}

function html_charset($filename) {
    $f = fopen($filename, 'r');
    $contents = fread($f, 2048);
    fclose($f);
    if (preg_match('#text/\w+;\scharset=([^\'"]+)"#i', $contents, $matches)) {
        $matches[1];
    } else {
        return mb_detect_encoding($contents, 'UTF-8,ISO-8859-7,ISO-8859-1');
    }
}

function text_charset($filename) {
    $f = fopen($filename, 'r');
    $contents = fread($f, 2048);
    fclose($f);
    return mb_detect_encoding($contents, 'ASCII,UTF-8,ISO-8859-7,ISO-8859-1');
}

function public_path_to_disk_path($path_components, $path = '') {
    global $group_sql;

    $depth = substr_count($path, '/') + 1;
    foreach ($path_components as $component) {
        $component = urldecode(str_replace(chr(1), '/', $component));
        $r = Database::get()->querySingle("SELECT path, visible, public, format, extra_path,
                                      (LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) AS depth
                                      FROM document
                                      WHERE $group_sql AND
                                            filename = ?s AND
                                            path LIKE '$path%' HAVING depth = $depth", $component);
        if (!$r) {
            not_found('/' . implode('/', $path_components));
        }        
        $path = $r->path;
        $depth++;
    }

    if (!preg_match("/\.$r->format$/", $component)) {
        $component .= '.' . $r->format;
    }
    $r->filename = $component;

    return $r;
}

function not_found($path) {
    header("HTTP/1.0 404 Not Found");
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
    '<title>404 Not Found</title></head><body>',
    '<h1>Not Found</h1><p>The requested path "',
    htmlspecialchars($path),
    '" was not found.</p></body></html>';
    exit;
}

function error($message) {
    global $urlServer;
    
    Session::Messages($message, 'alert-danger');
    session_write_close();

    header("Location: $urlServer");

    exit;
}
