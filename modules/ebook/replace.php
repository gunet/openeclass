<?php

$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';

require_once '../../include/baseTheme.php';
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/lib/fileUploadLib.inc.php';

$nameTools = $langEBook;
$nameTools = $langEBookReplace;

$lastdir = getcwd();
$id = 0;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langEBook);
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $id, 'name' => $langEBookEdit);
}
$r = Database::get()->querySingle("SELECT title FROM ebook WHERE course_id = ?d AND id = ?d", $course_id, $id);

if (!$is_editor or !$r) {
    redirect_to_home_page();
} elseif (isset($_FILES['file'])) {

    validateUploadedFile($_FILES['file']['name'], 2);
    $zip = new pclZip($_FILES['file']['tmp_name']);
    validateUploadedZipFile($zip->listContent(), 2);

    $basedir = $webDir . 'courses/' . $course_code . '/ebook/' . $id;
    chdir($basedir);
    if ($zip->extract()) {
        $tool_content .= "<div class='alert alert-success'>$langEBookReplaceDoneZip</div>\n";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langErrorReadingZipFile</div>\n";
    }
    $tool_content .= "<p><a href='edit.php?course=$course_code&amp;id=$id'>$langBack</a></p>\n";
} else {
    $title = $r->title;
    $tool_content .= "<form method='post' action='replace.php?course=$course_code&amp;id=$id' enctype='multipart/form-data'>
                             <fieldset><legend>$langUpload</legend>
                                <table width='99%' class='tbl'>
                                   <tr><th>$langTitle:</th>
                                            <td>" . q($title) . "</td></tr>
                                   <tr><th>$langZipFile:</th>
                                       <td><input type='file' name='file' size='53' /></td></tr>
                                   <tr><th>&nbsp;</th>
                                       <td><input type='submit' name='submit' value='$langSend' /></td></tr>
                                </table></fieldset></form>";
}

chdir($lastdir);
draw($tool_content, 2, null, $head_content);

