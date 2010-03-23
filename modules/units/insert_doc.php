<?
/*========================================================================
 *   Open eClass 2.3
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2010  Greek Universities Network - GUnet
 *  A full copyright notice can be read in "/info/copyright.txt".
 *
 *  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
 *			Yannis Exidaridis <jexi@noc.uoa.gr>
 *			Alexandros Diamantidis <adia@noc.uoa.gr>
 *			Tilemachos Raptis <traptis@noc.uoa.gr>
 *
 *  For a full list of contributors, see "credits.txt".
 *
 *  Open eClass is an open platform distributed in the hope that it will
 *  be useful (without any warranty), under the terms of the GNU (General
 *  Public License) as published by the Free Software Foundation.
 *  The full license can be read in "/info/license/license_gpl.txt".
 *
 *  Contact address: 	GUnet Asynchronous eLearning Group,
 *  			Network Operations Center, University of Athens,
 *  			Panepistimiopolis Ilissia, 15784, Athens, Greece
 *  			eMail: info@openeclass.org
 * =========================================================================*/


function display_docs()
{
        global $id, $currentCourseID, $webDir, $tool_content,
               $langDirectory, $langUp, $langName, $langSize, $langDate, $langType, $langAddModulesButton, $langChoice, $langNoDocuments;

        $basedir = $webDir . 'courses/' . $currentCourseID . '/document';
        if (isset($_GET['path'])) {
                $path = escapeSimple($_GET['path']);
                if ($path == '/') {
			$path = '';
		}
        } else {
                $path = "";
        }
        $result = db_query("SELECT * FROM document
                            WHERE path LIKE '$path/%'
                            AND path NOT LIKE '$path/%/%'", $currentCourseID);
        $fileinfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
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
                $tool_content .= "\n<p class='alert1'>$langNoDocuments</p>";
        } else {
                if (empty($path)) {
                        $dirname = '';
                        $parenthtml = '';
                        $colspan = 5;
                } else {
                        list($dirname) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                   WHERE path = '$path'"));
			$parentpath = dirname($path);
                        $dirname = "/".htmlspecialchars($dirname);
                        $parentlink = $_SERVER['PHP_SELF'] . "?type=doc&amp;id=$id&amp;path=" . $parentpath;
                        $parenthtml = "<th class='right'><a href='$parentlink'>$langUp</a> <a href='$parentlink'>" .
                                      "<img src='../../template/classic/img/parent.gif' height='20' width='20' /></a></th>";
                        $colspan = 4;
                }
        $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id' />" .
                                 "<div class='fileman'><table class='Documents'><tbody>" .
                                 "<tr><th colspan='$colspan' class='left'>$langDirectory: $dirname</th>" .
                                 $parenthtml . "</tr>\n" .
                                 "<tr><th>$langType</th><th>$langName</th><th>$langSize</th>" .
                                 "<th>$langDate</th><th>$langChoice</th></tr>\n";
	$counter = 0;
		foreach (array(true, false) as $is_dir) {
			foreach ($fileinfo as $entry) {
				if ($entry['is_dir'] != $is_dir) {
					continue;
				}
				$dir = $entry['path'];
				if ($is_dir) {
					$image = '../../template/classic/img/folder.gif';
					$file_url = "$_SERVER[PHP_SELF]?type=doc&amp;id=$id&amp;path=$dir";
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
						$vis = '';
					} else {
						$vis = 'odd';
					}
				}
				$tool_content .= "<tr class='$vis'>";
				$tool_content .= "<td width='1%' valign='top' style='padding-top: 7px;' align='center'>
					<a href='$file_url'$link_extra><img src='$image' border='0' /></a></td>";
				$tool_content .= "<td width='60%'><div align='left'>
					<a href='$file_url'$link_extra>$link_text</a>";
	
				/*** comments ***/
				if (!empty($entry['comment'])) {
					$tool_content .= "<br /><span class='comment'>" .
						nl2br(htmlspecialchars($entry['comment'])) .
						"</span>\n";
				}
				$tool_content .= "</div></td>";
				if ($is_dir) {
					// skip display of date and time for directories
					$tool_content .= "<td>&nbsp;</td><td>&nbsp;</td>";
				} else {
					$size = format_file_size($entry['size']);
					$date = format_date($entry['date']);
					$tool_content .= "<td class='center'>$size</td><td class='center'>$date</td>";
				}
					$tool_content .= "<td class='center'><input type='checkbox' name='document[]' value='$entry[id]' /></td>";
					$tool_content .= "</tr>";
				$counter++;
			}
		}
		$tool_content .= "<tr><td colspan=$colspan class='right'>";
		$tool_content .= "<input type='submit' name='submit_doc' value='$langAddModulesButton' /></td>";
                $tool_content .= "</tr></tbody></table></div></form>\n";
        }
}
