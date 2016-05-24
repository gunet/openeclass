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
 * @brief admin available attendances
 * @global type $course_id
 * @global type $tool_content
 * @global type $course_code
 * @global type $langEditChange
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langDeactivate
 * @global type $langCreateDuplicate
 * @global type $langActivate
 * @global type $langAvailableAttendances
 * @global type $langNoAttendances
 * @global type $is_editor
 */
function display_certificates() {

    global $course_id, $tool_content, $course_code, $langEditChange,
           $langDelete, $langConfirmDelete, $langDeactivate, $langCreateDuplicate,
           $langActivate, $langAvailableAttendances, $langNoAttendances, $is_editor,
           $langViewHide, $langViewShow, $langEditChange, $langStart, $langEnd, $uid;

    if ($is_editor) {
        $result = Database::get()->queryArray("SELECT * FROM certificate WHERE course = ?d", $course_id);
    } else {
        $result = Database::get()->queryArray("SELECT attendance.* "
                . "FROM attendance, attendance_users "
                . "WHERE attendance.active = 1 "
                . "AND attendance.course_id = ?d "
                . "AND attendance.id = attendance_users.attendance_id AND attendance_users.uid = ?d", $course_id, $uid);
    }
    if (count($result) == 0) { // no attendances
        $tool_content .= "<div class='alert alert-info'>$langNoAttendances</div>";
    } else {
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>
                            <th>Διαθέσιμα πιστοποιητικά</th>
                            <th>$langStart</th>
                            <th>$langEnd</th>";
        if( $is_editor) {
            $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr>";
        foreach ($result as $a) {
            $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $a->created)->format('d-m-Y H:i');
            $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $a->expires)->format('d-m-Y H:i');
            $row_class = !$a->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$a->id'>".q($a->title)."</a>
                        </td>
                        <td>$start_date</td>
                        <td>$end_date</td>";
            if( $is_editor) {
                $tool_content .= "<td class='option-btn-cell'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$a->id&amp;editSettings=1",
                                          'icon' => 'fa-cogs'),
                                    // array('title' => $a->active ? $langViewHide : $langViewShow,
                                    //       'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id&amp;vis=" .
                                    //               ($a->active ? '0' : '1'),
                                    //       'icon' => $a->active ? 'fa-eye-slash' : 'fa-eye'),
                                    // array('title' => $langCreateDuplicate,
                                    //       'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id&amp;dup=1",
                                    //       'icon' => 'fa-copy'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_at=$a->id",
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
 * @brief display attendance users
 * @global type $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $actID
 * @global type $langName
 * @global type $langSurname
 * @global type $langRegistrationDateShort
 * @global type $langAttendanceAbsences
 * @global type $langAm
 * @global type $langAttendanceEdit
 * @global type $langAttendanceBooking
 * @global type $langID
 * @param type $attendance_id
 */
function register_user_presences($attendance_id, $actID) {

    global $tool_content, $course_id, $course_code, $langAttendanceAutoBook,
           $langName, $langSurname, $langRegistrationDateShort, $langAttendanceAbsences,
           $langAm, $langAttendanceBooking, $langID, $langAttendanceEdit, $langCancel;
    $result = Database::get()->querySingle("SELECT * FROM attendance_activities WHERE id = ?d", $actID);
    $act_type = $result->auto; // type of activity
    $tool_content .= "<div class='alert alert-info'>" . q($result->title) . "</div>";
    //record booking
    if(isset($_POST['bookUsersToAct'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        //get all the active users
        $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM attendance_users WHERE attendance_id = ?d", $attendance_id);

        if ($activeUsers){
            foreach ($activeUsers as $result) {
                $userInp = intval(@$_POST[$result->userID]); //get the record from the teacher (input name is the user id)
                // //check if there is record for the user for this activity
                $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book
                                                        WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $result->userID);
                if($checkForBook->count) {
                    //update
                    Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                } else {
                    //insert
                    Database::get()->query("INSERT INTO attendance_book SET uid = ?d,
                                                    attendance_activity_id = ?d, attend = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                }
            }
            Session::Messages($langAttendanceEdit,"alert-success");
            redirect_to_home_page("modules/attendance/index.php");
        }
    }
    //display users
    $resultUsers = Database::get()->queryArray("SELECT attendance_users.id AS recID, attendance_users.uid AS userID,
                                                user.surname AS surname, user.givenname AS name, user.am AS am, course_user.reg_date AS reg_date
                                            FROM attendance_users, user, course_user
                                                WHERE attendance_id = ?d
                                                AND attendance_users.uid = user.id
                                                AND `user`.id = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);
    if ($resultUsers) {
        //table to display the users
        $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' id='user_attendances_form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;ins=" . getIndirectReference($actID) . "'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr>
                  <th class='text-center' width='5%'>$langID</th>
                  <th class='text-left'>$langName $langSurname</th>
                  <th>$langAm</th>
                  <th class='text-center'>$langRegistrationDateShort</th>
                  <th class='text-center'>$langAttendanceAbsences</th>
                </tr>
            </thead>
            <tbody>";

        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $cnt++;
            $tool_content .= "<tr>
                <td class='text-center'>$cnt</td>
                <td> " . display_user($resultUser->userID). "</td>
                <td>$resultUser->am</td>
                <td class='text-center'>" . nice_format($resultUser->reg_date, true, true) . "</td>
                <td class='text-center'><input type='checkbox' value='1' name='userspresence[$resultUser->userID]'";
                //check if the user has attendace for this activity already OR if it should be automatically inserted here
                $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $resultUser->userID);
                if(isset($q->attend) && $q->attend == 1) {
                    $tool_content .= " checked";
                }
                $tool_content .= "><input type='hidden' value='" . getIndirectReference($actID) . "' name='actID'></td>";
                $tool_content .= "</tr>";
        }
        $tool_content .= "</tbody></table>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<div class='col-xs-12'>" .
                        form_buttons(array(
                            array(
                                'text' => $langAttendanceBooking,
                                'name' => 'bookUsersToAct',
                                'value'=> $langAttendanceBooking
                                ))).
                "<a href='index.php?course=$course_code&amp;attendance_id=" . $attendance_id . "' class='btn btn-default'>$langCancel</a>";
//        if ($act_type == 1) {
//            $tool_content .= form_buttons(array(
//                                array(
//                                    'text' => $langAttendanceAutoBook,
//                                    'name' => 'updateUsersToAct',
//                                    'value'=> $langAttendanceAutoBook
//                                )));
//            }
        $tool_content .= "</div></div>";
        $tool_content .= generate_csrf_token_form_field() ."</form></div>";
        $tool_content .= "</tbody></table>";
    }
}

/**
 * @brief display attendance activities
 * @global type $tool_content
 * @global type $course_code
 * @global type $langAttendanceActList
 * @global type $langTitle
 * @global type $langType
 * @global type $langAttendanceActivityDate
 * @global type $langAttendanceAbsences
 * @global type $langAttendanceNoTitle
 * @global type $langExercise
 * @global type $langAssignment
 * @global type $langAttendanceInsAut
 * @global type $langAttendanceInsMan
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $langConfirmDelete
 * @global type $langAttendanceNoActMessage1
 * @global type $langAttendanceActivity
 * @global type $langHere
 * @global type $langAttendanceNoActMessage3
 * @global type $langToA
 * @global type $langcsvenc1
 * @global type $langcsvenc2
 * @global type $langConfig
 * @global type $langUsers
 * @global type $langGradebookAddActivity
 * @global type $langInsertWorkCap
 * @global type $langInsertExerciseCap
 * @global type $langAdd
 * @global type $langExport
 * @param type $attendance_id
 */
function display_certificate_activities($certificate_id) {

    global $tool_content, $course_code, $attendance,
           $langAttendanceActList, $langTitle, $langType, $langAttendanceActivityDate, $langAttendanceAbsences,
           $langGradebookNoTitle, $langExercise, $langAssignment,$langAttendanceInsAut, $langAttendanceInsMan,
           $langDelete, $langEditChange, $langConfirmDelete, $langAttendanceNoActMessage1, $langAttendanceActivity,
           $langHere, $langAttendanceNoActMessage3, $langToA, $langcsvenc1, $langcsvenc2,
           $langConfig, $langStudents, $langGradebookAddActivity, $langInsertWorkCap, $langInsertExerciseCap,
           $langAdd, $langExport, $langBack, $langNoRegStudent, $course_id;


    $tool_content .= action_bar(
            array(
                array('title' => $langAdd,
                      'level' => 'primary-label',
                      'options' => array(
                          array('title' => "$langInsertWorkCap",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityAs=1",
                                'icon' => 'fa fa-flask space-after-icon',
                                'class' => ''),
                          array('title' => "$langInsertExerciseCap",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityEx=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Blog",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityBlog=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Σχόλια σε blogs",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityCom=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Σχόλια στο μάθημα",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityComCourse=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Forum",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityFor=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Γραμμή μάθησης",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityLp=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Likes στους κοινωνικούς συνδέσμους",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityRat=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Likes στα forum posts ",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityRatPosts=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Προβολή εγγράφου",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityDoc=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Προβολή πολυμέσου",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityMul=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Προβολή video link",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityVid=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Προσπέλαση ηλ. βιβλίου",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityBook=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Απάντηση σε ερωτηματολόγιο",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityQue=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "Αριθμός δημιουργημένων σελίδων στο wiki",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityWi=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => '')),
                     'icon' => 'fa-plus'),
                array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'),
                array('title' => $langConfig,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;editSettings=1",
                      'icon' => 'fa-cog'),
                array('title' => "$langExport $langToA $langcsvenc1",
                        'url' => "dumpcertificatebook.php?course=$course_code&amp;certificate_id=$certificate_id&amp;enc=1253",
                    'icon' => 'fa-file-excel-o'),
                array('title' => "$langExport $langToA $langcsvenc2",
                        'url' => "dumpcertificatebook.php?course=$course_code&amp;certificate_id=$certificate_id",
                        'icon' => 'fa-file-excel-o'),
            ),
            true
        );

    /*
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count
                                            FROM attendance_users WHERE attendance_id=?d ", $attendance_id)->count;
    if ($participantsNumber == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoRegStudent <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=" . $attendance->id . "&amp;editUsers=1'>$langHere</a>.</div>";
    }
    */
    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM certificate_criterion WHERE certificate = ?d  ORDER BY `id` DESC", $certificate_id);

    if (count($result) > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                        <table class='table-default'>
                        <tr class='list-header'><th class='text-center' colspan='5'>$langAttendanceActList</th></tr>
                        <tr class='list-header'>
                            <th>$langTitle</th>
                            <th>$langType</th>
                            <th>Τιμή</th>
                            <th class='text-center'><i class='fa fa-cogs'></i></th>
                        </tr>";
        foreach ($result as $details) {

        	//name
        	if($details->activity_type == "exercise"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM exercise WHERE exercise.course_id = ?d AND exercise.id = ?d", $course_id, $details->resource);
        		foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
        		}
        		$type = "Άσκηση";
        	  if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
          }

          //SOSOSOSO εδώ και ΟΛΑ ΤΑ ΑΛΛΑ

          if($details->activity_type == "assignment"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $details->resource);
        		foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
        		}
        		$type = "Εργασία";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == LearningPathEvent::ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT name FROM  lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->name;
            }
        		$type = "Γραμμή μάθησης";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == "document"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM document WHERE document.course_id = ?d AND document.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "Έγγραφο";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == "video"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM video WHERE video.course_id = ?d AND video.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "Πολυμέσα";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == "videolink"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM videolink WHERE videolink.course_id = ?d AND videolink.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "Σύνδεσμος video";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == "ebook"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM ebook WHERE ebook.course_id = ?d AND ebook.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "eBook";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == "questionnaire"){
        		$checkForExer = Database::get()->queryArray("SELECT name FROM poll WHERE poll.course_id = ?d AND poll.pid = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->name;
            }
        		$type = "Ερωτηματολόγιο";
            if($details->resource == ""){
              $title = "Όλες οι δραστηριότητες";
            }
        	}

          if($details->activity_type == BlogEvent::ACTIVITY){
        		$type = "Blog";
            $title = "Πλήθος posts";
        	}

          if($details->activity_type == CommentEvent::BLOG_ACTIVITY && $details->module == MODULE_ID_COMMENTS){
        		$type = "Σχόλια";
            $title = "Σχόλια σε blogs";
        	}

          if($details->activity_type == CommentEvent::COURSE_ACTIVITY && $details->module == MODULE_ID_COMMENTS){
        		$type = "Σχόλια";
            $title = "Σχόλια στο μάθημα";
        	}

          if($details->activity_type == ForumEvent::ACTIVITY){
        		$type = "Συζητήσεις";
            $title = "Posts σε συζητήσεις";
        	}

          if($details->activity_type == "social bookmark likes"){
        		$type = "Likes στους κοινωνικούς συνδέσμους";
            $title = "Ratings";
        	}

          if($details->activity_type == "forum likes"){
        		$type = "Likes στα forum posts";
            $title = "Ratings";
        	}
          if($details->activity_type == WikiEvent::ACTIVITY){
        		$type = "Aριθμός δημιουργημένων σελίδων στο wiki";
            $title = "Wiki";
        	}



            //$content = ellipsize_html($details->description, 50);
            $tool_content .= "<tr><td>";
            $tool_content .= $title;
            $tool_content .= "</td><td>".$type."</td><td>";

            if($details->operator=='eq') $tool_content .=" = ";
            if($details->operator=='lt') $tool_content .=" < ";
            if($details->operator=='gt') $tool_content .=" > ";
            if($details->operator=='let') $tool_content .=" <= ";
            if($details->operator=='get') $tool_content .=" >= ";
            if($details->operator=='nee') $tool_content .=" != ";


            $tool_content .= " $details->threshold </td>";

            $tool_content .= "<td class='text-center option-btn-cell'>".
                    action_button(array(
                                array('title' => $langEditChange,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;modify=" . getIndirectReference($details->id)
                                    ),
                                array('title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;&amp;certificate_id=$certificate_id&amp;delete=" .getIndirectReference($details->id),
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete'))).
                    "</td></tr>";
        } // end of while
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν δραστηριότητες στο πιστοποιητικό. Κάντε κλικ στο 'Προσθήκη' για να εισάγετε.</div>";
    }
}

/**
 * @brief display available exercises for adding them to attendance
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langGradebookActivityDate2
 * @global type $langDescr
 * @global type $langAdd
 * @global type $langAttendanceNoActMessageExe4
 * @global type $langTitle
 * @param type $attendance_id
 */
function certificate_display_available_exercises($certificate_id) {

    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                AND exercise.active = 1 AND exercise.id
                                NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'exercise' AND module = 10)", $course_id, $certificate_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";

        //check if there is an option for all
        //$check = Database::get()->querySingle("SELECT id FROM certificate_criterion WHERE certificate = ?d AND resource='' AND module=10 ", $certificate_id);
        //if(!isset($check) && $check ==""){
            $tool_content .= "<table class='table-default'>";
            $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
            $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=2")."</th></table>";
        //}

        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langDescr</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr>";

        foreach ($checkForExer as $newExerToCertificate) {
            $content = ellipsize_html($newExerToCertificate->description, 50);
            $tool_content .= "<tr><td><b>";
            if (!empty($newExerToCertificate->title)) {
                $tool_content .= q($newExerToCertificate->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>" . nice_format($newExerToCertificate->start_date, true, true) . " </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=1");
        }
        $tool_content .= "</td></tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
    }
}

/**
 * @brief display available assignments for adding them to attendance
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
 * @param type $attendance_id
 */
function certificate_display_available_assignments($certificate_id) {

    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                AND assignment.active = 1
                                                AND assignment.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'assignment' AND module = 5)", $course_id, $certificate_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
        $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=2")."</th></table>";

        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langDescr</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr>";

        foreach ($checkForExer as $newExerToCertificate) {
            $content = ellipsize_html($newExerToCertificate->description, 50);
            $tool_content .= "<tr><td><b>";
            if (!empty($newExerToCertificate->title)) {
                $tool_content .= q($newExerToCertificate->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>" . nice_format($newExerToCertificate->submission_date, true, true) . " </div></td>"
                    . "<td>" . $newExerToCertificate->description . "</td>";
            $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=2");
        }
        $tool_content .= "</td></tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
    }
}


function certificate_display_available_Blog($certificate_id){

}
function certificate_display_available_Com($certificate_id){

}
function certificate_display_available_For($certificate_id){

}

function certificate_display_available_Lp($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  //$checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.visible = 1 AND lp_learnPath.learnPath_id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' )", $course_id, $certificate_id);

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                              AND lp_learnPath.visible = 1
                                              AND lp_learnPath.learnPath_id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'learning path' AND module = 23)", $course_id, $certificate_id);
  $checkForLpNumber = count($checkForLp);
  if ($checkForLpNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=23")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescr</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForLp as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->name)) {
              $tool_content .= q($newExerToCertificate->name);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>"
                  . "<td>" . $newExerToCertificate->comment . "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->learnPath_id . "&amp;type=23");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
  }
}

