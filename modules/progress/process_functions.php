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
 * @file process_functions.php
 * @param $element can be 'certificate' or 'badge'
 * @param $element_id can be 'certificate_id or 'badge_id'
 */


/**
 * @brief add assignment db entries in certificate criterion 
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_assignment_to_certificate($element, $element_id) {
            
    if (isset($_POST['assignment'])) {        
        foreach ($_POST['assignment'] as $datakey => $data) {
            Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                        module= " . MODULE_ID_ASSIGN . ", 
                                        resource = ?d, 
                                        activity_type = '" . AssignmentEvent::ACTIVITY . "', 
                                        operator = ?s, 
                                        threshold = ?f",
                                    $element_id, 
                                    $_POST['assignment'][$datakey], 
                                    $_POST['operator'][$data], 
                                    $_POST['threshold'][$data]);
        }
    }
    return;    
}


/**
 * @brief add exercise db entries in certificate criterion 
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_exercise_to_certificate($element, $element_id) {
        
    if (isset($_POST['exercise'])) {
        foreach ($_POST['exercise'] as $datakey => $data) {    
            Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                        module = " . MODULE_ID_EXERCISE . ", 
                                        resource = ?d, 
                                        activity_type = '" . ExerciseEvent::ACTIVITY . "', 
                                        operator = ?s, 
                                        threshold = ?f",
                                    $element_id, 
                                    $_POST['exercise'][$datakey],
                                    $_POST['operator'][$data],
                                    $_POST['threshold'][$data]);
        }
    }
    return;
}


/**
 * @brief add document db entries in certificate criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_document_to_certificate($element, $element_id) {
    
    if (isset($_POST['document'])) {
        foreach ($_POST['document'] as $data) {           
           Database::get()->query("INSERT INTO ${element}_criterion
                            SET $element = ?d, 
                                module= " . MODULE_ID_DOCS . ",
                                resource = ?d, 
                                activity_type = '" . ViewingEvent::DOCUMENT_ACTIVITY . "'",
                        $element_id, $data);              
            }
        }
    return;
}


/**
 * @brief add multimedia db entries in criterion
 * @param type $element
 * @param type $element_id
 */
function add_multimedia_to_certificate($element, $element_id) {
                
    if (isset($_POST['video'])) {
        foreach ($_POST['video'] as $data) {
            $d = explode(":", $data);
            Database::get()->query("INSERT INTO ${element}_criterion
                                SET $element = ?d, module= " . MODULE_ID_VIDEO . ", resource = ?d, activity_type = ?s",
                            $element_id, $d[1], $d[0])->lastInsertID;
        }
    }
    if (isset($_POST['videocatlink'])) {
        foreach ($_POST['videocatlink'] as $data) {
            $d = explode(":", $data);
            Database::get()->query("INSERT_INTO ${element}_criterion
                                SET $element = ?d, module = " . MODULE_ID_VIDEO . ", resource = ?d, activity_type = ?s",
                            $element_id, $d[1], $d[0])->lastInsertID;
        }
    }
    return;
}


/**
 * @brief add LP db entries in criterion
 * @param type $element
 * @param type $element_id
 */
function add_lp_to_certificate($element, $element_id) {
    
    if (isset($_POST['lp'])) {
        foreach ($_POST['lp'] as $datakey => $data) {
            Database::get()->query("INSERT INTO ${element}_criterion
                                SET $element = ?d, 
                                module = " . MODULE_ID_LP . ", 
                                resource = ?d,
                                activity_type = '" . LearningPathEvent::ACTIVITY . "',
                                operator = ?s, 
                                threshold = ?f",
                            $element_id, 
                            $_POST['lp'][$datakey],
                            $_POST['operator'][$data],
                            $_POST['threshold'][$data]);
        }
    }
    return;
}

