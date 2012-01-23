<?php
/* ========================================================================
 * Open eClass 2.4
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



function list_videos()
{
        global $id, $currentCourseID, $tool_content, $urlServer,
               $langTitle, $langDescr, $langDate, $langChoice,
               $langAddModulesButton, $langNoVideo, $code_cours,
               $themeimg, $cours_id, $mysqlMainDb; 

        $table_started = false;
        $count = 0;
        foreach (array('video', 'videolinks') as $table) {
                $result = db_query("SELECT * FROM $table WHERE course_id = $cours_id", $mysqlMainDb);
                $count += mysql_num_rows($result);
                $numLine=0;
                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        if (!$table_started) {
                                $tool_content .= "\n  <form action='insert.php?course=$code_cours' method='post'><input type='hidden' name='id' value='$id' />";
                                $tool_content .= "\n  <table class='tbl_alt' width='99%'>";
                        	$tool_content .= "\n  <tr>" .
                                                 "\n    <th><div align='left'>&nbsp;$langTitle</div></th>" .
                                                 "\n    <th><div align='left'>$langDescr</div></th>" .
                                                 "\n    <th width='100'>$langDate</th>" .
                                                 "\n    <th width='80'>$langChoice</th>" .
                                                 "\n  </tr>";
                                $table_started = true;
                        }
                        
                        if ($table == 'video') 
                        {
                            list($mediaURL, $mediaPath, $mediaPlay) = media_url($row['path']);

                            $videolink = choose_media_ahref($mediaURL, $mediaPath, $mediaPlay, q($row['title']), $row['path']);
                        }
                        else
                        {
                            $videolink = choose_medialink_ahref(q($row['url']), q($row['title']));
                        }

                          if ($numLine%2 == 0) {
                              $tool_content .= "\n  <tr class='even'>";
                          } else {
                              $tool_content .= "\n  <tr class='odd'>";
                          }

                        $tool_content .= "\n    <td>&nbsp;<img src='$themeimg/videos_on.png' />&nbsp;&nbsp;" . $videolink . "</td>".
                                         "\n    <td>" . htmlspecialchars($row['description']) . "</td>".
                                         "\n    <td class='center'>" . format_date(strtotime($row['date'])) . "</td>" .
                                         "\n    <td class='center'><input type='checkbox' name='video[]' value='$table:$row[id]' /></td>\n" .
                                         "\n  </tr>";
                $numLine++;
                }
        }
        if ($count > 0) {
                $tool_content .= "\n  <tr>\n    <th colspan='4'><div align='right'><input type='submit' name='submit_video' value='$langAddModulesButton' />&nbsp;&nbsp;</div>\n    </th>\n  </tr>\n  </table>\n  </form>";
        } else {
                $tool_content .= "\n  <p class='alert1'>$langNoVideo</p>";
        }
}
