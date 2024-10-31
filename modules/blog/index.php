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

if (isset($_GET['course'])) { //course blog
    $require_current_course = TRUE;
    $blog_type = 'course_blog';
} else { //personal blog
    $require_login = true;
    $require_valid_uid = TRUE;
    $require_current_course = FALSE;
    $blog_type = 'perso_blog';
}
$require_help = TRUE;
$helpTopic = 'blog';
require_once '../../include/baseTheme.php';
require_once 'modules/comments/class.commenting.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/blog/class.blog.php';
require_once 'modules/blog/class.blogpost.php';
require_once 'include/course_settings.php';
require_once 'modules/sharing/sharing.php';
require_once 'modules/progress/BlogEvent.php';
require_once 'modules/analytics/BlogAnalyticsEvent.php';

if ($blog_type == 'course_blog') {
    $user_id = 0;

    define_rss_link();
    $toolName = $langBlog;

    //check if commenting is enabled for course blogs
    $comments_enabled = setting_get(SETTING_BLOG_COMMENT_ENABLE, $course_id);
    //check if rating is enabled for course blogs
    $ratings_enabled = setting_get(SETTING_BLOG_RATING_ENABLE, $course_id);

    $sharing_allowed = is_sharing_allowed($course_id);
    $sharing_enabled = setting_get(SETTING_BLOG_SHARING_ENABLE, $course_id);

    $url_params = "course=$course_code";
} elseif ($blog_type == 'perso_blog') {
    if (!get_config('personal_blog')) {
        $tool_content = "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langPersoBlogDisabled</span></div></div>";
        draw($tool_content, 1);
        exit;
    }

    $course_id = 0;

    $is_blog_editor = false;

    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        if ($user_id == $_SESSION['uid']) {
            $is_blog_editor = true;
        } elseif (isset($is_admin) && $is_admin) {
            $is_blog_editor = true;
        }
    } else {
        $user_id = $_SESSION['uid']; //current user's blog
        $is_blog_editor = true;
    }

    $db_user = Database::get()->querySingle("SELECT surname, givenname FROM user WHERE id = ?d", $user_id);
    if (!$db_user) {
        $tool_content = "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langBlogUserNotExist</span></div></div>";
        draw($tool_content, 1);
        exit;
    }

    if ($user_id == $_SESSION['uid']) {
        $toolName = $langMyBlog;
    } else {
        $toolName = $langBlog." - ".$db_user->surname." ".$db_user->givenname;
    }
    $navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
    //check if commenting is enabled for personal blogs
    $comments_enabled = get_config('personal_blog_commenting');
    //check if rating is enabled for personal blogs
    $ratings_enabled = get_config('personal_blog_rating');
    //check if sharing is platform widely allowed and enabled for personal blogs
    $sharing_allowed = get_config('enable_social_sharing_links');
    $sharing_enabled = get_config('personal_blog_sharing');

    $url_params = "user_id=$user_id";
}


load_js('tools.js');

$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
		$langEmptyBlogPostTitle . '";</script>';

//define allowed actions
$allowed_actions = array("showBlog", "showPost", "createPost", "editPost", "delPost", "savePost", "settings");

//initialize $_REQUEST vars
$action = (isset($_REQUEST['action']) && in_array($_REQUEST['action'], $allowed_actions))? $_REQUEST['action'] : "showBlog";
$pId = isset($_REQUEST['pId']) ? intval($_REQUEST['pId']) : 0;
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;

//config setting allowing students to create posts and edit/delete own posts
$stud_allow_create = setting_get(SETTING_BLOG_STUDENT_POST, $course_id);

$posts_per_page = 10;
$num_popular = 5;//number of popular blog posts to show in sidebar
$num_chars_teaser_break = 500;//chars before teaser break

