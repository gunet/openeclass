<?php
/* ========================================================================
 * Open eClass 3.0
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

/*
 * Edit, Course Description
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Actions for add/edit/delete portions of a course's descriptions
 *
 * Based on course units code
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/log.php';

$nameTools = $langEditCourseProgram;
$navigation[] = array ('url' => "index.php?course=$course_code", 'name' => $langCourseDescription);

if (isset($_REQUEST['numBloc'])) {        
        // Display block edit form (edit action)
        $numBloc = intval($_REQUEST['numBloc']);
        if (isset($titreBloc[$numBloc])) {
                $title = q($titreBloc[$numBloc]);
        }
        if (isset($title) and @!$titreBlocNotEditable[$numBloc]) {
               $edit_title = " value='$title'";
        } else {
               $edit_title = false;
        }
        if (isset($_POST['add']) and @!$titreBlocNotEditable[$numBloc]) {
                $numBloc = new_description_res_id(description_unit_id($course_id));
                $contentBloc = '';
        } else {
                $q = db_query("SELECT title, comments FROM unit_resources WHERE unit_id =
                                        (SELECT id FROM course_units WHERE course_id = $course_id AND `order` = -1)
                                        AND res_id = $numBloc");
                if ($q and mysql_num_rows($q)) {
                        list($title, $contentBloc) = mysql_fetch_row($q);
                        if ($edit_title) {
                               $edit_title = " value='$title'";
                        }
                } else {
                        $contentBloc = '';
                }
        }
        $tool_content .= "<form method='post' action='index.php?course=$course_code'>
                <input type='hidden' name='edIdBloc' value='$numBloc' />
                <fieldset>
                <table class='tbl'>
                <tr>
                <th width='100'>$langTitle:</th>";
        if ($edit_title) {
                $tool_content .= "<td><input type='text' name='edTitleBloc' $edit_title /></td></tr>";
        } else {
                $tool_content .= "<td><b>$title</b><input type='hidden' name='edTitleBloc' value='$title' /></td></tr>";
        }

        $tool_content .= "<tr>
           <th valign='top'>$langContent:</th>
           <td>".@rich_text_editor('edContentBloc', 4, 20, $contentBloc)."</td>
        </tr>
        <tr>
           <td>&nbsp;</td>
           <td class='right'><input class='Login' type='submit' name='save' value='$langAdd' />&nbsp;&nbsp;
              <input class='Login' type='submit' name='ignore' value='$langBackAndForget' />
           </td>
        </tr>
        </table>
      </fieldset>
      </form>\n";
} else {
        display_add_block_form();
}

draw($tool_content, 2, null, $head_content);


/**
 * Display form to add a new block
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $titreBloc
 * @global type $langAddCat
 * @global type $langAdd
 * @global type $langSelection
 * @global type $titreBlocNotEditable
 */
function display_add_block_form()
{
        global $course_id, $course_code, $tool_content, $titreBloc, $langAddCat, $langAdd, $langSelection, $titreBlocNotEditable;
        
        $q = db_query("SELECT res_id FROM unit_resources WHERE unit_id =
                                (SELECT id FROM course_units WHERE course_id = $course_id AND `order` = -1)
                       ORDER BY `order`");
        while ($row = mysql_fetch_row($q)) {
                if (@$titreBlocNotEditable[$row[0]]) {
                        $blocState[$row[0]] = true;
                }
        }

        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <input type='hidden' name='add' value='1' />
        <fieldset>
          <legend>$langAddCat</legend>
          <table class='tbl'>
          <tr>
            <th>$langSelection:</th>
            <td><select name='numBloc' size='1'>";
        while (list($numBloc,) = each($titreBloc)) {
                if (!isset($blocState[$numBloc])) {
                        $tool_content .= "<option value='$numBloc'>$titreBloc[$numBloc]</option>\n";
                }
        }
        $tool_content .= "\n</select></td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td><input type='submit' name='add' value='$langAdd' /></td>
          </tr>
          </table>
        </fieldset>
        </form>";
}