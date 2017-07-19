<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ========================================================================
 */

function zip_offline_directory($zip_filename, $downloadDir) {
    global $public_code;
    $zipfile = new PclZip($zip_filename);
    $v = $zipfile->create($downloadDir, PCLZIP_OPT_REMOVE_PATH, $downloadDir, PCLZIP_OPT_ADD_PATH, $public_code . '-offline');
    if ($v === 0) {
        die("error: " . $zipfile->errorInfo(true));
    }
}

function offline_documents($curDirPath, $curDirName, $bladeData) {
    global $blade, $webDir, $course_id, $course_code, $downloadDir,
           $langDownloadDir, $langSave, $copyright_titles, $copyright_links;

    // doc init
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    mkdir($downloadDir . '/modules/document' . $curDirName);

    $files = $dirs = array();
    $result = Database::get()->queryArray("SELECT id, path, filename, format,
                                        title, extra_path, course_id,
                                        date_modified, public, visible,
                                        editable, copyrighted, comment,
                                        IF((title = '' OR title IS NULL), filename, title) AS sort_key
                                FROM document
                                WHERE 
                                      course_id = ?d AND
                                      path LIKE ?s AND
                                      path NOT LIKE ?s ORDER BY sort_key COLLATE utf8_unicode_ci", $course_id, $curDirPath . "/%", $curDirPath . "/%/%");
    foreach ($result as $row) {
        $is_dir = $row->format == '.dir';
        if ($real_path = common_doc_path($row->extra_path, true)) {
            $path = $real_path;
        } else {
            $path = $basedir . $row->path;
        }
        if (!$real_path and $row->extra_path) {
            // external file
            $size = 0;
        } else {
            $size = file_exists($path) ? filesize($path): 0;
            if (file_exists($path) && !$is_dir) {
                copy($path, $downloadDir . '/modules/document' . $curDirName . '/' . $row->filename);
            }
        }

        $info = array(
            'is_dir' => $is_dir,
            'size' => format_file_size($size),
            'title' => $row->sort_key,
            'filename' => $row->filename,
            'format' => $row->format,
            'path' => $row->path,
            'extra_path' => $row->extra_path,
            'visible' => ($row->visible == 1),
            'public' => $row->public,
            'comment' => $row->comment,
            'date' => nice_format($row->date_modified, true, true),
            'date_time' => nice_format($row->date_modified, true),
            'editable' => $row->editable,
            'updated_message' => '');

        if ($row->extra_path) {
            $info['common_doc_path'] = common_doc_path($row->extra_path); // sets global $common_doc_visible
            $info['common_doc_visible'] = $GLOBALS['common_doc_visible'];
        }

        if (!$row->extra_path or $info['common_doc_path']) { // Normal or common document
            $download_url = $row->filename;
        } else { // External document
            $download_url = $row->extra_path;
        }

        $downloadMessage = $row->format == '.dir' ? $langDownloadDir : $langSave;
        if ($row->format != '.dir') {
            $info['action_button'] = icon('fa-download', $downloadMessage, $download_url);
        }

        $info['copyrighted'] = false;
        if ($is_dir) {
            $info['icon'] = 'fa-folder';
            $info['url'] = $row->filename . "/index.html";
            $newData = $bladeData;
            $newData['urlAppend'] .= '../';
            $newData['template_base'] = $newData['urlAppend'] . 'template/default';
            $newData['themeimg'] = $newData['urlAppend'] . 'template/default/img';
            $newData['logo_img'] = $newData['themeimg'] . '/eclass-new-logo.png';
            $newData['logo_img_small'] = $newData['themeimg'] . '/logo_eclass_small.png';
            $newData['toolArr'] = lessonToolsMenu_offline(true, $newData['urlAppend']);
            offline_documents($row->path, $curDirName . '/' . $row->filename, $newData);

            $dirs[] = (object) $info;
        } else {
            $info['icon'] = choose_image('.' . $row->format);
            $GLOBALS['group_sql'] = "course_id = $course_id AND subsystem = " . MAIN;
            $info['url'] = file_url($row->path, $row->filename);
            $info['link'] = "<a href='$download_url' title='".q($row->filename)."'>" . $row->filename . "</a>";

            $copyid = $row->copyrighted;
            if ($copyid and $copyid != 2) {
                $info['copyrighted'] = true;
                $info['copyright_icon'] = ($copyid == 1) ? 'fa-copyright' : 'fa-cc';
                $info['copyright_title'] = $copyright_titles[$copyid];
                $info['copyright_link'] = $copyright_links[$copyid];
            }

            $files[] = (object) $info;
        }
    }
    $bladeData['fileInfo'] = array_merge($dirs, $files);
    $docout = $blade->view()->make('modules.document.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/document' . $curDirName . '/index.html', 'w');
    fwrite($fp, $docout);
    fclose($fp);
}