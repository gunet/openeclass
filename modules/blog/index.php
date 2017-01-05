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
$helpTopic = 'Blog';
require_once '../../include/baseTheme.php';
require_once 'modules/comments/class.commenting.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/blog/class.blog.php';
require_once 'modules/blog/class.blogpost.php';
require_once 'include/course_settings.php';
require_once 'modules/sharing/sharing.php';

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
        $tool_content = "<div class='alert alert-danger'>$langPersoBlogDisabled</div>";
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
        $tool_content = "<div class='alert alert-danger'>$langBlogUserNotExist</div>";
        draw($tool_content, 1);
        exit;
    }
    
    if ($user_id == $_SESSION['uid']) {
        $toolName = $langMyBlog;
    } else {
        $toolName = $langBlog." - ".$db_user->surname." ".$db_user->givenname;
    }    
    
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
    $tool_content .= action_bar(array(
                         array('title' => $langBack,
                               'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog",
                               'icon' => 'fa-reply',
                               'level' => 'primary-label',
                               'show' => isset($action) and $action != "showBlog" and $action != "showPost" and $action != "savePost" and $action != "delPost")
    ));
    if ($action == "settings") {
        
        if (isset($_POST['submitSettings'])) {
            setting_set(SETTING_BLOG_STUDENT_POST, $_POST['1_radio'], $course_id);
            setting_set(SETTING_BLOG_COMMENT_ENABLE, $_POST['2_radio'], $course_id);
            setting_set(SETTING_BLOG_RATING_ENABLE, $_POST['3_radio'], $course_id);
		    if (isset($_POST['4_radio'])) {
                setting_set(SETTING_BLOG_SHARING_ENABLE, $_POST['4_radio'], $course_id);
		    }
            Session::Messages($langRegDone, 'alert-success');
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
        
        
        $tool_content .= "
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='form-wrapper'>
                        <form class='form-horizontal' action='' role='form' method='post'>
                            <fieldset>                               
                                <div class='form-group'>
                                    <label class='col-sm-3'>$langBlogPerm</label>
                                    <div class='col-sm-9'> 
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='1_radio' $checkTeach>$langBlogPermTeacher
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='1_radio' $checkStud>$langBlogPermStudents
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class='form-group'>
                                    <label class='col-sm-3'>$langBlogCommenting</label>
                                    <div class='col-sm-9'>    
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='2_radio' $checkCommentEn>$langCommentsEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='2_radio' $checkCommentDis>$langCommentsDis
                                            </label>
                                        </div>
                                    </div>
                                </div>                            
                                <div class='form-group'>
                                    <label class='col-sm-3'>$langBlogRating:</label>
                                    <div class='col-sm-9'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='3_radio' $checkRatingEn>$langRatingEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='3_radio' $checkRatingDis>$langRatingDis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label class='col-sm-3'>$langBlogSharing:</label>
                                    <div class='col-sm-9'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='4_radio' $checkSharingEn $sharing_radio_dis>$langSharingEn
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='4_radio' $checkSharingDis $sharing_radio_dis>$langSharingDis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class='form-group'>
                                <div class='col-sm-9 col-sm-offset-3'>".
                                    form_buttons(array(
                                        array(
                                            'text'  =>  $langSave,
                                            'name'  =>  'submitSettings',
                                            'value' =>  $langSubmit
                                        ),
                                        array(
                                            'href'  =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog"
                                        )
                                    ))
                                    ."</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>";
        
        
        
    }
} elseif ($blog_type == 'perso_blog' && $is_blog_editor) {    
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?user=$user_id&amp;action=showBlog",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label',
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
                Session::Messages($langBlogPostDelSucc, 'alert-success');
            } else {
                Session::Messages($langBlogPostDelFail);
            }
        } else {
            Session::Messages($langBlogPostNotAllowedDel);
        }
    } else {
        Session::Messages($langBlogPostNotFound);      
    }
    redirect_to_home_page("modules/blog/index.php?$url_params");
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
            $commenting_setting = "<div class='form-group'>
                                       <label class='col-sm-2 control-label'>$langBlogPostCommenting:</label>
                                       <div class='col-sm-10'>
                                           <div>
                                                <input type='radio' value='1' name='commenting' checked>
                                                $langCommentsEn
                                           </div>
                                           <div>
                                                <input type='radio' value='0' name='commenting'>
                                                $langCommentsDis
                                           </div>
                                       </div>
                                   </div>";
        }
        $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?$url_params' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
            <fieldset>
                <div class='form-group'>
                    <label for='blogPostTitle' class='col-sm-2 control-label'>$langBlogPostTitle:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='blogPostTitle' id='blogPostTitle' placeholder='$langBlogPostTitle'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='newContent' class='col-sm-2 control-label'>$langBlogPostBody:</label>
                    <div class='col-sm-10'>
                        ".rich_text_editor('newContent', 4, 20, '')."
                    </div>
                </div>
                $commenting_setting
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>".
                        form_buttons(array(
                            array(
                                'text'  =>  $langSave,
                                'name'  =>  'submitBlogPost',
                                'value' =>  $langAdd
                            ),
                            array(
                                'href'  =>  "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog"
                            )
                        ))
                        ."</div>
                </div>          
                <input type='hidden' name='action' value='savePost' />
            </fieldset>
            </form>
        </div>";
    } else {
        Session::Messages($langBlogPostNotAllowedCreate);
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
                $commenting_setting = "<div class='form-group'>
                                           <label class='col-sm-2 control-label'>$langBlogPostCommenting:</label>
                                           <div class='col-sm-10'>
                                               <div>
                                                   <input type='radio' value='1' name='commenting' $checkCommentEn>
                                                   $langCommentsEn
                                               </div>
                                               <div>
                                                   <input type='radio' value='0' name='commenting' $checkCommentDis>
                                                   $langCommentsDis
                                               </div>
                                           </div>
                                       </div>";
            }
            $tool_content .= "
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?$url_params' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
                <fieldset>
                <div class='form-group'>
                    <label for='blogPostTitle' class='col-sm-2 control-label'>$langBlogPostTitle:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='blogPostTitle' id='blogPostTitle' value='".q($post->getTitle())."' placeholder='$langBlogPostTitle'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='newContent' class='col-sm-2 control-label'>$langBlogPostBody:</label>
                    <div class='col-sm-10'>
                        ".rich_text_editor('newContent', 4, 20, $post->getContent())."
                    </div>
                </div>
                $commenting_setting
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>".
                        form_buttons(array(
                            array(
                                'text'  =>  $langSave,
                                'name'  =>  'submitBlogPost',
                                'value' =>  $langModifBlogPost
                            ),
                            array(
                                'href'  =>  "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog"
                            )
                        ))
                        ."</div>
                </div>              
                <input type='hidden' name='action' value='savePost'>
                <input type='hidden' name='pId' value='".$post->getId()."'>
                </fieldset>
            </form>
        </div>";
        } else {
            Session::Messages($langBlogPostNotAllowedEdit);
            redirect_to_home_page("modules/blog/index.php?$url_params");            
        }
    } else {
        Session::Messages($langBlogPostNotFound);
        redirect_to_home_page("modules/blog/index.php?$url_params");        
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
                Session::Messages($langBlogPostSaveSucc, 'alert-success');
            } else {
                Session::Messages($langBlogPostSaveFail);
            }
        } else {
            Session::Messages($langBlogPostNotAllowedCreate);
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
                    Session::Messages($langBlogPostSaveSucc, 'alert-success');
                } else {
                    Session::Messages($langBlogPostSaveFail);
                }
            } else {
                Session::Messages($langBlogPostNotAllowedEdit);
            }
        } else {
            Session::Messages($langBlogPostNotFound);                      
        }
    } 
    redirect_to_home_page("modules/blog/index.php?$url_params");      
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
}