/**
 * @brief add course participation db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_courseparticipation_to_certificate($element, $element_id) {
              
    if (isset($_POST['participation'])) {        
        Database::get()->query("INSERT INTO ${element}_criterion
                            SET $element = ?d, 
                            module = " . MODULE_ID_USAGE . ", 
                            resource = null, 
                            activity_type = '" . CourseParticipationEvent::ACTIVITY . "',
                            operator = ?s,
                            threshold = ?f",
                        $element_id,                         
                        $_POST['operator'],
                        $_POST['threshold']);
        
    }
    return;
}

/**
 * @brief add wiki db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_wiki_to_certificate($element, $element_id) {
              
    if (isset($_POST['wiki'])) {        
        Database::get()->query("INSERT INTO ${element}_criterion
                            SET $element = ?d, 
                            module = " . MODULE_ID_WIKI . ", 
                            resource = null, 
                            activity_type = '" . WikiEvent::ACTIVITY . "',
                            operator = ?s,
                            threshold = ?f",
                        $element_id,                         
                        $_POST['operator'],
                        $_POST['threshold']);
        
    }
    return;
}

/**
 * @brief add poll db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_poll_to_certificate($element, $element_id) {
    
    if (isset($_POST['poll'])) {
        foreach ($_POST['poll'] as $data) {            
          Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                    module= " . MODULE_ID_QUESTIONNAIRE . ", 
                                    resource = ?d, 
                                    activity_type = '" . ViewingEvent::QUESTIONNAIRE_ACTIVITY . "'",
                            $element_id,
                            $data);
        }
    }
    return;
}


/**
 * @brief add ebook db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_ebook_to_certificate($element, $element_id) {
     if (isset($_POST['ebook'])) {
        foreach ($_POST['ebook'] as $data) {     
          Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                    module= " . MODULE_ID_EBOOK . ", 
                                    resource = ?d, 
                                    activity_type = '" . ViewingEvent::EBOOK_ACTIVITY . "'",
                            $element_id,
                            $data);
        }
    }
    if (isset($_POST['section'])) {
        foreach ($_POST['section'] as $data) {     
          Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                    module= " . MODULE_ID_EBOOK . ", 
                                    resource = ?d, 
                                    activity_type = '" . ViewingEvent::EBOOK_ACTIVITY . "'",
                            $element_id,
                            $data);
        }
    }
    if (isset($_POST['subsection'])) {
        foreach ($_POST['subsection'] as $data) {     
          Database::get()->query("INSERT INTO ${element}_criterion
                                    SET $element = ?d, 
                                    module= " . MODULE_ID_EBOOK . ", 
                                    resource = ?d, 
                                    activity_type = '" . ViewingEvent::EBOOK_ACTIVITY . "'",
                            $element_id,
                            $data);
        }
    }
    
    return;
}



/**
 * @brief add forum db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_forum_to_certificate($element, $element_id) {
    
    if (isset($_POST[ForumEvent::ACTIVITY])) {        
        Database::get()->query("INSERT INTO ${element}_criterion
                            SET $element = ?d, 
                            module = " . MODULE_ID_FORUM . ", 
                            resource = null, 
                            activity_type = '" . ForumEvent::ACTIVITY . "',
                            operator = ?s,
                            threshold = ?f",
                        $element_id,
                        $_POST['operator'],
                        $_POST['threshold']);
    }
    return;
}


/**
 * @brief add forum topic db entries in criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_forumtopic_to_certificate($element, $element_id) {
    if (isset($_POST[ForumTopicEvent::ACTIVITY])) {
        foreach ($_POST[ForumTopicEvent::ACTIVITY] as $datakey => $data) {
            Database::get()->query("INSERT INTO ${element}_criterion
                                SET $element = ?d, 
                                module = " . MODULE_ID_FORUM . ", 
                                resource = ?d, 
                                activity_type = '" . ForumTopicEvent::ACTIVITY . "',
                                operator = ?s,
                                threshold = ?f",
                            $element_id, 
                            $_POST['forumtopic'][$datakey],
                            $_POST['operator'][$data],
                            $_POST['threshold'][$data]);
        }        
    }
    return;
}

/**
 * @brief add blog db entries in criterion
 * @param type $element
 * @param type $element_id
 */
function add_blog_to_certificate($element, $element_id) {
    
    if (isset($_POST[BlogEvent::ACTIVITY])) {        
        Database::get()->query("INSERT INTO ${element}_criterion
                            SET $element = ?d, 
                            module = " . MODULE_ID_BLOG . ", 
                            resource = null, 
                            activity_type = '" . BlogEvent::ACTIVITY . "',
                            operator = ?s,
                            threshold = ?f",
                        $element_id,                         
                        $_POST['operator'],
                        $_POST['threshold']);        
    }
}

