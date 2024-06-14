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

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'modules/group/group_functions.php';

$toolName = $langGroups;

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroups);
$pageName = $langCourseInfo;

$multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);
$student_desc = setting_get(SETTING_GROUP_STUDENT_DESCRIPTION, $course_id);

$checked_single_reg = $checked_category_reg = $checked_multi_reg = '';

$checked_single_reg = ($multi_reg == 0)? ' checked' : '';
$checked_multi_reg = ($multi_reg == 1)? ' checked' : '';
if (!has_group_categories($course_id)) {
    $checked_category_reg = " disabled";
} else {
    $checked_category_reg = ($multi_reg == 2)? ' checked' : '';
}

$checked_student_desc = $student_desc ? ' checked' : '';

if (isset($_POST['submit'])) {
    if (isset($_POST['group_reg'])) {
        if ($_POST['group_reg'] == 1) {
            setting_set(SETTING_GROUP_MULTIPLE_REGISTRATION, 1, $course_id);
        } elseif ($_POST['group_reg'] == 2) {
            setting_set(SETTING_GROUP_MULTIPLE_REGISTRATION, 2, $course_id);
        } else {
            setting_set(SETTING_GROUP_MULTIPLE_REGISTRATION, 0, $course_id);
        }
    }
    if (isset($_POST['student_desc'])) {
        setting_set(SETTING_GROUP_STUDENT_DESCRIPTION, $_POST['student_desc'], $course_id);
    } else {
        setting_set(SETTING_GROUP_STUDENT_DESCRIPTION, 0, $course_id);

    }
    Session::flash('message',$langGlossaryUpdated);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/group/group_settings.php?course=$course_code");
} else {
    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <div class='radio mb-2'>
                                  <label>
                                    <input type='radio' name='group_reg' value='0'$checked_single_reg>$langGroupAllowRegistration
                                  </label>
                            </div>
                            <div class='radio mb-2'>
                                  <label>
                                    <input type='radio' name='group_reg' value='2'$checked_category_reg>$langGroupAllowCategoryRegistration
                                  </label>
                            </div>
                            <div class='radio'>
                                  <label>
                                    <input type='radio' name='group_reg' value='1'$checked_multi_reg>$langGroupAllowMultipleRegistration
                                  </label>
                             </div>
                         </div>
                     </div>
                    <div class='form-group mt-2'>
                         <div class='col-sm-12'>
                            <div class='checkbox'>
                                <label class='label-container'>
                                    <input type='checkbox' name='student_desc' value='1'$checked_student_desc>
                                    <span class='checkmark'></span>  
                                    $langGroupAllowStudentGroupDescription
                                  </label>
                             </div>
                         </div>
                     </div>                    
                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-end align-items-center'>
                           
                             
                                ".form_buttons(array(
                                    array(
                                        'class' => 'submitAdminBtn',
                                        'text' => $langSave,
                                        'name' => 'submit',
                                        'value'=> $langSubmit
                                    ),
                                    array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href' => "index.php?course=$course_code"
                                    )
                                ))
                                ."
                           
                        </div>
                    </div>
                ". generate_csrf_token_form_field() ."
                </form>
              </div></div><div class='d-none d-lg-block'>
              <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
          </div>
          </div>";
}

draw($tool_content, 2);
