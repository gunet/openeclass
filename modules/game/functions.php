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


function display_certificates() {

    global $course_id, $tool_content, $course_code, $langEditChange,
           $langDelete, $langConfirmDelete, $langDeactivate, $langCreateDuplicate,
           $langActivate, $langAvailableAttendances, $langNoAttendances, $is_editor,
           $langViewHide, $langViewShow, $langEditChange, $langStart, $langEnd, $uid, $langAvailCert;

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
                            <th>$langAvailCert</th>
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



function display_certificate_activities($certificate_id) {

    global $tool_content, $course_code, $attendance,
           $langAttendanceActList, $langTitle, $langType, $langAttendanceActivityDate, $langAttendanceAbsences,
           $langGradebookNoTitle, $langExercise, $langAssignment,$langAttendanceInsAut, $langAttendanceInsMan,
           $langDelete, $langEditChange, $langConfirmDelete, $langAttendanceNoActMessage1, $langAttendanceActivity,
           $langHere, $langAttendanceNoActMessage3, $langToA, $langcsvenc1, $langcsvenc2,
           $langConfig, $langStudents, $langGradebookAddActivity, $langInsertWorkCap, $langInsertExerciseCap,
           $langAdd, $langExport, $langBack, $langNoRegStudent, $course_id, $langInsertWorkCap, $langInsertExerciseCap, $langBlog, $langCommentsBlog, $langCommentsCourse, $langForum, $langLP, $langLikesSocial, $langLikesforum, $langDoc, $langMult, $langVideoLink, $langEbook, $langAnsQuest, $langWikiPages, $langValue,
           $langVideo, $langsetvideo, $langEBook,$langMetaQuestionnaire, $langBlog, $langBlogPosts, $langComments, $langCommentsBlog, $langComments, $langCommentsCourse, $langForums, $langComments, $langForu, $langPersoValue, $langCourseSocialBookmarks, $langPersoValue, $langForumRating, $langPersoValue, $langWikiPages, $langWikis, $langCategoryExcercise, $langCategoryEssay, $langLearningPath, $langDocument, $langAllActivities;

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
                          array('title' => "$langBlog",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityBlog=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langCommentsBlog",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityCom=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langCommentsCourse",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityComCourse=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langForum",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityFor=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langLP",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityLp=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langLikesSocial",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityRat=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langLikesforum",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityRatPosts=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langDoc",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityDoc=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langMult",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityMul=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langVideoLink",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityVid=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langEbook",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityBook=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langAnsQuest",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addActivityQue=1",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langWikiPages",
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


    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM certificate_criterion WHERE certificate = ?d  ORDER BY `id` DESC", $certificate_id);

    if (count($result) > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                        <table class='table-default'>
                        <tr class='list-header'><th class='text-center' colspan='5'>$langAttendanceActList</th></tr>
                        <tr class='list-header'>
                            <th>$langTitle</th>
                            <th>$langType</th>
                            <th>$langValue</th>
                            <th class='text-center'><i class='fa fa-cogs'></i></th>
                        </tr>";
        foreach ($result as $details) {

        	if($details->activity_type == "exercise"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM exercise WHERE exercise.course_id = ?d AND exercise.id = ?d", $course_id, $details->resource);
        		foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
        		}
        		$type = "$langCategoryExcercise";
        	  if($details->resource == ""){
              $title = "$langAllActivities";
            }
          }

          if($details->activity_type == "assignment"){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $details->resource);
        		foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
        		}
        		$type = "$langCategoryEssay";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == LearningPathEvent::ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT name FROM  lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->name;
            }
        		$type = "$langLearningPath";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == ViewingEvent::DOCUMENT_ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM document WHERE document.course_id = ?d AND document.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "$langDocument";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == ViewingEvent::VIDEO_ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM video WHERE video.course_id = ?d AND video.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "$langVideo";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == ViewingEvent::VIDEOLINK_ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM videolink WHERE videolink.course_id = ?d AND videolink.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "$langsetvideo";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == ViewingEvent::EBOOK_ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT title FROM ebook WHERE ebook.course_id = ?d AND ebook.id = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->title;
            }
        		$type = "$langEBook";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == ViewingEvent::QUESTIONNAIRE_ACTIVITY){
        		$checkForExer = Database::get()->queryArray("SELECT name FROM poll WHERE poll.course_id = ?d AND poll.pid = ?d", $course_id, $details->resource);
            foreach ($checkForExer as $newExerToCertificate) {
        			$title = $newExerToCertificate->name;
            }
        		$type = "$langMetaQuestionnaire";
            if($details->resource == ""){
              $title = "$langAllActivities";
            }
        	}

          if($details->activity_type == BlogEvent::ACTIVITY){
        		$type = "$langBlog";
            $title = "$langBlogPosts";
        	}

          if($details->activity_type == CommentEvent::BLOG_ACTIVITY && $details->module == MODULE_ID_COMMENTS){
        		$type = "$langComments";
            $title = "$langCommentsBlog";
        	}

          if($details->activity_type == CommentEvent::COURSE_ACTIVITY && $details->module == MODULE_ID_COMMENTS){
        		$type = "$langComments";
            $title = "$langCommentsCourse";
        	}

          if($details->activity_type == ForumEvent::ACTIVITY){
        		$type = "$langForums";
            $title = "$langComments $langForums";
        	}

          if($details->activity_type == RatingEvent::SOCIALBOOKMARK_ACTIVITY && $details->module == MODULE_ID_RATING){
        		$type = "$langPersoValue $langCourseSocialBookmarks";
            $title = "$langPersoValue";
        	}

          if($details->activity_type == RatingEvent::FORUM_ACTIVITY && $details->module == MODULE_ID_RATING){
        		$type = "$langForumRating";
            $title = "$langPersoValue";
        	}
          if($details->activity_type == WikiEvent::ACTIVITY){
        		$type = "$langWikiPages";
            $title = "$langWikis";
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
            if($details->operator=='neq') $tool_content .=" != ";


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
        $tool_content .= "<div class='alert alert-warning'>$langNoActivCert</div>";
    }
}


