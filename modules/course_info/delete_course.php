<?php
$require_current_course = TRUE;
$langFiles = 'course_info';
$require_prof = true;
include '../../include/baseTheme.php';

$nameTools = $langDelCourse;

$tool_content = "";

if($is_adminOfCourse) {
	if(isset($delete)) {
		
		
		mysql_select_db("$mysqlMainDb",$db); 
		mysql_query("DROP DATABASE `$currentCourseID`");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_user WHERE code_cours='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_faculte WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.annonces WHERE code_cours='$currentCourseID'");
		##[BEGIN personalisation modification]############
		mysql_query("DELETE FROM `$mysqlMainDb`.agenda WHERE lesson_code='$currentCourseID'");
		##[END personalisation modification]############
		@mkdir("../../courses/garbage");
		rename("../../courses/$currentCourseID", "../../courses/garbage/$currentCourseID");
		
		$tool_content .= "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langTheCourse $currentCourseID $intitule $langHasDel</b></p>
							
							<p><a href=\"../../index.php\">".$langBackHome." ".$siteName."</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		
		draw($tool_content, 1);

		exit();
	} else {

  $tool_content .= "
  <div id=\"operations_container\">
  <ul id=\"opslist\">
    <li><a href=\"infocours.php\">$langBack</a></li>
  </ul>
  </div>";

  $tool_content .= "
  <br />
";

  $tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
    <th>&nbsp;</th>
    <td class=\"caution_NoBorder\" height='60' colspan='2'>
    <p>$langByDel_A <b>$intitule ($currentCourseID) </b>&nbsp;?  </p>
    </td>
  </tr>
    <tr>
      <th rowspan='2' class='left' width='150'>$langConfirmDel :</th>
      <td width='52' align='center'><a href=\"".$_SERVER['PHP_SELF']."?delete=yes\">$langYes</a></td>
      <td><small>$langByDel</small></td>
    </tr>
    <tr>
      <td align='center'><a href=\"infocours.php\">$langNo</a></td>
      <td>&nbsp;</td>
    </tr>
    </tbody>
    </table>";

	} // else
} else  {
	$tool_content .= "<center><p>$langForbidden</p></center>";
}

draw($tool_content, 2, 'course_info');

?>
