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
 * @global type $is_member
 * @global type $uid
 * @global type $urlServer
 * @global type $user_group_description
 * @global type $course_code
 * @param type $group_id
 */
function initialize_group_info($group_id) {
    
    global $course_id, $status, $self_reg, $multi_reg, $has_forum, $private_forum, $documents, $wiki,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $user_group_description, $course_code;

    if (!(isset($self_reg) and isset($multi_reg) and isset($has_forum) and isset($private_forum) and isset($documents) and isset($wiki))) {
        $grp_property_item = Database::get()->querySingle("SELECT self_registration, multiple_registration, forum, private_forum, documents, wiki
                         FROM group_properties WHERE course_id = ?d AND group_id = ?d", $course_id, $group_id);
        $self_reg = $grp_property_item->self_registration;
        $multi_reg = $grp_property_item->multiple_registration;
        $has_forum = $grp_property_item->forum;
        $private_forum = $grp_property_item->private_forum;
        $documents = $grp_property_item->documents;
        $wiki = $grp_property_item->wiki;
    }

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

function showcategoryadmintools($categoryid) {
    global $langDelete, $langEditChange, $langGroupCatDel, $tool_content, $course_code;

    $tool_content .= action_button(array(
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
					  'url' => "group_category.php?course=$course_code&amp;editcategory=1&amp;id=$categoryid"),
                array('title' => $langDelete,
                        'icon' => 'fa-times',
                        //'url' => "$basecaturl" . "action=deletecategory",
						'url' => "index.php?course=$course_code&amp;deletecategory=1&amp;id=$categoryid",
                        'class' => 'delete',
                        'confirm' => $langGroupCatDel)
                ));           
}



function showgroupsofcategory($catid) {
    global $is_editor, $course_id, $urlview, $socialview_param, $tool_content,
    $course_code, $langGroupDelconfirm, $langDelete, $langUp, $langDown,
    $langEditChange, $is_in_tinymce, $groups_num;

    $tool_content .= "<tr>";
    $result = Database::get()->queryArray("SELECT * FROM `group`
                                   WHERE course_id = ?d AND category_id = ?d
                                   ORDER BY `id`", $course_id, $catid);
  
    foreach ($result as $myrow) {
        $name = empty($myrow->name) ? $myrow->description : $myrow->name;        
        $tool_content .= "<td class='nocategory-link'>" . q($name) . "&nbsp;&nbsp;";

        if (!empty($myrow->description)) {
            $tool_content .= "<br />" . standard_text_escape($myrow->description);
        }
        if ($catid == -2) { 
            global $uid;
            $rating = new Rating('thumbs_up', 'group', $myrow->id);
            $tool_content .= $rating->put($is_editor, $uid, $course_id);
        }
        $tool_content .= "</td>";
        
        if ($is_editor && !$is_in_tinymce) {   
            $tool_content .= "<td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                array('title' => $langEditChange,
                      'icon' => 'fa-edit',
                      'url' => "group_edit.php?course=$course_code&amp;category=$catid&amp;group_id=$myrow->id"),
                array('title' => $langDelete,
                      'icon' => 'fa-times',
                      'class' => 'delete',
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;deletegroup=1&amp;id=$myrow->id",
                      'confirm' => $langGroupDelconfirm)
            ));
            $tool_content .= "</td>";
        } /*elseif ($catid == -2 && !$is_in_tinymce) {
            if (isset($_SESSION['uid'])) {
                if (is_link_creator($myrow->id)) {
                    $tool_content .= "<td class='option-btn-cell'>";
                    $editgroup = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=editgroup&amp;id=" . getIndirectReference($myrow->id) . "&amp;urlview=$urlview".$socialview_param;
                    $tool_content .= action_button(array(
                            array('title' => $langEditChange,
                                    'icon' => 'fa-edit',
                                    'url' => $editgroup),
                            array('title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'class' => 'delete',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=deletegroup&amp;id=" . getIndirectReference($myrow->id) . "&amp;urlview=$urlview".$socialview_param,
                                    'confirm' => $langGroupDelconfirm)
                    ));
                    $tool_content .= "</td>";
                } else {
                    if (abuse_report_show_flag('group', $myrow->id , $course_id, $is_editor)) {
                        $flag_arr = abuse_report_action_button_flag('group', $myrow->id, $course_id);
                    
                        $tool_content .= "<td class='option-btn-cell'>".action_button(array($flag_arr[0])).$flag_arr[1]."</td>"; //action button option
                    } else {
                        $tool_content .= "<td>&nbsp;</td>";
                    }
                }
            }
        }*/
        
        $tool_content .= "</tr>";
    }
}

function delete_category($id) {
    global $course_id, $langGroupCategoryDeleted;

    Database::get()->query("DELETE FROM `group` WHERE course_id = ?d AND category_id = ?d", $course_id, $id);
    $category = Database::get()->querySingle("SELECT name FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id)->name;
    Database::get()->query("DELETE FROM `group_category` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('cat_id' => $id,
                                                               'category' => $category));
}

function submit_category() {
    global $course_id, $langCategoryAdded, $langCategoryModded,
    $categoryname, $description, $langFormErrors;
			
	
    register_posted_variables(array('categoryname' => true,
                                    'description' => true), 'all', 'trim');
    $set_sql = "SET name = ?s, description = ?s";
    $terms = array($categoryname, purify($description));

    if (isset($_POST['id'])) {

		
			$id = getDirectReference($_POST['id']);
			Database::get()->query("UPDATE `group_category` $set_sql WHERE course_id = ?d AND id = ?d", $terms, $course_id, $id);
			$log_type = LOG_MODIFY;
	}

	else {
		/*$v = new Valitron\Validator($_POST);
		$v->rule('required', array('categoryname'));
		if($v->validate()) {*/
        $order = Database::get()->querySingle("SELECT MAX(`order`) as maxorder FROM `group_category`
                                      WHERE course_id = ?d", $course_id)->maxorder;
        $order++;
        $id = Database::get()->query("INSERT INTO `group_category` $set_sql, course_id = ?d, `order` = ?d", $terms, $course_id, $order)->lastInsertID;
        $log_type = LOG_INSERT;

		/*} else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/group/group_category.php?course=$course_code&amp;addcategory=1");
		}*/
	}
    $txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
    Log::record($course_id, MODULE_ID_LINKS, $log_type, array('id' => $id,
        'category' => $categoryname,
        'description' => $txt_description));

}

function category_form_defaults($id) {
    global $course_id, $form_name, $form_description;

    $myrow = Database::get()->querySingle("SELECT name,description  FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id);
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

function delete_group($id) {
    global $course_id, $langGroupDeleted;

    $tuple = Database::get()->querySingle("SELECT name, category_id FROM group WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $name = $tuple->name;
    $category_id = $tuple->category_id;

    Database::get()->query("DELETE FROM `group` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('id' => $id,
																'name' => $name));
}
