<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 *
 * @brief display groups
 * @file index.php
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

require_once '../../include/baseTheme.php';
require_once 'group_functions.php';
require_once 'include/log.php';
/* * ***Required classes for wiki creation*** */
require_once 'modules/wiki/lib/class.wiki.php';
require_once 'modules/wiki/lib/class.wikipage.php';
require_once 'modules/wiki/lib/class.wikistore.php';
/* ***Required classes for forum deletion*** */
require_once 'modules/search/indexer.class.php';
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_GROUPS);
/* * *********************************** */

$toolName = $langGroups;
$totalRegistered = 0;
unset($message);

unset($_SESSION['secret_directory']);
unset($_SESSION['forum_id']);

if (!$uid or !$courses[$course_code]) {
    forbidden();
}

initialize_group_info();
$user_groups = user_group_info($uid, $course_id);

if ($is_editor) {
    if (isset($_POST['creation'])) {
			$category_id = intval(getDirectReference($_POST['selectcategory']));
			$id = Database::get()->query("INSERT INTO `group` (course_id, name, description, forum_id, category_id, max_members, secret_directory)
                                    VALUES (?d, ?s, ?s, ?d, ?d, ?d, ?s)",  $course_id, $group_name, $group_desc, $forum_id, $category_id, $group_max, $secretDirectory)->lastInsertID;
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('group_quantity'));
        $v->rule('numeric', array('group_quantity'));
        $v->rule('min', array('group_quantity'), 1);
        $v->rule('required', array('group_max'));
        $v->rule('numeric', array('group_max'));
        $v->rule('min', array('group_max'), 1);
        $v->labels(array(
            'group_quantity' => "$langTheField $langNewGroups",
            'group_max' => "$langTheField $langNewGroupMembers"
        ));
        if($v->validate()) {          
            $group_quantity = intval($_POST['group_quantity']);
            if (preg_match('/^[0-9]/', $_POST['group_max'])) {
                $group_max = intval($_POST['group_max']);
            } else {
                $group_max = 0;
            }
            $group_num = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `group` WHERE course_id = ?d", $course_id)->count;

            // Create a hidden category for group forums
            $req = Database::get()->querySingle("SELECT id FROM forum_category
                                    WHERE cat_order = -1
                                    AND course_id = ?d", $course_id);
            if ($req) {
                $cat_id = $req->id;
            } else {
                $req2 = Database::get()->query("INSERT INTO forum_category (cat_title, cat_order, course_id)
                                             VALUES (?s, -1, ?d)", $langCatagoryGroup, $course_id);
                $cat_id = $req2->lastInsertID;
            }
            for ($i = 1; $i <= $group_quantity; $i++) {
                $res = Database::get()->query("SELECT id FROM `group` WHERE name = '$langGroup ". $group_num . "'");
                if ($res) {
                    $group_num++;
                }
                $forumname = "$langForumGroup $group_num";
                $q = Database::get()->query("INSERT INTO forum SET name = '$forumname',
                                                        `desc` = ' ', num_topics = 0, num_posts = 0, last_post_id = 1, cat_id = ?d, course_id = ?d", $cat_id, $course_id);
                $forum_id = $q->lastInsertID;
                // Create a unique path to group documents to try (!)
                // avoiding groups entering other groups area
                $secretDirectory = uniqid('');
                mkdir("courses/$course_code/group/$secretDirectory", 0777, true);
                touch("courses/$course_code/group/index.php");
                touch("courses/$course_code/group/$secretDirectory/index.php");

                Database::get()->query("INSERT INTO `group` (max_members, secret_directory)
                                    VALUES (?d, ?s)", $group_max, $secretDirectory);

                $id = Database::get()->query("INSERT INTO `group` SET
                                             course_id = ?d,
                                             name = '$langGroup $group_num',
                                             forum_id =  ?d,
                                             max_members = ?d,
                                             secret_directory = ?s",
                                    $course_id, $forum_id, $group_max, $secretDirectory)->lastInsertID;

                /*             * ********Create Group Wiki*********** */
                //Set ACL
                $wikiACL = array();
                $wikiACL['course_read'] = true;
                $wikiACL['course_edit'] = false;
                $wikiACL['course_create'] = false;
                $wikiACL['group_read'] = true;
                $wikiACL['group_edit'] = true;
                $wikiACL['group_create'] = true;
                $wikiACL['other_read'] = false;
                $wikiACL['other_edit'] = false;
                $wikiACL['other_create'] = false;

                $wiki = new Wiki();
                $wiki->setTitle($langGroup . " " . $group_num . " - Wiki");
                $wiki->setDescription('');
                $wiki->setACL($wikiACL);
                $wiki->setGroupId($id);
                $wikiId = $wiki->save();

                $mainPageContent = $langWikiMainPageContent;

                $wikiPage = new WikiPage($wikiId);
                $wikiPage->create($uid, '__MainPage__', $mainPageContent, '', date("Y-m-d H:i:s"), true);
                /*             * ************************************ */

                Log::record($course_id, MODULE_ID_GROUPS, LOG_INSERT, array('id' => $id,
                                                                            'name' => "$langGroup $group_num",
                                                                            'max_members' => $group_max,
                                                                            'secret_directory' => $secretDirectory));
            }
            if ($group_quantity == 1) {
                $message = "$group_quantity $langGroupAdded";
            } else {
                $message = "$group_quantity $langGroupsAdded";
            }
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/group/group_creation.php?course=$course_code");
        }
    } elseif (isset($_POST['properties'])) {
        register_posted_variables(array(
            'self_reg' => true,
            'multi_reg' => true,
            'private_forum' => true,
            'has_forum' => true,
            'documents' => true,
            'wiki' => true), 'all');
        Database::get()->query("UPDATE group_properties SET
                                 self_registration = ?d,
                                 multiple_registration = ?d,
                                 private_forum = ?d,
                                 forum = ?d,
                                 documents = ?d,
                                 wiki = ?d WHERE course_id = ?d",
                    $self_reg, $multi_reg, $private_forum, $has_forum, $documents, $wiki, $course_id);
        $message = $langGroupPropertiesModified;
    } elseif (isset($_REQUEST['delete_all'])) {
    elseif (isset($_POST['submitCategory'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        submit_category();
        $messsage = isset($_POST['id']) ? $langCategoryModded : $langCategoryAdded;
        Session::Messages($messsage, 'alert-success');
        redirect_to_home_page("modules/group/index.php");
    }
        /*         * ************Delete All Group Wikis********** */
        $sql = "SELECT id "
                . "FROM wiki_properties "
                . "WHERE group_id "
                . "IN (SELECT id FROM `group` WHERE course_id = ?d)";

        $results = Database::get()->queryArray($sql, $course_id);
        if (is_array($results)) {
            foreach ($results as $result) {
                $wikiStore = new WikiStore();
                $wikiStore->deleteWiki($result->id);
            }
        }
        /*         * ******************************************** */
        /*         * ************Delete All Group Forums********** */
        $results = Database::get()->queryArray("SELECT `forum_id` FROM `group` WHERE `course_id` = ?d AND `forum_id` <> 0 AND `forum_id` IS NOT NULL", $course_id);
        if (is_array($results)) {

            foreach ($results as $result) {
                $forum_id = $result->forum_id;
                $result2 = Database::get()->queryArray("SELECT id FROM forum_topic WHERE forum_id = ?d", $forum_id);
                foreach ($result2 as $result_row2) {
                    $topic_id = $result_row2->id;
                    Database::get()->query("DELETE FROM forum_post WHERE topic_id = ?d", $topic_id);
                    Indexer::queueAsync(Indexer::REQUEST_REMOVEBYTOPIC, Indexer::RESOURCE_FORUMPOST, $topic_id);
                }
                Database::get()->query("DELETE FROM forum_topic WHERE forum_id = ?d", $forum_id);
                Indexer::queueAsync(Indexer::REQUEST_REMOVEBYFORUM, Indexer::RESOURCE_FORUMTOPIC, $forum_id);
                Database::get()->query("DELETE FROM forum_notify WHERE forum_id = ?d AND course_id = ?d", $forum_id, $course_id);
                Database::get()->query("DELETE FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);
                Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUM, $forum_id);
            }
        }
        /*         * ******************************************** */

        Database::get()->query("DELETE FROM group_members WHERE group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $course_id);
        Database::get()->query("DELETE FROM `group` WHERE course_id = ?d", $course_id);
        Database::get()->query("DELETE FROM document WHERE course_id = ?d AND subsystem = 1", $course_id);
        // Move all groups to garbage collector and re-create an empty work directory
        $groupGarbage = $course_code . '_groups_' . uniqid(20);

        @mkdir("../../courses/garbage");
        @touch("../../courses/garbage/index.php");
        rename("../../courses/$course_code/group", "../../courses/garbage/$groupGarbage");
        mkdir("../../courses/$course_code/group", 0777);
        touch("../../courses/$course_code/group/index.php");

        $message = $langGroupsDeleted;
    } elseif (isset($_REQUEST['delete'])) {
        $id = intval($_REQUEST['delete']);

        // move group directory to garbage collector
        $groupGarbage = "courses/garbage/{$course_code}_group_{$id}_" . uniqid(20);
        $myDir = Database::get()->querySingle("SELECT secret_directory, forum_id, name FROM `group` WHERE id = ?d", $id);
        if ($myDir and $myDir->secret_directory) {
            rename("courses/$course_code/group/" . $myDir->secret_directory, $groupGarbage);
        }
        // delete group forum
        $result = Database::get()->querySingle("SELECT `forum_id` FROM `group` WHERE `course_id` = ?d AND `id` = ?d AND `forum_id` <> 0 AND `forum_id` IS NOT NULL", $course_id, $id);
        if ($result) {

            $forum_id = $result->forum_id;
            $result2 = Database::get()->queryArray("SELECT id FROM forum_topic WHERE forum_id = ?d", $forum_id);
            foreach ($result2 as $result_row2) {
                $topic_id = $result_row2->id;
                Database::get()->query("DELETE FROM forum_post WHERE topic_id = ?d", $topic_id);
                Indexer::queueAsync(Indexer::REQUEST_REMOVEBYTOPIC, Indexer::RESOURCE_FORUMPOST, $topic_id);
            }
            Database::get()->query("DELETE FROM forum_topic WHERE forum_id = ?d", $forum_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVEBYFORUM, Indexer::RESOURCE_FORUMTOPIC, $forum_id);
            Database::get()->query("DELETE FROM forum_notify WHERE forum_id = ?d AND course_id = ?d", $forum_id, $course_id);
            Database::get()->query("DELETE FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUM, $forum_id);
        }
        /*         * *********************************** */

        Database::get()->query("DELETE FROM document WHERE course_id = ?d AND subsystem = 1 AND subsystem_id = ?d", $course_id, $id);
        Database::get()->query("DELETE FROM group_members WHERE group_id = ?d", $id);
		Database::get()->query("DELETE FROM group_properties WHERE group_id = ?d", $id);
        Database::get()->query("DELETE FROM `group` WHERE id = ?d", $id);

        /*         * ********Delete Group Wiki*********** */
        $result = Database::get()->querySingle("SELECT id FROM wiki_properties WHERE group_id = ?d", $id);
        if ($result) {
            $wikiStore = new WikiStore();
            $wikiStore->deleteWiki($result->id);
        }
        /*         * *********************************** */

        Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('gid' => $id,
            'name' => $myDir? $myDir->name:"[no name]"));

        $message = $langGroupDel;
    } elseif (isset($_REQUEST['empty'])) {
        Database::get()->query("DELETE FROM group_members
                                   WHERE group_id IN
                                   (SELECT id FROM `group` WHERE course_id = ?d)", $course_id);
        $message = $langGroupsEmptied;
    } elseif (isset($_REQUEST['fill'])) {
        $resGroups = Database::get()->queryArray("SELECT id, max_members -
                                                      (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = id)
                                                  AS remaining
                                              FROM `group` WHERE course_id = ?d ORDER BY id", $course_id);
        foreach ($resGroups as $resGroupsItem) {
            $idGroup = $resGroupsItem->id;
            $places = $resGroupsItem->remaining;
            if ($places > 0) {
                $placeAvailableInGroups[$idGroup] = $places;
            }
        }
        // Course members not registered to any group
        $resUserSansGroupe = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname
                                FROM (user u, course_user cu)
                                WHERE cu.course_id = ?d AND
                                      cu.user_id = u.id AND
                                      cu.status = 5 AND
                                      cu.tutor = 0 AND
                                      u.id NOT IN (SELECT user_id FROM group_members, `group`
                                                                  WHERE `group`.id = group_members.group_id AND
                                                                        `group`.course_id = ?d)
                                GROUP BY u.id
                                ORDER BY u.surname, u.givenname", $course_id, $course_id);
        //die(var_dump(max($placeAvailableInGroups)));
        //['20' => 1]
        // gets highest group value 1
        //finds groups with  highest value 1
        // gets first group with highest value and adds user id
        foreach ($resUserSansGroupe as $idUser) {
            
            $idGroupChoisi = array_keys($placeAvailableInGroups, max($placeAvailableInGroups));
            $idGroupChoisi = $idGroupChoisi[0];
            if ($placeAvailableInGroups[$idGroupChoisi] > 0){            
                $userOfGroups[$idGroupChoisi][] = $idUser->id;
                $placeAvailableInGroups[$idGroupChoisi] --;
            } else {
                continue;
            }
        }

        // NOW we have $userOfGroups containing new affectation. We must write this in database
        if (isset($userOfGroups) and is_array($userOfGroups)) {
            reset($userOfGroups);
            while (list($idGroup, $users) = each($userOfGroups)) {
                while (list(, $idUser) = each($users)) {
                    Database::get()->query("INSERT INTO group_members SET user_id = ?d, group_id = ?d", $idUser, $idGroup);
                }
            }
        }
        $message = $langGroupFilledGroups;
    }

    // Show DB messages
    if (isset($message)) {
        $tool_content .= "<div class='alert alert-success'>$message</div><br>";
    }

    $groupSelect = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d ORDER BY id", $course_id);
    $num_of_groups = count($groupSelect);
    $tool_content .= action_bar(array(
                array('title' => $langCreate,
                    'url' => "group_creation.php?course=$course_code",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langGroupProperties,
                    'url' => "group_properties.php?course=$course_code",
                    'icon' => 'fa-gear'),
				array('title' => $langCategoryAdd,
                      'url' => "group_category.php?course=$course_code",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label'),
                array('title' => $langFillGroupsAll,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;fill=yes",
                    'icon' => 'fa-pencil',
                    'show' => $num_of_groups > 0),
                array('title' => $langEmtpyGroups,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;empty=yes",
                    'icon' => 'fa-trash',
                    'class' => 'delete',
                    'confirm' => $langEmtpyGroups,
                    'confirm_title' => $langEmtpyGroupsAll,
                    'show' => $num_of_groups > 0),
                array('title' => $langDeleteGroups,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_all=yes",
                    'icon' => 'fa-times',
                    'confirm' => $langDeleteGroupAllWarn,
                    'show' => $num_of_groups > 0)));

    $groupSelect = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
    $myIterator = 0;
    $num_of_groups = count($groupSelect);
    // groups list
    if ($num_of_groups > 0) {
        $tool_content .= "
                <div class='table-responsive'>
                <table class='table-default'>
                <tr class='list-header'>
                  <th>$langGroupName</th>
                  <th width='250'>$langGroupTutor</th>
                  <th width='30'>$langGroupMembersNum</th>
                  <th width='30'>$langMax</th>
                  <th class='text-center'>".icon('fa-gears', $langActions)."</th>
                </tr>";
        foreach ($groupSelect as $group) {
            initialize_group_info($group->id);
            $tool_content .= "<tr>";
            $tool_content .= "<td>
                        <a href='group_space.php?course=$course_code&amp;group_id=$group->id'>" . q($group_name) . "</a><p>$group_description</p></td>";
            $tool_content .= "<td class='center'>";
            foreach ($tutors as $t) {
                $tool_content .= display_user($t->user_id) . "<br />";
            }
            $tool_content .= "</td><td class='text-center'>$member_count</td>";
            if ($max_members == 0) {
                $tool_content .= "<td>-</td>";
            } else {
                $tool_content .= "<td class='text-center'>$max_members</td>";
            }
            $tool_content .= "<td class='option-btn-cell'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "group_edit.php?course=$course_code&amp;group_id=$group->id",
                            'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$group->id",
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete))) .
                        "</td></tr>";
            $totalRegistered += $member_count;
            $myIterator++;
        }
        $tool_content .= "</table></div><br>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoGroup</div>";
    }

} else {
    // Begin student view
    $q = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
    if (count($q) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoGroup</div>";
    } else {
        $tool_content .= "<div class='table-responsive'>
            <table class='table-default'>
                <tr class='list-header'>
                  <th class='text-left'>$langGroupName</th>
                  <th width='250'>$langGroupTutor</th>";
        $tool_content .= "<th width='50'>$langRegistration</th>";

        $tool_content .= "<th width='50'>$langGroupMembersNum</th><th width='50'>$langMax</th></tr>";
        foreach ($q as $row) {
            $group_id = $row->id;
            initialize_group_info($group_id);
            $tool_content .= "<td class='text-left'>";
            // Allow student to enter group only if member
            if ($is_member) {
                $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) .
                        "</a> <span style='color:#900; weight:bold;'>($langMyGroup)</span>";
            } else {
                $tool_content .= q($group_name);
            }
            if ($user_group_description) {
                $tool_content .= "<br />" . q($user_group_description) . "&nbsp;&nbsp;" .
                        icon('fa-edit', $langModify, "group_description.php?course=$course_code&amp;group_id=$group_id") . "&nbsp;" .
                        icon('fa-times', $langDelete, "group_description.php?course=$course_code&amp;group_id=$group_id&amp;delete=true", 'onClick="return confirmation();"');
            } elseif ($is_member) {
                $tool_content .= "<br /><a href='group_description.php?course=$course_code&amp;group_id=$group_id'><i>$langAddDescription</i></a>";
            }
            $tool_content .= "</td>";
            $tool_content .= "<td class='text-center'>";
            foreach ($tutors as $t) {
                $tool_content .= display_user($t->user_id) . "<br />";
            }
            $tool_content .= "</td>";

            // If self-registration and multi registration allowed by admin and group is not full
            $tool_content .= "<td class='text-center'>";
            if ($uid and
                    $self_reg and ( !$user_groups or $multi_reg) and ! $is_member and ( !$max_members or $member_count < $max_members)) {
                $tool_content .= "<a href='group_space.php?course=$course_code&amp;selfReg=1&amp;group_id=$group_id'>$langRegistration</a>";
            } else {
                $tool_content .= "-";
            }
            $tool_content .= "</td>";
            $tool_content .= "<td class='text-center'>$member_count</td><td class='text-center'>" .
                    ($max_members ? $max_members : '-') . "</td></tr>";
            $totalRegistered += $member_count;
        }
        $tool_content .= "</table></div>";
    }
	
}

function submit_category() {
    global $course_id, $langCategoryAdded, $langCategoryModded,
    $categoryname, $description;

    register_posted_variables(array('categoryname' => true,
                                    'description' => true), 'all', 'trim');
    $set_sql = "SET name = ?s, description = ?s";
    $terms = array($categoryname, purify($description));

    if (isset($_POST['id'])) {
        $id = getDirectReference($_POST['id']);
        Database::get()->query("UPDATE `group_category` $set_sql WHERE course_id = ?d AND id = ?d", $terms, $course_id, $id);
        $log_type = LOG_MODIFY;
    } else {
        $order = Database::get()->querySingle("SELECT MAX(`order`) as maxorder FROM `link_category`
                                      WHERE course_id = ?d", $course_id)->maxorder;
        $order++;
        $id = Database::get()->query("INSERT INTO `group_category` $set_sql, course_id = ?d, `order` = ?d", $terms, $course_id, $order)->lastInsertID;
        $log_type = LOG_INSERT;
    }
    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
    Log::record($course_id, MODULE_ID_LINKS, $log_type, array('id' => $id,
        'category' => $categoryname,
        'description' => $txt_description));
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
