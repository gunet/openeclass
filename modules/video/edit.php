<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

// setup
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'Video';

// dependencies
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/drives/clouddrive.php';
require_once 'include/action.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
require_once 'delos_functions.php';
require_once 'video_functions.php';

$action = new action();
$action->record('MODULE_ID_VIDEO');
$data = array();

// navigation
$toolName = $langVideo;
if (isset($_GET['id'])) {
    $pageName = $langEditChange;
} else {
    $pageName = $langAddV;
}
$backPath = $data['backPath'] = $urlAppend . "modules/video/index.php?course=" . $course_code;
$navigation[] = array('url' => $backPath, 'name' => $langVideo);


if (isset($_GET['form_input'])) {
    $form_input = $_GET['form_input'];
} else {
    $form_input = '';
}
$data['form_input'] = $form_input;

// handle submitted data
if (isset($_POST['edit_submit']) && isset($_POST['id'])) { // edit
    $id = $_POST['id'];

    if (isset($_POST['table'])) {
        $table = select_table($_POST['table']);
    }

    if ($table == 'video') {
        Database::get()->query("UPDATE video
                    SET title = ?s,
                        description = ?s,
                        creator = ?s,
                        publisher = ?s,
                        category = ?d
                  WHERE id = ?d",
            $_POST['title'], $_POST['description'], $_POST['creator'], $_POST['publisher'], $_POST['selectcategory'], $id);
    } elseif ($table == 'videolink') {
        Database::get()->query("UPDATE videolink
                SET url = ?s,
                    title = ?s,
                    description = ?s,
                    creator = ?s,
                    publisher = ?s,
                    category = ?d
              WHERE id = ?d",
            canonicalize_url($_POST['url']), $_POST['title'], $_POST['description'], $_POST['creator'], $_POST['publisher'], $_POST['selectcategory'], $id);
    }

    // index and log
    if ($table == 'video') {
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEO, $id);
    } elseif ($table == 'videolink') {
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEOLINK, $id);
    }
    Log::record($course_id, MODULE_ID_VIDEO, LOG_MODIFY, array('id' => $id,
        'url' => canonicalize_url($_POST['url']),
        'title' => $_POST['title'],
        'description' => ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+')));

    // navigate
    Session::flash('message',$langGlossaryUpdated);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/video/index.php?course=" . $course_code);
}

// handle submitted data
if (isset($_POST['add_submit'])) { // add
    print_a($_POST);


    $uploaded = false;
    $date = date_create($_POST['date']);
    $videodate = date_format($date, "Y-m-d H:i");
    if (isset($_POST['URL'])) { // add videolink
        $url = $_POST['URL'];
        $title = ($_POST['title'] == '') ? $url : $_POST['title'];
        $q = Database::get()->query('INSERT INTO videolink (course_id, url, title, description, category, creator, publisher, date)
                                                        VALUES (?s, ?s, ?s, ?s, ?d, ?s, ?s, ?t)',
            $course_id, canonicalize_url($url), $title, $_POST['description'], $_POST['selectcategory'], $_POST['creator'], $_POST['publisher'], $videodate);
        $id = $q->lastInsertID;
        // index and log
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEOLINK, $id);
        $txt_description = ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+');
        Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, array('id' => $id,
            'url' => canonicalize_url($url),
            'title' => $title,
            'description' => $txt_description));

        // navigate
        $successAlert = $langLinkAdded;

    } else { // add video

        list($diskQuotaVideo, $updir, $diskUsed) = getQuotaInfo($course_code, $webDir);

        if (isset($_POST['fileCloudInfo'])) { // upload cloud file
            $cloudfile = CloudFile::fromJSON($_POST['fileCloudInfo']);
            $file_name = $cloudfile->name();
        } else if (isset($_FILES['userFile']) && is_uploaded_file($_FILES['userFile']['tmp_name'])) { // upload local file
            if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaVideo) {
                Session::flash('message',$langNoSpace);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("modules/video/index.php?course=" . $course_code);
            } else {
                $file_name = $_FILES['userFile']['name'];
                $tmpfile = $_FILES['userFile']['tmp_name'];
            }
        } else {
            Session::flash('message', $langNoFileUploaded);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/video/index.php?course=" . $course_code);
        }

        if (!isWhitelistAllowed($file_name)) {
            Session::flash('message', $langUploadedFileNotAllowed . " <strong>" . q($file_name) . "</strong>");
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/video/index.php?course=" . $course_code);
        }

        // convert php file in phps to protect the platform against malicious codes
        $file_name = str_replace(" ", "%20", $file_name);
        $file_name = str_replace("%20", "", $file_name);
        $file_name = str_replace("\'", "", $file_name);
        $safe_filename = sprintf('%x', time()) . randomkeys(16) . "." . get_file_extension($file_name);

        $iscopy = (isset($cloudfile))
                ? ($cloudfile->storeToLocalFile("$updir/$safe_filename") == CloudDriveResponse::OK)
                : copy("$tmpfile", "$updir/$safe_filename");

        if (!$iscopy) {
            Session::flash('message',$langFileNot);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/video/index.php?course=" . $course_code);
        }

        $connector = AntivirusApp::getAntivirus();
        if($connector->isEnabled()) {
            $output=$connector->check("$updir/$safe_filename");
            if($output->status==$output::STATUS_INFECTED){
                AntivirusApp::block($output->output);
            }
        }

        $path = '/' . $safe_filename;
        $url = $file_name;
        $id = Database::get()->query('INSERT INTO video
                                                       (course_id, path, url, title, description, category, creator, publisher, date)
                                                       VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?s, ?t)'
            , $course_id, $path, $url, $_POST['title'], $_POST['description'], $_POST['selectcategory']
            , $_POST['creator'], $_POST['publisher'], $videodate)->lastInsertID;

        // index and log
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_VIDEO, $id);
        Log::record($course_id, MODULE_ID_VIDEO, LOG_INSERT, @array('id' => $id,
            'path' => $path,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => ellipsize(canonicalize_whitespace(strip_tags($_POST['description'])), 50, '+')));

        // navigate
        $successAlert = $langFAdd;
    }

    // navigate
    Session::flash('message',$successAlert);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/video/index.php?course=" . $course_code);
}

