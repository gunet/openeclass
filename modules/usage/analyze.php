<?php

$u_user_id = $_POST['u_user_id'];
$query = "SELECT * FROM actions WHERE user_id = '$u_user_id'";
$result = db_query($query, $currentCourseID);
while ($row = mysql_fetch_assoc($result)) {
    $tool_content .= '<p>'.print_r($row, true).'</p>';
}
$tool_content .= $query;

?>
