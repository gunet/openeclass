<?php

$require_login = true;

require_once 'test.php';

/*$content_id = $_GET['id'];

echo "This is delete.php<br>";

if (delete_content($content_id)) {
    header("location: index.php");
}*/

delete_content($_GET['id']);
Session::Messages($langH5pDeleteSuccess, 'alert-success');
redirect($urlAppend . 'modules/h5p/?course=' . $course_code);