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

/**
 *
 * @file groups_management.php
 * @brief Page in order to manage groups
 */

$require_login = true;
$require_current_course = true;
$require_help = true;
$helpTopic = 'groups';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

$toolName = $langGroups;

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroups);
$pageName = $langGroupsManagment;

load_js('select2');

$head_content .= "
<script>
    $(function () {
        $('#group-name').select2({

            minimumInputLength: 2,
            tags: true,
            tokenSeparators: [','],
            width: '100%',
            selectOnClose: true,
            createSearchChoice: function(term, data) {
              if ($(data).filter(function() {
                return this.text.localeCompare(term) === 0;
              }).length === 0) {
                return {
                  id: term,
                  text: term
                };
              }
            },
            ajax: {
                url: 'searchGroup.php?course=$course_id',
                dataType: 'json',
                data: function(term, page) {
                    return {
                        q: term
                    };
                },
                processResults: function(data, page) {
                    return {results: data};
                }
            }
        });

    });
</script>";

$tool_content = '';
$selectedCategory = '';
$selectedCategory2 = '';
$selectedCategory3 = '';

//check if social bookmarking is enabled for this course
$social_bookmarks_enabled = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);

// Display all available groups

$num_of_groups = 0;

//Search groups by names
if(isset($_POST['submitGroupNames'])){
    $idsGroups = array();
    if(isset($_POST['groupNames']) and $_POST['groupNames']){
        foreach($_POST['groupNames'] as $n){
            $res = Database::get()->querySingle("SELECT id FROM `group` WHERE name = ?s AND course_id = ?d",$n,$course_id);
            if($res){
                $idsGroups[] = $res->id;
            }
        }
        if(count($idsGroups) > 0){
            $values = implode(',', $idsGroups);
            $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE (id) IN ($values) AND course_id = ?d ORDER BY name", $course_id);
            $num_of_groups = count($groupSelect);
        }else{
            $num_of_groups = 0;
        }
    }else{
        $num_of_groups = 0;
    }
}

// Search groups by catecory
if(isset($_POST['searchGroupByCategory'])){
    if($_POST['searchGroupByCategory'] == -1){
        $selectedCategory2 = 'selected';
        $num_of_groups = 0;
    }elseif($_POST['searchGroupByCategory'] == 0){
        $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND category_id = ?d ORDER BY name", $course_id,0);
        $selectedCategory3 = 'selected';
        $num_of_groups = count($groupSelect);
    }else{
        $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND category_id = ?d ORDER BY name", $course_id,$_POST['searchGroupByCategory']);
        $selectedCategory = $_POST['searchGroupByCategory'];
        $num_of_groups = count($groupSelect);
    }

}


$tool_content .= "<div class='row mb-4'>
                    <div class='col-md-6 col-12'>";
                       $tool_content .= "<form method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                                            <label for='group-name' class='control-label-notes mb-1'>$langSearchGroupByName</label>
                                            <div class='col-12 d-flex justify-content-start'>
                                                <button type='submit' name='submitGroupNames' class='btn btn-sm searchGroupBtn h-40px' aria-label='Submit button'>
                                                    <span class='fa fa-search fs-6'></span>
                                                </button>
                                                <select id='group-name' class='form-select' name='groupNames[]' multiple></select>
                                            </div>
                                         </form>";
$tool_content .= "  </div>
                    <div class='col-md-6 col-12 mt-md-0 mt-3'>";
                        $tool_content .= "
                        <form method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                            <label for='searchGroupByCategoryId' class='control-label-notes mb-1'>$langSearchGroupByCategory</label>
                            <div class='col-12'>
                                <select id='searchGroupByCategoryId' class='form-select py-0 mt-0' name='searchGroupByCategory' onchange='this.form.submit()'>
                                    <option value='-1' $selectedCategory2>--</option>
                                    <option value='0' $selectedCategory3>$langAllGroupsWithoutCategory</option>";
                                    $resultcategories = Database::get()->queryArray("SELECT * FROM group_category WHERE course_id = ?d ORDER BY `name`", $course_id);
                                    foreach ($resultcategories as $myrow) {
                                        $tool_content .= "<option value='$myrow->id'";
                                        $category_id = $myrow->id;
                                        if ($selectedCategory == $myrow->id) {
                                            $tool_content .= " selected='selected'";
                                        }
                                        $tool_content .= '>' . q($myrow->name) . "</option>";
                                    }
                                    $tool_content .= "
                                </select>
                            </div>
                        </form>";
  $tool_content .= "</div>
                  </div>";



