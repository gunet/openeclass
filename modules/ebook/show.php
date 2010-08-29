<?php

session_start();
$unit = false;
$file_path = false;
$full_url_found = false;
$uri = strstr($_SERVER['REQUEST_URI'], 'ebook/show.php');
$path_components = explode('/', $uri);
if (count($path_components) >= 3) {
	$_SESSION['dbname'] = $path_components[2];
	$current_display_id = $path_components[3];
	@list($current_section_id, $current_subsection_id) = explode(',', $current_display_id);
	$current_section_id = intval($current_section_id);
	if (isset($current_subsection_id)) {
		$current_subsection_id = intval($current_subsection_id);
	}
	$full_url_found = true;
	if (preg_match('/^unit=([0-9]+)$/', $path_components[4], $matches)) {
		$unit = intval($matches[1]);
	} elseif (array_search('..', $path_components) === false) {
		$file_path = implode('/', array_slice($path_components, 4));
	}
}

$require_current_course = TRUE;
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';

$ebook_url_base = "{$urlServer}modules/ebook/show.php/$currentCourseID/";

$nameTools = 'Ηλεκτρονικό Βιβλίο';

if ($unit !== false) {
	$exit_fullscreen_link = $urlAppend . '/modules/units/?id=' . $unit;
	$unit_parameter = 'unit=' . $unit;
} else {
	$exit_fullscreen_link = $urlAppend . '/courses/' . $currentCourseID . '/';
	$unit_parameter = '';
}

$q = db_query("SELECT ebook_section.id AS section_id,
                      ebook_section.title AS section_title,
                      ebook_subsection.id AS subsection_id,
                      ebook_subsection.title AS subsection_title,
                      ebook_subsection.file
               FROM ebook, ebook_section, ebook_subsection
	       WHERE ebook.id = ebook_section.ebook_id AND
                     ebook.course_id = $cours_id AND
                     ebook_section.id = ebook_subsection.section_id
               ORDER BY ebook_section.id, ebook_subsection.id");
while ($row = mysql_fetch_array($q)) {
	$section_id = $row['section_id'];
	if (!isset($current_section_id)) {
		$current_section_id = $section_id;
	}
	if ($current_section_id == $section_id and !isset($current_subsection_id)) {
		$current_subsection_id = $row['subsection_id'];
	}
	if ($row['file'] == $file_path) {
		$file_path = false;
		$current_subsection_id = $row['subsection_id'];
	}
	if (!isset($last_section_id) or $last_section_id != $section_id) {
		$display_id = $section_id;
	} else {
		$display_id = $section_id . ',' . $row['subsection_id'];
	}
	if (!isset($current_display_id)) {
		$current_display_id = $display_id;
	}
	$sections[$section_id] = $row['section_title'];
	if ($current_display_id == $display_id) {
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
	$subsections[$section_id][$row['subsection_id']] = array(
		'title' => $row['subsection_title'],
		'file' => "{$webDir}courses/$currentCourseID/ebook/$section_id/$file",
		'url' => $ebook_url_base . $display_id . '/' . $unit_parameter . $fragment,
		'class' => $class_active);
	if (isset($last_display_id)) {
		if ($current_display_id == $display_id) {
			$back_section_id = $last_display_id;
			$back_title = $last_title;
		} elseif ($current_display_id == $last_display_id) {
			$next_section_id = $display_id;
			$next_title = $row['subsection_title'];
		}
	}	
	$last_section_id = $section_id;
	$last_display_id = $display_id;
	$last_title = $row['subsection_title'];
}

if (!$full_url_found) {
	header('Location: ' . $ebook_url_base . $current_section_id . '/');
	exit;
}

if ($file_path) {
	if (!send_file_to_client("{$webDir}courses/$currentCourseID/ebook/$current_section_id/$file_path",
		                 $file_path, true, false)) {
		header("HTTP/1.0 404 Not Found");
                echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
                     '<title>404 Not Found</title></head><body>',
                     '<h1>Not Found</h1><p>The requested path "',
                     htmlspecialchars($file_path),
                     '" was not found.</p></body></html>';

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
$t->set_var('img_base', $urlAppend . '/template/classic/img/ebook/');

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
$dom->loadHTMLFile($subsections[$current_section_id][$current_subsection_id]['file']);
foreach ($dom->getElementsByTagName('link') as $element) {
	 $ebook_head .= $dom->saveXML($element);
}
foreach ($dom->getElementsByTagName('script') as $element) {
	 $ebook_head .= $dom->saveXML($element);
}
$body_node = $dom->getElementsByTagName('body')->item(0);
foreach ($body_node->childNodes as $element) {
	$ebook_body .= $dom->saveXML($element);
}
unset($dom);
$t->set_var('ebook_head', $ebook_head);
$t->set_var('ebook_body', $ebook_body);

$t->set_block('page', 'chapter_select_options', 'option_var');
foreach ($sections as $section_id => $section_title) {
	$t->set_var('chapter_title', $section_title);
	$t->set_var('chapter_id', $section_id);
	if ($section_id == $current_section_id) {
		$t->set_var('chapter_selected', ' selected="selected"');
	} else {
		$t->set_var('chapter_selected', '');
	}
	$t->parse('option_var', 'chapter_select_options', true);
}

if (count($subsections[$current_section_id]) == 1) {
	$t->set_block('page', 'section_titles', 'section_titles_hide');
} else {
	$t->set_block('page', 'section_title_entry', 'section_var');
	foreach ($subsections[$current_section_id] as $subsection_id => $info) {
		$t->set_var('section_link', $info['url']);
		$t->set_var('section_title', $info['title']);
		$t->set_var('class_active', $info['class']);
		$t->parse('section_var', 'section_title_entry', true);
	}
}

$t->pparse('Output', 'page');
