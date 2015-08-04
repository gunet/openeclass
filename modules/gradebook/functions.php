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


/**
 * @brief display user grades (teacher view)
 * @global type $course_code
 * @global type $tool_content
 * @global type $langTitle
 * @global type $langGradebookActivityDate2
 * @global type $langType
 * @global type $langGradebookNewUser
 * @global type $langGradebookWeight
 * @global type $langGradebookBooking
 * @global type $langGradebookNoActMessage1
 * @global type $langGradebookNoActMessage2
 * @global type $langGradebookNoActMessage3
 * @global type $langGradebookActCour
 * @global type $langGradebookAutoGrade
 * @global type $langGradebookΝοAutoGrade
 * @global type $langGradebookActAttend
 * @global type $langGradebookOutRange
 * @global type $langGradebookUpToDegree
 * @global type $langGradeNoBookAlert
 * @global type $langGradebookGrade
 * @param type $gradebook_id
 */
function display_user_grades($gradebook_id) {

    global $course_code, $tool_content,
           $langTitle, $langGradebookActivityDate2, $langType, $langGradebookNewUser,
           $langGradebookWeight, $langGradebookBooking, $langGradebookNoActMessage1,
           $langGradebookNoActMessage2, $langGradebookNoActMessage3, $langGradebookActCour,
           $langGradebookAutoGrade, $langGradebookΝοAutoGrade, $langGradebookActAttend,
           $langGradebookOutRange, $langGradebookUpToDegree, $langGradeNoBookAlert, $langGradebookGrade;

    $gradebook_range = get_gradebook_range($gradebook_id);

    if(weightleft($gradebook_id, 0) == 0) {
        $userID = intval($_GET['book']); //user
        //check if there are booking records for the user, otherwise alert message for first input
        $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) AS count FROM gradebook_book, gradebook_activities
                            WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                            AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
        if(!$checkForRecords) {
            $tool_content .="<div class='alert alert-success'>$langGradebookNewUser</div>";
        }

        //get all the activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
        $actNumber = count($result);
        if ($actNumber > 0) {
            $tool_content .= "<h5>" . display_user($userID) . " ($langGradebookGrade: " . userGradeTotal($gradebook_id, $userID) . ")</h5>";
            $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                              <table class='table-default'>";
            $tool_content .= "<tr><th>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langType</th><th>$langGradebookWeight</th>";
            $tool_content .= "<th width='10' class='text-center'>$langGradebookBooking</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
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
                            $userGrade = $qusergrade->grade;
                        }
                    }
                } else {
                    $qusergrade = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if ($qusergrade) {
                        $userGrade = $qusergrade->grade;
                    }
                }

                $content = standard_text_escape($activity->description);

                $tool_content .= "<tr><td><b>";

                if (!empty($activity->title)) {
                    $tool_content .= q($activity->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>";
                if($activity->date){
                    $tool_content .= "<td><div class='smaller'><span class='day'>" . nice_format($activity->date, true, true) . "</div></td>";
                } else {
                    $tool_content .= "<td>-</td>";
                }
                if ($activity->module_auto_id) {
                    $tool_content .= "<td class='smaller'>$langGradebookActCour";
                    if ($activity->auto) {
                        $tool_content .= "<br>($langGradebookAutoGrade)";
                    } else {
                        $tool_content .= "<br>($langGradebookΝοAutoGrade)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langGradebookActAttend</td>";
                }
                $tool_content .= "<td width='' class='text-center'>" . $activity->weight . "%</td>";
                @$tool_content .= "<td class='text-center'>
                <input style='width:30px' type='text' value='".$userGrade."' name='" . getIndirectReference($activity->id) . "'"; //SOS 4 the UI!!
                $tool_content .= ">
                <input type='hidden' value='" . getIndirectReference($userID) . "' name='userID'>
                </td>";
            } // end of while
        }
        $tool_content .= "</tr></table>";
        $tool_content .= "<div class='pull-right'><input class='btn btn-primary' type='submit' name='bookUser' value='$langGradebookBooking'>".generate_csrf_token_form_field()."</div>";

        if(userGradeTotal($gradebook_id, $userID) > $gradebook_range){
            $tool_content .= "<br>" . $langGradebookOutRange;
        }
        $tool_content .= "<span class='help-block'><small>" . $langGradebookUpToDegree . $gradebook_range . "</small></span>";
    } else {
        $tool_content .= "<div class='alert alert-success'>$langGradeNoBookAlert " . weightleft($gradebook_id, 0) . "%</div>";
    }
}



/**
 * @brief display form for creating a new gradebook
 * @global string $tool_content
 * @global type $course_code
 * @global type $langNewGradebook
 * @global type $langNewGradebook2
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 */
function new_gradebook() {

    global $tool_content, $course_code,
           $langTitle, $langSave, $langInsert, $langGradebookRange, $langGradeScalesSelect;
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : '';
    $degreerange_error  = Session::getError('degreerange');
    $degreerange = Session::has('degreerange') ? Session::get('degreerange') : 0;
    $tool_content .= 
        "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&newGradebook=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group".($title_error ? " has-error" : "")."'>
                    <div class='col-xs-12'>
                        <label>$langTitle</label>                      
                    </div>
                    <div class='col-xs-12'>
                        <input class='form-control' type='text' name='title' value='$title'>
                        <span class='help-block'>$title_error</span>
                    </div>
                </div>
                <div class='form-group".($degreerange_error ? " has-error" : "")."'>
                    <label class='col-xs-12'>$langGradebookRange</label>
                    <div class='col-xs-12'>
                        <select name='degreerange' class='form-control'>
                            <option value".($degreerange == 0 ? ' selected' : '').">$langGradeScalesSelect</option>
                            <option value='5'".($degreerange == 5 ? ' selected' : '').">0-5</option>
                            <option value='10'".($degreerange == 10 ? ' selected' : '').">0-10</option>
                            <option value='20'".($degreerange == 20 ? ' selected' : '').">0-20</option>
                            <option value='100'".($degreerange == 100 ? ' selected' : '').">0-100</option>
                        </select>
                        <span class='help-block'>$degreerange_error</span>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-xs-12'>".form_buttons(array(
                        array(
                                'text' => $langSave,
                                'name' => 'newGradebook',
                                'value'=> $langInsert
                            ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                            )
                        ))."
                    </div>
                </div>
            ". generate_csrf_token_form_field() ."
            </form>
        </div>";
}


/**
 * @brief create copy of an existing gradebook. (with same activities and no users).
 * @global type $course_id
 * @param type $gradebook_id
 */
