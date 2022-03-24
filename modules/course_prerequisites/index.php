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
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greeceαψτι
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'prequesities';

require_once '../../include/baseTheme.php';

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

$(document).ready(function () {
    $(document).on("click", ".delete_btn", function(e) {
        var link = $(this).attr("href");
        e.preventDefault();
        bootbox.confirm("$langDelWarnCoursePrerequisite", function(result) {
            if (result) {
                document.location.href = link;
            }
        });
    });
});

/* ]]> */
</script>
hContent;

$prereqs_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langCoursePrerequisites);
$toolName = $langCoursePrerequisites;

if (isset($_GET['add'])) {
    $pageName = $langNewCoursePrerequisite;
    $navigation[] = $prereqs_url;
    new_prereq();
} else {
    if (isset($_POST['addcommit'])) {
        $prereqId = intval($_POST['prerequisite_course']);
        add_prereq($prereqId);
    }
    if (isset($_GET['del'])) {
        $prereqId = intval($_GET['del']);
        del_prereq($prereqId);
    }

    show_prereqs();
}

draw($tool_content, 2, null, $head_content);

/////////////////
// FUNCTIONS  //
///////////////

function new_prereq() {
    global $tool_content, $head_content, $urlServer, $course_code,
           $langBack, $langCourse, $langSubmit, $langCancel, $langNote, $langNewCoursePrerequisiteHelp2;

    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "{$urlServer}modules/course_prerequisites/index.php?course=" . $course_code,
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ));

    load_js('select2');

    $head_content .= <<<hContent
        <script type="text/javascript">
        /* <![CDATA[ */
        
        $(document).ready(function () {
            $('#courses-select').select2({
                minimumInputLength: 2,
                tags: true,
                ajax: {
                  url: '{$urlServer}modules/course_prerequisites/coursefeed.php',
                  dataType: 'json'
                }
            });
        });
        
        /* ]]> */
        </script>
hContent;

    $tool_content .= "
    <div class='alert alert-info'><label>$langNote</label> $langNewCoursePrerequisiteHelp2</div>
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='index.php?course=" . $course_code . "'>
          <input type='hidden' name='addcommit' value='1'>
          <fieldset>
            <div class='form-group'>
              <label for='courses-select' class='col-sm-3 control-label'>$langCourse:</label>
              <div class='col-sm-9'>
                <select id='courses-select' class='form-control' name='prerequisite_course'></select>
              </div>
            </div>
            <div class='form-group'>
              <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmit) . "'>
                <a href='index.php?course=" . $course_code . "'' class='btn btn-default'>$langCancel</a>
              </div>
            </div>
          </fieldset>
          ". generate_csrf_token_form_field() ."
        </form>
      </div>
    ";
}

function add_prereq($prereqId) {
    global $course_id, $langNewCoursePrerequisiteSuccess, $langNewCoursePrerequisiteFailInvalid, $langNewCoursePrerequisiteFailSelf, $langNewCoursePrerequisiteFailAlreadyIn,
           $langNewCoursePrerequisiteFailBadgeMissing;

    // check invalid
    if ($prereqId <= 0) {
        Session::Messages($langNewCoursePrerequisiteFailInvalid, 'alert-danger');
        return;
    }

    // check the prereq same as current course
    if ($prereqId == $course_id) {
        Session::Messages($langNewCoursePrerequisiteFailSelf, 'alert-danger');
        return;
    }

    // check already exists
    $result = Database::get()->queryArray("SELECT cp.id
                                 FROM course_prerequisite cp 
                                 WHERE cp.course_id = ?d
                                 AND cp.prerequisite_course = ?d", $course_id, $prereqId);
    if (count($result) > 0) {
        Session::Messages($langNewCoursePrerequisiteFailAlreadyIn, 'alert-danger');
        return;
    }

    // check if badge for course completion exists
    $result = Database::get()->queryArray("SELECT id
                                 FROM badge  
                                 WHERE course_id = ?d
                                 AND bundle = -1 AND active = 1", $prereqId);
    if (!$result) {
        Session::Messages($langNewCoursePrerequisiteFailBadgeMissing, 'alert-danger');
        return;
    }

    Session::Messages($langNewCoursePrerequisiteSuccess, 'alert-success');
    Database::get()->query("INSERT INTO course_prerequisite (course_id, prerequisite_course) VALUES (?d, ?d)", $course_id, $prereqId);
}

function del_prereq($prereqId) {
    global $course_id, $langDelCoursePrerequisiteSuccess;

    if ($prereqId <= 0) {
        return;
    }

    Session::Messages($langDelCoursePrerequisiteSuccess, 'alert-success');
    Database::get()->query("DELETE FROM course_prerequisite WHERE course_id = ?d AND prerequisite_course = ?d", $course_id, $prereqId);
}

function show_prereqs() {
    global $tool_content, $course_id, $course_code, $urlServer,
           $langTitle, $langRemovePrerequisite, $langNoCoursePrerequisites,
           $langNewCoursePrerequisite, $langBack;

    $tool_content .= action_bar(array(
        array('title' => $langNewCoursePrerequisite,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
            'button-class' => 'btn-success',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "{$urlServer}courses/$course_code/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ));

    $result = Database::get()->queryArray("SELECT c.*
                                 FROM course_prerequisite cp 
                                 JOIN course c on (c.id = cp.prerequisite_course) 
                                 WHERE cp.course_id = ?d 
                                 ORDER BY c.title", $course_id);

    if (count($result) > 0) {
        $tool_content .= "
            <div class='row'><div class='col-sm-12'>
            <div class='table-responsive'><table class='table-default'>
                                  <tr class='list-header'>
                                      <th>$langTitle</th>
                                      <th class='text-center'>".icon('fa-gears')."</th>
                                  </tr>";
        foreach ($result as $row) {

            $cid = intval($row->id);
            $course_title = q($row->title . " (" . $row->public_code . ")");

            $tool_content .= "<tr>
            <td>$course_title</td>
            <td class='option-btn-cell'>" . action_button(
                    array(
                        array(
                            'title' => $langRemovePrerequisite,
                            'level' => 'primary',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del=" . $cid,
                            'icon' => 'fa-times',
                            'btn_class' => 'delete_btn btn-default'
                        )
                    )
                ) . "</td>
            </tr>" ;
        }

        $tool_content .= '</table></div></div></div>';
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoCoursePrerequisites</div>";
    }
}