function certificate_display_available_exercises($certificate_id) {

    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                AND exercise.active = 1 AND exercise.id
                                NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'exercise' AND module = 10)", $course_id, $certificate_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";

        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langJQCheckAll</th>";
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
                    . "<td><div class='smaller'><span class='day'>" . nice_format($newExerToCertificate->start_date, true, true) . " </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;addCourseActivity=" . $newExerToCertificate->id . "&amp;type=1");
        }
        $tool_content .= "</td></tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
    }
}


function certificate_display_available_assignments($certificate_id) {

    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert;

    $checkForExer = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                AND assignment.active = 1
                                                AND assignment.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'assignment' AND module = 5)", $course_id, $certificate_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th>$langJQCheckAll</th>";
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
        $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
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
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert, $langJQCheckAll;

  $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                              AND lp_learnPath.visible = 1
                                              AND lp_learnPath.learnPath_id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'learning path' AND module = 23)", $course_id, $certificate_id);
  $checkForLpNumber = count($checkForLp);
  if ($checkForLpNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langJQCheckAll</th>";
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
      $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
  }
}

function certificate_display_available_Rat($certificate_id){

}

function certificate_display_available_Doc($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoDocuments;

  $checkForDoc = Database::get()->queryArray("SELECT * FROM document WHERE document.course_id = ?d
                                              AND document.visible = 1
                                              AND document.id NOT IN (SELECT resource FROM certificate_criterion WHERE certificate = ?d AND resource!='' AND activity_type = 'document' AND module = 3)", $course_id, $certificate_id);
  $checkForDocNumber = count($checkForDoc);
  if ($checkForDocNumber > 0) {
      $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
      $tool_content .= "<table class='table-default'>";
      $tool_content .= "<tr class='list-header'><th>$langJQCheckAll</th>";
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
      $tool_content .= "<div class='alert alert-warning'>$langNoDocuments</div>";
  }
}

function certificate_display_available_Mul($certificate_id){
  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert;

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
      $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
  }
}


function certificate_display_available_Vid($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert;

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
      $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
  }
}

function certificate_display_available_Book($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert;

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
      $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
  }
}

function certificate_display_available_Que($certificate_id){

  global $course_id, $course_code, $tool_content,
         $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $langNoInsCert;

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
      $tool_content .= "<div class='alert alert-warning'>$langNoInsCert</div>";
  }

}

function certificate_display_available_Wi($certificate_id){

}


