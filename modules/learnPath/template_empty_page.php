<?php

/*
Header, Copyright, etc ...
*/

/***************
Initializations
****************/

$require_current_course = TRUE;
$langFiles = "learnPath";

require_once("../../include/baseTheme.php");
$tool_content = "";

$nameTools = $langlearnPath;


if($is_adminOfCourse) {       // Teacher View

} // Teacher View

else { // Student View

} // Student View



/*>>>>>>>>>>>> COMMON FOR THEACHERS AND STUDENTS  <<<<<<<<<<<<*/

/*>>>>>>>>>>>> END: COMMON TO TEACHERS AND STUDENTS <<<<<<<<<<<<*/

draw($tool_content, 2, "learnPath");

?>
