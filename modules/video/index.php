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
 * @abstract upload and display multimedia files
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'video';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/drives/clouddrive.php';

require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_VIDEO);

require_once 'include/lib/forcedownload.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
require_once 'video_functions.php';
require_once 'delos_functions.php';

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
$sort = 'title';
$reverse = false;
if (isset($_GET['sort']) && $_GET['sort'] === 'date') {
    $order = 'ORDER BY date';
    $sort = 'date';
}
if (isset($_GET['rev'])) {
    $order .= ' DESC';
    $reverse = true;
}
$data['order'] = $order;

if ($is_editor && !$is_in_tinymce) { // admin actions

    if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
        $pageName = $langQuotaBar;
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langVideo);
        $data = array();
        list($diskQuotaVideo, $updir, $diskUsed) = getQuotaInfo($course_code, $webDir);
        $data['showQuota'] = showquota($diskQuotaVideo, $diskUsed, "$_SERVER[SCRIPT_NAME]?course=$course_code");
        view('modules.video.showQuota', $data);
    }

    // visibility commands
    if (isset($_GET['vis'])) {
        $table = select_table($_GET['table']);
        if (!resource_belongs_to_progress_data(MODULE_ID_VIDEO, $_GET['vid'])) {
            Database::get()->query("UPDATE $table SET visible = ?d WHERE id = ?d", $_GET['vis'], $_GET['vid']);
            $action_message = "<div class='alert alert-success'>$langViMod</div>";
        } else {
         //   Session::Messages($langResourceBelongsToCert, "alert-warning");
            Session::flash('message',$langResourceBelongsToCert); 
            Session::flash('alert-class', 'alert-warning');
        }
    }

    // Public accessibility commands
    if (isset($_GET['public']) or isset($_GET['limited'])) {
        $new_public_status = isset($_GET['public']) ? 1 : 0;
        $table = select_table($_GET['table']);
        Database::get()->query("UPDATE $table SET public = ?d WHERE id = ?d", $new_public_status, $_GET['vid']);
        $action_message = "<div class='alert alert-success'>$langViMod</div>";
    }

    if (isset($_GET['delete'])) {
        if ($_GET['delete'] == 'delcat') { // delete video category
            // delete category videos
            $error = FALSE;
            $q1 = Database::get()->queryArray("SELECT id FROM video WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
            foreach ($q1 as $a) {
                if (!resource_belongs_to_progress_data(MODULE_ID_VIDEO, $a->id)) {
                    delete_video($a->id, 'video', $course_id, $course_code, $webDir);
                }   else {
                  //  Session::Messages($langResourceBelongsToCert, "alert-warning");
                    Session::flash('message',$langResourceBelongsToCert); 
                    Session::flash('alert-class', 'alert-warning');
                    $error = TRUE;
                }
            }
            $q = Database::get()->queryArray("SELECT id FROM videolink WHERE category = ?d AND course_id = ?d", $_GET['id'], $course_id);
            foreach ($q as $a) {
                delete_video($a->id, 'videolink');
            }
            delete_video_category($_GET['id']);
        } else {  // delete video / videolink
            $table = select_table($_GET['table']);
            if (!resource_belongs_to_progress_data(MODULE_ID_VIDEO, $_GET['id'])) {
                delete_video($_GET['id'], $table, $course_id, $course_code, $webDir);
            } else {
               // Session::Messages($langResourceBelongsToCert, "alert-warning");
                Session::flash('message',$langResourceBelongsToCert); 
                Session::flash('alert-class', 'alert-warning');
            }
        }
    }
} // end of admin actions

// list data
$data['count_video'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM video $filterv AND course_id = ?d", $course_id)->count;
$data['count_video_links'] = Database::get()->querySingle("SELECT count(*) AS count FROM videolink $filterl AND course_id = ?d", $course_id)->count;
$data['num_of_categories'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `video_category` WHERE course_id = ?d", $course_id)->count;
$data['items'] = getLinksOfCategory(0, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin); // uncategorized items
$data['categories'] = Database::get()->queryArray("SELECT * FROM `video_category` WHERE course_id = ?d ORDER BY name", $course_id);

// js and view
if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    load_js('tinymce.popup.urlgrabber.min.js');
}
add_units_navigation(TRUE); // TODO: test
ModalBoxHelper::loadModalBox(true);
view('modules.video.index', $data);
