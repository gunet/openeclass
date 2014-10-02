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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Blog';
require_once '../comments/class.comment.php';
require_once '../comments/class.commenting.php';
require_once '../rating/class.rating.php';
require_once '../../include/baseTheme.php';
require_once 'class.blog.php';
require_once 'class.blogpost.php';
require_once 'include/course_settings.php';
require_once 'modules/sharing/sharing.php';

define ('RSS', 'modules/blog/rss.php?course='.$course_code);
load_js('tools.js');

$nameTools = $langBlog;

$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
		$langEmptyBlogPostTitle . '";</script>';

//check if commenting is enabled for blogs
$comments_enabled = setting_get(SETTING_BLOG_COMMENT_ENABLE, $course_id);
//check if rating is enabled for blogs
$ratings_enabled = setting_get(SETTING_BLOG_RATING_ENABLE, $course_id);

$sharing_allowed = is_sharing_allowed($course_id); 
$sharing_enabled = setting_get(SETTING_BLOG_SHARING_ENABLE, $course_id);

if ($comments_enabled == 1) {
    commenting_add_js(); //add js files needed for comments
}

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

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langBlog);

if ($is_editor) {
    if (isset($action) and $action != "showBlog" and $action != "showPost" and $action != "savePost" and $action != "delPost") {
        $tool_content .= "
            <div id='operations_container'>
            <ul id='opslist'>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog'>" . $langBack . "</a></li>
            </ul>
            </div>";
    }    
    if ($action == "settings") {
        if (isset($_POST['submitSettings'])) {
            setting_set(SETTING_BLOG_STUDENT_POST, $_POST['1_radio'], $course_id);
            setting_set(SETTING_BLOG_COMMENT_ENABLE, $_POST['2_radio'], $course_id);
            setting_set(SETTING_BLOG_RATING_ENABLE, $_POST['3_radio'], $course_id);
		    if (isset($_POST['4_radio'])) {
                setting_set(SETTING_BLOG_SHARING_ENABLE, $_POST['4_radio'], $course_id);
		    }
            $message = "<p class='success'>$langRegDone</p>";
        }
        
        if (isset($message) && $message) {
        	$tool_content .= $message . "<br/>";
        	unset($message);
        }
        
        $tool_content .= "<form action=\"\" method=\"post\" >";
        
        if (setting_get(SETTING_BLOG_STUDENT_POST, $course_id) == 1) {
            $checkTeach = "";
            $checkStud = "checked ";
        } else {
            $checkTeach = "checked ";
            $checkStud = "";
        }
        
        $tool_content .= "<fieldset><legend>$langBlogPerm</legend>";
        $tool_content .= "<table class=\"tbl\" width=\"100%\">";
        $tool_content .= "<tbody>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"0\" name=\"1_radio\" $checkTeach/>$langBlogPermTeacher</td></tr>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"1\" name=\"1_radio\" $checkStud/>$langBlogPermStudents</td></tr>";
        $tool_content .= "</tbody>";
        $tool_content .= "</table>";
        $tool_content .= "</fieldset>";
        
        if (setting_get(SETTING_BLOG_COMMENT_ENABLE, $course_id) == 1) {
        	$checkDis = "";
        	$checkEn = "checked ";
        } else {
        	$checkDis = "checked ";
        	$checkEn = "";
        }
        
        $tool_content .= "<fieldset><legend>$langCommenting</legend>";
        $tool_content .= "<table class=\"tbl\" width=\"100%\">";
        $tool_content .= "<tbody>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"1\" name=\"2_radio\" $checkEn/>$langCommentsEn</td></tr>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"0\" name=\"2_radio\" $checkDis/>$langCommentsDis</td></tr>";
        $tool_content .= "</tbody>";
        $tool_content .= "</table>";
        $tool_content .= "</fieldset>";
        
        if (setting_get(SETTING_BLOG_RATING_ENABLE, $course_id) == 1) {
        	$checkDis = "";
        	$checkEn = "checked ";
        } else {
        	$checkDis = "checked ";
        	$checkEn = "";
        }
        
        $tool_content .= "<fieldset><legend>$langRating</legend>";
        $tool_content .= "<table class=\"tbl\" width=\"100%\">";
        $tool_content .= "<tbody>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"1\" name=\"3_radio\" $checkEn/>$langRatingEn</td></tr>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"0\" name=\"3_radio\" $checkDis/>$langRatingDis</td></tr>";
        $tool_content .= "</tbody>";
        $tool_content .= "</table>";
        $tool_content .= "</fieldset>";
		
        if (!$sharing_allowed) {
            $radio_dis = " disabled";
            $sharing_dis_label = "<tr><td><em>";
            if (!get_config('enable_social_sharing_links')) {
                $sharing_dis_label .= $langSharingDisAdmin;
            }
            if (course_status($course_id) != COURSE_OPEN) {
                $sharing_dis_label .= " ".$langSharingDisCourse;
            }
            $sharing_dis_label .= "</em></td></tr>";
        } else {
            $radio_dis = "";
            $sharing_dis_label = "";
        }
		
        if ($sharing_enabled == 1) {
            $checkDis = "";
            $checkEn = "checked";
        } else {
            $checkDis = "checked";
            $checkEn = "";
        }
        
        $tool_content .= "<fieldset><legend>$langSharing</legend>";
        $tool_content .= "<table class=\"tbl\" width=\"100%\">";
        $tool_content .= "<tbody>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"1\" name=\"4_radio\" $checkEn $radio_dis/>$langSharingEn</td></tr>";
        $tool_content .= "<tr><td><input type=\"radio\" value=\"0\" name=\"4_radio\" $checkDis $radio_dis/>$langSharingDis</td></tr>";
        $tool_content .= "<tr><td>$sharing_dis_label</tr></td>";
        $tool_content .= "</tbody>";
        $tool_content .= "</table>";
        $tool_content .= "</fieldset>";
        
        $tool_content .= "<p class=\"right\"><input type=\"submit\" name=\"submitSettings\" value=\"$langSubmit\" /></p>";
        
        $tool_content .= "</form>";
        
    }
}

