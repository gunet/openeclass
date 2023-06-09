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
 * @file groups_managment.php
 * @brief Page in order to manage groups
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

$tool_content = '';
$selectedCategory = '';
$selectedCategory2 = '';
$selectedCategory3 = '';

//check if social bookmarking is enabled for this course
$social_bookmarks_enabled = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);

// Display all available groups
$groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
$num_of_groups = count($groupSelect); 

// Search groups by catecory
if(isset($_POST['searchGroupByCategory'])){
    if($_POST['searchGroupByCategory'] == 0){
        $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        $selectedCategory2 = 'selected';
    }elseif($_POST['searchGroupByCategory'] == -1){
        $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND category_id = ?d ORDER BY name", $course_id,0);
        $selectedCategory3 = 'selected';
    }else{
        $groupSelect = Database::get()->queryArray("SELECT * FROM `group` WHERE course_id = ?d AND category_id = ?d ORDER BY name", $course_id,$_POST['searchGroupByCategory']);
        $selectedCategory = $_POST['searchGroupByCategory'];
    }
    $num_of_groups = count($groupSelect); 
    
}
$tool_content .= "<div class='row'>
                    <div class='col-xl-7 col-md-6 col-2'>";
                        $tool_content .= action_bar(array(
                        array(  'title' => $langBack,
                                    'url' => "index.php?course=$course_code",
                                    'icon' => 'fa-reply',
                                    'level' => 'primary-label'),
                        ));
$tool_content .= "  </div>
                    <div class='col-xl-5 col-md-6 col-10'>";
                        $tool_content .= "
                        <form method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                            <div class='col-12'>
                                <select class='form-select rounded-2 py-0 mt-0' name='searchGroupByCategory' onchange='this.form.submit()'>
                                    <option value='0' $selectedCategory2>$langAllGroups</option>
                                    <option value='-1' $selectedCategory3>$langAllGroupsWithoutCategory</option>";
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
if(isset($_POST['modify'])){
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

        $self_reg = $allow_unreg = $has_forum = $documents = $wiki = $public_users_list = 0;

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
        $private_forum = $_POST[$private_forum_group_id];
        

        Database::get()->query("UPDATE group_properties SET
                                self_registration = ?d,
                                allow_unregister = ?d,
                                forum = ?d,
                                private_forum = ?d,
                                documents = ?d,
                                wiki = ?d,
                                public_users_list = ?d
                        WHERE course_id = ?d AND group_id = ?d",
            $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $wiki, $public_users_list, $course_id, $group_id);

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
            $message .= "<p class='TextBold fs-6 text-dark mb-1'>$result->name</p><p>$langGroupMembersUnchanged</p>";
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

    redirect_to_home_page("modules/group/groups_managment.php?course=$course_code");
    
}

