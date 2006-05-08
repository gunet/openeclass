<?php  
/*
Header
*/


$require_current_course = TRUE;
$langFiles = array('course_description','pedaSuggest');
$require_help = TRUE;
$helpTopic = 'Coursedescription';
require("../../../config/config.php");
include ('../../../include/init.php');

include('../../../include/lib/textLib.inc.php'); 

$nameTools = $langCourseProgram;
//begin_page();

?>
<html>
<body>
<table width="100%" border="0">
<tr>
<td colspan="2">

<?php 


	mysql_select_db("$currentCourseID",$db);
	$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) >0 )
	{
		echo "
			<hr noshade size=\"1\">";
		while ($bloc = mysql_fetch_array($res))
		{ 
			echo "
			<H4>
				".$bloc["title"]."
			</H4>
			<font size=2 face='arial, helvetica'>
				".make_clickable(nl2br($bloc["content"]))."
			</font>";
		}
	}
	else
	{
		echo "<br><h4>$langThisCourseDescriptionIsEmpty</h4>";
	}


?>
		</td>
	</tr>
	<tr name="bottomLine" >
		<td colspan="2">
			<br>
			<hr noshade size="1">
		</td>
	</tr>
</table>
</body>
</html>