function certificate_display_available_Rat($certificate_id){

}

function certificate_display_available_Doc($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForDoc = Database::get()->queryArray("SELECT * FROM document WHERE document.course_id = ?d
                                              AND document.visible = 1
                                              AND document.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'document' AND module = 3)", $course_id, $certificate_id);
  $checkForDocNumber = count($checkForDoc);
  if ($checkForDocNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=3")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescr</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForDoc as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->title)) {
              $tool_content .= q($newExerToCertificate->title);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>"
                  . "<td>" . $newExerToCertificate->comment . "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=3");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν έγγραφα</div>";
  }
}

function certificate_display_available_Mul($certificate_id){
  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForMul = Database::get()->queryArray("SELECT * FROM video WHERE video.course_id = ?d
                                              AND video.visible = 1
                                              AND video.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'video' AND module = 4)", $course_id, $certificate_id);

  $checkForMulNumber = count($checkForMul);
  if ($checkForMulNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=4")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescr</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForMul as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->title)) {
              $tool_content .= q($newExerToCertificate->title);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>"
                  . "<td>" . $newExerToCertificate->description . "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=4");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν έγγραφα</div>";
  }
}


function certificate_display_available_Vid($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForVid = Database::get()->queryArray("SELECT * FROM videolink WHERE videolink.course_id = ?d
                                              AND videolink.visible = 1
                                              AND videolink.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'videolink' AND module = 4)", $course_id, $certificate_id);

  $checkForVidNumber = count($checkForVid);
  if ($checkForVidNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=4a")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescr</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForVid as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->title)) {
              $tool_content .= q($newExerToCertificate->title);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>"
                  . "<td>" . $newExerToCertificate->description . "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=4a");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν έγγραφα</div>";
  }
}