/**
 * @brief add blog comment db entries in criterion
 * @param type $element
 * @param type $element_id
 */
function add_blogcomment_to_certificate($element, $element_id) {
    
    if (isset($_POST['blogcomment'])) {
        foreach ($_POST['blogcomment'] as $datakey => $data) {
            Database::get()->query("INSERT INTO ${element}_criterion
                                SET $element = ?d, 
                                module = " . MODULE_ID_COMMENTS . ", 
                                resource = ?d, 
                                activity_type = '" . CommentEvent::BLOG_ACTIVITY . "',
                                operator = ?s,
                                threshold = ?f",
                            $element_id, 
                            $_POST['blogcomment'][$datakey],
                            $_POST['operator'][$data],
                            $_POST['threshold'][$data]);
        }
    }
}


/**
 * @brief get certificate title
 * @param type $table
 * @param type $id
 * @return type
 */
function get_cert_title($table, $id) {

    $cert_title = Database::get()->querySingle("SELECT title FROM $table WHERE id = ?d", $id)->title;

    return $cert_title;
}

/**
 * @brief get certificate description
 * @param type $element
 * @param type $id
 * @return type
 */
function get_cert_desc($element, $id) {
    
    $cert_desc = Database::get()->querySingle("SELECT description FROM $element WHERE id = ?d", $id)->description;

    return $cert_desc;
}

/**
 * @brief get certificate expiration date
 * @param type $element
 * @param type $id
 * @return type
 */
function get_cert_expiration_day($element, $id) {
    $cert_expires = Database::get()->querySingle("SELECT expires FROM $element WHERE id = ?d", $id)->expires;
    
    return $cert_expires;
}


/**
 * @brief get certificate issuer
 * @param type $certificate_id
 * @return type
 */
function get_cert_issuer($certificate_id) {

    $cert_issuer =  Database::get()->querySingle("SELECT issuer FROM certificate WHERE id = ?d", $certificate_id)->issuer;
    
    return $cert_issuer;
}

/**
 * @brief get certificate template filename
 * @param type $certificate_id
 */
function get_certificate_template($certificate_id) {
    
    $r = Database::get()->querySingle("SELECT name, filename FROM certificate_template WHERE id = ?d", $certificate_id);   
    
    return [$r->name => $r->filename];
}

/**
 * @brief get available certificate templates
 * @return type
 */
function get_certificate_templates() {
    
    $templates = array();
    
    $t = Database::get()->queryArray("SELECT id, name FROM certificate_template");
    foreach ($t as $data) {
        $templates[$data->id] = $data->name;
    }
    return $templates;
}

/**
 * @brief get available badge icon
 * @param type $badge_id
 * @return type
 */
function get_badge_icon($badge_id) {
    
    $r = Database::get()->querySingle("SELECT name, filename FROM badge_icon WHERE id = ?d", $badge_id);
   
    return [$r->name => $r->filename];    
    
}

/**
 * @brief get available badge icons
 * @return string
 */
function get_badge_icons() {
    
    $badges = array();
    
    $b = Database::get()->queryArray("SELECT id, name FROM badge_icon");
    foreach ($b as $data) {
        $badges[$data->id] = $data->name;
    }
    return $badges;
}

/**
 * @brief check if we are trying to access other user details
 * @param type $uid
 */
function check_user_details($uid) {
    
    if (isset($_REQUEST['u'])) {
        if ($uid != $_REQUEST['u']) {
            redirect_to_home_page();
        }
    }
}

/**
 * @brief check if certificate / badge is active
 * @param type $element
 * @param type $element_id
 * @return boolean
 */
function is_cert_visible($element, $element_id) {
        
    $sql = Database::get()->querySingle("SELECT active FROM $element WHERE id = ?d", $element_id);
    if ($sql) {
        if (!$sql->active) {
            return false;
        }
    } else {
        return false;
    }
    return true;
}

