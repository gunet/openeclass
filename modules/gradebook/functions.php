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


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once 'modules/progress/GradebookEvent.php';

/**
 * @brief display user grades (teacher view)
 * @param int $gradebook_id
 */
function display_user_grades($gradebook_id) {

    global $course_code, $tool_content,
           $langTitle, $langGradebookActivityDate2, $langType, $langGradebookNewUser,
           $langGradebookWeight, $langGradebookBooking, $langGradebookNoActMessage1,
           $langGradebookNoActMessage2, $langGradebookNoActMessage3, $langGradebookActCour,
           $langGradebookAutoGrade, $langGradebookNoAutoGrade, $langGradebookActAttend,
           $langGradebookOutRange, $langGradebookUpToDegree, $langGradeNoBookAlert, $langGradebookGrade, $langAddGrade;

    $gradebook_range = get_gradebook_range($gradebook_id);
    if(weightleft($gradebook_id, 0) == 0) {
        $userID = intval($_GET['book']); //user
        //check if there are booking records for the user, otherwise alert message for first input
        $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) AS count FROM gradebook_book, gradebook_activities
                            WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                            AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
        if(!$checkForRecords) {
            $tool_content .="<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langGradebookNewUser</span></div>";
        }

        //get all the activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
        $actNumber = count($result);
        if ($actNumber > 0) {
            $tool_content .= "<div class='text-heading-h5'>" . display_user($userID) . " ($langGradebookGrade: " . userGradeTotal($gradebook_id, $userID) . ")</div>";
            $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                              <div class='table-responsive'><table class='table-default'>";
            $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langType</th><th>$langGradebookWeight</th>";
            $tool_content .= "<th>$langGradebookBooking</th>";
            $tool_content .= "</tr></thead>";
        } else {
            $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</span></div>\n";
        }

        if ($result) {
            foreach ($result as $activity) {
                //check if there is auto mechanism
                if($activity->auto == 1){
                    //check for autograde (if there is already a record do not propose the auto grade)
                    //if there is not get the grade from the book table
                    $checkForAuto = Database::get()->querySingle("SELECT id FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if($activity->module_auto_type && !$checkForAuto) { //assignments, exercises, lp(scorms)
                        $userGrade = attendForAutoGrades($userID, $activity->module_auto_id, $activity->module_auto_type, $gradebook_range);
                    } else {
                        $qusergrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                        if ($qusergrade) {
                            $userGrade = $qusergrade->grade * $gradebook_range;
                        }
                    }
                } else {
                    $qusergrade = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if ($qusergrade) {
                        $userGrade = $qusergrade->grade * $gradebook_range;
                    }
                }

                $content = standard_text_escape($activity->description);

                $tool_content .= "<tr><td>";

                if (!empty($activity->title)) {
                    $tool_content .= q($activity->title);
                }

                $tool_content .= "</td>";
                if($activity->date){
                    $tool_content .= "<td><div class='smaller'><span class='day'>" . format_locale_date(strtotime($activity->date), 'short', false) . "</div></td>";
                } else {
                    $tool_content .= "<td>-</td>";
                }
                if ($activity->module_auto_id) {
                    $tool_content .= "<td class='smaller'>$langGradebookActCour";
                    if ($activity->auto) {
                        $tool_content .= "<br>($langGradebookAutoGrade)";
                    } else {
                        $tool_content .= "<br>($langGradebookNoAutoGrade)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langGradebookActAttend</td>";
                }
                $tool_content .= "<td>" . $activity->weight . "%</td>";
                @$tool_content .= "<td>
                <input class='form-control' aria-label='$langAddGrade' type='text' value='".$userGrade."' name='" . getIndirectReference($activity->id) . "'"; //SOS 4 the UI!!
                $tool_content .= ">
                <input type='hidden' value='" . $gradebook_range . "' name='degreerange'>
                <input type='hidden' value='" . getIndirectReference($userID) . "' name='userID'>
                </td>";
            } // end of while
        }
        $tool_content .= "</tr></table></div>";
        $tool_content .= "<div class='float-end mt-3'><input class='btn submitAdminBtn' type='submit' name='bookUser' value='$langGradebookBooking'>".generate_csrf_token_form_field()."</div></form>";

        if(userGradeTotal($gradebook_id, $userID) > $gradebook_range){
            $tool_content .= "<br>" . $langGradebookOutRange;
        }
        $tool_content .= "<span class='help-block'><small>" . $langGradebookUpToDegree . $gradebook_range . "</small></span>";
    } else {
        $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langGradeNoBookAlert " . weightleft($gradebook_id, 0) . "%</span></div>";
    }
}



/**
 * @brief display form for creating a new gradebook
 */
function new_gradebook() {

    global $tool_content, $course_code, $langStart, $langEnd, $head_content, $language,
           $langTitle, $langSave, $langInsert, $langGradebookRange, $langGradeScalesSelect, $urlAppend, $langImgFormsDes;

        load_js('bootstrap-datetimepicker');
        $head_content .= "
        <script type='text/javascript'>
            $(function() {
                $('#start_date, #end_date').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true
                });
            });
        </script>";
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : '';
    $start_date_error = Session::getError('start_date');
    $start_date = Session::has('start_date') ? Session::get('start_date') : '';
    $end_date_error = Session::getError('end_date');
    $end_date = Session::has('end_date') ? Session::get('end_date') : '';
    $degreerange_error  = Session::getError('degreerange');
    $degreerange = Session::has('degreerange') ? Session::get('degreerange') : 0;
    $tool_content .=
        "
<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&newGradebook=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group".($title_error ? " has-error" : "")."'>
                    <div class='col-12'>
                        <label for='title' class='control-label-notes'>$langTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                    </div>
                    <div class='col-12'>
                        <input id='title' class='form-control' placeholder='$langTitle' type='text' name='title' value='$title'>
                        <span class='help-block Accent-200-cl'>$title_error</span>
                    </div>
                </div>
                
                   
                        <div class='form-group".($start_date_error ? " has-error" : "")." mt-4'>
                            <div class='col-12'>
                                <label for='start_date' class='control-label-notes'>$langStart <span class='asterisk Accent-200-cl'>(*)</span></label>
                            </div>
                            <div class='col-12'>
                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                    <input class='form-control mt-0 border-start-0' placeholder='$langStart' type='text' name='start_date' id='start_date' value='$start_date'>
                                    
                                </div>
                                <span class='help-block Accent-200-cl'>$start_date_error</span>
                            </div>
                        </div>
                   
                    
                        <div class='form-group".($end_date_error ? " has-error" : "")." mt-4'>
                            <div class='col-12'>
                                <label for='end_date' class='control-label-notes'>$langEnd <span class='asterisk Accent-200-cl'>(*)</span></label>
                            </div>
                            <div class='col-12'>
                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                    <input class='form-control mt-0 border-start-0' placeholder='$langEnd' type='text' name='end_date' id='end_date' value='$end_date'>
                                    
                                </div>
                                <span class='help-block Accent-200-cl'>$end_date_error</span>
                            </div>
                        </div>
                 
                
                <div class='form-group".($degreerange_error ? " has-error" : "")." mt-4'>
                    <label for='degree_range_id' class='col-12 control-label-notes'>$langGradebookRange <span class='asterisk Accent-200-cl'>(*)</span></label>
                    <div class='col-12'>
                        <select name='degreerange' class='form-select' id='degree_range_id'>
                            <option value".($degreerange == 0 ? ' selected' : '').">-- $langGradeScalesSelect --</option>
                            <option value='5'".($degreerange == 5 ? ' selected' : '').">0-5</option>
                            <option value='10'".($degreerange == 10 ? ' selected' : '').">0-10</option>
                            <option value='20'".($degreerange == 20 ? ' selected' : '').">0-20</option>
                            <option value='100'".($degreerange == 100 ? ' selected' : '').">0-100</option>
                        </select>
                        <span class='help-block Accent-200-cl'>$degreerange_error</span>
                    </div>
                </div>
                <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items-center'>

                           "
                                .form_buttons(array(
                                    array(
                                            'class' => 'submitAdminBtn',
                                            'text' => $langSave,
                                            'name' => 'newGradebook',
                                            'value'=> $langInsert
                                    ),
                                    array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                        )
                                    )).
                            "


                    </div>
                </div>
                ". generate_csrf_token_form_field() ."
            </form>
        </div>
    </div>
    <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";
}


/**
 * @brief create copy of an existing gradebook. (with same activities and no users).
 * @param type $gradebook_id
 */
function clone_gradebook($gradebook_id) {

    global $course_id, $langCopyDuplicate;

    $gradebook = Database::get()->querySingle("SELECT * FROM gradebook WHERE id = ?d", $gradebook_id);
    $newTitle = $gradebook->title." $langCopyDuplicate";
    $new_gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d,
                                                      students_semester = 1, `range` = ?d,
                                                      active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $gradebook->range, $newTitle, $gradebook->start_date, $gradebook->end_date)->lastInsertID;
    Database::get()->query("INSERT INTO gradebook_activities (gradebook_id, title, activity_type, date, description, weight, module_auto_id, module_auto_type, auto, visible)
                                SELECT $new_gradebook_id, title, activity_type, " . DBHelper::timeAfter() . ", description, weight, module_auto_id, module_auto_type, auto, 1
                                 FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id);
}

/**
 * @brief delete gradebook
 * @param int $gradebook_id
 */
function delete_gradebook($gradebook_id) {

    global $course_id, $langGradebookDeleted;

    $r = Database::get()->queryArray("SELECT id FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id);
    foreach ($r as $act) {
        delete_gradebook_activity($gradebook_id, $act->id);
    }
    Database::get()->query("DELETE FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
    $action = Database::get()->query("DELETE FROM gradebook WHERE id = ?d AND course_id = ?d", $gradebook_id, $course_id);
    if ($action) {
        Session::flash('message', $langGradebookDeleted);
        Session::flash('alert-class', 'alert-success');
    }
}


/**
 * @brief delete gradebook activity
 * @param int $gradebook_id
 * @param int $activity_id
 */
function delete_gradebook_activity($gradebook_id, $activity_id) {

    global $langGradebookDel, $langGradebookDelFailure;

    $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $activity_id, $gradebook_id)->affectedRows;
    Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $activity_id);
    if ($delAct) {
        Session::flash('message', $langGradebookDel);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', $langGradebookDelFailure);
        Session::flash('alert-class', 'alert-danger');
    }
}

