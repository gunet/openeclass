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


/**
 * @brief display list of available wikis (if any)
 */
function list_wikis() {
    global $id, $course_id, $tool_content, $urlServer,
    $langWikis, $langAddModulesButton, $langChoice, $langWikiNoWiki, $course_code, $langSelect;


    $result = Database::get()->queryArray("SELECT * FROM wiki_properties WHERE group_id = 0 AND course_id = ?d", $course_id);
    $wikiinfo = array();
    foreach ($result as $row) {
        $wikiinfo[] = array(
            'id' => $row->id,
            'title' => $row->title,
            'description' => $row->description);
    }
    if (count($wikiinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langWikiNoWiki</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>
                <input type='hidden' name='id' value='$id'>
                <div class='table-responsive'><table class='table-default'>
                <thead>
                    <tr class='list-header'>
                        <th>$langChoice</th>
                        <th>$langWikis</th>
                    </tr></thead>";
        foreach ($wikiinfo as $entry) {
            if (!empty($entry['description'])) {
                $description_text = "<div>" .  $entry['description']. "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr>
                                <td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='wiki[]' value='$entry[id]'><span class='checkmark'></span></label></td>
                                <td><a href='{$urlServer}modules/wiki/page.php?course=$course_code&amp;wikiId=$entry[id]&amp;action=show'>$entry[title]</a>
                                $description_text</td>
                            </tr>";
        }
        $tool_content .= "</table></div>
                <div class='d-flex justify-content-start mt-4'>
                    <input class='btn submitAdminBtn' type='submit' name='submit_wiki' value='$langAddModulesButton'>
                </div>
            </form>";
    }
}
