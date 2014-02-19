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
    global $course_id, $status, $self_reg, $multi_reg, $has_forum, $private_forum, $documents,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $mysqlMainDb, $user_group_description, $course_code;

    if (!(isset($self_reg) and isset($multi_reg) and isset($has_forum) and isset($private_forum) and isset($documents))) {
        list($self_reg, $multi_reg, $has_forum, $private_forum, $documents) = mysql_fetch_row(db_query(
                        "SELECT self_registration, multiple_registration, forum, private_forum, documents
                         FROM `$mysqlMainDb`.group_properties WHERE course_id = $course_id"));
    }

    // Guest users aren't allowed to register in a group
    if ($status == 10) {
        $self_reg = 0;
    }

    if ($group_id !== false) {
        $res = db_query("SELECT name, description, forum_id, max_members, secret_directory
                                 FROM `group` WHERE course_id = $course_id AND id = $group_id");
        if (!$res or mysql_num_rows($res) == 0) {
            header("Location: {$urlServer}modules/group/index.php?course=$course_code");
            exit;
        }
        list($group_name, $group_description, $forum_id, $max_members, $secret_directory) = mysql_fetch_row($res);
        list($member_count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM group_members
                                                                        WHERE group_id = $group_id 
                                                                        AND is_tutor = 0"));

        $tutors = group_tutors($group_id);
        $is_tutor = $is_member = $user_group_description = false;
        if (isset($uid)) {
            $res = db_query("SELECT is_tutor, description FROM group_members
                                         WHERE group_id = $group_id AND user_id = $uid");
            if (mysql_num_rows($res) > 0) {
                $is_member = true;
                list($is_tutor, $user_group_description) = mysql_fetch_row($res);
            }
        }
    }
}

function group_tutors($group_id) {
    $tutors = array();
    $res = db_query("SELECT user.id AS user_id, surname, givenname, has_icon FROM group_members, user
			 WHERE group_id = $group_id AND
			       is_tutor = 1 AND
			       group_members.user_id = user.id
			 ORDER BY surname, givenname");
    while ($tutor = mysql_fetch_array($res)) {
        $tutors[] = $tutor;
    }
    return $tutors;
}

// fills an array with user groups (group_id => group_name)
function user_group_info($uid, $course_id) {
    $gids = array();

    if ($uid != null) {
        $extra_sql = "AND group_members.user_id = $uid";
    } else {
        $extra_sql = "";
    }
    $q = db_query("SELECT group_members.group_id AS grp_id, `group`.name AS grp_name FROM group_members,`group`
			WHERE group_members.group_id = `group`.id
			AND `group`.course_id = $course_id $extra_sql");

    while ($r = mysql_fetch_array($q)) {
        $gids[$r['grp_id']] = $r['grp_name'];
    }
    return $gids;
}

// returns group name gives its group id
function gid_to_name($gid) {

    if ($res = mysql_fetch_row(db_query("SELECT name FROM `group` WHERE id = $gid"))) {
        return $res[0];
    } else {
        return false;
    }
}
