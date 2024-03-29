<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
$require_login = TRUE;
$require_user_registration = TRUE;
$require_help = TRUE;
$helpTopic = 'groups';

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'group_functions.php';
require_once 'include/log.class.php';
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

$user_groups = user_group_info($uid, $course_id);
$user_visible_groups = user_visible_groups($uid, $course_id);

$multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);
$student_desc = setting_get(SETTING_GROUP_STUDENT_DESCRIPTION, $course_id);
//check if social bookmarking is enabled for this course
$social_bookmarks_enabled = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);
if (isset($_GET['socialview'])) {
    $socialview = true;
    $socialview_param = '&amp;socialview';
} else {
    $socialview = false;
    $socialview_param = '';
}
if (isset($_GET['urlview'])) {
    $urlview = urlencode($_GET['urlview']);
} else {
    $urlview = '';
}

if ($is_editor) {
    if (isset($_GET['choice'])) { // change group visibility
        change_group_visibility($_GET['choice'], $_GET['group_id'], $course_id);
    }
    if (isset($_GET['deletecategory'])) { // delete group category
        $id = $_GET['id'];
        delete_group_category($id);
        //Session::Messages($langGroupCategoryDeleted, 'alert-success');
        Session::flash('message',$langGroupCategoryDeleted);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/group/index.php");
    }
    if (isset($_POST['creation'])) { // groups creation
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('group_name'));
        $v->rule('required', array('group_quantity'));
        $v->rule('numeric', array('group_quantity'));
        $v->rule('min', array('group_quantity'), 1);
        $v->rule('required', array('group_max'));
        $v->rule('numeric', array('group_max'));
        $v->rule('min', array('group_max'), 0);
        $v->labels(array(
            'group_name' => "$langTheField $langGroupName",
            'group_quantity' => "$langTheField $langNewGroups",
            'group_max' => "$langTheField $langNewGroupMembers"
        ));

        if($v->validate()) {
            $group_max = $_POST['group_max'];
            $group_quantity = $_POST['group_quantity'];
            $group_description = isset($_POST['description']) ? $_POST['description'] : '';
            $private_forum = isset($_POST['private_forum']) ? $_POST['private_forum'] : 0;
            if (isset($_POST['group_name'])) {
                $group_name = $_POST['group_name'];
            }

            if (isset($_POST['all'])) { // default values if we create multiple groups
                $self_reg = 1;
                $allow_unreg = 0;
            } else {
                if (isset($_POST['self_reg']) and $_POST['self_reg'] == 'on') {
                    $self_reg = 1;
                } else {
                    $self_reg = 0;
                }

                if (isset($_POST['allow_unreg']) and $_POST['allow_unreg'] == 'on') {
                    $allow_unreg = 1;
                } else {
                    $allow_unreg = 0;
                }
            }
            if (isset($_POST['forum']) and $_POST['forum'] == 'on') {
                $has_forum = 1;
            } else {
                $has_forum = 0;
            }

            if (isset($_POST['documents']) and $_POST['documents'] == 'on'){
                $documents = 1;
            } else {
                $documents = 0;
            }

            if (isset($_POST['wiki']) and $_POST['wiki'] == 'on'){
                $wiki = 1;
            } else {
                $wiki = 0;
            }

            if (isset($_POST['booking']) and $_POST['booking'] == 'on'){
                $booking = 1;
            } else {
                $booking = 0;
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
                if (isset($_POST['all'])) {
                    $g_name = "$langGroup $group_num";
                    $res = Database::get()->query("SELECT id FROM `group` WHERE name = '$langGroup ". $group_num . "'");
                    if ($res) {
                        $group_num++;
                    }
                    $forumname = "$langForumGroup $group_num";
                } else {
                    $g_name = $_POST['group_name'];
                    $forumname = "$langForumGroup $g_name";
                }
                // Create a unique path to group documents to try (!)
                // avoiding groups entering other groups area
                $secretDirectory = uniqid('');
                make_dir("courses/$course_code/group/$secretDirectory");
                touch("courses/$course_code/group/index.php");
                touch("courses/$course_code/group/$secretDirectory/index.php");

                // group forum creation
                $q = Database::get()->query("INSERT INTO forum SET name = ?s,
                                                    `desc` = ' ', num_topics = 0,
                                                    num_posts = 0, last_post_id = 1,
                                                    cat_id = ?d, course_id = ?d", $forumname, $cat_id, $course_id);
                $forum_id = $q->lastInsertID;

                $id = Database::get()->query("INSERT INTO `group` SET
                                             course_id = ?d,
                                             name = ?s,
                                             description = ?s,
                                             forum_id = ?d,
                                             max_members = ?d,
                                             secret_directory = ?s,
                                             category_id = ?d",
                                    $course_id, $g_name, $group_description, $forum_id, $group_max, $secretDirectory, $_POST['selectcategory'])->lastInsertID;


                if (isset($_POST['tutor'])) {
                    $user_tutor_id = 0;
                    foreach ($_POST['tutor'] as $user_tutor_id) {
                        Database::get()->query("INSERT INTO group_members SET group_id = ?d, user_id = ?d, is_tutor = 1", $id, $user_tutor_id);
                    }
                }
                if (isset($_POST['ingroup'])) {
                    $new_group_members = count($_POST['ingroup']);
                    for ($i = 0; $i < $new_group_members; $i++) {
                       Database::get()->query("INSERT INTO group_members (user_id, group_id)
                                              VALUES (?d, ?d)", $_POST['ingroup'][$i], $id);
                    }
                }

                $query_vars = [
                    $course_id,
                    $id,
                    $self_reg,
                    $allow_unreg,
                    $has_forum,
                    $private_forum,
                    $documents,
                    $wiki,
                    $booking
                ];

                $group_info = Database::get()->query("INSERT INTO `group_properties` SET course_id = ?d,
                                                                    group_id = ?d, self_registration = ?d,
                                                                    allow_unregister = ?d,
                                                                    forum = ?d, private_forum = ?d,
                                                                    documents = ?d, wiki = ?d, booking = ?d,
                                                                    agenda = 0", $query_vars);

                /** ********Create Group Wiki*********** */
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

                $wiki_obj = new Wiki();
                $wiki_obj->setTitle($langGroup . " " . $group_num . " - Wiki");
                $wiki_obj->setDescription('');
                $wiki_obj->setACL($wikiACL);
                $wiki_obj->setGroupId($id);
                $wikiId = $wiki_obj->save();

                $mainPageContent = $langWikiMainPageContent;

                $wikiPage = new WikiPage($wikiId);
                $wikiPage->create($uid, '__MainPage__', $mainPageContent, '', date("Y-m-d H:i:s"), true);
                /*             * ************************************ */

                Log::record($course_id, MODULE_ID_GROUPS, LOG_INSERT, array('id' => $id,
                                                                            'name' => "$langGroup $group_num",
                                                                            'max_members' => $group_max,
                                                                            'secret_directory' => $secretDirectory));
            }

            //Session::Messages($langGroupsAdded2, "alert-success");
            Session::flash('message',$langGroupsAdded2);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/group/index.php?course=$course_code");

        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/group/group_creation.php?course=$course_code");
        }
    }  elseif (isset($_POST['submitCategory'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        submit_group_category();
        $messsage = isset($_POST['id']) ? $langCategoryModded : $langCategoryAdded;
        //Session::Messages($messsage, 'alert-success');
        Session::flash('message',$messsage);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/group/index.php");
    } elseif (isset($_REQUEST['delete_all'])) {
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

        make_dir("courses/garbage");
        @touch("courses/garbage/index.php");
        rename("courses/$course_code/group", "courses/garbage/$groupGarbage");
        make_dir("courses/$course_code/group");
        touch("courses/$course_code/group/index.php");

        $message = $langGroupsDeleted;
    } elseif (isset($_REQUEST['delete'])) {
        $id = intval($_REQUEST['delete']);

        // move group directory to garbage collector
        if (!file_exists("courses/garbage")) {
            make_dir("courses/garbage");
        }
        $groupGarbage = "courses/garbage/{$course_code}_group_{$id}_" . uniqid(20);
        $myDir = Database::get()->querySingle("SELECT secret_directory, forum_id, name FROM `group` WHERE id = ?d", $id);
        if ($myDir and $myDir->secret_directory) {
            $secret_dir = "courses/$course_code/group/" . $myDir->secret_directory;
            if (file_exists($secret_dir)) {
                rename($secret_dir, $groupGarbage);
            }
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

        //Session::Messages($langGroupDel, "alert-success");
        Session::flash('message',$langGroupDel);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    } elseif (isset($_REQUEST['empty'])) {
        Database::get()->query("DELETE FROM group_members
                                   WHERE group_id IN
                                   (SELECT id FROM `group` WHERE course_id = ?d)", $course_id);
        $message = $langGroupsEmptied;
    } elseif (isset($_REQUEST['fill'])) {
        $placeAvailableInGroups = [];
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

        if (count($placeAvailableInGroups) > 0) {
            // gets first group with the highest value and adds user id
            foreach ($resUserSansGroupe as $idUser) {

                $idGroupChoisi = array_keys($placeAvailableInGroups, max($placeAvailableInGroups));
                $idGroupChoisi = $idGroupChoisi[0];
                if ($placeAvailableInGroups[$idGroupChoisi] > 0) {
                    $userOfGroups[$idGroupChoisi][] = $idUser->id;
                    $placeAvailableInGroups[$idGroupChoisi]--;
                } else {
                    continue;
                }
            }
        }

        // NOW we have $userOfGroups containing new affectation. We must write this in database
        if (isset($userOfGroups) and is_array($userOfGroups)) {
            foreach ($userOfGroups as $idGroup => $users) {
                foreach ($users as $idUser) {
                    Database::get()->query("INSERT INTO group_members SET user_id = ?d, group_id = ?d", $idUser, $idGroup);
                }
            }
        }
        $message = $langGroupFilledGroups;
    }

    // Show DB messages
    if (isset($message)) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$message</span></div></div>";
    }

    $groupSelect = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d ORDER BY id", $course_id);
    $num_of_groups = count($groupSelect);
    $tool_content .= action_bar(array(
                array('title' => $langCreateOneGroup,
                      'url' => "group_creation.php?course=$course_code",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langCreationGroups,
                      'url' => "group_creation.php?course=$course_code&amp;all=1",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langGroupsManagment,
                      'url' => "groups_managment.php?course=$course_code",
                      'icon' => "fa-tasks",
                      'level' => 'primary-label',
                      'button-class' => "btn-primary"),
                array('title' => $langCourseInfo,
                      'url' => "group_settings.php?course=$course_code",
                      'icon' => 'fa-gears',
                      'level' => 'primary-label'),
                array('title' => $langCategoryAdd,
                      'url' => "group_category.php?course=$course_code&amp;addcategory=1",
                      'icon' => 'fa-plus-circle'),
                array('title' => $langFillGroupsAll,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;fill=yes",
                      'icon' => 'fa-pencil',
                      'show' => $num_of_groups > 0),
                array('title' => $langDeleteGroups,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_all=yes",
                      'icon' => 'fa-xmark',
                      'confirm' => $langDeleteGroupAllWarn,
                      'show' => $num_of_groups > 0),
                array('title' => $langEmptyGroups,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;empty=yes",
                      'icon' => 'fa-trash',
                      'class' => 'delete',
                      'confirm' => $langEmptyGroups,
                      'confirm_title' => $langEmptyGroupsAll,
                      'show' => $num_of_groups > 0)
                ));

    $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND (category_id = 0 OR category_id IS NULL) ORDER BY name", $course_id);
    $num_of_groups = count($groupSelect);
    $cat = Database::get()->queryArray("SELECT * FROM `group_category` WHERE course_id = ?d ORDER BY `name`", $course_id);
    $num_of_cat = count($cat);
    $q = count(Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id));
        // groups list
    if ($num_of_groups>0 || $num_of_cat>0) {
        $head_content .= "
                            <script>
                            $(function(){
                                $('#userFeedbacks').on('show.bs.modal', function (event) {
                                  var button = $(event.relatedTarget) // Button that triggered the modal
                                  var content = button.data('content') // Extract info from data-* attributes
                                  var modal = $(this)
                                  modal.find('.modal-body').html(content);
                                })
                            });
                            </script>";
    }
    if ($num_of_groups==0 && $num_of_cat==0) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoGroup</span></div></div>";
        }
    elseif ($num_of_groups==0 && $num_of_cat>0) {
            $tool_content .= "
            <div class='row'>
                <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default nocategory-links'>
                <thead><tr class='list-header'><th class='text-start list-header py-2 px-2'>$langGroupTeam</th>
                <th class=' option-btn-cell text-end py-2 px-2'>" . icon('fa-gears') . "</th>
                </tr></thead>
                <tr><td class='not_visible nocategory-link'> - $langNoGroupInCategory - </td>
                <td></td></tr></table></div></div></div>";
        } elseif ($num_of_groups > 0) {
        //     $tool_content .= "<div class='table-responsive'>
        //         <table class='table-default'>
        //         <tr class='list-header'>
        //           <th class='ps-3'>$langGroupTeam</th>
        //           <th width='250'>$langGroupTutor</th>
        //           <th width='50'>$langGroupMembersNum</th>
        //           <th width='50'>$langMax</th>
        //           <th class='text-center' style='width:45px;'>".icon('fa-gears', $langActions)."</th>
        //         </tr>";

        // foreach ($groupSelect as $group) {
        //     if (!is_group_visible($group->id, $course_id)) {
        //         $link_class = 'not_visible';
        //     } else {
        //         $link_class = '';
        //     }
        //     initialize_group_info($group->id);
        //     $tool_content .= "<tr class='$link_class'>";
        //     $tool_content .= "<td><a href='group_space.php?course=$course_code&amp;group_id=$group->id'>" . q($group_name) . "</a>
        //             <br><p style='padding-top:10px;'>$group_description</p>";
        //     if ($user_group_description && $student_desc) {
        //         $tool_content .= "<small><a href = 'javascirpt:void(0);' data-bs-toggle = 'modal' data-bs-content='".q($user_group_description)."' data-bs-target = '#userFeedbacks' ><span class='fa fa-comments' ></span > $langCommentsUser</a ></small>";
        //     }
        //     $tool_content .= "</td><td class='center'>";
        //     foreach ($tutors as $t) {
        //         $tool_content .= display_user($t->user_id) . "<br>";
        //     }
        //     $tool_content .= "</td><td class='text-center'>$member_count</td>";
        //     if ($max_members == 0) {
        //         $tool_content .= "<td class='text-center'>&mdash;</td>";
        //     } else {
        //         $tool_content .= "<td class='text-center'>$max_members</td>";
        //     }

        //     if (is_group_visible($group->id, $course_id)) {
        //         $visibility_text = $langViewHide;
        //         $visibility_icom = 'fa-eye-slash';
        //         $visibility_url = 'choice=disable';
        //     } else {
        //         $visibility_text = $langViewShow;
        //         $visibility_icom = 'fa-eye';
        //         $visibility_url = 'choice=enable';
        //     }

        //     $tool_content .= "<td class='option-btn-cell text-center'>" .
        //             action_button(array(
        //                 array('title' => $langEditChange,
        //                     'url' => "group_edit.php?course=$course_code&amp;category=$group->category_id&amp;group_id=$group->id",
        //                     'icon' => 'fa-edit'),
        //                 array('title' => $langAddManyUsers,
        //                     'url' => "muladduser.php?course=$course_code&amp;group_id=$group->id",
        //                     'icon' => 'fa-plus-circle'),
        //                 array('title' => $visibility_text,
        //                     'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group->id&amp;$visibility_url",
        //                     'icon' => $visibility_icom),
        //                 array('title' => $langDelete,
        //                     'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$group->id",
        //                     'icon' => 'fa-times',
        //                     'class' => 'delete',
        //                     'confirm' => $langConfirmDelete))) .
        //                 "</td></tr>";
        //     $totalRegistered += $member_count;
        // }
        // $tool_content .= "</table></div><br>";

        $tool_content .= "<div class='col-12'>
                            <div class='row row-cols-1 row-cols-md-2 g-4'>";
                                $pagesPag = 0;
                                $allGroups = 0;
                                $temp_pages = 0;
                                $countCards = 1;
                                if($countCards == 1){
                                    $pagesPag++;
                                }
                                foreach ($groupSelect as $group) {
                                    if (!is_group_visible($group->id, $course_id)) {
                                        $link_class = 'not_visible';
                                    } else {
                                        $link_class = '';
                                    }
                                    initialize_group_info($group->id);
                                    if (is_group_visible($group->id, $course_id)) {
                                        $visibility_text = $langViewHide;
                                        $visibility_icom = 'fa-eye-slash';
                                        $visibility_url = 'choice=disable';
                                    } else {
                                        $visibility_text = $langViewShow;
                                        $visibility_icom = 'fa-eye';
                                        $visibility_url = 'choice=enable';
                                    }
                                    $tool_content .= "<div class='col cardGroup$pagesPag'>
                                                        <div class='card panelCard $link_class card$pagesPag px-lg-4 py-lg-3 h-100'>
                                                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <a class='ViewGroup TextBold' href='group_space.php?course=$course_code&amp;group_id=$group->id'>" . q($group_name) . "</a>
                                                                <div>
                                                                    " .
                                                                    action_button(array(
                                                                        array('title' => $langEditChange,
                                                                            'url' => "group_edit.php?course=$course_code&amp;category=$group->category_id&amp;group_id=$group->id",
                                                                            'icon' => 'fa-edit'),
                                                                        array('title' => $langAddManyUsers,
                                                                            'url' => "muladduser.php?course=$course_code&amp;group_id=$group->id",
                                                                            'icon' => 'fa-plus-circle'),
                                                                        array('title' => $visibility_text,
                                                                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group->id&amp;$visibility_url",
                                                                            'icon' => $visibility_icom),
                                                                        array('title' => $langDelete,
                                                                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$group->id",
                                                                            'icon' => 'fa-xmark',
                                                                            'class' => 'delete',
                                                                            'confirm' => $langConfirmDelete))) .
                                                                        "
                                                                </div>
                                                            </div>
                                                            <div class='card-body rounded-0'>";
                                                            $tool_content .= "<p class='form-label'>$langGroupTutor</p>";
                                                            if(count($tutors)>0){
                                                                foreach ($tutors as $t) {
                                                                    $tool_content .= display_user($t->user_id) . "</br><div class='mt-2'></div>";
                                                                }
                                                            }else{
                                                                $tool_content .= "<p class='small-text'>$langNoInfoAvailable</p>";
                                                            }


                                            $tool_content .= "<p class='form-label mt-3'>$langDescription</p>";
                                                                if(!empty($group_description)){
                                                                    $tool_content .= "<p class='small-text'>$group_description</p>";
                                                                }else{
                                                                    $tool_content .= "<p class='small-text'>$langNoInfoAvailable</p>";
                                                                }

                                                                if ($user_group_description && $student_desc) {
                                                                    $tool_content .= "<small><a href = 'javascirpt:void(0);' data-bs-toggle = 'modal' data-content='".q($user_group_description)."' data-bs-target = '#userFeedbacks' ><span class='fa fa-comments' ></span > $langCommentsUser</a ></small>";
                                                                }
                                        $tool_content .= "</div>
                                                            <div class='card-footer d-flex justify-content-end align-items-center border-0 pb-3'>";
                                                            if ($max_members > 0) {
                                                                $tool_content .= " <span class='badge bg-info text-white'>$langGroupMembersNum:&nbsp;$member_count/$max_members</span>";
                                                            } else {
                                                                $tool_content .= " <span class='badge bg-info text-white'>$langGroupMembersNum:&nbsp;$member_count</span>";
                                                            }
                                        $tool_content .= "</div>
                                                    </div>
                                                </div>";

                                    $totalRegistered += $member_count;


                                    if($countCards == 4 and $temp_pages < count($groupSelect)){
                                        $pagesPag++;
                                        $countCards = 0;
                                    }
                                    $countCards++;
                                    $allGroups++;
                                }



                                // Card's pagination
                                $tool_content .= "<input type='hidden' id='KeyallGroup' value='$allGroups'>
                                        <input type='hidden' id='KeypagesGroup' value='$pagesPag'>
                                        <div class='col-12'>
                                        <div class='col-12 d-flex justify-content-center overflow-auto mt-2 mb-3'>
                                            <nav aria-label='Page navigation example w-100'>
                                                <ul class='pagination mycourses-pagination w-100 mb-0'>
                                                    <li class='page-item page-item-previous'>
                                                        <a class='page-link' href='javascript:void(0);'><span class='fa-solid fa-chevron-left'></span></a>
                                                    </li>";
                                                    if($pagesPag >=12 ){
                                                        for($i=1; $i<=$pagesPag; $i++){

                                                            if($i>=1 && $i<=5){
                                                                if($i==1){
                                                                    $tool_content .="
                                                                        <li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                            <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                        </li>
                        
                                                                        <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                                            <a>...</a>
                                                                        </li>";
                                                                }else{
                                                                    if($i<$pagesPag){
                                                                        $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                            <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                        </li>";
                                                                    }
                                                                }
                                                            }

                                                            if($i>=6 && $i<=$pagesPag-1){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages d-none'>
                                                                                    <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                </li>";

                                                                if($i==$pagesPag-1){
                                                                    $tool_content .="<li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                                                        <a>...</a>
                                                                                    </li>";
                                                                }
                                                            }

                                                            if($i==$pagesPag){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                    <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                </li>";
                                                            }
                                                        }

                                                    }else{
                                                        for($i=1; $i<=$pagesPag; $i++){
                                                            $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                            </li>";
                                                        }
                                                    }

                                    $tool_content .="<li class='page-item page-item-next'>
                                                        <a class='page-link' href='javascript:void(0);'><span class='fa-solid fa-chevron-right'></span></a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div></div>";


                                $tool_content .= "
                                    <div class='modal fade' id='userFeedbacks' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
                                    <div class='modal-dialog' role='document'>
                                        <div class='modal-content'>
                                        <div class='modal-header'>
                                            <div class='modal-title' id='myModalLabel'>$langCommentsUser</div>
                                            <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                            
                                        </div>
                                        <div class='modal-body'>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                ";

        $tool_content .= "</div></div>";
    }
} else {
    // ************************************
    // Begin student view
    // ************************************
    if (($multi_reg == 0) and !$user_visible_groups) {
        $tool_content .= "<div class='col-12 mb-3'><h4>$langSingleGroupRegInfo</h4></div>";
    } else if ($multi_reg == 1) {
        $tool_content .= "<div class='col-12 mb-3'><h4>$langMultipleGroupRegInfo</h4></div>";
    } else if (($multi_reg == 2)) {
        $tool_content .= "<div class='col-12 mb-3'><h4>$langCategoryGroupRegInfo</h4></div>";
    }

    $q = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d AND (category_id = 0 OR category_id IS NULL) ORDER BY name", $course_id);
    if (count($q) == 0) {
        $tool_content .= "
            <div class='row'>
                <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default nocategory-links'>
                <thead><tr class='list-header'><th class='text-start list-header'>$langGroupTeam</th>
                </tr></thead>
                <tr><td class='not_visible nocategory-link'> - $langNoGroupInCategory - </td>
                </tr></table></div></div></div>";
    } else {
        // $tool_content .= "<div class='table-responsive'>
        //     <table class='table-default'>
        //         <tr class='list-header'>
        //           <th class='text-start'>$langGroupTeam</th>
        //           <th width='250'>$langGroupTutor</th>
        //           <th width='50'>$langGroupMembersNum</th>
        //           <th width='50'>$langMax</th>
        //           <th class='text-center' style='width:45px;'>".icon('fa-gears', $langActions)."</th>
        //         </tr>";
        // foreach ($q as $row) {
        //     $group_id = $row->id;

        //     initialize_group_info($group_id);
        //     // group visibility
        //     if (!is_group_visible($group_id, $course_id)) {
        //         continue;
        //     }

        //     $tool_content .= "<tr>";
        //     $tool_content .= "<td class='text-start'>";
        //     // Allow student to enter group only if he's a member
        //     if ($is_member or $is_tutor) {
        //         $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) .
        //                 "</a> <span class='TextBold text-uppercase Neutral-900-cl'>--$langMyGroup--</span>";
        //     } else {
        //         $full_group_message = '';
        //         if ($max_members > 0 and $max_members == $member_count) {
        //            $full_group_message = " <span class='TextBold text-uppercase Warning-200-cl'>--$langGroupFull--</span>";
        //         }
        //         $tool_content .= q($group_name) . "$full_group_message";
        //     }
        //     $tool_content .= "<br><p style='padding-top:10px;'>$group_description</p>";
        //     if ($student_desc) {
        //         if ($user_group_description) {
        //             $tool_content .= "<br><span class='small'><i>$user_group_description</i></span>&nbsp;&nbsp;" .
        //                     icon('fa-edit', $langModify, "group_description.php?course=$course_code&amp;group_id=$group_id") . "&nbsp;" .
        //                     icon('fa-times', $langDelete, "group_description.php?course=$course_code&amp;group_id=$group_id&amp;delete=true", 'onClick="return confirmation();"');
        //         } elseif ($is_member) {
        //             $tool_content .= "<br><a href='group_description.php?course=$course_code&amp;group_id=$group_id'><i>$langAddDescription</i></a>";
        //         }
        //     }
        //     $tool_content .= "</td>";
        //     $tool_content .= "<td class='text-center'>";
        //     foreach ($tutors as $t) {
        //         $tool_content .= display_user($t->user_id) . "<br>";
        //     }
        //     $tool_content .= "</td>";
        //     $tool_content .= "<td class='text-center'>$member_count</td><td class='text-center'>" .
        //             ($max_members ? $max_members : '&mdash;') . "</td>";
        //     // If self-registration and multi registration allowed by admin and group is not full
        //     $tool_content .= "<td class='text-center'>";
        //     $group_id_indirect = getIndirectReference($group_id);
        //     $control = '';

        //     if ($uid) {
        //         if (!$is_member) {
        //             if (($multi_reg == 0) and (!$user_visible_groups)) {
        //                 $user_can_register_to_group = true;
        //             } else if ($multi_reg == 1 or $multi_reg == 2) {
        //                 $user_can_register_to_group = true;
        //             } else {
        //                 $user_can_register_to_group = false;
        //             }
        //             if ($self_reg and $user_can_register_to_group and (!$max_members or $member_count < $max_members)) {
        //                 $control = icon('fa-sign-in', $langRegister, "group_space.php?course=$course_code&amp;selfReg=1&amp;group_id=$group_id_indirect");
        //             }
        //         } elseif ($allow_unreg) {
        //             $control = icon('fa-sign-out', $langUnRegister, "group_space.php?course=$course_code&amp;selfUnReg=1&amp;group_id=$group_id_indirect", " style='color:#d9534f;'");
        //         }
        //     }
        //     $tool_content .= ($control? $control: '&mdash;') . "</td></tr>";
        //     $totalRegistered += $member_count;
        // }
        // $tool_content .= "</table></div>";



        $pagesPag = 0;
        $allGroups = 0;
        $temp_pages = 0;
        $countCards = 1;
        if($countCards == 1){
            $pagesPag++;
        }
        $tool_content .= "<div class='col-12'>
                            <div class='row row-cols-1 row-cols-md-2 g-4'>";
                                foreach ($q as $row) {
                                    $group_id = $row->id;

                                    initialize_group_info($group_id);

                                    $temp_pages++;

                                    // group visibility
                                    if (!is_group_visible($group_id, $course_id)) {
                                        continue;
                                    }
                                    $tool_content .= "<div class='col cardGroup$pagesPag'>
                                                      <div class='card panelCard card$pagesPag px-lg-4 py-lg-3 h-100'>
                                                        <div class='card-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-3'>";
                                                                if ($is_member or $is_tutor) {
                                                                $tool_content .= "<a class='ViewGroup TextBold' href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) .
                                                                                    "</a> 
                                                                                    <span class='badge bg-success TextBold text-white text-capitalize'>$langMyGroup</span>";
                                                                } else {
                                                                    $full_group_message = '';
                                                                    if ($max_members > 0 and $max_members == $member_count) {
                                                                        $full_group_message = " <span class='badge bg-warning TextBold text-white text-capitalize'>$langGroupFull</span>";
                                                                    }
                                                                        $tool_content .= "<h3 class='mb-0'>".q($group_name)."</h3>" . "$full_group_message";
                                                                }
                                        $tool_content .= "</div>
                                                        <div class='card-body rounded-0'>
                                                                <p class='form-label'>$langGroupTutor</p>";

                                                                if(count($tutors) > 0){
                                                                    foreach ($tutors as $t) {
                                                                        $tool_content .= display_user($t->user_id) . "</br><div class='mt-2'></div>";
                                                                    }
                                                                }else{
                                                                    $tool_content .= "<p class='small-text'>$langNoInfoAvailable</p>";
                                                                }
                                            $tool_content .= "<p class='form-label mt-3'>$langDescription</p>";
                                                                if(!empty($group_description)){
                                                                    $tool_content .= "<p class='small-text'>$group_description</p>";
                                                                }else{
                                                                    $tool_content .= "<p class='small-text'>$langNoInfoAvailable</p>";
                                                                }
                                                                if ($student_desc) {
                                                                    if ($user_group_description) {
                                                                        $tool_content .= "<br><span class='small'><i>$user_group_description</i></span>&nbsp;&nbsp;" .
                                                                                icon('fa-edit pe-2', $langModify, "group_description.php?course=$course_code&amp;group_id=$group_id") . "&nbsp;" .
                                                                                icon('fa-xmark link-delete', $langDelete, "group_description.php?course=$course_code&amp;group_id=$group_id&amp;delete=true", 'onClick="return confirmation();"');
                                                                    } elseif ($is_member) {
                                                                        $tool_content .= "<br><a href='group_description.php?course=$course_code&amp;group_id=$group_id'><i>$langAddDescription</i></a>";
                                                                    }
                                                                }

                                        $tool_content .= "</div>
                                                        <div class='card-footer d-flex border-0 justify-content-between align-items-center flex-wrap gap-3'>";
                                                                $group_id_indirect = getIndirectReference($group_id);
                                                                $control = '';

                                                                if ($uid) {
                                                                    if (!$is_member) {
                                                                        if (($multi_reg == 0) and (!$user_visible_groups)) {
                                                                            $user_can_register_to_group = true;
                                                                        } else if ($multi_reg == 1 or $multi_reg == 2) {
                                                                            $user_can_register_to_group = true;
                                                                        } else {
                                                                            $user_can_register_to_group = false;
                                                                        }
                                                                        if ($self_reg and $user_can_register_to_group and (!$max_members or $member_count < $max_members)) {
                                                                            $control = icon('fa-sign-in fs-4', $langRegister, "group_space.php?course=$course_code&amp;selfReg=1&amp;group_id=$group_id_indirect");
                                                                        }
                                                                    } elseif ($allow_unreg) {
                                                                        $control = icon('fa-sign-out Accent-200-cl fs-4', $langUnRegister, "group_space.php?course=$course_code&amp;selfUnReg=1&amp;group_id=$group_id_indirect", " style='color:#d9534f;'");
                                                                    }
                                                                }
                                                                $tool_content .= ($control? $control: '&mdash;');
                                                                if ($max_members == 0) {
                                                                    $tool_content .= " <span class='badge bg-info text-white'>$langGroupMembersNum:&nbsp; -- </span>";
                                                                } else {
                                                                    $tool_content .= " <span class='badge bg-info text-white'>$langGroupMembersNum:&nbsp;$member_count/$max_members</span>";
                                                                }
                                        $tool_content .= "</div>
                                                    </div>
                                                   </div>";


                                    $totalRegistered += $member_count;

                                    if($countCards == 4 and $temp_pages < count($q)){
                                        $pagesPag++;
                                        $countCards = 0;
                                    }
                                    $countCards++;
                                    $allGroups++;
                                }

                                // Card's pagination
                                $tool_content .= "<input type='hidden' id='KeyallGroup' value='$allGroups'>
                                        <input type='hidden' id='KeypagesGroup' value='$pagesPag'>
                                        <div class='col-12'>
                                        <div class='col-12 d-flex justify-content-center overflow-auto mt-2 mb-3'>
                                            <nav aria-label='Page navigation example w-100'>
                                                <ul class='pagination mycourses-pagination w-100 mb-0'>
                                                    <li class='page-item page-item-previous'>
                                                        <a class='page-link' href='javascript:void(0);'><span class='fa-solid fa-chevron-left'></span></a>
                                                    </li>";
                                                    if($pagesPag >=12 ){
                                                        for($i=1; $i<=$pagesPag; $i++){

                                                            if($i>=1 && $i<=5){
                                                                if($i==1){
                                                                    $tool_content .="
                                                                        <li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                            <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                        </li>
                        
                                                                        <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                                            <a>...</a>
                                                                        </li>";
                                                                }else{
                                                                    if($i<$pagesPag){
                                                                        $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                            <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                        </li>";
                                                                    }
                                                                }
                                                            }

                                                            if($i>=6 && $i<=$pagesPag-1){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages d-none'>
                                                                                    <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                </li>";

                                                                if($i==$pagesPag-1){
                                                                    $tool_content .="<li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                                                        <a>...</a>
                                                                                    </li>";
                                                                }
                                                            }

                                                            if($i==$pagesPag){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                    <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                                </li>";
                                                            }
                                                        }

                                                    }else{
                                                        for($i=1; $i<=$pagesPag; $i++){
                                                            $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                <a id='Keypage$i' class='page-link' href='javascript:void(0);'>$i</a>
                                                                            </li>";
                                                        }
                                                    }

                                    $tool_content .="<li class='page-item page-item-next'>
                                                        <a class='page-link' href='javascript:void(0);'><span class='fa-solid fa-chevron-right'></span></a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div></div>";

        $tool_content .= "</div>
                        </div>";



    }
}

    if (!in_array($action, array('addcategory', 'editcategory'))) {
        $numberofzerocategory = count(Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND (category_id = 0 OR category_id IS NULL)", $course_id));
        $cat = Database::get()->queryArray("SELECT * FROM `group_category` WHERE course_id = ?d ORDER BY `name`", $course_id);
        $aantalcategories = count($cat);
        $tool_content .= "<div class='col-sm-12'><div class='table-responsive'>";
        if($cat){
           $tool_content .= "<div class='margin-bottom-thin border-bottom-table-head py-2 px-2'>";
        }else{
            $tool_content .= "<div class='margin-bottom-thin border-bottom-table-head py-2 px-2'>";
        }
        if ($aantalcategories > 0) {
            $tool_content .= "<span class='form-label'>$langCategorisedGroups</span>&nbsp;";
            if (isset($urlview) and abs(intval($urlview)) == 0) {
                $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('1', $aantalcategories) . $socialview_param . "'>" . icon('fa-plus-square', $langViewShow)."</a>";
            } else {
                $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=" . str_repeat('0', $aantalcategories) . $socialview_param . "'>" .icon('fa-minus-square', $langViewHide)."</a>";
            }
        }
        $tool_content .= "</div>            
            <table class='table-default category-links rounded-0'>";

    if ($urlview === '') {
            $urlview = str_repeat('0', $aantalcategories);
        }
        $i = 0;
        foreach ($cat as $myrow) {
            if (empty($urlview)) {
                // No $view set in the url, thus for each category link it should be all zeros except it's own
                $view = makedefaultviewcode($i);
            } else {
                $view = $urlview;
                $view[$i] = '1';
            }
            // if the $urlview has a 1 for this category, this means it is expanded and should be displayed as a
            // - instead of a +, the category is no longer clickable and all the links of this category are displayed
            $description = standard_text_escape($myrow->description);
            if ((isset($urlview[$i]) and $urlview[$i] == '1')) {
                $newurlview = $urlview;
                $newurlview[$i] = '0';
                $tool_content .= "<tr class='link-subcategory-title'>
                                    <th class = 'text-start category-link px-2' colspan='4'>
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=$newurlview$socialview_param' class='open-category'>".icon('fa-solid fa-minus-square', $langViewHide)."&nbsp;&nbsp;". q($myrow->name) . "</a>";
                if (!empty($description)) {
                    $tool_content .= "<br><span class='link-description'>$description</span></th>";
                } else {
                    $tool_content .= "</th>";
                }

                if ($is_editor) {
                    $tool_content .= "<td class='option-btn-cell text-end'>";
                    showgroupcategoryadmintools($myrow->id);
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td>&nbsp;</td>";
                }

                $tool_content .= "</tr>";
                // display category groups
                showgroupsofcategory($myrow->id);
            } else {
                $tool_content .= "
                        <tr class='link-subcategory-title'>
                            <th class = 'text-start category-link px-2' colspan='4'>&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;urlview=";
                $tool_content .= is_array($view) ? implode('', $view) : $view;
                $tool_content .= "' class='open-category'>".icon('fa-plus-square', $langViewShow)
                    . "&nbsp;&nbsp;" . q($myrow->name) . "</a>";
                $description = standard_text_escape($myrow->description);
                if (!empty($description)) {
                    $tool_content .= "<br><span class='link-description'>$description</span</th>";
                } else {
                    $tool_content .= "</th>";
                }

                if ($is_editor) {
                    $tool_content .= "<td class='option-btn-cell text-end'>";
                    showgroupcategoryadmintools($myrow->id);
                    $tool_content .= "</td>";
                }
                $tool_content .= "</tr>";
            }
            $i++;
        }
    $tool_content .= "</table></div></div>";
    add_units_navigation(TRUE);
}


$head_content .= "<script type='text/javascript'>
$(document).ready(function() {
var arrayLeftRight = [];

// init page1
if(arrayLeftRight.length == 0){
    var totalGroups = $('#KeyallGroup').val();
    
    for(j=1; j<=totalGroups; j++){
        if(j!=1){
            $('.cardGroup'+j).removeClass('d-block');
            $('.cardGroup'+j).addClass('d-none');
        }else{
            $('.page-item-previous').addClass('disabled');
            $('.cardGroup'+j).removeClass('d-none');
            $('.cardGroup'+j).addClass('d-block');
            $('#Keypage1').addClass('active');
        }
    }
    var totalPages = $('#KeypagesGroup').val();
    if(totalPages == 1){
        $('.page-item-previous').addClass('disabled');
        $('.page-item-next').addClass('disabled');
    }
}


// prev-button
$('.page-item-previous .page-link').on('click',function(){

    var prevPage;

    $('.page-item-pages .page-link.active').each(function(index, value){
        var IDCARD = this.id;
        var number = parseInt(IDCARD.match(/\d+/g));
        prevPage = number-1;

        arrayLeftRight.push(number);

        var totalGroups = $('#KeyallGroup').val();
        var totalPages = $('#KeypagesGroup').val();
        for(i=1; i<=totalGroups; i++){
            if(i == prevPage){
                $('.cardGroup'+i).removeClass('d-none');
                $('.cardGroup'+i).addClass('d-block');
                $('#Keypage'+prevPage).addClass('active');
            }else{
                $('.cardGroup'+i).removeClass('d-block');
                $('.cardGroup'+i).addClass('d-none');
                $('#Keypage'+i).removeClass('active');
            }
        }

        if(prevPage == 1){
            $('.page-item-previous').addClass('disabled');
        }else{
            if(prevPage < totalPages){
                $('.page-item-next').removeClass('disabled');
            }
            $('.page-item-previous').removeClass('disabled');
        }


        //create page-link in center
        if(number <= totalPages-3 && number >= 6 && totalPages>=12){

            $('#KeystartLi').removeClass('d-none');
            $('#KeystartLi').addClass('d-block');
            
            for(i=2; i<=totalPages-1; i++){
                $('#KeypageCenter'+i).removeClass('d-block');
                $('#KeypageCenter'+i).addClass('d-none');
            }

            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

            var currentPage = number-1;
            $('#KeypageCenter'+currentPage).removeClass('d-none');
            $('#KeypageCenter'+currentPage).addClass('d-block');

            var prevPage = number-2;
            $('#KeypageCenter'+prevPage).removeClass('d-none');
            $('#KeypageCenter'+prevPage).addClass('d-block');

            $('#KeycloseLi').removeClass('d-none');
            $('#KeycloseLi').addClass('d-block');

        }else if(number <= 5 && totalPages>=12){

            $('#KeystartLi').removeClass('d-block');
            $('#KeystartLi').addClass('d-none');

            for(i=6; i<=totalPages-1; i++){
                $('#KeypageCenter'+i).removeClass('d-block');
                $('#KeypageCenter'+i).addClass('d-none');
            }

            $('#KeycloseLi').removeClass('d-none');
            $('#KeycloseLi').addClass('d-block');

            
            for(i=1; i<=number; i++){
                $('#KeypageCenter'+i).removeClass('d-none');
                $('#KeypageCenter'+i).addClass('d-block');
            }

        }

    });

});




// next-button
$('.page-item-next .page-link').on('click',function(){

    $('.page-item-pages .page-link.active').each(function(index, value){
        var IDCARD = this.id;
        var number = parseInt(IDCARD.match(/\d+/g));
        arrayLeftRight.push(number);
        var nextPage = number+1;

        var delPageActive = nextPage-1;
        $('#Keypage'+delPageActive).removeClass('active');
        $('#Keypage'+nextPage).addClass('active');
    
        var totalGroups = $('#KeyallGroup').val();
        var totalPages = $('#KeypagesGroup').val();
        
        for(i=1; i<=totalGroups; i++){
            if(i == nextPage){
                $('.cardGroup'+i).removeClass('d-none');
                $('.cardGroup'+i).addClass('d-block');
                // $('#Keypage'+nextPage).addClass('active');
            }else{
                $('.cardGroup'+i).removeClass('d-block');
                $('.cardGroup'+i).addClass('d-none');
                //$('#Keypage'+i).removeClass('active');
            }
        }

        if(totalPages > 1){
            $('.page-item-previous').removeClass('disabled');
        }
        if(nextPage == totalPages){
            $('.page-item-next').addClass('disabled');
        }else{
            $('.page-item-next').removeClass('disabled');
        }


        //create page-link in center
        if(number >= 4 && number < totalPages-5 && totalPages>=12){//5-7

            $('#KeystartLi').removeClass('d-none');
            $('#KeystartLi').addClass('d-block');
            
            for(i=2; i<=totalPages-1; i++){
                $('#KeypageCenter'+i).removeClass('d-block');
                $('#KeypageCenter'+i).addClass('d-none');
            }

            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

            var currentPage = number+1;
            $('#KeypageCenter'+currentPage).removeClass('d-none');
            $('#KeypageCenter'+currentPage).addClass('d-block');

            var nextPage = number+2;
            $('#KeypageCenter'+nextPage).removeClass('d-none');
            $('#KeypageCenter'+nextPage).addClass('d-block');

            $('#KeycloseLi').removeClass('d-none');
            $('#KeycloseLi').addClass('d-block');

        }else if(arrayLeftRight[arrayLeftRight.length-1] >= totalPages-5 && totalPages>=12){//>=8

            $('#KeystartLi').removeClass('d-none');
            $('#KeystartLi').addClass('d-block');

            for(i=2; i<=totalPages-5; i++){
                $('#KeypageCenter'+i).removeClass('d-block');
                $('#KeypageCenter'+i).addClass('d-none');
            }

            $('#KeycloseLi').removeClass('d-block');
            $('#KeycloseLi').addClass('d-none');

            var nextPage = arrayLeftRight[arrayLeftRight.length-1] + 1;
            for(i=nextPage; i<=totalPages; i++){
                $('#KeypageCenter'+i).removeClass('d-none');
                $('#KeypageCenter'+i).addClass('d-block');
            }

        }else if(number>=1 && number<=4 && totalPages>=12){
            $('#KeystartLi').removeClass('d-block');
            $('#KeystartLi').addClass('d-none');

            for(i=1; i<=4; i++){
                $('#KeypageCenter'+i).removeClass('d-none');
                $('#KeypageCenter'+i).addClass('d-block');
            }
        }

        
    });
});




// page-link except prev-next button
$('.page-item-pages .page-link').on('click',function(){
    
    var IDCARD = this.id;
    var number = parseInt(IDCARD.match(/\d+/g));

    arrayLeftRight.push(number);

    var totalPages = $('#KeypagesGroup').val();
    var totalGroups = $('#KeyallGroup').val();
    for(i=1; i<=totalGroups; i++){
        if(i!=number){
            $('.cardGroup'+i).removeClass('d-block');
            $('.cardGroup'+i).addClass('d-none');
        }else{
            $('.cardGroup'+i).removeClass('d-none');
            $('.cardGroup'+i).addClass('d-block');
        }

        // about prev-next button
        if(number>1){
            $('.page-item-previous').removeClass('disabled');
            $('.page-item-next').removeClass('disabled');
        }if(number == 1){
            if(totalPages == 1){
                $('.page-item-previous').addClass('disabled');
                $('.page-item-next').addClass('disabled');
            }
            if(totalPages > 1){
                $('.page-item-previous').addClass('disabled');
                $('.page-item-next').removeClass('disabled');
            }
        }if(number == totalPages){
            $('.page-item-next').addClass('disabled');
        }if(number < totalPages-1){
            $('.page-item-next').removeClass('disabled');
        }
    }

   
    if(number>=1 && number<=4 && totalPages>=12){

        $('#KeystartLi').removeClass('d-block');
        $('#KeystartLi').addClass('d-none');

        for(i=1; i<=5; i++){
            $('#KeypageCenter'+i).removeClass('d-none');
            $('#KeypageCenter'+i).addClass('d-block'); 
        }
        for(i=6; i<=totalPages-1; i++){
            $('#KeypageCenter'+i).removeClass('d-block');
            $('#KeypageCenter'+i).addClass('d-none');
        }

        $('#KeycloseLi').removeClass('d-none');
        $('#KeycloseLi').addClass('d-block');
    }
    if(number>=5 && number<=totalPages-5 && totalPages>=12){

        for(i=5; i<=totalPages-1; i++){
            $('#KeypageCenter'+i).removeClass('d-block');
            $('#KeypageCenter'+i).addClass('d-none');
        }

        var prevPage = number-1;
        var nextPage = number+1;
        var currentPage = number;

        $('#KeystartLi').removeClass('d-none');
        $('#KeystartLi').addClass('d-block');

        for(i=2; i<=4; i++){
            $('#KeypageCenter'+i).removeClass('d-block');
            $('#KeypageCenter'+i).addClass('d-none');
        }

        $('#KeypageCenter'+prevPage).removeClass('d-none');
        $('#KeypageCenter'+prevPage).addClass('d-block');

        $('#KeypageCenter'+currentPage).removeClass('d-none');
        $('#KeypageCenter'+currentPage).addClass('d-block');

        $('#KeypageCenter'+nextPage).removeClass('d-none');
        $('#KeypageCenter'+nextPage).addClass('d-block');

        $('#KeycloseLi').removeClass('d-none');
        $('#KeycloseLi').addClass('d-block');

    }
    if(number>=totalPages-4 && totalPages>=12){

        $('#KeystartLi').removeClass('d-none');
        $('#KeystartLi').addClass('d-block');

        for(i=2; i<=totalPages-5; i++){
            $('#KeypageCenter'+i).removeClass('d-block');
            $('#KeypageCenter'+i).addClass('d-none');
        }

        for(i=totalPages-4; i<=totalPages; i++){
            $('#KeypageCenter'+i).removeClass('d-none');
            $('#KeypageCenter'+i).addClass('d-block');
        }


        $('#KeycloseLi').removeClass('d-block');
        $('#KeycloseLi').addClass('d-none');
    }
    if(number==totalPages-4 && arrayLeftRight[arrayLeftRight.length-2]>number && totalPages>=12){

        $('#KeystartLi').removeClass('d-none');
        $('#KeystartLi').addClass('d-block');

        for(i=2; i<=totalPages-1; i++){
            $('#KeypageCenter'+i).removeClass('d-block');
            $('#KeypageCenter'+i).addClass('d-none');
        }

        var prevPage = number+1;
        var nextPage = number-1;
        var currentPage = number;

        $('#KeypageCenter'+prevPage).removeClass('d-none');
        $('#KeypageCenter'+prevPage).addClass('d-block');

        $('#KeypageCenter'+currentPage).removeClass('d-none');
        $('#KeypageCenter'+currentPage).addClass('d-block');

        $('#KeypageCenter'+nextPage).removeClass('d-none');
        $('#KeypageCenter'+nextPage).addClass('d-block');

        $('#KeycloseLi').removeClass('d-none');
        $('#KeycloseLi').addClass('d-block');
    }


    // about active page-item
    $('.page-item-pages .page-link').each(function(index, value){
        $('.page-item-pages .page-link').removeClass('active');
    });
    $(this).addClass('active');

});
});
</script>";











draw($tool_content, 2, null, $head_content);
