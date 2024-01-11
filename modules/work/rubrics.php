<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
$helpSubTopic = 'rubric';
include '../../include/baseTheme.php';

$toolName = $langGradeRubrics;

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);

if (isset($_GET['clone'])) {
    clone_rubric($_GET['rubric_id'], $_POST['clone_to_course_id']);
    Session::Messages($langCopySuccess, 'alert-success');
    redirect_to_home_page('modules/work/rubrics.php?course=' . $course_code);
}

$my_courses = Database::get()->queryArray("SELECT a.course_id Course_id, b.title Title 
            FROM course_user a, course b 
            WHERE a.course_id = b.id 
            AND a.course_id != ?d 
            AND a.user_id = ?d 
            AND a.status = " . USER_TEACHER, $course_id, $uid);
$courses_options = "";
foreach ($my_courses as $row) {
    $courses_options .= "'<option value=\"$row->Course_id\">" . js_escape($row->Title) . "</option>'+";
}

// rubric cloning bootbox
$head_content .= "<script type='text/javascript'>
        $(document).on('click', '.warnLink', function() {
            var rubric_id = $(this).data('rubric-id');
            bootbox.dialog({
                title: '" . js_escape($langCreateDuplicateIn) . "',
                message: '<form action=\"$_SERVER[SCRIPT_NAME]\" method=\"post\" id=\"clone_form\">'+
                            '<select class=\"form-control\" id=\"course_id\" name=\"clone_to_course_id\">'+
                                '<option value=\"$course_id\">--- " . js_escape($langCurrentCourse) . " ---</option>'+
                                $courses_options
                            '</select>'+
                          '</form>',
                    buttons: {
                        cancel: {
                            label: '" . js_escape($langCancel) . "',
                            className: 'btn-default'
                        },
                        success: {
                            label: '" . js_escape($langCreateDuplicate) . "',
                            className: 'btn-success',
                            callback: function (d) {
                                $('#clone_form').attr('action', 'rubrics.php?course=$course_code&clone=true&rubric_id=' + rubric_id);
                                $('#clone_form').submit();
                            }
                        }
                    }
            });
        });
    </script>";

if (isset($_GET['delete'])) { // delete rubric
    Database::get()->query("DELETE FROM `rubric` WHERE id = ?d", $_GET['delete']);
    Session::Messages($langRubricDeleted, 'alert-success');
    redirect_to_home_page("modules/work/rubrics.php");
}


if (isset($_GET['rubric_id']) and $_GET['rubric_id']) {
    $rubric_data = Database::get()->querySingle("SELECT * FROM rubric WHERE id = ?d AND course_id = ?d", $_GET['rubric_id'], $course_id);
}
// submit rubric
if (isset($_POST['submitRubric'])) {
    if (is_rubric_used_in_grading($_POST['rubric_id'], $course_id)) { // rubric is used in assignments. Only updating options are allowed.
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('name'));
        if ($v->validate()) {
            $name = $_POST['name'];
            $desc = $_POST['desc'];
            $preview_rubric = isset($_POST['options0']) ? 1 : 0;
            $points_to_graded = isset($_POST['options1']) ? 1 : 0;
            Database::get()->query("UPDATE rubric SET name = ?s, description = ?s, 
                                        preview_rubric = ?d, points_to_graded = ?d, 
                                        course_id = ?d WHERE id = ?d",
                $name, $desc, $preview_rubric, $points_to_graded, $course_id, $_POST['rubric_id']);
            Session::Messages($langRubricUpdated, 'alert-success');
            redirect_to_home_page("modules/work/rubrics.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/work/rubrics.php?course=$course_code&rubric_id=$rubric_id");
        }
    } else {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('name'));
        $v->rule('required', array('title'));
        $v->rule('required', array('scale_item_value'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'max_grade' => "$langTheField $m[max_grade]"
        ));
        if ($v->validate()) {
            $name = $_POST['name'];
            $desc = $_POST['desc'];
            $sum_weight = 0;
            $criteria = array();
            foreach ($_POST['title'] as $crit => $item_criterio) {
                $criteria[$crit]['title_name'] = $item_criterio;
                $criteria[$crit]['crit_weight'] = $_POST['weight'][$crit];
                foreach ($_POST['scale_item_name'][$crit] as $key => $item_name) {
                    $criteria[$crit]['crit_scales'][$key]['scale_item_name'] = $item_name;
                    $criteria[$crit]['crit_scales'][$key]['scale_item_value'] = $_POST['scale_item_value'][$crit][$key];
                }
                $sum_weight += $criteria[$crit]['crit_weight'];
            }
            if ($sum_weight != 100) {
                Session::flashPost()->Messages($langRubricWeight);
                redirect_to_home_page("modules/work/rubrics.php?course=$course_code&rubric_id=$rubric_id");
            } else {
                $serialized_criteria = serialize($criteria);
                $preview_rubric = isset($_POST['options0']) ? 1 : 0;
                $points_to_graded = isset($_POST['options1']) ? 1 : 0;
                if (isset($_POST['rubric_id'])) {
                    Database::get()->query("UPDATE rubric SET name = ?s, scales = ?s, description = ?s, preview_rubric = ?d, points_to_graded = ?d, course_id = ?d WHERE id = ?d", $name, $serialized_criteria, $desc, $preview_rubric, $points_to_graded, $course_id, $_POST['rubric_id']);
                } else {
                    Database::get()->query("INSERT INTO rubric (name, scales, description, preview_rubric, points_to_graded, course_id) VALUES (?s, ?s, ?s, ?d, ?d, ?d)", $name, $serialized_criteria, $desc, $preview_rubric, $points_to_graded, $course_id);
                }
            }
            Session::Messages($langRubricCreated, 'alert-success');
            redirect_to_home_page("modules/work/rubrics.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/work/rubrics.php?course=$course_code&rubric_id=$rubric_id");
        }
    }
}

