<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

/**
 * @brief strip submit value
 * @param type $submitArray
 */
function stripSubmitValue(&$submitArray) {
    while ($array_element = each($submitArray)) {
        $name = $array_element['key'];
        $GLOBALS[$name] = stripslashes($GLOBALS [$name]);
        $GLOBALS[$name] = str_replace("\"", "'", $GLOBALS [$name]);
    }
}

 /**
 * @brief  Define the image to display for each file extension
  * This needs an existing image repository to work
 * @author - Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) - name of a file
 * @return - image filename
 */
function choose_image($fileName) {
    static $type, $image;

    // Table initialisation
    if (!$type || !$image) {
        $type = array(
            'word' => array('doc', 'dot', 'rtf', 'mcw', 'wps', 'docx'),
            'web' => array('htm', 'html', 'htx', 'xml', 'xsl', 'php', 'phps', 'meta'),
            'css' => array('css'),
            'image' => array('gif', 'jpg', 'png', 'bmp', 'jpeg', 'tif', 'tiff'),
            'audio' => array('wav', 'mp2', 'mp3', 'mp4', 'vqf'),
            'midi' => array('midi', 'mid'),
            'video' => array('avi', 'mpg', 'mpeg', 'mov', 'divx', 'wmv', 'asf', 'asx'),
            'real' => array('ram', 'rm'),
            'flash' => array('swf', 'flv'),
            'excel' => array('xls', 'xlt', 'xlsx'),
            'compressed' => array('zip', 'tar', 'gz', 'bz2', 'tar.gz', 'tar.bz2', '7z'),
            'rar' => array('rar'),
            'code' => array('js', 'cpp', 'c', 'java'),
            'acrobat' => array('pdf'),
            'powerpoint' => array('ppt', 'pptx', 'pps', 'ppsx'),
            'text' => array('txt'),
        );

        $image = array(
            'word' => 'fa-file-word-o',
            'web' => 'fa-file-code-o',
            'css' => 'fa-file-code-o',
            'image' => 'fa-file-image-o',
            'audio' => 'fa-file-audio-o',
            'midi' => 'fa-file-audio-o',
            'video' => 'fa-file-video-o',
            'ram' => 'fa-file-audio-o',
            'flash' => 'fa-bolt',
            'excel' => 'fa-file-excel-o',
            'compressed' => 'fa-file-archive-o',
            'rar' => 'fa-file-archive-o',
            'code' => 'fa-file-code-o',
            'acrobat' => 'fa-file-pdf-o',
            'powerpoint' => 'fa-file-powerpoint-o',
            'text' => 'fa-file-text-o',
        );
    }

    // function core
    if (preg_match('/\.([[:alnum:]]+)$/', $fileName, $extension)) {
        $ext = strtolower($extension[1]);
        foreach ($type as $genericType => $typeList) {
            if (in_array($ext, $typeList)) {
                return $image[$genericType];
            }
        }
    }

    return 'fa-file';
}

/**
 * @brief Transform the file size in a human readable format
 * @param type $fileSize
 * @return string
 */
function format_file_size($fileSize) {
    if ($fileSize >= 1073741824) {
        $fileSize = round($fileSize / 1073741824 * 100) / 100 . " GB";
    } elseif ($fileSize >= 1048576) {
        $fileSize = round($fileSize / 1048576 * 100) / 100 . " MB";
    } elseif ($fileSize >= 1024) {
        $fileSize = round($fileSize / 1024 * 100) / 100 . " KB";
    } else {
        $fileSize = $fileSize . " B";
    }

    return $fileSize;
}

/**
 * @brief Transform the file path in a url
 * @param type $filePath
 * @return type
 */
function format_url($filePath) {
    $stringArray = explode("/", $filePath);

    for ($i = 0; $i < sizeof($stringArray); $i++) {
        $stringArray[$i] = rawurlencode($stringArray[$i]);
    }

    return implode("/", $stringArray);
}

function file_url_escape($name) {
    return str_replace(array('%2F', '%2f'), array('//', '//'), rawurlencode($name));
}

