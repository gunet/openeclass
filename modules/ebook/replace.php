<?php

$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';

include '../../include/baseTheme.php';
require_once '../../include/pclzip/pclzip.lib.php';
require_once '../../include/lib/fileUploadLib.inc.php';

mysql_select_db($mysqlMainDb);

$nameTools = $langEBook;
$nameTools = $langEBookReplace;

$lastdir = getcwd();
$id = 0;
if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $navigation[] = array('url' => 'index.php?course='.$code_cours, 'name' => $langEBook);
        $navigation[] = array('url' => 'edit.php?course='.$code_cours.'&amp;id=' . $id, 'name' => $langEBookEdit);
}
$r = db_query("SELECT title FROM ebook WHERE course_id = $cours_id AND id = $id");

if (!$is_editor or mysql_num_rows($r) == 0) {
        redirect_to_home_page();
} elseif (isset($_FILES['file'])) {
    
        validateUploadedFile($_FILES['file']['name'], 2);
        $zip = new pclZip($_FILES['file']['tmp_name']);
        validateUploadedZipFile($zip->listContent(), 2);
        
    
        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $id;
        chdir($basedir);
        if ($zip->extract()) {
                $tool_content .= "<p class='success'>$langEBookReplaceDoneZip</p>\n";
        } else {
                $tool_content .= "<p class='alert1'>$langErrorReadingZipFile</p>\n";
        }
        $tool_content .= "<p><a href='edit.php?course=$code_cours&amp;id=$id'>$langBack</a></p>\n";
} else {
        list($title) = mysql_fetch_row($r);
        $tool_content .= "<form method='post' action='replace.php?course=$code_cours&amp;id=$id' enctype='multipart/form-data'>
                             <fieldset><legend>$langUpload</legend>
                                <table width='99%' class='tbl'>
                                   <tr><th>$langTitle:</th>
                                            <td>" . q($title) . "</td></tr>
                                   <tr><th>$langZipFile:</th>
                                       <td><input type='file' name='file' size='53' /></td></tr>
                                   <tr><th>&nbsp;</th>
                                       <td><input type='submit' name='submit' value='".q($langSend)."' /></td></tr>
                                </table></fieldset></form>";
}

chdir($lastdir);
draw($tool_content, 2, null, $head_content);

