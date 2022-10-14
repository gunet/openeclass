<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */
$require_current_course = TRUE;
$require_editor = true;
$require_help = true;
$helpTopic = 'assignments';
$helpSubTopic = 'scale';

include '../../include/baseTheme.php';

$toolName = $langGradeScales;
$pageName = $langGradeScales;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);

if (isset($_GET['delete'])) { // delete scale
    Database::get()->query("DELETE FROM `grading_scale` WHERE id = ?d", $_GET['delete']);
    //Session::Messages($langGradeScalesDeleted, 'alert-success');
    Session::flash('message',$langGradeScalesDeleted); 
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/work/grading_scales.php");
}

if (isset($_POST['submitScale'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array(
        'title' => "$langTheField $langTitle",
        'max_grade' => "$langTheField $m[max_grade]"
    ));
    $scale_id = isset($_POST['grading_scale_id']) ? $_POST['grading_scale_id'] : 0;
    if($v->validate()) {
        $title = $_POST['title'];
        $scales = array();
        foreach ($_POST['scale_item_name'] as $key => $item_name) {
            $scales[$key]['scale_item_name'] = $item_name;
            $scales[$key]['scale_item_value'] = $_POST['scale_item_value'][$key];
        }
        $serialized_scales = serialize($scales);
        if ($scale_id) {
            Database::get()->query("UPDATE grading_scale SET title = ?s, scales = ?s, course_id = ?d WHERE id = ?d", $title, $serialized_scales, $course_id, $_POST['grading_scale_id']);
            update_assignments_max_grade($scale_id);
        } else {
            Database::get()->query("INSERT INTO grading_scale (title, scales, course_id) VALUES (?s, ?s, ?d)", $title, $serialized_scales, $course_id);
        }
        redirect_to_home_page("modules/work/grading_scales.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grading_scales.php?course=$course_code&scale_id=$scale_id");
    }
}
if (isset($_GET['scale_id'])) {
    load_js('bootstrap-validator');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#addScale').on('click', function() {
                var rowCount = $('#scale_table tbody tr').length;
                $('#scale_table tbody').append(
                    '<tr>'+
                    '<td class=\'form-group\'>'+
                    '<input type=\'text\' name=\'scale_item_name[' + rowCount +']\' class=\'form-control\' value=\'\' required>'+
                    '</td>'+
                    '<td class=\'form-group\'>'+
                    '<input type=\'number\' name=\'scale_item_value[' + rowCount +']\' class=\'form-control\' value=\'\' min=\'0\' required>'+
                    '</td>'+
                    '<td class=\'text-center\'>'+
                    '<a href=\'#\' class=\'removeScale\'><span class=\'fa fa-times\' style=\'color:red\'></span></a>'+
                    '</td>'+
                    '</tr>'
                );
            });
            $('#scale_table tbody').on('click', 'a.removeScale', function() {
                $(this).closest('tr').remove();
                var i = 0;
                $('#scale_table tbody tr').each(function() {
                    $(this).find('td:first input').attr('name', 'scale_item_name[' + i + ']');
                    $(this).find('td:second input').attr('name', 'scale_item_value[' + i + ']');
                    i++;
                });
            });
        });
    </script>
    ";
    $scale_used = 0;
    if ($_GET['scale_id']) {
        $scale_data = Database::get()->querySingle("SELECT * FROM grading_scale WHERE id = ?d AND course_id = ?d", $_GET['scale_id'], $course_id);
        $scale_used = Database::get()->querySingle("SELECT COUNT(*) as count FROM `assignment`, `assignment_submit` "
                . "WHERE `assignment`.`grading_scale_id` = ?d AND `assignment`.`course_id` = ?d AND `assignment`.`id` = `assignment_submit`.`assignment_id` AND `assignment_submit`.`grade` IS NOT NULL", $_GET['scale_id'], $course_id)->count;
    }
    $title = Session::has('title') ? Session::get('title') : (isset($scale_data) ? $scale_data->title : "");
    $scale_rows = "";
    $hidden_input = "";
    if (isset($scale_data)) {
        $hidden_input .= "<input type='hidden' name='grading_scale_id' value='$scale_data->id'>";
        $unserialized_scales = unserialize($scale_data->scales);
        foreach ($unserialized_scales as $key => $scale) {
            $scale_rows .= "
                    <tr>
                        <td class='form-group'>
                            <input type='text' name='scale_item_name[$key]' class='form-control' value='".q($scale['scale_item_name'])."' required".($scale_used ? " disabled" : "").">
                        </td>
                        <td class='form-group'>
                            <input type='number' name='scale_item_value[$key]' class='form-control' value='$scale[scale_item_value]' min='0' required".($scale_used ? " disabled" : "").">
                        </td>";
            if (!$scale_used) {
                    $scale_rows .= "<td class='text-center'>
                                    <a href='#' class='removeScale'><span class='fa fa-times' style='color:red'></span></a>
                                </td>";
            }
            $scale_rows .= "</tr>";
        }
    }
    $toolName = $langGradeScales;
    $pageName = $langNewGradeScale;
    $navigation[] = array("url" => "grading_scales.php?course=$course_code", "name" => $langGradeScales);
    if ($scale_used) {
        $tool_content .= "<div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'><div class='alert alert-info'>$langGradeScaleNotEditable</div></div>";
    }
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "grading_scales.php?course=$course_code"
        ),
    ));
    $tool_content .= "
        
            <div class='col-12 mt-3'>
                <div class='form-wrapper form-edit p-3 rounded'>
                    <form class='form-horizontal' role='form' data-bs-toggle='validator' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' id='scales_form'>
                    <fieldset>
                        $hidden_input
                        
                        <div class='form-group".(Session::getError('title') ? " has-error" : "")." mt-3'>
                            <label for='title' class='col-sm-6 control-label-notes'>$langTitle:</label>
                            <div class='col-sm-12'>
                              <input name='title' type='text' class='form-control' id='title' value='$title'".($scale_used ? " disabled" : "").">
                              ".(Session::getError('title') ? "<span class='help-block'>" . Session::getError('title') . "</span>" : "")."
                            </div>
                        </div>

                       

                        <div class='form-group mt-3'>
                            <label class='col-sm-6 control-label-notes'>$langScales:</label>
                            <div class='col-sm-12'>
                                <div class='table-responsive mt-0'>
                                    <table class='table-default' id='scale_table'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th style='width:47%'>$langWording</th>
                                                <th style='width:47%'>$langValue</th>
                                                ".(!$scale_used ? "<th class='text-center option-btn-cell'  style='width:5%'>".icon('fa-gears')."</th>" : "")."
                                            </tr>
                                        </thead>
                                        <tbody>
                                            $scale_rows
                                        </tbody>
                                    </table>
                                </div>
                            </div>";
    if (!$scale_used) {
        $tool_content .= "<div class='col-12 mt-5 d-flex justify-content-center'>
                             <a class='btn btn-sm btn-success w-50 submitAdminBtn margin-top-thin' id='addScale'>$langAdd</a>
                         </div>";
    }
    $tool_content .= "</div>";
    if (!$scale_used) {
        $tool_content .= " 
     
                        <div class='form-group mt-5'>
                            <div class='col-12'>
                              <div class='row'>
                                <div class='col-6'>
                                  ".
                                  form_buttons(array(
                                      array(
                                          'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                          'text' => $langSave,
                                          'name' => 'submitScale'
                                      )
                                  ))
                                  ."
                                </div>
                                <div class='col-6'>
                                  ".
                                  form_buttons(array(
                                      array(
                                          'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                          'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                      )
                                  ))
                                  ."
                                </div>
                              </div>
                            </div>
                        </div>";
    }
    $tool_content .= "
                    </fieldset>
                    </form>
                </div>
            </div>";

} else {
    $tool_content .= action_bar(array(
        array(
            'title' => $langNewGradeScale,
            'level' => 'primary-label',
            'icon' => 'fa-plus-circle',
            'url' => "grading_scales.php?course=$course_code&amp;scale_id=0",
            'button-class' => 'btn-success'
        ),
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "index.php?course=$course_code"
        ),
    ),false);

    $grading_scales = Database::get()->queryArray("SELECT * FROM grading_scale WHERE course_id = ?d", $course_id);
    if ($grading_scales) {
        $table_content = "";
        foreach ($grading_scales as $grading_scale) {
            $scales = unserialize($grading_scale->scales);
            $scales_list = "";
            foreach ($scales as $scale) {
                $scales_list .= "<li>$scale[scale_item_name] ($scale[scale_item_value])</li>";
            }
            $table_content .= "
                        <tr>
                            <td style='padding-left:15px'>$grading_scale->title</td>
                            <td>
                                <ul class='list-unstyled'>
                                    $scales_list
                                </ul>
                            </td>
                            <td style='float:right' class='option-btn-cell'>
                            ". action_button(array(
                                    array(
                                        'title' => $langEdit,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;scale_id=$grading_scale->id",
                                        'icon' => 'fa-edit'
                                    ),
                                    array(
                                        'title' => $langDelete,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$grading_scale->id",
                                        'icon' => 'fa-times',
                                        'show' => !is_scale_used_in_assignment($grading_scale->id, $course_id),
                                        'class' => 'delete',
                                        'confirm' => $langConfirmDelete)
                                    ))."
                            </td>
                        </tr>
                        ";
        }
        $tool_content .= "

            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th style='padding-left:15px;'class='text-white'>$langTitle</th>
                            <th  class='text-white'>$langGradebookMEANS</th>
                            <th class='text-white text-end' style='padding-right:15px;'>" . icon('fa-cogs') . "</th>
                        </tr>
                    </thead>
                    <tbody>
                        $table_content
                    </tbody>
                </table>
            </div>";

    } else {
        $tool_content .= "<div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><div class='alert alert-warning'>$langNoGradeScales</div></div>";
    }
}
draw($tool_content, 2, null, $head_content);