//instantiate the object representing this blog
$blog = new Blog($course_id, 0);

//delete post
if ($action == "delPost") {
    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        if ($post->permEdit($is_editor, $stud_allow_create, $uid)) {
            if($post->delete()) {
                $message = "<p class='success'>$langBlogPostDelSucc</p>";
            } else {
                $message = "<p class='alert1'>$langBlogPostDelFail</p>";
            }
        } else {
            $message = "<p class='alert1'>$langBlogPostNotAllowedDel</p>";
        }
    } else {
        $message = "<p class='alert1'>$langBlogPostNotFound</p>";
    }
    $action = "showBlog";
}

//create blog post form
if ($action == "createPost") {
    if ($blog->permCreate($is_editor, $stud_allow_create, $uid)) {
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=".$course_code."' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
        <fieldset>
        <legend>$langBlogPost</legend>
        <table class='tbl' width='100%'>
        <tr>
        <th>$langBlogPostTitle:</th>
        </tr>
        <tr>
        <td><input type='text' name='blogPostTitle' size='50' /></td>
        </tr>
        <tr>
        <th>$langBlogPostBody:</th>
        </tr>
        <tr>
        <td>".rich_text_editor('newContent', 4, 20, '')."</td>
        </tr>
        <tr><td class='right'><input type='submit' name='submitBlogPost' value='$langAdd' /></td>
        </tr>
        </table>
        <input type='hidden' name='action' value='savePost' />
        </fieldset>
        </form>";
    } else {
        $message = "<p class='alert1'>$langBlogPostNotAllowedCreate</p>";
    }
    
}

