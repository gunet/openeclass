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
$helpTopic = 'forum';
require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/forum/functions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/course_settings.php';
require_once 'include/user_settings.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/abuse_report/abuse_report.php';

ModalBoxHelper::loadModalBox();

$toolName = $langForums;

// need improvement


/**if ($is_editor) {
    load_js('tools.js');
    $head_content .= "<script type='text/javascript'>
            $(document).ready(function() {
                $('#delete-btn_1').click(function(e) {
                    e.preventDefault();
                    bootbox.confirm('" . js_escape($langConfirmDelete) . "', function(result) {
                        if (result) {
                            $(location).attr('href');                            
                        }
                    });
                });
            });
        </script>";
}**/

// get forums post view user settings
$user_settings = new UserSettings($uid);
if (isset($_GET['view'])) {
    $user_settings->set(SETTING_FORUM_POST_VIEW, $_GET['view']);
}
$view = $user_settings->get(SETTING_FORUM_POST_VIEW);

if (isset($_GET['all'])) {
    $paging = false;
} else {
    $paging = true;
}

if (isset($_GET['unit'])) {
    $unit = intval($_GET['unit']);
}

if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}

$is_member = false;
$group_id = init_forum_group_info($forum);

// security check
if (($group_id) and !$is_editor) {
    if (!$is_member or !$has_forum) {
        header("Location: index.php?course=$course_code");
        exit();
    }
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
}
if (isset($_GET['post_id'])) { //needed to find post page for anchors
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
        Database::get()->query("UPDATE forum SET 
                                    num_topics = num_topics-1,
                                    num_posts = num_posts-1
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

if ($paging and $total > POSTS_PER_PAGE) {
    $times = 0;
    for ($x = 0; $x < $total; $x += POSTS_PER_PAGE) {
        $times++;
    }
    $pages = $times;
}

$topic_subject = Database::get()->querySingle("SELECT title FROM forum_topic WHERE id = ?d", $topic)->title;

if (!add_units_navigation(TRUE)) {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
    $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum", 'name' => q($forum_name));
}
$pageName = $langTopic;

if (isset($_SESSION['message'])) {
    $tool_content .= $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($topic_locked == 1) {
    $tool_content .= "<div class='alert alert-warning'>$langErrorTopicLocked</div>";
} else {
    if (isset($unit)) {
        $back_url = "../units/index.php?course=$course_code&id=$unit";
        $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum&amp;unit=$unit";
    } else {
        $back_url = "viewforum.php?course=$course_code&forum=$forum";
        $reply_url = "reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum";
    }
    $tool_content .=
            action_bar(array(
                array('title' => $langReply,
                    'url' => "$reply_url",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langBack,
                    'url' => $back_url,
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ));
    // forum posts view selection
    $selected_view_0 = $selected_view_1 = $selected_view_2 = 0;
    if ($view == POSTS_PAGINATION_VIEW_ASC) {
        $selected_view_0 = 'selected';
    } else if ($view == POSTS_PAGINATION_VIEW_DESC) {
        $selected_view_1 = 'selected';
    } else if ($view == POSTS_THREADED_VIEW) {
        $selected_view_2 = 'selected';
    }
    if (isset($unit)) {
        $selection_url = "../units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum&unit=$unit";
        $hidden_inputs = "<input type='hidden' name='res_type' value='forum_topic'>
                          <input type='hidden' name='unit' value='$unit'>";
    } else {
        $selection_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&topic=$topic&forum=$forum";
        $hidden_inputs = '';
    }

    $tool_content .= "
    <div class='row'>
        <div class='col-md-12'>            
            <form class='form-horizontal' name='viewselect' action='$selection_url' method='get'>
                <div class='form-group'>
                    <label class='col-sm-8 control-label'>$langQuestionView</label>
                    <div class='col-sm-4'>
                        $hidden_inputs
                        <input type='hidden' name='course' value='$course_code'>
                        <input type='hidden' name='forum' value='$forum'>
                        <input type='hidden' name='topic' value='$topic'>
                        <select name='view' id='view' class='form-control' onChange='document.viewselect.submit();'>
                            <option value='0' $selected_view_0>$langForumPostFlatViewAsc</option>
                            <option value='1' $selected_view_1>$langForumPostFlatViewDesc</option>
                            <option value='2' $selected_view_2>$langForumPostThreadedView</option>
                        </select>
                    </div>
                </div>
            </form>        
        </div>
    </div>";


}

if ($view != POSTS_THREADED_VIEW) {
    // pagination
    if ($paging and $total > POSTS_PER_PAGE) {
        $times = 1;
        if (isset($post_id)) {
            $result = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post WHERE topic_id = ?d AND post_time <= ?t", $topic, $myrow->post_time);
            $num = $result->c;
            $_GET['start'] = (ceil($num / POSTS_PER_PAGE) - 1) * POSTS_PER_PAGE;
        }

        if (isset($_GET['start'])) {
            $start = intval($_GET['start']);
        } else {
            $start = 0;
        }

        $last_page = $start - POSTS_PER_PAGE;
        if (isset($start) && $start > 0) {
            if (isset($unit)) {
                $pagination_btns = "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page&amp;unit=$unit'><span aria-hidden='true'>&laquo;</span></a></li>";
            } else {
                $pagination_btns = "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page'><span aria-hidden='true'>&laquo;</span></a></li>";
            }
        } else {
            $pagination_btns = "<li class='disabled'><a href='#'><span aria-hidden='true'>&laquo;</span></a></li>";
        }
        for ($x = 0; $x < $total; $x += POSTS_PER_PAGE) {
            if ($start && ($start == $x)) {
                $pagination_btns .= "<li class='active'><a href='#'>$times</a></li>";
            } else if ($start == 0 && $x == 0) {
                $pagination_btns .= "<li class='active'><a href='#'>1</a></li>";
            } else {
                if (isset($unit)) {
                    $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$x&amp;unit=$unit'>$times</a></li>";
                } else {
                    $pagination_btns .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$x'>$times</a></li>";
                }

            }
            $times++;
        }
        if (($start + POSTS_PER_PAGE) < $total) {
            $next_page = $start + POSTS_PER_PAGE;
            if (isset($unit)) {
                $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page&amp;unit=$unit'><span aria-hidden='true'>&raquo;</span></a></li>";
            } else {
                $pagination_btns .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page'><span aria-hidden='true'>&raquo;</span></a></li>";
            }

        } else {
            $pagination_btns .= "<li class='disabled'><a href='#'><span aria-hidden='true'>&raquo;</span></a></li>";
        }
        $tool_content .= "
            <nav>
                <ul class='pagination'>
                $pagination_btns
                </ul>
                <div class='pull-right'>";
                if (isset($unit)) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true&amp;unit=$unit'>$langAllOfThem</a>";
                } else {
                    $tool_content .= "<a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>";
                }
                $tool_content .= "</div>
              </nav>
            ";
    } else {
        if ($total > POSTS_PER_PAGE) {
            $tool_content .= "
            <div class='clearfix margin-bottom-fat'>
              <nav>
                <div class='pull-right'>";
                if (isset($unit)) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0&amp;unit=$unit'>$langPages</a>";
                } else {
                    $tool_content .= "<a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>";
                }
                $tool_content .= "</div>
              </nav>
            </div>";
        }
    }
    // end of pagination

    if (isset($_GET['all'])) {
        if ($view == POSTS_PAGINATION_VIEW_DESC) {
            // initial post
            $res1 = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id LIMIT 1", $topic);
            // all the rest with descending order
            $res2 = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id DESC", $topic);
            $result = array_merge($res1, $res2);
        } else {
            $result = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id", $topic);
        }
    } elseif (isset($_GET['start'])) {
        if ($view == POSTS_PAGINATION_VIEW_DESC) {
            $res1 = array();
            if ($_GET['start'] == 0) {
                // display the initial post in first page
                $res1 = Database::get()->queryArray("SELECT * FROM forum_post
                        WHERE topic_id = ?d ORDER BY id LIMIT 1", $topic);
                // all the rest with descending order
            }
            $res2 = Database::get()->queryArray("SELECT * FROM forum_post
                    WHERE topic_id = ?d ORDER BY id DESC 
                            LIMIT ?d, " . POSTS_PER_PAGE . "", $topic, $_GET['start']);
            $result = array_merge($res1, $res2);
        } else {
            $result = Database::get()->queryArray("SELECT * FROM forum_post
                        WHERE topic_id = ?d ORDER BY id
                                LIMIT ?d, " . POSTS_PER_PAGE . "", $topic, $_GET['start']);
        }
    } else {
        if ($view == POSTS_PAGINATION_VIEW_DESC) {
            // initial post
            $res1 = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id LIMIT 1", $topic);
            // all the rest with descending order
            $res2 = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id DESC 
                            LIMIT " . POSTS_PER_PAGE . "", $topic);
            $res3 = array_pop($res2);
            $result = array_merge($res1, $res2); // remove last entry (we displayed it in $res1 already)
        } else {
            $result = Database::get()->queryArray("SELECT * FROM forum_post 
                WHERE topic_id = ?d ORDER BY id 
                  LIMIT " . POSTS_PER_PAGE . "", $topic);
        }
    }
}

$tool_content .= "<div class='row'><div class='col-xs-12'>";

if ($view != POSTS_THREADED_VIEW) { // pagination view
    $count = 1; // highlight the initial post
    if (isset($_GET['start']) and $_GET['start'] > 0) {
        $count = 2; // we don't want to highlight each top post in each page
    }
    $user_stats = array();
    foreach ($result as $myrow) {
        $forum_data = Database::get()->querySingle("SELECT * FROM forum_post WHERE id = ?d", $myrow->id);
        $tool_content .= post_content($forum_data, $user_stats, $topic_subject, $topic_locked,0, $count);
        $count++;
    }
} else { // threaded view
     $result = Database::get()->queryArray("SELECT * FROM forum_post WHERE topic_id = ?d ORDER BY id", $topic);
     $count = 1; // num of posts
     $user_stats = array();
     foreach ($result as $myrow) {
         if ($myrow->parent_post_id == 0) { // if it is parent post
             $forum_data = Database::get()->querySingle("SELECT * FROM forum_post WHERE id = ?d", $myrow->id);
             $tool_content .= post_content($forum_data, $user_stats, $topic_subject, $topic_locked,0, $count);
             $count++;
             find_child_posts($result, $myrow, 1); // check if there are child posts under it
         } else {
             continue;
         }
     }
 }

$tool_content .= "</div></div>";

Database::get()->query("UPDATE forum_topic SET num_views = num_views + 1
            WHERE id = ?d AND forum_id = ?d", $topic, $forum);

if ($view == POSTS_PAGINATION_VIEW_ASC) {
    if ($paging and $total > POSTS_PER_PAGE) {
        $tool_content .= "
        <nav>
            <ul class='pagination'>
            $pagination_btns
            </ul>
            <div class='pull-right'>";
            if (isset($unit)) {
                $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true&amp;unit=$unit'>$langAllOfThem</a>";
            } else {
                $tool_content .= "<a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>";
            }

            $tool_content .= "</div>
          </nav>
        ";
    } else {
        if ($total > POSTS_PER_PAGE) {
            $tool_content .= "
            <div class='clearfix margin-bottom-fat'>
              <nav>
                <div class='pull-right'>";
                if (isset($unit)) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0&amp;unit=$unit'>$langPages</a>";
                } else {
                    $tool_content .= "<a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>";
                }
                $tool_content .= "</div>
              </nav>
            </div>";
        }
    }
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief display post
 * @param $myrow
 * @param $user_stats
 * @param $topic_subject
 * @param $topic_locked
 * @param $offset
 * @param $count
 * @return string
 */
function post_content($myrow, $user_stats, $topic_subject, $topic_locked, $offset, $count) {

    global $langForumPostParentDel, $langMsgRe, $course_id, $langReply, $unit, $langFrom2,
           $langMessages, $course_code, $is_editor, $topic, $forum, $uid, $langMessage, $head_content,
           $langModify, $langDelete, $langConfirmDelete, $langSent, $dateTimeFormatShort;

    $content = $reply_button_link = '';
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
    $parent_post_link = "";
    if ($myrow->parent_post_id == -1) {
        $parent_post_link = "<span class='help-block'>$langForumPostParentDel</span>";
    }

    if ($count > 1) { // for all posts except first
        $content .= "<div class='panel panel-default col-sm-offset-$offset'>";
        $content .= "<div class='panel-heading'><h5 class='panel-title'>$langMsgRe " . q($topic_subject);
    } else {
        $content .= "<div class='panel panel-primary'>";
        $content .= "<div class='panel-heading'><h5 class='panel-title'>". q($topic_subject);
    }

    if ($is_editor) {
        $content .= "<a href='../forum/editpost.php?course=$course_code&amp;post_id=" . $myrow->id . "&amp;topic=$topic&amp;forum=$forum'>
                     <span class='fa fa-edit' title='$langModify' data-toggle='tooltip' data-original-title='$langModify'></span>
                     </a>";
        $content .= "<a id='delete-btn_$count' href='../forum/viewtopic.php?course=$course_code&amp;post_id=$myrow->id&amp;topic=$topic&amp;forum=$forum&amp;delete=on'>
                     <span class='fa fa-times' title='$langDelete' data-toggle='tooltip' data-original-title='$langDelete'></span>
                     </a>";
    }
    $content .= "</h5></div>";

    $content .= "<div class='panel-body'>";
    $content .= "<span class='col-sm-1'>" . profile_image($myrow->poster_id, '40px', 'img-responsive img-circle margin-bottom-thin') . "</span>";
    $message = $myrow->post_text;
    // support for math symbols
    $message = mathfilter($message, 12, "../../courses/mathimg/");

    $rate_str = "";
    if (setting_get(SETTING_FORUM_RATING_ENABLE, $course_id)) {
        $rating = new Rating('thumbs_up', 'forum_post', $myrow->id);
        $rate_str = $rating->put($is_editor, $uid, $course_id);
    }

    $dyntools = (!$is_editor) ? array() : array(
        array('title' => $langModify,
            'url' => "../forum/editpost.php?course=$course_code&amp;post_id=" . $myrow->id . "&amp;topic=$topic&amp;forum=$forum",
            'icon' => 'fa-edit'
        ),
        array('title' => $langDelete,
            'url' => "../forum/viewtopic.php?course=$course_code&amp;post_id=$myrow->id&amp;topic=$topic&amp;forum=$forum&amp;delete=on",
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

    $content .= "<span class='col-sm-11'>";
    $content .= "<div class='forum-post-header'>";
    $content .= "<small class='help-block'><strong>$langSent:</strong> " . claro_format_locale_date($dateTimeFormatShort, strtotime($myrow->post_time)) . "";
    $content .= " $langFrom2 " .display_user($myrow->poster_id, false, false) . " (" . $user_stats[$myrow->poster_id] . ")</small>";

    if (!empty($dyntools)) {
        $content .= "<span style='margin-left: 20px;' class='pull-right'>";
        if (isset($report_modal)) {
            $content .= "<span class='option-btn-cell'>" . action_button($dyntools) . $report_modal . "</span>";
            unset($report_modal);
        } else {
            $content .= "<span class='option-btn-cell'>" . action_button($dyntools) . "</span>";
        }
        $content .= "</span>";
    }
    $content .= "</div></span>";

    /* message body */
    $content .= "<div class='col-sm-offset-1' style='margin-top: 40px;'><span class='text-justify'>$message</span></div>";

    $content .= "</div>";
    /* footer */
    $content .= "<div class='panel-footer'>";
    if ($topic_locked != 1 and $count > 1) { // `reply` button except first post (and if topic is not locked)
        if (isset($unit)) {
            $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id&amp;unit=$unit";
        } else {
            $reply_url = "reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id";
        }
        $reply_button_link = "<a class='btn btn-success btn-xs reply-post-btn' style='margin-right:15px;' href='$reply_url'>$langReply</a>";
    }
    $content .= "<div class='row'>
                    <span class='pull-left'>$rate_str</span>
                    <span class='pull-right'><small>$reply_button_link $parent_post_link</small>                      
                 </div>";
    $content .= "</div>";
    /* end of footer */
    $content .= "</div>";

    return $content;
}