function certificate_display_available_Book($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForBook = Database::get()->queryArray("SELECT * FROM ebook WHERE ebook.course_id = ?d
                                              AND ebook.visible = 1
                                              AND ebook.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'ebook' AND module = 18)", $course_id, $certificate_id);

  $checkForBookNumber = count($checkForBook);
  if ($checkForBookNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=18")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForBook as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->title)) {
              $tool_content .= q($newExerToCertificate->title);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=18");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν έγγραφα</div>";
  }
}

function certificate_display_available_Que($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

  $checkForPol = Database::get()->queryArray("SELECT * FROM poll WHERE poll.course_id = ?d
                                              AND poll.active = 1
                                              AND poll.pid NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'questionnaire' AND module = 21)", $course_id, $certificate_id);

  $checkForPolNumber = count($checkForPol);
  if ($checkForPolNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>Επιλογή όλων</th>";
      $tool_content .= "<th class='text-center'>". icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=&amp;type=21")."</th></table>";

      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescr</th>";
      $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
      $tool_content .= "</tr>";

      foreach ($checkForPol as $newExerToCertificate) {
          //$content = ellipsize_html($newExerToCertificate->description, 50);
          $tool_content .= "<tr><td><b>";
          if (!empty($newExerToCertificate->name)) {
              $tool_content .= q($newExerToCertificate->name);
          }
          $tool_content .= "</b>";
          $tool_content .= "</td>"
                  . "<td>" . $newExerToCertificate->description . "</td>";
          $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->pid . "&amp;type=21");
      }
      $tool_content .= "</td></tr></table></div></div></div>";
  } else {
      $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν έγγραφα</div>";
  }

}
function certificate_display_available_Wi($certificate_id){

}



/**
 * @brief add other attendance activity
 * @global type $tool_content
 * @global type $course_code
 * @global type $langTitle
 * @global type $langAttendanceInsAut
 * @global type $langAdd
 * @global type $langAdd
 * @global type $langSave
 * @global type $langAttendanceActivityDate
 * @param type $attendance_id
 */