if ($blog_type == 'course_blog' && $is_editor) {
    if ($action == "settings") {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBlog);
        $pageName = $langGeneralSettings;
    } elseif ($action == "createPost") {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBlog);
        $pageName = $langBlogAddPost;
    } elseif ($action == "editPost") {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBlog);
        $pageName = $langEditChange;
    }
    $action_bar = action_bar(array(
                         array('title' => $langBack,
                               'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog",
                               'icon' => 'fa-reply',
                               'level' => 'primary',
                               'show' => isset($action) and $action != "showBlog" and $action != "showPost" and $action != "savePost" and $action != "delPost")
    ));
    $tool_content .= $action_bar;
    if ($action == "settings") {

        if (isset($_POST['submitSettings'])) {
            setting_set(SETTING_BLOG_STUDENT_POST, $_POST['1_radio'], $course_id);
            setting_set(SETTING_BLOG_COMMENT_ENABLE, $_POST['2_radio'], $course_id);
            setting_set(SETTING_BLOG_RATING_ENABLE, $_POST['3_radio'], $course_id);
		    if (isset($_POST['4_radio'])) {
                setting_set(SETTING_BLOG_SHARING_ENABLE, $_POST['4_radio'], $course_id);
		    }
            Session::flash('message',$langRegDone);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/blog/index.php?course='.$course_code);
        }

        if (isset($message) && $message) {
        	$tool_content .= $message . "<br/>";
        	unset($message);
        }



        if (setting_get(SETTING_BLOG_STUDENT_POST, $course_id) == 1) {
            $checkTeach = "";
            $checkStud = "checked ";
        } else {
            $checkTeach = "checked ";
            $checkStud = "";
        }
        if (setting_get(SETTING_BLOG_COMMENT_ENABLE, $course_id) == 1) {
        	$checkCommentDis = "";
        	$checkCommentEn = "checked ";
        } else {
        	$checkCommentDis = "checked ";
        	$checkCommentEn = "";
        }
        if (setting_get(SETTING_BLOG_RATING_ENABLE, $course_id) == 1) {
        	$checkRatingDis = "";
        	$checkRatingEn = "checked ";
        } else {
        	$checkRatingDis = "checked ";
        	$checkRatingEn = "";
        }
        if (!$sharing_allowed) {
            $sharing_radio_dis = " disabled";
            $sharing_dis_label = "<tr><td><em>";
            if (!get_config('enable_social_sharing_links')) {
                $sharing_dis_label .= $langSharingDisAdmin;
            }
            if (course_status($course_id) != COURSE_OPEN) {
                $sharing_dis_label .= " ".$langSharingDisCourse;
            }
            $sharing_dis_label .= "</em></td></tr>";
        } else {
            $sharing_radio_dis = "";
            $sharing_dis_label = "";
        }

        if ($sharing_enabled == 1) {
            $checkSharingDis = "";
            $checkSharingEn = "checked";
        } else {
            $checkSharingDis = "checked";
            $checkSharingEn = "";
        }

        $flex_content = '';
        $flex_grow = '';
        $column_content = '';

        if(isset($course_id) and $course_id){
            $flex_content = 'd-lg-flex gap-4';
            $flex_grow = 'flex-grow-1';
            $column_content = 'form-content-modules';
        }else{
            $flex_content = 'row m-auto';
            $flex_grow = 'col-lg-6 col-12 px-0';
            $column_content = 'col-lg-6 col-12';
        }

        $tool_content .= "

            <div class='$flex_content mt-4'>
                <div class='$flex_grow'>
                    <div class='form-wrapper form-edit rounded border-0 px-0'>
                        <form class='form-horizontal' action='' role='form' method='post'>
                            <fieldset>
                                <legend class='mb-0' aria-label='$langForm'></legend>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>$langBlogPerm</div>
                                    <div class='col-sm-12'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='1_radio' $checkTeach> $langBlogPermTeacher
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='1_radio' $checkStud> $langBlogPermStudents
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class='mb-0' aria-label='$langForm'></legend>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>$langBlogCommenting</div>
                                    <div class='col-sm-12'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='2_radio' $checkCommentEn> $langCommentsEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='2_radio' $checkCommentDis> $langCommentsDis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>$langBlogRating:</div>
                                    <div class='col-sm-12'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='3_radio' $checkRatingEn> $langRatingEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='3_radio' $checkRatingDis> $langRatingDis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>$langBlogSharing:</div>
                                    <div class='col-sm-12'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='4_radio' $checkSharingEn $sharing_radio_dis> $langSharingEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='4_radio' $checkSharingDis $sharing_radio_dis> $langSharingDis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class='form-group mt-5 d-flex justify-content-end align-items-center'>



                                         ".
                                         form_buttons(array(
                                             array(
                                                 'class' => 'submitAdminBtn',
                                                 'text'  =>  $langSave,
                                                 'name'  =>  'submitSettings',
                                                 'value' =>  $langSubmit
                                             ),
                                             array(
                                                'class' => 'cancelAdminBtn ms-1',
                                                 'href'  =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog"
                                             )
                                         ))
                                         ."




                            </div>
                        </form>
                    </div>
                </div>
                <div class='$column_content d-none d-lg-block'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>
                ";



    }
} elseif ($blog_type == 'perso_blog' && $is_blog_editor) {
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?user=$user_id&amp;action=showBlog",
                  'icon' => 'fa-reply',
                  'level' => 'primary',
                  'show' => isset($action) and $action != "showBlog" and $action != "showPost" and $action != "savePost" and $action != "delPost")
    ),false);
}

