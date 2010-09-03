<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';
$guest_allowed = true;

include '../../include/baseTheme.php';

mysql_select_db($mysqlMainDb);

$nameTools = $langEBookEdit;

if (!$is_adminOfCourse) {
        redirect_to_home_page();
}

if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
} else {
        redirect_to_home_page();
}

if (isset($_POST['delete'])) {
        db_query("DELETE FROM ebook_section WHERE ebook_id = $id AND id = " . autoquote($_POST['delete']));
} elseif (isset($_POST['new_section_submit'])) {
        if (isset($_POST['csid'])) {
                db_query("UPDATE ebook_section
                                 SET public_id = " . autoquote($_POST['new_section_id']) . ",
                                     title = " . autoquote($_POST['new_section_title']) . "
                                 WHERE ebook_id = $id AND id = " . intval($_POST['csid']));
        } else {
                db_query("INSERT INTO ebook_section SET ebook_id = $id,
                                                        public_id = " . autoquote($_POST['new_section_id']) . ",
                                                        title = " . autoquote($_POST['new_section_title']));
        }
        header("Location: " . $urlAppend . '/modules/ebook/edit.php?id=' . $id);
        exit;
} elseif (isset($_POST['title_submit'])) {
        $info = mysql_fetch_array(db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id AND id = $id"));
        $ebook_title = trim(autounquote($_POST['ebook_title']));
        if (!empty($ebook_title) and $info['title'] != $ebook_title) {
                db_query("UPDATE `ebook` SET title = " . quote($ebook_title) . " WHERE id = $info[id]");
        }
} elseif (isset($_POST['submit'])) {
        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $id;
        $html_files = find_html_files($basedir);
        sort($html_files, SORT_STRING);
        db_query("DELETE FROM ebook_subsection WHERE section_id IN (SELECT id FROM ebook_section WHERE ebook_id = $id)");
        foreach ($_POST['sid'] as $key => $sid) {
                if (!empty($sid)) {
                        $sid = intval($sid);
                        $qssid = quote($_POST['ssid'][$key]);
                        $qtitle = quote($_POST['title'][$key]);
                        $qfile = quote($html_files[$key]);
                        db_query("INSERT INTO ebook_subsection
                                         SET section_id = $sid,
                                             public_id = $qssid,
                                             title = $qtitle,
                                             file = $qfile");
                }
        }
} 

$q = db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id AND id = $id");

if (mysql_num_rows($q) == 0) {
        $tool_content .= "<p class='alert1'>$langNoEBook</p>\n";
} else {
        $info = mysql_fetch_array($q);
        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $id;
        $k = 0;
        $html_files = find_html_files($basedir);
        sort($html_files, SORT_STRING);
        $tool_content .= "<form method='post' action='edit.php'>
                          <p>
                          <input type='hidden' name='id' value='$id' /><br />
                          $langTitle: <input type='text' name='ebook_title' size='53' value='" . q($info['title']) . "' />
                          <input name='title_submit' type='submit' value='$langModify' />
                          </p>
                          </form>

                          <form method='post' action='edit.php'>
                          <input type='hidden' name='id' value='$id' /><br />
                          <table>
                             <tr><th>αρ. ενότητας</th><th>τίτλος ενότητας</th><th>&nbsp;</th></tr>\n";
        $q = db_query("SELECT id, public_id, title FROM ebook_section
                       WHERE ebook_id = $info[id]
                       ORDER BY public_id");
        $sections = array('' => '---');
        if (isset($_GET['s'])) {
                $edit_section = $_GET['s'];
        } else {
                $edit_section = null;
        }
        $section_editing = false;
        while ($section = mysql_fetch_array($q)) {
                $sid = $section['id'];
                $qsid = q($section['public_id']);
                $qstitle = q($section['title']);
                $sections[$sid] = $qsid . '. ' . $section['title'];
                if ($sid === $edit_section) {
                        $section_id = "<input type='hidden' name='csid' value='$sid' />" .
                                      "<input type='text size='5' name='new_section_id' value='$qsid' />";
                        $section_title = "<input type='text size='5' name='new_section_title' value='$qstitle' />";
                        $section_editing = true;
                } else {
                        $section_id = $qsid;
                        $section_title = $qstitle;
                }
                $tool_content .= "<tr><td>$section_id</td><td>$section_title</td><td><input type='image'
                                     src='../../template/classic/img/delete.gif'
                                     alt='$langDelete' title='$langDelete' name='delete' value='$sid'
                                     onclick=\"javascript:if(!confirm('".
                                         js_escape(sprintf($langEBookSectionDelConfirm, $section['title'])) ."')) return false;\"" .
                                     " />
                                     &nbsp;<a href='edit.php?id=$id&amp;s=$sid'><img
                                     src='../../template/classic/img/edit.gif'
                                     alt='$langModify' title='$langModify' /></a></td></tr>\n";
        }
        if (!$section_editing) {
                $tool_content .= "<tr><td><input type='text' size='5' name='new_section_id' /></td>
                                      <td><input type='text' size='35' name='new_section_title' /></td><td>&nbsp;</td></tr>";
        }
        $tool_content .= "<tr><td colspan='3' class='right'><input type='submit' name='new_section_submit' value='$langAdd' /></td></tr>
                          </table>
                          <table>
                             <tr><th>$langFileName</th><th>$langTitle</th><th>$langSection</th><th>$langSubsection</th></tr>\n";
        $q = db_query("SELECT ebook_section.id AS sid,
                              ebook_section.id AS psid,
                              ebook_section.title AS section_title,
                              ebook_subsection.id AS ssid,
                              ebook_subsection.public_id AS pssid,
                              ebook_subsection.title AS subsection_title,
                              ebook_subsection.file
                       FROM ebook_section, ebook_subsection
                       WHERE ebook_section.ebook_id = $info[id] AND
                             ebook_section.id = ebook_subsection.section_id
                             ORDER BY CONVERT(psid, UNSIGNED), psid,
                                      CONVERT(pssid, UNSIGNED), pssid");
        while ($r = mysql_fetch_array($q)) {
                $class = odd_even($k); 
                $key = array_search($r['file'], $html_files);
                $display_id = $r['sid'] . ',' . $r['ssid'];
                $tool_content .= "<tr$class><td><a href='show.php/$currentCourseID/$display_id/'
                                                   target='_blank'>" . q($r['file']) . "</a></td>
                                     <td><input type='text' name='title[$key]' size='30' value='" . q($r['subsection_title']) . "' /></td>
                                     <td>" . selection($sections, "sid[$key]", $r['sid']) . "</td>
                                     <td><input type='text' name='ssid[$key]' size='5' value='" . q($r['pssid']) . "' /></td></tr>\n";
                if ($key !== false) {
                        unset($html_files[$key]);
                }
                $k++;
        }
        foreach ($html_files as $key => $file) {
                $class = odd_even($k); 
                $title = get_html_title($basedir . '/' . $file);
                $tool_content .= "<tr$class><td><a href='show.php/$currentCourseID/$id/_/" . q($file) .
                                      "' target='_blank'>" . q($file) . "</a></td>
                        <td><input type='text' name='title[$key]' size='30' value='" . q($title) . "' /></td>
                        <td>" . selection($sections, "sid[$key]") . "</td>
                        <td><input type='text' name='ssid[$key]' size='5' /></td></tr>\n";

                $k++;
        }
        $tool_content .= "</table><input type='submit' name='submit' value='$langSubmit' /></form>\n";
}

draw($tool_content, 2, '', $head_content);

function find_html_files($base, $prepend = '')
{
        $files = array();
        if ($handle = opendir($base)) {
                while (($file = readdir($handle)) !== false){
                        if (is_dir($base . '/' . $file) and $file != '.' and $file != '..'){
                                $files = array_merge($files, find_html_files($base . '/' . $file, $file . '/'));
                        } elseif (preg_match('/\.html?$/i', $file)) {
                                $files[] = $prepend . $file;
                        }
                }
                closedir($handle);
        }
        return $files;
}

function get_html_title($file)
{
        $dom = new DOMDocument();
        @$dom->loadHTMLFile($file);
        $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        return html_entity_decode($title, ENT_QUOTES, 'UTF-8');
}