function add_certificate_other_activity($certificate_id) {

    global $tool_content, $course_code, $langDescription,
           $langTitle, $langAttendanceInsAut, $langAdd,
           $langAdd, $langSave, $langAttendanceActivityDate;

    $date_error = Session::getError('date');
    $tool_content .= "<div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id'>
                    <fieldset>";
                    if (isset($_GET['modify'])) { // modify an existing attendance activity

                        $id  = filter_var(getDirectReference($_GET['modify']), FILTER_VALIDATE_INT);

                        //All activity data (check if it's in this attendance)
                        $modifyActivity = Database::get()->querySingle("SELECT * FROM certificate_criterion WHERE id = ?d AND certificate = ?d", $id, $certificate_id);

                        $certificateActivityToModify = $id;


                        if(isset($modifyActivity -> threshold)){
                         $threshold = $modifyActivity -> threshold;
                        }else {
                            $threshold = "";
                        }

                        if(isset($modifyActivity -> operator)){
                         $operator = $modifyActivity -> operator;
                        }else {
                            $operator = "";
                        }


                    }

                    $tool_content .= "
                        <div class='form-group'>
                            <label for='actTitle' class='col-sm-2 control-label'>Τελεστής</label>
                            <div class='col-sm-10'>
                                <select class='form-control' name='operator'>
                                    <option value='eq' ".((!isset($operator))?'selected="selected"':"")."> </option>
                                    <option value='eq' ".(($operator=='eq')?'selected="selected"':"").">=</option>
                                    <option value='lt' ".(($operator=='lt')?'selected="selected"':"")."><</option>
                                    <option value='gt' ".(($operator=='gt')?'selected="selected"':"").">></option>
                                    <option value='let' ".(($operator=='let')?'selected="selected"':"")."><=</option>
                                    <option value='get' ".(($operator=='get')?'selected="selected"':"").">>=</option>
                                    <option value='nee' ".(($operator=='nee')?'selected="selected"':"").">!=</option>

                                </select>
                            </div>
                        </div>
                        <div class='form-group".($date_error ? " has-error" : "")."'>
                            <label for='date' class='col-sm-2 control-label'>Τιμή</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='threshold' id='threshold' value='$threshold'/>
                                <span class='help-block'>$date_error</span>
                                <small>Αν δεν επιλέξετε τιμή, λαμβάνεται υπόψη η ολοκλήρωση της δραστηριότητας</small>
                            </div>

                        </div>";

                    $tool_content .= "<div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>".form_buttons(array(
                        array(
                            'text' => $langSave,
                            'name' => 'submitCertificateActivity',
                            'value'=> $langAdd
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                        )
                    ))."</div></div>";
                    if (isset($_GET['modify'])) {
                        $tool_content .= "<input type='hidden' name='id' value='" . $certificateActivityToModify . "'>";
                    } else {
                        $tool_content .= " <input type='hidden' name='id' value=''>";
                    }
                    $tool_content .= "</fieldset>
                            </form>
                        </div>
                    </div>
                </div>";
}

function add_certificate_other_activity_only_value($certificate_id, $type) {

    global $tool_content, $course_code, $langDescription,
           $langTitle, $langAttendanceInsAut, $langAdd,
           $langAdd, $langSave, $langAttendanceActivityDate;

    $date_error = Session::getError('date');
    $tool_content .= "<div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id'>
                    <fieldset>";

                    $tool_content .= "
                        <div class='form-group'>
                            <label for='actTitle' class='col-sm-2 control-label'>Τελεστής</label>
                            <div class='col-sm-10'>
                                <select class='form-control' name='operator'>
                                    <option value='eq'> </option>
                                    <option value='eq'>>=</option>
                                    <option value='lt' ><</option>
                                    <option value='gt' >></option>
                                    <option value='let'><=</option>
                                    <option value='get'>>=</option>
                                    <option value='nee'>!=</option>

                                </select>
                            </div>
                        </div>
                        <div class='form-group".($date_error ? " has-error" : "")."'>
                            <label for='date' class='col-sm-2 control-label'>Τιμή</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='threshold' id='threshold' value=''/>
                                <span class='help-block'>$date_error</span>
                                <small>Αν δεν επιλέξετε τιμή, λαμβάνεται υπόψη η ολοκλήρωση της δραστηριότητας</small>
                            </div>

                        </div>";

                    $tool_content .= "<div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>".form_buttons(array(
                        array(
                            'text' => $langSave,
                            'name' => 'submitCertificateActivity',
                            'value'=> $langAdd
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                        )
                    ))."</div></div>";

                    $tool_content .= "<input type='hidden' name='type' value='$type'>";

                    $tool_content .= "</fieldset>
                            </form>
                        </div>
                    </div>
                </div>";
}

/**
 * @brief add available activity in attendance
 * @global type $course_id
 * @param type $attendance_id
 * @param type $id
 * @param type $type
 */
