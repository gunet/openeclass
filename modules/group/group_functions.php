<?php
/*========================================================================
*   Open eClass 2.4
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*/

function initialize_group_id($param = 'userGroupId')
{
        global $group_id, $urlServer;

        if (isset($_REQUEST[$param])) {
                $group_id = intval($_REQUEST[$param]);
        } else {
                header("Location: {$urlServer}modules/group/group.php");
                exit;
        }
}

function initialize_group_info($group_id = false)
{
        global $cours_id, $statut, $self_reg, $multi_reg, $has_forum, $private_forum, $documents,
               $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
               $member_count, $is_tutor, $is_member, $uid, $urlServer, $mysqlMainDb;

        if (!(isset($self_reg) and isset($multi_reg) and isset($has_forum) and isset($private_forum) and isset($documents))) {
                list($self_reg, $multi_reg, $has_forum, $private_forum, $documents) = mysql_fetch_row(mysql_query(
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
                        header("Location: {$urlServer}modules/group/group.php");
                        exit;
                }
                list($group_name, $group_description, $forum_id, $max_members, $secret_directory) = mysql_fetch_row($res);

                list($member_count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM `$mysqlMainDb`.group_members
							       WHERE group_id = $group_id"));

                $tutors = array();
                $res = db_query("SELECT user.user_id, nom, prenom FROM `$mysqlMainDb`.group_members, `$mysqlMainDb`.user
                                 WHERE group_id = $group_id AND
                                       is_tutor = 1 AND
                                       group_members.user_id = user.user_id
                                 ORDER BY nom, prenom");
                while ($tutor = mysql_fetch_array($res)) {
                        $tutors[] = $tutor;
                }

                $is_tutor = $is_member = false;
                if (isset($uid)) {
                        $res = db_query("SELECT is_tutor FROM `$mysqlMainDb`.group_members
                                         WHERE group_id = $group_id AND user_id = $uid");
                        if (mysql_num_rows($res) > 0) {
                                $is_member = true;
                                list($is_tutor) = mysql_fetch_row($res);
                        }
                }
        }
}