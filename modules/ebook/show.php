<?php

session_start();

// save current course
if (isset($_SESSION['dbname'])) {
        define('old_dbname', $_SESSION['dbname']);
}

$unit = false;
$file_path = false;
$full_url_found = false;
$uri = strstr($_SERVER['REQUEST_URI'], 'ebook/show.php');
$path_components = explode('/', $uri);
if (count($path_components) >= 4) {
        $_SESSION['dbname'] = $path_components[2];
        $ebook_id = intval($path_components[3]);
        if (!empty($path_components[4])) {
                if ($path_components[4] == '_') {
                        $show_orphan_file = true;
                } else {
                        $show_orphan_file = false;
                        $ids = explode(',', $path_components[4]);
                        $current_sid = intval($ids[0]);
                        if (isset($ids[1])) {
                                $current_ssid = intval($ids[1]);
                                $full_url_found = true;
                                $current_display_id = $current_sid . ',' . $current_ssid;
                        }
                }
        }
        if (isset($path_components[5])) {
                if (preg_match('/^unit=([0-9]+)$/', $path_components[5], $matches)) {
                        $unit = intval($matches[1]);
                } elseif (array_search('..', $path_components) === false) {
                        $file_path = implode('/', array_slice($path_components, 5));
                }
        }
} else {
        not_found($uri);
}

$require_current_course = true;
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';
mysql_select_db($mysqlMainDb);

$ebook_url_base = "{$urlServer}modules/ebook/show.php/$currentCourseID/$ebook_id/";

if ($show_orphan_file and $file_path) {
        $disk_path = "{$webDir}courses/$currentCourseID/ebook/$ebook_id/$file_path";
	if (!send_file_to_client($disk_path, $file_path, true, false)) {
                not_found($file_path);
	}
	exit;
}

$nameTools = 'Ηλεκτρονικό Βιβλίο';

