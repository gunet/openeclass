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


$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'For';
require_once '../../include/baseTheme.php';
require_once 'modules/forum/config.php';
require_once 'modules/forum/functions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/course_settings.php';
require_once 'include/log.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/abuse_report/abuse_report.php';

ModalBoxHelper::loadModalBox();

$toolName = $langForums;
if ($is_editor) {
    load_js('tools.js');
}

if (isset($_GET['all'])) {
    $paging = false;
} else {
    $paging = true;
}

if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
}
if (isset($_GET['post_id'])) {//needed to find post page for anchors
    $post_id = intval($_GET['post_id']);
    $myrow = Database::get()->querySingle("SELECT f.id, f.name, p.post_time, p.poster_id, p.post_text, t.locked FROM forum f, forum_topic t, forum_post p
            WHERE f.id = ?d
            AND t.id = ?d
            AND p.id = ?d
            AND t.forum_id = f.id
            AND p.topic_id = t.id
            AND f.course_id = ?d", $forum, $topic, $post_id, $course_id);
} else {
    $myrow = Database::get()->querySingle("SELECT f.id, f.name, t.locked FROM forum f, forum_topic t
            WHERE f.id = ?d
            AND t.id = ?d
            AND t.forum_id = f.id
            AND f.course_id = ?d", $forum, $topic, $course_id);
}


if (!$myrow) {
    $tool_content .= "<div class='alert alert-warning'>$langErrorTopicSelect</div>";
    draw($tool_content, 2);
    exit();
}
$forum_name = $myrow->name;
$forum = $myrow->id;
$topic_locked = $myrow->locked;
$total = get_total_posts($topic);

if (isset($_GET['delete']) && isset($post_id) && $is_editor) {
    $last_post_in_thread = get_last_post($topic);

    $this_post_time = $myrow->post_time;
    $this_post_author = $myrow->poster_id;

    //delete forum posts rating first
    Database::get()->query("DELETE FROM rating WHERE rtype = ?s AND rid = ?d", 'forum_post', $post_id);
    Database::get()->query("DELETE FROM rating_cache WHERE rtype = ?s AND rid = ?d", 'forum_post', $post_id);
    //delete abuse reports for this post and log actions
    $res = Database::get()->queryArray("SELECT * FROM abuse_report WHERE `rid` = ?d AND `rtype` = ?s", $post_id, 'forum_post');
    foreach ($res as $r) {
        Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
            array('id' => $r->id,
                  'user_id' => $r->user_id,
                  'reason' => $r->reason,
                  'message' => $r->message,
                  'rtype' => 'forum_post',
                  'rid' => $post_id,
                  'rcontent' => $myrow->post_text,
                  'status' => $r->status
        ));
    }
    Database::get()->query("DELETE FROM abuse_report WHERE rid = ?d AND rtype = ?s", $post_id, 'forum_post');   
    Database::get()->query("DELETE FROM forum_post WHERE id = ?d", $post_id);
    Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUMPOST, $post_id);

    //orphan replies get -1 to parent_post_id
    Database::get()->query("UPDATE forum_post SET parent_post_id = -1 WHERE parent_post_id = ?d", $post_id);

    $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $this_post_author, $course_id);
    Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $this_post_author, $course_id);
    if ($forum_user_stats->c != 0) {
        Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $this_post_author, $forum_user_stats->c, $course_id);
    }

    if ($total == 1) { // if exists one post in topic
        Database::get()->query("DELETE FROM forum_topic WHERE id = ?d AND forum_id = ?d", $topic, $forum);
        Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUMTOPIC, $topic);
        Database::get()->query("UPDATE forum SET num_topics = 0,
                            num_posts = 0
                            WHERE id = ?d
                            AND course_id = ?d", $forum, $course_id);
        header("Location: viewforum.php?course=$course_code&forum=$forum");
    } else {
        $last_post = Database::get()->querySingle("SELECT MAX(id) AS last_post FROM forum_post WHERE topic_id = ?d", $topic)->last_post;

        Database::get()->query("UPDATE forum SET
                        `num_posts` = `num_posts`-1,
                        last_post_id = ?d
                        WHERE id = ?d
                        AND course_id = ?d", $last_post, $forum, $course_id);

        Database::get()->query("UPDATE forum_topic SET
                                `num_replies` = `num_replies`-1,
                                last_post_id = ?d
                        WHERE id = ?d", $last_post, $topic);
    }
    if ($last_post_in_thread == $this_post_time) {
        $topic_time_fixed = $last_post_in_thread;
        $sql = "UPDATE forum_topic
			SET topic_time = '$topic_time_fixed'
			WHERE id = $topic";
    }
    $tool_content .= "<div class='alert alert-success'>$langDeletedMessage</div>";
}



if ($paging and $total > $posts_per_page) {
    $times = 0;
    for ($x = 0; $x < $total; $x += $posts_per_page) {
        $times++;
    }
    $pages = $times;
}

$topic_subject = Database::get()->querySingle("SELECT title FROM forum_topic WHERE id = ?d", $topic)->title;

if (!add_units_navigation(TRUE)) {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
    $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum", 'name' => q($forum_name));
}
$pageName = q($topic_subject);

if (isset($_SESSION['message'])) {
    $tool_content .= $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($topic_locked == 1) {
    $tool_content .= "<div class='alert alert-warning'>$langErrorTopicLocked</div>";
} else {
    $tool_content .= 
            action_bar(array(
                array('title' => $langReply,
                    'url' => "reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langBack,
                    'url' => "viewforum.php?course=$course_code&forum=$forum",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')                
                ),false);
}

if ($paging and $total > $posts_per_page) {
    $times = 1;
    if (isset($post_id)) {
        $result = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post WHERE topic_id = ?d AND post_time <= ?t", $topic, $myrow->post_time);
        $num = $result->c;
        $_GET['start'] = (ceil($num / $posts_per_page) - 1) * $posts_per_page;
    }

    if (isset($_GET['start'])) {
        $start = intval($_GET['start']);
    } else {
        $start = 0;
    }

    $last_page = $start - $posts_per_page;
    if (isset($start) && $start > 0) {
        $pagination_btns = "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page'><span aria-hidden='true'>&laquo;</span></a></li>";
    } else {
        $pagination_btns = "<li class='disabled'><a href='#'><span aria-hidden='true'>&laquo;</span></a></li>";
    }    
    for ($x = 0; $x < $total; $x += $posts_per_page) {
        if ($start && ($start == $x)) {
            $pagination_btns .= "<li class='active'><a href='#'>$times</a></li>";
        } else if ($start == 0 && $x == 0) {
            $pagination_btns .= "<li class='active'><a href='#'>1</a></li>";
        } else {
            $pagination_btns .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$x'>$times</a></li>";
        }
        $times++;
    }
    if (($start + $posts_per_page) < $total) {
        $next_page = $start + $posts_per_page;
        $pagination_btns .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page'><span aria-hidden='true'>&raquo;</span></a></li>";
    } else {
        $pagination_btns .= "<li class='disabled'><a href='#'><span aria-hidden='true'>&raquo;</span></a></li>";
    }      
    $tool_content .= "
        <nav>
            <ul class='pagination'>
            $pagination_btns
            </ul>
            <div class='pull-right'>
                <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>
            </div>
          </nav>
        ";
} else {
    if ($total > $posts_per_page) {
        $tool_content .= "
        <div class='clearfix margin-bottom-fat'>
          <nav>  
            <div class='pull-right'>
                <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>
            </div>
          </nav>
        </div>";
    }
}

$tool_content .= "<div class='table-responsive'><table class='table-default'>
    <tr class='list-header'>
      <th style='width:20%'>$langUserForum</th>
      <th>$langMessage</th>";
if ($is_editor ) {
    $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
}
$tool_content .= "</tr>";

if (isset($_GET['all'])) {
    $result = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id", $topic);
} elseif (isset($_GET['start'])) {
    $start = intval($_GET['start']);
    $result = Database::get()->queryArray("SELECT * FROM forum_post
		WHERE topic_id = ?d
		ORDER BY id
                LIMIT $start, $posts_per_page", $topic);
} else {
    $result = Database::get()->queryArray("SELECT * FROM forum_post
		WHERE topic_id = ?d
		ORDER BY id
                LIMIT $posts_per_page", $topic);
}

$count = 0;
$user_stats = array();
foreach ($result as $myrow) {
    if (!isset($user_stats[$myrow->poster_id])) {
        $user_num_posts = Database::get()->querySingle("SELECT num_posts FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $myrow->poster_id, $course_id);
        if ($user_num_posts) {
            if ($user_num_posts->num_posts == 1) {
                $user_stats[$myrow->poster_id] = "<span class='text-muted'>$langMessage: " . $user_num_posts->num_posts."</span>";
            } else {
                $user_stats[$myrow->poster_id] = "<span class='text-muted'>$langMessages: " . $user_num_posts->num_posts."</span>";
            }
        } else {
            $user_stats[$myrow->poster_id] = '';
        }
    }
    
    $tool_content .= "<td>
                        <div>".profile_image($myrow->poster_id, '100px', 'img-responsive img-circle margin-bottom-thin'). "</div>
                        <div>" .display_user($myrow->poster_id, false, false)."</div>
                        <div>".$user_stats[$myrow->poster_id]."</div>
                      </td>";
    $message = $myrow->post_text;
    // support for math symbols
    $message = mathfilter($message, 12, "../../courses/mathimg/");
    if ($count == 0) {
        $postTitle = "<h4 class='h4'>".q($topic_subject)."</h4>";
    } else {
        $postTitle = "";
    }

    $rate_str = "";
    if (setting_get(SETTING_FORUM_RATING_ENABLE, $course_id)) {
        $rating = new Rating('thumbs_up', 'forum_post', $myrow->id);
        $rate_str = $rating->put($is_editor, $uid, $course_id);
    }

    $anchor_link = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;post_id=$myrow->id#$myrow->id'>#$myrow->id</a><br/>";
    if ($myrow->parent_post_id == -1) {
        $parent_post_link = "<br/>$langForumPostParentDel";
    } elseif ($myrow->parent_post_id != 0) {
        $parent_post_link = "$langForumPostParent<a href='viewtopic.php?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;post_id=$myrow->parent_post_id#$myrow->parent_post_id'>#$myrow->parent_post_id</a>";
    } else {
        $parent_post_link = "";
    }

    $tool_content .= "<td class='forum-response-column'>
	  <div class='forum-post-header'>
	    <span class='pull-right forum-anchor-link'>";
    if ($topic_locked != 1) {
        $tool_content .= "<a class='btn btn-default btn-xs reply-post-btn' href='reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id'>$langReply</a>";
    }
        $tool_content .= "<a name='".$myrow->id."'></a>".$anchor_link."</span>";
	$tool_content .= "$postTitle<small class='text-muted'>$langSent: " . $myrow->post_time . "</small>
	  </div>
	  <div class='forum-post-message'>$message</div><div class='forum-post-footer clearfix'><div class='pull-left'>$rate_str</div><div class='pull-right text-muted'><small>$parent_post_link</small></div>
	</div></td>";

    $dyntools = (!$is_editor) ? array() : array(
        array('title' => $langModify,
            'url' => "editpost.php?course=$course_code&amp;post_id=" . $myrow->id . "&amp;topic=$topic&amp;forum=$forum",
            'icon' => 'fa-edit'
        ),
        array('title' => $langDelete,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;post_id=$myrow->id&amp;topic=$topic&amp;forum=$forum&amp;delete=on",
            'icon' => 'fa-times',
            'class' => 'delete',
            'confirm' => $langConfirmDelete)
    );

    if (abuse_report_show_flag('forum_post', $myrow->id, $course_id, $is_editor)) {
        $head_content .= abuse_report_add_js();
        $flag_arr = abuse_report_action_button_flag('forum_post', $myrow->id, $course_id);
        
        $dyntools[] = $flag_arr[0]; //action button option
        $report_modal = $flag_arr[1]; //modal html code
    }
    if (!empty($dyntools)) {
        if (isset($report_modal)) {
            $tool_content .= "<td class='option-btn-cell'>" . action_button($dyntools) . $report_modal . "</td>";
            unset($report_modal);
        } else {
            $tool_content .= "<td class='option-btn-cell'>" . action_button($dyntools) . "</td>";
        }
    }
    $tool_content .= "</tr>";
    $count++;
}

Database::get()->query("UPDATE forum_topic SET num_views = num_views + 1
            WHERE id = ?d AND forum_id = ?d", $topic, $forum);

$tool_content .= "</table></div>";

if ($paging and $total > $posts_per_page) {
    $tool_content .= "
        <nav>
            <ul class='pagination'>
            $pagination_btns
            </ul>
            <div class='pull-right'>
                <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>
            </div>
          </nav>
        ";    
} else {
    if ($total > $posts_per_page) {
        $tool_content .= "
        <div class='clearfix margin-bottom-fat'>
          <nav>  
            <div class='pull-right'>
                <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>
            </div>
          </nav>
        </div>";
    }
}
draw($tool_content, 2, null, $head_content);