function clone_gradebook($gradebook_id) {

    global $course_id, $langCopyDuplicate;

    $newTitle = get_gradebook_title($gradebook_id).$langCopyDuplicate;
    $newRange = get_gradebook_range($gradebook_id);
    $new_gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d,
                                                      students_semester = 1, `range` = ?d,
                                                      active = 1, title = ?s", $course_id, $newRange, $newTitle)->lastInsertID;
    Database::get()->query("INSERT INTO gradebook_activities (gradebook_id, title, activity_type, date, description, weight, module_auto_id, module_auto_type, auto, visible)
                                SELECT $new_gradebook_id, title, activity_type, " . DBHelper::timeAfter() . ", description, weight, module_auto_id, module_auto_type, auto, 1
                                 FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id);
}

/**
 * @brief delete gradebook
 * @global type $langGradebookdeleted
 * @global type $course_id
 * @param type $gradebook_id
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
        Session::Messages("$langGradebookDeleted", "alert-success");
    }
}


/**
 * @brief delete gradebook activity
 * @global type $langGradebookDel
 * @global type $langGradebookDelFailure
 * @param type $gradebook_id
 * @param type $activity_id
 */
function delete_gradebook_activity($gradebook_id, $activity_id) {

    global $langGradebookDel, $langGradebookDelFailure;

    $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $activity_id, $gradebook_id)->affectedRows;
    Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $activity_id);
    if ($delAct) {
        Session::Messages("$langGradebookDel", "alert-success");
    } else {
        Session::Messages("$langGradebookDelFailure", "alert-danger");
    }
}

/**
 * @brief delete user from gradebook
 * @global type $langGradebookEdit
 * @param type $gradebook_id
 * @param type $userid
 */
function delete_gradebook_user($gradebook_id, $userid) {

    global $langGradebookEdit;

    Database::get()->query("DELETE FROM gradebook_book WHERE uid = ?d AND gradebook_activity_id IN
                                (SELECT id FROM gradebook_activities WHERE gradebook_id = ?d)", $userid, $gradebook_id);
    Database::get()->query("DELETE FROM gradebook_users WHERE uid = ?d AND gradebook_id = ?d", $userid, $gradebook_id);
    Session::Messages($langGradebookEdit,"alert-success");
}

/**
 * @brief insert/modify gradebook settings
 * @global type $tool_content
 * @global type $course_code
 * @global type $langTitle
 * @global type $langSave
 * @global type $langGradebookRange
 * @global type $langGradebookUpdate
 * @global type $langGradebookInfoForUsers
 * @param type $gradebook_id
 */
function gradebook_settings($gradebook_id) {

    global $tool_content, $course_code,
           $langTitle, $langSave,
           $langSave, $langGradebookRange, $langGradebookUpdate,
           $gradebook_title;

    $gradebook_range = get_gradebook_range($gradebook_id);

    // update gradebook title
    $tool_content .= "<div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "'>
                    <div class='form-group'>
                        <label class='col-xs-12'>$langTitle</label>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$gradebook_title'>
                        </div>
                    </div>
                    <div class='form-group'><label class='col-xs-12'>$langGradebookRange</label>
                            <div class='col-xs-12'>
                            <select name='degreerange' class='form-control'><option value=10";
                            if (isset($gradebook_range) and $gradebook_range == 10) {
                                $tool_content .= " selected ";
                            }
                            $tool_content .= ">0-10</option><option value=20";
                            if (isset($gradebook_range) and $gradebook_range == 20) {
                                $tool_content .= " selected ";
                            }
                            $tool_content .= ">0-20</option><option value=5";
                            if (isset($gradebook_range) and $gradebook_range == 5) {
                                $tool_content .= " selected ";
                            }
                            $tool_content .= ">0-5</option><option value=100";
                            if (isset($gradebook_range) and $gradebook_range == 100) {
                                $tool_content .= " selected ";
                            }
                            $tool_content .= ">0-100</option></select>";
                            $tool_content .= "</div>
                        </div>
                        <div class='form-group'>
                            <div class='col-xs-12'>".form_buttons(array(
                                array(
                                    'text' => $langSave,
                                    'name' => 'submitGradebookSettings',
                                    'value'=> $langGradebookUpdate
                                ),
                                array(
                                    'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . ""
                                )
                            ))."</div>
                        </div>
                    </fieldset>
                ". generate_csrf_token_form_field() ."
                </form>
            </div>
        </div>
    </div>";
}

/**
 * @brief modify user gradebook settings
 * @global type $tool_content
 * @global type $course_code
 * @global type $langGroups
 * @global type $langAttendanceUpdate
 * @global type $langGradebookInfoForUsers
 * @global type $langRegistrationDate
 * @global type $langFrom2
 * @global type $langTill
 * @global type $langRefreshList
 * @global type $langUserDuration
 * @global type $langAll
 * @global type $langSpecificUsers
 * @global type $langStudents
 * @global type $langMove
 * @global type $langParticipate
 * @param type $gradebook_id
 */