if ($unit !== false) {
	$exit_fullscreen_link = $urlAppend . '/modules/units/?id=' . $unit;
	$unit_parameter = 'unit=' . $unit;
} else {
	$exit_fullscreen_link = $urlAppend . '/courses/' . $currentCourseID . '/';
	$unit_parameter = '';
}
        $q = db_query("SELECT ebook_section.id AS sid,
                              ebook_section.id AS psid,
                              ebook_section.title AS section_title,
                              ebook_subsection.id AS ssid,
                              ebook_subsection.public_id AS pssid,
                              ebook_subsection.title AS subsection_title,
                              ebook_subsection.file
                       FROM ebook, ebook_section, ebook_subsection
                       WHERE ebook.id = $ebook_id AND
                             ebook.course_id = $cours_id AND
                             ebook_section.ebook_id = ebook.id AND
                             ebook_section.id = ebook_subsection.section_id
                             ORDER BY CONVERT(psid, UNSIGNED), psid,
                                      CONVERT(pssid, UNSIGNED), pssid");
while ($row = mysql_fetch_array($q)) {
	$sid = $row['sid'];
	$ssid = $row['ssid'];
	if (!isset($current_sid)) {
		$current_sid = $sid;
	}
	if ($current_sid == $sid and !isset($current_ssid)) {
		$current_ssid = $row['ssid'];
	}
        if (!isset($current_display_id) and isset($current_sid) and isset($current_ssid)) {
                $current_display_id = $sid . ',' . $ssid;
        }
        $display_id = $sid . ',' . $ssid;
	$sections[$sid] = $row['section_title'];
	if (isset($current_display_id) and $current_display_id == $display_id) {
		$class_active = ' class="active"';
	} else {
		$class_active = '';
	}
	if ($fragment_pos = strpos($row['file'], '#')) {
		$display_id = $section_id;
		$fragment = substr($row['file'], $fragment_pos);
		$file = substr($row['file'], 0, $fragment_pos);
	} else {
		$fragment = '';
		$file = $row['file'];
	}
	$subsections[$sid][$ssid] = array(
		'title' => $row['subsection_title'],
		'file' => "{$webDir}courses/$currentCourseID/ebook/$ebook_id/$file",
		'url' => $ebook_url_base . $display_id . '/' . $unit_parameter . $fragment,
                'class' => $class_active,
                'psid' => $row['psid'],
                'pssid' => $row['pssid']);
	if (isset($last_display_id) and isset($current_display_id)) {
		if ($current_display_id == $display_id) {
			$back_section_id = $last_display_id;
			$back_title = $last_title;
		} elseif ($current_display_id == $last_display_id) {
			$next_section_id = $display_id;
			$next_title = $row['subsection_title'];
		}
	}	
	$last_section_id = $sid;
	$last_display_id = $display_id;
	$last_title = $row['subsection_title'];
}

restore_saved_course();

if (!$full_url_found) {
	header('Location: ' . $ebook_url_base . $current_display_id . '/');
	exit;
}

if ($file_path) {
        $disk_path = preg_replace('#/[^/]*$#', '/', $subsections[$current_sid][$current_ssid]['file']) .
                     $file_path;
	if (!send_file_to_client($disk_path, $file_path, true, false)) {
                not_found($file_path);
	}
	exit;
}

$t = new Template();
$t->set_file('page', $webDir . 'template/classic/ebook_fullscreen.html');
$t->set_var('page_title', $currentCourseName . ': ' . $nameTools);
$t->set_var('course_title', $currentCourseName);
$t->set_var('course_home_link', $urlAppend . '/courses/' . $currentCourseID . '/');
$t->set_var('exit_fullscreen_link', $exit_fullscreen_link);
$t->set_var('unit_parameter', $unit_parameter);
$t->set_var('template_base', $urlAppend . '/template/classic/');
$t->set_var('img_base', $urlAppend . '/template/classic/img/ebook');
$t->set_var('js_base', $urlAppend . '/js');

$t->set_var('ebook_url_base', $ebook_url_base);
if (isset($back_section_id)) {
	$t->set_block('page', 'back_link_inactive', 'back_link_hide');
	$t->set_var('back_chapter_link', q($ebook_url_base . $back_section_id . '/' . $unit_parameter));
	$t->set_var('back_chapter_title', q($back_title));
} else {
	$t->set_block('page', 'back_link_active', 'back_link_hide');
}
if (isset($next_section_id)) {
	$t->set_block('page', 'next_link_inactive', 'next_link_hide');
	$t->set_var('next_chapter_link', q($ebook_url_base . $next_section_id . '/' . $unit_parameter));
	$t->set_var('next_chapter_title', q($next_title));
} else {
	$t->set_block('page', 'next_link_active', 'next_link_hide');
}

$ebook_body = '';
$ebook_head = '';
$dom = new DOMDocument();
$dom->loadHTMLFile($subsections[$current_sid][$current_ssid]['file']);
foreach ($dom->getElementsByTagName('link') as $element) {
	 $ebook_head .= $dom->saveXML($element);
}
foreach ($dom->getElementsByTagName('script') as $element) {
	 $ebook_head .= $dom->saveXML($element);
}
$body_node = $dom->getElementsByTagName('body')->item(0);
foreach ($body_node->childNodes as $element) {
	$ebook_body .= str_replace('&#13;', '', $dom->saveXML($element));
}
unset($dom);
$t->set_var('ebook_head', $ebook_head);
$t->set_var('ebook_body', $ebook_body);

$t->set_block('page', 'chapter_select_options', 'option_var');
foreach ($sections as $section_id => $section_title) {
	$t->set_var('chapter_title', $section_title);
	$t->set_var('chapter_id', $section_id);
	if ($sid == $current_sid) {
		$t->set_var('chapter_selected', ' selected="selected"');
	} else {
		$t->set_var('chapter_selected', '');
	}
	$t->parse('option_var', 'chapter_select_options', true);
}

if (count($subsections[$current_sid]) == 1) {
	$t->set_block('page', 'section_titles', 'section_titles_hide');
} else {
	$t->set_block('page', 'section_title_entry', 'section_var');
	foreach ($subsections[$current_sid] as $subsection_id => $info) {
		$t->set_var('section_link', $info['url']);
		$t->set_var('section_title', $info['title']);
		$t->set_var('class_active', $info['class']);
		$t->parse('section_var', 'section_title_entry', true);
	}
}

$t->pparse('Output', 'page');

function not_found($path)
{
        header("HTTP/1.0 404 Not Found");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
                '<title>404 Not Found</title></head><body>',
                '<h1>Not Found</h1><p>The requested path "',
                htmlspecialchars($file_path),
                '" was not found.</p></body></html>';
        restore_saved_course();
        exit;
}

// Restore current course
function restore_saved_course()
{
        if (defined('old_dbname')) {
                $_SESSION['dbname'] = old_dbname;
        }
}