//instantiate the object representing this blog
$blog = new Blog($course_id, $user_id);

//delete post
if ($action == "delPost") {
    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        //different criteria regarding editing posts for different blog types
        if ($blog_type == 'course_blog') {
            $allow_to_edit = $post->permEdit($is_editor, $stud_allow_create, $uid);
        } elseif ($blog_type == 'perso_blog') {
            $allow_to_edit = $is_blog_editor;
        }
        if ($allow_to_edit) {
            if($post->delete()) {
                Session::flash('message',$langBlogPostDelSucc);
                Session::flash('alert-class', 'alert-success');
                triggerGame($course_id, $uid, BlogEvent::DELPOST);
                triggerAnalytics($course_id, $uid, BlogAnalyticsEvent::BLOGEVENT);
            } else {
                Session::flash('message',$langBlogPostDelFail);
                Session::flash('alert-class', 'alert-danger');
            }
        } else {
            Session::flash('message',$langBlogPostNotAllowedDel);
            Session::flash('alert-class', 'alert-danger');
        }
    } else {
        Session::flash('message',$langBlogPostNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page("modules/blog/index.php?".str_replace('&amp;', '&', $url_params));
}

//create blog post form
if ($action == "createPost") {
    //different criteria regarding creating posts for different blog types
    if ($blog_type == 'course_blog') {
        $allow_to_create = $blog->permCreate($is_editor, $stud_allow_create, $uid);
    } elseif ($blog_type == 'perso_blog') {
        $allow_to_create = $is_blog_editor;
    }
    if ($allow_to_create) {
        $commenting_setting = '';
        if ($comments_enabled) {
            $commenting_setting = "<div class='form-group mt-4'>
                                       <div class='col-sm-12 control-label-notes mb-2'>$langBlogPostCommenting <span class='asterisk Accent-200-cl'>(*)</span></div>

                                           <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='commenting' checked>
                                                    $langCommentsEn
                                                </label>
                                           </div>
                                           <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='commenting'>
                                                    $langCommentsDis
                                                </label>
                                           </div>

                                   </div>";
        }


        $flex_content = '';
        $flex_grow = '';
        $column_content = '';

        if(isset($course_id) and $course_id){
            $flex_content = 'd-lg-flex gap-4';
            $flex_grow = 'flex-grow-1';
            $column_content = 'form-content-modules';
        }else{
            $flex_content = 'row m-auto';
            $flex_grow = 'col-lg-6 col-12 px-0';
            $column_content = 'col-lg-6 col-12';
        }

        $tool_content .= "
<div class='col-12'>
    <div class='$flex_content mt-4'>
        <div class='$flex_grow'>
            <div class='form-wrapper form-edit rounded border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?$url_params' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
                    <fieldset>

                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                            <label for='blogPostTitle' class='col-sm-12 control-label-notes'>$langBlogPostTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='blogPostTitle' id='blogPostTitle' placeholder='$langBlogPostTitle'>
                            </div>
                        </div>



                        <div class='form-group mt-4'>
                            <label for='newContent' class='col-sm-12 control-label-notes'>$langBlogPostBody</label>
                            <div class='col-sm-12'>
                                ".rich_text_editor('newContent', 4, 20, '')."
                            </div>
                        </div>
                        $commenting_setting

                        <div class='form-group mt-5 d-flex justify-content-end align-items-center'>



                                    ".
                                    form_buttons(array(
                                        array(
                                            'class' => 'submitAdminBtn',
                                            'text'  =>  $langSave,
                                            'name'  =>  'submitBlogPost',
                                            'value' =>  $langAdd
                                        ),
                                        array(
                                            'class' => 'cancelAdminBtn ms-1',
                                            'href'  =>  "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog"
                                        )
                                    ))
                                    ."




                        </div>
                        <input type='hidden' name='action' value='savePost' />
                    </fieldset>
                </form>
            </div>
        </div>
        <div class='$column_content d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
    </div>
</div>

        ";

    } else {
        Session::flash('message',$langBlogPostNotAllowedCreate);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/blog/index.php?$url_params");
    }

}

//edit blog post form
if ($action == "editPost") {
    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        //different criteria regarding creating posts for different blog types
        if ($blog_type == 'course_blog') {
            $allow_to_edit = $post->permEdit($is_editor, $stud_allow_create, $uid);
        } elseif ($blog_type == 'perso_blog') {
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]", 'name' => $langBlog);
            $pageName = $langEditChange;
            $allow_to_edit = $is_blog_editor;
        }
        if ($allow_to_edit) {
            $commenting_setting = '';
            if ($comments_enabled) {
                if ($post->getCommenting() == 1) {
                    $checkCommentEn = 'checked';
                    $checkCommentDis = '';
                } elseif ($post->getCommenting() == 0) {
                    $checkCommentEn = '';
                    $checkCommentDis = 'checked';
                }
                $commenting_setting = "
                                        <div class='form-group mt-4'>
                                              <div class='col-sm-12 control-label-notes mb-2'>$langBlogPostCommenting:</div>

                                               <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' value='1' name='commenting' $checkCommentEn>
                                                        $langCommentsEn
                                                    </label>
                                               </div>
                                               <div class='radio'>
                                                    <label>
                                                        <input type='radio' value='0' name='commenting' $checkCommentDis>
                                                        $langCommentsDis
                                                    </label>
                                               </div>

                                       </div>";
            }

            $flex_content = '';
            $flex_grow = '';
            $column_content = '';

            if(isset($course_id) and $course_id){
                $flex_content = 'd-lg-flex gap-4';
                $flex_grow = 'flex-grow-1';
                $column_content = 'form-content-modules';
            }else{
                $flex_content = 'row m-auto';
                $flex_grow = 'col-lg-6 col-12 px-0';
                $column_content = 'col-lg-6 col-12';
            }

            $tool_content .= "
<div class='col-12'>
    <div class='$flex_content mt-4'>
        <div class='$flex_grow'>
            <div class='form-wrapper form-edit rounded border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?$url_params' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                            <label for='blogPostTitle' class='col-sm-12 control-label-notes'>$langBlogPostTitle:</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='blogPostTitle' id='blogPostTitle' value='".q($post->getTitle())."' placeholder='$langBlogPostTitle'>
                            </div>
                        </div>



                        <div class='form-group mt-4'>
                            <label for='newContent' class='col-sm-12 control-label-notes'>$langBlogPostBody:</label>
                            <div class='col-sm-12'>
                                ".rich_text_editor('newContent', 4, 20, $post->getContent())."
                            </div>
                        </div>
                        $commenting_setting

                        <div class='form-group mt-5 d-flex justify-content-end align-items-center'>


                                ".
                                form_buttons(array(
                                    array(
                                        'class' => 'submitAdminBtn',
                                        'text'  =>  $langSave,
                                        'name'  =>  'submitBlogPost',
                                        'value' =>  $langModifBlogPost
                                    ),
                                    array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href'  =>  "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog"
                                    )
                                ))
                                ."




                        </div>
                        <input type='hidden' name='action' value='savePost'>
                        <input type='hidden' name='pId' value='".$post->getId()."'>
                    </fieldset>
                </form>
            </div>
        </div>

        <div class='$column_content d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
    </div>
</div>
       ";



        } else {
            Session::flash('message',$langBlogPostNotAllowedEdit);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/blog/index.php?".str_replace('&amp;', '&', $url_params));
        }
    } else {
        Session::flash('message',$langBlogPostNotFound);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/blog/index.php?".str_replace('&amp;', '&', $url_params));
    }
}