function user_gradebook_settings($gradebook_id) {

    global $tool_content, $course_code, $langGroups,
           $langAttendanceUpdate, $langGradebookInfoForUsers,
           $langRegistrationDate, $langFrom2, $langTill, $langRefreshList,
           $langUserDuration, $langAll, $langSpecificUsers,
           $langStudents, $langMove, $langParticipate;

    // default values
    $UsersStart = date('d-m-Y', strtotime('now -6 month'));
    $UsersEnd = date('d-m-Y', strtotime('now'));

    $tool_content .= "
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "&editUsers=1'>
                    <div class='form-group'>
                        <label class='col-xs-12'><span class='help-block'>$langGradebookInfoForUsers</span></label>
                    </div>
                    <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langUserDuration:</label>
                        <div class='col-sm-10'>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_all_users' name='specific_gradebook_users' value='0' checked>
                                <span id='button_all_users_text'>$langAll</span>
                              </label>
                            </div>
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
                        </div>
                    </div>
                    <div class='form-group' id='all_users'>
                        <div class='input-append date form-group' id='startdatepicker' data-date='$UsersStart' data-date-format='dd-mm-yyyy'>
                            <label for='UsersStart' class='col-sm-2 control-label'>$langRegistrationDate $langFrom2:</label>
                            <div class='col-xs-10 col-sm-9'>
                                <input class='form-control' name='UsersStart' id='UsersStart' type='text' value='$UsersStart'>
                            </div>
                            <div class='col-xs-2 col-sm-1'>
                                <span class='add-on'><i class='fa fa-calendar'></i></span>
                            </div>
                        </div>
                        <div class='input-append date form-group' id='enddatepicker' data-date='$UsersEnd' data-date-format='dd-mm-yyyy'>
                            <label for='UsersEnd' class='col-sm-2 control-label'>$langTill:</label>
                            <div class='col-xs-10 col-sm-9'>
                                <input class='form-control' name='UsersEnd' id='UsersEnd' type='text' value='$UsersEnd'>
                            </div>
                            <div class='col-xs-2 col-sm-1'>
                                <span class='add-on'><i class='fa fa-calendar'></i></span>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <div class='table-responsive'>
                                <table id='participants_tbl' class='table-default hide'>
                                    <tr class='title1'>
                                      <td id='users'>$langStudents</td>
                                      <td class='text-center'>$langMove</td>
                                      <td>$langParticipate</td>
                                    </tr>
                                    <tr>
                                      <td>
                                        <select class='form-control' id='users_box' size='10' multiple></select>
                                      </td>
                                      <td class='text-center'>
                                        <input type='button' onClick=\"move('users_box','participants_box')\" value='   &gt;&gt;   ' /><br />
                                        <input type='button' onClick=\"move('participants_box','users_box')\" value='   &lt;&lt;   ' />
                                      </td>
                                      <td width='40%'>
                                        <select class='form-control' id='participants_box' name='specific[]' size='10' multiple></select>
                                      </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-xs-10 col-sm-offset-2'>".form_buttons(array(
                        array(
                            'text' => $langRefreshList,
                            'name' => 'resetGradebookUsers',
                            'value'=> $langAttendanceUpdate,
                            'javascript' => "selectAll('participants_box',true)"
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;gradebookBook=1"
                        )
                    ))."</div>
                    </div>
                ". generate_csrf_token_form_field() ."
                </form>
            </div>
        </div>
    </div>";
}

/**
 * @brief display all users grade
 * @global type $course_id
 * @global type $course_code
 * @global type $langName
 * @global type $langSurname
 * @global type $langID
 * @global type $langAm
 * @global type $langRegistrationDateShort
 * @global type $langGradebookGrade
 * @global type $langGradebookBook
 * @global type $langGradebookDelete
 * @global type $langConfirmDelete
 * @global type $langNoRegStudent
 * @global type $langHere
 * @global type $langGradebookOutRange
 * @global type $langGradebookGradeAlert
 * @param type $gradebook_id
 */
function display_all_users_grades($gradebook_id) {

    global $course_id, $course_code, $tool_content, $langName, $langSurname,
           $langID, $langAm, $langRegistrationDateShort, $langGradebookGrade,
           $langGradebookBook, $langGradebookDelete, $langConfirmDelete,
           $langNoRegStudent, $langHere, $langGradebookOutRange, $langGradebookGradeAlert;

    $gradebook_range = get_gradebook_range($gradebook_id);
    $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID,
                                                            gradebook_users.uid as userID,
                                                            user.am as am, DATE(course_user.reg_date) as reg_date
                                                 FROM gradebook_users, user, course_user
                                                    WHERE gradebook_id = ?d
                                                    AND gradebook_users.uid = user.id
                                                    AND `user`.id = `course_user`.`user_id`
                                                    AND `course_user`.`course_id` = ?d", $gradebook_id, $course_id);
    if (count($resultUsers)> 0) {
        $tool_content .= "<table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr>
                  <th width='1'>$langID</th>
                  <th><div align='left' width='100'>$langName $langSurname</div></th>
                  <th>$langRegistrationDateShort</th>
                  <th>$langGradebookGrade</th>
                  <th class='text-center'><i class='cogs'></i></th>
                </tr>
            </thead>
            <tbody>";
        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $cnt++;
            $tool_content .= "
                <tr>
                <td>$cnt</td>
                <td>" . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                <td>" . nice_format($resultUser->reg_date) . "</td>
                <td>";
                if(weightleft($gradebook_id, 0) == 0) {
                    $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID);
                } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                    $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID) . " (<small>" . $langGradebookGradeAlert . "</small>)";
                }
                if (userGradeTotal($gradebook_id, $resultUser->userID) > $gradebook_range) {
                    $tool_content .= "<br><div class='smaller'>" . $langGradebookOutRange . "</div>";
                }
            $tool_content .="</td><td class='option-btn-cell'>".
                    action_button(array(
                        array('title' => $langGradebookBook,
                                'icon' => 'fa-plus',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;book=$resultUser->userID"),
                        array('title' => $langGradebookDelete,
                                'icon' => 'fa-times',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gb=" . getIndirectReference($gradebook_id) . "&amp;ruid=" . getIndirectReference($resultUser->userID) . "&amp;deleteuser=yes",
                                'class' => 'delete',
                                'confirm' => $langConfirmDelete)))
                        ."</td></tr>";
        }
        $tool_content .= "</tbody></table>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoRegStudent <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;editUsers=1'>$langHere</a>.</div>";
    }
}

/**
 * @brief display user grades (student view)
 * @global type $tool_content
 * @global type $uid
 * @global type $langGradebookTotalGradeNoInput
 * @global type $langGradebookTotalGrade
 * @global type $langTitle
 * @global type $langGradebookActivityDate2
 * @global type $langGradebookActivityDescription
 * @global type $langGradebookActivityWeight
 * @global type $langGradebookGrade
 * @global type $langGradebookAlertToChange
 * @param type $gradebook_id
 */
