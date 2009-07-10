<?
/*========================================================================
 *   Open eClass 2.1
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
               $langDirectory, $langUp, $langName, $langSize, $langDate, $m;

        $basedir = $webDir . 'courses/' . $currentCourseID . '/document';
        if (isset($_GET['path'])) {
                $path = escapeSimple($_GET['path']);
        } else {
                $path = "";
        }
        $result = db_query("SELECT * FROM document
                            WHERE path LIKE '$path/%'
                            AND path NOT LIKE '$path/%/%'", $currentCourseID);
        $fileinfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $fileinfo[] = array(
                        'is_dir' => is_dir($basedir . $row['path']),
                        'size' => filesize($basedir . $row['path']),
                        'title' => $row['title'],
                        'name' => htmlspecialchars($row['filename']),
                        'format' => $row['format'],
                        'path' => $row['path'],
                        'visible' => ($row['visibility'] == 'v'),
                        'comment' => $row['comment'],
                        'copyrighted' => $row['copyrighted'],
                        'date' => strtotime($row['date_modified']));
        }
        if (count($fileinfo) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoDocuments</p>";
        } else {
                if (empty($path)) {
                        $dirname = '/';
                        $parenthtml = '';
                        $colspan = 5;
                } else {
                        list($dirname) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                   WHERE path = '$path'"));
                        $parentdir = dirname($dirname);
                        $dirname = htmlspecialchars($dirname);
                        $parentlink = $_SERVER['PHP_SELF'] . "?type=doc&amp;id=$id&amp;path=" . $parentdir;
                        $parenthtml = "<th class='right'><a href='$parentlink'>$langUp</a> <a href='$parentlink'>" .
                                      "<img src='../../template/classic/img/parent.gif' height='20' width='20' /></a></th>";
                        $colspan = 4;
                }
                $tool_content .= "<form action='insert.php' method='post'>" .
                                 "<div class='fileman'><table class='Documents'><tbody>" .
                                 "<tr><th colspan='$colspan' class='left'>$langDirectory: $dirname</th>" .
                                 $parenthtml . "</tr>\n" .
                                 "<tr><th>$m[type]</th><th>$langName</th><th>$langSize</th>" .
                                 "<th>$langDate</th><th>&nbsp;</th></tr>\n";

                foreach ($fileinfo as $file) {
                        $tool_content .= "<tr><td>ICON</td><td>$file[name]</td><td>$file[size]</td><td>$file[date]</td><td>SELECT</td></tr>\n";
                }

                $tool_content .= "</tbody></table></div></form>\n";
        }
}