/**
 * @param $scale_id
 */
function update_assignments_max_grade($scale_id) {
    $max_grade = max_grade_from_scale($scale_id);
    Database::get()->query("UPDATE assignment SET max_grade = ?f WHERE grading_scale_id = ?d", $max_grade, $scale_id);
}

/**
 * @param $scale_id
 * @return int
 */
function max_grade_from_scale($scale_id) {
    global $course_id;

    $scale_data = Database::get()->querySingle("SELECT * FROM grading_scale WHERE id = ?d AND course_id = ?d", $scale_id, $course_id);
    $unserialized_scale_items = unserialize($scale_data->scales);
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $item) {
        if ($item['scale_item_value'] > $max_scale_item_value) {
            $max_scale_item_value = $item['scale_item_value'];
        }
    }
    return $max_scale_item_value;
}

/**
 * @brief check if rubric is used in some assignment
 * @param $scale_id
 * @param $course_id
 * @return bool
 */
function is_scale_used_in_assignment($scale_id, $course_id) {

    $sql = Database::get()->querySingle("SELECT * FROM assignment WHERE grading_scale_id = ?d
                                                  AND grading_type = 1 
                                                  AND course_id = ?d", $scale_id, $course_id);
    if ($sql) {
        return TRUE;
    } else {
        return FALSE;
    }

}