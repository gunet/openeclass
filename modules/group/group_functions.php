<?php
/*========================================================================
*   Open eClass 2.4
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*/

function initialize_group_id($param = 'group_id')
{
        global $group_id, $urlServer, $code_cours;

	if (!isset($group_id)) {
		if (isset($_REQUEST[$param])) {
			$group_id = intval($_REQUEST[$param]);
		} else {
			header("Location: {$urlServer}modules/group/group.php?course=$code_cours");
			exit;
		}
	}
}

function initialize_group_info($group_id = false)
{
        global $cours_id, $statut, $self_reg, $multi_reg, $has_forum, $private_forum, $documents,
               $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
               $member_count, $is_tutor, $is_member, $uid, $urlServer, $mysqlMainDb, $user_group_description, $code_cours;

        if (!(isset($self_reg) and isset($multi_reg) and isset($has_forum) and isset($private_forum) and isset($documents))) {
                list($self_reg, $multi_reg, $has_forum, $private_forum, $documents) = mysql_fetch_row(db_query(
                        "SELECT self_registration, multiple_registration, forum, private_forum, documents
                         FROM `$mysqlMainDb`.group_properties WHERE course_id = $cours_id"));
        }

	// Guest users aren't allowed to register in a group
	if ($statut == 10) {
		$self_reg = 0;
	}

        if ($group_id !== false) {
                $res = db_query("SELECT name, description, forum_id, max_members, secret_directory
                                 FROM `$mysqlMainDb`.`group` WHERE course_id = $cours_id AND id = $group_id");
                if (!$res or mysql_num_rows($res) == 0) {
                        header("Location: {$urlServer}modules/group/group.php?course=$code_cours");
                        exit;
                }
                list($group_name, $group_description, $forum_id, $max_members, $secret_directory) = mysql_fetch_row($res);

                list($member_count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM `$mysqlMainDb`.group_members
							       WHERE group_id = $group_id"));

		$tutors = group_tutors($group_id);	
                $is_tutor = $is_member = $user_group_description = false;
                if (isset($uid)) {
                        $res = db_query("SELECT is_tutor, description FROM `$mysqlMainDb`.group_members
                                         WHERE group_id = $group_id AND user_id = $uid");
                        if (mysql_num_rows($res) > 0) {
                                $is_member = true;
                                list($is_tutor, $user_group_description) = mysql_fetch_row($res);
                        }
                }
        }
}

function group_tutors($group_id)
{
	global $mysqlMainDb;
	
	$tutors = array();
	$res = db_query("SELECT user.user_id, nom, prenom, has_icon FROM `$mysqlMainDb`.group_members, `$mysqlMainDb`.user
			 WHERE group_id = $group_id AND
			       is_tutor = 1 AND
			       group_members.user_id = user.user_id
			 ORDER BY nom, prenom");
	while ($tutor = mysql_fetch_array($res)) {
		$tutors[] = $tutor;
	}
	return $tutors;
}


// fills an array with user groups (group_id => group_name)
// if $uid is null, returns all groups
// else, just this user's groups
function user_group_info($uid, $cours_id)
{
	global $mysqlMainDb;
	$gids = array();
	
        if ($uid === null) {
                $q = db_query("SELECT id AS grp_id , name AS grp_name
                                      FROM `$mysqlMainDb`.`group`
                                      WHERE `group`.course_id = $cours_id");
        } else {
                $q = db_query("SELECT group_members.group_id AS grp_id,
                                      `group`.name AS grp_name
                                      FROM group_members, `group`
                                      WHERE group_members.user_id = $uid AND
                                            group_members.group_id = `group`.id AND
                                            `group`.course_id = $cours_id", $mysqlMainDb);
        }
	
	while ($r = mysql_fetch_array($q)) {
		$gids[$r['grp_id']] = $r['grp_name'];
	}
	return $gids;
}

// returns group name gives its group id
function gid_to_name($gid)
{
	global $mysqlMainDb;
	
	if ($res = mysql_fetch_row(db_query("SELECT name FROM `group` WHERE id = $gid", $mysqlMainDb))) {
		return $res[0];
	} else {
		return false;
	}
}