function add_certificate_other_activity($certificate_id) {

    global $tool_content, $course_code, $langDescription,
           $langTitle, $langAttendanceInsAut, $langAdd,
           $langAdd, $langSave, $langAttendanceActivityDate, $lanfCertNoValMes;

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
                                    <option value='' ".((!isset($operator))?'selected="selected"':"")."> </option>
                                    <option value='eq' ".(($operator=='eq')?'selected="selected"':"").">=</option>
                                    <option value='lt' ".(($operator=='lt')?'selected="selected"':"")."><</option>
                                    <option value='gt' ".(($operator=='gt')?'selected="selected"':"").">></option>
                                    <option value='let' ".(($operator=='let')?'selected="selected"':"")."><=</option>
                                    <option value='get' ".(($operator=='get')?'selected="selected"':"").">>=</option>
                                    <option value='neq' ".(($operator=='neq')?'selected="selected"':"").">!=</option>

                                </select>
                            </div>
                        </div>
                        <div class='form-group".($date_error ? " has-error" : "")."'>
                            <label for='date' class='col-sm-2 control-label'>Τιμή</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='threshold' id='threshold' value='$threshold'/>
                                <span class='help-block'>$date_error</span>
                                <small>$lanfCertNoValMes</small>
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
           $langAdd, $langSave, $langAttendanceActivityDate, $langAutoJudgeOperator, $lanfCertNoValMes;

    $date_error = Session::getError('date');
    $tool_content .= "<div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id'>
                    <fieldset>";

                    $tool_content .= "
                        <div class='form-group'>
                            <label for='actTitle' class='col-sm-2 control-label'>$langAutoJudgeOperator</label>
                            <div class='col-sm-10'>
                                <select class='form-control' name='operator'>
                                    <option value=''> </option>
                                    <option value='eq'>=</option>
                                    <option value='lt' ><</option>
                                    <option value='gt' >></option>
                                    <option value='let'><=</option>
                                    <option value='get'>>=</option>
                                    <option value='neq'>!=</option>

                                </select>
                            </div>
                        </div>
                        <div class='form-group".($date_error ? " has-error" : "")."'>
                            <label for='date' class='col-sm-2 control-label'>Τιμή</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' name='threshold' id='threshold' value=''/>
                                <span class='help-block'>$date_error</span>
                                <small>$lanfCertNoValMes</small>
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


function new_certificate() {

    global $tool_content, $course_code, $langNewAttendance2, $head_content,
           $langTitle, $langSave, $langInsert, $langAttendanceLimitNumber,
           $attendance_limit, $langStart, $langEnd, $language, $langNewCertificate, $langNewCertificateAuto, $langActive;

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
    //$limit_error  = Session::getError('limit');
    //$limit = Session::has('limit') ? Session::get('limit') : '';

    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group'>
                    <label class='col-xs-12'>$langNewCertificate</label></div>
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
                        <label class='col-xs-12'>$langNewCertificateAuto <input class='' type='checkbox' name='autoassign' value='1'></label>
                    </div>
                    <div class='form-group'>
                        <label class='col-xs-12'>$langActive <input class='' type='checkbox' name='active' value='1'></label>
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



function certificate_settings($certificate_id) {

    global $tool_content, $course_code, $language,
           $langTitle, $langSave, $langAttendanceLimitNumber,
           $langAttendanceUpdate, $langSave, $head_content,
           $certificate, $langStart, $langEnd, $langNewCertificateAuto, $langActive;
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
                        <label class='col-xs-12'>$langNewCertificateAuto <input class='' type='checkbox' name='autoassign' value = 1 ";

                    if($autoassign == 1) {
                        $tool_content .= " checked ";
                    }

                    $tool_content .= "></label>
                    </div>
                    <div class='form-group'>
                        <label class='col-xs-12'>$langActive <input class='' type='checkbox' name='active' value = 1 ";

                    if($active == 1) {
                        $tool_content .= " checked ";
                    }

                    $tool_content .= "></label>
                    </div>

                    <div class='form-group'>
                        <div class='col-xs-12'>".form_buttons(array(
                            array(
                                'text' => $langSave,
                                'name' => 'submitCertificateSettings',
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


function delete_certificate($certificate_id) {

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

function delete_certificate_activity($certificate_id, $activity_id) {

    global $langAttendanceDel, $langAttendanceDelFailure;

    $delAct = Database::get()->query("DELETE FROM certificate_criterion WHERE id = ?d AND certificate = ?d", $activity_id, $certificate_id)->affectedRows;
    if($delAct) {
        Session::Messages("$langAttendanceDel", "alert-success");
    } else {
        Session::Messages("$langAttendanceDelFailure", "alert-danger");
    }
}


function get_certificate_title($certificate_id) {

    $at_title = Database::get()->querySingle("SELECT title FROM certificate WHERE id = ?d", $certificate_id)->title;

    return $at_title;
}
