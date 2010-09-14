<?php
/*========================================================================
*   Open eClass 2.3
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

/*
 * Announcements RSS Feed Component
 */

$path2add = 0;
include 'include/init.php';

$rss_lang = langname_to_code($language);
$result = db_query("SELECT DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM admin_announcements
		WHERE visible = 'V' AND lang = '$rss_lang'
		ORDER BY `date` DESC", $mysqlMainDb);
list($lastbuilddate) = mysql_fetch_row($result);

header ("Content-Type: application/xml;");
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
echo "<channel>";
echo "<atom:link href='${urlServer}rss.php' rel='self' type='application/rss+xml' />";
echo "<title>$langAnnouncements</title>";
echo "<link>".$urlServer."rss.php?lang=".$rss_lang."</link>";
echo "<description>$langAnnouncements $siteName</description>";
echo "<lastBuildDate>$lastbuilddate</lastBuildDate>";
echo "<language>".$rss_lang."</language>";

$sql = db_query("SELECT id, title, body, DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM admin_announcements
		WHERE visible = 'V' AND lang = '$rss_lang'
		ORDER BY `date` DESC", $mysqlMainDb);

while ($r = mysql_fetch_array($sql)) {
	echo "<item>";
	echo "<title>".$r['title']."</title>";
	echo "<link>".$urlServer."</link>";
	echo "<description>".q(standard_text_escape($r['body']))."</description>";	
	echo "<pubDate>".$r['dateformat']."</pubDate>";
	echo "<guid isPermaLink='false'>".$r['dateformat'].$r['id']."</guid>";
	echo "</item>";
}

echo "</channel></rss>";
