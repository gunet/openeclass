<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * Announcements RSS Feed Component 
 */

require_once 'include/init.php';

$result = db_query("SELECT DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM admin_announcements
		WHERE visible = 1 AND lang = '$language'
		ORDER BY `date` DESC");
list($lastbuilddate) = mysql_fetch_row($result);

header ("Content-Type: application/xml;");
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
echo "<channel>";
echo "<atom:link href='${urlServer}rss.php' rel='self' type='application/rss+xml' />";
echo "<title>$langAnnouncements $siteName</title>";
echo "<link>".$urlServer."rss.php?lang=$language</link>";
echo "<description>$langAnnouncements</description>";
echo "<lastBuildDate>$lastbuilddate</lastBuildDate>";
echo "<language>".$language."</language>";

$sql = db_query("SELECT id, title, body, DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM admin_announcements
		WHERE visible = 'V' AND lang = '$language'
		ORDER BY `date` DESC");

while ($r = mysql_fetch_array($sql)) {
	echo "<item>";
	echo "<title>".htmlspecialchars($r['title'], ENT_NOQUOTES)."</title>";
	echo "<link>".$urlServer."modules/announcements/main_ann.php?aid=".$r['id']."</link>";
	echo "<description>".htmlspecialchars($r['body'], ENT_NOQUOTES)."</description>";	
	echo "<pubDate>".$r['dateformat']."</pubDate>";
	echo "<guid isPermaLink='false'>".$r['dateformat'].$r['id']."</guid>";
	echo "</item>";
}

echo "</channel></rss>";
