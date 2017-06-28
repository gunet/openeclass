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
 * @file group_edit.php
 * @brief group editing
 *
 */

$require_current_course = TRUE;
$require_editor = TRUE;
$require_help = TRUE;
$helpTopic = 'groups';
$helpSubTopic = 'settings';

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'group_functions.php';

$toolName = $langGroups;
$pageName = $langModify;

initialize_group_id();
initialize_group_info($group_id);

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
$navigation[] = array('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => q($group_name));

load_js('select2');
$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-tutor').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

//check if social bookmarking is enabled for this course
$social_bookmarks_enabled = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);

$message = '';
// Once modifications have been done, the user validates and arrives here
if (isset($_POST['modify'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('name'));
    $v->rule('required', array('maxStudent'));
    $v->rule('numeric', array('maxStudent'));
    $v->rule('min', array('maxStudent'), 1);
    $v->labels(array(
        'name' => "$langTheField $langNewGroups",
        'maxStudent' => "$langTheField $langMax $langGroupPlacesThis"
    ));
    if($v->validate()) {
        $self_reg = $allow_unreg = $has_forum = $documents = $wiki = 0;

        if (isset($_POST['self_reg']) and $_POST['self_reg'] == 'on') {
            $self_reg = 1;
        }
        if (isset($_POST['allow_unreg']) and $_POST['allow_unreg'] == 'on') {
            $allow_unreg = 1;
        }
        if (isset($_POST['forum']) and $_POST['forum'] == 'on') {
            $has_forum = 1;
        }
        if (isset($_POST['documents']) and $_POST['documents'] == 'on'){
            $documents = 1;
        }
        if (isset($_POST['wiki']) and $_POST['wiki'] == 'on'){
            $wiki = 1;
        }
        $private_forum = $_POST['private_forum'];
        $group_id = $_POST['group_id'];

        Database::get()->query("UPDATE group_properties SET
                                self_registration = ?d,
                                allow_unregister = ?d,
                                forum = ?d,
                                private_forum = ?d,
                                documents = ?d,
                                wiki = ?d WHERE course_id = ?d AND group_id = ?d",
            $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $wiki, $course_id, $group_id);

        // Update main group settings
        register_posted_variables(array('name' => true, 'description' => true), 'all');
        register_posted_variables(array('maxStudent' => true), 'all');
        $student_members = $member_count - count($tutors);
        if ($maxStudent != 0 and $student_members > $maxStudent) {
            $maxStudent = $student_members;
            $message .= "<div class='alert alert-warning'>$langGroupMembersUnchanged</div>";
        }
        $category_id = intval($_POST['selectcategory']);
        Database::get()->query("UPDATE `group`
                                        SET name = ?s,
                                            description = ?s,
                                            max_members = ?d,
                                            category_id = ?d
                                        WHERE id = ?d", $name, $description, $maxStudent, $category_id, $group_id);

        Database::get()->query("UPDATE forum SET name = ?s WHERE id =
                            (SELECT forum_id FROM `group` WHERE id = ?d)
                                AND course_id = ?d", $name, $group_id, $course_id);

        if ($is_editor) {
            if (isset($_POST['tutor'])) {
                Database::get()->query("DELETE FROM group_members
                                         WHERE group_id = ?d AND is_tutor = 1", $group_id);
                foreach ($_POST['tutor'] as $tutor_id) {
                    $tutor_id = intval($tutor_id);
                    Database::get()->query("REPLACE INTO group_members SET group_id = ?d, user_id = ?d, is_tutor = 1", $group_id, $tutor_id);
                }
            } else {
                Database::get()->query("UPDATE group_members SET is_tutor = 0 WHERE group_id = ?d", $group_id);
            }
        }

        // Count number of members
        $numberMembers = @count($_POST['ingroup']);

        // Insert new list of members
        if ($maxStudent < $numberMembers and $maxStudent != 0) {
            // More members than max allowed
            $message .= "<div class='alert alert-warning'>$langGroupTooManyMembers</div>";
        } else {
            // Delete all members of this group
            $cur_member_ids = [];
            Database::get()->queryFunc("SELECT user_id FROM group_members "
                    . "WHERE group_id = ?d AND is_tutor = 0",
                    function ($group_member) use (&$cur_member_ids) {
                        array_push($cur_member_ids, $group_member->user_id);
                    },$group_id);
            if (isset($_POST['ingroup'])) {
                $ids_to_be_inserted = array_diff($_POST['ingroup'], $cur_member_ids);
                $ids_to_be_deleted = implode(', ', array_diff($cur_member_ids, $_POST['ingroup']));
                if ($ids_to_be_deleted) {
                Database::get()->query("DELETE FROM group_members
                                            WHERE group_id = ?d AND is_tutor = 0 AND user_id IN ($ids_to_be_deleted)", $group_id);
                }
                foreach ($ids_to_be_inserted as $user_id) {
                    Database::get()->query("INSERT INTO group_members (user_id, group_id)
                                              VALUES (?d, ?d)", $user_id, $group_id);
                }
            } else {
                Database::get()->query("DELETE FROM group_members
                                            WHERE group_id = ?d AND is_tutor = 0",$group_id);
            }
            Session::Messages($langGroupSettingsModified,'alert-success');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        }
        initialize_group_info($group_id);
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/group/group_edit.php?course=$course_code&category=$category_id&group_id=$group_id");
    }
}

$tool_content_group_name = q($group_name);

if ($is_editor) {

    $group = Database::get()->querySingle("SELECT * FROM group_properties WHERE group_id = ?d AND course_id = ?d", $group_id, $course_id);

    $checked['self_reg'] = ($group->self_registration?'checked':'');
    $checked['allow_unreg'] = ($group->allow_unregister?'checked':'');
    $checked['private_forum_yes'] =($group->private_forum?' checked="1"' : '');
    $checked['private_forum_no'] = ($group->private_forum? '' : ' checked="1"');
    $checked['has_forum'] = ($group->forum?'checked':'');
    $checked['documents'] = ($group->documents?'checked':'');
    $checked['wiki'] = ($group->wiki?'checked':'');

    $tool_content_tutor = "<select name='tutor[]' multiple id='select-tutor' class='form-control'>\n";
    $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname,
                                   user.id IN (SELECT user_id FROM group_members
                                                              WHERE group_id = ?d AND
                                                                    is_tutor = 1) AS is_tutor
                              FROM course_user, user
                              WHERE course_user.user_id = user.id AND
                                    course_user.tutor = 1 AND
                                    course_user.course_id = ?d
                              ORDER BY surname, givenname, user_id", $group_id, $course_id);
    foreach ($q as $row) {
        $selected = $row->is_tutor ? ' selected="selected"' : '';
        $tool_content_tutor .= "<option value='$row->user_id'$selected>" . q($row->surname) .
                ' ' . q($row->givenname) . "</option>";
    }
    $tool_content_tutor .= '</select>';
} else {
    $tool_content_tutor = display_user($tutors);
}

$tool_content_max_student = $max_members ? $max_members : 1;
$tool_content_group_description = q($group_description);

$multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);

if ($multi_reg) {
    // Students registered to the course but not members of this group
    $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                        FROM user u, course_user cu
                        WHERE cu.course_id = ?d AND
                              cu.user_id = u.id AND
                              u.id NOT IN (SELECT user_id FROM group_members WHERE group_id = ?d) AND
                              cu.status = " . USER_STUDENT . "
                        GROUP BY u.id
                        ORDER BY u.surname, u.givenname", $course_id, $group_id);
} else {
    // Students registered to the course but members of no group
    $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                        FROM (user u, course_user cu)
                        WHERE cu.course_id = $course_id AND
                              cu.user_id = u.id AND
                              cu.status = " . USER_STUDENT . " AND
                              u.id NOT IN (SELECT user_id FROM group_members, `group`
                                                               WHERE `group`.id = group_members.group_id AND
                                                               `group`.course_id = ?d)
                        GROUP BY u.id, u.surname, u.givenname, u.am
                        ORDER BY u.surname, u.givenname", $course_id);
}

$tool_content_not_Member = '';
foreach ($resultNotMember as $myNotMember) {
    $tool_content_not_Member .= "<option value='$myNotMember->id'>" .
            q("$myNotMember->surname $myNotMember->givenname") . (!empty($myNotMember->am) ? q(" ($myNotMember->am)") : "") . "</option>";
}

$q = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname
               FROM user, group_members
               WHERE group_members.user_id = user.id AND
                     group_members.group_id = ?d AND
                     group_members.is_tutor = 0
               ORDER BY user.surname, user.givenname", $group_id);

$tool_content_group_members = '';
foreach ($q as $member) {
    $tool_content_group_members .= "<option value='$member->id'>" . q("$member->surname $member->givenname") .
            "</option>";
}

if (!empty($message)) {
    $tool_content .= $message;
}
$back_url = isset($_GET['from']) && $_GET['from'] == 'group' ? "group_space.php?course=$course_code&group_id=$group_id" : "index.php?course=$course_code";
$tool_content .=  action_bar(array(
      array('title' => $langAdminUsers,
          'url' => "../user/?course=$course_code",
          'icon' => 'fa-users',
          'level' => 'primary-label'),
      array(
          'title' => $langBack,
          'level' => 'primary-label',
          'icon' => 'fa-reply',
          'url' => $back_url,
           )
  ));


$tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='groupedit' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;group_id=$group_id'>
        <fieldset>
        <div class='form-group".(Session::getError('name') ? " has-error" : "")."'>
            <label class='col-sm-2 control-label'>$langGroupName:</label>
            <div class='col-sm-10'>
                <input class='form-control' type=text name='name' size='40' value='$tool_content_group_name'>
                <span class='help-block'>".Session::getError('name')."</span>
            </div>
        </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langDescription $langOptional:</label>
          <div class='col-sm-10'><textarea class='form-control' name='description' rows='2' cols='60'>$tool_content_group_description</textarea></div>
        </div>
        <div class='form-group".(Session::getError('maxStudent') ? " has-error" : "")."'>
            <label class='col-sm-2 control-label'>$langMax $langGroupPlacesThis:</label>
            <div class='col-sm-10'>
                <input class='form-control' type=text name='maxStudent' size=2 value='$tool_content_max_student'>
                <span class='help-block'>".Session::getError('maxStudent')."</span>
            </div>

        </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langGroupTutor:</label>
          <div class='col-sm-10'>
             $tool_content_tutor
          </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langGroupMembers:</label>
        <div class='col-sm-10'>
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr class='title1'>
                          <th>$langNoGroupStudents</th>
                          <th width='100' class='text-center'>$langMove</th>
                          <th class='right'>$langGroupMembers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                          <td>
                            <select class='form-control' id='users_box' name='nogroup[]' size='15' multiple>
                              $tool_content_not_Member
                            </select>
                          </td>
                          <td class='text-center'>
                              <div class='form-group'>
                                  <input class='btn btn-default' type='button' onClick=\"move('users_box','members_box')\" value='   &gt;&gt;   ' />
                              </div>
                              <div class='form-group'>
                                  <input class='btn btn-default' type='button' onClick=\"move('members_box','users_box')\" value='   &lt;&lt;   ' />
                              </div>
                          </td>
                          <td class='text-right'>
                            <select class='form-control' id='members_box' name='ingroup[]' size='15' multiple>
                              $tool_content_group_members
                            </select>
                          </td>
                        </tr>
                    </tbody>
                </table>
            </div>
      </div>
    </div>
    <div class='form-group'>
            <label for='selectcategory' class='col-sm-2 control-label'>$langCategory:</label>
            <div class='col-sm-3'>
                <select class='form-control' name='selectcategory' id='selectcategory'>
                <option value='0'>--</option>";
        $resultcategories = Database::get()->queryArray("SELECT * FROM group_category WHERE course_id = ?d ORDER BY `name`", $course_id);
        foreach ($resultcategories as $myrow) {
            $tool_content .= "<option value='$myrow->id'";
            $category_id = $myrow->id;
            if ($group_category == $myrow->id) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= '>' . q($myrow->name) . "</option>";
        }
        $tool_content .= "
            </select>
            </div>
    </div>";

    $tool_content .= "
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langGroupStudentRegistrationType:</label>
                <div class='col-sm-10'>             
                    <div class='checkbox'>
                    <label>
                     <input type='checkbox' name='self_reg' $checked[self_reg]>
                        $langGroupAllowStudentRegistration
                        </label>
                        </div>                    
                </div>
            </div>
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langGroupAllowUnregister:</label>
                <div class='col-sm-10'>             
                    <div class='checkbox'>
                    <label>
                     <input type='checkbox' name='allow_unreg' $checked[allow_unreg]>
                        $langGroupAllowStudentUnregister
                        </label>
                        </div>                    
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langPrivate_1:</label>
                <div class='col-sm-10'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='1' checked=''  $checked[private_forum_yes]>
                        $langPrivate_2
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='0' $checked[private_forum_no]>
                        $langPrivate_3
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langGroupForum:</label>
                <div class='col-sm-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='forum' $checked[has_forum]>
                      </label>
                    </div>                    
                </div>
            </div>   
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langDoc:</label>
                <div class='col-sm-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='documents' $checked[documents]>
                      </label>
                    </div>                    
                </div>
            </div>  
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langWiki:</label>
                <div class='col-sm-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='wiki' $checked[wiki]>
                      </label>
                    </div>                    
                </div>
            </div>			
            <input type='hidden' name='group_id' value=$group_id></input>          
        <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>".
            form_buttons(array(
                array(
                    'text'  =>  $langSave,
                    'name'  =>  'modify',
                    'value' =>  $langModify,
                    'javascript' => "selectAll('members_box',true)"
                ),
                array(
                    'href'  =>  "index.php?course=$course_code"
                )
            ))
            ."</div>
        </div>
        </fieldset>
        </form>
</div>";

draw($tool_content, 2, null, $head_content);
