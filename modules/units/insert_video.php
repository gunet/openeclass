<?php
/* ========================================================================
 * Open eClass 2.8
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

require_once '../../include/lib/mediaresource.factory.php';
require_once '../../include/lib/multimediahelper.class.php';

function list_videos()
{
        global $id, $currentCourseID, $tool_content,
               $langTitle, $langDescr, $langDate, $langChoice,
               $langAddModulesButton, $langNoVideo, $code_cours,
               $themeimg, $langCatVideoDirectory; 

        $count = 0;
        $video_found = FALSE;
        $result = db_query("SELECT url, titre, description, category FROM video UNION SELECT url, titre, description, category FROM videolinks ", $currentCourseID);
        $count += mysql_num_rows($result);
        $numLine = 0;
        if ($count > 0) {
            $video_found = TRUE;
            $tool_content .= " <form action='insert.php?course=$code_cours' method='post'><input type='hidden' name='id' value='$id' />";
                        $tool_content .= "<table class='tbl_alt' width='99%'>";
                        $tool_content .= "<tr>" .
                                         "<th width='200'><div align='left'>&nbsp;$langTitle</div></th>" .
                                         "<th><div align='left'>$langDescr</div></th>" .
                                         "<th width='100'>$langDate</th>" .
                                         "<th width='80'>$langChoice</th>" .
                                         "</tr>";
            foreach (array('video', 'videolinks') as $table) {
                $result = db_query("SELECT * FROM $table WHERE (category IS NULL OR category = 0)", $currentCourseID);
                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $row['course_id'] = $GLOBALS['cours_id'];
                    if ($table == 'video') {
                        $vObj = MediaResourceFactory::initFromVideo($row);
                        $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                    } else {
                        $vObj = MediaResourceFactory::initFromVideoLink($row);
                        $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                    }
                    if ($numLine%2 == 0) {
                        $tool_content .= "<tr class='even'>";
                    } else {
                        $tool_content .= "<tr class='odd'>";
                    }
                    $tool_content .= "<td>&nbsp;<img src='$themeimg/videos_on.png' />&nbsp;&nbsp;" . $videolink . "</td>".
                                     "<td>" . htmlspecialchars($row['description']) . "</td>".
                                     "<td class='center'>" . nice_format($row['date'], true, true) . "</td>" .
                                     "<td class='center'><input type='checkbox' name='video[]' value='$table:$row[id]' /></td>\n" .
                                     "</tr>";
                    $numLine++;
                }
            }
            $sql = db_query("SELECT * FROM video_category ORDER BY name", $currentCourseID);
            if (mysql_num_rows($sql) > 0) {
                $tool_content .= "<tr class='odd'><td colspan='3' class='bold'>&nbsp;$langCatVideoDirectory</td></tr>";
                while ($videocat = mysql_fetch_array($sql, MYSQL_ASSOC)) {                    
                    $tool_content .= "<tr class='even'>";
                    $tool_content .= "<td><img src='$themeimg/folder_open.png' />&nbsp;&nbsp;" .
                                     q($videocat['name']) . "</td>";
                    $tool_content .= "<td colspan='2'>" . standard_text_escape($videocat['description']) . "</td>";
                    $tool_content .= "<td align='center'><input type='checkbox' name='videocatlink[]' value='$videocat[id]' /></td>";
                    $tool_content .= "</tr>";
                    foreach (array('video', 'videolinks') as $table) {
                        $sql2 = db_query("SELECT * FROM $table WHERE category = $videocat[id]", $currentCourseID);
                        while($linkvideocat = mysql_fetch_array($sql2, MYSQL_ASSOC)) {
                                $tool_content .= "<tr class='even'>";
                                $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat['url']) . "' target='_blank'>" .
                                        q(($linkvideocat['titre'] == '')? $linkvideocat['url']: $linkvideocat['titre']) . "</a></td>";
                                $tool_content .= "<td>" . standard_text_escape($linkvideocat['description']) . "</td>";
                                $tool_content .= "<td class='center'>" . nice_format($linkvideocat['date'], true, true) . "</td>";
                                $tool_content .= "<td align='center'><input type='checkbox' name='video[]' value='$table:$linkvideocat[id]' /></td>";
                                $tool_content .= "</tr>";	
                        }
                    }
                }
            }
        $tool_content .= "<tr><th colspan='4'><div align='right'><input type='submit' name='submit_video' value='".q($langAddModulesButton)."' />&nbsp;&nbsp;</div>\n    </th>\n  </tr>\n  </table>\n  </form>";
    }
    if (!$video_found) {
        $tool_content .= "<p class='alert1'>$langNoVideo</p>";
    }
}