/**
 * @brief delete user from gradebook
 * @param int $gradebook_id
 * @param int $userid
 */
function delete_gradebook_user($gradebook_id, $userid) {

    global $langGradebookEdit;

    Database::get()->query("DELETE FROM gradebook_book WHERE uid = ?d AND gradebook_activity_id IN
                                (SELECT id FROM gradebook_activities WHERE gradebook_id = ?d)", $userid, $gradebook_id);
    Database::get()->query("DELETE FROM gradebook_users WHERE uid = ?d AND gradebook_id = ?d", $userid, $gradebook_id);
    Session::flash('message', $langGradebookEdit);
    Session::flash('alert-class', 'alert-success');
}

/**
 * @brief insert/modify gradebook settings
 * @param int $gradebook_id
 */
function gradebook_settings($gradebook_id) {

    global $tool_content, $course_code,
           $langTitle, $langSave, $langStart, $langEnd, $head_content,
           $langSave, $langGradebookRange, $langGradebookUpdate,
           $gradebook, $langGradeScalesSelect, $language, $urlAppend, $langImgFormsDes;
    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#start_date, #end_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : $gradebook->title;
    $start_date_error = Session::getError('start_date');
    $start_date = Session::has('start_date') ? Session::get('start_date') : DateTime::createFromFormat('Y-m-d H:i:s', $gradebook->start_date)->format('d-m-Y H:i');
    $end_date_error = Session::getError('end_date');
    $end_date = Session::has('end_date') ? Session::get('end_date') : DateTime::createFromFormat('Y-m-d H:i:s', $gradebook->end_date)->format('d-m-Y H:i');
    $degreerange_error  = Session::getError('degreerange');
    $degreerange = Session::has('degreerange') ? Session::get('degreerange') : $gradebook->range;
    // update gradebook title
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "'>
                    <div class='form-group".($title_error ? " has-error" : "")."'>
                        <label for='title' class='col-12 control-label-notes'>$langTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-12'>
                            <input id='title' class='form-control' type='text' placeholder='$langTitle' name='title' value='$title'>
                            <span class='help-block Accent-200-cl'>$title_error</span>
                        </div>
                    </div>
                    
                       
                            <div class='form-group".($start_date_error ? " has-error" : "")." mt-4'>
                                <div class='col-12'>
                                    <label for='start_date' class='control-label-notes'>$langStart <span class='asterisk Accent-200-cl'>(*)</span></label>
                                </div>
                                <div class='col-12'>
                                    <div class='input-group'>
                                        <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                        <input class='form-control mt-0 border-start-0' type='text' name='start_date' id='start_date' value='$start_date'>
                                        
                                    </div>
                                    <span class='help-block Accent-200-cl'>$start_date_error</span>
                                </div>
                            </div>
                      
                      
                            <div class='form-group".($end_date_error ? " has-error" : "")." mt-4'>
                                <div class='col-12'>
                                    <label for='end_date' class='control-label-notes'>$langEnd</label>
                                </div>
                                <div class='col-12'>
                                    <div class='input-group'>
                                        <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                        <input class='form-control mt-0 border-start-0' type='text' name='end_date' id='end_date' value='$end_date'>
                                        
                                    </div>
                                    <span class='help-block Accent-color-cl'>$end_date_error</span>
                                </div>
                            </div>
                       
                    
                    <div class='form-group".($degreerange_error ? " has-error" : "")." mt-4'><label for='degreerangeid' class='col-12 control-label-notes'>$langGradebookRange</label>
                            <div class='col-12'>
                                <select name='degreerange' class='form-select' id='degreerangeid'>
                                    <option value".($degreerange == 0 ? ' selected' : '').">-- $langGradeScalesSelect --</option>
                                    <option value='10'" . ($degreerange == 10 ? " selected" : "") .">0-10</option>
                                    <option value='20'" . ($degreerange == 20 ? " selected" : "") .">0-20</option>
                                    <option value='5'" . ($degreerange == 5 ? " selected " : "") .">0-5</option>
                                    <option value='100'" . ($degreerange == 100 ? " selected" : "") .">0-100</option>
                                </select>
                                <span class='help-block'>$degreerange_error</span>
                            </div>
                        </div>
                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>


                                      ".form_buttons(array(
                                        array(
                                            'class' => 'submitAdminBtn',
                                            'text' => $langSave,
                                            'name' => 'submitGradebookSettings',
                                            'value'=> $langGradebookUpdate
                                        ),
                                        array(
                                            'class' => 'cancelAdminBtn ms-1',
                                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook->id) . ""
                                        )
                                    ))."



                            </div>
                        </div>
                    </fieldset>
                ". generate_csrf_token_form_field() ."
                </form>
            </div>
        </div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";
}

/**
 * @brief modify user gradebook settings
 */
function user_gradebook_settings() {

    global $tool_content, $course_code, $langGroups, $language,
           $langAttendanceUpdate, $langGradebookInfoForUsers,
           $langRegistrationDate, $langFrom2, $langTill, $langRefreshList,
           $langUserDuration, $langGradebookAllBetweenRegDates, $langSpecificUsers, $head_content,
           $langStudents, $langMove, $langParticipate, $gradebook, $urlAppend, $langImgFormsDes, $langForm;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
    $(function() {
            $('#UsersStart, #UsersEnd').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
    });
    </script>";
    // default values
    $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $gradebook->start_date)->format('d-m-Y H:i');
    $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $gradebook->end_date)->format('d-m-Y H:i');
    $tool_content .= "

    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . getIndirectReference($gradebook->id) . "&editUsers=1'>
                    <fieldset>
                    <legend class='mb-0' aria-label='$langForm'></legend>
                    <div class='form-group'>
                        <div class='action-bar-title'>$langGradebookInfoForUsers</div>
                    </div>
                    <div class='form-group mt-4'>
                    <div class='col-sm-12 control-label-notes mb-2'>$langUserDuration</div>
                        <div class='col-sm-12'>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_some_users' name='specific_gradebook_users' value='1'>
                                <span id='button_some_users_text'>$langSpecificUsers</span>
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_groups' name='specific_gradebook_users' value='2'>
                                <span id='button_groups_text'>$langGroups</span>
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_all_users' name='specific_gradebook_users' value='0' checked>
                                <span id='button_all_users_text'>$langGradebookAllBetweenRegDates</span>
                              </label>
                            </div>
                        </div>
                    </div>
                    <div class='row form-group mt-4' id='all_users'>
                        <div class='col-md-6 col-12'>
                            <div class='input-append date form-group' id='startdatepicker'>
                                <label for='UsersStart' class='col-sm-12 control-label-notes mb-2'>$langRegistrationDate $langFrom2</label>
                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='UsersStart' id='UsersStart' type='text' value='$start_date'>
                                    
                                </div>
                            </div>
                        </div>
                        <div class='col-md-6 col-12'>
                            <div class='input-append date form-group mt-md-0 mt-4' id='enddatepicker'>
                                <label for='UsersEnd' class='col-sm-12 control-label-notes mb-2'>$langTill</label>
                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='UsersEnd' id='UsersEnd' type='text' value='$end_date'>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-3'>
                        <div class='col-sm-12 col-sm-offset-2'>
                            <div class='table-responsive'>
                                <table id='participants_tbl' class='table-default hide'>
                                    <thead>
                                        <tr class='title1 list-header'>
                                            <td id='users' class='form-label'>$langStudents</td>
                                            <td class='form-label text-center'>$langMove</td>
                                            <td class='form-label'>$langParticipate</td>
                                        </tr>
                                    </thead>
                                    <tr>
                                      <td>
                                        <select class='form-select h-100' id='users_box' size='10' multiple></select>
                                      </td>
                                      <td>
                                        <div class='d-flex align-items-center flex-column gap-2'>
                                            <input class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick=\"move('users_box','participants_box')\" value='   &gt;&gt;   ' />
                                            <input class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick=\"move('participants_box','users_box')\" value='   &lt;&lt;   ' />
                                        </div>
                                      </td>
                                      <td width='40%'>
                                        <select class='form-select h-100' id='participants_box' name='specific[]' size='10' multiple></select>
                                      </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-end align-items-center'>


                                 ".form_buttons(array(
                                    array(
                                        'class' => 'submitAdminBtn',
                                        'text' => $langRefreshList,
                                        'name' => 'resetGradebookUsers',
                                        'value'=> $langAttendanceUpdate,
                                        'javascript' => "selectAll('participants_box',true)"
                                    ),
                                    array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook->id) . "&amp;gradebookBook=1"
                                    )
                                ))."



                        </div>
                    </div>
                ". generate_csrf_token_form_field() ."
                </fieldset>
                </form>
            </div>
        </div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";
}

/**
 * @brief display all users grade
 * @param int $gradebook_id
 */