//save blog post
if ($action == "savePost") {

    if (isset($_POST['submitBlogPost']) && $_POST['submitBlogPost'] == $langAdd) {
        //different criteria regarding creating posts for different blog types
        if ($blog_type == 'course_blog') {
            $allow_to_create = $blog->permCreate($is_editor, $stud_allow_create, $uid);
        } elseif ($blog_type == 'perso_blog') {
            $allow_to_create = $is_blog_editor;
        }
        if ($allow_to_create) {
            $post = new BlogPost();
            if (isset($_POST['commenting'])) {
                $commenting = intval($_POST['commenting']);
            } else {
                $commenting = NULL;
            }
            if ($post->create($_POST['blogPostTitle'], purify($_POST['newContent']), $uid, $course_id, $commenting)) {
                Session::flash('message',$langBlogPostSaveSucc);
                Session::flash('alert-class', 'alert-success');
                triggerGame($course_id, $uid, BlogEvent::NEWPOST);
                triggerAnalytics($course_id, $uid, BlogAnalyticsEvent::BLOGEVENT);
            } else {
                Session::flash('message',$langBlogPostSaveFail);
                Session::flash('alert-class', 'alert-danger');
            }
        } else {
            Session::flash('message',$langBlogPostNotAllowedCreate);
            Session::flash('alert-class', 'alert-danger');
        }
    } elseif (isset($_POST['submitBlogPost']) && $_POST['submitBlogPost'] == $langModifBlogPost) {
        $post = new BlogPost();
        if ($post->loadFromDB($_POST['pId'])) {
            //different criteria regarding creating posts for different blog types
            if ($blog_type == 'course_blog') {
                $allow_to_edit = $post->permEdit($is_editor, $stud_allow_create, $uid);
            } elseif ($blog_type == 'perso_blog') {
                $allow_to_edit = $is_blog_editor;
            }
            if ($allow_to_edit) {
                if (isset($_POST['commenting'])) {
                    $commenting = intval($_POST['commenting']);
                } else {
                    $commenting = NULL;
                }
                if ($post->edit($_POST['blogPostTitle'], purify($_POST['newContent']), $commenting)) {
                    Session::flash('message',$langBlogPostSaveSucc);
                    Session::flash('alert-class', 'alert-success');
                } else {
                    Session::flash('message',$langBlogPostSaveFail);
                    Session::flash('alert-class', 'alert-danger');
                }
            } else {
                Session::flash('message',$langBlogPostNotAllowedEdit);
                Session::flash('alert-class', 'alert-danger');
            }
        } else {
            Session::flash('message',$langBlogPostNotFound);
            Session::flash('alert-class', 'alert-danger');
        }
    }
    redirect_to_home_page("modules/blog/index.php?".str_replace('&amp;', '&', $url_params));
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
}

