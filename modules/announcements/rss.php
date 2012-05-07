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

include '../../include/init.php';

if (isset($_GET['c'])) {
	$code = $_GET['c'];
	$cours_id = course_code_to_id(escapeSimple($code));
} else {
	$code = '';
	$cours_id = false;
}
if ($cours_id === false) {
	header("HTTP/1.0 404 Not Found");
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
	     '<title>404 Not Found</title></head><body>',
	     '<h1>Not Found</h1><p>The requested course "',
	     htmlspecialchars($code),
	     '" does not exist.</p></body></html>';
	exit;
}

list($title) = mysql_fetch_row(db_query("SELECT title FROM course WHERE code = '$code'"));
$title = htmlspecialchars($intitule, ENT_NOQUOTES); 

$result = db_query("SELECT DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM announcement WHERE course_id = $cours_id 
                ORDER BY `order` DESC");
list($lastbuilddate) = mysql_fetch_row($result);

header ("Content-Type: application/xml;");
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
echo "<channel>";
echo "<atom:link href='".$urlServer."modules/announcements/rss.php?c=".$code."' rel='self' type='application/rss+xml' />";
echo "<title>$langCourseAnnouncements $title</title>";
echo "<link>".$urlServer."courses/".$code."</link>";
echo "<description>$langAnnouncements</description>";
echo "<lastBuildDate>$lastbuilddate</lastBuildDate>";
echo "<language>el</language>";

$sql = db_query("SELECT id, title, content, DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM announcement WHERE course_id = $cours_id ORDER BY `order` DESC");

while ($r = mysql_fetch_array($sql)) {
	echo "<item>";
	echo "<title>".htmlspecialchars($r['title'], ENT_NOQUOTES)."</title>";
	echo "<link>".$urlServer."modules/announcements/announcements.php?an_id=".$r['id']."&amp;c=".$code."</link>";
	echo "<description>".htmlspecialchars($r['content'], ENT_NOQUOTES)."</description>";	
	echo "<pubDate>".$r['dateformat']."</pubDate>";
	echo "<guid isPermaLink='false'>".$r['dateformat'].$r['id']."</guid>";
	echo "</item>";
}

echo "</channel></rss>";
