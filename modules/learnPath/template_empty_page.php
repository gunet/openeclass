<?php

/*
Header, Copyright, etc ...
*/

/***************
Initializations
****************/

$require_current_course = TRUE;
$langFiles = "learnPath";

include("../../include/init.php");

$nameTools = $langlearnPath;

begin_page();


/* entos plaisiou */
echo "</td></tr></table>";
/* ap' arkh s'akrh */


if($is_adminOfCourse) {       // Teacher View

} // Teacher View

else { // Student View

} // Student View



/*>>>>>>>>>>>> COMMON FOR THEACHERS AND STUDENTS  <<<<<<<<<<<<*/

/*>>>>>>>>>>>> END: COMMON TO TEACHERS AND STUDENTS <<<<<<<<<<<<*/

/*  TODO

- Add introduction text
- Create | Import | Other stuff
- Learning Path table

*/

?>

</body>
</html>