//show blog post
if ($action == "showPost") {
    $action_bar = action_bar(array(
            array('title' => $langBack,
                    'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog",
                    'icon' => 'fa-reply',
                    'level' => 'primary')
    ));
    $tool_content .= $action_bar;

    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        if ($blog_type == 'course_blog') {
            $allow_to_edit = $post->permEdit($is_editor, $stud_allow_create, $uid);
        } elseif ($blog_type == 'perso_blog') {
            $allow_to_edit = $is_blog_editor;
        }
        $post->incViews();
        $sharing_content = '';
        $rating_content = '';
        if ($sharing_allowed) {
            $sharing_content = ($sharing_enabled) ? print_sharing_links($urlServer."modules/blog/index.php?$url_params&amp;action=showPost&amp;pId=".$post->getId(), $post->getTitle()) : '';
        }
        if ($ratings_enabled) {
            $rating = new Rating('up_down', 'blogpost', $post->getId());
            if ($blog_type == 'course_blog') {
                $rating_content = $rating->put($is_editor, $uid, $course_id);
            } elseif ($blog_type == 'perso_blog') {
                //in this case send user_id as third argument instead of course_id which is 0
                //since we only need this info for identifying user's blog
                $rating_content = $rating->put(NULL, $uid, $user_id);
            }
        }


        $tool_content .= "
                    <div class='col-12'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>

                                <h3>
                                    ".q($post->getTitle())."
                                </h3>

                                <div>
                                    ". action_button(array(
                                        array(
                                            'title' => $langEditChange,
                                            'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=editPost&amp;pId=".$post->getId(),
                                            'icon' => 'fa-edit',
                                            'show' => $allow_to_edit
                                        ),
                                        array(
                                            'title' => $langDelete,
                                            'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=delPost&amp;pId=".$post->getId(),
                                            'icon' => 'fa-xmark',
                                            'class' => 'delete',
                                            'confirm' => $langSureToDelBlogPost,
                                            'show' => $allow_to_edit
                                        ),
                                        array(
                                            'title' => $langAddResePortfolio,
                                            'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=blog&amp;rid=".$post->getId(),
                                            'icon' => 'fa-star',
                                            'show' => (get_config('eportfolio_enable') && $post->getAuthor()==$uid)
                                        ),
                                    ))."
                                </div>

                            </div>
                            <div class='card-body'>" . format_locale_date(strtotime($post->getTime())). "</p><small>".$langBlogPostUser.display_user($post->getAuthor(), false, false)."</small><br><br>".standard_text_escape($post->getContent())."</div>
                            <div class='card-footer border-0 d-flex justify-content-between align-items-center'>


                                    <div>$rating_content</div>
                                    <div>$sharing_content</div>

                            </div>
                        </div>
                    </div>";

        if ($comments_enabled) {
            $tool_content .= "
                    <div class='col-12'>";
                        if ($post->getCommenting() == 1) {
                            commenting_add_js(); //add js files needed for comments
                            $comm = new Commenting('blogpost', $post->getId());
                            if ($blog_type == 'course_blog') {
                                $tool_content .= $comm->put($course_code, $is_editor, $uid, true);
                            } elseif ($blog_type == 'perso_blog') {
                                $tool_content .= $comm->put(NULL, $is_blog_editor, $uid, true);
                            }
                        }
            $tool_content .= "</div>";
        }

    } else {
        Session::flash('message',$langBlogPostNotFound);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/blog/index.php?".str_replace('&amp;', '&', $url_params));
    }

}