function display_all_users_grades($gradebook_id) {

    global $course_id, $course_code, $tool_content, $langName, $langSurname,
           $langID, $langGroup, $langRegistrationDateShort, $langGradebookGrade,
           $langGradebookBook, $langGradebookDelete, $langConfirmDelete,
           $langNoRegStudent, $langHere, $langGradebookGradeAlert, $is_editor;

        $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id AS recID, gradebook_users.uid as userID, user.surname AS surname,
                                                                user.givenname AS name, user.am AS am, DATE(course_user.reg_date) AS reg_date
                                                            FROM gradebook_users
                                                            JOIN user ON gradebook_users.uid = user.id AND gradebook_id = ?d
                                                            LEFT JOIN course_user ON user.id = course_user.user_id AND course_user.course_id = ?d
                                                                ORDER BY surname,name", $gradebook_id, $course_id);

    if (count($resultUsers)> 0) {
        $tool_content .= "<div class='table-responsive'><table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr class='list-header'>
                  <th class='count-col'>$langID</th>
                  <th>$langName $langSurname</th>
                  <th>$langGroup</th>
                  <th>$langRegistrationDateShort</th>
                  <th>$langGradebookGrade</th>";
                if ($is_editor) {
                    $tool_content .= "<th class='text-end'>" . icon('fa-cogs') . "</th>";
                }
        $tool_content .= "</tr>
            </thead>
            <tbody>";
        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $classvis = '';
            if (is_null($resultUser->reg_date)) {
                $classvis = 'not_visible';
            }
            $cnt++;
            $tool_content .= "
                <tr class='$classvis'>
                <td class='count-col'>$cnt</td>
                <td>" . display_user($resultUser->userID). "
                    <div class='text-muted'><small>$resultUser->am</small></div>
                </td>
                <td>" . user_groups($course_id, $resultUser->userID) . "</td>
                <td>";
                if (!empty($resultUser->reg_date)) {
                    $tool_content .= format_locale_date(strtotime($resultUser->reg_date), 'short', false);
                } else {
                    $tool_content .= " &mdash; ";
                }
                $tool_content .= "<td>";
                if (weightleft($gradebook_id, 0) == 0) {
                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;u=$resultUser->userID'>" . userGradeTotal($gradebook_id, $resultUser->userID). "</a>";
                } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;u=$resultUser->userID'>" . userGradeTotal($gradebook_id, $resultUser->userID). "</a>" . " (<small>" . $langGradebookGradeAlert . "</small>)";
                }
            $tool_content .="</td>";
            if ($is_editor) {
                $tool_content .= "<td class='option-btn-cell text-end'>" .
                    action_button(array(
                        array('title' => $langGradebookBook,
                            'icon' => 'fa-plus',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;book=$resultUser->userID"),
                        array('title' => $langGradebookDelete,
                            'icon' => 'fa-xmark',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gb=" . getIndirectReference($gradebook_id) . "&amp;ruid=" . getIndirectReference($resultUser->userID) . "&amp;deleteuser=yes",
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete)))
                . "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</tbody></table></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoRegStudent <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;editUsers=1'>$langHere</a>.</span></div>";
    }
}

/**
 * @brief display user grades (student view)
 * @param int $gradebook_id
 * @param int $uid
 */
function student_view_gradebook($gradebook_id, $uid) {

    global $tool_content, $course_code, $is_editor, $action_bar,
           $langGradebookTotalGradeNoInput, $langGradebookTotalGrade, $langGradebookSum,
           $langTitle, $langGradebookActivityDate2, $langGradebookNoTitle, $langType,
           $langGradebookActivityWeight, $langGradebookGrade, $langGradebookAlertToChange, $langBack,
           $langAssignment, $langExercise, $langGradebookActivityAct, $langAttendanceActivity;

    if ($is_editor) {
        $extra = "";
    } else {
        $extra = "AND gradebook_activities.visible = 1";
    }

    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) AS count
                                            FROM gradebook_book, gradebook_activities
                                        WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                                            $extra
                                            AND uid = ?d
                                            AND gradebook_activities.gradebook_id = ?d", $uid, $gradebook_id)->count;

    $back_link = ($is_editor)? "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=". getIndirectReference($gradebook_id) . "&amp;gradebookBook=1" : "$_SERVER[SCRIPT_NAME]?course=$course_code";

    $action_bar = action_bar(array(
        array(  'title' => $langBack,
                'url' => "$back_link",
                'icon' => 'fa-reply',
                'level' => 'primary'),
    ));
    $tool_content .= $action_bar;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookTotalGradeNoInput</span></div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities
                                WHERE gradebook_id = ?d $extra ORDER BY `DATE` DESC", $gradebook_id);
    $results = count($result);

    if ($results > 0) {
        if ($checkForRecords) {
            $range = Database::get()->querySingle("SELECT `range` FROM gradebook WHERE id = ?d", $gradebook_id)->range;
        }
        if(weightleft($gradebook_id, 0) != 0) {
            $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookAlertToChange</span></div>";
        }
        $tool_content .= "<div class='badge Primary-200-bg p-2'>" . display_user($uid, false, false) . "</div>";
        $tool_content .= "<div class='table-responsive'><table class='table-default' >";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th>
                              <th>$langGradebookActivityDate2</th>
                              <th>$langType</th>
                              <th>$langGradebookActivityWeight</th>
                              <th>$langGradebookGrade</th>
                              <th>$langGradebookTotalGrade</th>
                          </tr></thead>";
    }
    if ($result) {
        foreach ($result as $details) {
            $tool_content .= "
                <tr>
                    <td class='text-nowrap'>
                        " .(!empty($details->title) ? q($details->title) : $langGradebookNoTitle) . "
                    </td>
                    <td class='text-nowrap'>
                        " . (!is_null($details->date) ? format_locale_date(strtotime($details->date), 'short', false) : " &mdash;") . "
                    </td>";

            if ($details->module_auto_id) {
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
                    $tool_content .= "<td class='smaller text-nowrap'>$langAssignment";
                }
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
                    $tool_content .= "<td class='smaller text-nowrap'>$langExercise ";
                }
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
                    $tool_content .= "<td class='smaller text-nowrap'>$langGradebookActivityAct";
                }
                $tool_content .= "</td>";
            } else {
                $tool_content .= "<td class='smaller text-nowrap'>$langAttendanceActivity</td>";
            }
            $tool_content .= "<td class='text-nowrap'>" . q($details->weight) . "%</td>";
            $tool_content .= "<td class='text-nowrap'>";
            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT grade FROM gradebook_book
                                                            WHERE gradebook_activity_id = ?d
                                                                AND uid = ?d", $details->id, $uid);
            if ($sql) {
                $tool_content .= round($sql->grade * $range, 2) . ' / '.$range;
            } else {
                $tool_content .= "&mdash;";
            }
            $tool_content .= "</td>";
            $tool_content .= "<td class='text-nowrap'>";
            $tool_content .= $sql ? round($sql->grade * $range * $details->weight / 100, 2)." / $range</td>" : "&mdash;";
            $tool_content .= "</td>
            </tr>";
        } // end of while
        $s_grade = userGradeTotal($gradebook_id, $uid);
        $tool_content .= "
            <tr>
                <th colspan='5' class='text-end text-nowrap'>$langGradebookSum:</th>
                <th>". (($s_grade != "&mdash;") ? $s_grade . " / $range" : "$s_grade"). "</th>
            </tr>";
    }
    $tool_content .= "</table></div>";
}

/**
 * @brief display gradebook activities
 * @param int $gradebook_id
 */
