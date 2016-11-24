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

include '../../include/baseTheme.php';

$toolName = $langGradeRubrics;
$pageName = $langGradeRubrics;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);

if (isset($_POST['submitScale'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array(
        'title' => "$langTheField $langTitle",
        'max_grade' => "$langTheField $m[max_grade]"
    ));
    $scale_id = isset($_POST['rubric_id']) ? $_POST['rubric_id'] : 0;
    if($v->validate()) {
        $name = $_POST['name'];
		$title = $_POST['title'];
        $scales = array();
        foreach ($_POST['scale_item_name'] as $key => $item_name) {
            $scales[$key]['scale_item_name'] = $item_name;
            $scales[$key]['scale_item_value'] = $_POST['scale_item_value'][$key];
        }
        $serialized_scales = serialize($scales);
        if ($scale_id) {
            Database::get()->query("UPDATE rubric SET name = ?s, description = ?s, title = ?s, scales = ?s, preview_rubric = ?d, rubric_during_evaluation = ?d, rubric_to_graded = ?d, points_during_evaluation = ?d, points_to_graded = ?d, course_id = ?d WHERE id = ?d",$name, $desc, $title, $serialized_scales, $preview_rubric, $rubric_during_evaluation, $rubric_to_graded, $points_during_evaluation, $points_to_graded, $course_id, $_POST['rubric_id']);
            update_assignments_max_grade($scale_id);
        } else {
            Database::get()->query("INSERT INTO rubric (name, description, title, scales,  preview_rubric, rubric_during_evaluation, rubric_to_graded, points_during_evaluation, points_to_graded, course_id) VALUES (?s, ?s, ?s, ?d, ?d, ?d, ?d, ?d, ?d, ?d)", $name, $desc, $title, $serialized_scales, $preview_rubric, $rubric_during_evaluation, $rubric_to_graded, $points_during_evaluation, $points_to_graded, $course_id);
        }
        redirect_to_home_page("modules/work/rubrics.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/rubrics.php?course=$course_code&rubric_id=$rubric_id");
    }
}

if (isset($_GET['scale_id'])) {
    load_js('bootstrap-validator');
    $head_content .= "
    <script type='text/javascript'>
		
        $(function() {
		var j = 1;
		var trc=1;
			var ins_scale = function (par){
			console.log(par.attr('id').substr(8));
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
						trc++;
					};
					
            $('a[id^=\'addScale\']').on('click', function(){ins_scale($(this));});
           
			var del_scale  =  function (par){	
				par.closest('tr').remove();
			}
			
			var del_crit  =  function (par){	
			console.log(par);
				par.closest('div[id^=\'critDiv\']').remove();
			}
			//$('table[id^=\'scale_table\'] a').on('click', function(){del_scale($(this));});
            
			$('#addCriteria').on('click', function() {
			
				$('#inserthere').before(
						'<div id=\'critDiv'+ j +'\'>'+	
						'<div class=\'form-group\'>'+
                        '   <label for=\'title\' class=\'col-sm-2 control-label\'>Κριτήριο:</label>'+
                        '    <div class=\'col-sm-9\'>'+
                        '      <input name=\'title[]\' class=\'form-control\' id=\'title\' value=\'\' type=\'text\'> '+     
                        '    </div>'+
						'    <div class=\'col-sm-1\'><a href=\'#\' id=\'remCrit'+j+'\'><span class=\'fa fa-times\' style=\'color:red\'></span></a></div>'+
                        '</div>'+
                        '<div class=\'form-group\'>'+
                        '    <label class=\'col-sm-2 control-label\'>Κλίμακες:</label>'+
                        '    <div class=\'col-sm-10\'>'+
                        '        <div class=\'table-responsive\'>'+
                        '            <table class=\'table-default\' id=\'scale_table'+ j +'\'>'+
                        '                <thead>'+
                        '                    <tr>'+
                        '                        <th style=\'width:47%\'>Λεκτικό</th>'+
                        '                        <th style=\'width:47%\'>Τιμή</th>'+
                        '                        <th class=\'text-center option-btn-cell\' style=\'width:5%\'><span class=\'fa fa-gears\'></span></th>'+
                        '                    </tr>'+
                        '                </thead>'+
                        '                <tbody>'+
                        '                </tbody>'+
                        '            </table>'+
                        '        </div>'+
                        '    </div>'+
                        '    <div class=\'col-xs-offset-2 col-sm-10\'>'+
                        '         <a class=\'btn btn-xs btn-success margin-top-thin\' id=\'addScale'+ j +'\'>Προσθήκη</a>'+
                        '    </div>'+
						'	</div>'+
						'</div>'
				);
				$('#remCrit'+ j +'').bind('click', function(){del_crit($(this));});
				$('#addScale'+ j +'').bind('click', function(){ins_scale($(this));});
				j++;
			});
        });
    </script>
    ";
	

    $scale_used = 0;
    if ($_GET['scale_id']) {
        $scale_data = Database::get()->querySingle("SELECT * FROM rubric WHERE id = ?d AND course_id = ?d", $_GET['scale_id'], $course_id);
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
                $scale_rows .= "            
                            <td class='text-center'>
                                <a href='#' class='removeScale'><span class='fa fa-times' style='color:red'></span></a>
                            </td>";
            }
            $scale_rows .= "
                    </tr>
                ";
        }
    }
    $toolName = $langGradeRubrics;
    $pageName = $langNewGradeRubric;
    $navigation[] = array("url" => "rubrics.php?course=$course_code", "name" => $langGradeRubrics);
    if ($scale_used) {
        $tool_content .= "<div class='alert alert-info'>$langGradeScaleNotEditable</div>";
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
                    <form class='form-horizontal' role='form' data-toggle='validator' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' id='scales_form'>
                    <fieldset>
                        $hidden_input
                        <div class='form-group".(Session::getError('name') ? " has-error" : "")."'>
                            <label for='name' class='col-sm-2 control-label'>$langTitleRubric:</label>
                            <div class='col-sm-10'>
                              <input name='name' type='text' class='form-control' id='name' value='$name'".($scale_used ? " disabled" : "").">
                              ".(Session::getError('name') ? "<span class='help-block'>" . Session::getError('name') . "</span>" : "")."
                            </div>
						</div>
						<div class='form-group'>
							<label for='desc' class='col-sm-2 control-label'>$langRubricDesc:</label>
							<div class='col-sm-10'>
							 " . rich_text_editor('desc', 4, 20, $desc) . "
							</div>
						</div>						
                        <div id='clonethis'><div class='form-group".(Session::getError('title') ? " has-error" : "")."'>
                            <label for='title' class='col-sm-2 control-label'>$langRubricCrit:</label>
                            <div class='col-sm-10'>
                              <input name='title' type='text' class='form-control' id='title' value='$title'".($scale_used ? " disabled" : "").">
                              ".(Session::getError('title') ? "<span class='help-block'>" . Session::getError('title') . "</span>" : "")."
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langScales:</label>
                            <div class='col-sm-10'>
                                <div class='table-responsive'>
                                    <table class='table-default' id='scale_table'>
                                        <thead>
                                            <tr>
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
        $tool_content .= "
                            <div class='col-xs-offset-2 col-sm-10'>
                                 <a class='btn btn-xs btn-success margin-top-thin' id='addScale'>$langAdd</a>
                            </div>
							</div>";
    }
    $tool_content .= "  </div>
						<div id='inserthere' class=''>
							<div class='form-group'>
								<a class='btn btn-xs btn-success margin-top-thin' id='addCriteria'>$langAddRubricCriteria</a>
							</div>
						</div>
		<div class='form-group'>
            <label for='rubric_options' class='col-sm-2 control-label'>$langRubricOptions:</label>
				<div class='table-responsive'>
                    <table id='rubric_opts'> 
							<tr class='title1'>
								<td colspan='2'>
									<input type='checkbox' id='user_button0' name='options[0]' checked='1' />
									$langRubricOption1
								</td>
							</tr>
							<tr class='title1'>
								<td colspan='2'>
									<input type='checkbox' id='user_button1' name='options[1]' checked='1' />
									$langRubricOption2
								</td>
							</tr>
							<tr class='title1'>
								<td colspan='2'>
									<input type='checkbox' id='user_button2' name='options[2]' checked='1' />
									$langRubricOption3
								</td>
							</tr>
							<tr class='title1'>
								<td colspan='2'>
									<input type='checkbox' id='user_button3' name='options[3]' checked='1' />
									$langRubricOption4
								</td>
							</tr>
							<tr class='title1'>
								<td colspan='2'>
									<input type='checkbox' id='user_button4' name='options[4]' checked='1' />
									$langRubricOption5
								</td>
							</tr>
					</table>
				</div>
		</div>";
	
    if (!$scale_used) {
        $tool_content .= " 
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>".
                                form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'name' => 'submitScale'
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                    )
                                ))
                                ."</div>
                        </div>";
    }
    $tool_content .= "
                    </fieldset>
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
            'url' => "rubrics.php?course=$course_code&amp;scale_id=0",
            'button-class' => 'btn-success'
        ),
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "index.php?course=$course_code"
        ),
    ),false);

    $grading_scales = Database::get()->queryArray("SELECT * FROM rubric WHERE course_id = ?d", $course_id);
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
                            <td>$grading_scale->title</td>
                            <td>
                                <ul class='list-unstyled'>
                                    $scales_list
                                </ul>
                            </td>
                            <td class='option-btn-cell'>
                            ". action_button(array(
                                array(
                                    'title' => $langEdit,
                                    'url' => "rubrics.php?course=$course_code&amp;scale_id=$grading_scale->id",
                                    'icon' => 'fa-edit'
                                )
                            ))."
                            </td>
                        </tr>
                        ";
        }
        $tool_content .= "
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr>
                            <th>$langTitle</th>
                            <th>$langGradebookMEANS</th>
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
draw($tool_content, 2, null, $head_content);

function update_assignments_max_grade($scale_id) {
    $max_grade = max_grade_from_scale($scale_id);
    Database::get()->query("UPDATE assignment SET max_grade = ?f WHERE grading_scale_id = ?d", $max_grade, $scale_id);
}
function max_grade_from_scale($scale_id) {
    global $course_id;
    $scale_data = Database::get()->querySingle("SELECT * FROM rubric WHERE id = ?d AND course_id = ?d", $scale_id, $course_id);
    $unserialized_scale_items = unserialize($scale_data->scales);
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $item) {
        if ($item['scale_item_value'] > $max_scale_item_value) {
            $max_scale_item_value = $item['scale_item_value'];
        }
    }
    return $max_scale_item_value;
}
