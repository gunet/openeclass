<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


/**
 * @brief display list of available wikis (if any)
 * @global type $id
 * @global type $course_id
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langWikis
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langWikiNoWiki
 * @global type $langWikiDescriptionForm
 * @global type $course_code
 */
function list_wikis() {
    global $id, $course_id, $tool_content, $urlServer,
    $langWikis, $langAddModulesButton, $langChoice, $langWikiNoWiki,
    $langWikiDescriptionForm, $course_code;


    $result = Database::get()->queryArray("SELECT * FROM wiki_properties WHERE group_id = 0 AND course_id = ?d", $course_id);
    $wikiinfo = array();
    foreach ($result as $row) {
        $wikiinfo[] = array(
            'id' => $row->id,
            'title' => $row->title,
            'description' => $row->description);
    }
    if (count($wikiinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langWikiNoWiki</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>
                <input type='hidden' name='id' value='$id'>
                <table class='table-default'>
                    <tr class='list-header'>
                        <th class='text-leftt'>$langWikis</th>
                        <th>$langWikiDescriptionForm</th>
                        <th>$langChoice</th>
                    </tr>";        
        foreach ($wikiinfo as $entry) {
            $tool_content .= "<tr><td>&nbsp;".icon('fa-wikipedia')."&nbsp;&nbsp;<a href='${urlServer}modules/wiki/page.php?course=$course_code&amp;wikiId=$entry[id]&amp;action=show'>$entry[title]</a></td>
                                <td>$entry[description]</td>
                                <td align='center'><input type='checkbox' name='wiki[]' value='$entry[id]'></td>
                            </tr>";            
        }
        $tool_content .= "
                    </table>
                <div class='text-right'>
                    <input class='btn btn-primary' type='submit' name='submit_wiki' value='$langAddModulesButton'>
                </div></form>";
    }
}