function display_gradebook($gradebook) {

    global $course_code, $urlServer, $tool_content, $langGradebookGradeAlert, $langGradebookNoActMessage1,
           $langTitle, $langViewShow, $langScore, $langHere, $action_bar,
           $langGradebookActivityDate2, $langGradebookWeight, $langGradebookNoTitle, $langType,
           $langGradebookAutoGrade, $langGradebookNoAutoGrade, $langAttendanceActivity, $langDelete, $langConfirmDelete,
           $langEditChange, $langYes, $langNo, $langPreview, $langAssignment, $langGradebookActivityAct, $langGradebookGradeAlert3,
           $langGradebookExams, $langGradebookLabs, $langGradebookOral, $langGradebookProgress, $langGradebookOtherType,
           $langAdd, $langInsertWork, $langExercise, $langLearningPath1, $langInsertExercise,
           $langUnitActivity, $langNoRegStudent, $langStudents, $langRefreshGrade, $langRefreshGrades,
           $langExportGradebook, $langExportGradebookWithUsers,
           $is_editor, $is_course_reviewer, $is_collaborative_course;

    $gradebook_id = getIndirectReference($gradebook->id);
    if ($is_editor) {
        $action_bar = action_bar(
            array(
                array('title' => "$langAdd $langUnitActivity",
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;addActivity=1",
                    'icon' => 'fa fa-solid fa-pencil fa space-after-icon',
                    'level' => 'primary-label',
                    'show' => (isset($is_collaborative_course) and !$is_collaborative_course)),
                array('title' => "$langAdd $langInsertWork",
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;addActivityAs=1",
                    'icon' => 'fa fa-solid fa-upload space-after-icon',
                    'level' => 'primary-label'),
                array('title' => "$langAdd $langInsertExercise",
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;addActivityEx=1",
                    'icon' => 'fa fa-solid fa-file-pen space-after-icon',
                    'level' => 'primary-label',
                    'show' => (isset($is_collaborative_course) and !$is_collaborative_course)),
                array('title' => "$langAdd $langLearningPath1",
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;addActivityLp=1",
                    'icon' => 'fa fa-solid fa-timeline space-after-icon',
                    'level' => 'primary-label',
                    'show' => (isset($is_collaborative_course) and !$is_collaborative_course)),
                array('title' => $langEditChange,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;editSettings=1",
                      'icon' => 'fa-cog'),
                array('title' => $langStudents,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;gradebookBook=1",
                      'icon' => 'fa-users',
                      'level' => 'primary-label'),
                array('title' => $langRefreshGrades,
                      'url' => "refreshgrades.php?course=$course_code&amp;t=2&amp;gradebook_id=$gradebook_id",
                      'icon' => 'fa-refresh'),
                array('title' => $langExportGradebook,
                      'url' => "dumpgradebook.php?course=$course_code&amp;t=1&amp;gradebook_id=$gradebook_id",
                      'icon' => 'fa-file-excel'),
                array('title' => $langExportGradebookWithUsers,
                      'url' => "dumpgradebook.php?course=$course_code&amp;t=2&amp;gradebook_id=$gradebook_id",
                      'icon' => 'fa-file-excel'),
                ),
                true
            );
        $tool_content .= $action_bar;
    }

    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count
                                        FROM gradebook_users WHERE gradebook_id=?d ", $gradebook->id)->count;
    if ($participantsNumber == 0) {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoRegStudent <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook->id) . "&amp;editUsers=1'>$langHere</a>.</span></div>";
    }
    $weightMessage = "";
    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d ORDER BY `DATE` DESC", $gradebook->id);
    $activityNumber = count($result);

    if (!$result or $activityNumber == 0) {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookNoActMessage1</span></div>";
    } else {
        foreach ($result as $details) {
            if ($details->weight == 0 or (empty($details->weight))) { // check if there are activities with 0% weight
                $weightMessage = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookGradeAlert3</span></div>";
            }
        }
        //check if there is spare weight
        if(weightleft($gradebook->id, 0)) {
            $weightMessage = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGradebookGradeAlert (" . weightleft($gradebook->id, 0) . "%)</span></div>";
        }
        $tool_content .= $weightMessage;
        $tool_content .= "
                        <div class='col-sm-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                <thead>
                                    <tr class='list-header'>
                                        <th style='width:30%;'>$langTitle</th>
                                        <th>$langGradebookActivityDate2</th>
                                        <th style='width:30%;'>$langType</th><th>$langGradebookWeight</th>
                                        <th>$langViewShow</th>
                                        <th>$langScore</th>";
                            if ($is_editor) {
                                $tool_content .= "<th class='text-end'>" . icon('fa-cogs') . "</i></th>";
                            }
                        $tool_content .= "</tr></thead>";

        foreach ($result as $details) {
            $activity_id = getIndirectReference($details->id);
            $content = ellipsize_html($details->description, 50);
            $tool_content .= "<tr><td>";
            if ($is_editor) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;ins=$activity_id'>" . (!empty($details->title) ? q($details->title) : $langGradebookNoTitle) . "</a>";
            } else if ($is_course_reviewer) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;gradebookBook=1'>" . (!empty($details->title) ? q($details->title) : $langGradebookNoTitle) . "</a>";
            }
            $tool_content .= "<div><small class='help-block'>";
            switch ($details->activity_type) {
                 case 1: $tool_content .= "($langGradebookOral)"; break;
                 case 2: $tool_content .= "($langGradebookLabs)"; break;
                 case 3: $tool_content .= "($langGradebookProgress)"; break;
                 case 4: $tool_content .= "($langGradebookExams)"; break;
                 case 5: $tool_content .= "($langGradebookOtherType)"; break;
                 default : $tool_content .= "";
             }
            $tool_content .= "</small></div>";
            $tool_content .= "</td><td>";
            if (!empty($details->date)) {
                $tool_content .= "<div class='smaller'>" . format_locale_date(strtotime($details->date), 'short', false) . "</div>";
            }
            $tool_content .= "</td>";

            if ($details->module_auto_id) {
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
                    $tool_content .= "<td class='smaller'>$langAssignment";
                }
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
                    $tool_content .= "<td class='smaller'>$langExercise ";
                }
                if ($details->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
                    $tool_content .= "<td class='smaller'>$langGradebookActivityAct";
                }

                if ($details->auto) {
                    $tool_content .= " <small class='help-block'> ($langGradebookAutoGrade)</small>";
                } else {
                    $tool_content .= " <small class='help-block'> ($langGradebookNoAutoGrade)</small>";
                    }
                $tool_content .= "</td>";
            } else {
                $tool_content .= "<td class='smaller'>$langAttendanceActivity</td>";
            }

            if (fmod($details->weight, 1) !== 0.00) { // if number doesn't contain `.00`
                $tool_content .= "<td>" . $details->weight . "%</td>";
            } else {
                $tool_content .= "<td>" . round($details->weight) . "%</td>";
            }

            $tool_content .= "<td>";
            if ($details->visible) {
                $tool_content .= $langYes;
            } else {
                $tool_content .= $langNo;
            }
            $tool_content .= "</td>";
            $tool_content .= "<td>" . userGradebookTotalActivityStats($details, $gradebook) . "</td>";
            if ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
                $preview_link = "{$urlServer}modules/exercise/results.php?course=$course_code&amp;exerciseId=$details->module_auto_id";
            } elseif ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
                $preview_link = "{$urlServer}modules/work/index.php?course=$course_code&amp;id=$details->module_auto_id";
            } elseif ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
                $preview_link = "{$urlServer}modules/learnPath/detailsAll.php?course=$course_code";
            } else {
                $preview_link = '';
            }
            if ($is_editor) {
                $tool_content .= "<td class='option-btn-cell text-end'>".
                action_button(array(
                            array('title' => $langEditChange,
                                'icon' => 'fa-edit',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;modify=" . $activity_id),
                            array('title' => $langPreview,
                                'icon' => 'fa-plus',
                                'url' => $preview_link,
                                'show' => !empty($preview_link)),
                            array('title' => $langRefreshGrade,
                                'icon' => 'fa-refresh',
                                'url' => "refreshgrades.php?course=$course_code&amp;t=2&amp;gradebook_id=$gradebook_id&amp;activity=$activity_id",
                                'show' => (!empty($preview_link))),
                            array('title' => $langDelete,
                                'icon' => 'fa-xmark',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;delete=" . $activity_id,
                                'confirm' => $langConfirmDelete,
                                'class' => 'delete')
                    )) . "</td>";
            }
            $tool_content .= "</tr>";
        } // end of foreach
        $tool_content .= "</table></div></div>";
    }
}

/**
 * @brief admin available gradebook
 */
function display_gradebooks() {

    global $course_id, $tool_content, $course_code, $langEditChange,
           $langDelete, $langConfirmDelete, $langCreateDuplicate,
           $langAvailableGradebooks, $langNoGradeBooks, $is_editor, $is_course_reviewer,
           $langViewShow, $langViewHide, $langStart, $uid, $langFinish, $langSettingSelect;

    if ($is_course_reviewer) {
        $result = Database::get()->queryArray("SELECT * FROM gradebook WHERE course_id = ?d", $course_id);
    } else {
        $result = Database::get()->queryArray("SELECT gradebook.* "
                . "FROM gradebook, gradebook_users "
                . "WHERE gradebook.active = 1 "
                . "AND gradebook.course_id = ?d "
                . "AND gradebook.id = gradebook_users.gradebook_id AND gradebook_users.uid = ?d", $course_id, $uid);
    }

    if (count($result) == 0) { // no grade-books
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoGradeBooks</span></div>";
    } else {
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'>
        
                            <th style='width:46%;'>$langAvailableGradebooks</th>
                            <th style='width:22%;'>$langStart</th>
                            <th style='width:22%;'>$langFinish</th>";
        if( $is_editor) {
            $tool_content .= "<th style='width:10%;' class='text-end' aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr></thead>";
        foreach ($result as $g) {
            $row_class = !$g->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td style='width:46%;'>
                            <div class='table_td'>
                                <div class='tahle_td_header'>
                                    <div class='line-height-default'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "'>" . q($g->title) . "</a></div>
                                </div>
                            </div>
                        </td>
                        <td style='width:22%;'>" . format_locale_date(strtotime($g->start_date), 'short') . "</td>
                        <td style='width:22%;'>" . format_locale_date(strtotime($g->end_date), 'short') . "</td>
                        ";
            if( $is_editor) {
                $tool_content .= "<td style='width:10%;' class='option-btn-cell text-end'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "&amp;editSettings=1",
                                          'icon' => 'fa-cog'),
                                    array('title' => $g->active ? $langViewHide : $langViewShow,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "&amp;vis=" .
                                                  ($g->active ? '0' : '1'),
                                          'icon' => $g->active ? 'fa-eye-slash' : 'fa-eye'),
                                    array('title' => $langCreateDuplicate,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "&amp;dup=1",
                                          'icon' => 'fa-copy'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_gb=" . getIndirectReference($g->id),
                                          'icon' => 'fa-xmark',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete))
                                        );
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div>";
    }
}


/**
 * @brief display available exercises for adding them to gradebook
 * @param int $gradebook_id
 */
function display_available_exercises($gradebook_id) {

    global $course_id, $course_code, $tool_content, $urlServer,
           $langDescription, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                        AND exercise.active = 1 AND exercise.id
                        NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = " . GRADEBOOK_ACTIVITY_EXERCISE . " AND gradebook_id = ?d)",
                $course_id, $gradebook_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-end'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr></thead>";

        foreach ($checkForExer as $newExerToGradebook) {
            $content = ellipsize_html($newExerToGradebook->description, 50);
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-start'><a href='{$urlServer}modules/exercise/admin.php?course=$course_code&amp;exerciseId=$newExerToGradebook->id&amp;preview=1'>" . q($newExerToGradebook->title) . "</a></td>";
            $tool_content .= "<td>" . $content . "</td>";
            $tool_content .= "<td class='text-end'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newExerToGradebook->id) . "&amp;type=2");
            $tool_content .= "</td></tr>";
        }
        $tool_content .= "</table></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageExe4</span></div>";
    }
}

/**
 * @brief display available assignments for adding them to gradebook
 * @param int $gradebook_id
 */
function display_available_assignments($gradebook_id) {

    global $course_id, $course_code, $tool_content, $urlServer,
           $m, $langDescription, $langAttendanceNoActMessageAss4,
           $langAdd, $langTitle;

    $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d
                    AND assignment.active = 1
                    AND assignment.id NOT IN
                        (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = " . GRADEBOOK_ACTIVITY_ASSIGNMENT . " AND gradebook_id = ?d)", $course_id, $gradebook_id);

    $checkForAssNumber = count($checkForAss);

    if ($checkForAssNumber > 0) {
        $tool_content .= "
            <div class='row'><div class='col-sm-12'><div class='table-responsive'>
                          <table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-end'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr></thead>";
        foreach ($checkForAss as $newAssToGradebook) {
            $content = ellipsize_html($newAssToGradebook->description, 50);
            if ($newAssToGradebook->assign_to_specific == 1) { // assignment to specific users
                $content .= "$m[WorkAssignTo]:<br>";
                $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname, user.givenname FROM `assignment_to_specific`, user WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                foreach ($checkForAssSpec as $checkForAssSpecR) {
                    $content .= q($checkForAssSpecR->surname). " " . q($checkForAssSpecR->givenname) . "<br>";
                }
            }
            if ($newAssToGradebook->assign_to_specific == 2) { // assignment to specific groups
                $content .= "$m[WorkAssignTo]:<br>";
                $checkForAssSpec = Database::get()->queryArray("SELECT group_id, `group`.name FROM `assignment_to_specific`, `group` WHERE assignment_to_specific.group_id= `group`.id AND assignment_id = ?d", $newAssToGradebook->id);
                foreach ($checkForAssSpec as $checkForAssSpecR) {
                    $content .= q($checkForAssSpecR->name) . "<br>";
                }
            }
            $tool_content .= "<tr>";
            $tool_content .= "<td><a href='{$urlServer}modules/work/index.php?course=$course_code&amp;id=$newAssToGradebook->id'>" . q($newAssToGradebook->title) . "</a></td>";
            $tool_content .= "<td>" . $content . "</td>";
            $tool_content .= "<td class='text-end'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newAssToGradebook->id) . "&amp;type=1");
            $tool_content .= "</td></tr>";
        } // end of while
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageAss4</span></div>";
    }
}

