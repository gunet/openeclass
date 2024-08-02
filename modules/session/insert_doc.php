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


require_once 'modules/document/doc_init.php';

doc_init();

/**
 * @brief list documents while inserting them in course session
 */
function list_documents($sid, $cid) {
    global $webDir, $tool_content,
    $group_sql, $langDirectory, $langUp, $langName, $langSize,
    $langDate, $langAddModulesButton, $langChoice,
    $langNoDocuments, $course_code, $langCommonDocs, $pageName;

    $basedir = $webDir . '/courses/' . $course_code . '/document';
    $path = get_dir_path('path');
    $dir_param = get_dir_path('dir');
    $dir_setter = $dir_param ? ('&amp;dir=' . $dir_param) : '';
    $dir_html = $dir_param ? "<input type='hidden' name='dir' value='$dir_param'>" : '';

    if ($cid == -1) {
        $common_docs = true;
        $pageName = $langCommonDocs;
        $group_sql = "course_id = -1 AND subsystem = " . COMMON . "";
        $basedir = $webDir . '/courses/commondocs';
        $visible_sql = 'visible = 1 AND';
    } else {
        $common_docs = false;
        $visible_sql = '';
    }

    // Do not show the same files if these are uploaded as resource in a session.
    $session_sql = '';
    if($sid){
        $session_sql = "AND id NOT IN (SELECT res_id FROM session_resources WHERE session_id = $sid AND doc_id = 0 AND from_user = 0 AND type = 'doc')";
    }

    $result = Database::get()->queryArray("SELECT id, course_id, path, filename, format, title, extra_path, date_modified, visible, copyrighted, comment, IF(title = '', filename, title) AS sort_key FROM document
                                WHERE $group_sql AND $visible_sql
                                      path LIKE ?s AND
                                      path NOT LIKE ?s $session_sql
                                ORDER BY sort_key COLLATE utf8mb4_unicode_ci",
                                "$path/%", "$path/%/%");

    $fileinfo = array();
    $urlbase = $_SERVER['SCRIPT_NAME'] . "?course=$course_code$dir_setter&amp;type=doc&amp;id=$sid&amp;path=";

    foreach ($result as $row) {
        $fullpath = $basedir . $row->path;
        if ($row->extra_path) {
            $size = 0;
        } else {
            $size = file_exists($fullpath)? filesize($fullpath): 0;
        }
        $fileinfo[] = array(
            'id' => $row->id,
            'is_dir' => is_dir($fullpath),
            'size' => $size,
            'title' => $row->title,
            'name' => htmlspecialchars($row->filename),
            'format' => $row->format,
            'path' => $row->path,
            'visible' => $row->visible,
            'comment' => $row->comment,
            'copyrighted' => $row->copyrighted,
            'date' => $row->date_modified,
            'object' => MediaResourceFactory::initFromDocument($row));
    }
    if (count($fileinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoDocuments</span></div></div>";
    } else {
        if (!empty($path)) {
            $dirname = Database::get()->querySingle("SELECT filename FROM document
                                                                   WHERE $group_sql AND path = ?s", $path);
            $parentpath = dirname($path);
            $dirname =  htmlspecialchars($dirname->filename);
            $parentlink = $urlbase . $parentpath;
            $parenthtml = "<span class='float-end'><a href='$parentlink'>$langUp " .
                    icon('fa-level-up') . "</a></span>";
            $colspan = 4;
        }
        $tool_content .= "<form action='resource.php?course=$course_code&session=$sid' method='post'><input type='hidden' name='id' value='$sid' />" .
                "<div class='table-responsive'><table class='table-default'>";
        if( !empty($path)) {
        $tool_content .=
                "<tr>" .
                "<th colspan='$colspan'><div class='text-start'>$langDirectory: $dirname$parenthtml</div></th>" .
                "</tr>" ;
        }
        $tool_content .=
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langName</th>" .
                "<th>$langSize</th>" .
                "<th>$langDate</th>" .
                "</tr></thead>";
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
            foreach ($fileinfo as $entry) {
                if ($entry['is_dir'] != $is_dir) {
                    continue;
                }
                $dir = $entry['path'];
                if ($is_dir) {
                    $image = 'fa-folder-open';
                    $file_url = $urlbase . $dir;
                    $link_text = $entry['name'];

                    $link_href = "<a href='$file_url'>$link_text</a>";
                } else {
                    $image = choose_image('.' . $entry['format']);
                    $file_url = file_url($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code);

                    $dObj = $entry['object'];
                    $dObj->setAccessURL($file_url);
                    $dObj->setPlayURL(file_playurl($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code));

                    $link_href = MultimediaHelper::chooseMediaAhref($dObj);
                }
                if ($entry['visible'] == 'i') {
                    $vis = 'invisible';
                } else {
                    $vis = '';
                }
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td><label class='label-container'><input type='checkbox' name='document[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
                $tool_content .= "<td>" . icon($image, '')."&nbsp;&nbsp;&nbsp;$link_href";

                /* * * comments ** */
                if (!empty($entry['comment'])) {
                    $tool_content .= "<br /><div class='comment'>" .
                            standard_text_escape($entry['comment']) .
                            "</div>";
                }
                $tool_content .= "</td>";
                if ($is_dir) {
                    // skip display of date and time for directories
                    $tool_content .= "<td>&nbsp;</td><td>&nbsp;</td>";
                } else {
                    $size = format_file_size($entry['size']);
                    $date = format_locale_date(strtotime($entry['date']), 'short', false);
                    $tool_content .= "<td>$size</td><td>$date</td>";
                }
                $tool_content .= "</tr>";
                $counter++;
            }
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_doc' value='$langAddModulesButton' /></div>$dir_html</form>";

    }

    return $tool_content;
}


function upload_file($sid){
    global $webDir, $tool_content, $course_code, $langTitle, $langComments, 
           $langSubmit, $langDownloadFile, $is_consultant, $course_id, $langReferencedObject, 
           $uid, $langDescription, $langImgFormsDes;

        $resources = array();
        if(!$is_consultant){
            $badge = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d AND course_id = ?d",$sid,$course_id);
            if($badge){
                $badge_id = $badge->id;
                $resources = Database::get()->queryArray("SELECT * FROM session_resources
                                                            WHERE res_id IN (SELECT resource FROM badge_criterion WHERE badge = ?d AND activity_type = ?s)
                                                            AND doc_id = ?d
                                                            AND session_id = ?d",$badge_id,'document-submit',0,$sid);
            }
        }

        $tool_content .= "  
                            <div class='d-lg-flex gap-4 mt-4'>
                                <div class='flex-grow-1'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form role='form' class='form-horizontal' action='resource.php?course=$course_code&session=$sid' method='post' enctype='multipart/form-data'>
                                            <fieldset>

                                                <input type='hidden' name='id' value='$sid' />

                                                <div class='form-group'>
                                                    <label for='file-upload' class='col-12 control-label-notes'>$langDownloadFile</label>
                                                    <input id='file-upload' type='file' name='file-upload'/>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='title' class='col-12 control-label-notes'>$langTitle</label>
                                                    <div class='col-12'>
                                                        <input id='title' type='text' name='title' class='form-control'>";
                                  $tool_content .= "</div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='comments' class='col-12 control-label-notes'>$langDescription</label>
                                                    " . rich_text_editor('comments', 5, 40, '') . "
                                                </div>";

                                                if(!$is_consultant){
                                                    $tool_content .= "
                                                        <div class='form-group mt-4'>
                                                            <label for='refers_to_resource' class='col-12 control-label-notes'>$langReferencedObject&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                                            <select class='form-select' name='refers_to_resource' id='refers_to_resource'>";
                                                                foreach($resources as $r){
                                                                    $tool_content .= "
                                                                    <option value='$r->res_id'>$r->title</option>";
                                                                }
                                        $tool_content .= "  </select>
                                                        </div>
                                                        <input type='hidden' name='fromUser' value='$uid' />
                                                    ";
                                                }


                              $tool_content .= "<div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                        <input class='btn submitAdminBtn' type='submit' name='submit_upload' value='$langSubmit'>
                                                    </div>
                                                </div>

                                                " . generate_csrf_token_form_field() . "    

                                            </fieldset>
                                        </form>
                                    </div>
                                </div>
                                <div class='d-none d-lg-block'>
                                    <img class='form-image-modules' src='" . get_form_image() . "' alt='$langImgFormsDes'>
                                </div>
                            </div>
                            
                            
                            
                            ";

    return $tool_content;

}