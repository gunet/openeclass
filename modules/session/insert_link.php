<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once 'include/course_settings.php';

/**
 * display available links (if any)
 */
function list_session_links($sid,$cid) {
    global $id, $course_id, $tool_content,
            $langNoCategory, $langAddModulesButton,
            $langChoice, $langNoLinksExist, $langLinks, $course_code, $langSocialCategory, $langSelect, $langOpenNewTab;

    $result = Database::get()->queryArray("SELECT * FROM link 
                                            WHERE course_id = ?d 
                                            AND id NOT IN (SELECT res_id FROM session_resources 
                                                            WHERE session_id = ?d
                                                            AND type = ?s)", $cid,$sid,'link');
    if (count($result) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoLinksExist</span></div></div>";
    } else {
        $tool_content .= "<form action='resource.php?course=$course_code&session=$sid' method='post'>
                <input type='hidden' name='id' value='$sid' />" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langLinks</th>" .
                "</tr></thead>";
        $sql = Database::get()->queryArray("SELECT * FROM link_category 
                                            WHERE course_id = ?d
                                            AND id NOT IN (SELECT category FROM link 
                                                            WHERE id IN (SELECT res_id FROM session_resources 
                                                                         WHERE session_id = ?d 
                                                                         AND type = ?s))", $cid,$sid,'link');
        if (count($sql) > 0) {
            foreach ($sql as $catrow) {
                if (!empty($catrow->description)) {
                    $description_text = "<div style='margin-top: 10px;'>" .  standard_text_escape($catrow->description) . "</div>";
                } else {
                    $description_text = '';
                }
                $tool_content .= "<tr>";
                $tool_content .= "<td><strong>". q($catrow->name) . "</strong>$description_text</td>";
                $tool_content .= "<td></td>";
                $tool_content .= "</tr>";
                $sql2 = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = ?d", $cid, $catrow->id);
                foreach ($sql2 as $linkcatrow) {
                    if (!empty($catrow->description)) {
                        $cat_description_text = "<div style='margin-top: 10px;'>" .  standard_text_escape($linkcatrow->description) . "</div>";
                    } else {
                        $cat_description_text = '';
                    }
                    $tool_content .= "<tr>";
                    $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='link[]' value='$linkcatrow->id'><span class='checkmark'></span></label></td>";
                    $tool_content .= "<td>".icon('fa-link')."&nbsp;&nbsp;<a href='" . q($linkcatrow->url) . "' target='_blank' aria-label='$langOpenNewTab'>" .
                            q(($linkcatrow->title == '') ? $linkcatrow->url : $linkcatrow->title) . "</a>$cat_description_text</td>";
                    $tool_content .= "</tr>";
                }
            }
        }
        $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = 0
                                                AND id NOT IN (SELECT res_id FROM session_resources 
                                                                WHERE session_id = ?d
                                                                AND type = ?s)", $cid,$sid,'link');
        $linkinfo = array();
        foreach ($result as $row) {
            $linkinfo[] = array(
                'id' => $row->id,
                'url' => $row->url,
                'title' => ($row->title == '') ? $row->url : $row->title,
                'comment' => $row->description,
                'category' => $row->category);
        }
        if (count($linkinfo) > 0) {
            $tool_content .= "<tr><td colspan='2'><strong>$langNoCategory</strong></td></tr>";
            foreach ($linkinfo as $entry) {
                if (!empty($entry['comment'])) {
                    $link_description_text = "<div style='margin-top: 10px;'>" .  standard_text_escape($entry['comment']) . "</div>";
                } else {
                    $link_description_text = '';
                }
                $tool_content .= "<tr><td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='link[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
                $tool_content .= "<td>&nbsp;&nbsp;" . icon('fa-link') . "&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target='_blank' aria-label='$langOpenNewTab'>" . q($entry['title']) . "</a>$link_description_text</td>";
                $tool_content .= "</tr>";
            }
        }
        if (setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $cid) == 1) {
            $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = -2
                                                    AND id NOT IN (SELECT res_id FROM session_resources 
                                                                    WHERE session_id = ?d
                                                                    AND type = ?s)", $cid,$sid,'link');
            $linkinfo = array();
            foreach ($result as $row) {
                $linkinfo[] = array(
                        'id' => $row->id,
                        'url' => $row->url,
                        'title' => ($row->title == '') ? $row->url : $row->title,
                        'comment' => $row->description,
                        'category' => $row->category);
            }
            if (count($linkinfo) > 0) {
                $tool_content .= "<tr><td colspan='2'><strong>$langSocialCategory</strong></td></tr>";
                foreach ($linkinfo as $entry) {
                    if (!empty($entry['comment'])) {
                        $sb_link_description_text = "<div style='margin-top: 10px;'>" .  standard_text_escape($entry['comment']) . "</div>";
                    } else {
                        $sb_link_description_text = '';
                    }
                    $tool_content .= "<tr><td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='link[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
                    $tool_content .= "<td>&nbsp;&nbsp;".icon('fa-link')."&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target=_blank>" . q($entry['title']) . "</a>$sb_link_description_text</td>";
                    $tool_content .= "</tr>";
                }
            }
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>" .
                "<input class='btn submitAdminBtn' type='submit' name='submit_link' value='$langAddModulesButton'></div></form>";
    }

    return $tool_content;
}

/**
 * @brief insert link in database
 * @param integer $sid
 */
function insert_session_link($sid) {
    global $course_id, $course_code;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
    if (isset($_POST['link']) and count($_POST['link']) > 0) {
        foreach ($_POST['link'] as $link_id) {
            $order++;
            $link = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
            $q = Database::get()->query("INSERT INTO session_resources SET session_id = ?d, type = 'link', title = ?s,
                                        comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                            $sid, $link->title, $link->description, $order, $link->id);
        }
    }

    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}
