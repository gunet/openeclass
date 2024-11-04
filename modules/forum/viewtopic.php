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


$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'forum';
require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/forum/functions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/course_settings.php';
require_once 'include/user_settings.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/abuse_report/abuse_report.php';

ModalBoxHelper::loadModalBox();

$toolName = $langForums;

load_js('tools.js');
$head_content .= "
    <script type='text/javascript'>
        function highlight(selector) {
            $(selector).removeClass('panel-default').removeClass('panel-primary').addClass('panel-success').css('border-color','green').css('border', '2px solid');
        }
        $(document).ready(function() {
            if (window.location.hash) {
                highlight(window.location.hash);
            }
            $('.anchor_to_parent_post_id a').click(function(e) {
                $('.post-message').removeClass('panel-success').addClass('panel-default').css('border-color','').css('border', '');
                if ($('.parent-post-message').hasClass('panel-success')) {
                    $('.parent-post-message').removeClass('panel-success').addClass('panel-primary').css('border-color','').css('border', '');
                }
                var parent_post_id = $(this).attr('id');
                highlight('#' + parent_post_id);
            });

";

if ($is_editor) { // delete post confirmation
    $head_content .= "
        $('.delete-btn').click(function(e) {
            var link = $(this).attr('href');
            e.preventDefault();            
            bootbox.confirm({ 
                closeButton: false,
                title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
                message: '<p class=\'text-center\'>".js_escape($langConfirmDelete)."</p>',
                buttons: {
                    cancel: {
                        label: '".js_escape($langCancel)."',
                        className: 'cancelAdminBtn position-center'
                    },
                    confirm: {
                        label: '".js_escape($langDelete)."',
                        className: 'deleteAdminBtn position-center',
                    }
                },
                callback: function (result) {
                    if(result) {
                        document.location.href = link;
                    }
                }
            });     

        });
    ";
}

$head_content .= "
        });
</script>";

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

$unit = $_GET['unit'] ?? null;
$res_type = isset($_GET['res_type']);