function student_view_gradebook($gradebook_id) {

    global $tool_content, $uid,
           $langGradebookTotalGradeNoInput, $langGradebookTotalGrade,
           $langTitle, $langGradebookActivityDate2, $langGradebookActivityDescription,
           $langGradebookActivityWeight, $langGradebookGrade, $langGradebookAlertToChange, $langBack, $course_code;

    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) AS count
                                            FROM gradebook_book, gradebook_activities
                                        WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                                            AND gradebook_activities.visible = 1
                                            AND uid = ?d
                                            AND gradebook_activities.gradebook_id = ?d", $uid, $gradebook_id)->count;
    $tool_content .= action_bar(array(
        array(  'title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'),
    ));
    if (!$checkForRecords) {
        $tool_content .="<div class='alert alert-warning'>$langGradebookTotalGradeNoInput</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities
                                WHERE gradebook_activities.visible = 1 AND gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
    $results = count($result);

    if ($results > 0) {
        if ($checkForRecords) {
            $range = Database::get()->querySingle("SELECT `range` FROM gradebook WHERE id = ?d", $gradebook_id)->range;
            $tool_content .= "<div class='alert alert-info'>$langGradebookTotalGrade: <strong>" . userGradeTotal($gradebook_id, $uid) . " / ". $range . "</strong></div>";
        }
        if(weightleft($gradebook_id, 0) != 0) {
            $tool_content .= "<div class='alert alert-warning'>$langGradebookAlertToChange</p>";
        }
        $tool_content .= "<table class='table-default' >";
        $tool_content .= "<tr><th>$langTitle</th>
                              <th>$langGradebookActivityDate2</th>
                              <th>$langGradebookActivityDescription</th>
                              <th>$langGradebookActivityWeight</th>
                              <th>$langGradebookGrade</th>
                          </tr>";
    }
    if ($result) {
        foreach ($result as $details) {
            $content = standard_text_escape($details->description);
            $tool_content .= "<tr><td><b>";
            if (!empty($details->title)) {
                $tool_content .= q($details->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'>" . nice_format($details->date, true, true) . "</div></td>"
                    . "<td>" . $content . "</td>"
                    . "<td>" . q($details->weight) . "%</td>";
            $tool_content .= "<td width='70' class='text-center'>";
            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT grade FROM gradebook_book
                                                            WHERE gradebook_activity_id = ?d
                                                                AND uid = ?d", $details->id, $uid);
            if ($sql) {
                $tool_content .= $sql->grade;
            } else {
                $tool_content .= "&mdash;";
            }
            $tool_content .= "</td>";
        } // end of while
    }
    $tool_content .= "</table>";
}

/**
 * @brief display gradebook activities
 * @global type $course_code
 * @global type $course_id
 * @global type $urlServer
 * @global type $tool_content
 * @global type $langGradebookGradeAlert
 * @global type $langGradebookNoActMessage1
 * @global type $langTitle
 * @global type $langView
 * @global type $langScore
 * @global type $langGradebookActList
 * @global type $langGradebookActivityDate2
 * @global type $langGradebookWeight
 * @global type $langGradebookNoTitle
 * @global type $langType
 * @global type $langExercise
 * @global type $langGradebookInsAut
 * @global type $langGradebookInsMan
 * @global type $langAssignment
 * @global type $langGradebookActivityAct
 * @global type $langAttendanceActivity
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langEditChange
 * @global type $langYes
 * @global type $langNo
 * @global type $langConfig
 * @global type $langUsers
 * @global type $langGradebookAddActivity
 * @global type $langInsertWorkCap
 * @global type $langInsertExerciseCap
 * @global type $langLearningPath
 * @global type $langAdd
 * @param type $gradebook_id
 */
function display_gradebook($gradebook_id) {

    global $course_code, $urlServer, $tool_content, $langGradebookGradeAlert, $langGradebookNoActMessage1,
           $langTitle, $langView, $langScore, $langGradebookActList, $langAdd,
           $langGradebookActivityDate2, $langGradebookWeight, $langGradebookNoTitle, $langType, $langExercise,
           $langGradebookInsAut, $langGradebookInsMan, $langAttendanceActivity, $langDelete, $langConfirmDelete,
           $langEditChange, $langYes, $langNo, $langPreview, $langAssignment, $langGradebookActivityAct, $langGradebookGradeAlert3,
           $langGradebookExams, $langGradebookLabs, $langGradebookOral, $langGradebookProgress, $langGradebookOtherType,
           $langConfig, $langUsers, $langGradebookAddActivity, $langInsertWorkCap, $langInsertExerciseCap, $langLearningPath,
           $langExport, $langcsvenc1, $langcsvenc2, $langBack, $langToA;

    $tool_content .= action_bar(
        array(
            array('title' => $langAdd,
                  'level' => 'primary-label',
                  'options' => array(
                      array('title' => $langGradebookAddActivity,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addActivity=1",
                            'icon' => 'fa fa-plus fa space-after-icon',
                            'class' => ''),
                      array('title' => "$langInsertWorkCap",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addActivityAs=1",
                            'icon' => 'fa fa-flask space-after-icon',
                            'class' => ''),
                      array('title' => "$langInsertExerciseCap",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addActivityEx=1",
                            'icon' => 'fa fa-edit space-after-icon',
                            'class' => ''),
                      array('title' => "$langLearningPath",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addActivityLp=1",
                            'icon' => 'fa fa-ellipsis-h space-after-icon',
                            'class' => ''),
                      ),
                  'icon' => 'fa-plus'),
            array('title' => $langConfig,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;editSettings=1",
                  'icon' => 'fa-cog'),
            array('title' => $langUsers,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;gradebookBook=1",
                  'icon' => 'fa-users'),
            array('title' => "$langExport $langToA $langcsvenc1",
                  'url' => "dumpgradebook.php?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;enc=1253",
                  'icon' => 'fa-file-excel-o'),
            array('title' => "$langExport $langToA $langcsvenc2",
                  'url' => "dumpgradebook.php?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "",
                  'icon' => 'fa-file-excel-o'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'),
            ),
            true
        );

    $weightMessage = "";
    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d ORDER BY `DATE` DESC", $gradebook_id);
    $activityNumber = count($result);

    if (!$result or $activityNumber == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langGradebookNoActMessage1</a></div>";
    } else {
        foreach ($result as $details) {
            if ($details->weight == 0 or (empty($details->weight))) { // check if there are activities with 0% weight
                $weightMessage = "<div class='alert alert-warning'>$langGradebookGradeAlert3</div>";
            }
        }
        //check if there is spare weight
        if(weightleft($gradebook_id, 0)) {
            $weightMessage = "<div class='alert alert-warning'>$langGradebookGradeAlert (" . weightleft($gradebook_id, 0) . "%)</div>";
        }
        $tool_content .= $weightMessage;
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                          <table class='table-default'>
                          <tr class='list-header'><th colspan='7' class='text-center'>$langGradebookActList</th></tr>
                          <tr class='list-header'><th>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langType</th><th>$langGradebookWeight</th>
                          <th class='text-center'>$langView</th>
                          <th class='text-center'>$langScore</th>
                          <th class='text-center'><i class='fa fa-cogs'></i></th>";
                          $tool_content .= "</tr>";
        foreach ($result as $details) {
            $content = ellipsize_html($details->description, 50);
            $tool_content .= "<tr><td><b>";
            if (empty($details->title)) {
                $tool_content .= "$langGradebookNoTitle<br>";
            } else {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($details->id) . "'>" . q($details->title) . "</a>";
            }
            $tool_content .= "<small class='help-block'>";
            switch ($details->activity_type) {
                 case 1: $tool_content .= "($langGradebookOral)"; break;
                 case 2: $tool_content .= "($langGradebookLabs)"; break;
                 case 3: $tool_content .= "($langGradebookProgress)"; break;
                 case 4: $tool_content .= "($langGradebookExams)"; break;
                 case 5: $tool_content .= "($langGradebookOtherType)"; break;
                 default : $tool_content .= "";
             }
            $tool_content .= "</small";
            $tool_content .= "</b>";
            $tool_content .= "</td><td><div class='smaller'>" . nice_format($details->date, true, true) . "</div></td>";

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
                    $tool_content .= "<small class='help-block'>($langGradebookInsAut)</small>";
                } else {
                    $tool_content .= "<small class='help-block'>($langGradebookInsMan)</small>";
                }
                $tool_content .= "</td>";
            } else {
                $tool_content .= "<td class='smaller'>$langAttendanceActivity</td>";
            }

            $tool_content .= "<td class='text-center'>" . $details->weight . "%</td>";
            $tool_content .= "<td width='' class='text-center'>";
            if ($details->visible) {
                $tool_content .= $langYes;
            } else {
                $tool_content .= $langNo;
            }
            $tool_content .= "</td>";
            $tool_content .= "<td width='120' class='text-center'>" . userGradebookTotalActivityStats($details->id, $gradebook_id) . "</td>";
            if ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
                $preview_link = "${urlServer}modules/exercise/results.php?course=$course_code&amp;exerciseId=$details->module_auto_id";
            } elseif ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
                $preview_link = "${urlServer}modules/work/index.php?course=$course_code&amp;id=$details->module_auto_id";
            } elseif ($details->module_auto_id and $details->module_auto_type == GRADEBOOK_ACTIVITY_LP) {
                $preview_link = "${urlServer}modules/learnPath/detailsAll.php?course=$course_code";
            } else {
                $preview_link = '';
            }
            $tool_content .= "<td class='option-btn-cell text-center'>".
                action_button(array(
                            array('title' => $langEditChange,
                                'icon' => 'fa-edit',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;modify=" . getIndirectReference($details->id)),
                            array('title' => $langPreview,
                                'icon' => 'fa-plus',
                                'url' => $preview_link,
                                'show' => (!empty($preview_link))),
                            array('title' => $langDelete,
                                'icon' => 'fa-times',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;delete=" . getIndirectReference($details->id),
                                'confirm' => $langConfirmDelete,
                                'class' => 'delete')
                    )).
                "</td>";
            
        } // end of while
        $tool_content .= "</table></div></div></div>";
    }
}