//show all blog posts
if ($action == "showBlog") {
    if ($blog_type == 'course_blog') {
        $allow_to_create = $blog->permCreate($is_editor, $stud_allow_create, $uid);
    } elseif ($blog_type == 'perso_blog') {
        $allow_to_create = $is_blog_editor;
    }
    $action_bar = action_bar(array(
                        array('title' => $langBlogAddPost,
                              'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=createPost",
                              'icon' => 'fa-plus-circle',
                              'level' => 'primary',
                              'button-class' => 'btn-success',
                              'show' => $allow_to_create),
                        array('title' => $langConfig,
                              'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=settings",
                              'icon' => 'fa-gear',
                              'level' => 'primary',
                              'show' => ($blog_type == 'course_blog') && $is_editor && $blog->permCreate($is_editor, $stud_allow_create, $uid))
                     ));
    $tool_content .= $action_bar;
    $num_posts = $blog->blogPostsNumber();
    if ($num_posts == 0) {//no blog posts
        $tool_content .= "
            <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langBlogEmpty</span></div></div>";
    } else {//show blog posts
        //if page num was changed at the url and exceeds pages number show the first page
        if ($page > ceil($num_posts/$posts_per_page)-1) {
            $page = 0;
        }

        //retrieve blog posts
        $posts = $blog->getBlogPostsDB($page, $posts_per_page);

        /***blog posts area***/
        $tool_content .= "<div class='row row-cols-1 row-cols-md-2 g-3 g-md-4'>";
        $tool_content .= "<div class='col-md-8'>";
        foreach ($posts as $post) {
            if ($blog_type == 'course_blog') {
                $allow_to_edit = $post->permEdit($is_editor, $stud_allow_create, $uid);
            } elseif ($blog_type == 'perso_blog') {
                $allow_to_edit = $is_blog_editor;
            }
            $sharing_content = '';
            $rating_content = '';
            if ($sharing_allowed) {
                $sharing_content = ($sharing_enabled) ? print_sharing_links($urlServer."modules/blog/index.php?$url_params&amp;action=showPost&amp;pId=".$post->getId(), $post->getTitle()) : '';
            }
            if ($ratings_enabled) {
                $rating = new Rating('up_down', 'blogpost', $post->getId());
                if ($blog_type == 'course_blog') {
                    $rating_content = $rating->put($is_editor, $uid, $course_id);
                } elseif ($blog_type == 'perso_blog') {
                    //in this case send user_id as third argument instead of course_id which is 0
                    //since we only need this info for identifying user's blog
                    $rating_content = $rating->put(NULL, $uid, $user_id);
                }
            }
            if ($comments_enabled && ($post->getCommenting() == 1)) {
                $comm = new Commenting('blogpost', $post->getId());
                //$comment_content = "<a class='btn submitAdminBtn float-end mt-3' href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."#comments_title'>$langComments (".$comm->getCommentsNum().")</a>";


                $comment_content = "<a class='commentPress float-end mt-3 pe-0' href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."#comments_title'>
                                            <i class='fa-regular fa-comment-dots'></i>
                                            &nbsp;|&nbsp;
                                            <span class='vsmall-text text-decoration-underline'>$langComments (".$comm->getCommentsNum().")</span>
                                    </a>";


            } else {
                $comment_content = "<div class=\"blog_post_empty_space\"></div>";
            }
            $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mb-3 mt-2' style='border-radius:3px !important;'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>



                                        <a class='TextBold fs-6' href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."'>".q($post->getTitle())."</a>


                                        <div>
                                            ". action_button(array(
                                                array(
                                                    'title' => $langEditChange,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=editPost&amp;pId=".$post->getId(),
                                                    'icon' => 'fa-edit',
                                                    'show' => $allow_to_edit
                                                ),
                                                array(
                                                    'title' => $langDelete,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=delPost&amp;pId=".$post->getId(),
                                                    'icon' => 'fa-xmark',
                                                    'class' => 'delete',
                                                    'confirm' => $langSureToDelBlogPost,
                                                    'show' => $allow_to_edit
                                                ),
                                                array(
                                                    'title' => $langAddResePortfolio,
                                                    'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=blog&amp;rid=".$post->getId(),
                                                    'icon' => 'fa-star',
                                                    'show' => (get_config('eportfolio_enable') && $post->getAuthor()==$uid)
                                                ),
                                            ))."
                                        </div>



                                </div>
                                <div class='card-body'>
                                    <p class='TextBold mb-2'>" . format_locale_date(strtotime($post->getTime())) . "</p><small>".$langBlogPostUser.display_user($post->getAuthor(), false, false)."</small><br><br>".ellipsize_html(standard_text_escape($post->getContent()), $num_chars_teaser_break, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."'> <span class='smaller'>[$langMore]</span></a></strong>")."
                                    $comment_content
                                </div>
                                <div class='card-footer d-flex justify-content-between align-items-center border-top-default pt-lg-3'>

                                        <div>$rating_content</div>
                                        <div>$sharing_content</div>

                                </div>
                             </div>";
        }


        //display navigation links
        $tool_content .= $blog->navLinksHTML($page, $posts_per_page);

        $tool_content .= "</div>";
        /***end of blog posts area***/


        /***sidebar area***/
        $tool_content .= "<div class='col-md-4'>";
        $tool_content .= $blog->popularBlogPostsHTML($num_popular);
        $tool_content .= $blog->chronologicalTreeHTML(date('n',strtotime($posts[0]->getTime())), date('Y',strtotime($posts[0]->getTime())));

        $tool_content .= "</div></div>";
        /***end of sidebar area***/
    }
}

if ($blog_type == 'course_blog') {
    draw($tool_content, 2, null, $head_content);
} elseif ($blog_type == 'perso_blog') {
    draw($tool_content, 1, null, $head_content);
}

function triggerGame($courseId, $uid, $eventName) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = BlogEvent::ACTIVITY;
    $eventData->module = MODULE_ID_BLOG;

    BlogEvent::trigger($eventName, $eventData);
}

function triggerAnalytics($courseId, $uid, $eventName) {
    $data = new stdClass();
    $data->uid = $uid;
    $data->course_id = $courseId;

    $data->element_type = 1;
    BlogAnalyticsEvent::trigger(BlogAnalyticsEvent::BLOGEVENT, $data, true);
}
