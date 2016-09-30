<?php
session_start();
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Course';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';

$diskUsed = dir_total_space($basedir);
$diskQuotaDocument = $diskUsed + ini_get('upload_max_filesize') * 1024 * 1024;

require_once 'include/action.php';
$action = new action();

global $course_id;


function outputJSON($msg, $status = 'error'){
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

header('Content-type: application/json');
$json = file_get_contents('php://input');
$json_decode = json_decode($json, true); 
$mind_s1r=$_POST["mind_str"];

$file_path=$mind_s1r+".jm";
$fileName="$langMindMap";
$file_format = get_file_extension($fileName);
$file_date = date("Y\-m\-d G\:i\:s");


$fileName = php2phps(add_ext_on_mime($fileName));
        // File name used in file system and path field
        $safe_fileName = safe_filename(get_file_extension($fileName));
         $file_path = '/' . $safe_fileName;


$q = Database::get()->query("INSERT INTO document SET
                                        course_id = ?d,
										subsystem = ?d,
										subsystem_id = ?d,
                                        path = ?s,
                                        filename = ?s,
                                        visible = ?d,
                                        creator = ?s,
                                        date = ?t,
                                        date_modified = ?t ,
                                        format = ?s,
										language = ?s
                                       "
                            , $course_id, 0, 0, $file_path, $fileName, 1, ($_SESSION['givenname'] . " " . $_SESSION['surname']), $file_date, $file_date, $file_format, "el");

if ($q) {
     if (!isset($id)) {
                        $id = $q->lastInsertID;
                        $log_action = LOG_INSERT;
					}
				}


