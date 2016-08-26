<?php

// playmode is used in order to re-use this script's logic via play.php
$is_in_playmode = defined('SHOW_PHP__PLAY_MODE');

if (stripos($_SERVER['REQUEST_URI'], '%5c') !== false) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . str_ireplace('%5c', '/', $_SERVER['REQUEST_URI']));
    exit;
}
session_start();

// save current course
if (isset($_SESSION['dbname'])) {
    define('old_dbname', $_SESSION['dbname']);
}

$unit = false;
$file_path = false;
$full_url_found = false;
$show_orphan_file = false;
$uri = (!$is_in_playmode) ? preg_replace('/\?[^?]*$/', '', strstr($_SERVER['REQUEST_URI'], 'ebook/show.php')) :
        preg_replace('/\?[^?]*$/', '', strstr($_SERVER['REQUEST_URI'], 'ebook/play.php'));
$path_components = explode('/', $uri);
if (count($path_components) >= 4) {
    $_SESSION['dbname'] = $path_components[2];
    $ebook_id = intval($path_components[3]);
    if (!empty($path_components[4])) {
        if ($path_components[4] == '_') {
            $show_orphan_file = true;
        } else {
            if (preg_match('/^unit=([0-9]+)$/', $path_components[4], $matches)) {
                $unit = intval($matches[1]);
            } else {
                $ids = explode(',', $path_components[4]);
                $current_sid = intval($ids[0]);
                if (isset($ids[1])) {
                    $current_ssid = intval($ids[1]);
                    $full_url_found = true;
                    $current_display_id = $current_sid . ',' . $current_ssid;
                }
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
    $not_found = false;
} else {
    $not_found = true;
    $ebook_id = 0;
}

if ($not_found) {
    not_found($uri);
}

$require_current_course = true;
$guest_allowed = true;
define('EBOOK_DOCUMENTS', true);

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/document/doc_init.php';

doc_init();
$ebook_url_base = "{$urlServer}modules/ebook/show.php/$course_code/$ebook_id/";

if ($show_orphan_file and $file_path) {
    if (!preg_match('/\.html?$/i', $file_path)) {
        if (!$is_in_playmode)
            send_file_by_url_file_path($file_path);
        else {
            require_once 'include/lib/multimediahelper.class.php';

            $path_components = explode('/', str_replace('//', chr(1), $file_path));
            $file_info = public_path_to_disk_path($path_components, '');

            $mediaPath = file_url($file_info->path, $file_info->filename);
            $mediaURL = $urlServer . 'modules/ebook/document.php?course=' . $course_code . '&amp;ebook_id=' . $ebook_id . '&amp;download=' . $file_info->path;
            $token = token_generate($file_info->path, true);
            $mediaAccess = $mediaPath . '?token=' . $token;

            echo MultimediaHelper::mediaHtmlObjectRaw($mediaAccess, $mediaURL, $mediaPath);
            exit();
        }
    }
}

$pageName = $langEBook;
if ($unit !== false) {
    $exit_fullscreen_link = $urlAppend . "modules/units/index.php?course=$course_code&amp;id=$unit";
    $unit_parameter = 'unit=' . $unit;
} else {
    $exit_fullscreen_link = $urlAppend . "modules/ebook/index.php?course_code=$course_code";
    $unit_parameter = '';
}
$q = Database::get()->queryArray("SELECT ebook_section.id AS sid,
                      ebook_section.public_id AS psid,
                      ebook_section.title AS section_title,
                      ebook_subsection.id AS ssid,
                      ebook_subsection.public_id AS pssid,
                      ebook_subsection.title AS subsection_title,
                      document.path as path,
                      document.filename as filename
               FROM ebook, ebook_section, ebook_subsection, document
               WHERE ebook.id = $ebook_id AND
                     ebook.course_id = $course_id AND
                     ebook_section.ebook_id = ebook.id AND
                     ebook_section.id = ebook_subsection.section_id AND
                     document.id = ebook_subsection.file_id AND
                     document.course_id = $course_id AND
                     document.subsystem = $subsystem
                     ORDER BY CONVERT(psid, UNSIGNED), psid,
                              CONVERT(pssid, UNSIGNED), pssid");
if (!$q) {
    not_found($uri);
}
$last_section_id = null;
$sections = array();
foreach ($q as $row) {
    $url_filename = public_file_path($row->path, $row->filename);
    $sid = $row->sid;
    $ssid = $row->ssid;
    if (!isset($current_sid)) {
        $current_sid = $sid;
    }
    if ($current_sid == $sid and !isset($current_ssid)) {
        $current_ssid = $row->ssid;
    }
    if (!isset($current_display_id) and isset($current_sid) and isset($current_ssid)) {
        $current_display_id = $sid . ',' . $ssid;
    }
    $display_id = $sid . ',' . $ssid;
    if ($last_section_id != $row->sid) {
        $sections[] = array('id' => $display_id,
            'title' => $row->section_title,
            'current' => false,
            'indent' => false);
    }
    $sections[] = array('id' => $display_id,
        'title' => $row->subsection_title,
        'indent' => true,
        'current' => (isset($current_display_id) and $current_display_id == $display_id));
    $subsection_path[$sid][$ssid] = $row->path;
    if (isset($last_display_id) and isset($current_display_id)) {
        if ($current_display_id == $display_id) {
            $back_section_id = $last_display_id;
            $back_title = $last_title;
        } elseif ($current_display_id == $last_display_id) {
            $next_section_id = $display_id;
            $next_title = $row->subsection_title;
        }
    }
    $last_section_id = $sid;
    $last_display_id = $display_id;
    $last_title = $row->subsection_title;
}

if (!$full_url_found and !$show_orphan_file) {
    header('Location: ' . $ebook_url_base . $current_display_id . '/' . $unit_parameter);
    exit;
}

if ($file_path) {
    $initial_path = preg_replace('#/[^/]*$#', '', $subsection_path[$current_sid][$current_ssid]);
    if (!preg_match('/\.html?$/i', $file_path)) {
        send_file_by_url_file_path($file_path, $initial_path);
    } else {
        $path_components = explode('/', str_replace('//', chr(1), $file_path));
        $file_info = public_path_to_disk_path($path_components, $initial_path);
        $subsection_path[$current_sid][$current_ssid] = $file_info->path;
    }
}

$t = new Template();
$t->set_root($webDir . '/template/' . $theme);
$t->set_file('page', 'ebook_fullscreen.html');
$t->set_var('URL_PATH', $urlAppend);
$t->set_var('langBack', $langLeave);
$t->set_var('page_title', q($currentCourseName . ': ' . $pageName));
$t->set_var('course_title', q($currentCourseName));
$t->set_var('course_title_short', q(ellipsize($currentCourseName, 35)));
$t->set_var('course_home_link', $urlAppend . 'courses/' . $course_code . '/');
$t->set_var('course_ebook', $langEBook);
$t->set_var('course_ebook_link', $urlAppend . 'modules/ebook/index.php?course=' . $course_code);
$t->set_var('exit_fullscreen_link', $exit_fullscreen_link);
$t->set_var('unit_parameter', $unit_parameter);
$t->set_var('template_base', $urlAppend . 'template/' . $theme . '/');
$t->set_var('img_base', $themeimg);
$t->set_var('js_base', $urlAppend . 'js');

$t->set_var('ebook_url_base', $ebook_url_base);
if (!$show_orphan_file) {
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
}

$ebook_body = '';
$ebook_head = '';
$dom = new DOMDocument();
@$dom->loadHTMLFile($basedir . $subsection_path[$current_sid][$current_ssid]);

if (isset($_SESSION['glossary_terms_regexp']) and !empty($_SESSION['glossary_terms_regexp'])) {
    $xpath = new DOMXpath($dom);
    $textNodes = $xpath->query('//text()');
    foreach ($textNodes as $textNode) {
        if (!empty($textNode->data)) {
            $new_contents = glossary_expand($textNode->data);
            if ($new_contents != $textNode->data) {
                $newdoc = new DOMDocument();
                $newdoc->loadXML('<span>' . $new_contents . '</span>', LIBXML_NONET|LIBXML_DTDLOAD|LIBXML_DTDATTR);
                $newnode = $dom->importNode($newdoc->getElementsByTagName('span')->item(0), true);
                $textNode->parentNode->replaceChild($newnode, $textNode);
            }
        }
    }
}

foreach (array('link', 'style', 'script') as $tagname) {
    foreach ($dom->getElementsByTagName($tagname) as $element) {
        $ebook_head .= str_replace(array('<![CDATA[', ']]>'), array('', ''), dom_save_html($dom, $element));
    }
}
$body_node = $dom->getElementsByTagName('body')->item(0);
foreach ($body_node->childNodes as $element) {
    $ebook_body .= str_replace('&#13;', '', dom_save_html($dom, $element));
}
unset($dom);
$t->set_var('ebook_head', $ebook_head);
$t->set_var('ebook_body', $ebook_body);

$t->set_block('page', 'chapter_select_options', 'option_var');
if (!$show_orphan_file) {
    foreach ($sections as $section_info) {
        $t->set_var('chapter_title', ($section_info['indent'] ? '&nbsp;&nbsp;&nbsp;' : '') .
                q(ellipsize($section_info['title'], 40)));
        $t->set_var('chapter_id', $section_info['id']);
        if ($section_info['current']) {
            $t->set_var('chapter_selected', ' selected="selected"');
        } else {
            $t->set_var('chapter_selected', '');
        }
        $t->parse('option_var', 'chapter_select_options', true);
    }
} else {
    $t->set_block('page', 'chapter_select', 'delete');
    $t->set_block('page', 'back_link_active', 'delete');
    $t->set_block('page', 'back_link_inactive', 'delete');
    $t->set_block('page', 'next_link_active', 'delete');
    $t->set_block('page', 'next_link_inactive', 'delete');
}

$ebook_title = Database::get()->querySingle("SELECT title FROM ebook WHERE id = ?d", $ebook_id)->title;
$t->set_var('ebook_title', q($ebook_title));
$t->set_var('ebook_title_short', q(ellipsize($ebook_title, 35)));

if (get_config('ext_analytics_enabled') and $html_footer = get_config('ext_analytics_code')) {
    $t->set_var('HTML_FOOTER', $html_footer);
}

$t->pparse('Output', 'page');

/**
 * 
 * @global type $basedir 
 * @param type $file_path
 * @param type $initial_path
 */
function send_file_by_url_file_path($file_path, $initial_path = '') {
    global $basedir;

    $path_components = explode('/', str_replace('//', chr(1), $file_path));
    $file_info = public_path_to_disk_path($path_components, $initial_path);

    if (!send_file_to_client($basedir . $file_info->path, $file_info->filename, null, false)) {
        not_found($file_path);
    }
    exit;
}
