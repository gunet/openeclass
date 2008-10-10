<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/**===========================================================================
    about.php
    @last update: 31-05-2006 by Pitsiougas Vagelis
    @authors list: Karatzidis Stratos <kstratos@uom.gr>
               Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: About page for the administrator

     This script displays information about GUnet eClass version and about the
     server running (PHP version, Apache version, MySQL version).

     The user can : - See the information
                 - Return to main administrator page

     @Comments: The script is organised in two sections.

     1) Gather the information
  2) Display them on an HTML page

==============================================================================*/

/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langVersion;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";
$totalHits = 0;
/*****************************************************************************
        MAIN BODY
******************************************************************************/

$sql = "SELECT COUNT(*) AS cnt FROM cours";
$result = db_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $totalCourses = $row['cnt'];
}
mysql_free_result($result);

$sql = "SELECT code FROM cours";
$result = db_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $course_codes[] = $row['code'];
}
mysql_free_result($result);

$first_date_time = time();
foreach ($course_codes as $course_code) {
    $sql = "SELECT COUNT(*) AS cnt FROM actions";
    $result = db_query($sql, $course_code);
    while ($row = mysql_fetch_assoc($result)) {
        $totalHits += $row['cnt'];
    }
    mysql_free_result($result);

    $sql = "SELECT UNIX_TIMESTAMP(MIN(date_time)) AS first FROM actions";
    $result = db_query($sql, $course_code);
    while ($row = mysql_fetch_assoc($result)) {
        $tmp = $row['first'];
        if ($tmp < $first_date_time) {
            $first_date_time = $tmp;
        }
    }
    mysql_free_result($result);
}
$uptime = date("d-m-Y H:i", $first_date_time);

// Constract a table with all information
$tool_content .= "
    <table width=\"75%\" class=\"Smart\" align=\"center\" >
    <tbody>
    <tr class=\"odd\">
      <th width=\"160\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
      <td><b>$langPlatformIdentity</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">Version:</th>
      <td>$langAboutText <b>".$siteName." ".$langEclassVersion."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">IP Host:</th>
      <td>".$langHostName."<b>".$SERVER_NAME."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">Web Server:</th>
      <td>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">Data Base Server:</th>
      <td>";
        if (extension_loaded('mysql'))
            $tool_content .= "$langMySqlVersion<b>".mysql_get_server_info()."</b>";
        else // If not display message no MySQL
            $tool_content .= "<font color=\"red\">".$langNoMysql."</font>";
    $tool_content .= "</td>
    </tr>
    </tbody>
    </table>

    <br>";

$tool_content .= "
    <table width=\"75%\" class=\"Smart\" align=\"center\" >
    <tbody>
    <tr class=\"odd\">
      <th width=\"160\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
      <td><b>$langStoixeia</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langCoursesHeader:</th>
      <td>".$langAboutCourses." <b>".$totalCourses."</b> ".$langCourses."</td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">".$langTotalHits.":</th>
      <td><b>".$totalHits."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">".$langUptime.":</th>
      <td><b>".$uptime."</b></td>
    </tr>
    </tbody>
    </table>

    <br>";

$tool_content .= "<br><p class=\"right\"><a href=\"index.php\">".$langBack."</a></p>";

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
