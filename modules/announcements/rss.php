<?php
/* ========================================================================
 * Open eClass 2.6
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

if (!visible_module(7)) {
        $toolContent_ErrorExists = caution($langCheckPublicTools);
	$_SESSION['errMessage'] = $toolContent_ErrorExists;
	session_write_close();
        if (!$uid) {
                $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
                header("Location:" . $urlSecure . "login_form.php?next=" . urlencode($next));
        } else {
                header("Location:" . $urlServer . "index.php");
        }	        
        if (isset($_SESSION['errMessage']) && strlen($_SESSION['errMessage']) > 0) {
                $extraMessage = $_SESSION['errMessage'];
                unset($_SESSION['errMessage']);
        }        
        $errorMessagePath = "../../";
        exit;
 }
 
list($intitule) = mysql_fetch_row(db_query("SELECT intitule FROM cours WHERE code = '$code'"));
$intitule = htmlspecialchars($intitule, ENT_NOQUOTES); 

$result = db_query("SELECT DATE_FORMAT(temps,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM annonces WHERE cours_id = $cours_id ORDER BY temps DESC", $mysqlMainDb);
list($lastbuilddate) = mysql_fetch_row($result);

header ("Content-Type: application/xml;");
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
echo "<channel>";
echo "<atom:link href='".$urlServer."modules/announcements/rss.php?c=".$code."' rel='self' type='application/rss+xml' />";
echo "<title>$langCourseAnnouncements $intitule</title>";
echo "<link>".$urlServer."courses/".$code."</link>";
echo "<description>$langAnnouncements</description>";
echo "<lastBuildDate>$lastbuilddate</lastBuildDate>";
echo "<language>el</language>";

$sql = db_query("SELECT id, title, contenu, DATE_FORMAT(temps,'%a, %d %b %Y %T +0300') AS dateformat 
		FROM annonces WHERE cours_id = $cours_id ORDER BY temps DESC", $mysqlMainDb);

while ($r = mysql_fetch_array($sql)) {
	echo "<item>";
	echo "<title>".htmlspecialchars($r['title'], ENT_NOQUOTES)."</title>";
	echo "<link>".$urlServer."modules/announcements/announcements.php?an_id=".$r['id']."&amp;c=".$code."</link>";
	echo "<description>".htmlspecialchars($r['contenu'], ENT_NOQUOTES)."</description>";	
	echo "<pubDate>".$r['dateformat']."</pubDate>";
	echo "<guid isPermaLink='false'>".$r['dateformat'].$r['id']."</guid>";
	echo "</item>";
}

echo "</channel></rss>";