if (isset($_GET['rubric_id']) or isset($_GET['new_rubric'])) { // edit rubric or new rubric
    load_js('bootstrap-validator');
    $head_content .= "
        <script type='text/javascript'>
            $(document).ready(function() {
            var j = -1;
            var trc=-1;
            var ins_scale = function (par){
                if(trc==-1) {
                   trc=$(\"a[id^='remScale']\").length;
                } else {
                   trc++;
                }                
                var rowCount = $('#scale_table tbody tr').length;
                $('#scale_table'+par.attr('id').substr(8)+' tbody').append(
                    '<tr>'+
                    '<td class=\'form-group\'>'+
                    '<input type=\'text\' name=\'scale_item_name['+par.attr('id').substr(8)+'][]\' class=\'form-control\' value=\'\' required>'+
                    '</td>'+
                    '<td class=\'form-group\'>'+
                    '<input type=\'number\' name=\'scale_item_value['+par.attr('id').substr(8)+'][]\' class=\'form-control\' value=\'\' min=\'0\' required>'+
                    '</td>'+
                    '<td class=\'text-center\'>'+
                    '<a href=\'#\' class=\'removeScale\' id=\'remScale'+trc+'\'><span class=\'fa fa-times\' style=\'color:red\'></span></a>'+
                    '</td>'+
                    '</tr>'
                );
                $('#remScale'+ trc +'').bind('click', function(){del_scale($(this));});
            };
                        
            $('a[id^=\'addScale\']').on('click', function(){ins_scale($(this));});
                var del_scale  =  function (par) {
                    par.closest('tr').remove();
                }
            
            var del_crit  =  function (par) {
                par.closest('div[id^=\'critDiv\']').remove();
            }
            $('a[id^=\'remCrit\']').on('click', function(){del_crit($(this));});
            $('a[id^=\'remScale\']').on('click', function(){del_scale($(this));});

            $('#addCriteria').on('click', function() {
                if(j==-1) {
                   j=$(\"div[id^='critDiv']\").length;
                } else {
                   j++;
                }
                $('#inserthere').before(
                        '<div id=\'critDiv'+ j +'\'>'+	
                        '<div class=\'form-group\'>'+
                        '   <label for=\'title\' class=\'col-sm-2 control-label\'>". js_escape($langRubricCrit). ":</label>'+
                        '    <div class=\'col-sm-3\'>'+
                        '      <input name=\'title[]\' class=\'form-control\' id=\'title\' value=\'\' type=\'text\'> '+     
                        '    </div>'+
                            '<label for=\'weight\' class=\'col-sm-3 control-label\'>" . js_escape($langGradebookWeight). " (%):</label>'+
                        '    <div class=\'col-sm-2\'>'+
                        '      <input name=\'weight[]\' class=\'form-control\' id=\'weight\' value=\'\' type=\'number\'> '+     
                        '    </div>'+
                        '    <div class=\'col-sm-1\'><a href=\'#\' id=\'remCrit'+j+'\'><span class=\'fa fa-times\' style=\'color:red\'></span></a></div>'+
                        '</div>'+
                        '<div class=\'form-group\'>'+
                        '    <label class=\'col-sm-2 control-label\'>" . js_escape($langScales). ":</label>'+
                        '    <div class=\'col-sm-10\'>'+
                        '        <div class=\'table-responsive\'>'+
                        '            <table class=\'table-default\' id=\'scale_table'+ j +'\'>'+
                        '                <thead>'+
                        '                    <tr>'+
                        '                        <th style=\'width:47%\'>" . js_escape($langWording). "</th>'+
                        '                        <th style=\'width:47%\'>" . js_escape($langValue). "</th>'+
                        '                        <th class=\'text-center option-btn-cell\' style=\'width:5%\'><span class=\'fa fa-gears\'></span></th>'+
                        '                    </tr>'+
                        '                </thead>'+
                        '                <tbody>'+
                                            '<tr>'+
                                            '<td class=\'form-group\'>'+
                                            '<input type=\'text\' name=\'scale_item_name['+j+'][]\' class=\'form-control\' value=\'\' required>'+
                                            '</td>'+
                                            '<td class=\'form-group\'>'+
                                            '<input type=\'number\' name=\'scale_item_value['+j+'][]\' class=\'form-control\' value=\'\' min=\'0\' required>'+
                                            '</td>'+
                                            '<td class=\'text-center\'>'+
                                            '</td>'+
                                            '</tr>'+				
                        '                </tbody>'+
                        '            </table>'+
                        '        </div>'+
                        '    </div>'+
                        '    <div class=\'col-xs-offset-2 col-sm-10\'>'+
                        '         <a class=\'btn btn-xs btn-success margin-top-thin\' id=\'addScale'+ j +'\'>" . js_escape($langAdd) . "</a>'+
                        '    </div>'+
                        '	</div>'+
                        '</div>'
                    );
                    $('#remCrit'+ j +'').bind('click', function(){del_crit($(this));});
                    $('#addScale'+ j +'').bind('click', function(){ins_scale($(this));});
                });
            });
        </script>
    ";

    $toolName = $langGradeRubrics;
    $pageName = $langNewGradeRubric;
    $navigation[] = array("url" => "rubrics.php?course=$course_code", "name" => $langGradeRubrics);

    $name = Session::has('name') ? Session::get('name') : (isset($rubric_data) ? $rubric_data->name : "");
    $scale_rows = "";

    if (isset($_GET['rubric_id'])) {
        $pageName = $langEditMind;
        if (is_rubric_used_in_grading($_GET['rubric_id'], $course_id)) {
            $tool_content .= "<div class='alert alert-info'>$langRubricNotEditable</div>";
        }
    }

    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "rubrics.php?course=$course_code"
        ),
    ));
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' data-toggle='validator' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' id='rubric_form'>
                    <fieldset>                     
                        <div class='form-group".(Session::getError('name') ? " has-error" : "")."'>
                            <label for='name' class='col-sm-2 control-label'>$langTitleRubric:</label>
                            <div class='col-sm-10'>
                              <input name='name' type='text' class='form-control' id='name' value='$name'>
                              ".(Session::getError('name') ? "<span class='help-block'>" . Session::getError('name') . "</span>" : "")."
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='desc' class='col-sm-2 control-label'>$langRubricDesc:</label>
                            <div class='col-sm-10'>
                             " . @rich_text_editor('desc', 4, 20, $rubric_data->description) . "
                            </div>
                        </div>";
        if (isset($rubric_data) and !is_rubric_used_in_grading($_GET['rubric_id'], $course_id)) {
            $unserialized_criteria = unserialize($rubric_data->scales);
            $cc = -1;
            foreach ($unserialized_criteria as $crit => $title) {
                $tool_content .= "
                <div id='critDiv$crit'>
                <div class='form-group'>
                    <label for='title[$crit]' class='col-sm-2 control-label'>$langRubricCrit:</label>
                    <div class='col-sm-3'>
                        <input type='text' name='title[$crit]' class='form-control' value='".q($title['title_name'])."' required>
                    </div>
                    <label for='weight[$crit]' class='col-sm-3 control-label'>$langGradebookWeight (%):</label>
                    <div class='col-sm-2'>
                        <input name='weight[$crit]' class='form-control' id='weight' value='".q($title['crit_weight'])."' type='number'>     
                    </div>";
                if($crit>0)
                    $tool_content .= "
                <div class='col-sm-1'>
                        <a href='#' class='removeCrit' id='remCrit$crit'><span class='fa fa-times' style='color:red'></span></a>
                </div>";
                $tool_content .= "</div>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>$langScales:</label>
                        <div class='col-sm-10'>
                            <div class='table-responsive'>
                                <table class='table-default' id='scale_table$crit'>
                                    <thead>
                                        <tr>
                                            <th style='width:47%'>$langWording</th>
                                            <th style='width:47%'>$langValue</th>
                                            <th class='text-center option-btn-cell' style='width:5%'><span class='fa fa-gears'></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                if (is_array($title['crit_scales'])) {
                    $i = 0;
                    foreach ($title['crit_scales'] as $key => $scale) {
                        $cc++;
                        $tool_content .= "<tr>
                                            <td class='form-group'><input name='scale_item_name[$crit][]' class='form-control' value='".q($scale['scale_item_name'])."' required type='text'></td>
                                            <td class='form-group'><input name='scale_item_value[$crit][]' class='form-control' value='$scale[scale_item_value]' min='0' required type='number'></td>";
                        if ($i == 0) {
                            $tool_content .= "<td class='text-center'></td>";
                        } else {
                            $tool_content .= "<td class='text-center'><a href='#' class='removeScale' id='remScale$cc$i'><span class='fa fa-times' style='color:red'></span></a>
                                                </td>";
                        }
                        $tool_content .= "</tr>";
                        $i++;
                    }
                }
                $tool_content .= "</tbody>
                                    </table>
                                </div>
                            </div>
                        <div class='col-xs-offset-2 col-sm-10'>
                            <a class='btn btn-xs btn-success margin-top-thin' id='addScale$crit'>$langAdd</a>
                        </div>
                </div>
            </div>";
            }
    } else {
        if (isset($_GET['new_rubric']) or !is_rubric_used_in_grading($_GET['rubric_id'], $course_id)) {
            @$tool_content .= "<div id='critDiv0'>
                <div class='form-group" . (Session::getError('title') ? " has-error" : "") . "'>
                    <label for='title' class='col-sm-2 control-label'>$langRubricCrit:</label>
                    <div class='col-sm-3'>
                      <input name='title[]' type='text' class='form-control' id='title' value='$title'>
                      " . (Session::getError('title') ? "<span class='help-block'>" . Session::getError('title') . "</span>" : "") . "
                    </div>
                    <label for='weight' class='col-sm-3 control-label'>$langGradebookWeight (%):</label>
                    <div class='col-sm-2'>
                        <input name='weight[]' class='form-control' id='weight' value='" . q($title['crit_weight']) . "' type='number'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langScales:</label>
                    <div class='col-sm-10'>
                        <div class='table-responsive'>
                            <table class='table-default' id='scale_table0'>
                                <thead>
                                    <tr>
                                        <th style='width:47%'>$langWording</th>
                                        <th style='width:47%'>$langValue</th>
                                        <th class='text-center option-btn-cell'  style='width:5%'>" . icon('fa-gears') . "</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class='form-group'>
                                            <input type='text' name='scale_item_name[0][]' class='form-control' value='' required>
                                        </td>
                                        <td class='form-group'>
                                            <input type='number' name='scale_item_value[0][]' class='form-control' value='' min='0' required>
                                        </td>
                                        <td class='text-center'>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <div class='col-xs-offset-2 col-sm-10'>
                     <a class='btn btn-xs btn-success margin-top-thin' id='addScale0'>$langAdd</a>
                </div>
            </div>";
            $tool_content .= "</div>";
            $tool_content .= "<div id='inserthere' class=''>
                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-sm-10'>
                                <a class='btn btn-xs btn-success margin-top-thin' id='addCriteria'>$langAddRubricCriteria</a>
                            </div>
                        </div>
                    </div>";
        }
    }

    $opt1 = $sel_opt1 = $opt2 = $sel_opt2 = '';
    if (isset($rubric_data)) {
        $opt1 = $rubric_data->preview_rubric;
        $sel_opt1 = ($opt1==1?"checked=\"checked\"":"");
        $opt2 = $rubric_data->points_to_graded;
        $sel_opt2 = ($opt2==1?"checked=\"checked\"":"");
    }
    $tool_content .= "<div class='form-group'>
                        <label for='rubric_options' class='col-sm-2 control-label'>$langConfig:</label>
                        <div class='table-responsive'>
                        <table id='rubric_opts'> 
                            <tr class='title1'>
                                <td colspan='2'>
                                    <input type='checkbox' id='user_button0' name='options0' value='$opt1' $sel_opt1 />
                                    $langRubricOption1
                                </td>
                            </tr>
                            <tr class='title1'>
                                <td colspan='2'>
                                    <input type='checkbox' id='user_button1' name='options1' value='$opt2' $sel_opt2/>
                                    $langRubricOption2
                                </td>
                            </tr>
                        </table>
                        </div>
                    </div>";
        $tool_content .= "<div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>".
                            form_buttons(array(
                                array(
                                    'text' => $langSave,
                                    'name' => 'submitRubric',
                                    'value' => 1
                                ),
                                array(
                                    'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                )
                            ))
                            ."</div>
                        </div>";

    if (isset($_GET['rubric_id'])) {
        $rubric_id = intval($_GET['rubric_id']);
        $tool_content .= "<input type='hidden' name='rubric_id' value='$rubric_id'>";
    }
    $tool_content .= "</fieldset>
                    </form>
                </div>
            </div>
        </div>";

} else {
    $tool_content .= action_bar(array(
        array(
            'title' => $langNewGradeRubric,
            'level' => 'primary-label',
            'icon' => 'fa-plus-circle',
            'url' => "rubrics.php?course=$course_code&amp;new_rubric=true",
            'button-class' => 'btn-success'
        ),
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => (isset($_GET['preview']) ? "rubrics" : "index") . ".php?course=$course_code"
        ),
    ), false);

    if (isset($_GET['preview'])) { // preview rubric
        $rubric_id = $_GET['preview'];
        show_rubric($rubric_id);
    } else { // display available rubrics
        $rubrics = Database::get()->queryArray("SELECT * FROM rubric WHERE course_id = ?d", $course_id);
        if ($rubrics) {
            $table_content = "";
            foreach ($rubrics as $rubric) {
                $rubric_id = $rubric->id;
                $criteria = unserialize($rubric->scales);
                $criteria_list = "";
                foreach ($criteria as $ci => $criterio) {
                    $criteria_list .= "<li>$criterio[title_name] <b>($criterio[crit_weight])</b></li>";
                    if (is_array($criterio['crit_scales']))
                        foreach ($criterio['crit_scales'] as $si => $scale) {
                            $criteria_list .= "<ul><li>$scale[scale_item_name] ( $scale[scale_item_value] )</li></ul>";
                        }
                }
                $assignments_with_rubrics = Database::get()->queryArray("SELECT title, grading_scale_id FROM assignment 
                    WHERE course_id = ?d
                    AND grading_type = " . ASSIGNMENT_RUBRIC_GRADE . " 
                    AND grading_scale_id = ?d",
                    $course_id, $rubric_id);

                $rubric_assignment = '';
                if (count($assignments_with_rubrics) > 0) {
                    foreach ($assignments_with_rubrics as $data) {
                        $assignment_title = $data->title;
                        $rubric_assignment .= "$assignment_title<br>";
                    }
                }
                $table_content .= "<tr>
                        <td><a href='rubrics.php?course=$course_code&amp;preview=$rubric_id'>$rubric->name</a><small>$rubric->description</small></td>
                        <td>$rubric_assignment</td>";
                $table_content .= "<td class='option-btn-cell'>
                        " . action_button(array(
                        array(
                            'title' => $langEdit,
                            'url' => "rubrics.php?course=$course_code&amp;rubric_id=$rubric->id",
                            'icon' => 'fa-edit'
                        ),
                        array(
                            'title' => $langCreateDuplicate,
                            'url' => "#",
                            'icon' => 'fa-copy',
                            'icon-class' => 'warnLink',
                            'icon-extra' => "data-rubric-id='$rubric->id'",
                        ),
                        array(
                            'title' => $langDelete,
                            'url' => "rubrics.php?course=$course_code&amp;delete=$rubric->id",
                            'icon' => 'fa-times',
                            'show' => !is_rubric_used_in_assignment($rubric->id, $course_id),
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete)
                    )) . "
                        </td>
                    </tr>";
            }
            $tool_content .= "<div class='table-responsive '>
            <table class='table-default'>
                <thead>
                    <tr>
                        <th>$langName</th>
                        <th>$langWorks</th>
                        <th class='text-center'>" . icon('fa-gears') . "</th>
                    </tr>
                </thead>
                <tbody>
                    $table_content
                </tbody>
            </table>
        </div>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langNoGradeRubrics</div>";
        }
    }
}
draw($tool_content, 2, null, $head_content);

