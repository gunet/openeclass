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
require_once '../../include/baseTheme.php';
require_once 'class.blog.php';
require_once 'class.blogpost.php';

define ('RSS', 'modules/blog/rss.php?course='.$course_code);
load_js('tools.js');

$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
		$langEmptyBlogPostTitle . '";</script>';

//define allowed actions
$allowed_actions = array("showBlog", "showPost", "createPost", "editPost", "delPost", "savePost");

//initialize $_REQUEST vars
$action = (isset($_REQUEST['action']) && in_array($_REQUEST['action'], $allowed_actions))? $_REQUEST['action'] : "showBlog";
$pId = isset($_REQUEST['pId']) ? intval($_REQUEST['pId']) : 0;
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;

//config setting allowing students to create posts and edit/delete own posts
//leaving it static for now
$stud_allow_create = false;

$posts_per_page = 10;
$num_popular = 5;//number of popular blog posts to show in sidebar
$num_chars_teaser_break = 500;//chars before teaser break

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langBlog);

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
            <td><input type='text' name='blogPostTitle' value='".$post->getTitle()."' size='50' /></td>
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
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=createPost'>" . $langBlogAddPost . "</a></li>
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
            <img src='$themeimg/edit.png' title='".$langModify."'/></a>
            <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=delPost&amp;pId=".$post->getId()."' onClick=\"return confirmation('$langSureToDelBlogPost');\">
            <img src='$themeimg/delete.png' title='".$langDelete."' /></a>";
        }
        
        $tool_content .= "</h2></div>";
        
        $tool_content .= "<div class='blog_post_content'><p>".standard_text_escape($post->getContent())."</p></div>";
        $tool_content .= "<div class='smaller'>" . nice_format($post->getTime(), true).$langBlogPostUser.uid_to_name($post->getAuthor())."</div>";
        $tool_content .= "</div>";
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
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=createPost'>" . $langBlogAddPost . "</a></li>
            </ul>
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
                <img src='$themeimg/edit.png' title='".$langModify."'/></a>
                <a href='$_SERVER[SCRIPT_NAME]?course=".$course_code."&amp;action=delPost&amp;pId=".$post->getId()."' onClick=\"return confirmation('$langSureToDelBlogPost');\">
                <img src='$themeimg/delete.png' title='".$langDelete."' /></a>";
            }
            
            $tool_content .= "</h2></div>";
            
            $tool_content .= "<div class='blog_post_content'><p>".standard_text_escape(ellipsize_html($post->getContent(), $num_chars_teaser_break, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showPost&amp;pId=".$post->getId()."'> <span class='smaller'>[$langMore]</span></a></strong>"))."</p></div>";
            $tool_content .= "<div class='smaller'>" . nice_format($post->getTime(), true).$langBlogPostUser.uid_to_name($post->getAuthor())."</div>";
            $tool_content .= "</div>";
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