/**
 * @brief admin available gradebook
 * @global type $course_id
 * @global type $tool_content
 * @global type $course_code
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langDeactivate
 * @global type $langActivate
 * @global type $langCreateDuplicate
 * @global type $langNoGradeBooks
 */
function display_gradebooks() {

    global $course_id, $tool_content, $course_code, $langEditChange,
           $langDelete, $langConfirmDelete, $langDeactivate, $langCreateDuplicate,
           $langActivate, $langAvailableGradebooks, $langNoGradeBooks, $is_editor;

    if ($is_editor) {
        $result = Database::get()->queryArray("SELECT * FROM gradebook WHERE course_id = ?d", $course_id);
    } else {
        $result = Database::get()->queryArray("SELECT * FROM gradebook WHERE active = 1 AND course_id = ?d", $course_id);
    }
    if (count($result) == 0) { // no gradebooks
        $tool_content .= "<div class='alert alert-info'>$langNoGradeBooks</div>";
    } else {
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langAvailableGradebooks</th>";
        if( $is_editor) {
            $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr>";
        foreach ($result as $g) {
            $row_class = !$g->active ? "class='not_visible'" : "";
            $tool_content .= "<tr $row_class><td><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "'>" . q($g->title) . "</a></td>";
            if( $is_editor) {
                $tool_content .= "<td class='option-btn-cell'>";
                $tool_content .= action_button(array(
                                    array('title' => $g->active ? $langDeactivate : $langActivate,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "&amp;vis=" .
                                                  ($g->active ? '0' : '1'),
                                          'icon' => $g->active ? 'fa-toggle-off' : 'fa-toggle-on'),
                                    array('title' => $langCreateDuplicate,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($g->id) . "&amp;dup=1",
                                          'icon' => 'fa-copy'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_gb=" . getIndirectReference($g->id),
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete))
                                        );
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div></div>";
    }
}


/**
 * @brief display available exercises for adding them to gradebook
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langGradebookActivityDate2
 * @global type $langDescr
 * @global type $langAdd
 * @global type $langAttendanceNoActMessageExe4
 * @global type $langTitle
 * @param type $gradebook_id
 */
function display_available_exercises($gradebook_id) {

    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                AND exercise.active = 1 AND exercise.id
                                NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2 AND gradebook_id = ?d)", $course_id, $gradebook_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langDescr</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr>";

        foreach ($checkForExer as $newExerToGradebook) {
            $content = ellipsize_html($newExerToGradebook->description, 50);
            $tool_content .= "<tr><td><b>";
            if (!empty($newExerToGradebook->title)) {
                $tool_content .= q($newExerToGradebook->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>" . nice_format($newExerToGradebook->start_date, true, true) . " </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newExerToGradebook->id) . "&amp;type=2");
        }
        $tool_content .= "</td></tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
    }
}

/**
 * @brief display available assignments for adding them to gradebook
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $dateFormatLong
 * @global type $langWorks
 * @global type $m
 * @global type $langDescription
 * @global type $langAttendanceNoActMessageAss4
 * @global type $langAdd
 * @global type $langTitle
 * @global type $langHour
 * @param type $gradebook_id
 */
function display_available_assignments($gradebook_id) {

    global $course_id, $course_code, $tool_content, $dateFormatLong,
           $langWorks, $m, $langDescription, $langAttendanceNoActMessageAss4,
           $langAdd, $langTitle, $langHour;

    $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1 AND gradebook_id = ?d)", $course_id, $gradebook_id);

    $checkForAssNumber = count($checkForAss);

    if ($checkForAssNumber > 0) {
        $tool_content .= "
            <div class='row'><div class='col-sm-12'><div class='table-responsive'>
                          <table class='table-default'";
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>".q($m['deadline'])."</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr>";
        foreach ($checkForAss as $newAssToGradebook) {
            $content = ellipsize_html($newAssToGradebook->description, 50);
            if($newAssToGradebook->assign_to_specific){
                $content .= "($langAssignmentignSpecific)<br>";
                $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname , user.givenname FROM `assignment_to_specific`, user WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                foreach ($checkForAssSpec as $checkForAssSpecR) {
                    $content .= q($checkForAssSpecR->surname). " " . q($checkForAssSpecR->givenname) . "<br>";
                }
            }
            if ((int) $newAssToGradebook->deadline){
                $d = strtotime($newAssToGradebook->deadline);
                $date_str = ucfirst(claro_format_locale_date($dateFormatLong, $d));
                $hour_str = "($langHour: " . ucfirst(date('H:i', $d)).")";
            } else {
                $date_str = $m['no_deadline'];
                $hour_str = "";
            }
            $tool_content .= "<tr><td><b>";

            if (!empty($newAssToGradebook->title)) {
                $tool_content .= q($newAssToGradebook->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>".q($date_str)."</span> ".q($hour_str)." </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newAssToGradebook->id) . "&amp;type=1");
        } // end of while
        $tool_content .= "</tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageAss4</div>";
    }
}