/**
 * @brief display available learning paths
 * @param int $gradebook_id
 */
function display_available_lps($gradebook_id) {

    global $course_id, $course_code, $tool_content, $urlServer,
           $langAdd, $langAttendanceNoActMessageLp4, $langTitle,
           $langDescription, $langActions, $langSettingSelect;


    $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE course_id = ?d ORDER BY name
                        AND learnPath_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = " . GRADEBOOK_ACTIVITY_LP . " AND gradebook_id = ?d)", $course_id, $gradebook_id);

    $checkForLpNumber = count($checkForLp);

    if ($checkForLpNumber > 0) {
        $tool_content .= "<div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th aria-label='$langSettingSelect'></th>";
        $tool_content .= "</tr></thead>";
        foreach ($checkForLp as $newExerToGradebook) {
            $tool_content .= "<tr>";
            $tool_content .= "<td><a href='{$urlServer}modules/learnPath/learningPathAdmin.php?course=$course_code&amp;path_id=$newExerToGradebook->learnPath_id'>" . q($newExerToGradebook->name) . "</a></td>";
            $tool_content .= "<td><p>" .q($newExerToGradebook->comment). "</p></td>";
            $tool_content .= "<td class='text-end'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newExerToGradebook->learnPath_id) . "&amp;type=3")."</td>";
            $tool_content .= "</tr>";
        } // end of while
        $tool_content .= "</table></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageLp4</span></div>";
    }
}

/**
 * @brief display users of gradebook
 * @param int $gradebook_id
 * @param int $actID
 */
function register_user_grades($gradebook_id, $actID) {

    global $tool_content, $course_id, $course_code,
            $langID, $langName, $langSurname, $langGroup, $langRegistrationDateShort,
            $langGradebookGrade, $langGradebookNoTitle,
            $langGradebookBooking, $langGradebookTotalGrade,
            $langGradebookActivityWeight, $langCancel;

    //display form and list
    $gradebook_range = get_gradebook_range($gradebook_id);
    $result = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d", $actID);
    $act_type = $result->activity_type; // type of activity
    $tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" .(!empty($result->title) ? q($result->title) : $langGradebookNoTitle) . " <br>
                        <small>$langGradebookActivityWeight: $result->weight%</small></span></div>";
    //display users
    $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id AS recID, gradebook_users.uid as userID, user.surname AS surname,
                                                            user.givenname AS name, user.am AS am, DATE(course_user.reg_date) AS reg_date
                                                        FROM gradebook_users
                                                        JOIN user ON gradebook_users.uid = user.id AND gradebook_id = ?d
                                                        LEFT JOIN course_user ON user.id = course_user.user_id AND course_user.course_id = ?d
                                                            ORDER BY surname,name", $gradebook_id, $course_id);

    if ($resultUsers) {
        $tool_content .= "<div class='col-sm-12'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' id='user_grades_form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($actID) . "'>
        <div class='col-12'>
        <div class='table-responsive'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr class='list-header'>
                    <th class='count-col'>$langID</th>
                    <th>$langName $langSurname</th>
                    <th>$langGroup</th>
                    <th>$langRegistrationDateShort</th>
                    <th style='width:10%;'>$langGradebookGrade</th>
                    <th style='width:10%;'>$langGradebookTotalGrade</th>
                </tr>
            </thead>
            <tbody>";
        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $classvis = '';
            if (is_null($resultUser->reg_date)) {
                $classvis = 'not_visible';
            }
            $cnt++;
            $q = Database::get()->querySingle("SELECT grade FROM gradebook_book
                                                            WHERE gradebook_activity_id = ?d
                                                        AND uid = ?d", $actID, $resultUser->userID);
            $user_grade = $q ? $q->grade : '-';
            $user_ind_id = getIndirectReference($resultUser->userID);
            $grade = Session::has($user_ind_id) ? Session::get($user_ind_id) : ($q ? round($q->grade * $gradebook_range, 2) : '');
            $total_grade = is_numeric($grade) ? round($grade * $result->weight / 100, 2) : ' - ';
            $tool_content .= "
            <tr class='$classvis'>
                <td class='count-col'>$cnt</td>
                <td>" . display_user($resultUser->userID). "
                    <div class='text-muted'><small>$resultUser->am</small></div>
                </td>
                <td>" . user_groups($course_id, $resultUser->userID). "</td>
                <td>";
                if (!empty($resultUser->reg_date)) {
                    $tool_content .= format_locale_date(strtotime($resultUser->reg_date), 'short', false);
                } else {
                    $tool_content .= " &mdash; ";
                }
                $tool_content .= "</td><td class='form-group".(Session::getError(getIndirectReference($resultUser->userID)) ? " has-error" : "")."'>
                    <input class='form-control' type='text' name='usersgrade[".getIndirectReference($resultUser->userID)."]' value = '".$grade."'>
                    <input type='hidden' value='" . getIndirectReference($actID) . "' name='actID'>
                    <span class='help-block Accent-200-cl'>".Session::getError(getIndirectReference($resultUser->userID))."</span>
                </td>
                <td><input class='form-control' type='text' value='$total_grade' disabled></td>
            </tr>";
        }
        $tool_content .= "</tbody></table></div></div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<div class='col-12 d-flex justify-content-end gap-2 mt-4'>" .
                        form_buttons(array(
                            array(
                                'text' => $langGradebookBooking,
                                'name' => 'bookUsersToAct',
                                'value'=> $langGradebookBooking,
                                'class' => 'submitAdminBtn',
                                ))).
                        "<a href='index.php?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "' class='btn cancelAdminBtn'>$langCancel</a>";
        $tool_content .= "</div></div>";
        $tool_content .= generate_csrf_token_form_field()."</form></div></div>";
    }
}

/**
 * @brief add available activity in gradebook
 * @param int $gradebook_id
 * @param int $id
 * @param int $type
 */