function add_certificate_activity($certificate_id, $id, $type) {

    global $course_id;

    if ($type == 1) { // add exercises

       //check if it is for a sing exe
       if($id!=""){
            //checking if it is new or not
            $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                                                AND exercise.active = 1 AND exercise.id
                                                        NOT IN (SELECT resource FROM certificate_criterion
                                                                    WHERE  certificate = ?d AND resource!='' AND activity_type = 'exercise' AND module = 10)
                                                        AND exercise.id = ?d", $course_id, $certificate_id, $id);
            if ($checkForExe) {
                $module_auto_id = $checkForExe->id;
                $module_auto_type = "exercise";

                return Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=10, resource = ?d, activity_type = ?s",
                                        $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
            }
       }else{
           Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=10, activity_type = 'exercise'",
                                        $certificate_id);
            return 1;
       }
    }
    if ($type == 2) { // assignents

       //check if it is for a sing exe
       if($id!=""){
            //checking if it is new or not
            $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                                AND assignment.active = 1 AND assignment.id
                                                        NOT IN (SELECT resource FROM certificate_criterion
                                                                    WHERE  certificate = ?d AND resource!='' AND activity_type = 'assignment' AND module = 5)
                                                        AND assignment.id = ?d", $course_id, $certificate_id, $id);

            if ($checkForAss) {
                $module_auto_id = $checkForAss->id;
                $module_auto_type = "assignment";

                return Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=5, resource = ?d, activity_type = ?s",
                                        $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
            }
       }else{
           Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=5, activity_type = 'assignment'",
                                        $certificate_id);
            return 1;
       }
    }
    if ($type == 23) { // LP

       //check if it is for a sing exe
       if($id!=""){
            //checking if it is new or not
            $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                                        AND lp_learnPath.visible = 1
                                                        AND lp_learnPath.learnPath_id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'learning path' AND module = 23) AND lp_learnPath.learnPath_id = ?d", $course_id, $certificate_id, $id);

            if ($checkForLp) {
                $module_auto_id = $id;
                $module_auto_type = "learning path";

                return Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=23, resource = ?d, activity_type = ?s",
                                        $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
            }
       }else{
           Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=23, activity_type = 'learning path'",
                                        $certificate_id);
            return 1;
       }
    }
    if ($type == 3) { // docs

       //check if it is for a sing exe
       if($id!=""){
            $checkForDoc = Database::get()->queryArray("SELECT * FROM document WHERE document.course_id = ?d
                                                        AND document.visible = 1
                                                        AND document.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'document' AND module = 3) AND document.id = ?d", $course_id, $certificate_id, $id);

            if ($checkForDoc) {
                $module_auto_id = $id;
                $module_auto_type = "document";

                return Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=3, resource = ?d, activity_type = ?s",
                                        $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
            }
       }else{
           Database::get()->query("INSERT INTO certificate_criterion
                                            SET certificate = ?d, module=3, activity_type = 'document'",
                                        $certificate_id);
            return 1;
       }
    }
    if ($type == 4){ //mul
      if($id!=""){

           $checkForMul = Database::get()->queryArray("SELECT * FROM video WHERE video.course_id = ?d
                                                       AND video.visible = 1
                                                       AND video.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'video' AND module = 4) AND video.id = ?d", $course_id, $certificate_id, $id);

           if ($checkForMul) {
               $module_auto_id = $id;
               $module_auto_type = "video";

               return Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=4, resource = ?d, activity_type = ?s",
                                       $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
           }
      }else{
          Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=4, activity_type = 'video'",
                                       $certificate_id);
           return 1;
      }
    }
    if ($type == "4a"){ //video
      if($id!=""){

        $checkForVid = Database::get()->queryArray("SELECT * FROM videolink WHERE videolink.course_id = ?d
                                                    AND videolink.visible = 1
                                                    AND videolink.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'videolink' AND module = 4)", $course_id, $certificate_id);

           $checkForVid = Database::get()->queryArray("SELECT * FROM videolink WHERE videolink.course_id = ?d
                                                       AND videolink.visible = 1
                                                       AND videolink.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'videolink' AND module = 4) AND videolink.id = ?d", $course_id, $certificate_id, $id);

           if ($checkForVid) {
               $module_auto_id = $id;
               $module_auto_type = "videolink";

               return Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=4, resource = ?d, activity_type = ?s",
                                       $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
           }
      }else{
          Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=4, activity_type = 'videolink'",
                                       $certificate_id);
           return 1;
      }
    }
    if ($type == 18){ //ebbok
      if($id!=""){

          $checkForEbook = Database::get()->queryArray("SELECT * FROM ebook WHERE ebook.course_id = ?d
                                                    AND ebook.visible = 1
                                                    AND ebook.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'ebook' AND module = 18)", $course_id, $certificate_id);

           if ($checkForEbook) {
               $module_auto_id = $id;
               $module_auto_type = "ebook";

               return Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=18, resource = ?d, activity_type = ?s",
                                       $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
           }
      }else{
          Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=18, activity_type = 'ebook'",
                                       $certificate_id);
           return 1;
      }
    }
    if ($type == 21){ //quest
      if($id!=""){

          $checkForEbook = Database::get()->queryArray("SELECT * FROM poll WHERE poll.course_id = ?d
                                                    AND poll.active = 1
                                                    AND poll.pid NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'questionnaire' AND module = 21)", $course_id, $certificate_id);

           if ($checkForEbook) {
               $module_auto_id = $id;
               $module_auto_type = "questionnaire";

               return Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=21, resource = ?d, activity_type = ?s",
                                       $certificate_id, $module_auto_id, $module_auto_type)->lastInsertID;
           }
      }else{
          Database::get()->query("INSERT INTO certificate_criterion
                                           SET certificate = ?d, module=21, activity_type = 'questionnaire'",
                                       $certificate_id);
           return 1;
      }
    }

}


function update_user_attendance_activities($attendance_id, $uid) {
    $attendanceActivities = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d AND auto = 1", $attendance_id);
    foreach ($attendanceActivities as $attendanceActivity) {
        if ($attendanceActivity->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
            $exerciseUserRecord = Database::get()->querySingle("SELECT * FROM exercise_user_record WHERE eid = ?d AND uid = $uid AND attempt_status != ?d AND attempt_status != ?d LIMIT 1", $attendanceActivity->module_auto_id, ATTEMPT_PAUSED, ATTEMPT_CANCELED);
            if ($exerciseUserRecord) {
                $allow_insert = TRUE;
            }
        } elseif ($attendanceActivity->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
            $grd = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = $uid", $attendanceActivity->module_auto_id);
            if ($grd) {
                $allow_insert = TRUE;
            }
        }
        if (isset($allow_insert) && $allow_insert) {
            update_attendance_book($uid, $attendanceActivity->module_auto_id, $attendanceActivity->module_auto_type, $attendance_id);
        }
        unset($allow_insert);
    }
}

/**
 * @brief create new attendance
 * @global string $tool_content
 * @global type $course_code
 * @global type $langNewAttendance2
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 */
function new_certificate() {

    global $tool_content, $course_code, $langNewAttendance2, $head_content,
           $langTitle, $langSave, $langInsert, $langAttendanceLimitNumber,
           $attendance_limit, $langStart, $langEnd, $language;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#start_date, #end_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
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
    $limit_error  = Session::getError('limit');
    $limit = Session::has('limit') ? Session::get('limit') : '';

    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group'>
                    <label class='col-xs-12'>Δημιουργία νέου πιστοποιητικού</label></div>
                    <div class='form-group".($title_error ? " has-error" : "")."'>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' placeholder='$langTitle' name='title'>
                            <span class='help-block'>$title_error</span>
                        </div>
                    </div>
                    <div class='form-group".($start_date_error ? " has-error" : "")."'>
                        <div class='col-xs-12'>
                            <label>$langStart</label>
                        </div>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' name='start_date' id='start_date' value='$start_date'>
                            <span class='help-block'>$start_date_error</span>
                        </div>
                    </div>
                    <div class='form-group".($end_date_error ? " has-error" : "")."'>
                        <div class='col-xs-12'>
                            <label>$langEnd</label>
                        </div>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' name='end_date' id='end_date' value='$end_date'>
                            <span class='help-block'>$end_date_error</span>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-xs-12'>Αυτόματη ανάθεση <input class='' type='checkbox' name='autoassign' value='1'></label>
                    </div>
                    <div class='form-group'>
                        <label class='col-xs-12'>Ενεργό <input class='' type='checkbox' name='active' value='1'></label>
                    </div>

                    <div class='form-group'>
                        <div class='col-xs-12'>".form_buttons(array(
                            array(
                                    'text' => $langSave,
                                    'name' => 'newCertificate',
                                    'value'=> $langInsert
                                ),
                            array(
                                'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                )
                            ))."</div>
                    </div>
            </form>
        </div>";
}

/**
 * @brief dislay user presences
 * @global type $course_code
 * @global type $tool_content
 * @global type $langTitle
 * @global type $langType
 * @global type $langAttendanceNewBookRecord
 * @global type $langDate
 * @global type $langAttendanceNoActMessage1
 * @global type $langAttendanceBooking
 * @global type $langAttendanceActAttend
 * @global type $langAttendanceActCour
 * @global type $langAttendanceInsAut
 * @global type $langAttendanceInsMan
 * @global type $langGradebookUpToDegree
 * @global type $langAttendanceBooking
 * @param type $attendance_id
 */