function public_file_path($disk_path, $filename = null) {
    global $group_sql;
    static $seen_paths;
    $dirpath = dirname($disk_path);        
    
    if ($dirpath == '/') {
        $dirname = '';
    } else {
        if (!isset($seen_paths[$disk_path])) {
            $components = explode('/', $dirpath);
            array_shift($components);
            $partial_path = '';
            $dirname = '';
            foreach ($components as $c) {
                $partial_path .= '/' . $c;
                if (!isset($seen_paths[$partial_path])) {
                    $name = Database::get()->querySingle("SELECT filename FROM document
                                                                       WHERE $group_sql AND
                                                                             path = ?s", $partial_path)->filename;                    
                    $dirname .= '/' . file_url_escape($name);
                    $seen_paths[$partial_path] = $dirname;
                } else {
                    $dirname = $seen_paths[$partial_path];
                }
            }
        } else {
            $dirname = $seen_paths[$partial_path];
        }
    }
    if (!isset($filename)) {
        $filename = Database::get()->querySingle("SELECT filename FROM document
                                               WHERE $group_sql AND
                                                     path = ?s", $disk_path)->filename;
    }
    return $dirname . '/' . file_url_escape($filename);
}

/**
 * @brief Generate download URL for documents
 * @global type $course_code
 * @global type $urlServer
 * @global type $group_id
 * @global type $ebook_id
 * @param type $path
 * @param type $filename
 * @param type $courseCode
 * @return type
 */
function file_url($path, $filename = null, $courseCode = null) {
    global $course_code, $urlServer, $group_id, $ebook_id, $uid;
    $courseCode = ($courseCode == null) ? $course_code : $courseCode;

    if (defined('EBOOK_DOCUMENTS')) {
        return htmlspecialchars($urlServer .
                "modules/ebook/show.php/$courseCode/$ebook_id/_" .
                public_file_path($path, $filename), ENT_QUOTES);
    } else {
        if (defined('COMMON_DOCUMENTS')) {
            $courseCode = 'common';
            $gid = '';
        } elseif (defined('MY_DOCUMENTS')) {
            $courseCode = 'user';
            $gid = ",$uid";
        } elseif (defined('GROUP_DOCUMENTS')) {
            $gid = ",$group_id";
        } else {
            $gid = '';
        }
        return htmlspecialchars($urlServer .
                "modules/document/file.php/$courseCode$gid" .
                public_file_path($path, $filename), ENT_QUOTES);
    }
}

/**
 * @global type $course_code
 * @global type $urlServer
 * @global type $group_id
 * @global type $ebook_id
 * @param type $path
 * @param type $filename
 * @param type $courseCode
 * @return type
 */
function file_playurl($path, $filename = null, $courseCode = null) {
    global $course_code, $urlServer, $group_id, $ebook_id;
    $courseCode = ($courseCode == null) ? $course_code : $courseCode;

    if (defined('EBOOK_DOCUMENTS')) {
        return htmlspecialchars($urlServer .
                "modules/ebook/play.php/$courseCode/$ebook_id/_" .
                public_file_path($path, $filename), ENT_QUOTES);
    } else {
        $gid = defined('GROUP_DOCUMENTS') ? ",$group_id" : '';
        if (defined('COMMON_DOCUMENTS'))
            $courseCode = 'common';

        return htmlspecialchars($urlServer .
                "modules/document/play.php/$courseCode$gid" .
                public_file_path($path, $filename), ENT_QUOTES);
    }
}

/**
 * @brief Initialize copyright/license global arrays for documents
 * @global type $language
 */
function copyright_info_init() {
    global $language;

    if ($language != 'en') {
        $link_suffix = 'deed.' . $language;
    } else {
        $link_suffix = '';
    }

    $GLOBALS['copyright_icons'] = array(
        '0' => '',
        '2' => '',
        '1' => 'copyrighted',
        '3' => 'cc/by',
        '4' => 'cc/by-sa',
        '5' => 'cc/by-nd',
        '6' => 'cc/by-nc',
        '7' => 'cc/by-nc-sa',
        '8' => 'cc/by-nc-nd');

    $GLOBALS['copyright_titles'] = array(
        '0' => $GLOBALS['langCopyrightedUnknown'],
        '2' => $GLOBALS['langCopyrightedFree'],
        '1' => $GLOBALS['langCopyrightedNotFree'],
        '3' => $GLOBALS['langCreativeCommonsCCBY'],
        '4' => $GLOBALS['langCreativeCommonsCCBYSA'],
        '5' => $GLOBALS['langCreativeCommonsCCBYND'],
        '6' => $GLOBALS['langCreativeCommonsCCBYNC'],
        '7' => $GLOBALS['langCreativeCommonsCCBYNCSA'],
        '8' => $GLOBALS['langCreativeCommonsCCBYNCND']);

    $GLOBALS['copyright_links'] = array(
        '0' => null,
        '2' => null,
        '1' => null,
        '3' => 'http://creativecommons.org/licenses/by/3.0/' . $link_suffix,
        '4' => 'http://creativecommons.org/licenses/by-sa/3.0/' . $link_suffix,
        '5' => 'http://creativecommons.org/licenses/by-nd/3.0/' . $link_suffix,
        '6' => 'http://creativecommons.org/licenses/by-nc/3.0/' . $link_suffix,
        '7' => 'http://creativecommons.org/licenses/by-nc-sa/3.0/' . $link_suffix,
        '8' => 'http://creativecommons.org/licenses/by-nc-nd/3.0/' . $link_suffix);
}