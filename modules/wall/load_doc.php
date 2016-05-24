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

$require_current_course = TRUE;

require_once '../../include/baseTheme.php';

header('Content-Type: application/json');

$data = array();

//variables initialization
if (isset($_GET['subsystem']) AND $_GET['subsystem'] == 'mydocs') { //MY DOCUMENTS
    $subsystem = MYDOCS;
    $sql = "subsystem = $subsystem AND subsystem_id = $uid";
} else { //MAIN DOCS
    $subsystem = MAIN;
    $sql = "course_id = $course_id AND subsystem = $subsystem";
}

if (isset($_GET['id'])) {
    if (isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
    }
    
    if ($_GET['id'] == '#') {
        $path = '';
    } else {
        $id = intval($_GET['id']);
        $result = Database::get()->querySingle("SELECT path FROM document WHERE $sql AND id = ?d", $id);
        $path = $result->path;
    }
        
    $result = Database::get()->queryArray("SELECT id, path, filename, format, title, visible, IF(title = '', filename, title) AS sort_key FROM document
            WHERE $sql AND
            visible = ?d AND
            path LIKE ?s AND
            path NOT LIKE ?s
            ORDER BY sort_key COLLATE utf8_unicode_ci",
            1, "$path/%", "$path/%/%");
    
    foreach ($result as $row) {
        $text = (empty($row->title))? $row->filename : $row->title;
        
        if ($row->format == '.dir') {
            $data[] = array('id'=> $row->id, 'text' => $text, 'children' => true, 'state' => array('opened' => true), 'type' => 'folder');
        } else {
            if (isset($post_id)) {
                $selected = Database::get()->querySingle("SELECT COUNT(*) as cnt FROM wall_post_resources WHERE post_id=?d AND res_id=?d AND type=?s", $post_id, $row->id, 'document');
                if ($selected->cnt > 0) {
                    $sel_arr = array('selected' => true);
                } else {
                    $sel_arr = array('selected' => false);
                }
            } else {
                $sel_arr = array('selected' => false);
            }
            $data[] = array('id'=> $row->id, 'text' => $text, 'type' => 'file', 'state' => $sel_arr);
        }
    }
}

echo json_encode($data);