//show blog post
if ($action == "showPost") {
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                    'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showBlog",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
    ));
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
        $tool_content .= "<div class='panel panel-action-btn-default'>
                            <div class='panel-heading'>
                                <div class='pull-right'>
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
                                            'icon' => 'fa-times',
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
                                <h3 class='panel-title'>
                                    ".q($post->getTitle())."
                                </h3>
                            </div>
                            <div class='panel-body'><div class='label label-success'>" . nice_format($post->getTime(), true). "</div><small>".$langBlogPostUser.display_user($post->getAuthor(), false, false)."</small><br><br>".standard_text_escape($post->getContent())."</div>
                            <div class='panel-footer'>
                                <div class='row'>
                                    <div class='col-sm-6'>$rating_content</div>
                                    <div class='col-sm-6 text-right'>$sharing_content</div>
                                </div>
                            </div>
                        </div>";
        
        if ($comments_enabled) {
            if ($post->getCommenting() == 1) {
                commenting_add_js(); //add js files needed for comments
                $comm = new Commenting('blogpost', $post->getId());
            if ($blog_type == 'course_blog') {
                $tool_content .= $comm->put($course_code, $is_editor, $uid, true);
            } elseif ($blog_type == 'perso_blog') {
                $tool_content .= $comm->put(NULL, $is_blog_editor, $uid, true);
            }
            }
        }
        
    } else {
        Session::Messages($langBlogPostNotFound);
        redirect_to_home_page("modules/blog/index.php?$url_params");  
    }

}

