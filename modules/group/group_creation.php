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
 * @file group_creation.php
 * @brief create users group
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'groups';
$helpSubTopic = 'create';
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'group_functions.php';

$toolName = $langGroups;
$pageName = $langCreateOneGroup;
if (isset($_GET['all'])) {
    $pageName = $langCreationGroups;
}

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);

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

$group_max_value = Session::has('group_max') ? Session::get('group_max') : 0;
$group_quantity_value = Session::has('group_quantity') ? Session::get('group_quantity') : 1;

if (isset($_GET['all'])) {
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>
        <fieldset>
        <legend class='mb-0' aria-label='$langForm'></legend>
        <div class='form-group".(Session::getError('group_quantity') ? " has-error":"")."'>
            <label for='group_quantity' class='col-sm-12 control-label-notes'>$langNewGroups <span class='asterisk Accent-200-cl'>(*)</span></label>
            <div class='col-sm-12'>
                <input name='group_quantity' type='text' class='form-control' id='group_quantity' value='$group_quantity_value' placeholder='$langNewGroups'>
                <span class='help-block Accent-200-cl'>".Session::getError('group_quantity')."</span>
            </div>
        </div>
        <div class='form-group".(Session::getError('group_max') ? " has-error":"")." mt-4'>
            <label for='group_max' class='col-sm-12 control-label-notes'>$langNewGroupMembers <span class='asterisk Accent-200-cl'>(*)</span></label>
            <div class='col-sm-12'>
                <input name='group_max' type='text' class='form-control' id='group_max' value='$group_max_value' placeholder='$langNewGroupMembers'>
                <span class='help-block Accent-200-cl'>".(Session::getError('group_max') ?: "$langGroupInfiniteUsers")."</span>
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='selectcategory' class='col-sm-12 control-label-notes'>$langCategory:</label>
            <div class='col-12'>
                <select class='form-select' name='selectcategory' id='selectcategory'>
                <option value='0'>--</option>";
        if ($social_bookmarks_enabled) {
            $tool_content .= "<option value='-2'";
            if (isset($category) and $category == -2) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= ">$langSocialCategory</option>";
        }
        $resultcategories = Database::get()->queryArray("SELECT * FROM group_category WHERE course_id = ?d ORDER BY `name`", $course_id);
        foreach ($resultcategories as $myrow) {
            $tool_content .= "<option value='" . $myrow->id . "'";
            if (isset($category) and $myrow->id == $category) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= '>' . q($myrow->name) . "</option>";
        }
        $tool_content .= "
            </select>
            </div>
        </div>";
        $tool_content .= "<input type='hidden' name='all' value='$_GET[all]'>";
        $tool_content .= "<div class='form-group mt-5'>
        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
            <input class='btn submitAdminBtn' type='submit' value='$langSubmit' name='creation'>
            <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
        </div>
        </div>
        </fieldset>
        </form>
    </div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
} else {
    if ($is_editor) {
            $tool_content_tutor = "<select name='tutor[]' multiple id='select-tutor' class='form-select h-100 rounded-0'>";
            $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname
                                                FROM course_user, user
                                                WHERE course_user.user_id = user.id AND
                                                      course_user.course_id = ?d AND
                                                      course_user.status != " . USER_GUEST . " AND
                                                      user.expires_at >= " . DBHelper::timeAfter() . "
                                                ORDER BY course_user.status, surname, givenname, user_id", $course_id);
            foreach ($q as $row) {
                $tool_content_tutor .= "<option value='$row->user_id'>" . q($row->surname) .
                    ' ' . q($row->givenname) . "</option>\n";
            }
            $tool_content_tutor .= '</select>';
    } else {
            $tool_content_tutor = display_user($tutors);
    }
    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>
        <fieldset>
        <legend class='mb-0' aria-label='$langForm'></legend>
        <div class='form-group".(Session::getError('group_name') ? " has-error" : "")."'>
            <label for='group_name_id' class='col-sm-12 control-label-notes'>$langGroupName <span class='asterisk Accent-200-cl'>(*)</span></label>
            <div class='col-sm-12'>
                <input id='group_name_id' class='form-control' type=text name='group_name' size='40'>
                <span class='help-block Accent-200-cl'>".Session::getError('group_name')."</span>
            </div>
        </div>
        <div class='form-group mt-4'>
          <label for='description_id' class='col-sm-12 control-label-notes'>$langDescription $langOptional</label>
          <div class='col-sm-12'><textarea id='description_id' class='form-control' name='description' rows='2' cols='60'></textarea></div>
        </div>
       
           
                <div class='form-group".(Session::getError('group_max') ? " has-error" : "")." mt-4'>
                    <label for='group_max_id' class='col-sm-12 control-label-notes'>$langNewGroupMembers</label>
                    <div class='col-sm-12'>
                        <input id='group_max_id' class='form-control' type=text name='group_max' value='$group_max_value' size=2>
                        <span class='help-block'>".(Session::getError('group_max') ?: "$langGroupInfiniteUsers")."</span>
                    </div>
                </div>
            
            
                <div class='form-group mt-4'>
                    <label for='select-tutor' class='col-sm-12 control-label-notes mb-2'>$langGroupTutor</label>
                    <div class='col-sm-12'>
                        $tool_content_tutor
                    </div>
                </div>
            
        ";

    $multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);

    if ($multi_reg) {
        // All students registered to the course
        $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                            FROM user u, course_user cu
                            WHERE cu.course_id = ?d AND
                                  cu.user_id = u.id AND
                                  cu.status = " . USER_STUDENT . " AND
                                  u.expires_at >= " . DBHelper::timeAfter() . "
                            GROUP BY u.id, u.surname, u.givenname, u.am
                            ORDER BY u.surname, u.givenname", $course_id);
    } else {
        // Students registered to the course but members of no group
        $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                                                FROM (user u, course_user cu)
                                                    WHERE cu.course_id = $course_id AND
                                                          cu.user_id = u.id AND
                                                          cu.status = " . USER_STUDENT . " AND
                                                          u.expires_at >= " . DBHelper::timeAfter() . " AND
                                                          u.id NOT IN (SELECT user_id FROM group_members, `group`
                                                                        WHERE `group`.id = group_members.group_id AND
                                                                        `group`.course_id = ?d)
                                                    GROUP BY u.id, u.surname, u.givenname, u.am 
                                                    ORDER BY u.surname, u.givenname", $course_id);
    }
    $tool_content_not_Member = $tool_content_group_members = '';
    foreach ($resultNotMember as $myNotMember) {
        $tool_content_not_Member .= "<option value='$myNotMember->id'>" .
                q("$myNotMember->surname $myNotMember->givenname") . (!empty($myNotMember->am) ? q(" ($myNotMember->am)") : "") . "</option>";
    }

    $tool_content .= "<div class='form-group mt-4'>
            <div class='col-sm-12 control-label-notes'>$langGroupMembers</div>
        <div class='col-sm-12'>
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr class='title1 list-header'>
                          <th>$langNoGroupStudents</th>
                          <th width='100' class='text-center'>$langMove</th>
                          <th class='right'>$langGroupMembers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                          <td>
                            <select aria-label='$langNoGroupStudents' class='form-select h-100 rounded-0' id='users_box' name='nogroup[]' size='15' multiple>
                              $tool_content_not_Member
                            </select>
                          </td>
                          <td class='text-center'>
                              <div class='form-group'>
                                  <input class='btn btn-sm btn-outline-primary rounded-2 h-40px' type='button' onClick=\"move('users_box','members_box')\" value='   &gt;&gt;   ' />
                              </div>
                              <div class='form-group mt-2'>
                                  <input class='btn btn-sm btn-outline-primary rounded-2 h-40px' type='button' onClick=\"move('members_box','users_box')\" value='   &lt;&lt;   ' />
                              </div>
                          </td>
                          <td class='text-end'>
                            <select aria-label='$langGroupMembers' class='form-select h-100 rounded-0' id='members_box' name='ingroup[]' size='15' multiple>
                              $tool_content_group_members
                            </select>
                          </td>
                        </tr>
                    </tbody>
                </table>
            </div>
      </div>
    </div>
    <div class='form-group mt-4'>
            <label for='selectcategory' class='col-sm-12 control-label-notes'>$langCategory</label>
            <div class='col-sm-12'>
                <select class='form-select' name='selectcategory' id='selectcategory'>
                <option value='0'>--</option>";
        if ($social_bookmarks_enabled) {
            $tool_content .= "<option value='-2'";
            if (isset($category) and -2 == $category) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= ">$langSocialCategory</option>";
        }
        $resultcategories = Database::get()->queryArray("SELECT * FROM group_category WHERE course_id = ?d ORDER BY `name`", $course_id);
        foreach ($resultcategories as $myrow) {
            $tool_content .= "<option value='"  . $myrow->id . "'";
            if (isset($category) and $myrow->id == $category) {
                $tool_content .= " selected='selected'";
            }
            $tool_content .= '>' . q($myrow->name) . "</option>";
        }
        $tool_content .= "
            </select>
            </div>
        </div>
            <div class='form-group mt-4'>
                <div class='col-sm-12 control-label-notes mb-2'>$langGroupStudentRegistrationType</div>
                <div class='col-sm-12'>
                    <div class='checkbox'>
                    <label class='label-container' aria-label='$langSelect'>
                        <input type='checkbox' name='self_reg' checked>
                        <span class='checkmark'></span>$langGroupAllowStudentRegistration
                    </label>
                 </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='col-sm-12 control-label-notes mb-2'>$langGroupAllowUnregister</div>
                <div class='col-sm-12'>
                    <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='allow_unreg'>
                            <span class='checkmark'></span>$langGroupAllowStudentUnregister
                        </label>
                    </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='col-sm-12 control-label-notes mb-2'>$langPrivate_1</div>
                <div class='col-sm-12'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='1' checked=''>
                        $langPrivate_2
                      </label>
                    </div>
                    <div class='radio mt-2'>
                      <label>
                        <input type='radio' name='private_forum' value='0'>
                        $langPrivate_3
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='col-12'>
        
                    
                    <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='forum'>
                            <span class='checkmark'></span>
                            $langGroupForum
                        </label>
                    </div>
                    
                </div>
            </div>
            <div class='form-group mt-4'>
               <div class='col-12'>
                    
                
                    <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='documents'>
                            <span class='checkmark'></span>
                            $langDoc
                        </label>
                    </div>
                   
                </div>
            </div>";

            if((isset($is_collaborative_course) and !$is_collaborative_course)){
            $tool_content .= "
            <div class='form-group mt-4'>
                <div class='col-12'>
                    <div class='checkbox'>
                        <label class='label-container' aria-label='$langSelect'>
                            <input type='checkbox' name='wiki'>
                            <span class='checkmark'></span>
                            $langWiki
                        </label>
                    </div>
                </div>
            </div>";}

            if(get_config('individual_group_bookings')){
                $tool_content .= "
                    <div class='form-group mt-4'>
                        <div class='col-12'>
                            <div class='checkbox'>
                                <label class='label-container' aria-label='$langSelect'>
                                    <input type='checkbox' name='booking'>
                                    <span class='checkmark'></span>
                                    $langBookings
                                </label>
                            </div>
                        </div>
                    </div>";
            }

        $tool_content .= "<input type='hidden' name='group_quantity' value='1'>";
        $tool_content .= "<div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' value='$langSubmit' name='creation' onClick=\"selectAll('members_box', true)\" >
                <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
            </div>
        </div>
        </fieldset>
        </form>
    </div></div>
    <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";
}
draw($tool_content, 2, null, $head_content);
