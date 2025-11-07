<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
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
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/forum/functions.php';

$unit = isset($_GET['unit'])? intval($_GET['unit']): null;
$res_type = isset($_GET['res_type']);
if (!add_units_navigation(true)) {
    if (!$res_type) {
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
    } else {
        $navigation[] = array('url' => "../wall/index.php?course=$course_code", 'name' => $langWall);
    }
}

if ($is_editor) {
    load_js('tools.js');
}
load_js('datatables');

$next = 0;
if (isset($_GET['forum'])) {
    $forum_id = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
$is_member = false;
$group_id = init_forum_group_info($forum_id);

// security check
if (($group_id) and !$is_editor) {
    if (!$is_member or !$has_forum) {
        header("Location: index.php?course=$course_code");
        exit();
    }
}

$myrow = Database::get()->querySingle("SELECT id, name FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);

$forum_name = $myrow->name;
$forum_id = $myrow->id;

if (isset($_GET['empty'])) { // if we come from newtopic.php
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEmptyNewTopic</span></div></div>";
}

$pageName = $forum_name;
if ($can_post) {
    $newtopicUrl = "newtopic.php?course=$course_code&amp;forum=$forum_id";
    if ($unit) {
        $newtopicUrl = "view.php?course=$course_code&amp;res_type=forum_new_topic&amp;forum=$forum_id&amp;unit=$unit";
    } else if ($res_type) {
        $newtopicUrl = "view.php?course=$course_code&amp;res_type=forum_new_topic&amp;forum=$forum_id";
    }
    $action_bar =
            action_bar(array(
                array('title' => $langNewTopic,
                    'url' => $newtopicUrl,
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')
                ));
    $tool_content .= $action_bar;
}

/*
 * Retrieve and present data from course's forum
 */

$total_topics = Database::get()->querySingle("SELECT num_topics FROM forum
                WHERE id = ?d
                AND course_id = ?d", $forum_id, $course_id)->num_topics;

if ($total_topics > TOPICS_PER_PAGE) {
    if(($total_topics % TOPICS_PER_PAGE) == 0) {
        $pages = intval($total_topics / TOPICS_PER_PAGE); // get total number of pages
    } else {
        $pages = intval($total_topics / TOPICS_PER_PAGE) + 1; // get total number of pages
    }
}

if (isset($_GET['start'])) {
    $first_topic = intval($_GET['start']);
} else {
    $first_topic = 0;
}

// delete topic
if (($is_editor) and isset($_GET['topicdel'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
    $number_of_posts = get_total_posts($topic_id);
    $sql = Database::get()->queryArray("SELECT id,poster_id,post_text FROM forum_post WHERE topic_id = ?d", $topic_id);
    $post_authors = array();
    foreach ($sql as $r) {
        $post_authors[] = $r->poster_id;
        //delete abuse_reports for forum_posts and log actions
        $result = Database::get()->queryArray("SELECT * FROM abuse_report WHERE `rid` = ?d AND `rtype` = ?s", $r->id, 'forum_post');
        foreach ($result as $res) {
            Log::record($res->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
                array('id' => $res->id,
                     'user_id' => $res->user_id,
                     'reason' => $res->reason,
                     'message' => $res->message,
                     'rtype' => 'forum_post',
                     'rid' => $r->id,
                     'rcontent' => $r->post_text,
                     'status' => $res->status
            ));
        }
        Database::get()->query("DELETE FROM abuse_report WHERE rid = ?d AND rtype = ?s", $r->id, 'forum_post');
        //delete forum posts rating first
        Database::get()->query("DELETE FROM rating WHERE rtype = ?s AND rid = ?d", 'forum_post', $r->id);
        Database::get()->query("DELETE FROM rating_cache WHERE rtype = ?s AND rid = ?d", 'forum_post', $r->id);
        Database::get()->query("DELETE FROM forum_post WHERE id = $r->id");
        triggerForumGame($course_id, $uid, ForumEvent::DELPOST);
        triggerTopicGame($course_id, $uid, ForumTopicEvent::DELPOST, $topic_id);
        triggerForumAnalytics($course_id, $uid, ForumAnalyticsEvent::FORUMEVENT);
    }
    $post_authors = array_unique($post_authors);
    foreach ($post_authors as $author) {
        $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $author, $course_id);
        Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $author, $course_id);
        if ($forum_user_stats->c != 0) {
            Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $author, $forum_user_stats->c, $course_id);
        }
    }
    Indexer::queueAsync(Indexer::REQUEST_REMOVEBYTOPIC, Indexer::RESOURCE_FORUMPOST, $topic_id);
    $number_of_topics = get_total_topics($forum_id);
    $num_topics = $number_of_topics - 1;
    if ($number_of_topics < 0) {
        $num_topics = 0;
    }
    Database::get()->query("DELETE FROM forum_topic WHERE id = ?d AND forum_id = ?d", $topic_id, $forum_id);
    Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUMTOPIC, $topic_id);

    $last_post = Database::get()->querySingle("SELECT MAX(last_post_id) AS last_post FROM forum_topic WHERE forum_id = ?d", $forum_id)->last_post;
    if (!$last_post) {
        $last_post = 0;
    }

    Database::get()->query("UPDATE forum SET num_topics = ?d,
                                num_posts = num_posts-$number_of_posts,
                                last_post_id = ?d
                            WHERE id = ?d
                                AND course_id = ?d", $num_topics, $last_post, $forum_id, $course_id);
    Database::get()->query("DELETE FROM forum_notify WHERE topic_id = ?d AND course_id = ?d", $topic_id, $course_id);
    Session::flash('message',$langTopicDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/viewforum.php?course=$course_code&forum=$forum_id");
}

// modify topic notification
if (isset($_GET['topicnotify'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
    $rows = Database::get()->querySingle("SELECT COUNT(*) AS count FROM forum_notify
        WHERE user_id = ?d AND topic_id = ?d AND course_id = ?d", $uid, $topic_id, $course_id);
    if ($rows->count > 0) {
        Database::get()->query("UPDATE forum_notify SET notify_sent = ?d
            WHERE user_id = ?d AND topic_id = ?d AND course_id = ?d", $_GET['topicnotify'], $uid, $topic_id, $course_id);
    } else {
        Database::get()->query("INSERT INTO forum_notify SET user_id = ?d,
            topic_id = $topic_id, notify_sent = 1, course_id = ?d", $uid, $course_id);
    }
}

if (isset($_GET['topicpin'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
}

//lock and unlock topic
if ($is_editor and isset($_GET['topiclock'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
    Database::get()->query("UPDATE forum_topic SET locked = !locked WHERE id = ?d", $topic_id);
    $locked = Database::get()->querySingle("SELECT locked FROM forum_topic WHERE id = ?d", $topic_id)->locked;
    if ($locked == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langUnlockedTopic</span></div></div>";
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langLockedTopic</span></div></div>";
    }

}

$result = Database::get()->queryArray("SELECT t.*, p.post_time, t.poster_id AS topic_poster_id, p.poster_id AS poster_id, f.cat_id
        FROM forum_topic t
        LEFT JOIN forum_post p ON t.last_post_id = p.id
        INNER JOIN forum f ON t.forum_id = f.id
        WHERE t.forum_id = ?d
        ORDER BY topic_time DESC", $forum_id);

if (count($result) > 0) { // topics found
    $tool_content .= "<div class='table-responsive'>
        <table class='table-default forum_viewforum'>
        <thead>
        <tr class='list-header'>
          <th class='forum_td'>$langTopics</th>
          <th>$langAnswers</th>
          <th>$langSender</th>
          <th>$langSeen</th>
          <th>$langLastMsg</th>
          <th aria-label='$langSettingSelect'></th>
        </tr></thead>";
    foreach ($result as $myrow) {
        $replies = $myrow->num_replies;
        $topic_id = $myrow->id;
        $forum_id = $myrow->forum_id;
        $cat_id = $myrow->cat_id;
        $last_post_datetime = $myrow->post_time;
        $topic_title = $myrow->title;
        $topic_locked = $myrow->locked;

        $pagination = '';
        $topiclink = "viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id";
        if ($unit) {
            $topiclink = "view.php?course=$course_code&amp;&amp;res_type=forum_topic&amp;topic=$topic_id&amp;forum=$forum_id&amp;unit=$unit";
        } else if ($res_type) {
            $topiclink = "view.php?course=$course_code&amp;&amp;res_type=forum_topic&amp;topic=$topic_id&amp;forum=$forum_id";
        }
        if ($topic_locked) {
            $image_lock = icon('fa-lock');
        } else {
            $image_lock = '';
        }

        if ($replies >= HOT_THRESHOLD) {
            $image_fire = icon('fa-fire');
        } else {
            $image_fire = '';
        }

//        $sql_notify = Database::get()->querySingle("SELECT notify_sent FROM forum_notify
//			WHERE user_id = ?d AND topic_id = ?d AND course_id = ?d AND notify_sent = 1", $uid, $myrow->id, $course_id);

        $sql_notify = Database::get()->queryArray("SELECT * FROM forum_notify
			WHERE user_id = ?d
			AND course_id = ?d
			AND cat_id = ?d
			OR forum_id = ?d
			OR topic_id = ?d
			order by topic_id DESC, forum_id DESC, cat_id DESC LIMIT 1", $uid, $course_id, $cat_id, $forum_id, $topic_id);

        if ($sql_notify && $sql_notify[0]->notify_sent == 1) {
            $notify_action = 1;
            $image_notify = icon('fa-envelope');
        } else {
            $notify_action = 0;
            $image_notify = '';
        }

        $tool_content .= "<td><div class='d-flex justify-content-between border-0'><a href='$topiclink'>" . q($topic_title) . "</a> <span class='d-flex align-items-center gap-2'>$image_lock $image_fire $image_notify</span></div></td>";
        $tool_content .= "<td>$replies</td>";
        $tool_content .= "<td>" . q(uid_to_name($myrow->topic_poster_id)) . "</td>";
        $tool_content .= "<td>$myrow->num_views</td>";
        if (!is_null($last_post_datetime)) {
//            $tool_content .= "<td data-order='$last_post_datetime'>";
            $tool_content .= "<td>";
            $tool_content .= format_locale_date(strtotime($last_post_datetime), 'short');
        } else {
            $tool_content .= "<td data-order='00/00/0000 - 00:00'>";
        }
        $tool_content .= "</td>";
        $sql = Database::get()->querySingle("SELECT notify_sent FROM forum_notify
            WHERE user_id = ?d AND topic_id = ?d AND course_id = ?d", $uid, $myrow->id, $course_id);
        if ($sql) {
            $topic_action_notify = $sql->notify_sent;
        }
        if (!isset($topic_action_notify)) {
            $topic_link_notify = FALSE;
            $topic_icon = '_off';
        } else {
            $topic_link_notify = toggle_link($topic_action_notify);
            $topic_icon = toggle_icon($topic_action_notify);
        }
        $tool_content .= "<td class='text-end option-btn-cell'>";
        if ($unit) {
            $modify_link = "../forum/forum_admin.php?course=$course_code&amp;forumtopicedit=yes&amp;topic_id=$myrow->id";
            $del_link = "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topicdel=yes";
        } else {
            $modify_link = "forum_admin.php?course=$course_code&amp;forumtopicedit=yes&amp;topic_id=$myrow->id";
            $del_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topicdel=yes";
        }

        $dyntools = (!$is_editor) ? array() : array(
            array('title' => $langModify,
                'url' => $modify_link,
                'icon' => 'fa-edit'
            ),
            array('title' => $langDelete,
                'url' => $del_link,
                'icon' => 'fa-xmark',
                'class' => 'delete',
                'confirm' => $langConfirmDelete)
        );

        if ($is_editor) {
            if ($unit) {
                $lock_link = "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topiclock=yes";
                $unlock_link = "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topiclock=yes";
            } else {
                $lock_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topiclock=yes";
                $unlock_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow->id&amp;topiclock=yes";
            }

            if ($topic_locked == 0) {
                $dyntools[] = array('title' => $langLockTopic,
                    'url' => $lock_link,
                    'icon' => 'fa-lock'
                    );
            } else {
                $dyntools[] = array('title' => $langUnlockTopic,
                    'url' => $unlock_link,
                    'icon' => 'fa-unlock'
                    );
            }
        }

        if ($unit) {
            $link_notify = "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow->id";
            if (isset($_GET['start']) and $_GET['start'] > 0) {
                $link_notify = "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;start=$_GET[start]&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow->id";
            }
        } else {
            $link_notify = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow->id";
            if (isset($_GET['start']) and $_GET['start'] > 0) {
                $link_notify = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;start=$_GET[start]&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow->id";
            }
        }

        $dyntools[] = array('title' => $notify_action ? $langStopNotify : $langNotify,
                            'url' => $link_notify,
                            'icon' => $notify_action ? 'fa-envelope-open' : 'fa-envelope',
                            'show' => (!setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)));

        $tool_content .= action_button($dyntools);
        $tool_content .= "</td></tr>";
    } // end of while
    $tool_content .= "</table>

    <script>
            $(document).ready(function() {
                $('.table-default').DataTable({
                    ordering: true,
                    order: [],
                    searching: true,
                    columnDefs: [
                        {
                            targets: 0, // Enable searching only for the first column
                            searchable: true
                        },
                        {
                            targets: '_all', // Disable searching for all other columns
                            searchable: false
                        },
                        {
                            targets: -1, // No orderable for last column
                            orderable: false
                        }
                    ],
                    'searchDelay': 1000,
                    'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '" . $langNoResult . "',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
                });
            });
        </script>

    </div>";
} else {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoTopics</span></div></div>";
}
draw($tool_content, 2, null, $head_content);
