<?php

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Course';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/course_metadata/CourseXML.php';

// track stats
require_once 'include/action.php';
$action = new action();

global $course_id;

load_js('tools.js');
ModalBoxHelper::loadModalBox(true);

$tool_content = '<div><h1>hi</h1></div>';
function outputJSON($msg, $status = 'error'){
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

$json = file_get_contents('php://input');
$json_decode = json_decode($json, true); 
$mind_s1r=$_POST["mind_str"];

$file_path=$mind_s1r+".jm";
$fileName="jsmind.jm";
$file_format = get_file_extension($fileName);
$vis=1;
$file_date = date("Y\-m\-d G\:i\:s");

echo '<script language="javascript">';
echo 'alert("message successfully sent")';
echo '</script>';

 Database::get()->query("INSERT INTO document SET
                                        course_id = ?d,
                                        path = ?s,
                                        filename = ?s,
                                        visible = ?d,
                                        date = ?t,
                                        format = ?s,
                                       "
                            , $course_id, $msg, $fileName, $vis
                            , $file_date
                            , $file_format)->lastInsertID;

draw($tool_content, 2, null, $head_content);




