function display_user_presences($attendance_id) {

    global $course_code, $tool_content,
           $langTitle, $langType, $langAttendanceNewBookRecord, $langDate,
           $langAttendanceNoActMessage1, $langAttendanceBooking,
           $langAttendanceActAttend, $langAttendanceActCour,
           $langAttendanceInsAut, $langAttendanceInsMan,
           $langAttendanceBooking;

        $attendance_limit = get_attendance_limit($attendance_id);

        $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;
        $userID = intval($_GET['book']); //user
        //check if there are booking records for the user, otherwise alert message for first input
        $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) AS count FROM attendance_book, attendance_activities
                            WHERE attendance_book.attendance_activity_id = attendance_activities.id
                            AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
        if(!$checkForRecords) {
            $tool_content .="<div class='alert alert-success'>$langAttendanceNewBookRecord</div>";
        }

        //get all the activities
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $actNumber = count($result);
        if ($actNumber > 0) {
            $tool_content .= "<h5>". display_user($userID) ."</h5>";
            $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                              <table class='table-default'>";
            $tool_content .= "<tr><th>$langTitle</th><th >$langDate</th><th>$langType</th>";
            $tool_content .= "<th width='10' class='text-center'>$langAttendanceBooking</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage1</div>";
        }

        if ($result) {
            foreach ($result as $activity) {
                //check if there is auto mechanism
                if($activity->auto == 1) {
                    if($activity->module_auto_type) { //assignments, exercises, lp(scorms)
                        $userAttend = attendForAutoActivities($userID, $activity->module_auto_id, $activity->module_auto_type);
                        if ($userAttend == 0) {
                            $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                            if ($q) {
                                $userAttend = $q->attend;
                            }
                        }
                    }
                } else {
                    $q = Database::get()->querySingle("SELECT attend FROM attendance_book
                                                        WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if ($q) {
                        $userAttend = $q->attend;
                    } else {
                        $userAttend = 0;
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
                    $tool_content .= "<td class='smaller'>$langAttendanceActCour";
                    if ($activity->auto) {
                        $tool_content .= "<br>($langAttendanceInsAut)";
                    } else {
                        $tool_content .= "<br>($langAttendanceInsMan)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActAttend</td>";
                }
                $tool_content .= "<td class='text-center'>
                <input type='checkbox' value='1' name='" . $activity->id . "'";
                if(isset($userAttend) && $userAttend) {
                    $tool_content .= " checked";
                }
                $tool_content .= ">
                <input type='hidden' value='" . $userID . "' name='userID'>
                </td></tr>";
            } // end of while
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='pull-right'><input class='btn btn-primary' type='submit' name='bookUser' value='$langAttendanceBooking'></div>";
}


/**
 * @brief display all users presences
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langName
 * @global type $langSurname
 * @global type $langID
 * @global type $langAm
 * @global type $langRegistrationDateShort
 * @global type $langAttendanceAbsences
 * @global type $langAttendanceBook
 * @global type $langAttendanceDelete
 * @global type $langConfirmDelete
 * @global type $langNoRegStudent
 * @global type $langHere
 * @param type $attendance_id
 */
function display_all_users_presences($attendance_id) {

    global $course_id, $course_code, $tool_content, $langName, $langSurname,
           $langID, $langAm, $langRegistrationDateShort, $langAttendanceAbsences,
           $langAttendanceBook, $langAttendanceDelete, $langConfirmDelete,
           $langNoRegStudent, $langHere;

    $attendance_limit = get_attendance_limit($attendance_id);

    $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID,
                                                    attendance_users.uid AS userID, user.surname AS surname,
                                                    user.givenname AS name, user.am AS am,
                                                    DATE(course_user.reg_date) AS reg_date
                                                FROM attendance_users, user, course_user
                                                    WHERE attendance_id = ?d
                                                    AND attendance_users.uid = user.id
                                                    AND `user`.id = `course_user`.`user_id`
                                                    AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);
    if (count($resultUsers)) {
        //table to display the users
        $tool_content .= "<table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr>
                  <th width='1'>$langID</th>
                  <th>$langName $langSurname</th>
                  <th>$langAm</th>
                  <th class='center'>$langRegistrationDateShort</th>
                  <th class='center'>$langAttendanceAbsences</th>
                  <th class='text-center'><i class='fa fa-cogs'></i></th>
                </tr>
            </thead>
            <tbody>";
        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $cnt++;
            $tool_content .= "<tr>
                <td>$cnt</td>
                <td>" . display_user($resultUser->userID) . "</td>
                <td>$resultUser->am</td>
                <td>" . nice_format($resultUser->reg_date) . "</td>
                <td>" . userAttendTotal($attendance_id, $resultUser->userID) . "/" . $attendance_limit . "</td>
                <td class='option-btn-cell'>"
                   . action_button(array(
                        array('title' => $langAttendanceBook,
                            'icon' => 'fa-plus',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;book=" . $resultUser->userID),
                       array('title' => $langAttendanceDelete,
                            'icon' => 'fa-times',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;at=$attendance_id&amp;ruid=$resultUser->userID&amp;deleteuser=yes",
                            'confirm' => $langConfirmDelete,
                            'class' => 'delete')))."</td>
            </tr>";
        }
        $tool_content .= "</tbody></table>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoRegStudent <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;editUsers=1'>$langHere</a>.</div>";
    }
}

/**
 * @brief insert/modify attendance settings
 * @global string $tool_content
 * @global type $course_code
 * @global type $langTitle
 * @global type $langSave
 * @global type $langAttendanceLimitNumber
 * @global type $langAttendanceUpdate
 * @global type $langSave
 * @global type $attendance_title
 * @param type $attendance_id
 */
function certificate_settings($certificate_id) {

    global $tool_content, $course_code, $language,
           $langTitle, $langSave, $langAttendanceLimitNumber,
           $langAttendanceUpdate, $langSave, $head_content,
           $certificate, $langStart, $langEnd;
    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#start_date, #end_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : $certificate->title;
    $start_date_error = Session::getError('start_date');
    $start_date = Session::has('start_date') ? Session::get('start_date') : DateTime::createFromFormat('Y-m-d H:i:s', $certificate->created)->format('d-m-Y H:i');
    $end_date_error = Session::getError('end_date');
    $end_date = Session::has('end_date') ? Session::get('end_date') : DateTime::createFromFormat('Y-m-d H:i:s', $certificate->expires)->format('d-m-Y H:i');
    //$limit_error  = Session::getError('limit');
    //$limit = Session::has('limit') ? Session::get('limit') : get_certificate_limit($attendance_id);

    $autoassign = Session::has('autoassign') ? Session::get('autoassign') : $certificate->autoassign;
    $active = Session::has('active') ? Session::get('active') : $certificate->active;

    $tool_content .= "<div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&certificate_id=$certificate_id'>
                    <div class='form-group".($title_error ? " has-error" : "")."'>
                        <label class='col-xs-12'>$langTitle</label>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' placeholder='$langTitle' name='title' value='".q($title)."'>
                            <span class='help-block'>$title_error</span>
                        </div>
                    </div>
                    <div class='form-group".($start_date_error ? " has-error" : "")."'>
                        <div class='col-xs-12'>
                            <label>$langStart</label>
                        </div>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' name='start_date' id='start_date' value='$start_date'>
                            <span class='help-block'>$start_date_error</span>
                        </div>
                    </div>
                    <div class='form-group".($end_date_error ? " has-error" : "")."'>
                        <div class='col-xs-12'>
                            <label>$langEnd</label>
                        </div>
                        <div class='col-xs-12'>
                            <input class='form-control' type='text' name='end_date' id='end_date' value='$end_date'>
                            <span class='help-block'>$end_date_error</span>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label class='col-xs-12'>Αυτόματη ανάθεση <input class='' type='checkbox' name='autoassign' value = 1 ";

                    if($autoassign == 1) {
                        $tool_content .= " checked ";
                    }

                    $tool_content .= "></label>
                    </div>
                    <div class='form-group'>
                        <label class='col-xs-12'>Ενεργό <input class='' type='checkbox' name='active' value = 1 ";

                    if($active == 1) {
                        $tool_content .= " checked ";
                    }

                    $tool_content .= "></label>
                    </div>

                    <div class='form-group'>
                        <div class='col-xs-12'>".form_buttons(array(
                            array(
                                'text' => $langSave,
                                'name' => 'submitAttendanceBookSettings',
                                'value'=> $langAttendanceUpdate
                            ),
                            array(
                                'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id"
                            )
                        ))."</div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>";
}

