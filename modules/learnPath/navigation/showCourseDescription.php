<?php  

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	showCourseDescription.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================        
    @Description:

 	@Comments:
 
  	@todo: 
==============================================================================
*/

$require_current_course = TRUE;
$langFiles = "course_description";;
require_once("../../../config/config.php");
require_once ('../../../include/init.php');

require_once('../../../include/lib/textLib.inc.php'); 

$nameTools = $langCourseProgram;

?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset?>">
  <title><?php echo $langCourseProgram ?></title>
</head>
<body>
<table width="99%" border="0">
<tr>
<td colspan="2">

<?php 


	mysql_select_db("$currentCourseID",$db);
	$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
	$res = db_query($sql);
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