//edit blog post form
if ($action == "editPost") {
    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        if ($post->permEdit($is_editor, $stud_allow_create, $uid)) {
            $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=".$course_code."' onsubmit=\"return checkrequired(this, 'blogPostTitle');\">
            <fieldset>
            <legend>$langBlogPost</legend>
            <table class='tbl' width='100%'>
            <tr>
            <th>$langBlogPostTitle:</th>
            </tr>
            <tr>
            <td><input type='text' name='blogPostTitle' value='".q($post->getTitle())."' size='50' /></td>
            </tr>
            <tr>
            <th>$langBlogPostBody:</th>
            </tr>
            <tr>
            <td>".rich_text_editor('newContent', 4, 20, $post->getContent())."</td>
    		</tr>
    		<tr><td class='right'><input type='submit' name='submitBlogPost' value='$langModifBlogPost' /></td>
    		</tr>
    		</table>
    		<input type='hidden' name='action' value='savePost' />
    		<input type='hidden' name='pId' value='".$post->getId()."' />
    		</fieldset>
    		</form>";
        } else {
            $message = "<p class='alert1'>$langBlogPostNotAllowedEdit</p>";
        }
    } else {
        $message = "<p class='alert1'>$langBlogPostNotFound</p>";
    }

}

//save blog post
if ($action == "savePost") {
    
    if (isset($_POST['submitBlogPost']) && $_POST['submitBlogPost'] == $langAdd) {
        if ($blog->permCreate($is_editor, $stud_allow_create, $uid)) {
            $post = new BlogPost();
            if ($post->create($_POST['blogPostTitle'], purify($_POST['newContent']), $uid, $course_id)) {
                $message = "<p class='success'>$langBlogPostSaveSucc</p>";
            } else {
                $message = "<p class='alert1'>$langBlogPostSaveFail</p>";
            }
        } else {
            $message = "<p class='alert1'>$langBlogPostNotAllowedCreate</p>";
        }
    } elseif (isset($_POST['submitBlogPost']) && $_POST['submitBlogPost'] == $langModifBlogPost) {
        $post = new BlogPost();
        if ($post->loadFromDB($_POST['pId'])) {
            if ($post->permEdit($is_editor, $stud_allow_create, $uid)) {
                if ($post->edit($_POST['blogPostTitle'], purify($_POST['newContent']))) {
                    $message = "<p class='success'>$langBlogPostSaveSucc</p>";
                } else {
                    $message = "<p class='alert1'>$langBlogPostSaveFail</p>";
                }
            } else {
                $message = "<p class='alert1'>$langBlogPostNotAllowedEdit</p>";
            }
        } else {
            $message = "<p class='alert1'>$langBlogPostNotFound</p>";
        }
    } 
    $action = "showBlog";
    
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
}

//show blog post
if ($action == "showPost") {
    if ($blog->permCreate($is_editor, $stud_allow_create, $uid)) {
        $tool_content .= "
            <div id='operations_container'>
            <ul id='opslist'>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showBlog'>" . $langBack . "</a></li>
            </ul>
            </div>";
    }
    
    $post = new BlogPost();
    if ($post->loadFromDB($pId)) {
        $post->incViews();
        
        $tool_content .= "<div class='blog_post'>";
        $tool_content .= "<div class='blog_post_title'><h2><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showPost&amp;pId=".$post->getId()."'>".q($post->getTitle())."</a>";
        
        if ($post->permEdit($is_editor, $stud_allow_create, $uid)) {
            $tool_content .= "
            <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=editPost&amp;pId=".$post->getId()."'>
            <img src='$themeimg/edit.png' alt='".$langModify."' title='".$langModify."'/></a>
            <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=delPost&amp;pId=".$post->getId()."' onClick=\"return confirmation('$langSureToDelBlogPost');\">
            <img src='$themeimg/delete.png' alt='".$langDelete."' title='".$langDelete."' /></a>";
        }
        
        $tool_content .= "</h2></div>";
        
        $tool_content .= "<div class='blog_post_content'>".standard_text_escape($post->getContent())."</div>";
        $tool_content .= "<div class='smaller'>" . nice_format($post->getTime(), true).$langBlogPostUser.q(uid_to_name($post->getAuthor()))."</div>";
        $tool_content .= "</div>";
        
        if ($ratings_enabled == 1) {
        	$rating = new Rating('up_down', 'blogpost', $post->getId());
        	$tool_content .= $rating->put($is_editor, $uid, $course_id);
        }
        
        if ($sharing_allowed) {
            if ($sharing_enabled == 1) {
                $tool_content .= print_sharing_links($urlServer."modules/blog/index.php?course=$course_code&amp;action=showPost&amp;pId=".$post->getId(), $post->getTitle());
            }
        }
        
        if ($comments_enabled == 1) {
            $comm = new Commenting('blogpost', $post->getId());
            $tool_content .= $comm->put($course_code, $is_editor, $uid);
        }
        
    } else {
        $tool_content .= "<p class='alert1'>$langBlogPostNotFound</p>";
    }

}

