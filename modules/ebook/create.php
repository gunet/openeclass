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


$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/document/doc_init.php';
require_once 'include/log.class.php';

$pageName = $langEBookCreate;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langEBook);
define('EBOOK_DOCUMENTS', true);

if (isset($_POST['title'])) {
    $title = trim($_POST['title']);
}

if (empty($title)) {
    Session::flash('message',$langFieldsMissing);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
}

$order = Database::get()->querySingle("SELECT COALESCE(MAX(`order`), 1) AS `order` FROM ebook WHERE course_id = ?d", $course_id)->order;
$ebook_id = Database::get()->query("INSERT INTO ebook SET `order` = ?d, `course_id` = ?d, `title` = ?s, `visible` = 1", $order + 1, $course_id, $title)->lastInsertID;
Database::get()->query("INSERT INTO ebook_section SET ebook_id = ?d,
                                                public_id = ?s,
                                                title = ?s"
        , $ebook_id, '1', $langSection.' 1');
// Initialize document subsystem global variables
doc_init();

if (!make_dir($basedir)) {
    Database::get()->query("DELETE FROM ebook WHERE course_id = ?d AND id = ?d", $course_id, $ebook_id);
    Session::flash('message',$langImpossible);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
}

if (isset($_FILES['file']['name']) and !$_FILES['file']['error']) {
    if (!preg_match('/\.zip$/i', $_FILES['file']['name'])) {
        Session::flash('message',"$langUnwantedFiletype: " . $_FILES['file']['name']);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
    }
    validateUploadedFile($_FILES['file']['name'], 2);
    $userFile = $_FILES['file']['tmp_name'];
    $realFileSize = 0;
    $files_in_zip = array();
    $zipFile = new ZipArchive();
    if ($zipFile->open($userFile) == TRUE) {
        $diskUsed = dir_total_space($basedir);
        $diskQuotaDocument = Database::get()->querySingle("SELECT doc_quota AS quotatype FROM course WHERE id = ?d", $course_id)->quotatype;
        // check for file type in zip contents
        for ($i = 0; $i < $zipFile->numFiles; $i++) {
            $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
            $files_in_zip[$i] = $stat['name'];
            if (!empty(my_basename($files_in_zip[$i]))) {
                validateUploadedFile(my_basename($files_in_zip[$i]), 2);
            }
        }
        for ($i = 0; $i < $zipFile->numFiles; $i++) {
            $stat = $zipFile->statIndex($i);
            $realFileSize += $stat["size"]; // check for free space
            if ($diskUsed + $realFileSize > $diskQuotaDocument) {
                Session::flash('message',$langNoSpace);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
            }
            $extracted_file_name = process_extracted_file($stat);
            if (!is_null($extracted_file_name)) {
                $zipFile->renameIndex($i, $extracted_file_name);
                $zipFile->extractTo("$basedir/", $extracted_file_name);
            }
        }
        $zipFile->close();
    } else {
        Session::flash('message',$langErrorFileMustBeZip);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
    }
}

redirect_to_home_page("modules/ebook/edit.php?course=$course_code&id=$ebook_id");