function add_gradebook_activity($gradebook_id, $id, $type) {

    global $course_id, $langLearnPath;

    if ($type == GRADEBOOK_ACTIVITY_ASSIGNMENT) { //  add  assignments
        //checking if it's new or not
        $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                        AND assignment.active = 1 AND assignment.id
                                            NOT IN (SELECT module_auto_id FROM gradebook_activities
                                                    WHERE module_auto_type = 1
                                                    AND gradebook_id = ?d)
                                                    AND assignment.id = ?d", $course_id, $gradebook_id, $id);
        if ($checkForAss) {
            $module_auto_id = $checkForAss->id;
            $module_auto_type = GRADEBOOK_ACTIVITY_ASSIGNMENT;
            $module_auto = 1; //auto grade enabled by default
            $module_weight = weightleft($gradebook_id, '');
            $actTitle = $checkForAss->title;
            $actDate = $checkForAss->deadline;
            $actDesc = $checkForAss->description;
            Database::get()->query("INSERT INTO gradebook_activities
                                        SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        weight = ?d, module_auto_id = ?d, auto = ?d, module_auto_type = ?d, visible = 1",
                                    $gradebook_id, $actTitle, $actDate, $actDesc, $module_weight, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            if ($checkForAss->group_submissions) {
                foreach ($sql as $u) {
                    $user_groups = Database::get()->queryArray("SELECT group_id FROM group_members WHERE user_id = ?d", $u->uid);
                    $group_ids = [];
                    foreach ($user_groups as $user_group) {
                        array_push($group_ids, $user_group->group_id);
                    }
                    if (!empty($group_ids)) {
                        $sql_ready_group_ids = implode(', ', $group_ids);
                        $grd = Database::get()->querySingle("SELECT assignment_submit.group_id AS group_id, assignment_submit.grade AS grade, assignment.max_grade AS max_grade "
                                . "FROM assignment_submit, assignment "
                                . "WHERE assignment_submit.assignment_id = assignment.id "
                                . "AND assignment.id =?d "
                                . "AND assignment_submit.group_id IN ($sql_ready_group_ids)", $id);
                        if ($grd) {
                            $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $grd->group_id);
                            foreach ($user_ids as $user_id) {
                                if (isset($grd->grade)) {
                                    update_gradebook_book($user_id, $id, $grd->grade/$grd->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($sql as $u) {
                    $grd = Database::get()->querySingle("SELECT assignment_submit.grade AS grade, assignment.max_grade AS max_grade "
                            . "FROM assignment_submit, assignment "
                            . "WHERE assignment_submit.assignment_id = assignment.id "
                            . "AND assignment.id =?d "
                            . "AND assignment_submit.uid = $u->uid", $id);
                    if ($grd && isset($grd->grade)) {
                        update_gradebook_book($u->uid, $id, $grd->grade/$grd->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    }
                }
            }
        }
    }

    if ($type == GRADEBOOK_ACTIVITY_EXERCISE) { // add exercises
        //checking if it is new or not
        $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                                            AND exercise.active = 1 AND exercise.id
                                                    NOT IN (SELECT module_auto_id FROM gradebook_activities
                                                                WHERE module_auto_type = 2 AND gradebook_id = ?d)
                                                    AND exercise.id = ?d", $course_id, $gradebook_id, $id);
        if ($checkForExe) {
            $module_auto_id = $checkForExe->id;
            $module_auto_type = GRADEBOOK_ACTIVITY_EXERCISE;
            $module_auto = 1;
            $module_weight = weightleft($gradebook_id, '');
            $actTitle = $checkForExe->title;
            $actDate = $checkForExe->end_date;
            $actDesc = $checkForExe->description;

            Database::get()->query("INSERT INTO gradebook_activities
                                        SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        weight = ?d, module_auto_id = ?d, auto = ?d, module_auto_type = ?d, visible = 1",
                                    $gradebook_id, $actTitle, $actDate, $actDesc, $module_weight, $module_auto_id, $module_auto, $module_auto_type);
            $users = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            foreach ($users as $user) {
                $exerciseUserRecord = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record "
                                                . "WHERE eid = ?d "
                                                . "AND uid = $user->uid "
                                                . "AND attempt_status = " . ATTEMPT_COMPLETED . "
                                                ORDER BY total_score/total_weighting DESC limit 1", $id);
                if ($exerciseUserRecord) {
                    update_gradebook_book($user->uid, $id, $exerciseUserRecord->total_score/$exerciseUserRecord->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE, $gradebook_id);
                }
            }
        }
    }
    if ($type == GRADEBOOK_ACTIVITY_LP) {    // add learning path
        //checking if it is new or not
        $checkForLp = Database::get()->querySingle("SELECT * FROM lp_learnPath WHERE course_id = ?d
                                                    AND learnPath_id NOT IN
                                                (SELECT module_auto_id FROM gradebook_activities
                                                    WHERE module_auto_type = 3
                                                    AND gradebook_id = ?d)
                                                    AND learnPath_id = ?d", $course_id, $gradebook_id, $id);
        if ($checkForLp) {
            $module_auto_id = $checkForLp->learnPath_id;
            $module_auto_type = GRADEBOOK_ACTIVITY_LP;
            $module_auto = 1;
            $module_weight = weightleft($gradebook_id, '');
            $actTitle = $checkForLp->name;
            $actDate = date("Y-m-d");
            $actDesc = $langLearnPath . ": " . $checkForLp->name;
            Database::get()->query("INSERT INTO gradebook_activities
                            SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                weight = ?d, module_auto_id = ?d, auto = ?d, module_auto_type = ?d, visible = 1",
                            $gradebook_id, $actTitle, $actDate, $actDesc, $module_weight, $module_auto_id, $module_auto, $module_auto_type);
            require_once 'include/lib/learnPathLib.inc.php';
            $users = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            foreach ($users as $user) {
                $lpProgress = get_learnPath_progress($module_auto_id, $user->uid);
                if ($lpProgress) {
                    update_gradebook_book($user->uid, $id, $lpProgress/100, GRADEBOOK_ACTIVITY_LP, $gradebook_id);
                }
            }
        }
    }
    return array('act_title' => $actTitle, 'act_date' => $actDate, 'act_descr' => $actDesc);
}

/**
 * @param int $gradebook_id
 * @param int $uid
 */
function update_user_gradebook_activities($gradebook_id, $uid) {

    require_once 'include/lib/learnPathLib.inc.php';

    $gradebook = Database::get()->querySingle("SELECT * FROM gradebook WHERE id = ?d", $gradebook_id);
    $gradebookActivities = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d AND auto = 1", $gradebook_id);
    foreach ($gradebookActivities as $gradebookActivity) {
        if ($gradebookActivity->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
            $grade = get_learnPath_progress($gradebookActivity->module_auto_id, $uid)/100;
            $allow_insert = $grade ? TRUE : FALSE;
        } elseif ($gradebookActivity->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
            $exerciseUserRecord = Database::get()->querySingle("SELECT total_score, total_weighting "
                    . "FROM exercise_user_record "
                    . "WHERE eid = ?d "
                    . "AND uid = ?d "
                    . "AND attempt_status = " . ATTEMPT_COMPLETED . " "
                    . "AND record_end_date <= '$gradebook->end_date' "
                    . "AND record_end_date >= '$gradebook->start_date' "
                    . "ORDER BY total_score/total_weighting DESC limit 1", $gradebookActivity->module_auto_id, $uid);
            if ($exerciseUserRecord) {
                $grade = $exerciseUserRecord->total_score/$exerciseUserRecord->total_weighting;
                $allow_insert = TRUE;
            }
        } elseif ($gradebookActivity->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
            $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $gradebookActivity->module_auto_id);
            if ($assignment->group_submissions) {
                $group_members = Database::get()->queryArray("SELECT group_id FROM group_members WHERE user_id = ?d", $uid);
                if ($group_members) {
                    $group_ids_arr = [];
                    foreach ($group_members as $group_member) {
                        array_push($group_ids_arr, $group_member->group_id);
                    }
                    $sql_ready_group_ids = implode(', ', $group_ids_arr);
                    $grd = Database::get()->querySingle("SELECT assignment_submit.grade AS grade, assignment.max_grade AS max_grade "
                            . "FROM assignment_submit, assignment "
                            . "WHERE assignment_submit.assignment_id = assignment.id "
                            . "AND assignment.id =?d "
                            . "AND assignment_submit.submission_date <= '$gradebook->end_date' "
                            . "AND assignment_submit.submission_date >= '$gradebook->start_date' "
                            . "AND assignment_submit.group_id IN ($sql_ready_group_ids)", $gradebookActivity->module_auto_id);
                }
            } else {
                $grd = Database::get()->querySingle("SELECT assignment_submit.grade AS grade, assignment.max_grade AS max_grade "
                        . "FROM assignment_submit, assignment "
                        . "WHERE assignment_submit.assignment_id = assignment.id "
                        . "AND assignment.id =?d "
                        . "AND assignment_submit.submission_date <= '$gradebook->end_date' "
                        . "AND assignment_submit.submission_date >= '$gradebook->start_date' "
                        . "AND assignment_submit.uid = $uid", $gradebookActivity->module_auto_id);
            }
            if (isset($grd) && $grd && isset($grd->grade)) {
                $grade = $grd->grade/$grd->max_grade;
                $allow_insert = TRUE;
            }
        }
        if (isset($allow_insert) && $allow_insert) {
            update_gradebook_book($uid, $gradebookActivity->module_auto_id, $grade, $gradebookActivity->module_auto_type, $gradebook_id);
        }
        unset($allow_insert);
    }
}
/**
 * @brief display form for adding other activity in gradebook
 * @param int $gradebook_id
 */
function add_gradebook_other_activity($gradebook_id) {

    global $tool_content, $course_code, $visible,
           $langTitle, $langGradebookActivityDate2, $langGradebookActivityWeight, $langBBB,
           $langGradeVisible, $langComments, $langGradebookAutoGrade, $langAssignment,
           $langAdd, $langType, $langGradebookExams, $langGradebookLabs, $langExercise,
           $langGradebookOral, $langGradebookProgress, $langGradebookOtherType, $langLearnPath,
           $langGradebookRemainingGrade, $langSave, $head_content, $language, $urlAppend, $langImgFormsDes, $langSelect;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
    $(function() {
            $('#date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
    });
    </script>";

    $weight_error = Session::getError('weight');
    $date_error = Session::getError('date');
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "'>";
                        if (isset($_GET['modify'])) { // modify an existing gradebook activity
                            $id  = filter_var(getDirectReference($_GET['modify']), FILTER_VALIDATE_INT);
                            //All activity data (check if it's in this gradebook)
                            $modifyActivity = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $id, $gradebook_id);
                            if ($modifyActivity) {
                                $titleToModify = $modifyActivity->title;
                                $contentToModify = $modifyActivity->description;
                                if (is_null($modifyActivity->date)) {
                                    $oldDate = '';
                                } else {
                                    $oldDate = DateTime::createFromFormat('Y-m-d H:i:s', $modifyActivity->date)->format('d-m-Y H:i:s');
                                }
                                $date = Session::has('date') ? Session::get('date') : $oldDate;
                                $module_auto_id = $modifyActivity->module_auto_id;
                                if ($modifyActivity->module_auto_type) {
                                    switch ($modifyActivity->module_auto_type) {
                                        case GRADEBOOK_ACTIVITY_ASSIGNMENT:
                                            $module_auto_label = $langAssignment;
                                            break;
                                        case GRADEBOOK_ACTIVITY_EXERCISE:
                                            $module_auto_label = $langExercise;
                                            break;
                                        case GRADEBOOK_ACTIVITY_LP:
                                            $module_auto_label = $langLearnPath;
                                            break;
                                        case GRADEBOOK_ACTIVITY_TC:
                                            $module_auto_label = $langBBB;
                                            break;
                                    }
                                }
                                $auto = $modifyActivity->auto;
                                $weight = Session::has('weight') ? Session::get('title') : $modifyActivity->weight;
                                $activity_type = $modifyActivity->activity_type;
                                $visible = $modifyActivity->visible;
                            } else {
                                $activity_type = '';
                            }
                            $gradebookActivityToModify = $id;
                        } else { //new activity
                            $gradebookActivityToModify = $activity_type = $weight = '';
                            $date = date('d-m-Y H:i:s', time());
                            $visible = 1;
                        }
                        if (!isset($contentToModify)) {
                            $contentToModify = "";
                        }
                        @$tool_content .= "
                        
               
                                <div class='form-group'>
                                    <div class='col-sm-6 control-label-notes'>$langType</div>
                                    <div class='col-sm-12'>" . (isset($module_auto_label)? "
                                        <p class='form-control-static'>$module_auto_label</p>": "
                                        <select name='activity_type' class='form-select' id='activity_type_id' aria-label='$langType'>
                                            <option value=''  " . typeSelected($activity_type, '') . " >-</option>
                                            <option value='4' " . typeSelected($activity_type, 4) . " >" . $langGradebookExams . "</option>
                                            <option value='2' " . typeSelected($activity_type, 2) . " >" . $langGradebookLabs . "</option>
                                            <option value='1' " . typeSelected($activity_type, 1) . " >" . $langGradebookOral . "</option>
                                            <option value='3' " . typeSelected($activity_type, 3) . " >" . $langGradebookProgress . "</option>
                                            <option value='5' " . typeSelected($activity_type, 5) . " >" . $langGradebookOtherType . "</option>
                                        </select>") . "
                                    </div>
                                </div>
                    
                            
                                <div class='form-group mt-4'>
                                    <label for='actTitle' class='col-sm-6 control-label-notes'>$langTitle</label>
                                    <div class='col-sm-12'>
                                        <input id='actTitle' type='text' class='form-control' name='actTitle' value='".q($titleToModify)."'/>
                                    </div>
                                </div>
                            
                        ";
                        if (isset($modifyActivity) and $modifyActivity->module_auto_type == 0) {
                            $tool_content .= "<div class='form-group".($date_error ? " has-error" : "")." mt-4'>
                                <label for='date' class='col-sm-12 control-label-notes'>$langGradebookActivityDate2:</label>
                                <div class='col-sm-12'>
                                    <input type='text' class='form-control' name='date' id='date' value='" . datetime_remove_seconds($date) . "'/>
                                    <span class='help-block'>$date_error</span>
                                </div>
                            </div>";
                        }
                        $tool_content .= "<div class='form-group".($weight_error ? " has-error" : "")." mt-4'>
                            <label for='weight' class='col-sm-12 control-label-notes'>$langGradebookActivityWeight</label>
                            <div class='col-sm-12'>
                                <input id='weight' type='text' class='form-control' name='weight' value='$weight' size='5'>
                                <span class='help-block'>". ($weight_error ? $weight_error :  "($langGradebookRemainingGrade: " . weightleft($gradebook_id, '') . "%)")."</span>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                           
                            <div class='col-12'>
                                <label class='label-container' aria-label='$langSelect'>
                                    <input type='checkbox' id='visible' name='visible' value='1'";
                                    if ($visible == 1) {
                                        $tool_content .= " checked";
                                    }
                                $tool_content .= " />
                                <span class='checkmark'></span>
                                $langGradeVisible
                                </label>
                            
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='actDesc' class='col-sm-12 control-label-notes'>$langComments</label>
                            <div class='col-sm-12'>
                                " . rich_text_editor('actDesc', 4, 20, $contentToModify) . "
                            </div>
                        </div>";
                        if (isset($module_auto_id) && $module_auto_id != 0) { //accept the auto booking mechanism
                            $tool_content .= "<div class='form-group mt-4'>
                                    <div class='col-12'>
                                    <label class='label-container' aria-label='$langSelect'>
                                    <input type='checkbox' value='1' name='auto' ";
                            if ($auto) {
                                $tool_content .= " checked";
                            }
                            $tool_content .= "/>
                            <span class='checkmark'></span>
                            $langGradebookAutoGrade
                            </label></div></div>";
                        }
                        $tool_content .= "<div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center'>


                                        ".form_buttons(array(
                                            array(
                                                'class' => 'submitAdminBtn',
                                                'text' => $langSave,
                                                'name' => 'submitGradebookActivity',
                                                'value'=> $langAdd
                                            ),
                                            array(
                                                'class' => 'cancelAdminBtn ms-1',
                                                'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                            )
                                        ))."



                                </div></div>";
                        if (isset($_GET['modify'])) {
                            $tool_content .= "<input type='hidden' name='id' value='" . getIndirectReference($gradebookActivityToModify) . "'>";
                        } else {
                            $tool_content .= " <input type='hidden' name='id' value='' >";
                        }
                    $tool_content .= "
                ". generate_csrf_token_form_field() ."
                </form>
            </div>
        </div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";
}


/**
 * @brief insert grades for activity
 * @param int $gradebook_id
 * @param int $actID
 */
function insert_grades($gradebook_id, $actID) {

    global $tool_content, $langGradebookEdit, $gradebook, $langTheField,
           $course_code, $langFormErrors, $langGradebookGrade, $course_id;

    $v = new Valitron\Validator($_POST['usersgrade']);
    $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
        if(is_numeric($value) || empty($value)) return true;
    });
    foreach ($_POST['usersgrade'] as $userID => $userInp) {
        $v->rule('emptyOrNumeric', array("$userID"));
        $v->rule('min', array("$userID"), 0);
        $v->rule('max', array("$userID"), $gradebook->range);
        $v->labels(array(
            "$userID" => "$langTheField $langGradebookGrade"
        ));
    }
    if($v->validate()) {
        foreach ($_POST['usersgrade'] as $userID => $userInp) {
            $uid = getDirectReference($userID);
            if ($userInp == '') {
                Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, $uid);
            } else {
                Database::get()->query("INSERT INTO gradebook_book (uid, gradebook_activity_id, grade, comments)
                                            VALUES (?d, ?d, ?f, ?s)
                                        ON DUPLICATE KEY UPDATE grade = ?f",
                            $uid, $actID, $userInp/$gradebook->range, '', $userInp/$gradebook->range);
            }
            triggerGameGradebook($course_id, $uid, $gradebook_id);
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=".getIndirectReference($gradebook->id)."&ins=".getIndirectReference($actID));
    }


    $message = "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langGradebookEdit</span></div>";
    $tool_content .= $message . "<br/>";
}

/**
 * @brief import user grades in gradebook activity from a csv file
 * @param $gradebook_id
 * @param $activity_id
 * @return void
 */
function import_grades($gradebook_id, $activity_id, $import = false) {

    global $tool_content, $course_code, $langGradebookUsers,
           $langImportGradesGradebookHelp, $langWorkFile, $langUpload,
           $langImportInvalidUsers, $langImportGradesError, $langImportErrorLines,
           $langImportExtraGradebookUsers, $langGradesImported, $urlAppend, $langImgFormsDes, $langForm;

    if ($import and isset($_FILES['userfile'])) { // import user grades
        $gradebook_range = get_gradebook_range($gradebook_id);

        $file = IOFactory::load($_FILES['userfile']['tmp_name']);
        $sheet = $file->getActiveSheet();
        $userGrades = $errorLines = $invalidUsers = $extraUsers = [];

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($row = 1; $row <= $highestRow; ++$row) {
            if ($row <= 4) { // first 4 rows are headers
                continue;
            } else {
                for ($col = 4; $col <= $highestColumnIndex; $col = $col + 2) {
                    $cells = [$col, $row];
                    $value = trim($sheet->getCell($cells)->getValue());
                    $data[] = $value;
                }
                if (!is_numeric($data[1]) or $data[1] < 0 or $data[1] > $gradebook_range) {
                    $errorLines[] = $data;
                }
                if (preg_match('/\(([^)]+)\)/', $data[0], $matches)) {
                    $username = $matches[1];
                } else {
                    $username = $data[0];
                }
                $uname_where = (get_config('case_insensitive_usernames')) ? "COLLATE utf8mb4_general_ci = " : "COLLATE utf8mb4_bin = ";
                $user = Database::get()->querySingle("SELECT * FROM user WHERE username $uname_where ?s", $username);

                if (!$user) {
                    $invalidUsers[] = $username;
                } else {
                    $submission = Database::get()->querySingle("SELECT id FROM gradebook_users WHERE uid = ?d AND gradebook_id = ?d",
                        $user->id, $gradebook_id);
                    if (!$submission) {
                        $extraUsers[] = $username;
                    } else {
                        $userGrades[$user->id] = $data[1];
                    }
                }
                $data = [];
            }
        }

        if (!($errorLines or $invalidUsers or $extraUsers)) {
            foreach ($userGrades as $user_id => $grade) {
                Database::get()->query("INSERT INTO gradebook_book (uid, gradebook_activity_id, grade, comments)
                                            VALUES (?d, ?d, ?f, ?s)
                                        ON DUPLICATE KEY UPDATE grade = ?f",
                    $user_id, $activity_id, $grade/$gradebook_range, '', $grade/$gradebook_range);
            }
            Session::flash('message', $langGradesImported);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "&ins=" . getIndirectReference($activity_id));
        } else {
            $message = $langImportGradesError;
            if ($invalidUsers) {
                $errorText = implode('', array_map(function ($username) {
                    return '<li>' . q($username) . '</li>';
                }, $invalidUsers));
                $message .= "<p>$langImportInvalidUsers<ul>$errorText</ul></p>";
            }
            if ($extraUsers) {
                $errorText = implode('', array_map(function ($username) {
                    return '<li>' . q($username) . '</li>';
                }, $extraUsers));
                $message .= "<p>$langImportExtraGradebookUsers<ul>$errorText</ul></p>";
            }
            if ($errorLines) {
                $errorText = implode('', array_map(function ($line) {
                    $line = array_map('q', $line);
                    return '<tr class="danger"><td>' . implode('</td><td>', $line) . '</td></tr>';
                }, $errorLines));
                $message .= "<p>$langImportErrorLines
                    <table class='table table-condensed table-bordered table-striped'>
                        <tbody>$errorText</tbody>
                    </table></p>";
            }
            Session::flash('message', $message);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "&ins=" . getIndirectReference($activity_id));
        }
    } else { // import grades form
        enableCheckFileSize();
        $tool_content .= "
            
            <div class='d-lg-flex gap-4 mt-4'>
                <div class='flex-grow-1'>
                    <div class='form-wrapper'>
                        <form class='form-horizontal' enctype='multipart/form-data' method='post' 
                            action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;imp=$activity_id&amp;import_grades=true'>
                            <fieldset>
                                <legend class='mb-0' aria-label='$langForm'></legend>
                                <div class='form-group'>
                                    <div class='col-sm-12'>
                                        <p class='form-control-static'>$langImportGradesGradebookHelp</p>
                                        <a href='dumpgradebook.php?course=$course_code&t=3&gradebook_id=" . getIndirectReference($gradebook_id) . "&activity_id=$activity_id'>$langGradebookUsers</a>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='userfile' class='col-sm-12 form-label'>$langWorkFile:</label>
                                    <div class='col-sm-10'>" . fileSizeHidenInput() . "
                                        <input type='file' id='userfile' name='userfile'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-12 d-flex justify-content-end'>" .
                    form_buttons([['class' => 'btn-primary',
                                    'name' => 'new_assign',
                                    'value' => $langUpload,
                                    'javascript' => ''],
                                    ['class' => 'btn cancelAdminBtn','href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($activity_id) . ""]]) . "
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div><div class='d-none d-lg-block'>
                <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
            </div>
            </div>
            ";
    }
}


/**
 * @brief update gradebook with user grade
 * @param int $uid
 * @param int $id
 * @param float $grade
 * @param int $activity
 */
function update_gradebook_book($uid, $id, $grade, $activity, $gradebook_id = 0)
{
    global $course_id;

    $params = [$activity, $id];
    $sql = "SELECT gradebook_activities.id, gradebook_activities.gradebook_id
                FROM gradebook_activities, gradebook
               WHERE gradebook_activities.module_auto_type = ?d
                 AND gradebook_activities.module_auto_id = ?d
                 AND gradebook_activities.auto = 1
                 AND gradebook_activities.gradebook_id = gradebook.id
                 AND gradebook_activities.gradebook_id ";
    if ($gradebook_id) {
        $sql .= "= ?d";
        array_push($params, $gradebook_id);
    } else {
        $sql .= "IN (
                    SELECT gradebook_id
                    FROM gradebook_users
                    WHERE uid = ?d)";
        array_push($params, $uid);
    }
    // This query gets the gradebook activities that:
    // 1) belong to gradebooks (or specific gradebook if $gradebook_id != 0) within the date constraints of a specific module
    // 2) have grade auto-submission enabled
    // 3) include a specific user
    $gradebookActivities = Database::get()->queryArray($sql, $params);
    if ($gradebookActivities) {
        foreach($gradebookActivities as $gradebookActivity){
            $gradebook_id = $gradebookActivity->gradebook_id;
            $gradebook_book = Database::get()->querySingle("SELECT grade
                    FROM gradebook_book
                    WHERE gradebook_activity_id = ?d AND uid = ?d",
                $gradebookActivity->id, $uid);
            if ($gradebook_book) {
                if (is_null($grade)) {
                    Database::get()->query('DELETE FROM gradebook_book
                            WHERE gradebook_activity_id = ?d AND uid = ?d',
                        $gradebookActivity->id, $uid);
                } elseif ($grade > $gradebook_book->grade or
                          ($grade < $gradebook_book->grade and $activity == GRADEBOOK_ACTIVITY_ASSIGNMENT)) {
                    Database::get()->query('UPDATE gradebook_book
                                                SET grade = ?f
                                                WHERE gradebook_activity_id = ?d AND uid = ?d',
                        $grade, $gradebookActivity->id, $uid);
                }
            } elseif (!is_null($grade)) {
                Database::get()->query("INSERT INTO gradebook_book (uid, gradebook_activity_id, grade, comments)
                                            VALUES (?d, ?d, ?f, ?s)
                                        ON DUPLICATE KEY UPDATE grade = ?f",
                    $uid, $gradebookActivity->id, $grade, '', $grade);
            }
            triggerGameGradebook($course_id, $uid, $gradebook_id);
        }
    }
}

/**
 * @brief function to help selected option
 * @param type $type
 * @param type $optionType
 * @return string
 */
function typeSelected($type, $optionType){
    if($type == $optionType){
        return "selected";
    }
}


/**
 * @brief calculate the weight left
 * @param type $gradebook_id
 * @param type $currentActivity
 * @return int
 */
function weightleft($gradebook_id, $currentActivity){

    if($currentActivity){
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d AND id != ?d", $gradebook_id, $currentActivity)->count;
    } else {
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id)->count;
    }
    if($left >= 0 ){
        return 100-$left;
    } else {
        return 0;
    }

}

/**
 * @brief return auto grades
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @param type $range
 * @return string
 */
function attendForAutoGrades($userID, $exeID, $exeType, $range) {

    if ($exeType == GRADEBOOK_ACTIVITY_ASSIGNMENT) { // valid assignment submission!
       $autoAttend = Database::get()->querySingle("SELECT grade, max_grade FROM assignment_submit,assignment  WHERE assignment.id = assignment_id AND uid = ?d AND assignment_id = ?d", $userID, $exeID);
       if ($autoAttend) {
           $score = $autoAttend->grade;
           $scoreMax = $autoAttend->max_grade;
           if ($score >= 0) {
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == GRADEBOOK_ACTIVITY_EXERCISE) { //exercises (fetch the last attempt if there are more than one)
       $autoAttend = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record WHERE uid = ?d AND eid = ?d ORDER BY `record_end_date` DESC LIMIT 1", $userID, $exeID);
       if ($autoAttend) {
           $score = $autoAttend->total_score;
           $scoreMax = $autoAttend->total_weighting;
           if ($score >= 0) {
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == GRADEBOOK_ACTIVITY_LP) { // lps (exes and scorms)
       $autoAttend = Database::get()->querySingle("SELECT raw, scoreMax
               FROM lp_user_module_progress, lp_rel_learnPath_module, lp_module
               WHERE lp_module.module_id = ?d
               AND lp_user_module_progress.user_id = ?d
               AND lp_module.module_id = lp_rel_learnPath_module.module_id
               AND lp_rel_learnPath_module.learnPath_module_id = lp_user_module_progress.learnPath_module_id
               AND (lp_user_module_progress.lesson_status = 'FAILED' OR lp_user_module_progress.lesson_status = 'PASSED' OR lp_user_module_progress.lesson_status = 'COMPLETED')
               ", $exeID, $userID);
       if ($autoAttend) {
           $score = $autoAttend->raw;
           $scoreMax = $autoAttend->scoreMax;
           if ($score >= 0) { //to avoid the -1 for no score
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    }
}


/**
 * @brief get total number of user attend in a course gradebook
 * @param type $gradebook_id
 * @param type $userID
 * @param type $csv_output
 * @return string
 */
function userGradeTotal($gradebook_id, $userID, $csv_output = false) {

    global $is_editor;

    if ($is_editor) {
        $extra = "";
    } else {
        $extra = "AND gradebook_activities.visible = 1";
    }
    $character = ($csv_output)? "-": "&mdash;";

    $range = Database::get()->querySingle("SELECT * FROM gradebook WHERE id = ?d", $gradebook_id)->range;
    $userGradeTotal = Database::get()->querySingle("SELECT SUM(gradebook_activities.weight / 100 * gradebook_book.grade * $range) AS count FROM gradebook_book, gradebook_activities, gradebook
                                                    WHERE gradebook_book.uid = ?d
                                                        AND gradebook_book.gradebook_activity_id = gradebook_activities.id
                                                        AND gradebook.id = gradebook_activities.gradebook_id
                                                        AND gradebook_activities.gradebook_id = ?d $extra",
                                                    $userID, $gradebook_id)->count;

    if ($userGradeTotal) {
        return round($userGradeTotal, 2);
    } else {
        return $character;
    }
}

/**
 * @brief function to get the total gradebook number
 * @global type $langUsers
 * @global type $langMeanValue
 * @global type $langMinValue
 * @global type $langMaxValue
 * @param type $activityID
 * @param type $gradebook_id
 * @return string
 */
function userGradebookTotalActivityStats ($activity, $gradebook) {

    global $langUsers, $langMeanValue, $langMinValue, $langMaxValue;

    $users = Database::get()->querySingle("SELECT SUM(grade) as count, COUNT(DISTINCT gradebook_users.uid) AS users
                                        FROM gradebook_book, gradebook_users
                                        WHERE gradebook_users.uid=gradebook_book.uid
                                    AND gradebook_activity_id = ?d
                                    AND gradebook_users.gradebook_id = ?d ", $activity->id, $gradebook->id);

    $sumGrade = $users->count;
    //this is different than global participants number (it is limited to those that have taken degree)
    $participantsNumber = $users->users;
    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade ASC limit 1 ", $activity->id, $gradebook->id);
    if ($q) {
        $userGradebookTotalActivityMin = $q->grade * $gradebook->range;
    }
    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade DESC limit 1 ", $activity->id, $gradebook->id);
    if ($q) {
        $userGradebookTotalActivityMax = $q->grade * $gradebook->range;
    }
    $total_score = $gradebook->range;

    //check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round($sumGrade * $gradebook->range / $participantsNumber, 2);
        return "<i>$langUsers:</i> $participantsNumber<br>"
             . "$langMinValue: " . round($userGradebookTotalActivityMin, 2) . "/$total_score<br> "
             . "$langMaxValue: " . round($userGradebookTotalActivityMax, 2) . "/$total_score<br> "
             . "<i>$langMeanValue:</i> $mean/$total_score";
    } else {
        return "-";
    }
}


/**
 * @brief get gradebook range
 * @param type $gradebook_id
 * @return type
 */
function get_gradebook_range($gradebook_id) {

    $gd_range = Database::get()->querySingle("SELECT `range` FROM gradebook WHERE id = ?d", $gradebook_id)->range;

    return $gd_range;

}

/**
 * @brief get gradebook title
 * @param type $gradebook_id
 * @return type
 */
function get_gradebook_title($gradebook_id) {

    $gd_title = Database::get()->querySingle("SELECT title FROM gradebook WHERE id = ?d", $gradebook_id)->title;

    return $gd_title;
}

/**
 * @brief get activity title from given gradebook and activity
 * @param type $gradebook_id
 * @param type $activity_id
 * @return type
 */
function get_gradebook_activity_title($gradebook_id, $activity_id) {

    $act_title = Database::get()->querySingle("SELECT title FROM gradebook_activities WHERE id = ?d
                                                AND gradebook_id = ?d", $activity_id, $gradebook_id)->title;

    return $act_title;
}

/**
 * @brief gamification
 * @param $courseId
 * @param $uid
 * @param $gradebookId
 */
function triggerGameGradebook($courseId, $uid, $gradebookId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = GradebookEvent::ACTIVITY;
    $eventData->module = MODULE_ID_GRADEBOOK;
    $eventData->resource = intval($gradebookId);
    GradebookEvent::trigger(GradebookEvent::UPGRADE, $eventData);
}
