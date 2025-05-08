<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once 'include/course_settings.php';

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

/**
 * @brief group info initialization
 * @param type $group_id
 */
function initialize_group_info($group_id) {

    global $course_id, $is_editor, $status, $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $wiki,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors, $group_category,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $user_group_description, $course_code, $public_users_list, $booking;

    $grp_property_item = Database::get()->querySingle("SELECT self_registration, allow_unregister, forum, private_forum, documents, wiki, public_users_list, booking
                     FROM group_properties WHERE course_id = ?d AND group_id = ?d", $course_id, $group_id);
    $self_reg = $grp_property_item->self_registration;
    $allow_unreg = $grp_property_item->allow_unregister;
    $has_forum = $grp_property_item->forum;
    $private_forum = $grp_property_item->private_forum;
    $documents = $grp_property_item->documents;
    $wiki = $grp_property_item->wiki;
    $public_users_list = $grp_property_item->public_users_list;
    $booking = $grp_property_item->booking;

    // Guest users aren't allowed to register / unregister
    if ($status == USER_GUEST) {
        $self_reg = $allow_unreg = 0;
    }

    $res = Database::get()->querySingle("SELECT name, description, forum_id, max_members, secret_directory, category_id
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
    $group_category = $res->category_id;

    $member_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM group_members
                                                                    WHERE group_id = ?d
                                                                    AND is_tutor = 0", $group_id)->count;

    $tutors = group_tutors($group_id);

    $is_tutor = $is_member = FALSE;
    $user_group_description = NULL;

    if (isset($uid)) { // check if we are group_member
        $res = Database::get()->querySingle("SELECT user_id FROM group_members
                                     WHERE group_id = ?d AND user_id = ?d", $group_id, $uid);
        if ($res) {
            $is_member = TRUE;
        }
        // check if we are group tutor
        $res = Database::get()->querySingle("SELECT is_tutor FROM group_members
                                     WHERE group_id = ?d AND user_id = ?d AND is_tutor = 1", $group_id, $uid);
        if ($res) {
            $is_tutor = $res->is_tutor;
        }
    }

    // check description
    if ($is_tutor || $is_editor) {
        $res = Database::get()->queryArray("SELECT description,user_id FROM group_members
                                     WHERE group_id = ?d", $group_id);
        foreach ($res as $d) {
            if (!empty($d->description) or $d->description != '') {
                $user_group_description .= display_user($d->user_id, false, false)."<br>$d->description<br><br>";
            }
        }
    } else {
        if (isset($uid)) {
            $res = Database::get()->querySingle("SELECT description FROM group_members
                                         WHERE group_id = ?d AND user_id = ?d AND is_tutor != 1", $group_id, $uid);
            if ($res) {
                $user_group_description .= $res->description;
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

/**
 *
 * @param type $categoryid
 */

function showgroupcategoryadmintools($categoryid) {
    global $langDelete, $langEditChange, $langGroupCatDel, $tool_content, $course_code;

    $tool_content .= action_button(array(
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
                      'url' => "group_category.php?course=$course_code&amp;editcategory=1&amp;id=$categoryid"),
                array('title' => $langDelete,
                        'icon' => 'fa-xmark',
                        'url' => "index.php?course=$course_code&amp;deletecategory=1&amp;id=$categoryid",
                        'class' => 'delete',
                        'confirm' => $langGroupCatDel)
                ));
}


/**
 * @brief display groups of specified category
 * @param type $catid
 */
function showgroupsofcategory($catid) {

    global $is_editor, $course_id, $tool_content, $langUnRegister, $is_tutor,
        $course_code, $langGroupDelconfirm, $langDelete, $langRegister, $member_count,
        $langModify, $is_member, $multi_reg, $langMyGroup, $langAddDescription,
        $langEditChange, $uid, $totalRegistered, $student_desc, $allow_unreg,
        $tutors, $group_name, $self_reg, $user_group_description, $user_visible_groups,
        $max_members, $group_description, $langCommentsUser, $langViewHide, $langViewShow,
        $langAddManyUsers;

    $multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);

    $q = Database::get()->queryArray("SELECT id FROM `group`
                                   WHERE course_id = ?d AND category_id = ?d
                                   ORDER BY `name`", $course_id, $catid);

    foreach ($q as $row) {
        $group_id = $row->id;
        initialize_group_info($group_id);

        // group visibility
        if (!is_group_visible($group_id, $course_id) and !$is_editor) {
            continue;
        }

        if (!is_group_visible($group_id, $course_id)) {
            $link_class = 'not_visible';
        } else {
            $link_class = '';
        }
        $tool_content .= "<tr class='$link_class'><td style='padding-left: 25px;'>";
        if ($is_editor) {
            $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) . "</a>";
        } else {
            if ($is_member or $is_tutor) {
                $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) . "</a>";
                $tool_content .= "&nbsp;&nbsp;<span class='badge Primary-600-bg p-1 mt-2'>$langMyGroup</span>";
            } else {
                $tool_content .= q($group_name);
            }
        }
        $tool_content .= "<br><p style='padding-top:10px;'>$group_description</p>";
        if (!$is_editor) {
            if ($student_desc) {
                if ($user_group_description) {
                    $tool_content .= "<br>
                            <span class='small'>$user_group_description</span> &nbsp; <div class='d-flex flex-wrap gap-2 mt-2'>" .
                            icon('fa-edit', $langModify, "group_description.php?course=$course_code&amp;group_id=$group_id") . "" .
                            icon('fa-xmark Accent-200-cl', $langDelete, "group_description.php?course=$course_code&amp;group_id=$group_id&amp;delete=true", 'onClick="return confirmation();"') ."</div>";
                } elseif ($is_member) {
                    $tool_content .= "
                            <a href='group_description.php?course=$course_code&amp;group_id=$group_id'>
                                <i>$langAddDescription</i>
                            </a>";
                }
            }
        } else {
            if ($user_group_description && $student_desc) {
                $tool_content .= "<small><a href='javascript:void(0);' data-bs-toggle='modal' data-bs-html='true' data-bs-content='".q($user_group_description)."' data-bs-target='#userFeedbacks'><span class='fa fa-comments'></span > $langCommentsUser</a></small>";
            }
        }
        $tool_content .= "</td>";
        $tool_content .= "<td class='text-start' width='250'>";
        foreach ($tutors as $t) {
            $tool_content .= display_user($t->user_id) . "<div class='mb-2'></div>";
        }
        $tool_content .= "</td>";

        if ($catid == -2) {
            $rating = new Rating('thumbs_up', 'group', $group_id);
            $tool_content .= $rating->put($is_editor, $uid, $course_id);
        }
        $tool_content .= "<td width='50'>$member_count</td><td width='50'>" .
                ($max_members ? $max_members : '-') . "</td>";
        $totalRegistered += $member_count;

        if ($is_editor) {
            if (is_group_visible($group_id, $course_id)) {
                $visibility_text = $langViewHide;
                $visibility_icom = 'fa-eye-slash';
                $visibility_url = 'choice=disable';
            } else {
                $visibility_text = $langViewShow;
                $visibility_icom = 'fa-eye';
                $visibility_url = 'choice=enable';
            }
            $tool_content .= "<td class='option-btn-cell text-end'>";
            $tool_content .= action_button(array(
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
                      'url' => "group_edit.php?course=$course_code&amp;category=$catid&amp;group_id=$group_id"),
                array('title' => $langAddManyUsers,
                    'url' => "muladduser.php?course=$course_code&amp;group_id=$group_id",
                    'icon' => 'fa-plus-circle'),
                array('title' => $visibility_text,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group_id&amp;$visibility_url",
                    'icon' => $visibility_icom),
                array('title' => $langDelete,
                      'icon' => 'fa-xmark',
                      'class' => 'delete',
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$group_id",
                      'confirm' => $langGroupDelconfirm)
            ));
            $tool_content .= "</td>";
        } else {
            // If self-registration and multi registration allowed by admin and group is not full
            $tool_content .= "<td>";
            $group_id_indirect = getIndirectReference($group_id);
            $control = '';

            if ($uid) {
                if (!$is_member) {
                    if (($multi_reg == 0) and (!$user_visible_groups)) {
                        $user_can_register_to_group = true;
                    } else if ($multi_reg == 1) {
                        $user_can_register_to_group = true;
                    } else if (($multi_reg == 2) and (is_user_register_to_group_category_course($uid, $catid, $course_id))) {
                        $user_can_register_to_group = true;
                    } else {
                        $user_can_register_to_group = false;
                    }
                    if ($self_reg and $user_can_register_to_group and (!$max_members or $member_count < $max_members)) {
                        $control = icon('fa-sign-in', $langRegister, "group_space.php?course=$course_code&amp;selfReg=1&amp;group_id=$group_id_indirect");
                    }
                } elseif ($allow_unreg) {
                    $control = icon('fa-sign-out Accent-200-cl', $langUnRegister, "group_space.php?course=$course_code&amp;selfUnReg=1&amp;group_id=$group_id_indirect", " style='color:#d9534f;'");
                }
            }
            $tool_content .= ($control? $control: '&mdash;') . "</td>";
        }
        $tool_content .= "</tr>";
    }
}

/**
 * @brief submit new group category
 * @global type $course_id
 * @global type $langCategoryAdded
 * @global type $langCategoryModded
 * @global type $categoryname
 * @global type $description
 * @global type $langFormErrors
 * @global type $course_code
 */
function submit_group_category() {
    global $course_id, $langTheFieldIsRequired,
           $categoryname, $description, $langFormErrors,
           $course_code;

    register_posted_variables(array('categoryname' => true,
                                    'description' => true), 'all', 'trim');
    $set_sql = "SET name = ?s, description = ?s";
    $terms = array($categoryname, purify($description));
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('categoryname'))->message($langTheFieldIsRequired)->label('');
    if($v->validate()) {
        if (isset($_POST['id'])) {
                $id = getDirectReference($_POST['id']);
                Database::get()->query("UPDATE `group_category` $set_sql WHERE course_id = ?d AND id = ?d", $terms, $course_id, $id);
                $log_type = LOG_MODIFY;
        } else {
                $id = Database::get()->query("INSERT INTO `group_category` $set_sql, course_id = ?d", $terms, $course_id)->lastInsertID;
                $log_type = LOG_INSERT;
        }
        $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
        Log::record($course_id, MODULE_ID_LINKS, $log_type, array('id' => $id,
                                                                  'category' => $categoryname,
                                                                  'description' => $txt_description));
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/group/group_category.php?course=$course_code&addcategory=1");
    }
}

function category_form_defaults($id) {
    global $course_id, $form_name, $form_description;

    $myrow = Database::get()->querySingle("SELECT name,description FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($myrow) {
        $form_name = ' value="' . q($myrow->name) . '"';
        $form_description = q($myrow->description);
    } else {
        $form_name = $form_description = '';
    }
}

function makedefaultviewcode($locatie, $aantalcategories) {
    $view = str_repeat('0', $aantalcategories);
    $view[$locatie] = '1';
    return $view;
}

/**
 * @brief delete group
 * @global type $course_id
 * @param type $id
 */
function delete_group($id) {
    global $course_id;

    $tuple = Database::get()->querySingle("SELECT name, category_id FROM `group` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $name = $tuple->name;
    $category_id = $tuple->category_id;

    Database::get()->query("DELETE FROM `group` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('id' => $id,
                                                                'name' => $name));
}

/**
 * @brief delete group category
 * @global type $course_id
 * @global type $langGroupCategoryDeleted
 * @param type $id
 */
function delete_group_category($id) {
    global $course_id;

    Database::get()->query("DELETE FROM `group` WHERE course_id = ?d AND category_id = ?d", $course_id, $id);
    $category = Database::get()->querySingle("SELECT name FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id)->name;
    Database::get()->query("DELETE FROM `group_category` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('cat_id' => $id,
                                                               'category' => $category));
}

/**
 * @brief check whether user can register to another group in a course
 * @param type $uid
 * @param type $course_id
 * @return bool
 */
function user_can_register_to_group($uid, $course_id)
{
    $multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);
    if ($multi_reg) {
        return true; // user can register to unlimited groups
    }
    $q = Database::get()->querySingle('SELECT group_id
        FROM `group`, group_members
        WHERE `group`.id = group_members.group_id
            AND `group`.course_id = ?d
            AND group_members.is_tutor = 0
            AND group_members.user_id = ?d
        LIMIT 1', $course_id, $uid);
    if ($q and $q->group_id) {
        return false; // user can register to single group and already registered
    }
    return true;
}


/**
 * @brief change group visibility
 * @param $choice
 * @param $group_id
 * @param $course_id
 */
function change_group_visibility($choice, $group_id, $course_id) {

    if ($choice == 'enable') {
        $vis = 1;
    } else {
        $vis = 0;
    }
    Database::get()->query("UPDATE `group` SET visible = ?d
                                      WHERE id = ?d
                                      AND course_id = ?d",
                                $vis, $group_id, $course_id);

}

/**
 * @brief check if course has group categories
 * @param $course_id
 * @return bool
 */
function has_group_categories($course_id) {

    $q = Database::get()->querySingle("SELECT * FROM `group` WHERE
                                      category_id > 0
                                      AND course_id = ?d", $course_id);
    if ($q) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief check whether is registered to group belonging to category
 * @param $uid
 * @param $category_id
 * @param $course_id
 */
function is_user_register_to_group_category_course($uid, $category_id, $course_id) {

    $q = Database::get()->querySingle("SELECT COUNT(group_category.id) AS cnt
                    FROM group_members, `group`, group_category
                      WHERE group_members.group_id = `group`.id
                        AND group.category_id = group_category.id
                        AND user_id = ?d
                        AND `group`.course_id = ?d
                        AND `group`.visible = 1
                        AND group_category.id = ?d",
                    $uid, $course_id, $category_id);
    if ($q) {
        if ($q->cnt < 1) {
            return true;
        }
    } else {
        return false;
    }
}

/**
 * @brief returns user visible groups
 * @param $uid
 * @param $course_id
 * @return array
 */
function user_visible_groups($uid, $course_id)
{

    $group_ids = array();

    $q = Database::get()->queryArray("SELECT group_members.group_id AS grp_id, `group`.name AS grp_name FROM group_members,`group`
            WHERE group_members.group_id = `group`.id
            AND `group`.course_id = ?d
            AND `group`.visible = 1
            AND group_members.user_id = ?d", $course_id, $uid);

    foreach ($q as $r) {
        $group_ids[$r->grp_id] = $r->grp_name;
    }
    return $group_ids;
}


function getNextAvailableDateOfTutor($editorId,$g_id,$c_id){
    $end = date('Y-m-d',strtotime('now + 30days'));
    $available_next_dates_of_tutor_id = Database::get()->queryArray("SELECT * FROM tutor_availability_group
                                                            WHERE lesson_id = ?d
                                                            AND user_id = ?d
                                                            AND ((user_id NOT IN (SELECT tutor_id FROM booking)) OR (user_id IN (SELECT tutor_id FROM booking) AND start NOT IN (SELECT start FROM booking)))
                                                            AND group_id = ?d
                                                            AND start >= NOW()
                                                            AND end <= ?t ORDER BY start ASC",$c_id,$editorId,$g_id,$end);

    if(count($available_next_dates_of_tutor_id) > 0){
        $counter = 0;
        foreach($available_next_dates_of_tutor_id as $r){
            if($counter == 0){
                $firstDate[$r->user_id] = [
                    'id' => $r->id,
                    'tutor_id' => $r->user_id,
                    'group_id' => $r->group_id,
                    'start' => $r->start,
                    'end' => $r->end
                ];
            }
            $counter++;
        }
        return $firstDate;
    }else{
        return array();
    }
}