// handle OpenDelos submitted data
if (isset($_POST['add_submit_delos'])) {
    if (isset($_POST['delosResources'])) {
        list($jsonPublicObj, $jsonPrivateObj, $checkAuth) = requestDelosJSON();
        storeDelosResources($jsonPublicObj, $jsonPrivateObj, $checkAuth);
    }
    Session::flash('message',$langLinksAdded);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/video/index.php?course=" . $course_code);
}

// load requested entity for editing
if (isset($_GET['id']) && isset($_GET['table_edit'])) {
    $table_edit = $data['table_edit'] = select_table($_GET['table_edit']);
    $data['edititem'] = Database::get()->querySingle("SELECT * FROM $table_edit WHERE course_id = ?d AND id = ?d ORDER BY title", $course_id, $_GET['id']);
}

// handle common data for create/edit
$data['nick'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
$data['resultcategories'] = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY `name`", $course_id);

// js and view
load_js('tools.js');
load_js('bootstrap-datetimepicker');

if ($form_input === 'opendelos') {
    list($jsonPublicObj, $jsonPrivateObj, $checkAuth) = requestDelosJSON();
    $data['jsonPublicObj'] = $jsonPublicObj;
    $data['jsonPrivateObj'] = $jsonPrivateObj;
    $data['checkAuth'] = $checkAuth;
    $data['currentVideoLinks'] = getCurrentVideoLinks($course_id);
    $head_content .= getDelosJavaScript();
    view('modules.video.editdelos', $data);
} else {
    view('modules.video.edit', $data);
}
