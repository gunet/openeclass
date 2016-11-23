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
           $langActivate, $langAvailableAttendances, $langNoCertificates, $is_editor,
           $langViewHide, $langViewShow, $langEditChange, $langStart, $langEnd, $uid, $langAvailCert;

    if ($is_editor) {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM certificate WHERE course_id = ?d", $course_id);
    } else {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM certificate WHERE course_id = ?d AND active = 1", $course_id);
    }
    
    if (count($sql_cer) == 0) { // no certificates
        $tool_content .= "<div class='alert alert-info'>$langNoCertificates</div>";
    } else {
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>
                            <th>$langAvailCert</th>";
        if( $is_editor) {
            $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr>";
        foreach ($sql_cer as $data) {            
            $row_class = !$data->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id'>".q($data->title)."</a>
                        </td>";
            if($is_editor) {
                $tool_content .= "<td class='option-btn-cell'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;edit=1",
                                          'icon' => 'fa-cogs'),
                                    array('title' => $data->active ? $langViewHide : $langViewShow,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;vis=" .
                                                  ($data->active ? '0' : '1'),
                                          'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_cert_id=$data->id",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete)),
                                    array('title' => $langCreateDuplicate,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;dup=1",
                                          'icon' => 'fa-cogs')
                                        );
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div></div>";
    }
}


/**
 * @brief aisplay all certificate activities
 * @global type $tool_content
 * @global type $course_code
 * @global type $course_id
 * @global type $langNoActivCert
 * @global type $langAttendanceActList
 * @global type $langTitle
 * @global type $langType
 * @global type $langOfAssignment
 * @global type $langExerciseAsModuleLabel
 * @global type $langOfBlog
 * @global type $langDocumentAsModuleLabel
 * @global type $langMediaAsModuleLabel
 * @global type $langOfMultimediaLinks
 * @global type $langOfEBook
 * @global type $langOfPoll
 * @global type $langWiki
 * @global type $langOfForums
 * @global type $langOfBlogComments
 * @global type $langOfCourseComments
 * @global type $langOfLikesForum
 * @global type $langOfLikesSocial
 * @global type $langOfLearningPath
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $langConfirmDelete
 * @global type $langConfig
 * @global type $langInsertWorkCap
 * @global type $langLearningPath
 * @global type $langAdd
 * @global type $langExport
 * @global type $langBack
 * @global type $langInsertWorkCap
 * @global type $langCommentsBlog
 * @global type $langCommentsCourse
 * @global type $langWikis
 * @global type $langCategoryExcercise
 * @global type $langValue
 * @global type $langsetvideo
 * @global type $langEBook
 * @global type $langMetaQuestionnaire
 * @global type $langBlog
 * @global type $langBlogPosts
 * @global type $langPersoValue
 * @global type $langCourseSocialBookmarks
 * @global type $langPersoValue
 * @global type $langForumRating
 * @global type $langPersoValue
 * @global type $langWikiPages
 * @global type $langCategoryEssay
 * @global type $langDocument
 * @global type $langAllActivities
 * @param type $certificate_id
 */
function display_certificate_activities($certificate_id) {

    global $tool_content, $course_code, $course_id, 
           $langNoActivCert, $langAttendanceActList, $langTitle, $langType,
           $langOfAssignment, $langExerciseAsModuleLabel, $langOfBlog, $langDocumentAsModuleLabel,
           $langMediaAsModuleLabel, $langOfMultimediaLinks, $langOfEBook, $langOfPoll, $langWiki,
           $langOfForums, $langOfBlogComments, $langOfCourseComments, $langOfLikesForum, $langOfLikesSocial,
           $langOfLearningPath, $langDelete, $langEditChange, $langConfirmDelete,           
           $langConfig, $langInsertWorkCap, $langLearningPath,
           $langAdd, $langExport, $langBack, $langInsertWorkCap, 
           $langCommentsBlog, $langCommentsCourse, $langWikis, $langCategoryExcercise,           
           $langValue, $langsetvideo, $langEBook, $langMetaQuestionnaire, $langBlog, 
           $langBlogPosts, $langPersoValue, $langCourseSocialBookmarks,
           $langPersoValue, $langForumRating, $langPersoValue, $langWikiPages, 
           $langCategoryEssay, $langDocument, $langAllActivities;

    $tool_content .= action_bar(
            array(
                array('title' => $langAdd,
                      'level' => 'primary-label',
                      'options' => array(
                          array('title' => "$langOfAssignment",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=assignment",
                                'icon' => 'fa fa-flask space-after-icon',
                                'class' => ''),
                          array('title' => "$langExerciseAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=exercise",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfBlog",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=blog",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfBlogComments",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=blogcomments",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfCourseComments",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=coursecomments",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfForums",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=forum",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfLearningPath",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=lp",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfLikesSocial",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=likesocial",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfLikesForum",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=likeforum",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langDocumentAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=document",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langMediaAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=video",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfMultimediaLinks",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=videolinks",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfEBook",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=ebook",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfPoll",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=poll",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langWiki",
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;add=true&amp;act=wiki",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => '')),
                     'icon' => 'fa-plus'),
                array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'),
                array('title' => $langConfig,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id&amp;edit=1",
                      'icon' => 'fa-cog'),
                array('title' => "$langExport",
                        'url' => "dumpcertificatebook.php?course=$course_code&amp;certificate_id=$certificate_id&amp;enc=1253",
                    'icon' => 'fa-file-excel-o'),                
            ),
            true
        );

    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM certificate_criterion WHERE certificate = ?d ORDER BY `id` DESC", $certificate_id);

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
            if ($details->activity_type == "exercise") {
                $checkForExer = Database::get()->queryArray("SELECT title FROM exercise WHERE exercise.course_id = ?d AND exercise.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->title;
                }
                $type = "$langCategoryExcercise";
                if ($details->resource == "") {
                    $title = "$langAllActivities";
                }
            }
            if ($details->activity_type == "assignment") {
                $checkForExer = Database::get()->queryArray("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                        $title = $newExerToCertificate->title;
                }
                $type = "$langCategoryEssay";
                if ($details->resource == "") {
                    $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == LearningPathEvent::ACTIVITY) {
                $checkForExer = Database::get()->queryArray("SELECT name FROM lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                        $title = $newExerToCertificate->name;
                }
                $type = "$langLearningPath";
                if ($details->resource == "") {
                    $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == ViewingEvent::DOCUMENT_ACTIVITY) {
                $checkForExer = Database::get()->queryArray("SELECT title FROM document WHERE document.course_id = ?d AND document.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->title;
                }
                $type = "$langDocument";
                if ($details->resource == "") {
                    $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == ViewingEvent::VIDEO_ACTIVITY){
                $checkForExer = Database::get()->queryArray("SELECT title FROM video WHERE video.course_id = ?d AND video.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->title;
                }
                $type = "$langVideo";
                if ($details->resource == "") {
                  $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == ViewingEvent::VIDEOLINK_ACTIVITY){
                $checkForExer = Database::get()->queryArray("SELECT title FROM videolink WHERE videolink.course_id = ?d AND videolink.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->title;
                }
                $type = "$langsetvideo";
                if($details->resource == ""){
                  $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == ViewingEvent::EBOOK_ACTIVITY){
                $checkForExer = Database::get()->queryArray("SELECT title FROM ebook WHERE ebook.course_id = ?d AND ebook.id = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->title;
                }
                $type = "$langEBook";
                if ($details->resource == "") {
                  $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == ViewingEvent::QUESTIONNAIRE_ACTIVITY){
                $checkForExer = Database::get()->queryArray("SELECT name FROM poll WHERE poll.course_id = ?d AND poll.pid = ?d", $course_id, $details->resource);
                foreach ($checkForExer as $newExerToCertificate) {
                    $title = $newExerToCertificate->name;
                }
                $type = "$langMetaQuestionnaire";
                if ($details->resource == "") {
                  $title = "$langAllActivities";
                }
            }

            if ($details->activity_type == BlogEvent::ACTIVITY) {
                $type = "$langBlog";
                $title = "$langBlogPosts";
            }

            if ($details->activity_type == CommentEvent::BLOG_ACTIVITY && $details->module == MODULE_ID_COMMENTS) {
                $type = "$langComments";
                $title = "$langCommentsBlog";
            }

            if ($details->activity_type == CommentEvent::COURSE_ACTIVITY && $details->module == MODULE_ID_COMMENTS) {
                $type = "$langComments";
                $title = "$langCommentsCourse";
            }

            if ($details->activity_type == ForumEvent::ACTIVITY) {
                $type = "$langForums";
                $title = "$langComments $langForums";
            }

            if ($details->activity_type == RatingEvent::SOCIALBOOKMARK_ACTIVITY && $details->module == MODULE_ID_RATING) {
                $type = "$langPersoValue $langCourseSocialBookmarks";
                $title = "$langPersoValue";
            }

            if ($details->activity_type == RatingEvent::FORUM_ACTIVITY && $details->module == MODULE_ID_RATING) {
                $type = "$langForumRating";
                $title = "$langPersoValue";
            }
           
            if ($details->activity_type == WikiEvent::ACTIVITY) {
                $type = "$langWikiPages";
                $title = "$langWikis";
            }

            //$content = ellipsize_html($details->description, 50);
            $tool_content .= "<tr><td>";
            $tool_content .= $title;
            $tool_content .= "</td><td>".$type."</td><td>";

            if ($details->operator=='eq') $tool_content .=" = ";
            if ($details->operator=='lt') $tool_content .=" < ";
            if ($details->operator=='gt') $tool_content .=" > ";
            if ($details->operator=='let') $tool_content .=" <= ";
            if ($details->operator=='get') $tool_content .=" >= ";
            if ($details->operator=='neq') $tool_content .=" != ";

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


/**
 * @brief choose activity for inserting in certificate
 * @param type $certificate_id
 * @param type $activity
 */
function insert_activity($certificate_id, $activity) {
                            
    switch ($activity) {
        case 'assignment':        
            display_available_assignments($certificate_id);
            break;
        case 'exercise':
            display_available_exercises($certificate_id);
            break;
        case 'blog';
            display_available_blogs($certificate_id);
            break;
        case 'blogcomments':
            certificate_display_available_Com($certificate_id);
            break;
        case 'coursecomments':
            certificate_display_available_Com($certificate_id);
            break;
        case 'forum':
            certificate_display_available_For($certificate_id);
            break;
        case 'lp':
            display_available_lps($certificate_id);
            break;
        case 'likesocial';
            break;
        case 'likeforum';
            break;
        case 'document':
            display_available_documents($certificate_id);
            break;
        case 'video':
            certificate_display_available_Vid($certificate_id);
            break;
        case 'videolinks':
            certificate_display_available_Mul($certificate_id);
            break;
        case 'ebook':
            display_available_ebooks($certificate_id);
            break;
        case 'poll':
            display_available_polls($certificate_id);
            break;
        case 'wiki':
            certificate_display_available_Wi($certificate_id);
            break;
        default: break;
        }        
}

/**
 * @brief assignments display form
 * @global type $course_id
 * @global type $tool_content
 * @global type $langNoAssign
 * @global type $course_code
 * @global type $langTitle
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langChoice
 * @global type $langActive
 * @global type $langInactive
 * @global type $langAddModulesButton
 * @param type $certificate_id
 */
function display_available_assignments($certificate_id) {

    global $course_id, $tool_content, $langNoAssign, $course_code, $langTitle, 
           $langGroupWorkDeadline_of_Submission, $langChoice, 
           $langActive, $langInactive, $langAddModulesButton;
               
            
    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d AND active = 1 AND id NOT IN 
                                (SELECT resource FROM certificate_criterion WHERE certificate = ?d 
                                    AND resource != ''
                                    AND activity_type = 'assignment' 
                                    AND module = 5) 
                                ORDER BY title", $course_id, $certificate_id);    
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='certificate_id' value='$certificate_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langTitle</th>" .
                "<th width='120'>$langGroupWorkDeadline_of_Submission</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($result as $row) {
            
            if ($row->active) {
                $visible = icon('fa-eye', $langActive);
            } else {
                $visible = icon('fa-eye-slash', $langInactive);
            }            
            $description = empty($row->description) ? '' :
                    "<div>$row->description</div>";            
            $tool_content .= "<tr>" .
                    "<td> " . q($row->title) . "<br><br><div class='text-muted'>$description</div></td>" .
                    "<td class='text-center'>".nice_format($row->submission_date, true)."</td>" .
                    "<td class='text-center'><input name='assignment[]' value='$row->id' type='checkbox' /></td>" .
                    "</tr>";            
        }
        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_assignment' value='$langAddModulesButton'></div></th></form>";
    }        
}


/**
 * @brief exercises display form
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langExercices
 * @global type $langNoExercises
 * @global type $langDescription
 * @global type $langChoice
 * @global type $langAddModulesButton
 * @param type $certificate_id
 */
function display_available_exercises($certificate_id) {

    global $course_id, $course_code, $tool_content, $urlServer, $langExercices,
            $langNoExercises, $langDescription, $langChoice, $langAddModulesButton;
    
    $result = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                    AND exercise.active = 1 AND exercise.id NOT IN 
                                    (SELECT resource FROM certificate_criterion WHERE certificate = ?d 
                                            AND resource != '' 
                                            AND activity_type = 'exercise' 
                                            AND module = 10) ORDER BY title", $course_id, $certificate_id);
    $quizinfo = array();
    foreach ($result as $row) {
        $quizinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active);
    }
    if (count($quizinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoExercises</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" . 
                "<input type='hidden' name='certificate_id' value='$certificate_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th width='50%' class='text-left'>$langExercices</th>" .
                "<th class='text-left'>$langDescription</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-left'><a href='${urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$entry[id]'>" . q($entry['name']) . "</a></td>";
            $tool_content .= "<td class='text-left'>" . $entry['comment'] . "</td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";            
        }
        $tool_content .= "</table><div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_exercise' value='$langAddModulesButton'></div>
                </form>";
    }        
}



function display_available_blogs($certificate_id) {

}
function certificate_display_available_Com($certificate_id) {

}
function certificate_display_available_For($certificate_id) {

}

/**
 * @brief learning paths display form
 * @global type $course_id
 * @global type $course_code
 * @global type $urlServer
 * @global type $langNoLearningPath
 * @global type $langLearningPaths
 * @global type $langComments
 * @global type $langChoice
 * @global type $langAddModulesButton
 * @param type $certificate_id
 */
function display_available_lps($certificate_id) {

    global $course_id, $course_code, $urlServer, $tool_content, 
           $langNoLearningPath, $langLearningPaths, $langComments, $langChoice, 
           $langAddModulesButton;
    
  $result = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                            AND lp_learnPath.visible = 1
                                            AND lp_learnPath.learnPath_id NOT IN 
                                        (SELECT resource FROM certificate_criterion WHERE certificate = ?d 
                                                    AND resource!='' 
                                                    AND activity_type = 'learning path' 
                                                    AND module = 23)", $course_id, $certificate_id);
    $lpinfo = array(); 
    foreach ($result as $row) {
        $lpinfo[] = array(
            'id' => $row->learnPath_id,
            'name' => $row->name,
            'comment' => $row->comment,
            'visible' => $row->visible,
            'rank' => $row->rank);
    }
    if (count($lpinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='certificate_id' value='$certificate_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th><div align='left'>&nbsp;$langLearningPaths</div></th>" .
                "<th><div align='left'>$langComments</div></th>" .
                "<th width='80'>$langChoice</th>" .
                "</tr>";        
        foreach ($lpinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }            
            $m_id = Database::get()->querySingle("SELECT module_id FROM lp_rel_learnPath_module WHERE learnPath_id = ?d 
                                                    AND rank = (SELECT MIN(rank) FROM lp_rel_learnPath_module WHERE learnPath_id = ?d)", 
                                                $entry['id'], $entry['id']);
            if (($m_id) and $m_id->module_id > 0) {
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td>&nbsp;".icon('fa-ellipsis-h')."&nbsp;&nbsp;<a href='${urlServer}modules/learnPath/viewer.php?course=$course_code&amp;path_id=$entry[id]&amp;module_id=$m_id->module_id'>" . q($entry['name']) . "</a></td>";
                $tool_content .= "<td>" . $entry['comment'] . "</td>";
                $tool_content .= "<td class='text-center'><input type='checkbox' name='lp[]' value='$entry[id]' $disabled></td>";
                $tool_content .= "</tr>";            
            }
        }
        $tool_content .= "</table>\n";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_lp' value='$langAddModulesButton'></div></form>";
        
    }      
}

function certificate_display_available_Rat($certificate_id){

}

function display_available_documents($certificate_id){

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

function display_available_ebooks($certificate_id){

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

function display_available_polls($certificate_id){

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

/**
 * @brief add / edit certificate settings
 * @global string $tool_content
 * @global type $course_code
 * @global type $course_id
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 * @global type $langActivate
 * @global type $langDescription
 * @global type $langpublisher
 * @global type $langMessage
 * @global type $langTemplate
 * @param type $certificate_id
 */
function certificate_settings($certificate_id = 0) {

    global $tool_content, $course_code, $langTemplate, $course_id, 
           $langTitle, $langSave, $langInsert, $langMessage,
           $langActivate, $langDescription, $langpublisher;
       
        
    if ($certificate_id > 0) {      // edit
        $data = Database::get()->querySingle("SELECT issuer, template, title, description, message, active, bundle 
                                FROM certificate WHERE id = ?d AND course_id = ?d", $certificate_id, $course_id);
        $issuer = $data->issuer;
        $template = $data->template;
        $title = $data->title;
        $description = $data->description;
        $message = $data->message;
        $active = $data->active;
        $checked = ($active) ? ' checked': '';
        $cert_id = "<input type='hidden' name='certificate_id' value='$certificate_id'>";
        $name = 'editCertificate';
    } else {        // add
        $issuer = q(get_config('institution'));
        $template = '';
        $title = '';
        $description = '';
        $message = '';
        $active = 1;
        $checked = 'checked';
        $cert_id = '';
        $name = 'newCertificate';
    }
    
    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langTitle</label>            
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$title'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='description' class='col-sm-2 control-label'>$langDescription: </label>
                        <div class='col-sm-10'>
                            " . rich_text_editor('description', 2, 60, $description) . "
                        </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langTemplate</label>
                    <div class='col-sm-10'>
                    " . selection(array('1' => 'template 1', 
                                        '2' => 'template 2', 
                                        '3' => 'template 3'), 'template', $template) . "
                    </div>
                </div>
                <div class='form-group'>
                    <label for='message' class='col-sm-2 control-label'>$langMessage</label>
                    <div class='col-sm-10'>                        
                        " . rich_text_editor('message', 4, 60, $message) . "                        
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langpublisher</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='issuer' value='$issuer'>
                    </div>
                </div>
                 <div class='form-group'>
                    <label for='activate' class='col-sm-2 control-label'>$langActivate</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='checkbox' name='active' value='$active' $checked></label>
                    </div>
                </div>
                $cert_id
                <div class='form-group'>
                    <div class='col-xs-12'>".form_buttons(array(
                        array(
                                'text' => $langSave,
                                'name' => $name,
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