//show all blog posts
if ($action == "showBlog") {
    if ($blog_type == 'course_blog') {
        $allow_to_create = $blog->permCreate($is_editor, $stud_allow_create, $uid);
    } elseif ($blog_type == 'perso_blog') {
        $allow_to_create = $is_blog_editor;
    }
    $tool_content .= action_bar(array(
                        array('title' => $langBlogAddPost,
                              'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=createPost",
                              'icon' => 'fa-plus-circle',
                              'level' => 'primary-label',
                              'button-class' => 'btn-success',
                              'show' => $allow_to_create),
                        array('title' => $langConfig,
                              'url' => "$_SERVER[SCRIPT_NAME]?$url_params&amp;action=settings",
                              'icon' => 'fa-gear',
                              'level' => 'primary',
                              'show' => ($blog_type == 'course_blog') && $is_editor && $blog->permCreate($is_editor, $stud_allow_create, $uid))
                     ));
    
    $num_posts = $blog->blogPostsNumber();
    if ($num_posts == 0) {//no blog posts
        $tool_content .= "<div class='alert alert-warning'>$langBlogEmpty</div>";
    } else {//show blog posts
        //if page num was changed at the url and exceeds pages number show the first page
        if ($page > ceil($num_posts/$posts_per_page)-1) {
            $page = 0;
        }
        
        //retrieve blog posts
        $posts = $blog->getBlogPostsDB($page, $posts_per_page);
                
        /***blog posts area***/
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-9'>";
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
                $comment_content = "<a class='btn btn-primary btn-xs pull-right' href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."#comments_title'>$langComments (".$comm->getCommentsNum().")</a>";
            } else {
                $comment_content = "<div class=\"blog_post_empty_space\"></div>";
            }            
            $tool_content .= "<div class='panel panel-action-btn-default'>
                                <div class='panel-heading'>
                                    <div class='pull-right'>
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
                                                'icon' => 'fa-times',
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
                                    <h3 class='panel-title'>
                                        <a href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."'>".q($post->getTitle())."</a>
                                    </h3>                                    
                                </div>
                                <div class='panel-body'>
                                    <div class='label label-success'>" . nice_format($post->getTime(), true). "</div><small>".$langBlogPostUser.display_user($post->getAuthor(), false, false)."</small><br><br>".ellipsize_html(standard_text_escape($post->getContent()), $num_chars_teaser_break, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?$url_params&amp;action=showPost&amp;pId=".$post->getId()."'> <span class='smaller'>[$langMore]</span></a></strong>")."
                                    $comment_content
                                </div>
                                <div class='panel-footer'>
                                    <div class='row'>
                                        <div class='col-sm-6'>$rating_content</div>
                                        <div class='col-sm-6 text-right'>$sharing_content</div>
                                    </div>                                    
                                </div>
                             </div>";            
        }
        
        
        //display navigation links
        $tool_content .= $blog->navLinksHTML($page, $posts_per_page);
        
        $tool_content .= "</div>";
        /***end of blog posts area***/
        
        
        /***sidebar area***/
        $tool_content .= "<div class='col-sm-3'>";
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