// Manage all group at the same time
$message = '';
if(isset($_POST['group_id'])){
    foreach($_POST['group_id'] as $g){

        $group_id = $g;
        $tutorsPost = "tutor_$g";
        $self_reg_group_id = "self_reg_$g";
        $allow_unreg_group_id = "allow_unreg_$g";
        $forum_group_id = "forum_$g";
        $documents_group_id = "documents_$g";
        $wiki_group_id = "wiki_$g";
        $public_users_list_group_id = "public_users_list_$g";
        $private_forum_group_id = "private_forum_$g";
        $selectcategory_group_id = "selectcategory_$g";
        $maxStudent_group_id = "maxStudent_$g";
        $booking_group_id = "booking_$g";

        $self_reg = $allow_unreg = $has_forum = $documents = $wiki = $public_users_list = $booking = 0;

        if (isset($_POST[$self_reg_group_id]) and $_POST[$self_reg_group_id] == 'on') {
            $self_reg = 1;
        }
        if (isset($_POST[$allow_unreg_group_id]) and $_POST[$allow_unreg_group_id] == 'on') {
            $allow_unreg = 1;
        }
        if (isset($_POST[$forum_group_id]) and $_POST[$forum_group_id] == 'on') {
            $has_forum = 1;
        }
        if (isset($_POST[$documents_group_id]) and $_POST[$documents_group_id] == 'on') {
            $documents = 1;
        }
        if (isset($_POST[$wiki_group_id]) and $_POST[$wiki_group_id] == 'on') {
            $wiki = 1;
        }
        if (isset($_POST[$public_users_list_group_id]) and $_POST[$public_users_list_group_id] == 'on') {
            $public_users_list = 1;
        }
        if (isset($_POST[$booking_group_id]) and $_POST[$booking_group_id] == 'on') {
            $booking = 1;
        }
        $private_forum = $_POST[$private_forum_group_id];


        Database::get()->query("UPDATE group_properties SET
                                self_registration = ?d,
                                allow_unregister = ?d,
                                forum = ?d,
                                private_forum = ?d,
                                documents = ?d,
                                wiki = ?d,
                                public_users_list = ?d,
                                booking = ?d
                        WHERE course_id = ?d AND group_id = ?d",
            $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $wiki, $public_users_list, $booking, $course_id, $group_id);

        // Update main group settings
        $result = Database::get()->querySingle("SELECT name,max_members, secret_directory, category_id
                                                FROM `group` WHERE course_id = ?d AND id = ?d", $course_id, $group_id);
        if (!$result) {
            header("Location: {$urlServer}modules/group/index.php?course=$course_code");
            exit;
        }
        $memberCount = Database::get()->querySingle("SELECT COUNT(*) AS count FROM group_members
                                                        WHERE group_id = ?d
                                                        AND is_tutor = 0", $group_id)->count;

        $maxStudents = $_POST[$maxStudent_group_id];
        if($maxStudents != 0 && $maxStudents < $memberCount){
            $maxStudents = $memberCount;
            $message .= "<h5 class='TextBold mb-0'>$result->name</h5><p>$langGroupMembersUnchanged</p>";
        }
        $category_id = intval($_POST[$selectcategory_group_id]);
        Database::get()->query("UPDATE `group`
                                        SET
                                            max_members = ?d,
                                            category_id = ?d
                                        WHERE id = ?d", $maxStudents, $category_id, $group_id);


        if ($is_editor) {
            if (isset($_POST[$tutorsPost])) {
                Database::get()->query("DELETE FROM group_members
                                         WHERE group_id = ?d AND is_tutor = 1", $group_id);
                foreach ($_POST[$tutorsPost] as $tutor_id) {
                    $tutor_id = intval($tutor_id);
                    Database::get()->query("REPLACE INTO group_members SET group_id = ?d, user_id = ?d, is_tutor = 1", $group_id, $tutor_id);
                }
            } else {
                Database::get()->query("UPDATE group_members SET is_tutor = 0 WHERE group_id = ?d", $group_id);
            }
        }

    }

    if(empty($message)){
        Session::flash('message',$langGroupSettingsModified);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$message);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page("modules/group/groups_management.php?course=$course_code");

}

// Display groups by result
$tool_content .= "<div class='col-12'>
                    <form id='formId' class='form-wrapper' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                        <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>";

                            if($num_of_groups > 0){

                $tool_content .="<div class='table-responsive mt-0'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th>$langGroupName</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                            foreach($groupSelect as $gr){

                                                if (!is_group_visible($gr->id, $course_id)) {
                                                    $link_class = 'fa fa-eye-slash';
                                                } else {
                                                    $link_class = '';
                                                }

                                                initialize_group_info($gr->id);

                                                $head_content .= "<script type='text/javascript'>
                                                    $(document).ready(function () {
                                                        $('#select-tutor_$gr->id').select2();
                                                    });
                                                    </script>
                                                    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
                                                ";

                                                $group = Database::get()->querySingle("SELECT * FROM group_properties WHERE group_id = ?d AND course_id = ?d", $gr->id, $course_id);

                                                $self_reg = "self_reg_$gr->id";
                                                $checked[$self_reg] = ($group->self_registration?'checked':'');

                                                $allow_unreg = "allow_unreg_$gr->id";
                                                $checked[$allow_unreg] = ($group->allow_unregister?'checked':'');

                                                $private_forum_yes = "private_forum_yes_$gr->id";
                                                $checked[$private_forum_yes] =($group->private_forum?' checked="1"' : '');

                                                $private_forum_no = "private_forum_no_$gr->id";
                                                $checked[$private_forum_no] = ($group->private_forum? '' : ' checked="1"');

                                                $has_forum = "has_forum_$gr->id";
                                                $checked[$has_forum] = ($group->forum?'checked':'');

                                                $documents = "documents_$gr->id";
                                                $checked[$documents] = ($group->documents?'checked':'');

                                                $wiki = "wiki_$gr->id";
                                                $checked[$wiki] = ($group->wiki?'checked':'');

                                                $public_users_list = "public_users_list_$gr->id";
                                                $checked[$public_users_list] = ($group->public_users_list? 'checked':'');

                                                $booking = "booking_$gr->id";
                                                $checked[$booking] = ($group->booking?'checked':'');


                                                $tool_content .= "<tr>
                                                                    <td>
                                                                        <div class='d-flex justify-content-between align-items-center'>
                                                                            <a type='button' href='group_space.php?course=$course_code&amp;group_id=$gr->id'>" . q($gr->name) . "
                                                                                &nbsp;<span class='$link_class text-danger'></span>
                                                                            </a>
                                                                            <button aria-label='$langSettingSelect' class='btn submitAdminBtn btn-sm showSettings' type='button' data-bs-toggle='collapse' data-bs-target='#CollapseGroup_$gr->id'>
                                                                                <span class='fa fa-cogs'></span>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                    ";
                                                $tool_content .= "</tr>";

                                                $tool_content .= "<tr class='collapse w-100' id='CollapseGroup_$gr->id' style='background-color:transparent !important;'>
                                                                    <td class=' p-xl-4'>";
                                                                    $tool_content .= "
                                                                        <div class='col-12'>
                                                                            <div class='row'>
                                                                                <div class='col-lg-4 col-12'>";
                                                                    $tool_content .= "
                                                                                    <div class='row'>
                                                                                        <div class='col-12 form-group'>
                                                                                            <label for='select-tutor_$gr->id' class='control-label-notes mb-1'>$langGroupTutor</label>
                                                                                            <select name='tutor_$gr->id[]' multiple id='select-tutor_$gr->id' class='form-select'>\n";


                                                                                                $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname,
                                                                                                                                            user.id IN (SELECT user_id FROM group_members
                                                                                                                                                                    WHERE group_id = ?d AND
                                                                                                                                                                            is_tutor = 1) AS is_tutor
                                                                                                                                    FROM course_user, user
                                                                                                                                    WHERE course_user.user_id = user.id AND
                                                                                                                                            course_user.status != " . USER_GUEST . " AND
                                                                                                                                            user.expires_at >= " . DBHelper::timeAfter() . " AND
                                                                                                                                            course_user.course_id = ?d
                                                                                                                                    ORDER BY surname, givenname, user_id", $gr->id, $course_id);
                                                                                                foreach ($q as $row) {
                                                                                                    $selected = $row->is_tutor ? ' selected="selected"' : '';
                                                                                                    $tool_content .= "<option value='$row->user_id'$selected>" . q($row->surname) .
                                                                                                            ' ' . q($row->givenname) . "</option>";
                                                                                                }
                                                                        $tool_content .= "  </select>
                                                                                        </div>";


                                                                    $tool_content .= "  <div class='col-12 form-group mt-4'>
                                                                                            <label for='maxsStudent_$gr->id' class='control-label-notes mb-1'>$langMax $langGroupPlacesThis</label>
                                                                                            <div class='col-sm-12'>
                                                                                                <input id='maxsStudent_$gr->id' class='form-control' type=text name='maxStudent_$gr->id' size=2 value='$max_members'>
                                                                                            </div>";
                                                                                            $membersAvailable = Database::get()->querySingle("SELECT COUNT(*) AS count FROM group_members
                                                                                                                                            WHERE group_id = ?d
                                                                                                                                            AND is_tutor = 0", $gr->id)->count;
                                                                        $tool_content .= "  <div class='help-block mt-1'>$langGroupMembers:&nbsp;<span class='badge Primary-600-bg'>$membersAvailable</span></div>
                                                                                        </div>";


                                                                    $tool_content .= "  <div class='col-12 form-group mt-4'>
                                                                                            <label for='selectcategory_$gr->id' class='col-12 control-label-notes mb-1'>$langCategory</label>
                                                                                            <div class='col-sm-12'>
                                                                                                <select class='form-select' name='selectcategory_$gr->id' id='selectcategory_$gr->id'>
                                                                                                    <option value='0'>--</option>";
                                                                                                    $resultcategories = Database::get()->queryArray("SELECT * FROM group_category WHERE course_id = ?d ORDER BY `name`", $course_id);
                                                                                                    foreach ($resultcategories as $myrow) {
                                                                                                        $tool_content .= "<option value='$myrow->id'";
                                                                                                        $category_id = $myrow->id;
                                                                                                        if ($gr->category_id == $myrow->id) {
                                                                                                            $tool_content .= " selected='selected'";
                                                                                                        }
                                                                                                        $tool_content .= '>' . q($myrow->name) . "</option>";
                                                                                                    }
                                                                                                    $tool_content .= "
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>";
                                                                    $tool_content .= "</div>
                                                                                </div>

                                                                                <div class='col-lg-4 col-12 col-12 mt-lg-0 mt-4'>
                                                                                    <div class='row'>";
                                                                        $tool_content .="<div class='col-12 form-group'>
                                                                                                <div class='control-label-notes mb-1'>$langGroupStudentRegistrationType</div>
                                                                                                <div class='col-sm-12'>
                                                                                                    <div class='checkbox'>
                                                                                                        <label class='label-container' aria-label='$langSelect'>
                                                                                                            <input type='checkbox' name='self_reg_$gr->id' $checked[$self_reg]>
                                                                                                            <span class='checkmark'></span>
                                                                                                            $langGroupAllowStudentRegistration
                                                                                                        </label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class='col-12 form-group mt-4'>
                                                                                                <div class='control-label-notes mb-1'>$langGroupAllowUnregister</div>
                                                                                                <div class='col-sm-12'>
                                                                                                    <div class='checkbox'>
                                                                                                        <label class='label-container' aria-label='$langSelect'>
                                                                                                            <input type='checkbox' name='allow_unreg_$gr->id' $checked[$allow_unreg]>
                                                                                                            <span class='checkmark'></span>
                                                                                                            $langGroupAllowStudentUnregister
                                                                                                        </label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class='col-12 form-group mt-4'>
                                                                                                <div class='control-label-notes mb-1'>$langPrivate_1</div>
                                                                                                <div class='col-sm-12'>
                                                                                                    <div class='radio mb-2'>
                                                                                                        <label>
                                                                                                            <input type='radio' name='private_forum_$gr->id' value='1' checked=''  $checked[$private_forum_yes]>
                                                                                                            $langPrivate_2
                                                                                                        </label>
                                                                                                    </div>
                                                                                                    <div class='radio'>
                                                                                                        <label>
                                                                                                            <input  type='radio' name='private_forum_$gr->id' value='0' $checked[$private_forum_no]>
                                                                                                            $langPrivate_3
                                                                                                        </label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                    </div>";
                                                                $tool_content .= "</div>

                                                                                <div class='col-lg-4 col-12 col-12 mt-lg-0 mt-4'>
                                                                                    <div class='control-label-notes mb-1'>$langTools</div>
                                                                                    <div class='row'>";

                                                                    $tool_content .= "  <div class='col-12 form-group'>
                                                                                            <div class='checkbox'>
                                                                                                <label class='label-container' aria-label='$langSelect'>
                                                                                                    <input type='checkbox' name='public_users_list_$gr->id' $checked[$public_users_list]>
                                                                                                    <span class='checkmark'></span>
                                                                                                    $langGroupPublicUserList
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class='col-12 form-group mt-2'>
                                                                                            <div class='checkbox'>
                                                                                                <label class='label-container' aria-label='$langSelect'>
                                                                                                    <input type='checkbox' name='forum_$gr->id' $checked[$has_forum]>
                                                                                                    <span class='checkmark'></span>
                                                                                                    $langGroupForum
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class='col-12 form-group mt-2'>
                                                                                            <div class='checkbox'>
                                                                                                <label class='label-container' aria-label='$langSelect'>
                                                                                                    <input type='checkbox' name='documents_$gr->id' $checked[$documents]>
                                                                                                    <span class='checkmark'></span>
                                                                                                    $langDoc
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>";

                                                                                        if((isset($is_collaborative_course) and !$is_collaborative_course)){
                                                                                        $tool_content .= "
                                                                                        <div class='col-12 form-group mt-2'>
                                                                                            <div class='checkbox'>
                                                                                                <label class='label-container' aria-label='$langSelect'> 
                                                                                                    <input type='checkbox' name='wiki_$gr->id' $checked[$wiki]>
                                                                                                    <span class='checkmark'></span>
                                                                                                    $langWiki
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>";}

                                                                                        if(get_config('individual_group_bookings')){
                                                                                            $tool_content .= "<div class='col-12 form-group mt-2'>
                                                                                                <div class='checkbox'>
                                                                                                    <label class='label-container' aria-label='$langSelect'>
                                                                                                        <input type='checkbox' name='booking_$gr->id' $checked[$booking]>
                                                                                                        <span class='checkmark'></span>
                                                                                                        $langBookings
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>";
                                                                                        }

                                                                 $tool_content .= " </div>";

                                                                $tool_content .="</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>";

                                                $tool_content .= "<input type='hidden' name='group_id[]' value='$gr->id'></input>";

                                            }

                     $tool_content .= "</tbody>
                                    </table>
                                </div>

                                 <div class='col-12 mt-5'><input type='button' class='btn submitAdminBtn mx-auto' value='$langModify' onClick='submitDetailsForm()' /></div>
                                ";

                            }else{
                                $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoGroupAvailable</span></div></div>";
                            }

$tool_content .= "

                        </fieldset>
                    </form>
                  </div>";


$head_content .= "<script type='text/javascript'>
                    function submitDetailsForm() {
                        $('#formId').submit();
                    }
                  </script>
              ";

draw($tool_content, 2, null, $head_content);