/**
 * @brief check if user has completed certificate / badge
 * @param type $uid
 * @param type $element
 * @param type $element_id
 * @return boolean
 */
function has_certificate_completed($uid, $element, $element_id) {
        
    $sql = Database::get()->querySingle("SELECT completed FROM user_${element} WHERE $element = ?d AND user = ?d", $element_id, $uid);    
    if ($sql) {
        if (!$sql->completed) {
            return false;
        }
    } else {
        return false;
    }
    return true;
}


/**
 * @brief add certificate in DB
 * @global type $course_id
 * @param type $table
 * @param type $title
 * @param type $description
 * @param type $message
 * @param type $icon
 * @param type $issuer
 * @param type $active
 * @param type $bundle
 * @param type $expiration_day
 * @return type
 */
function add_certificate($table, $title, $description, $message, $icon, $issuer, $active, $bundle, $expiration_day) {
    
    global $course_id;
    if ($table == 'certificate') {
        $new_id = Database::get()->query("INSERT INTO certificate 
                                SET course_id = ?d,
                                title = ?s,
                                description = ?s,
                                message = ?s,
                                template = ?d,
                                issuer = ?s,
                                active = ?d,
                                bundle = ?d,
                                expires = ?t", $course_id, $title, $description, $message, $icon, $issuer, $active, $bundle, $expiration_day)->lastInsertID;
    } else {
        $new_id = Database::get()->query("INSERT INTO badge 
                                SET course_id = ?d,
                                title = ?s,
                                description = ?s,
                                message = ?s,
                                icon = ?d,
                                issuer = ?s,
                                active = ?d,
                                bundle = ?d,
                                expires = ?t", $course_id, $title, $description, $message, $icon, $issuer, $active, $bundle, $expiration_day)->lastInsertID;
    }
    return $new_id;
}

/**
 * @brief modify settings in DB
 * @global type $course_id
 * @param type $element_id
 * @param type $element
 * @param type $title
 * @param type $description
 * @param type $message
 * @param type $template
 * @param type $issuer
 * @param type $active
 */
function modify($element, $element_id, $title, $description, $message, $value, $issuer) {    
    
    global $course_id;
    $field = ($element == 'certificate')? 'template' : 'icon';
    Database::get()->query("UPDATE $element SET title = ?s, 
                                                   description = ?s,
                                                   message = ?s,
                                                   $field = ?d,
                                                   issuer = ?s                                                   
                                                WHERE id = ?d AND course_id = ?d",
                                    $title, $description, $message, $value, $issuer, $element_id, $course_id);
    
}

/**
 * @brief modify certificate resource activity
 * @param type $element_id
 * @param type $element
 */
function modify_certificate_activity($element, $element_id, $activity_id) {

    Database::get()->query("UPDATE ${element}_criterion 
                                SET threshold = ?f, 
                                    operator = ?s 
                                WHERE id = ?d 
                                AND $element = ?d",
                            $_POST['cert_threshold'], $_POST['cert_operator'], $activity_id, $element_id);
}


/**
 * @brief check if certificate / badge has activities
 * @param type $element
 * @param type $element_id
 * @return type
 */
function has_activity($element, $element_id) {
    
    $num_of_activities = Database::get()->querySingle("SELECT COUNT(*) AS act FROM ${element}_criterion WHERE $element = ?d", $element_id)->act;
    
    return $num_of_activities;    
}


/**
 * @brief modify certificate / badge visibility in DB
 * @global type $course_id
 * @param type $element_id
 * @param type $element
 * @param type $visibility 
 */
function update_visibility($element, $element_id, $visibility) {
    
    global $course_id;
    
    Database::get()->query("UPDATE $element SET active = ?d WHERE id = ?d AND course_id = ?d", $visibility, $element_id, $course_id);
    
}

/**
 * @brief check if we have created course completion badge
 * @global type $course_id
 * @return boolean
 */
function has_course_completion() {
    
    global $course_id;
    
    $sql = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND bundle = -1", $course_id);
    if ($sql) {
        return $sql->id;
    } else {
        return 0;
    }
}

/**
 * @brief get certificate / badge percentage completion
 * @global type $uid
 * @param type $element
 * @param type $element_id
 * @return type
 */
function get_cert_percentage_completion($element, $element_id) {
    
    global $uid;
    
    $data = Database::get()->querySingle("SELECT completed_criteria, total_criteria "
            . "FROM user_{$element} WHERE user = ?d AND $element = ?d", $uid, $element_id);
    
    return round($data->completed_criteria / $data->total_criteria * 100, 2);
}

/**
 * @brief delete certificate / badge db entries
 * @global type $course_id
 * @param type $certificate_id
 * @param type $element
 */
function delete_certificate($element, $element_id) {

    global $course_id;
    if ($element == 'certificate') {
        $r = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate = ?d", $element_id);
        foreach ($r as $act) { // delete certificate activities
            if (!resource_usage($element, $act->id)) { // check if activity has been used by user
                delete_activity('certificate', $element_id, $act->id);
            } else {
                return false;
            }
        }
        Database::get()->query("DELETE FROM certificate WHERE id = ?d AND course_id = ?d", $element_id, $course_id);        
    } else {
        $r = Database::get()->queryArray("SELECT id FROM badge_criterion WHERE badge = ?d", $element_id);
        foreach ($r as $act) { // delete badge activities
            if (!resource_usage($element, $act->id)) { // check if activity has been used by user
                delete_activity('badge', $element_id, $act->id);
            } else {
                return false;
            }            
        }
        Database::get()->query("DELETE FROM badge WHERE id = ?d AND course_id = ?d", $element_id, $course_id);        
    }
    
    return true;
}


/**
 * @brief delete certificate / badge activity
 * @param type $element
 * @param type $element_id
 * @param type $activity_id
 */
function delete_activity($element, $element_id, $activity_id) {
    
    $query = ($element == 'certificate')? 
            "DELETE FROM certificate_criterion WHERE id = ?d AND certificate = ?d" :
            "DELETE FROM badge_criterion WHERE id = ?d AND badge = ?d";
    Database::get()->query($query, $activity_id, $element_id);
    
}

/**
 * @brief checks if user has used a specified certificate / badge resource
 * @param type $element
 * @param type $element_resource_id
 * @return boolean
 */
function resource_usage($element, $element_resource_id) {
    
    $query = ($element == 'certificate')? 
            "SELECT user FROM user_certificate_criterion WHERE certificate_criterion = ?d" : 
            "SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d";
    $sql = Database::get()->querySingle($query, $element_resource_id);
    if ($sql) {
        return true;
    } else {
        return false;
    }    
}


/**
 * @brief get resource details given a certificate resource
 * @global type $course_id
 * @global type $langCategoryExcercise
 * @global type $langCategoryEssay
 * @global type $langLearningPath
 * @global type $langDocument
 * @global type $langVideo
 * @global type $langsetvideo
 * @global type $langEBook
 * @global type $langMetaQuestionnaire 
 * @global type $langBlog
 * @global type $langNumOfBlogs
 * @global type $langNumOfForums
 * @global type $langForums
 * @global type $langWikiPages
 * @global type $langWiki
 * @global type $langComments
 * @global type $langCommentsBlog
 * @global type $langCommentsCourse
 * @global type $langPersoValue
 * @global type $langCourseSocialBookmarks
 * @global type $langForumRating
 * @global type $langAllActivities
 * @global type $langCourseParticipation
 * @global type $langCourseHoursParticipation
 * @param type $resource_id
 * @return type
 */
function get_resource_details($element, $resource_id) {
    
    global $course_id, $langCategoryExcercise, $langCategoryEssay, $langLearningPath, $langNumOfForums,
            $langDocument, $langVideo, $langsetvideo, $langEBook, $langMetaQuestionnaire, 
            $langBlog, $langForums, $langWikiPages, $langNumOfBlogs, $langCourseParticipation,
            $langWiki, $langAllActivities, $langComments, $langCommentsBlog, $langCommentsCourse,
            $langPersoValue, $langCourseSocialBookmarks, $langForumRating, $langCourseHoursParticipation;
    
    $data = array('type' => '', 'title' => '');
    
    $res_data = Database::get()->querySingle("SELECT activity_type, module, resource FROM ${element}_criterion WHERE id = ?d", $resource_id);
    $resource = $res_data->resource;
    $resource_type = $res_data->activity_type;
    
    switch ($resource_type) {
        case ExerciseEvent::ACTIVITY: 
                $title = Database::get()->querySingle("SELECT title FROM exercise WHERE exercise.course_id = ?d AND exercise.id = ?d", $course_id, $resource)->title;
                $type = "$langCategoryExcercise";                
                break;
        case AssignmentEvent::ACTIVITY:
                $title = Database::get()->querySingle("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $resource)->title;
                $type = "$langCategoryEssay";                
            break;
        case LearningPathEvent::ACTIVITY: 
                $title = Database::get()->querySingle("SELECT name FROM lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $resource)->name;
                $type = "$langLearningPath";
            break;
        case ViewingEvent::DOCUMENT_ACTIVITY: 
                $cer_res = Database::get()->queryArray("SELECT IF(title = '', filename, title) AS file_details FROM document 
                                    WHERE document.course_id = ?d AND document.id = ?d", $course_id, $resource);                
                foreach ($cer_res as $res_data) {
                    $title = $res_data->file_details;
                }
                $type = "$langDocument";
            break;
        case ViewingEvent::VIDEO_ACTIVITY:
                $title = Database::get()->querySingle("SELECT title FROM video WHERE video.course_id = ?d AND video.id = ?d", $course_id, $resource)->title;
                $type = "$langVideo";                
            break;
        case ViewingEvent::VIDEOLINK_ACTIVITY:
                $title = Database::get()->querySingle("SELECT title FROM videolink WHERE videolink.course_id = ?d AND videolink.id = ?d", $course_id, $resource)->title;
                $type = "$langsetvideo";                
            break;
        case ViewingEvent::EBOOK_ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM ebook WHERE ebook.course_id = ?d AND ebook.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                } else {
                    $q1 = Database::get()->querySingle("SELECT ebook_section.title FROM ebook_section, ebook WHERE ebook_section.ebook_id = ebook.id AND ebook.course_id = ?d AND ebook_section.id = ?d", $course_id, $resource);
                    if ($q1) {
                        $title = $q1->title;
                    } else {
                        $q2 = Database::get()->querySingle("SELECT ebook_subsection.title FROM ebook_subsection, ebook_section, ebook "
                                . "WHERE ebook_subsection.id = ebook_subsection.section_id "
                                . "AND ebook_section.id = ebook_section.ebook_id "
                                . "AND ebook.course_id = ?d AND ebook_subsection.id = ?d", $course_id, $resource);
                        if ($q2) {
                            $title = $q2->title;
                        }                    
                    }
                }                
                $type = "$langEBook";
            break;
        case ViewingEvent::QUESTIONNAIRE_ACTIVITY: 
                $title = Database::get()->querySingle("SELECT name FROM poll WHERE poll.course_id = ?d AND poll.pid = ?d", $course_id, $resource)->name;
                $type = "$langMetaQuestionnaire";
            break;
        case BlogEvent::ACTIVITY: 
                $title = "$langNumOfBlogs";
                $type = "$langBlog";                
            break;
        case CommentEvent::BLOG_ACTIVITY:                
                $title = Database::get()->querySingle("SELECT title FROM blog_post WHERE blog_post.course_id = ?d AND blog_post.id = ?d", $course_id, $resource)->title;
                $type = "$langCommentsBlog";
            break;
        case CommentEvent::COURSE_ACTIVITY:
                $type = "$langComments";
                $title = "$langCommentsCourse";
            break;
        case RatingEvent::SOCIALBOOKMARK_ACTIVITY:
                $type = "$langPersoValue $langCourseSocialBookmarks";
                $title = "$langPersoValue";
            break;
        case ForumEvent::ACTIVITY:
                $title = "$langNumOfForums";
                $type = "$langForums";
            break;
        case ForumTopicEvent::ACTIVITY:
                $title = Database::get()->querySingle("SELECT title FROM forum_topic WHERE id = ?d", $resource)->title;
                $type = "$langForums";
            break;
        case RatingEvent::FORUM_ACTIVITY:
                $type = "$langForumRating";
                $title = "$langPersoValue";
            break;
        case WikiEvent::ACTIVITY:                
                $type = "$langWiki";
                $title = "$langWikiPages";
            break;
        case CourseParticipationEvent::ACTIVITY:
                $type = "$langCourseParticipation";
                $title = "$langCourseHoursParticipation";
            break;
        default: 
                $title = "$langAllActivities";
            break;
    }
    $data['type'] = $type;
    $data['title'] = $title;
        
    return $data;    
}


/**
 * @brief certificate pdf output 
 * @global type $webDir;
 * @param type $user_id
 */
function cert_output_to_pdf($certificate_id, $user, $certificate_title = null, $certificate_issuer = null) {
    
    global $webDir;
           
    $cert_file = Database::get()->querySingle("SELECT filename FROM certificate_template 
                                                    JOIN certificate ON certificate_template.id = certificate.template
                                               AND certificate.id = ?d", $certificate_id)->filename;
    
    $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 0, 0, 0, 0, 0, 0);
    
    $html_certificate = file_get_contents($webDir . CERT_TEMPLATE_PATH . $cert_file);

    if (is_null($certificate_title)) {
        $certificate_title = get_cert_title('certificate', $certificate_id);
    }
    if (is_null($certificate_issuer)) {
        $certificate_issuer = get_cert_issuer($certificate_id);
    }    
    
    $sql = Database::get()->querySingle("SELECT message FROM certificate WHERE id = ?d", $certificate_id);
    if ($sql) {
        $certificate_message = $sql->message;
    }
    
    if (intval($user) > 0) {
        $student_name = uid_to_name($user);
    } else {
        $student_name = $user;
    }    

    $html_certificate = preg_replace('(%certificate_title%)', $certificate_title, $html_certificate);
    $html_certificate = preg_replace('(%student_name%)', $student_name, $html_certificate);
    $html_certificate = preg_replace('(%issuer%)', $certificate_issuer, $html_certificate);
    $html_certificate = preg_replace('(%message%)', $certificate_message, $html_certificate);
    
    $mpdf->WriteHTML($html_certificate);

    $mpdf->Output();
}

/**
 * @brief register user as certified
 * @global type $course_id
 * @param type $table
 * @param type $element_id
 * @param type $element_title
 * @param type $user_id
 */
function register_certified_user($table, $element_id, $element_title, $user_id) {
    
    global $course_id;
    
    $title = course_id_to_title($course_id);    
    $user_fullname = uid_to_name($user_id);
    $issuer = get_cert_issuer($element_id);
    $expiration_date = get_cert_expiration_day($table, $element_id);
    Database::get()->query("INSERT INTO certified_users SET course_title = ?s, "
                                                                . "cert_title = ?s, "
                                                                . "cert_id = ?d, "
                                                                . "cert_issuer = ?s, "
                                                                . "user_fullname = ?s, "
                                                                . "assigned = " . DBHelper::timeAfter() . ","
                                                                . "expires = ?s, "
                                                                . "identifier = '" . uniqid(rand()) . "'", 
                                                    $title, $element_title, $element_id, $issuer, $user_fullname, $expiration_date);
    
}


/**
 * @brief get certification identifier
 * @param type $certificate_id
 * @param type $user_id
 * @return type
 */
function get_cert_identifier($certificate_id, $user_id) {
    
    $user_fullname = uid_to_name($user_id);
    $sql = Database::get()->querySingle("SELECT identifier FROM certified_users WHERE "
                                                        . "cert_id = ?d "
                                                        . "AND user_fullname = ?s", 
                                                    $certificate_id, $user_fullname);
    if ($sql) {    
        return $sql->identifier;
    } else {    
        return null;
    }
}


/**
 * @brief get public certificate link
 * @global type $urlServer
 * @param type $element_id
 * @param type $user_id
 * @return type
 */
function certificate_link($element_id, $user_id) {
    
    global $urlServer;
    
    $link = $urlServer . "modules/progress/out.php?i=".get_cert_identifier($element_id, $user_id);
    return "<a href='$link' target=_blank>$link</a>";
    
}