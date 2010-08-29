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
        header('Location: ' . $urlServer);
        exit;
}

$id = intval($_GET['id']);
 
$q = db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id AND public_id = $id");

if (mysql_num_rows($q) == 0) {
        $tool_content .= "<p class='alert1'>$langNoEBook</p>\n";
} else {
        $k = 0;
        $info = mysql_fetch_array($q);
        $html_files = find_html_files($webDir . 'courses/' . $currentCourseID . '/ebook/' . $id);
        sort($html_files, SORT_STRING);
        $q = db_query("SELECT ebook_section.id AS section_id,
                              ebook_section.title AS section_title,
                              ebook_subsection.id AS subsection_id,
                              ebook_subsection.title AS subsection_title,
                              ebook_subsection.file
                       FROM ebook_section, ebook_subsection
                       WHERE ebook_section.ebook_id = $info[id] AND
                             ebook_section.id = ebook_subsection.section_id
                       ORDER BY ebook_section.id, ebook_subsection.id");
        $tool_content .= "<form method='post' action='edit.php'>
                          <input type='hidden' name='id' value='$info[id]' /><br />
                          $langTitle: <input type='text' name='title' size='53' value='" . q($info['title']) . "' />
                          <table>
                             <tr><th>$langFileName</th><th>$langTitle</th><th>$langSection</th><th>$langSubsection</th></tr>\n";
        while ($r = mysql_fetch_array($q)) {
                $class = odd_even($k); 
                $tool_content .= "<tr$class><td><a href='show.php/$currentCourseID/" .
                                         display_id($r['section_id'], $r['subsection_id']) .
                                         "/'>" . q($r['file']) . "</a></td>
                                     <td><input type='text' name='title[]' size='30' value='" . q($r['title']) . "' /></td>
                                     <td><input type='text' name='sid[]' size='5' value='" . q($r['section_id']) . "' /></td>
                                     <td><input type='text' name='ssid[]' size='5' value='" . q($r['subsection_id']) . "' /></td></tr>\n";
                if (($file_key = array_search($r['file'], $html_files) !== false)) {
                        unset($html_files[$file_key]);
                }
                $k++;
        }
        foreach ($html_files as $file) {
                $class = odd_even($k); 
               $tool_content .= "<tr$class><td>" . q($file) . "</td>
                                     <td><input type='text' name='title[]' size='30' /></td>
                                     <td><input type='text' name='sid[]' size='5' /></td>
                                     <td><input type='text' name='ssid[]' size='5' /></td></tr>\n";

                $k++;
        }
        $tool_content .= "</table></form>\n";
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

function odd_even($k)
{
        if ($k % 2 == 0) {
                return " class='even'";
        } else {
                return " class='odd'";
        }
} 