/**
 * @brief display available learning paths
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langLearningPath
 * @global type $langAdd
 * @global type $langAttendanceNoActMessageLp4
 * @global type $langTitle
 * @global type $langDescription
 * @global type $langActions
 * @param type $gradebook_id
 */
function display_available_lps($gradebook_id) {

    global $course_id, $course_code, $tool_content,
           $langLearningPath, $langAdd, $langAttendanceNoActMessageLp4, $langTitle, $langDescription, $langActions;

    $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE course_id = ?d ORDER BY name
                        AND learnPath_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 3 AND gradebook_id = ?d)", $course_id, $gradebook_id);

    $checkForLpNumber = count($checkForLp);
    if ($checkForLpNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-center'>$langActions</th>";
        $tool_content .= "</tr>";
        foreach ($checkForLp as $newExerToGradebook) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>". q($newExerToGradebook->name) ."</td>";
            $tool_content .= "<td>" .q($newExerToGradebook->comment). "</td>";
            $tool_content .= "<td class='text-center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;addCourseActivity=" . getIndirectReference($newExerToGradebook->learnPath_id) . "&amp;type=3")."</td>";
            $tool_content .= "</tr>";
        } // end of while
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageLp4</div>";
    }
}

/**
 * @brief display users of gradebook
 * @global type $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $langID
 * @global type $langName
 * @global type $langSurname
 * @global type $langAm
 * @global type $langRegistrationDateShort
 * @global type $langGradebookGrade
 * @global type $langGradebookGradeAlert
 * @global type $langAttendanceBooking
 * @global type $langGradebookBooking
 * @global type $langGradebookOutRange
 * @global type $langGradebookUpdate
 * @param type $gradebook_id
 * @param type $actID
 */
function register_user_grades($gradebook_id, $actID) {

    global $tool_content, $course_id, $course_code,
            $langID, $langName, $langSurname, $langAm, $langRegistrationDateShort,
            $langGradebookGrade, $langGradebookUpdate,
            $langAttendanceBooking, $langGradebookBooking, $langGradebookOutRange;

    //display form and list
    $gradebook_range = get_gradebook_range($gradebook_id);
    $result = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d", $actID);
    $act_type = $result->activity_type; // type of activity
    $tool_content .= "<div class='alert alert-info'>" . q($result->title) . "</div>";
    //display users
    $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid as userID, user.surname as surname,
                                                    user.givenname as name, user.am as am, DATE(course_user.reg_date) AS reg_date
                                                FROM gradebook_users, user, course_user
                                                WHERE gradebook_id = ?d AND gradebook_users.uid = user.id
                                                    AND `user`.id = `course_user`.`user_id`
                                                    AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);

    if ($resultUsers) {
        $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($actID) . "'>
        <div class='form-group'>
        <div class='col-xs-12'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr class='list-header'>
                  <th width='1'>$langID</th>
                  <th width='350'>$langName $langSurname</th>
                  <th class='text-center' width='80'>$langRegistrationDateShort</th>
                  <th class='text-center'>$langGradebookGrade</th>
                  <th class='text-center'>$langAttendanceBooking</th>
                </tr>
            </thead>
            <tbody>";

        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $cnt++;
            $am_message = '';
            if (!empty($resultUser->am)) {
                $am_message = "($langAm: $resultUser->am)";
            }
            $q = Database::get()->querySingle("SELECT grade FROM gradebook_book
                                                            WHERE gradebook_activity_id = ?d
                                                        AND uid = ?d", $actID, $resultUser->userID);
            if ($q) {
                $user_grade = $q->grade;
            } else {
                $user_grade = 0;
            }
            $tool_content .= "<tr><td>$cnt</td>
                <td>" . display_user($resultUser->userID). " $am_message</td>
                <td>" . nice_format($resultUser->reg_date) . "</td>";
            $tool_content .= "<td class='text-center'>$user_grade";
            if ($user_grade > $gradebook_range) {
                $tool_content .= "<span class='help-block'><small>$langGradebookOutRange</small></span>";
            }
            $tool_content .= "</td>";
            $tool_content .= "<td class='text-center'><input type='text' name='usersgrade[".getIndirectReference($resultUser->userID)."]'";
            if(isset($q->grade)) {
                $tool_content .= " value = '".q($q->grade)."'";
            } else{
                $tool_content .= " value = ''";
            }
            $tool_content .= "><input type='hidden' value='" . getIndirectReference($actID) . "' name='actID'></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</tbody></table></div></div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<div class='col-xs-12'>" .
                        form_buttons(array(
                            array(
                                'text' => $langGradebookBooking,
                                'name' => 'bookUsersToAct',
                                'value'=> $langGradebookBooking
                                ),
                            array(
                                    'text' => $langGradebookUpdate,
                                    'name' => 'updateUsersToAct',
                                    'value'=> $langGradebookUpdate,
                                    'show' => $act_type == 0
                                )));
        $tool_content .= "</div></div>";
        $tool_content .= generate_csrf_token_form_field()."</form></div>";
    }
}

/**
 * @brief add available activity in gradebook
 * @global type $course_id
 * @param type $gradebook_id
 * @param type $id
 * @param type $type
 */
