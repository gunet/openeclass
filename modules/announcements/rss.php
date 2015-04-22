<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

rss_check_access();

$title = htmlspecialchars(Database::get()->querySingle("SELECT title FROM course WHERE id = ?d", $course_id)->title, ENT_NOQUOTES);

$q = Database::get()->querySingle("SELECT DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat
                FROM announcement WHERE course_id = ?d AND visible = 1
                ORDER BY `order` DESC", $course_id);
if ($q) {
    $lastbuilddate = $q->dateformat;
} else {
    $lastbuilddate = '';
}

$link = $urlServer . 'modules/announcements/rss.php?c=' . urlencode($course_code);
if (isset($_GET['token'])) {
    $link .= '&amp;uid=' . $_GET['uid'] . '&amp;token=' . $_GET['token'];
}

header("Content-Type: application/xml;");
echo "<?xml version='1.0' encoding='utf-8'?>";
echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
echo "<channel>";
echo "<atom:link href='$link' rel='self' type='application/rss+xml' />";
echo "<title>$langCourseAnnouncements " . q($title) . "</title>";
echo "<link>{$urlServer}courses/" . q($course_code) . "/</link>";
echo "<description>$langAnnouncements</description>";
echo "<lastBuildDate>$lastbuilddate</lastBuildDate>";
echo "<language>el</language>";

Database::get()->queryFunc("SELECT id, title, content, DATE_FORMAT(`date`,'%a, %d %b %Y %T +0300') AS dateformat
		FROM announcement WHERE course_id = ?d AND visible = 1 ORDER BY `order` DESC", function($r) use ($course_code, $urlServer) {
    echo "<item>";
    echo "<title>" . htmlspecialchars($r->title, ENT_NOQUOTES) . "</title>";
    echo "<link>{$urlServer}modules/announcements/index.php?an_id=" . $r->id . "&amp;course=" . urlencode($course_code) . "</link>";
    echo "<description>" . htmlspecialchars($r->content, ENT_NOQUOTES) . "</description>";
    echo "<pubDate>" . $r->dateformat . "</pubDate>";
    echo "<guid isPermaLink='false'>" . $r->dateformat . $r->id . "</guid>";
    echo "</item>";
}, $course_id);

echo "</channel></rss>";

