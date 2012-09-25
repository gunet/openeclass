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


include '../document/doc_init.php';

function list_docs()
{
        global $id, $webDir, $currentCourseID, $cours_id, $tool_content,
               $group_sql, $langDirectory, $langUp, $langName, $langSize,
               $langDate, $langType, $langAddModulesButton, $langChoice,
               $langNoDocuments, $code_cours, $themeimg;

        $basedir = $webDir . 'courses/' . $currentCourseID . '/document';
        if (isset($_GET['path'])) {
                $path = escapeSimple($_GET['path']);
                if ($path == '/' or $path == '\\') {
			$path = '';
		}
        } else {
                $path = "";
        }
        $result = db_query("SELECT * FROM document
                            WHERE $group_sql AND
			          path LIKE '$path/%' AND
                                  path NOT LIKE '$path/%/%'");
        $fileinfo = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $fileinfo[] = array(
			'id' => $row['id'],
                        'is_dir' => is_dir($basedir . $row['path']),
                        'size' => filesize($basedir . $row['path']),
                        'title' => $row['title'],
                        'name' => htmlspecialchars($row['filename']),
                        'format' => $row['format'],
                        'path' => $row['path'],
                        'visible' => $row['visibility'],
                        'comment' => $row['comment'],
                        'copyrighted' => $row['copyrighted'],
                        'date' => strtotime($row['date_modified']));
        }
        if (count($fileinfo) == 0) {
                $tool_content .= "\n  <p class='alert1'>$langNoDocuments</p>\n";
        } else {
                if (empty($path)) {
                        $dirname = '';
                        $parenthtml = '';
                        $colspan = 5;
                } else {
                        list($dirname) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                   WHERE $group_sql AND path = '$path'"));
			$parentpath = dirname($path);
                        $dirname = "/".htmlspecialchars($dirname);
                        $parentlink = $_SERVER['SCRIPT_NAME'] . "?course=$code_cours&amp;type=doc&amp;id=$id&amp;path=" . $parentpath;
                        $parenthtml = "<th class='right'><a href='$parentlink'>$langUp</a> <a href='$parentlink'>" .
                                      "<img src='$themeimg/folder_up.png' height='16' width='16' alt='icon' /></a></th>";
                        $colspan = 4;
                }
		$tool_content .= "\n    <form action='insert.php?course=$code_cours' method='post'><input type='hidden' name='id' value='$id' />" .
                         "\n    <table class='tbl_alt' width='99%'>" .
                         "\n    <tr>".
                         "\n       <th colspan='$colspan'><div align='left'>$langDirectory: $dirname</div></th>" .
                                   $parenthtml . 
                         "\n    </tr>" .
                         "\n    <tr>" .
                         "\n      <th>$langType</th>" .
                         "\n      <th><div align='left'>$langName</div></th>" .
                         "\n      <th width='100'>$langSize</th>" .
                         "\n      <th width='80'>$langDate</th>" .
                         "\n      <th width='80'>$langChoice</th>" .
                         "\n    </tr>\n";
		$counter = 0;
		foreach (array(true, false) as $is_dir) {
			foreach ($fileinfo as $entry) {
				if ($entry['is_dir'] != $is_dir) {
					continue;
				}
				$dir = $entry['path'];
				if ($is_dir) {
					$image = $themeimg.'/folder.png';
					$file_url = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;type=doc&amp;id=$id&amp;path=$dir";
					$link_extra = '';
					$link_text = $entry['name'];
				} else {
					$image = '../document/img/' . choose_image('.' . $entry['format']);
					$file_url = file_url($entry['path'], $entry['name']);
					$link_extra = " target='_blank'";
					if (empty($entry['title'])) {
						$link_text = $entry['name'];
					} else {
						$link_text = $entry['title'];
					}
				}
				if ($entry['visible'] == 'i') { 
					$vis = 'invisible';
				} else {
					if ($counter % 2 == 0) {
						$vis = 'even';
					} else {
						$vis = 'odd';
					}
				}
				$tool_content .= "\n    <tr class='$vis'>";
				$tool_content .= "\n      <td width='1' class='center'><a href='$file_url'$link_extra><img src='$image' border='0' /></a></td>";
				$tool_content .= "\n      <td><a href='$file_url'$link_extra>$link_text</a>";
	
				/*** comments ***/
				if (!empty($entry['comment'])) {
					$tool_content .= "<br /><div class='comment'>" .
						standard_text_escape($entry['comment']) .
						"</div>\n";
				}
				$tool_content .= "</td>";
				if ($is_dir) {
					// skip display of date and time for directories
					$tool_content .= "\n      <td>&nbsp;</td>\n      <td>&nbsp;</td>";
				} else {
					$size = format_file_size($entry['size']);
					$date = format_date($entry['date']);
					$tool_content .= "\n      <td class='center'>$size</td>\n      <td class='center'>$date</td>";
				}
					$tool_content .= "\n      <td class='center'><input type='checkbox' name='document[]' value='$entry[id]' /></td>";
					$tool_content .= "\n    </tr>";
				$counter++;
			}
		}
		$tool_content .= "\n    <tr>\n      <th colspan=$colspan><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_doc' value='$langAddModulesButton' /></div></th>";
                $tool_content .= "\n    </tr>\n    </table>\n    </form>\n";
        }
}
