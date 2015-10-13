<?php

/* ========================================================================
 *   Open eClass 3.0
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2014  Greek Universities Network - GUnet
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

/**
 * @brief group info initialization
 * @global type $course_id
 * @global type $status
 * @global type $self_reg
 * @global type $multi_reg
 * @global type $has_forum
 * @global type $private_forum
 * @global type $documents
 * @global type $wiki
 * @global type $group_name
 * @global type $group_description
 * @global type $forum_id
 * @global type $max_members
 * @global type $secret_directory
 * @global type $tutors
 * @global type $member_count
 * @global type $is_tutor
 * @global type $is_edito
 * @global type $is_member
 * @global type $uid
 * @global type $urlServer
 * @global type $user_group_description
 * @global type $course_code
 * @param type $group_id
 */
function initialize_group_info($group_id) {
    
    global $course_id, $is_editor, $status, $self_reg, $has_forum, $private_forum, $documents, $wiki,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $user_group_description, $course_code;
 
    $grp_property_item = Database::get()->querySingle("SELECT self_registration, forum, private_forum, documents, wiki
                     FROM group_properties WHERE course_id = ?d AND group_id = ?d", $course_id, $group_id);
    $self_reg = $grp_property_item->self_registration;        
    $has_forum = $grp_property_item->forum;
    $private_forum = $grp_property_item->private_forum;
    $documents = $grp_property_item->documents;
    $wiki = $grp_property_item->wiki;
    
   
    // Guest users aren't allowed to register in a group
    if ($status == USER_GUEST) {
        $self_reg = 0;
    }
    
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
    if ($is_tutor or $is_editor) {
        $res = Database::get()->queryArray("SELECT description,user_id FROM group_members
                                     WHERE group_id = ?d", $group_id);
        foreach ($res as $d) {
            if (!empty($d->description) or $d->description != ' ') {
                $user_group_description .= "$d->description &nbsp;" . display_user($d->user_id, false, false) . "<br>";
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
 * @brief fills an array with user groups (group_id => group_name)
 * passing $as_id will give back only the groups that have been given the specific assignment
 * @param type $uid
 * @param type $course_id
 * @param type $as_id
 * @return type
 */
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

/**
 * 
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $langGroupCatDel
 * @global type $tool_content
 * @global type $course_code
 * @param type $categoryid
 */

function showgroupcategoryadmintools($categoryid) {
    global $langDelete, $langEditChange, $langGroupCatDel, $tool_content, $course_code;

    $tool_content .= action_button(array(
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
                      'url' => "group_category.php?course=$course_code&amp;editcategory=1&amp;id=$categoryid"),
                array('title' => $langDelete,
                        'icon' => 'fa-times',                        
                        'url' => "index.php?course=$course_code&amp;deletecategory=1&amp;id=$categoryid",
                        'class' => 'delete',
                        'confirm' => $langGroupCatDel)
                ));           
}


/**
 * @brief display groups of specified category
 * @global type $is_editor
 * @global type $course_id
 * @global type $tool_content
 * @global type $course_code
 * @global type $langGroupDelconfirm
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $groups_num
 * @global type $uid
 * @param type $catid
 */
function showgroupsofcategory($catid) {
    
    global $is_editor, $course_id, $tool_content, $langConfig,
    $course_code, $langGroupDelconfirm, $langDelete, $langRegister, $member_count,
    $langModify, $is_member, $multi_reg, $langMyGroup, $langAddDescription,
    $langEditChange, $groups_num, $uid, $totalRegistered,
    $tutors, $group_name, $self_reg, $user_group_description, $user_groups, $max_members, $group_description;

    $q = Database::get()->queryArray("SELECT id FROM `group`
                                   WHERE course_id = ?d AND category_id = ?d
                                   ORDER BY `id`", $course_id, $catid);
          
    foreach ($q as $row) {
        $tool_content .= "<tr><td style='padding-left: 25px;'>";
        $group_id = $row->id;
        initialize_group_info($group_id);
        if ($is_editor) {
            $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) . "</a>";
        } else {
            if ($is_member) {
                $tool_content .= "<a href='group_space.php?course=$course_code&amp;group_id=$group_id'>" . q($group_name) . "</a>";
                $tool_content .= "&nbsp;<span style='color:#900; weight:bold;'>($langMyGroup)</span>";
            } else {
                $tool_content .= q($group_name);
            }
        }
        if ($user_group_description) {
            $tool_content .= "<br><span class='small'>$user_group_description</span>
                    <p>$group_description" .
                    icon('fa-edit', $langModify, "group_description.php?course=$course_code&amp;group_id=$group_id") . "&nbsp;" .
                    icon('fa-times', $langDelete, "group_description.php?course=$course_code&amp;group_id=$group_id&amp;delete=true", 'onClick="return confirmation();"');
        } elseif ($is_member) {
            $tool_content .= " </p><br><a href='group_description.php?course=$course_code&amp;group_id=$group_id'><i>$langAddDescription</i></a>";
        }
        $tool_content .= "</td>";
        $tool_content .= "<td class='text-center' width='250'>";
        foreach ($tutors as $t) {
            $tool_content .= display_user($t->user_id) . "<br>";
        }
        $tool_content .= "</td>";
               
        if ($catid == -2) {
            $rating = new Rating('thumbs_up', 'group', $group_id);
            $tool_content .= $rating->put($is_editor, $uid, $course_id);
        }
        $tool_content .= "<td class='text-center' width='50'>$member_count</td><td class='text-center' width='50'>" .
                ($max_members ? $max_members : '-') . "</td>";
        $totalRegistered += $member_count;

        if ($is_editor) {
            $tool_content .= "<td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                array('title' => $langConfig,
                      'icon' => 'fa-gear',
                      'url' => "group_properties.php?course=$course_code&amp;group_id=$group_id"),                        
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
                      'url' => "group_edit.php?course=$course_code&amp;category=$catid&amp;group_id=$group_id"),
                array('title' => $langDelete,
                      'icon' => 'fa-times',
                      'class' => 'delete',
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;deletegroup=1&amp;id=$group_id",
                      'confirm' => $langGroupDelconfirm)
            ));
            $tool_content .= "</td>";
        } else {            
            $tool_content .= "<td class='text-center'>";
            // If self-registration and multi registration allowed by admin and group is not full        
            if ($uid and $self_reg and (!$user_groups or $multi_reg) and !$is_member and (!$max_members or $member_count < $max_members)) {
                $tool_content .= icon('fa-sign-in', $langRegister, "group_space.php?course=$course_code&amp;selfReg=1&amp;group_id=$group_id");
            } else {
                $tool_content .= "-";
            }
            $tool_content .= "</td>";
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

function makedefaultviewcode($locatie) {
    global $aantalcategories;

    $view = str_repeat('0', $aantalcategories);
    $view[$locatie] = '1';
    return $view;
}

/**
 * @brief delete group
 * @global type $course_id
 * @global type $langGroupDeleted
 * @param type $id
 */
function delete_group($id) {
    global $course_id, $langGroupDeleted;

    $tuple = Database::get()->querySingle("SELECT name, category_id FROM group WHERE course_id = ?d AND id = ?d", $course_id, $id);
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