// get attached forum topic file (if any)
if (isset($_GET['get'])) {
    if (!send_forum_post_file($_GET['get'])) {
        Session::flash('message',$langFileNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
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
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langErrorTopicSelect</span></div></div>";
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
    // remove forum post attachment (if any)
    $fp = Database::get()->querySingle("SELECT topic_filepath FROM forum_post WHERE id = ?d", $post_id);
    if (!empty($fp->topic_filepath)) {
        unlink("$webDir/courses/$course_code/forum/$fp->topic_filepath");
    }
    // remove forum post entries
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
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langDeletedMessage</span></div></div>";
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
    if (!$res_type) {
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
        $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum", 'name' => q($forum_name));
    } else {
        $navigation[] = array('url' => "../wall/index.php?course=$course_code", 'name' => $langWall);
        $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum", 'name' => q($forum_name));
    }
} else {
    $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum&amp;unit=$unit", 'name' => q($forum_name));
}
$pageName = $langTopic;

if (isset($_SESSION['message'])) {
    $tool_content .= $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($topic_locked == 1) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langErrorTopicLocked</span></div></div>";
} else {
    if ($unit) {
        $back_url = "../units/index.php?course=$course_code&id=$unit";
        $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum&amp;unit=$unit";
    } else if ($res_type) {
        $back_url = "../wall/index.php?course=$course_code";
        $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum";
    } else {
        $back_url = "viewforum.php?course=$course_code&forum=$forum";
        $reply_url = "reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum";
    }
    $action_bar = action_bar(array(
                                    array('title' => $langReply,
                                        'url' => "$reply_url",
                                        'icon' => 'fa-regular fa-comments',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-success action-forum-btn'),
                                    array('title' => $langDumpPDF,
                                           'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&topic=$_GET[topic]&forum=$_GET[forum]&export_ans=true",
                                           'icon' => 'fa-solid fa-file-pdf',
                                            'level' => 'primary-label',
                                            'button-class' => 'btn-success action-forum-btn')
                                )
                            );
    $tool_content .= $action_bar;
    // forum posts view selection
    $selected_view_0 = $selected_view_1 = $selected_view_2 = 0;
    if ($view == POSTS_PAGINATION_VIEW_ASC) {
        $selected_view_0 = 'selected';
    } else if ($view == POSTS_PAGINATION_VIEW_DESC) {
        $selected_view_1 = 'selected';
    } else if ($view == POSTS_THREADED_VIEW) {
        $selected_view_2 = 'selected';
    }
    if ($unit) {
        $selection_url = "../units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum&unit=$unit";
        $hidden_inputs = "<input type='hidden' name='res_type' value='forum_topic'>
                          <input type='hidden' name='unit' value='$unit'>";
    } else if ($res_type) {
        $selection_url = "../units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum";
        $hidden_inputs = "<input type='hidden' name='res_type' value='forum_topic'>";
    } else {
        $selection_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&topic=$topic&forum=$forum";
        $hidden_inputs = '';
    }

    $tool_content .= "
        <div class='col-sm-12 selection_type mb-3'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' name='viewselect' action='$selection_url' method='get'>
                    <div class='form-group'>
                        <label for='view' class='col-sm-12 control-label-notes'>$langQuestionView</label>
                        <div class='col-sm-12'>
                            $hidden_inputs
                            <input type='hidden' name='course' value='$course_code'>
                            <input type='hidden' name='forum' value='$forum'>
                            <input type='hidden' name='topic' value='$topic'>
                            <select name='view' id='view' class='form-select' onChange='document.viewselect.submit();'>
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
            if ($unit) {
                $pagination_btns = "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page&amp;unit=$unit'><span aria-hidden='true'>&laquo;</span></a></li>";
            } else if ($res_type) {
                $pagination_btns = "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page'><span aria-hidden='true'>&laquo;</span></a></li>";
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
                if ($unit) {
                    $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$x&amp;unit=$unit'>$times</a></li>";
                } else if ($res_type) {
                    $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$x'>$times</a></li>";
                } else {
                    $pagination_btns .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=$x'>$times</a></li>";
                }

            }
            $times++;
        }
        if (($start + POSTS_PER_PAGE) < $total) {
            $next_page = $start + POSTS_PER_PAGE;
            if ($unit) {
                $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page&amp;unit=$unit'><span aria-hidden='true'>&raquo;</span></a></li>";
            } else if ($res_type) {
                $pagination_btns .= "<li><a href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page'><span aria-hidden='true'>&raquo;</span></a></li>";
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
                <div class='float-end'>";
                if ($unit) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true&amp;unit=$unit'>$langAllOfThem</a>";
                } else if ($res_type) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>";
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
                <div class='float-end'>";
                if ($unit) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0&amp;unit=$unit'>$langPages</a>";
                } else if ($res_type) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>";
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

$tool_content .= "<div class='col-12'>";

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

$tool_content .= "</div>";

Database::get()->query("UPDATE forum_topic SET num_views = num_views + 1
            WHERE id = ?d AND forum_id = ?d", $topic, $forum);

if ($view == POSTS_PAGINATION_VIEW_ASC) {
    if ($paging and $total > POSTS_PER_PAGE) {
        $tool_content .= "
        <nav>
            <ul class='pagination'>
            $pagination_btns
            </ul>
            <div class='float-end'>";
            if ($unit) {
                $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true&amp;unit=$unit'>$langAllOfThem</a>";
            } else if ($res_type) {
                $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;all=true'>$langAllOfThem</a>";
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
                <div class='float-end'>";
                if ($unit) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0&amp;unit=$unit'>$langPages</a>";
                } else if ($res_type) {
                    $tool_content .= "<a class='btn btn-default' href='../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>";
                } else {
                    $tool_content .= "<a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;start=0'>$langPages</a>";
                }
                $tool_content .= "</div>
              </nav>
            </div>";
        }
    }
}

// Export to pdf
if(isset($_GET['export_ans'])){
    pdf_forum_output($tool_content,$_GET['topic'],$_GET['forum']);
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

    global $langForumPostParentDel, $langMsgRe, $course_id, $langReply, $langAttachedFile, $unit, $res_type, $langFrom2,
           $langMessages, $course_code, $is_editor, $topic, $forum, $uid, $langMessage, $head_content,
           $langModify, $langDelete, $langSent, $webDir, $langForumPostParent;

    $content = '';
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

    $message = mathfilter($myrow->post_text, 12, "../../courses/mathimg/");

    $rate_str = "";
    if (setting_get(SETTING_FORUM_RATING_ENABLE, $course_id)) {
        $rating = new Rating('thumbs_up', 'forum_post', $myrow->id);
        $rate_str = $rating->put($is_editor, $uid, $course_id);
    }

    $dyntools = array();
    if (abuse_report_show_flag('forum_post', $myrow->id, $course_id, $is_editor)) {
        $head_content .= abuse_report_add_js();
        $flag_arr = abuse_report_action_button_flag('forum_post', $myrow->id, $course_id);
        $dyntools[] = $flag_arr[0]; //action button option
        $report_modal = $flag_arr[1]; //modal html code
    }

    // attached file (if any)
    if (!empty($myrow->topic_filename)) {
        $actual_filename = $webDir . "/courses/" . $course_code . "/forum/" . $myrow->topic_filepath;
        $fileinfo = "<p>
                        <span class='help-block'>$langAttachedFile: " .
                            "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$myrow->id";
        if ($unit) {
            $fileinfo .= "&amp;unit=$unit&amp;res_type=forum_topic";
        } else if ($res_type) {
            $fileinfo .= "&amp;res_type=forum_topic";
        }
        $fileinfo .= "'>" .q($myrow->topic_filename) ."</a> <i class='fa fa-save'></i> (" . format_file_size(filesize($actual_filename)) . ")</span>
                    </p>";
    } else {
        $fileinfo = '';
    }

    if ($topic_locked != 1 and $count > 1) { // `reply` button except first post (and if topic is not locked)
        if ($unit) {
            $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id&amp;unit=$unit";
        } else if ($res_type) {
            $reply_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id";
        } else {
            $reply_url = "reply.php?course=$course_code&amp;topic=$topic&amp;forum=$forum&amp;parent_post=$myrow->id";
        }
        $reply_button = "<a class='btn submitAdminBtn reply-post-btn' href='$reply_url'>$langReply</a>";
    } else {
        $reply_button = '';
    }

    if ($count > 1) { // for all posts except first
        $content .= "<div id='$myrow->id' class='post-message card panelCard card-default px-lg-4 py-lg-3 col-sm-offset-$offset mt-3'>";
        $content .= "<div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <div class='panel-title d-flex justify-content-between align-items-center w-100'>$langMsgRe " . q($topic_subject);
    } else {
        $content .= "<div id='$myrow->id' class='parent-post-message card panelCard card-default px-lg-4 py-lg-3 mt-3'>";
        $content .= "<div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <div class='panel-title d-flex justify-content-between align-items-center w-100'>". q($topic_subject);
    }

    if ($is_editor) {
        $content .= "
                <span class='d-flex gap-2 ps-3 actions-post-btns'>
                    <a href='../forum/editpost.php?course=$course_code&amp;post_id=" . $myrow->id .
                        "&amp;topic=$topic&amp;forum=$forum' aria-label='$langModify'>" .
                            "<span class='fa fa-edit pe-1' title='$langModify' data-bs-toggle='tooltip' " .
                                "data-bs-original-title='$langModify' data-bs-placement='bottom'></span></a>" .
                    "<a class='delete-btn link-delete' href='../forum/viewtopic.php?course=$course_code&amp;post_id=" . $myrow->id .
                        "&amp;topic=$topic&amp;forum=$forum&amp;delete=on' aria-label='$langDelete'>" .
                            "<span class='fa-solid fa-xmark' title='$langDelete' data-bs-toggle='tooltip' " .
                            "data-bs-original-title='$langDelete' data-bs-placement='bottom'></span></a>
                </span>";
    }

    // anchor to parent post
    $achor_to_parent_post = '';
    if ($myrow->parent_post_id > 0) {
        $anchor_url_to_parent_post_id = "viewtopic.php?course=$course_code&topic=$topic&forum=$forum&all=true#$myrow->parent_post_id";
        $parent_post_text = ellipsize(canonicalize_whitespace(strip_tags(get_post_text($myrow->parent_post_id))), 50);
        $achor_to_parent_post = "<div class='anchor_to_parent_post_id' style='padding-bottom: 15px;'><em>$langForumPostParent<a class='TextBold ms-1' href='$anchor_url_to_parent_post_id' id='$myrow->parent_post_id'>$parent_post_text</a></em></div>";
    }

    $content .= "
            </div>
        </div>";
        if ($rate_str or $parent_post_link or $reply_button) {
            $content .= "<div class='card-body'>";
        }else{
            $content .= "<div class='card-body'>";
        }
        $content .= "
                <div class='col-12 d-flex justify-content-start align-items-start gap-2 flex-wrap'>
                    <div class='div-profile-img'>" .
                        profile_image($myrow->poster_id, IMAGESIZE_SMALL, 'rounded-circle margin-bottom-thin') . "
                    </div>
                    <div class='flex-grow-1 d-flex justify-content-between align-items-start gap-3 flex-wrap'>
                        <div class='forum-post-header'>
                            <small class='help-block'>
                                <strong>$langSent:</strong> " . format_locale_date(strtotime($myrow->post_time), 'short') . 
                                " $langFrom2 " . display_user($myrow->poster_id, false, false) .
                                " ({$user_stats[$myrow->poster_id]})
                            </small>
                            <small>
                                $achor_to_parent_post";
                $content .= "</small>
                        </div>";
                        if (!empty($dyntools)) {
                            $content .= "<div class='div-menu-popover'>";
                            if (isset($report_modal)) {
                                $content .= "<span class='option-btn-cell'>" . action_button($dyntools) . $report_modal . "</span>";
                                unset($report_modal);
                            } else {
                                $content .= "<span class='option-btn-cell'>" . action_button($dyntools) . "</span>";
                            }
                            $content .= "</div>";
                        }
        $content .= "</div>
                </div>
                <div class='col-12 mt-3'>
                    <div class='text-justify'>$message</div>
                    $fileinfo
                </div>
            
        </div>";
    if ($rate_str or $parent_post_link or $reply_button) {
        $content .= "
        <div class='card-footer border-0'>
            <div class='row'>
                <div class='col-12 d-flex justify-content-between align-items-center'>
                   
                        <span>$rate_str $parent_post_link</span>
                        <span>$reply_button</span>
                    
                </div>
            </div>
        </div>";
    }
    $content .= "</div>";

    return $content;
}


/**
 * @brief output to pdf file for course forum
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_forum_output($content_m,$topic_id,$forum_id) {
    global $currentCourseName, $webDir, $course_id, $course_code, $language;

    $res = Database::get()->querySingle("SELECT * FROM forum_topic WHERE id = ?d AND forum_id = ?d",$topic_id,$forum_id);

    $newContent1 = str_replace("<a","<span",$content_m);
    $newContent2 = str_replace("</a>","</span>",$newContent1);
    $topicName = $res->title;

    $pdf_mcontent = "
        <!DOCTYPE html>
        <html lang='$language'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName") . "</title>
          <style>
            * { font-family: 'opensans'; }
            body { font-family: 'opensans'; font-size: 10pt; }
            small, .small { font-size: 8pt; }
            h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
            h1 { font-size: 16pt; }
            h2 { font-size: 12pt; }
            h3 { font-size: 10pt; color: #158; }
            .card-default { background: #fafafa; }
            .panel-title { color: #5d6d7e; }
            .action-bar-title { display: none; }
            .actions-post-btns { display: none; }
            .selection_type { display: none; }
            .ButtonsContent { display: none; }
            .div-profile-img { display: none; }
            .reply-post-btn { display: none; }
            .div-menu-popover{ display: none; }
            .card-default {border: solid 1px #000000; padding: 10px; margin-top: 15px; }
          </style>
        </head>
        <body>" . get_platform_logo() .
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($topicName) . "</h2>";

    $pdf_mcontent .= $newContent2;

    $pdf_mcontent .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
        'fontdata' => $fontData + [
                'opensans' => [
                    'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                    'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                    'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                    'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                ],
                'roboto' => [
                    'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                    'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                ]
            ]
    ]);

    $mpdf->setFooter('{DATE j-n-Y} || {PAGENO} / {nb}');
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_mcontent);
    $mpdf->Output("forum_topic.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}