/**
 * @brief modify user attendance settings
 * @global string $tool_content
 * @global type $course_code
 * @global type $langGroups
 * @global type $langAttendanceUpdate
 * @global type $langAttendanceInfoForUsers
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
 * @param type $attendance_id
 */
function user_attendance_settings($attendance_id) {

    global $tool_content, $course_code, $langGroups,
           $langAttendanceUpdate, $langAttendanceInfoForUsers,
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
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id&editUsers=1'>
                    <div class='form-group'>
                        <label class='col-xs-12'><span class='help-block'>$langAttendanceInfoForUsers</span></label>
                    </div>
                    <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langUserDuration:</label>
                        <div class='col-sm-10'>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_all_users' name='specific_attendance_users' value='0' checked>
                                <span id='button_all_users_text'>$langAll</span>
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_some_users' name='specific_attendance_users' value='1'>
                                <span id='button_some_users_text'>$langSpecificUsers</span>
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_groups' name='specific_attendance_users' value='2'>
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
                        <div class='col-xs-10 col-xs-offset-2'>".form_buttons(array(
                        array(
                            'text' => $langRefreshList,
                            'name' => 'resetAttendanceUsers',
                            'value'=> $langAttendanceUpdate,
                            'javascript' => "selectAll('participants_box',true)"
                        ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1"
                        )
                    ))."</div>
                    </div>
                </form>
            </div>
        </div>
    </div>";

}

/**
 * @brief display user presences (student view)
 * @global type $tool_content
 * @global type $uid
 * @global type $langAttendanceStudentFailure
 * @global type $langGradebookTotalGrade
 * @global type $langTitle
 * @global type $langAttendanceActivityDate2
 * @global type $langDescription
 * @global type $langAttendanceAbsencesYes
 * @global type $langAttendanceAbsencesNo
 * @global type $langBack
 * @global type $course_code
 * @param type $attendance_id
 */
function student_view_attendance($attendance_id) {

    global $tool_content, $uid, $langAttendanceAbsencesNo, $langAttendanceAbsencesFrom,
           $langAttendanceAbsencesFrom2, $langAttendanceStudentFailure,
           $langTitle, $langAttendanceActivityDate2, $langDescription,
           $langAttendanceAbsencesYes, $langBack, $course_code;

    $attendance_limit = get_attendance_limit($attendance_id);
    //check if there are attendance records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) AS count
                                            FROM attendance_book, attendance_activities
                                        WHERE attendance_book.attendance_activity_id = attendance_activities.id
                                            AND uid = ?d
                                            AND attendance_activities.attendance_id = ?d", $uid, $attendance_id)->count;
    $tool_content .= action_bar(array(
        array(  'title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'),
    ));
    if (!$checkForRecords) {
        $tool_content .="<div class='alert alert-warning'>$langAttendanceStudentFailure</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d ORDER BY `DATE` DESC", $attendance_id);
    $results = count($result);

    if ($results > 0) {
        if ($checkForRecords) {
            $range = Database::get()->querySingle("SELECT `limit` FROM attendance WHERE id = ?d", $attendance_id)->limit;
            $tool_content .= "<div class='alert alert-info'>" . userAttendTotal($attendance_id, $uid) ." ". $langAttendanceAbsencesFrom . " ". q($attendance_limit) . " " . $langAttendanceAbsencesFrom2. " </div>";
        }

        $tool_content .= "<table class='table-default' >";
        $tool_content .= "<tr><th>$langTitle</th>
                              <th>$langAttendanceActivityDate2</th>
                              <th>$langDescription</th>
                              <th>$langAttendanceAbsencesYes</th>
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
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>";
            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT attend FROM attendance_book
                                                            WHERE attendance_activity_id = ?d
                                                                AND uid = ?d", $details->id, $uid);
            if ($sql) {
                $attend = $sql->attend;
                if ($attend) {
                    $tool_content .= icon('fa-check-circle', $langAttendanceAbsencesYes);
                } else {
                    $auto_activity = Database::get()->querySingle("SELECT auto FROM attendance_activities WHERE id = ?d", $details->id)->auto;
                    if (!$auto_activity and ($details->date > date("Y-m-d"))) {
                        $tool_content .= icon('fa-question-circle', $langAttendanceStudentFailure);
                    } else {
                        $tool_content .= icon('fa-times-circle', $langAttendanceAbsencesNo);
                    }
                }
            } else {
                $tool_content .= icon('fa-question-circle', $langAttendanceStudentFailure);
            }
            $tool_content .= "</td></tr>";
        } // end of while
    }
    $tool_content .= "</table>";
}

/**
 * @brief Function to get the total attend number for a user in a course attendance
 * @param type $attendance_id
 * @param type $userID
 * @return int
 */
function userAttendTotal ($attendance_id, $userID){

    $userAttendTotal = Database::get()->querySingle("SELECT SUM(attend) as count FROM attendance_book, attendance_activities
                                            WHERE attendance_book.uid = ?d
                                            AND attendance_book.attendance_activity_id = attendance_activities.id
                                            AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;

    if($userAttendTotal){
        return $userAttendTotal;
    } else {
        return 0;
    }
}

/**
 * @brief Function to get the total attend number for a user in a course attendance
 * @param type $activityID
 * @param type $participantsNumber
 * @return string
 */
function userAttendTotalActivityStats ($activityID, $participantsNumber, $attendance_id){

    $sumAtt = 0;
    $userAttTotalActivity = Database::get()->queryArray("SELECT attend, attendance_book.uid FROM attendance_book, attendance_users
                                                            WHERE attendance_activity_id = ?d
                                                        AND attendance_users.uid=attendance_book.uid
                                                        AND attendance_users.attendance_id=?d", $activityID, $attendance_id);
    foreach ($userAttTotalActivity as $module) {
        $sumAtt += $module->attend;
    }
    //check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round(100 * $sumAtt / $participantsNumber, 2);
        return $sumAtt."/". $participantsNumber . " (" . $mean . "%)";
    } else {
        return "-";
    }

}


/**
 * @brief check for attend in auto activities
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @return int
 */
function attendForAutoActivities($userID, $exeID, $exeType) {

    if ($exeType == 1) { //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(id) AS count FROM assignment_submit
                                    WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->count;
       if ($autoAttend) {
           return 1;
       } else {
           return 0;
       }
    }
    if ($exeType == 2) { //exercises: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(eurid) AS count FROM exercise_user_record
                                            WHERE uid = ?d AND eid = ?d
                                            AND total_score > 0 AND attempt_status != ".ATTEMPT_PAUSED."", $userID, $exeID)->count;
        if ($autoAttend) {
            return 1;
        }else{
            return 0;
        }
    }
}


/**
 * @brief insert user presence
 * @global string $tool_content
 * @global type $langGradebookEdit
 * @param type $attendance_id
 * @param type $actID
 */
function insert_presence($attendance_id, $actID) {

    global $tool_content, $langGradebookEdit, $course_code;

    if (isset($_POST['userspresence'])) {

        $to_be_inserted = array_keys($_POST['userspresence']);
        $already_inserted = [];
        Database::get()->queryFunc("SELECT uid FROM attendance_book
                                        WHERE attendance_activity_id = ?d",
        function($attendance_book) use (&$already_inserted){
            array_push($already_inserted, $attendance_book->uid);
        },$actID);

        $to_be_deleted = array_diff($already_inserted, $to_be_inserted);
        foreach ($to_be_deleted as $row) {
            Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $row);
        }
        foreach ($to_be_inserted as $row) {
            // check if there is record for the user for this activity
            $checkForBook = Database::get()->querySingle("SELECT COUNT(id) AS count, id FROM attendance_book
                                        WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $row);
            if (!$checkForBook->count) {
                Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ?s", $row, $actID, 1, '');
            }
        }
    } else {
        Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id = ?d", $actID);
    }
    Session::Messages($langGradebookEdit, 'alert-success');
    redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id&ins=".  getIndirectReference($actID));
}