//show all blog posts
if ($action == "showBlog") {
    if ($blog->permCreate($is_editor, $stud_allow_create, $uid)) {
        $tool_content .= "
        <div id='operations_container'>
            <ul id='opslist'>
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=createPost'>" . $langBlogAddPost . "</a></li>";
        if ($is_editor) {
        	$tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=settings'>" . $langConfig . "</a></li>";
        }
        $tool_content .= "</ul>
        </div>";
    }
    
    $num_posts = $blog->blogPostsNumber();
    if ($num_posts == 0) {//no blog posts
        $tool_content .= "<p class='alert1'>$langBlogEmpty</p>";
    } else {//show blog posts
        //if page num was changed at the url and exceeds pages number show the first page
        if ($page > ceil($num_posts/$posts_per_page)-1) {
            $page = 0;
        }
        
        //retrieve blog posts
        $posts = $blog->getBlogPostsDB($page, $posts_per_page);
        
        /***blog posts area***/
        $tool_content .= "<div class='blog_posts'>";
        foreach ($posts as $post) {
            $tool_content .= "<div class='blog_post'>";
            $tool_content .= "<div class='blog_post_title'><h2><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showPost&amp;pId=".$post->getId()."'>".q($post->getTitle())."</a>";
            
            if ($post->permEdit($is_editor, $stud_allow_create, $uid)) {
                $tool_content .= "
                <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=editPost&amp;pId=".$post->getId()."'>
                <img src='$themeimg/edit.png' alt='".$langModify."' title='".$langModify."'/></a>
                <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=delPost&amp;pId=".$post->getId()."' onClick=\"return confirmation('$langSureToDelBlogPost');\">
                <img src='$themeimg/delete.png' alt='".$langDelete."' title='".$langDelete."' /></a>";
            }
            
            $tool_content .= "</h2></div>";
            
            $tool_content .= "<div class='blog_post_content'>".standard_text_escape(ellipsize_html($post->getContent(), $num_chars_teaser_break, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showPost&amp;pId=".$post->getId()."'> <span class='smaller'>[$langMore]</span></a></strong>"))."</div>";
            $tool_content .= "<div class='smaller'>" . nice_format($post->getTime(), true).$langBlogPostUser.q(uid_to_name($post->getAuthor()))."</div>";
            $tool_content .= "</div>";
            
            if ($ratings_enabled == 1) {
            	$rating = new Rating('up_down', 'blogpost', $post->getId());
            	$tool_content .= $rating->put($is_editor, $uid, $course_id);
            }
            
            if ($sharing_allowed) {
                if ($sharing_enabled == 1) {
                    $tool_content .= print_sharing_links($urlServer."modules/blog/index.php?course=$course_code&amp;action=showPost&amp;pId=".$post->getId(), $post->getTitle());
                }
            }
            
            if ($comments_enabled == 1) {
                $comm = new Commenting('blogpost', $post->getId());
                $tool_content .= $comm->put($course_code, $is_editor, $uid);
            } else {
                $tool_content .= "<div class=\"blog_post_empty_space\"></div>";
            }
            
        }
        
        
        //display navigation links
        $tool_content .= $blog->navLinksHTML($page, $posts_per_page);
        
        $tool_content .= "</div>";
        /***end of blog posts area***/
        
        
        /***sidebar area***/
        $tool_content .= "<div style=\"float: right; width: 25%;\">";
        $tool_content .= $blog->popularBlogPostsHTML($num_popular);
        $tool_content .= $blog->chronologicalTreeHTML(date('n',strtotime($posts[0]->getTime())), date('Y',strtotime($posts[0]->getTime())));
        
        $tool_content .= "</div>";
        /***end of sidebar area***/
    }
}

draw($tool_content, 2, null, $head_content);