function add_gradebook_activity($gradebook_id, $id, $type) {

    global $course_id, $langLearningPath;

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
            $module_auto_type = 1;
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
                foreach ($sql as $u) {
                    $grd = Database::get()->querySingle("SELECT grade FROM assignment_submit WHERE assignment_id = ?d AND uid = $u->uid", $id);
                    if ($grd) {
                        update_gradebook_book($u->uid, $id, $grd->grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
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
            $module_auto_type = 2; //2 for exercises
            $module_auto = 1;
            $module_weight = weightleft($gradebook_id, '');
            $actTitle = $checkForExe->title;
            $actDate = $checkForExe->end_date;
            $actDesc = $checkForExe->description;

            Database::get()->query("INSERT INTO gradebook_activities
                                        SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        weight = ?d, module_auto_id = ?d, auto = ?d, module_auto_type = ?d, visible = 1",
                                    $gradebook_id, $actTitle, $actDate, $actDesc, $module_weight, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            foreach ($sql as $u) {
                update_gradebook_book($u->uid, $id, 0, GRADEBOOK_ACTIVITY_EXERCISE);
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
            $module_auto_type = 3; //3 for lp
            $module_auto = 1;
            $module_weight = weightleft($gradebook_id, '');
            $actTitle = $checkForLp->name;
            $actDate = date("Y-m-d");
            $actDesc = $langLearningPath . ": " . $checkForLp->name;
            Database::get()->query("INSERT INTO gradebook_activities
                            SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                weight = ?d, module_auto_id = ?d, auto = ?d, module_auto_type = ?d, visible = 1",
                            $gradebook_id, $actTitle, $actDate, $actDesc, $module_weight, $module_auto_id, $module_auto, $module_auto_type);
        }
    }
}

/**
 * @brief dislay form for adding other activity in gradebook
 * @global type $tool_content
 * @global type $course_code
 * @global type $visible
 * @global type $langTitle
 * @global type $langGradebookActivityDate2
 * @global type $langGradebookActivityWeight
 * @global type $langGradeVisible
 * @global type $langComments
 * @global type $langGradebookInsAut
 * @global type $langAdd
 * @global type $langAdd
 * @global type $langType
 * @global type $langGradebookExams
 * @global type $langGradebookLabs
 * @global type $langGradebookOral
 * @global type $langGradebookProgress
 * @global type $langGradebookOtherType
 * @param type $gradebook_id
 */
function add_gradebook_other_activity($gradebook_id) {

    global $tool_content, $course_code, $visible,
           $langTitle, $langGradebookActivityDate2, $langGradebookActivityWeight,
           $langGradeVisible, $langComments, $langGradebookInsAut, $langAdd,
           $langAdd, $langType, $langGradebookExams, $langGradebookLabs,
           $langGradebookOral, $langGradebookProgress, $langGradebookOtherType,
           $langGradebookRemainingGrade, $langSave;

    $tool_content .= "
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "'>
                    <fieldset>";
                        if (isset($_GET['modify'])) { // modify an existing gradebook activity
                            $id  = filter_var(getDirectReference($_GET['modify']), FILTER_VALIDATE_INT);
                            //All activity data (check if it's in this gradebook)
                            $modifyActivity = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $id, $gradebook_id);
                            if ($modifyActivity) {
                                $titleToModify = $modifyActivity->title;
                                $contentToModify = $modifyActivity->description;
                                $date = $modifyActivity->date;
                                $module_auto_id = $modifyActivity->module_auto_id;
                                $auto = $modifyActivity->auto;
                                $weight = $modifyActivity->weight;
                                $activity_type = $modifyActivity->activity_type;
                                $visible = $modifyActivity->visible;
                            } else {
                                $activity_type = '';
                            }
                            $gradebookActivityToModify = $id;
                        } else { //new activity
                            $gradebookActivityToModify = "";
                            $activity_type = "";
                            $date = date("Y-n-j", time());
                            $visible = 1;
                        }

                        if (!isset($contentToModify)) $contentToModify = "";
                        @$tool_content .= "
                        <div class='form-group'>
                            <label for='activity_type' class='col-sm-2 control-label'>$langType:</label>
                            <div class='col-sm-10'>
                                <select name='activity_type' class='form-control'>
                                    <option value=''  " . typeSelected($activity_type, '') . " >-</option>
                                    <option value='4' " . typeSelected($activity_type, 4) . " >" . $langGradebookExams . "</option>
                                    <option value='2' " . typeSelected($activity_type, 2) . " >" . $langGradebookLabs . "</option>
                                    <option value='1' " . typeSelected($activity_type, 1) . " >" . $langGradebookOral . "</option>
                                    <option value='3' " . typeSelected($activity_type, 3) . " >" . $langGradebookProgress . "</option>
                                    <option value='5' " . typeSelected($activity_type, 5) . " >" . $langGradebookOtherType . "</option>
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='actTitle' class='col-sm-2 control-label'>$langTitle:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='actTitle' value='".q($titleToModify)."'/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='date' class='col-sm-2 control-label'>$langGradebookActivityDate2:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='date' value='" . datetime_remove_seconds($date) . "'/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='weight' class='col-sm-2 control-label'>$langGradebookActivityWeight:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='weight' value='$weight' size='5'><span class='help-block'>($langGradebookRemainingGrade: " . weightleft($gradebook_id, '') . "%)</span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='visible' class='col-sm-2 control-label'>$langGradeVisible:</label>
                            <div class='col-sm-10'>
                                <input type='checkbox' id='visible' name='visible' value='1'";
                                if ($visible == 1) {
                                    $tool_content .= " checked";
                                }
                            $tool_content .= " /></div>
                        </div>
                        <div class='form-group'>
                            <label for='actDesc' class='col-sm-2 control-label'>$langComments:</label>
                            <div class='col-sm-10'>
                                " . rich_text_editor('actDesc', 4, 20, $contentToModify) . "
                            </div>
                        </div>";
                        if (isset($module_auto_id) && $module_auto_id != 0) { //accept the auto booking mechanism
                            $tool_content .= "<div class='form-group'>
                                <label for='weight' class='col-sm-2 control-label'>$langGradebookInsAut:</label>
                                    <div class='col-sm-10'><input type='checkbox' value='1' name='auto' ";
                            if ($auto) {
                                $tool_content .= " checked";
                            }
                            $tool_content .= "/></div></div>";
                        }
                        $tool_content .= "<div class='form-group'>
                                <div class='col-sm-10 col-sm-offset-2'>".form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'name' => 'submitGradebookActivity',
                                        'value'=> $langAdd
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                    )
                                ))."</div></div>";
                        if (isset($_GET['modify'])) {
                            $tool_content .= "<input type='hidden' name='id' value='" . getIndirectReference($gradebookActivityToModify) . "'>";
                        } else {
                            $tool_content .= " <input type='hidden' name='id' value='' >";
                        }
                    $tool_content .= "</fieldset>
                ". generate_csrf_token_form_field() ."
                </form>
            </div>
        </div>
    </div>";
}


