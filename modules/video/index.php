<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 * @abstract upload and display multimedia files
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Video';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/drives/clouddrive.php';
require_once 'include/action.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'inc/delos_functions.php'; // required by view
require_once 'inc/video_functions.php';

$action = new action();
$action->record(MODULE_ID_VIDEO);

$toolName = $langVideo;
$data = array();

// common data for tinymce embedding, custom filtering, sorting, etc..
$is_in_tinymce = $data['is_in_tinymce'] = isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce';
$data['menuTypeID'] = $is_in_tinymce ? 5 : 2;
list($filterv, $filterl, $compatiblePlugin) = isset($_REQUEST['docsfilter']) 
        ? select_proper_filters($_REQUEST['docsfilter']) 
        : array('WHERE true', 'WHERE true', true);
$data['filterv'] = $filterv;
$data['filterl'] = $filterl;
$data['compatiblePlugin'] = $compatiblePlugin;

// custom sort
$order = 'ORDER BY title';
$sort = "title";
$reverse = false;
if (isset($_GET['sort']) && $_GET['sort'] === 'date') {
    $order = 'ORDER BY date';
    $sort = "date";
}
if (isset($_GET['rev'])) {
    $order .= ' DESC';
    $reverse = true;
}
$data['order'] = $order;

if ($is_editor && !$is_in_tinymce) { // admin actions

    // visibility commands
    if (isset($_GET['vis'])) {
        $table = select_table($_GET['table']);
        Database::get()->query("UPDATE $table SET visible = ?d WHERE id = ?d", $_GET['vis'], $_GET['vid']);
        Session::Messages($langViMod, "alert-success");
    }

    // Public accessibility commands
    if (isset($_GET['public']) or isset($_GET['limited'])) {
        $new_public_status = isset($_GET['public']) ? 1 : 0;
        $table = select_table($_GET['table']);
        Database::get()->query("UPDATE $table SET public = ?d WHERE id = ?d", $new_public_status, $_GET['vid']);
        Session::Messages($langViMod, "alert-success");
    }
    
    if (isset($_GET['delete'])) {
        if ($_GET['delete'] == 'delcat') { // delete video category
            // delete category videos
            $q1 = Database::get()->queryArray("SELECT id FROM video WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
            foreach ($q1 as $a) {
                delete_video($a->id, 'video', $course_id, $course_code, $webDir);
            }
            
            // delete category videolinks
            $q2 = Database::get()->queryArray("SELECT id FROM videolink WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
            foreach ($q2 as $a) {
                delete_video($a->id, 'videolink', $course_id, $course_code, $webDir);
            }
            
            // delete category
            delete_video_category($_GET['id']);
            
        } else { // delete video / videolink
            $table = select_table($_GET['table']);
            delete_video($_GET['id'], $table, $course_id, $course_code, $webDir);
        }
        
        Session::Messages($langGlossaryDeleted, "alert-success");
    }
} // end of admin actions

// list data    
$data['count_video'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM video $filterv AND course_id = ?d ORDER BY title", $course_id)->count;
$data['count_video_links'] = Database::get()->querySingle("SELECT count(*) AS count FROM videolink $filterl AND course_id = ?d ORDER BY title", $course_id)->count;
$data['num_of_categories'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `video_category` WHERE course_id = ?d", $course_id)->count;
$data['items'] = getLinksOfCategory(0, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin); // uncategorized items
$data['categories'] = Database::get()->queryArray("SELECT * FROM `video_category` WHERE course_id = ?d ORDER BY name", $course_id);

// js and view
if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    load_js('jquery-' . JQUERY_VERSION . '.min');
    load_js('tinymce.popup.urlgrabber.min.js');
}
add_units_navigation(TRUE); // TODO: test
ModalBoxHelper::loadModalBox(true);    
view('modules.video.index', $data);