/**
 * @brief update presence from modules for given activity
 * @param type $attendance_id
 * @param type $actID
 */
function update_presence($attendance_id, $actID) {

    $sql = Database::get()->querySingle("SELECT module_auto_type, module_auto_id
                            FROM attendance_activities WHERE id = ?d", $actID);
    if ($sql) {
        $activity_type = $sql->module_auto_type;
        $id = $sql->module_auto_id;
    }
    //get all the active users
    $q = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
    if ($q) {
        foreach ($q as $activeUsers) {
            update_attendance_book($activeUsers->uid, $id, $activity_type);
        }
    }
}

/**
 * @brief update attendance about user activities
 * @param type $id
 * @param type $activity
 * @return type
 */
function update_attendance_book($uid, $id, $activity, $attendance_id = 0) {
    $params = [$activity, $id];
    $sql = "SELECT attendance_activities.id, attendance_activities.attendance_id
                            FROM attendance_activities, attendance
                            WHERE attendance.start_date < NOW()
                            AND attendance.end_date > NOW()
                            AND attendance_activities.module_auto_type = ?d
                            AND attendance_activities.module_auto_id = ?d
                            AND attendance_activities.auto = 1
                            AND attendance_activities.attendance_id = attendance.id
                            AND attendance_activities.attendance_id ";
    if ($attendance_id) {
        $sql .= "= ?d";
        array_push($params, $attendance_id);
    } else {
        $sql .= "IN (
                    SELECT attendance_id
                    FROM attendance_users
                    WHERE uid = ?d)";
        array_push($params, $uid);
    }
    // This query gets the attendance activities that:
    // 1) belong to attendancebooks (or specific attendancebook if $attendance_id != 0)
    // withing the date constraints
    // 2) of a specifc module and have grade auto-submission enabled
    // 3) attended by a specifc user
    $attendanceActivities = Database::get()->queryArray($sql, $params);

    foreach ($attendanceActivities as $attendanceActivity) {
            $attendance_book = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = $attendanceActivity->id AND uid = ?d", $uid);
            if(!$attendance_book) {
                Database::get()->query("INSERT INTO attendance_book SET attendance_activity_id = $attendanceActivity->id, uid = ?d, attend = 1, comments = ''", $uid);
            }

    }

    return;
}

/**
 * @brief delete attendance
 * @global type $course_id
 * @global type $langAttendanceDeleted
 * @param type $attendance_id
 */
function delete_certificate($attendance_id) {

    global $course_id, $langAttendanceDeleted;

    $r = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate_id = ?d", $certificate_id);
    foreach ($r as $act) {
        delete_certificate_activity($certificate_id, $act->id);
    }
    $action = Database::get()->query("DELETE FROM certificate WHERE id = ?d AND course_id = ?d", $certificate_id, $course_id);
    if ($action) {
        Session::Messages("Επιτυχής διαγραφή", "alert-success");
    }
}

/**
 * @brief delete attendance activity
 * @global type $langAttendanceDel
 * @global type $langAttendanceDelFailure
 * @param type $attendance_id
 * @param type $activity_id
 */
function delete_certificate_activity($certificate_id, $activity_id) {

    global $langAttendanceDel, $langAttendanceDelFailure;

    $delAct = Database::get()->query("DELETE FROM certificate_criterion WHERE id = ?d AND certificate = ?d", $activity_id, $certificate_id)->affectedRows;
    if($delAct) {
        Session::Messages("$langAttendanceDel", "alert-success");
    } else {
        Session::Messages("$langAttendanceDelFailure", "alert-danger");
    }
}


/**
 * @brief delete user from attendance
 * @global type $langGradebookEdit
 * @param type $attendance_id
 * @param type $userid
 */
function delete_attendance_user($attendance_id, $userid) {

    global $langGradebookEdit;

    Database::get()->query("DELETE FROM attendance_book WHERE uid = ?d AND attendance_activity_id IN
                                (SELECT id FROM attendance_activities WHERE attendance_id = ?d)", $userid, $attendance_id);
    Database::get()->query("DELETE FROM attendance_users WHERE uid = ?d AND attendance_id = ?d", $userid, $attendance_id);
    Session::Messages($langGradebookEdit,"alert-success");
}


/**
 * @brief clone attendance
 * @global type $course_id
 * @param type $attendance_id*
 */
function clone_attendance($attendance_id) {

    global $course_id, $langCopyDuplicate;
    $attendance = Database::get()->querySingle("SELECT * FROM attendance WHERE id = ?d", $attendance_id);
    $newTitle = $attendance->title.' '.$langCopyDuplicate;
    $new_attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d,
                                                      students_semester = 1, `limit` = ?d,
                                                      active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $attendance->limit, $newTitle, $attendance->start_date, $attendance->end_date)->lastInsertID;
    Database::get()->query("INSERT INTO attendance_activities (attendance_id, title, date, description, module_auto_id, module_auto_type, auto)
                                SELECT $new_attendance_id, title, " . DBHelper::timeAfter() . ", description, module_auto_id, module_auto_type, auto
                                 FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
}

/**
 * @brief get attendance title
 * @param type $attendance_id
 * @return type
 */
function get_certificate_title($certificate_id) {

    $at_title = Database::get()->querySingle("SELECT title FROM certificate WHERE id = ?d", $certificate_id)->title;

    return $at_title;
}


/**
 * @brief get attendance limit
 * @param type $attendance_id
 * @return type
 */
function get_attendance_limit($attendance_id) {

    $at_limit = Database::get()->querySingle("SELECT `limit` FROM attendance WHERE id = ?d", $attendance_id)->limit;

    return $at_limit;

}