/**
 * @brief display rubric
 * @param type $rubric_id
 */
function show_rubric ($rubric_id): void
{

    global $tool_content, $course_code, $course_id,
        $langName, $langDescription, $langCriteria,
        $langEdit,$langDelete,$langConfirmDelete;

    $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);

    $criteria = unserialize($rubric->scales);
    $criteria_list = "";
    foreach ($criteria as $ci => $criterio) {
        $criteria_list .= "<li>$criterio[title_name] <b>($criterio[crit_weight]%)</b></li>";
        if (is_array($criterio['crit_scales'])) {
            foreach ($criterio['crit_scales'] as $si => $scale) {
                $criteria_list .= "<ul><li>$scale[scale_item_name] ( $scale[scale_item_value] )</li></ul>";
            }
        }
    }
        $tool_content .= "
        <div class='table-responsive'>
        <table class='table-default'>
            <thead>
                <th>$langName</th> 
                <th>$langDescription</th>
                <th>$langCriteria</th>
                <th class='text-center' rowspan='2'>" . icon('fa-gears') . "</th>
            </thead>
            <tr>
                <td>$rubric->name</td>
                <td>$rubric->description</td>
                <td>
                <ul class='list-unstyled'>
                    $criteria_list
                </ul>
                </td>
                <td class='option-btn-cell'>
                    ". action_button(array(
                        array(
                            'title' => $langEdit,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;rubric_id=$rubric->id",
                            'icon' => 'fa-edit'
                        ),
                        array(
                            'title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$rubric->id",
                            'icon' => 'fa-times',
                            'show' => !is_rubric_used_in_assignment($rubric->id, $course_id),
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete)
                        ))."
                </td>
                </tr>
        </table>
    </div>";
}

