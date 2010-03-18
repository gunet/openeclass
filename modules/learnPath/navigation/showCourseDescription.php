<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	showCourseDescription.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: This script displays the Course Description when
                  the user is navigating in a learning path.

    @Comments:

    @todo:
==============================================================================
*/

$require_current_course = TRUE;
require_once("../../../config/config.php");
require_once ('../../../include/init.php');

require_once('../../../include/lib/textLib.inc.php');

$nameTools = $langCourseProgram;

?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset?>">
  <link href="../../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />
  <link href="../tool.css" rel="stylesheet" type="text/css" />
  <title><?php echo $langCourseProgram ?></title>
</head>
<body style="margin: 2px;">

<?php
	mysql_select_db("$currentCourseID",$db);
	$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
	$res = db_query($sql);
	if (mysql_num_rows($res) >0 )
	{
		//echo "
		//	<hr noshade size=\"1\">";
		while ($bloc = mysql_fetch_array($res))
		{
			echo "<p><div id='course_topic_title_id'>".$bloc["title"]."</div></p>
			<p>".make_clickable(nl2br($bloc["content"]))."</p>";
		}
	}
	else
	{
		echo "<p>$langThisCourseDescriptionIsEmpty</p>";
	}


?>
    </body></html>