/**
 * @brief insert grades for activity
 * @global string $tool_content
 * @global type $langGradebookEdit
 * @param type $gradebook_id
 * @param type $actID
 */
function insert_grades($gradebook_id, $actID) {

    global $tool_content, $langGradebookEdit;

    foreach ($_POST['usersgrade'] as $userID => $userInp) {
        if ($userInp == '') {
            $userInp = 0;
        }
        // //check if there is record for the user for this activity
        $checkForBook = Database::get()->querySingle("SELECT COUNT(id) AS count, id FROM gradebook_book
                                    WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, getDirectReference($userID));
        if ($checkForBook->count) { // update
            Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE id = ?d", $userInp, $checkForBook->id);
        } else { // insert
            Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?d, comments = ?s", getDirectReference($userID), $actID, $userInp, '');
        }
    }
    $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
    $tool_content .= $message . "<br/>";
}

/**
 * @brief update grades from modules for given activity
 * @param type $gradebook_id
 * @param type $actID
 */
function update_grades($gradebook_id, $actID) {

    $sql = Database::get()->querySingle("SELECT module_auto_type, module_auto_id
                            FROM gradebook_activities WHERE id = ?d", $actID);
    if ($sql) {
        $activity_type = $sql->module_auto_type;
        $id = $sql->module_auto_id;
    }
    //get all the active users
    $q = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
    if ($q) {
        foreach ($q as $activeUsers) {
            update_gradebook_book($activeUsers->uid, $id, 0, $activity_type);
        }
    }
}


/**
 * @brief update gradebook about user grade
 * @param type $uid
 * @param type $id
 * @param type $grade
 * @param type $activity
 */
function update_gradebook_book($uid, $id, $grade, $activity)
{
    global $course_id;

    $act_id = Database::get()->queryArray("SELECT id, gradebook_id FROM gradebook_activities WHERE module_auto_type = ?d
                            AND module_auto_id = ?d
                            AND auto = 1", $activity, $id);

    if ($act_id) {
        foreach ($act_id as $q) {
            $u = Database::get()->querySingle("SELECT id FROM gradebook_users WHERE uid = ?d
                                    AND gradebook_id = ?d", $uid, $q->gradebook_id);
            if($u) {
                if ($activity == GRADEBOOK_ACTIVITY_EXERCISE) { // exercises
                    $sql = Database::get()->querySingle("SELECT MAX(total_score) AS total_score, total_weighting FROM exercise_user_record
                                                            WHERE uid = ?d AND eid = ?d", $uid, $id);
                    $score = $sql->total_score;
                    $scoreMax = $sql->total_weighting;
                } elseif ($activity == GRADEBOOK_ACTIVITY_ASSIGNMENT) { // assignments
                    $sql = Database::get()->querySingle("SELECT grade AS total_score FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $uid, $id);
                    if ($sql) {
                        $score = $sql->total_score;
                        $sql2 = Database::get()->querySingle("SELECT max_grade FROM assignment WHERE id = ?d", $id);
                        $scoreMax = $sql2->max_grade;
                    }
                }
                if ($sql) {
                    $range = Database::get()->querySingle("SELECT `range` FROM gradebook WHERE id = $q->gradebook_id AND course_id = ?d", $course_id)->range;
                     if($scoreMax) {
                        $grade = round(($range * $score) / $scoreMax, 2);
                     } else {
                        $grade = $score;
                     }
                }

                if ($grade == '') {
                    $grade = 0;
                }

                $q2 = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = $q->id AND uid = ?d", $uid);
                if ($q2) { // update grade if exists
                    Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE gradebook_activity_id = $q->id AND uid = ?d", $grade, $uid);
                } else { // insert grade
                    Database::get()->query("INSERT INTO gradebook_book SET gradebook_activity_id = $q->id, uid = ?d, grade = ?d, comments = ''", $uid, $grade);
                }
            }
        }
    }
    return;
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

    if ($exeType == 1) { //asignments: valid submission!
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
    } else if($exeType == 2){ //exercises (if there are more than one attemps we take the last)
       $autoAttend = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record WHERE uid = ?d AND eid = ?d ORDER BY `record_end_date` DESC LIMIT 1", $userID, $exeID);
       if ($autoAttend) {
           $score = $autoAttend->total_score;
           $scoreMax = $autoAttend->total_weighting;
           if($score >= 0) {
                if($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == 3){ //lps (exes and scorms)
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
           if($score >= 0){ //to avoid the -1 for no score
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
 * @return string
 */
function userGradeTotal ($gradebook_id, $userID, $student_view = 'false') {

    if ($student_view) {
        $visible = 1;
    }

    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) AS count FROM gradebook_book, gradebook_activities
                                                    WHERE gradebook_book.uid = ?d
                                                        AND gradebook_book.gradebook_activity_id = gradebook_activities.id
                                                        AND gradebook_activities.gradebook_id = ?d
                                                        AND gradebook_activities.visible = ?d", $userID, $gradebook_id, $visible)->count;

    if ($userGradeTotal) {
        return round($userGradeTotal/100, 2);
    } else {
        return false;
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
function userGradebookTotalActivityStats ($activityID, $gradebook_id) {

    global $langUsers, $langMeanValue, $langMinValue, $langMaxValue;

    $users = Database::get()->querySingle("SELECT SUM(grade) as count, COUNT(gradebook_users.uid) AS users
                                        FROM gradebook_book, gradebook_users
                                        WHERE gradebook_users.uid=gradebook_book.uid
                                    AND gradebook_activity_id = ?d
                                    AND gradebook_users.gradebook_id = ?d ", $activityID, $gradebook_id);

    $sumGrade = $users->count;
    //this is different than global participants number (it is limited to those that have taken degree)
    $participantsNumber = $users->users;

    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade ASC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMin = $q->grade;
    }
    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade DESC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMax = $q->grade;
    }

//check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round($sumGrade/$participantsNumber, 2);
        return "<i>$langUsers:</i> $participantsNumber<br>$langMinValue: $userGradebookTotalActivityMin<br> $langMaxValue: $userGradebookTotalActivityMax<br> <i>$langMeanValue:</i> $mean";
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