/**
 * @brief check if rubric is used in some assignment
 * @param $rubric_id
 * @param $course_id
 * @return bool
 */
function is_rubric_used_in_assignment($rubric_id, $course_id) {

    $sql = Database::get()->querySingle("SELECT * FROM assignment WHERE grading_scale_id = ?d
                                                  AND grading_type = " . ASSIGNMENT_RUBRIC_GRADE . "
                                                  AND course_id = ?d", $rubric_id, $course_id);
    if ($sql) {
        return TRUE;
    } else {
        return FALSE;
    }

}

/**
 * @brief check if there is assignment submissions graded with rubric
 * @param $rubric_id
 * @param $course_id
 * @return bool
 */
function is_rubric_used_in_grading($rubric_id, $course_id) {

    $sql = Database::get()->querySingle("SELECT * FROM `assignment`, `assignment_submit`
                    WHERE `assignment`.`grading_scale_id` = ?d 
                    AND `assignment`.`course_id` = ?d 
                    AND `assignment`.`id` = `assignment_submit`.`assignment_id` 
                    AND `assignment_submit`.`grade` IS NOT NULL", $rubric_id, $course_id);
    if ($sql) {
        return TRUE;
    } else {
        return FALSE;
    }
}


/**
 * @brief clone rubric
 * @param $rubric_id
 * @param $new_course_id
 * @return void
 */
function clone_rubric($rubric_id, $new_course_id) {

    global $langCopyDuplicate;

    $sql = Database::get()->query("INSERT INTO rubric (name, scales, description, preview_rubric, points_to_graded, course_id)
                                 SELECT CONCAT(name, ' $langCopyDuplicate') AS name, scales, description, preview_rubric, points_to_graded, ?d 
                                FROM rubric 
                                WHERE id = ?d",
                                $new_course_id, $rubric_id);

}