// Display groups by result
$tool_content .= "<div class='col-12'>
                    <form method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                        <div class='row row-cols-1 row-cols-md-2 g-4'>";
                            $pagesPag = 0;
                            $allGroups = 0;
                            $temp_pages = 0;
                            $countCards = 1;
                            if($countCards == 1){
                                $pagesPag++;
                            }
                            if($num_of_groups > 0){
                                foreach($groupSelect as $gr){

                                    if (!is_group_visible($gr->id, $course_id)) {
                                        $link_class = 'fa fa-eye-slash';
                                    } else {
                                        $link_class = '';
                                    }

                                    $temp_pages++;

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

                                    $tool_content .= "<div class='col cardGroup$pagesPag'>
                                                        <div class='card panelCard card$pagesPag px-lg-4 py-lg-3 h-100'>
                                                            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                                
                                                                    <a class='ViewGroup TextSemiBold fs-6' href='group_space.php?course=$course_code&amp;group_id=$gr->id'>" . q($gr->name) . "</a>
                                                                    <i class='$link_class text-danger'></i>
                                                            </div>
                                                            
                                                            <div class='card-body'> ";

                                                        $tool_content .= "<div class='form-group'>
                                                                            <label class='col-sm-12 control-label-notes'>$langGroupTutor</label>
                                                                            <select name='tutor_$gr->id[]' multiple id='select-tutor_$gr->id' class='form-select'>\n";
                                                                                $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname,
                                                                                                            user.id IN (SELECT user_id FROM group_members
                                                                                                                                        WHERE group_id = ?d AND
                                                                                                                                                is_tutor = 1) AS is_tutor
                                                                                                        FROM course_user, user
                                                                                                        WHERE course_user.user_id = user.id AND
                                                                                                                course_user.tutor = 1 AND
                                                                                                                course_user.course_id = ?d
                                                                                                        ORDER BY surname, givenname, user_id", $gr->id, $course_id);
                                                                                foreach ($q as $row) {
                                                                                    $selected = $row->is_tutor ? ' selected="selected"' : '';
                                                                                    $tool_content .= "<option value='$row->user_id'$selected>" . q($row->surname) .
                                                                                            ' ' . q($row->givenname) . "</option>";
                                                                                }
                                                        $tool_content .= "</select>
                                                                        </div>";

                                            $tool_content .= "
                                                                   
                                                                        <div class='form-group mt-4'>
                                                                            <label class='col-sm-12 control-label-notes mb-1'>$langMax $langGroupPlacesThis</label>
                                                                            <div class='col-sm-12'>
                                                                                <input class='form-control rounded-pill bgEclass border-0' type=text name='maxStudent_$gr->id' size=2 value='$max_members'>
                                                                            </div>";
                                                                            $membersAvailable = Database::get()->querySingle("SELECT COUNT(*) AS count FROM group_members
                                                                                                                            WHERE group_id = ?d
                                                                                                                            AND is_tutor = 0", $gr->id)->count;
                                                        $tool_content .= "<div class='help-block mt-1'>$langGroupMembers:&nbsp<span class='badge bg-info'>$membersAvailable</span></div>
                                                                        </div>
                                                                   
                                                                   
                                                                        <div class='form-group mt-4'>
                                                                            <label for='selectcategory_$gr->id' class='col-sm-6 control-label-notes'>$langCategory</label>
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


                                                                $tool_content .= "
                                                                
                                                                   
                                                                        <div class='form-group mt-4'>
                                                                            <label class='col-sm-12 control-label-notes mb-2'>$langGroupStudentRegistrationType</label>
                                                                            <div class='col-sm-12'>
                                                                                <div class='checkbox'>
                                                                                    <label>
                                                                                        <input type='checkbox' name='self_reg_$gr->id' $checked[$self_reg]>
                                                                                        $langGroupAllowStudentRegistration
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                
                                                                        <div class='form-group mt-4'>
                                                                            <label class='col-sm-12 control-label-notes mb-2'>$langGroupAllowUnregister</label>
                                                                            <div class='col-sm-12'>
                                                                                <div class='checkbox'>
                                                                                    <label>
                                                                                        <input type='checkbox' name='allow_unreg_$gr->id' $checked[$allow_unreg]>
                                                                                        $langGroupAllowStudentUnregister
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                  
                                                                        <div class='form-group mt-4'>
                                                                            <label class='col-sm-12 control-label-notes mb-2'>$langPrivate_1</label>
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
                                                                  
                                                                 
                                                                        <div class='form-group mt-4 d-flex justify-content-start align-items-start'>
                                                                            <div class='checkbox'>
                                                                                <label>
                                                                                    <input type='checkbox' name='public_users_list_$gr->id' $checked[$public_users_list]>
                                                                                </label>
                                                                            </div>
                                                                            <label class='help-block basicFontSize'>$langGroupPublicUserList</label>
                                                                        </div>
                                                              
                                                                 
                                                                        <div class='form-group mt-4 d-flex justify-content-start align-items-start'>
                                                                            <div class='checkbox'>
                                                                                <label>
                                                                                    <input type='checkbox' name='forum_$gr->id' $checked[$has_forum]>
                                                                                </label>
                                                                            </div> 
                                                                            <label class='help-block basicFontSize'>$langGroupForum</label>
                                                                        </div>
                                                                 
                                                                
                                                                  
                                                                        <div class='form-group  mt-4 d-flex justify-content-start align-items-start'>
                                                                            <div class='checkbox'>
                                                                                <label>
                                                                                    <input type='checkbox' name='documents_$gr->id' $checked[$documents]>
                                                                                </label>
                                                                            </div>
                                                                            <label class='help-block basicFontSize'>$langDoc</label>
                                                                        </div>
                                                                 

                                                                
                                                                        <div class='form-group mt-4 d-flex justify-content-start align-items-start'>
                                                                            <div class='checkbox'>
                                                                                <label>
                                                                                    <input type='checkbox' name='wiki_$gr->id' $checked[$wiki]>
                                                                                </label>
                                                                            </div>
                                                                            <label class='help-block basicFontSize'>$langWiki</label>
                                                                        </div>
                                                                 
                                                                
                                                            ";

                                    $tool_content .= "      
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <input type='hidden' name='group_id[]' value='$gr->id'></input>";


                                    if($countCards == 4 and $temp_pages < count($groupSelect)){
                                        $pagesPag++;
                                        $countCards = 0;
                                    }
                                    $countCards++;
                                    $allGroups++;
                                }

                                $tool_content .= "<div class='form-group mt-5 w-100'>
                                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                                        ".
                                                        form_buttons(array(
                                                            array(
                                                                'class' => 'submitAdminBtn',
                                                                'text'  =>  $langSave,
                                                                'name'  =>  'modify',
                                                                'value' =>  $langModify,
                                                                'javascript' => "selectAll('members_box',true)"
                                                            ),
                                                            array(
                                                                'class' => 'cancelAdminBtn ms-1',
                                                                'href'  =>  "index.php?course=$course_code"
                                                            )
                                                        ))
                                                        ."
                                                    </div>
                                                </div>";

                                // Card's pagination
                                $tool_content .= "<input type='hidden' id='KeyallGroup' value='$allGroups'>
                                        <input type='hidden' id='KeypagesGroup' value='$pagesPag'>
                                        <div class='col-12'>
                                        <div class='col-12 d-flex justify-content-center p-0 overflow-auto bg-white rounded-pill mt-2 mb-3 shadow-sm'>
                                            <nav aria-label='Page navigation example w-100'>
                                                <ul class='pagination mycourses-pagination w-100 mb-0'>
                                                    <li class='page-item page-item-previous'>
                                                        <a class='page-link bg-white' href='#'><span class='fa fa-chevron-left'></span></a>
                                                    </li>";
                                                    if($pagesPag >=12 ){
                                                        for($i=1; $i<=$pagesPag; $i++){
                                                        
                                                            if($i>=1 && $i<=5){
                                                                if($i==1){
                                                                    $tool_content .="
                                                                        <li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                            <a id='Keypage$i' class='page-link' href='#'>$i</a>
                                                                        </li>
                        
                                                                        <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                                            <a>...</a>
                                                                        </li>";
                                                                }else{
                                                                    if($i<$pagesPag){
                                                                        $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                            <a id='Keypage$i' class='page-link' href='#'>$i</a>
                                                                                        </li>";
                                                                    }
                                                                }
                                                            }
                    
                                                            if($i>=6 && $i<=$pagesPag-1){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages d-none'>
                                                                                    <a id='Keypage$i' class='page-link' href='#'>$i</a>
                                                                                </li>";
                    
                                                                if($i==$pagesPag-1){
                                                                    $tool_content .="<li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                                                        <a>...</a>
                                                                                    </li>";
                                                                }
                                                            }
                    
                                                            if($i==$pagesPag){
                                                                $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                    <a id='Keypage$i' class='page-link' href='#'>$i</a>
                                                                                </li>";
                                                            }
                                                        }
                                                    
                                                    }else{
                                                        for($i=1; $i<=$pagesPag; $i++){
                                                            $tool_content .="<li id='KeypageCenter$i' class='page-item page-item-pages'>
                                                                                <a id='Keypage$i' class='page-link' href='#'>$i</a>
                                                                            </li>";
                                                        }
                                                    }
                    
                                    $tool_content .="<li class='page-item page-item-next'>
                                                        <a class='page-link bg-white' href='#'><span class='fa fa-chevron-right'></span></a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div></div>";

                            }else{
                                $tool_content .= "<div class='col-12'><div class='alert alert-warning'>$langNoGroupInCategory</div></div>";
                            }


$tool_content .= "      </div>
                    </form>
                  </div>";






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
