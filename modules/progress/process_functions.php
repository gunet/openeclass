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
function add_assignment_to_certificate($element, $element_id, $activity_type) {
    if (isset($_POST['assignment'])) {
        $operator = 'get';
        $threshold = 1.0;
        foreach ($_POST['assignment'] as $datakey => $data) {
            if ($activity_type == AssignmentEvent::ACTIVITY) {
                $operator = $_POST['operator'][$data];
                $threshold = $_POST['threshold'][$data];
            }
            Database::get()->query("INSERT INTO {$element}_criterion
                                    SET $element = ?d,
                                        module= " . MODULE_ID_ASSIGN . ",
                                        resource = ?d,
                                        activity_type = ?s,
                                        operator = ?s,
                                        threshold = ?f",
                $element_id,
                $_POST['assignment'][$datakey],
                $activity_type,
                $operator,
                $threshold);
        }
    }
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
                                SET $element = ?d, module= " . MODULE_ID_VIDEO . ", resource = ?d, activity_type = ?s",
                $element_id, $d[1], $d[0])->lastInsertID;
        }
    }
    if (isset($_POST['videocatlink'])) {
        foreach ($_POST['videocatlink'] as $data) {
            $d = explode(":", $data);
            Database::get()->query("INSERT_INTO {$element}_criterion
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
function add_lp_to_certificate($element, $element_id, $activity_type) {

    if (isset($_POST['lp'])) {
        foreach ($_POST['lp'] as $datakey => $data) {
            Database::get()->query("INSERT INTO {$element}_criterion
                                SET $element = ?d,
                                module = " . MODULE_ID_LP . ",
                                resource = ?d,
                                activity_type = ?s,
                                operator = ?s,
                                threshold = ?f",
                $element_id,
                $_POST['lp'][$datakey],
                $activity_type,
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
        Database::get()->query("INSERT INTO {$element}_criterion
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
        Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
             Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
        Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
        Database::get()->query("INSERT INTO {$element}_criterion
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
            Database::get()->query("INSERT INTO {$element}_criterion
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
 * @brief add course completion as certificate
 * @param type $element_id
 */
function add_course_completion_to_certificate($element_id) {

    global $langQuotaSuccess, $course_code;
    $badge_id = is_course_completion_active(); // get course completion id

    Database::get()->querySingle("INSERT INTO certificate_criterion (certificate, activity_type, module, resource, threshold, operator)
                                   SELECT ?d, activity_type, module, resource, threshold, operator
                                   FROM badge_criterion WHERE badge = ?d", $element_id, $badge_id);
    // mapping badge_criterion_ids --->  cert_criterion_ids
    $cert_criterion_ids = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate = ?d ORDER BY id", $element_id);
    $badge_criterion_ids = Database::get()->queryArray("SELECT id FROM badge_criterion WHERE badge = ?d ORDER BY id", $badge_id);
    $ids = array_map(function ($item) {
        return $item->id;
    }, array_combine($badge_criterion_ids, $cert_criterion_ids));

    // get user progress (if exists)
    Database::get()->querySingle("INSERT INTO user_certificate (user, certificate, completed, completed_criteria, total_criteria, updated, assigned)
                                    SELECT user, ?d, completed, completed_criteria, total_criteria, updated, assigned
                                    FROM user_badge WHERE badge = ?d", $element_id, $badge_id);
    $data = Database::get()->queryArray("SELECT user FROM user_certificate WHERE certificate = ?d", $element_id);
    foreach ($data as $u) {
        $d = Database::get()->queryArray("SELECT badge_criterion, created
                                    FROM user_badge_criterion JOIN badge_criterion
                                    ON badge_criterion.id=user_badge_criterion.badge_criterion
                                    AND badge_criterion.badge = ?d
                                    AND user = ?d", $badge_id, $u->user);
        foreach ($d as $to_add) {
            $index = $to_add->badge_criterion;
            Database::get()->query("INSERT INTO user_certificate_criterion SET
                                        user = $u->user,
                                        certificate_criterion = $ids[$index],
                                        created = '$to_add->created'");
        }
    }

    Session::Messages("$langQuotaSuccess", 'alert-success');
    redirect_to_home_page("modules/progress/index.php?course=$course_code&certificate_id=$element_id");

}

/**
 * @brief add unit completion as certificate
 * @param type $element_id
 * @param int $unit_id
 */
function add_unit_completion_to_certificate($element_id, $unit_id) {

    print_r("element_id: ".$element_id.", unit_id: ".$unit_id." process_fun 414");
    exit;

    global $langQuotaSuccess, $course_code;

    $badge_id = is_unit_completion_active($unit_id); // get unit completion id

    Database::get()->querySingle("INSERT INTO certificate_criterion (certificate, activity_type, module, resource, threshold, operator)
                                   SELECT ?d, activity_type, module, resource, threshold, operator
                                   FROM badge_criterion WHERE badge = ?d", $element_id, $badge_id);
    // mapping badge_criterion_ids --->  cert_criterion_ids
    $d1 = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate = ?d ORDER BY id", $element_id);
    foreach ($d1 as $cert_criterion_ids) {
        $cc_ids[] = $cert_criterion_ids->id;
    }
    $d2 = Database::get()->queryArray("SELECT id FROM badge_criterion WHERE badge = ?d ORDER BY id", $badge_id);
    foreach ($d2 as $badge_criterion_ids) {
        $b_ids[] = $badge_criterion_ids->id;
    }
    $ids = array_combine($b_ids, $cc_ids);

    // get user progress (if exists)
    Database::get()->querySingle("INSERT INTO user_certificate (user, certificate, completed, completed_criteria, total_criteria, updated, assigned)
                                    SELECT user, ?d, completed, completed_criteria, total_criteria, updated, assigned
                                    FROM user_badge WHERE badge = ?d", $element_id, $badge_id);
    $data = Database::get()->queryArray("SELECT user FROM user_certificate WHERE certificate = ?d", $element_id);
    foreach ($data as $u) {
        $d = Database::get()->queryArray("SELECT badge_criterion, created
                                    FROM user_badge_criterion JOIN badge_criterion
                                    ON badge_criterion.id=user_badge_criterion.badge_criterion
                                    AND badge_criterion.badge = ?d
                                    AND user = ?d", $badge_id, $u->user);
        foreach ($d as $to_add) {
            $index = $to_add->badge_criterion;
            Database::get()->query("INSERT INTO user_certificate_criterion SET
                                        user = $u->user,
                                        certificate_criterion = $ids[$index],
                                        created = '$to_add->created'");
        }
    }

    Session::Messages("$langQuotaSuccess", 'alert-success');
    $localhostUrl = localhostUrl();

    redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
}

/**
 * @brief add gradebook db entries in certificate criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_gradebook_to_certificate($element, $element_id) {
    if (isset($_POST['gradebook'])) {
        foreach ($_POST['gradebook'] as $datakey => $data) {
            Database::get()->query("INSERT INTO {$element}_criterion
                                    SET $element = ?d,
                                        module= " . MODULE_ID_GRADEBOOK . ",
                                        resource = ?d,
                                        activity_type = '" . GradebookEvent::ACTIVITY . "',
                                        operator = ?s,
                                        threshold = ?f",
                $element_id,
                $_POST['gradebook'][$datakey],
                $_POST['operator'][$data],
                $_POST['threshold'][$data]);
        }
    }
    return;
}

/**
 * @brief add coursecompletion grade entry in criterion
 * @param type $element
 * @param type $element_id
 */
function add_coursecompletiongrade_to_certificate($element, $element_id) {
    if (isset($_POST[CourseCompletionEvent::ACTIVITY])) {
        Database::get()->query("INSERT INTO {$element}_criterion
                            SET $element = ?d,
                            module = " . MODULE_ID_PROGRESS . ",
                            resource = null,
                            activity_type = '" . CourseCompletionEvent::ACTIVITY . "',
                            operator = ?s,
                            threshold = ?f",
            $element_id,
            $_POST['operator'],
            $_POST['threshold']);
    }
}

function add_attendance_to_certificate($element, $element_id) {
    if (isset($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $datakey => $data) {
            Database::get()->query("INSERT INTO {$element}_criterion
                                    SET $element = ?d,
                                        module= " . MODULE_ID_ATTENDANCE . ",
                                        resource = ?d,
                                        activity_type = '" . AttendanceEvent::ACTIVITY . "',
                                        operator = ?s,
                                        threshold = ?f",
                $element_id,
                $_POST['attendance'][$datakey],
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
 * @param type $element
 * @param type $id
 * @return type
 */
function get_cert_issuer($element, $id) {

    $cert_issuer =  Database::get()->querySingle("SELECT issuer FROM $element WHERE id = ?d", $id)->issuer;

    return $cert_issuer;
}

/**
 * @brief get certificate message
 * @param type $element
 * @param type $id
 * @return type
 */
function get_cert_message($element, $id) {

    $cert_message = Database::get()->querySingle("SELECT message FROM $element WHERE id = ?d", $id)->message;

    return $cert_message;
}

/**
 * @brief get certificate template filename
 * @param type $template_id
 */
function get_certificate_template($template_id) {

    $r = Database::get()->querySingle("SELECT name, filename FROM certificate_template WHERE id = ?d", $template_id);

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
 * @brief get badge filename
 * @param type $badge_id
 * @return type
 */
function get_badge_filename($badge_id) {

    $r = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id =
                (SELECT icon FROM badge WHERE id = ?d)", $badge_id);

    return $r->filename;
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

    $sql = Database::get()->querySingle("SELECT completed FROM user_{$element} WHERE $element = ?d AND user = ?d", $element_id, $uid);
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
 * @param type $table
 * @param int $unit_id
 * @param type $title
 * @param type $description
 * @param type $message
 * @param type $icon
 * @param type $issuer
 * @param type $active
 * @param type $bundle
 * @param type $expiration_day
 * @return type
 * @global type $course_id
 */
function add_certificate($table, $title, $description, $message, $icon, $issuer, $active, $bundle, $expiration_day, $unit_id = null) {

    global $course_id;

    if ($unit_id) {
        $new_id = Database::get()->query("INSERT INTO badge
                                SET course_id = ?d,
                                unit_id = ?d,
                                title = ?s,
                                description = ?s,
                                message = ?s,
                                icon = ?d,
                                issuer = ?s,
                                active = ?d,
                                bundle = ?d,
                                expires = ?t", $course_id, $unit_id, $title, $description, $message, $icon, $issuer, $active, $bundle, $expiration_day)->lastInsertID;
    } else {
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
    Database::get()->query("UPDATE {$element}_criterion
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
    $num_of_activities = Database::get()->querySingle("SELECT COUNT(*) AS act FROM {$element}_criterion
                                                                WHERE $element = ?d", $element_id)->act;

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
    Database::get()->query("UPDATE $element SET active = ?d WHERE id = ?d
                                    AND course_id = ?d", $visibility, $element_id, $course_id);
}

/**
 * @brief check if course completion badge is active
 * @global type $course_id
 * @return boolean
 */
function is_course_completion_active() {

    global $course_id;

    $sql = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND bundle = -1
                                                    AND active = 1 AND unit_id = 0", $course_id);
    if ($sql) {
        return $sql->id;
    } else {
        return 0;
    }
}

/**
 * @brief check if we have created course completion badge
 * @global type $course_id
 * @return boolean
 */
function is_course_completion_enabled() {
    global $course_id;

    $sql = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND bundle = -1
                                                    AND unit_id = 0", $course_id);
    if ($sql) {
        return $sql->id;
    } else {
        return 0;
    }
}

/**
 * @brief check if course completion badge is active
 * @global type $course_id
 * @return boolean
 */
function is_unit_completion_active($unit_id) {

    global $course_id;

    $sql = Database::get()->querySingle("SELECT id FROM badge_unit WHERE course_id = ?d AND unit_id = ?d
                                                    AND bundle = -1 AND active = 1", $course_id, $unit_id);
    if ($sql) {
        return $sql->id;
    } else {
        return 0;
    }
}

/**
 * @brief check if we have created unit completion badge
 * @return boolean
 * @global type $course_id
 * @param int $unit_id
 */
function is_unit_completion_enabled($unit_id) {
    global $course_id;

    $sql = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d
                                                    AND bundle = -1", $course_id, $unit_id);

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

    if (!$data or !$data->total_criteria) {
        return 0;
    } else {
        return round($data->completed_criteria / $data->total_criteria * 100, 0);
    }
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
        Database::get()->query("DELETE FROM badge WHERE id = ?d AND course_id = ?d AND unit_id = 0", $element_id, $course_id);
    }

    return true;
}

/**
 * @brief purge certificate and / or badges
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function purge_certificate($element, $element_id, $unit_id = 0) {

    global $course_id;

    if ($element == 'badge') { // purge badges
        Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion IN
                                (SELECT id FROM badge_criterion WHERE badge IN
                                (SELECT id FROM badge WHERE id = ?d AND course_id = ?d))", $element_id, $course_id);
        Database::get()->query("DELETE FROM badge_criterion WHERE badge IN
                                (SELECT id FROM badge WHERE id = ?d AND course_id = ?d)", $element_id, $course_id);
        Database::get()->query("DELETE FROM user_badge WHERE badge IN
                                (SELECT id FROM badge WHERE id = ?d AND course_id = ?d AND unit_id = ?d)", $element_id, $course_id, $unit_id);
        Database::get()->query("DELETE FROM badge WHERE id = ?d AND course_id = ?d AND unit_id = ?d", $element_id, $course_id, $unit_id);
    } else { // purge certificates
        Database::get()->query("DELETE FROM user_certificate_criterion WHERE certificate_criterion IN
                            (SELECT id FROM certificate_criterion WHERE certificate IN
                            (SELECT id FROM certificate WHERE id = ?d AND course_id = ?d))", $element_id, $course_id);
        Database::get()->query("DELETE FROM certificate_criterion WHERE certificate IN
                                (SELECT id FROM certificate WHERE id = ?d AND course_id = ?d)", $element_id, $course_id);
        Database::get()->query("DELETE FROM user_certificate WHERE certificate IN
                                 (SELECT id FROM certificate WHERE id = ?d AND course_id = ?d)", $element_id, $course_id);
        Database::get()->query("DELETE FROM certificate WHERE id = ?d AND course_id = ?d", $element_id, $course_id);
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
 * @param type $element
 * @param type $resource_id
 * @return type
 */
function get_resource_details($element, $resource_id) {

    global $course_id, $langExercise, $langAssignment, $langLearnPath, $langNumOfForums,
            $langDocument, $langVideo, $langsetvideo, $langEBook, $langMetaQuestionnaire,
            $langBlog, $langForums, $langWikiPages, $langNumOfBlogs, $langCourseParticipation,
            $langWiki, $langAllActivities, $langComments, $langCommentsBlog, $langCommentsCourse,
            $langPersoValue, $langCourseSocialBookmarks, $langForumRating, $langCourseHoursParticipation, $langGradebook,
            $langGradeCourseCompletion, $langCourseCompletion, $langOfLearningPathDuration, $langAssignmentParticipation,
            $langAttendance;

    $data = array('type' => '', 'title' => '');
    $type = $title = '';

    $res_data = Database::get()->querySingle("SELECT activity_type, module, resource FROM {$element}_criterion WHERE id = ?d", $resource_id);

    $resource = $res_data->resource;
    $resource_type = $res_data->activity_type;

    switch ($resource_type) {
        case ExerciseEvent::ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM exercise WHERE exercise.course_id = ?d AND exercise.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                }
                $type = "$langExercise";
                break;
        case AssignmentEvent::ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                }
                $type = "$langAssignment";
            break;
        case AssignmentSubmitEvent::ACTIVITY:
            $q = Database::get()->querySingle("SELECT title FROM assignment WHERE assignment.course_id = ?d AND assignment.id = ?d", $course_id, $resource);
            if ($q) {
                $title = $q->title;
            }
            $type = "$langAssignmentParticipation";
            break;
        case LearningPathEvent::ACTIVITY:
                $q = Database::get()->querySingle("SELECT name FROM lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->name;
                }
                $type = "$langLearnPath";
            break;
        case LearningPathDurationEvent::ACTIVITY:
            $q = Database::get()->querySingle("SELECT name FROM lp_learnPath WHERE lp_learnPath.course_id = ?d AND lp_learnPath.learnPath_id = ?d", $course_id, $resource);
            if ($q) {
                $title = $q->name;
            }
            $type = "$langOfLearningPathDuration";
            break;
        case ViewingEvent::DOCUMENT_ACTIVITY:
                $cer_res = Database::get()->queryArray("SELECT (CASE WHEN title IS NULL OR title=' ' THEN filename ELSE title END) AS file_details FROM document
                                    WHERE document.course_id = ?d AND document.id = ?d", $course_id, $resource);
                foreach ($cer_res as $res_data) {
                    $title = $res_data->file_details;
                }
                $type = "$langDocument";
            break;
        case ViewingEvent::VIDEO_ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM video WHERE video.course_id = ?d AND video.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                }
                $type = "$langVideo";
            break;
        case ViewingEvent::VIDEOLINK_ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM videolink WHERE videolink.course_id = ?d AND videolink.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                }
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
                $q = Database::get()->querySingle("SELECT name FROM poll WHERE poll.course_id = ?d AND poll.pid = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->name;
                }
                $type = "$langMetaQuestionnaire";
            break;
        case BlogEvent::ACTIVITY:
                $title = "$langNumOfBlogs";
                $type = "$langBlog";
            break;
        case CommentEvent::BLOG_ACTIVITY:
                $q = Database::get()->querySingle("SELECT title FROM blog_post WHERE blog_post.course_id = ?d AND blog_post.id = ?d", $course_id, $resource);
                if ($q) {
                    $title = $q->title;
                }
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
                $q = Database::get()->querySingle("SELECT title FROM forum_topic WHERE id = ?d", $resource);
                if ($q) {
                    $title = $q->title;
                }
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
        case GradebookEvent::ACTIVITY:
            $q = Database::get()->querySingle("SELECT title FROM gradebook WHERE gradebook.course_id = ?d AND gradebook.id = ?d", $course_id, $resource);
            if ($q) {
                $title = $q->title;
            }
            $type = "$langGradebook";
            break;
        case CourseCompletionEvent::ACTIVITY:
            $title = "$langGradeCourseCompletion";
            $type = "$langCourseCompletion";
            break;
        case AttendanceEvent::ACTIVITY:
            $q = Database::get()->querySingle("SELECT title FROM attendance WHERE attendance.course_id = ?d AND attendance.id = ?d", $course_id, $resource);
            if ($q) {
                $title = $q->title;
            }
            $type = "$langAttendance";
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
 * @param type $certificate_id
 * @param type $user_id
 * @param type $certificate_title
 * @param type $certificate_message
 * @param type $certificate_issuer
 * @param type $certificate_date
 * @param type $certificate_template_id
 * @param type $certificate_identifier
 */
function cert_output_to_pdf($certificate_id, $user, $certificate_title = null, $certificate_message = null, $certificate_issuer = null, $certificate_date = null, $certificate_template_id = null, $certificate_identifier = null) {

    global $webDir, $urlServer, $langCertAuthenticity;

    if (intval($user) > 0) { // if we are logged in and course / certificate exist
        $certificate_title = get_cert_title('certificate', $certificate_id);
        $certificate_issuer = get_cert_issuer('certificate', $certificate_id);
        $certificate_message = get_cert_message('certificate', $certificate_id);
        $q = Database::get()->querySingle("SELECT filename, orientation FROM certificate_template
                                                    JOIN certificate ON certificate_template.id = certificate.template
                                               AND certificate.id = ?d", $certificate_id);
        $cert_file = $q->filename;
        $orientation = $q->orientation;
        $student_name = uid_to_name($user);
        $cert_link = $langCertAuthenticity . ":&nbsp;&nbsp;&nbsp;" . certificate_link($certificate_id, $user, true);
        $cert_date = Database::get()->querySingle("SELECT UNIX_TIMESTAMP(assigned) AS cert_date FROM user_certificate WHERE user = ?d AND certificate = ?d", $user, $certificate_id)->cert_date;
        if ($cert_date) {
            $certificate_date = format_locale_date($cert_date, 'full', false);
        } else {
            $certificate_date = format_locale_date(time(), 'full', false);
        }

    } else { // logged out
        $q = Database::get()->querySingle("SELECT filename, orientation FROM certificate_template
                                                JOIN certified_users ON certificate_template.id = certified_users.template_id
                                                AND certified_users.identifier = ?s", $certificate_identifier);
        $cert_file = $q->filename;
        $orientation = $q->orientation;
        $cert_link = $langCertAuthenticity . ":&nbsp;&nbsp;&nbsp;" . $urlServer . "main/out.php?i=" .$certificate_identifier;
        $student_name = $user;
    }
    // init pdf
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => $orientation == 'P'? 'A4': 'A4-L',
        'tempDir' => _MPDF_TEMP_PATH,
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
    ]);
    $mpdf->AddFontDirectory(_MPDF_TTFONTDATAPATH);
    chdir("$webDir" . CERT_TEMPLATE_PATH);
    $html_certificate = file_get_contents($cert_file);
    $html_certificate = preg_replace('(%certificate_title%)', $certificate_title, $html_certificate);
    $html_certificate = preg_replace('(%student_name%)', $student_name, $html_certificate);
    $html_certificate = preg_replace('(%issuer%)', $certificate_issuer, $html_certificate);
    $html_certificate = preg_replace('(%message%)', $certificate_message, $html_certificate);
    $html_certificate = preg_replace('(%date%)', $certificate_date, $html_certificate);
    $html_certificate = preg_replace('(%link%)', $cert_link, $html_certificate);

    $mpdf->WriteHTML($html_certificate);
    $mpdf->Output();
}

/**
 * @brief register user as certified
 * @param type $table
 * @param type $element_id
 * @param type $element_title
 * @param type $user_id
 */
function register_certified_user($table, $element_id, $user_id): void
{

    global $course_id;

    $title = course_id_to_title($course_id);
    $user_fullname = uid_to_name($user_id);
    $cert_title = get_cert_title($table, $element_id);
    $issuer = get_cert_issuer($table, $element_id);
    $message = get_cert_message($table, $element_id);
    $expiration_date = get_cert_expiration_day($table, $element_id);
    $template_id = Database::get()->querySingle("SELECT template FROM $table WHERE id = ?d", $element_id)->template;
    Database::get()->query("INSERT INTO certified_users SET course_title = ?s, "
                                                                . "cert_title = ?s, "
                                                                . "cert_message = ?s, "
                                                                . "cert_id = ?d, "
                                                                . "cert_issuer = ?s, "
                                                                . "user_fullname = ?s, "
                                                                . "assigned = " . DBHelper::timeAfter() . ","
                                                                . "expires = ?s, "
                                                                . "template_id = ?d, "
                                                                . "user_id = ?d, "
                                                                . "identifier = '" . uniqid(rand()) . "'",
                                                    $title, $cert_title, $message, $element_id, $issuer, $user_fullname, $expiration_date, $template_id, $user_id);

}

/**
 * @brief refresh user progress from activities (note: only from exercises, assignments and learning path)
 * @param $element
 * @param $element_id
 * @return void
 */
function refresh_user_progress($element, $element_id): void
{
    global $course_id;

    require_once "modules/exercise/exercise.class.php";
    require_once "modules/progress/AssignmentEvent.php";
    require_once "modules/progress/AssignmentSubmitEvent.php";
    require_once "modules/progress/ExerciseEvent.php";
    require_once "modules/progress/LearningPathEvent.php";
    require_once "modules/progress/LearningPathDurationEvent.php";
    require_once "modules/progress/AttendanceEvent.php";
    require_once "include/lib/learnPathLib.inc.php";
    require_once "modules/attendance/functions.php";

    $users = get_course_users($course_id);
    foreach ($users as $u) {
        $q = Database::get()->queryArray("SELECT * FROM {$element}_criterion WHERE $element = ?d", $element_id);
        foreach ($q as $data) {
            switch ($data->activity_type) {
                case AssignmentEvent::ACTIVITY:
                        $eventData = new stdClass();
                        $eventData->courseId = $course_id;
                        $eventData->uid = $u;
                        $eventData->activityType = AssignmentEvent::ACTIVITY;
                        $eventData->module = MODULE_ID_ASSIGN;
                        $eventData->resource = intval($data->resource);
                        AssignmentEvent::trigger(AssignmentEvent::UPGRADE, $eventData);
                    break;
                case AssignmentSubmitEvent::ACTIVITY:
                        $eventData = new stdClass();
                        $eventData->courseId = $course_id;
                        $eventData->uid = $u;
                        $eventData->activityType = AssignmentSubmitEvent::ACTIVITY;
                        $eventData->module = MODULE_ID_ASSIGN;
                        $eventData->resource = intval($data->resource);
                        AssignmentSubmitEvent::trigger(AssignmentSubmitEvent::UPDATE, $eventData);
                    break;
                case ExerciseEvent::ACTIVITY:
                        $eventData = new stdClass();
                        $eventData->courseId = $course_id;
                        $eventData->uid = $u;
                        $eventData->activityType = ExerciseEvent::ACTIVITY;
                        $eventData->module = MODULE_ID_EXERCISE;
                        $eventData->resource = intval($data->resource);
                        ExerciseEvent::trigger(ExerciseEvent::NEWRESULT, $eventData);
                    break;
                case LearningPathEvent::ACTIVITY:
                case LearningPathDurationEvent::ACTIVITY:
                        triggerLPGame($course_id, $u, $data->resource, LearningPathEvent::UPDPROGRESS);
                    break;
                case AttendanceEvent::ACTIVITY:
                        triggerAttendanceGame($course_id, $u, $data->resource, AttendanceEvent::UPDATE);
                    break;
            }
        }
    }
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
                                                        . "AND (user_fullname = ?s OR user_id = ?d)",
                                                    $certificate_id, $user_fullname, $user_id);
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
 * @param type $printable
 * @return type
 */
function certificate_link($element_id, $user_id, $printable = false) {

    global $urlServer;

    $link = $urlServer . "main/out.php?i=".get_cert_identifier($element_id, $user_id);
    if ($printable) {
        return $link;
    }
    return "<a href='$link' target=_blank>$link</a>";


}

/**
 * @brief check unit progress
 * @param $unit_id
 * @return bool
 */
function check_unit_progress($unit_id) {
    global $uid, $course_id;

    require_once 'Game.php';

    // check for completeness in order to refresh user data
    Game::checkCompleteness($uid, $course_id, $unit_id);

    return true;
}
