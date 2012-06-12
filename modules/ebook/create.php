<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/lib/fileUploadLib.inc.php';

$nameTools = $langEBookCreate;
$navigation[] = array('url' => 'index.php?course='.$course_code, 'name' => $langEBook);
define('EBOOK_DOCUMENTS', true);

mysql_select_db($mysqlMainDb);

if (!$is_editor) {
        redirect_to_home_page();
} else {
        $title = trim(@$_POST['title']);
        if (empty($title) or !isset($_FILES['file'])) {
                $tool_content .= "<p class='caution'>$langFieldsMissing</p>";
        }
        if (!preg_match('/\.zip$/i', $_FILES['file']['name'])) {
                $tool_content .= "<p class='caution'>$langUnwantedFiletype: " .
                                 q($_FILES['file']['name']) . "</p>";
        }
        if (!empty($tool_content)) {
                draw($tool_content, 2);
                exit;
        }

        list($order) = mysql_fetch_row(db_query("SELECT MAX(`order`) FROM ebook WHERE course_id = $course_id"));
        if (!$order) {
                $order = 1;
        } else {
                $order++;
        }
        db_query("INSERT INTO ebook SET `order` = $order, `course_id` = $course_id, `title` = " .
                         autoquote($title));
        $ebook_id = mysql_insert_id();

        // Initialize document subsystem global variables
        include '../document/doc_init.php';

        if (!mkdir($basedir, 0775, true)) {
                db_query("DELETE FROM ebook WHERE course_id = $course_id AND id = $ebook_id");
                $tool_content .= "<p class='caution'>$langImpossible</p>";
                draw($tool_content, 2);
                exit;
        }

        chdir($basedir);
        $zipFile = new pclZip($_FILES['file']['tmp_name']);
        $realFileSize = 0;
        $zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'process_extracted_file');
        header("Location: $urlAppend/modules/ebook/edit.php?course=$course_code&id=$ebook_id");
}
