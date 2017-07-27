<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */



$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/document/doc_init.php';
require_once 'include/log.class.php';

$pageName = $langEBookCreate;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langEBook);
define('EBOOK_DOCUMENTS', true);

if (!$is_editor) {
    redirect_to_home_page();
} else {
    $title = trim(@$_POST['title']);
    if (empty($title)) {
        Session::Messages($langFieldsMissing, 'alert-danger');
        redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
    }
    if (isset($_FILES['file']['name']) and !$_FILES['file']['error']) {
        if (!preg_match('/\.zip$/i', $_FILES['file']['name'])) {
            Session::Messages("$langUnwantedFiletype: " . $_FILES['file']['name'], 'alert-danger');
            redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
        }
        validateUploadedFile($_FILES['file']['name'], 2);
        $zipFile = new PclZip($_FILES['file']['tmp_name']);
        validateUploadedZipFile($zipFile->listContent(), 2);
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
        Session::Messages($langImpossible, 'alert-danger');
        redirect_to_home_page("modules/ebook/index.php?course=$course_code&create=1");
    }

    if (isset($zipFile)) {
        chdir($basedir);
        $realFileSize = 0;
        $zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'process_extracted_file');
    }
    redirect_to_home_page("modules/ebook/edit.php?course=$course_code&id=$ebook_id");
}
