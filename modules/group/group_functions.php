<?php

/* ========================================================================
 *   Open eClass 3.0
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2012  Greek Universities Network - GUnet
 */

function initialize_group_id($param = 'group_id') {
    global $group_id, $urlServer, $course_code;

    if (!isset($group_id)) {
        if (isset($_REQUEST[$param])) {
            $group_id = intval($_REQUEST[$param]);
        } else {
            header("Location: {$urlServer}modules/group/index.php?course=$course_code");
            exit;
        }
    }
}

function initialize_group_info($group_id = false) {
    global $course_id, $status, $self_reg, $multi_reg, $has_forum, $private_forum, $documents, $wiki,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $user_group_description, $course_code;

    if (!(isset($self_reg) and isset($multi_reg) and isset($has_forum) and isset($private_forum) and isset($documents) and isset($wiki))) {
        $grp_property_item = Database::get()->querySingle("SELECT self_registration, multiple_registration, forum, private_forum, documents, wiki
                         FROM group_properties WHERE course_id = ?d", $course_id);
        $self_reg = $grp_property_item->self_registration;
        $multi_reg = $grp_property_item->multiple_registration;
        $has_forum = $grp_property_item->forum;
        $private_forum = $grp_property_item->private_forum;
        $documents = $grp_property_item->documents;
        $wiki = $grp_property_item->wiki;
    }

    // Guest users aren't allowed to register in a group
    if ($status == 10) {
        $self_reg = 0;
    }

    if ($group_id !== false) {
        $res = Database::get()->querySingle("SELECT name, description, forum_id, max_members, secret_directory
                                 FROM `group` WHERE course_id = ?d AND id = ?d", $course_id, $group_id);
        if (!$res) {
            header("Location: {$urlServer}modules/group/index.php?course=$course_code");
            exit;
        }
        $group_name = Session::has('name') ? Session::get('name') : $res->name;
        $group_description = Session::has('description') ? Session::get('description') : $res->description;
        $forum_id = $res->forum_id;
        $max_members = Session::has('maxStudent') ? Session::get('maxStudent') : $res->max_members;
        $secret_directory = $res->secret_directory;
        $member_count = Database::get()->querySingle("SELECT COUNT(*) as count FROM group_members
                                                                        WHERE group_id = ?d
                                                                        AND is_tutor = 0", $group_id)->count;

        $tutors = group_tutors($group_id);
        $is_tutor = $is_member = $user_group_description = false;
        if (isset($uid)) {
            $res = Database::get()->querySingle("SELECT is_tutor, description FROM group_members
                                         WHERE group_id = ?d AND user_id = ?d", $group_id, $uid);
            if ($res) {
                $is_member = true;
                $is_tutor = $res->is_tutor;
                $user_group_description = $res->description;
            }
        }
    }
}

/**
 * @brief find group tutors
 * @param type $group_id
 * @return type
 */
function group_tutors($group_id) {
    
    $tutors = array();
    $res = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname, has_icon FROM group_members, user
			 WHERE group_id = ?d AND
			       is_tutor = 1 AND
			       group_members.user_id = user.id
			 ORDER BY surname, givenname", $group_id);    
    foreach ($res as $tutor) {
        $tutors[] = $tutor;
    }
    return $tutors;
}

// fills an array with user groups (group_id => group_name)
// passing $as_id will give back only the groups that have been given the specific assignment
function user_group_info($uid, $course_id, $as_id = NULL) {
    $gids = array();

    if ($uid != null) {
        $q = Database::get()->queryArray("SELECT group_members.group_id AS grp_id, `group`.name AS grp_name FROM group_members,`group`
			WHERE group_members.group_id = `group`.id
			AND `group`.course_id = ?d AND group_members.user_id = ?d", $course_id, $uid);
    } else {
        if (!is_null($as_id) && Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $as_id)->assign_to_specific) {
            $q = Database::get()->queryArray("SELECT `group`.name AS grp_name,`group`.id AS grp_id FROM `group`, assignment_to_specific WHERE `group`.id = assignment_to_specific.group_id AND `group`.course_id = ?d AND assignment_to_specific.assignment_id = ?d", $course_id, $as_id);
        } else {
            $q = Database::get()->queryArray("SELECT name AS grp_name,id AS grp_id FROM `group` WHERE course_id = ?d", $course_id);
        }
    }

    foreach ($q as $r) {
        $gids[$r->grp_id] = $r->grp_name;
    }
    return $gids;
}