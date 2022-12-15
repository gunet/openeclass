<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */
require_once 'modules/rating/class.rating.php';
require_once 'modules/comments/class.commenting.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/abuse_report/abuse_report.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/wall/ExtVideoUrlParser.class.php';

doc_init();

function allow_to_post($course_id, $user_id, $is_editor) {
    global $session;
    if (!$session->status) {
        return false;
    }
    if ($is_editor) {
        return true;
    } else {
        $sql = "SELECT COUNT(`user_id`) as c FROM `course_user` WHERE `course_id` = ?d AND `user_id` = ?d";
        $result = Database::get()->querySingle($sql, $course_id, $user_id);
        if ($result->c > 0) {//user is course member
            return true;
        } else {//user is not course member
            return false;
        }
    }
}

function allow_to_edit($post_id, $user_id, $is_editor) {
    global $session;
    if (!$session->status) {
        return false;
    }
    if ($is_editor) {
        global $course_id;
        $sql = "SELECT COUNT(`id`) as c FROM `wall_post` WHERE `id` = ?d AND `course_id` = ?d";
        $result = Database::get()->querySingle($sql, $post_id, $course_id);
        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        $sql = "SELECT COUNT(`user_id`) as c FROM `wall_post` WHERE `id` = ?d AND `user_id` = ?d";
        $result = Database::get()->querySingle($sql, $post_id, $user_id);
        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function links_autodetection($text) {
    $ret_text = '';

    $rexProtocol = '(https?://)?';
    $rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
    $rexPort     = '(:[0-9]{1,5})?';
    $rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
    $rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';

    $validTlds = array_fill_keys(explode(" ", ".aero .asia .biz .cat .com .coop .edu .gov .info .int .jobs .mil .mobi .museum .name .net .org .pro .tel .travel .ac .ad .ae .af .ag .ai .al .am .an .ao .aq .ar .as .at .au .aw .ax .az .ba .bb .bd .be .bf .bg .bh .bi .bj .bm .bn .bo .br .bs .bt .bv .bw .by .bz .ca .cc .cd .cf .cg .ch .ci .ck .cl .cm .cn .co .cr .cu .cv .cx .cy .cz .de .dj .dk .dm .do .dz .ec .ee .eg .er .es .et .eu .fi .fj .fk .fm .fo .fr .ga .gb .gd .ge .gf .gg .gh .gi .gl .gm .gn .gp .gq .gr .gs .gt .gu .gw .gy .hk .hm .hn .hr .ht .hu .id .ie .il .im .in .io .iq .ir .is .it .je .jm .jo .jp .ke .kg .kh .ki .km .kn .kp .kr .kw .ky .kz .la .lb .lc .li .lk .lr .ls .lt .lu .lv .ly .ma .mc .md .me .mg .mh .mk .ml .mm .mn .mo .mp .mq .mr .ms .mt .mu .mv .mw .mx .my .mz .na .nc .ne .nf .ng .ni .nl .no .np .nr .nu .nz .om .pa .pe .pf .pg .ph .pk .pl .pm .pn .pr .ps .pt .pw .py .qa .re .ro .rs .ru .rw .sa .sb .sc .sd .se .sg .sh .si .sj .sk .sl .sm .sn .so .sr .st .su .sv .sy .sz .tc .td .tf .tg .th .tj .tk .tl .tm .tn .to .tp .tr .tt .tv .tw .tz .ua .ug .uk .us .uy .uz .va .vc .ve .vg .vi .vn .vu .wf .ws .ye .yt .yu .za .zm .zw .xn--0zwm56d .xn--11b5bs3a9aj6g .xn--80akhbyknj4f .xn--9t4b11yi5a .xn--deba0ad .xn--g6w251d .xn--hgbk6aj7f53bba .xn--hlcj6aya9esc7a .xn--jxalpdlp .xn--kgbechtv .xn--zckzah .arpa"), true);

    $position = 0;
    while (preg_match("{\\b$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$))}i", $text, $match, PREG_OFFSET_CAPTURE, $position))
    {
        list($url, $urlPosition) = $match[0];

        // Print the text leading up to the URL.
        $ret_text .= htmlspecialchars(substr($text, $position, $urlPosition - $position));

        $domain = $match[2][0];
        $port   = $match[3][0];
        $path   = $match[4][0];

        // Check if the TLD is valid - or that $domain is an IP address.
        $tld = strtolower(strrchr($domain, '.'));
        if (preg_match('{\.[0-9]{1,3}}', $tld) || isset($validTlds[$tld]))
        {
            // Prepend http:// if no protocol specified
            $completeUrl = $match[1][0] ? $url : "http://$url";
            $completeUrl = htmlspecialchars($completeUrl);
            // Print the hyperlink.
            $ret_text .= sprintf('<a target="_blank" href="%s">%s</a>', $completeUrl, $completeUrl);
        }
        else
        {
            // Not a valid URL.
            $ret_text .= htmlspecialchars($url);
        }

        // Continue text parsing from after the URL.
        $position = $urlPosition + strlen($url);
    }

    // Print the remainder of the text.
    $ret_text .= htmlspecialchars(substr($text, $position));

    return $ret_text;
}

function generate_single_post_html($post) {
    global $urlServer, $langWallSharedPost, $langWallSharedVideo, $langWallUser,
    $course_code, $is_editor, $uid, $course_id, $langModify, $langDelete, $head_content, $langWallPostDelConfirm,
    $langWallPinPost, $langWallUnPinPost;

    commenting_add_js();

    $user_id = $post->user_id;
    $id = $post->id;
    $content = $post->content;
    $token = token_generate($user_id, true);
    $datetime = format_locale_date(strtotime($post->datetime));
    $extvideo = $post->extvideo;
    $pinned = $post->pinned;
    if ($extvideo == '') {
        $shared = $langWallSharedPost;
        $extvideo_block = '';
    } else {
        $shared = $langWallSharedVideo;
        $extvideo_embed = ExtVideoUrlParser::get_embed_url($extvideo);
        if ($extvideo_embed[0] == 'youtube') {
            $extvideo_block = '<div class="video_status">
                                   <iframe  scrolling="no" width="445" height="250" src="'.$extvideo_embed[1].'" frameborder="0" allowfullscreen></iframe>
                               </div>';
        } elseif ($extvideo_embed[0] == 'vimeo') {
            $extvideo_block = '<div class="video_status">
                                   <iframe  scrolling="no" width="445" height="250" src="'.$extvideo_embed[1].'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                               </div>';
        }
    }

    $rating = new Rating('thumbs_up', 'wallpost', $id);
    $rating_content = $rating->put($is_editor, $uid, $course_id);

    $comm = new Commenting('wallpost', $id);
    $comm_content = $comm->put($course_code, $is_editor, $uid, true);

    if (allow_to_edit($id, $uid, $is_editor)) {
        $head_content .= '<script>
                          $(document).on("click", ".link", function(e) {
                              var link = $(this).attr("href");
                              e.preventDefault();
                              bootbox.confirm("'.$langWallPostDelConfirm.'", function(result) {
                                  if (result) {
                                      document.location.href = link;
                                  }
                              });
                          });
                      </script>';

        $post_actions = '<div class="action-btns pull-right">';
        $post_actions .= '<a class="link" href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;delete='.$id.'">
                          <span class="fa fa-fw fa-times text-danger pull-right" data-original-title="'.$langDelete.'" title="" data-toggle="tooltip"></span></a>';
        if ($is_editor) { //add link for pin post
            $post_actions .= '<a href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;pin='.$id.'">';
            if ($pinned == 0) {
                $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
            } elseif ($pinned == 1) {
                $post_actions .= '<span class="fa fa-fw fa-thumb-tack text-danger pull-right" data-original-title="'.$langWallUnPinPost.'" title="" data-toggle="tooltip"></span></a>';
            }
        }
        if (!$is_editor) {
            if ($pinned == 1) {
                $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
            }
        }
        $post_actions .= '<a href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;edit='.$id.'">
                          <span class="fa fa-fw fa-edit pull-right" data-original-title="'.$langModify.'" title="" data-toggle="tooltip"></span></a>';

        if (abuse_report_show_flag('wallpost', $id, $course_id, $is_editor)) {
            $head_content .= abuse_report_add_js();
            $post_actions .= abuse_report_icon_flag ('wallpost', $id, $course_id);
        }
        $post_actions .= '</div>';
    } else {
        $post_actions = '<div class="action-btns pull-right">';
        if ($pinned == 1) {
            $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
        }
        if (abuse_report_show_flag('wallpost', $id, $course_id, $is_editor)) {
            $head_content .= abuse_report_add_js();
            $post_actions .= abuse_report_icon_flag ('wallpost', $id, $course_id);
        }
        $post_actions .= '</div>';
    }



    $ret = '<div class="row margin-right-thin margin-left-thin margin-top-thin">
                              <div class="col-sm-12">
                                  <div class="media">
                                      <a class="media-left" href="'.$urlServer.'main/profile/display_profile.php?id='.$user_id.'&amp;token='.$token.'">
                                        '. profile_image($user_id, IMAGESIZE_SMALL, 'img-circle') .'
                                      </a>
                                      <div class="media-body bubble">
                                          <div class="label label-success media-heading">'.$datetime.'</div>
                                          <small>'.$langWallUser.display_user($user_id, false, false).$shared.'</small>
                                          '.$post_actions.'
                                          <div class="margin-top-thin" style="padding:20px">
                                              '.$extvideo_block.'
                                              <div class="userContent">'.nl2br(standard_text_escape($content)).'</div>
                                          </div>
                                          '.show_resources($id).'
                                          '.$rating_content.'
                                          '.$comm_content.'
                                      </div>
                                  </div>
                              </div>
                          </div>';

    return $ret;
}

function generate_infinite_container_html($posts, $posts_per_page, $next_page) {
    global $urlServer, $langWallSharedPost, $langWallSharedVideo, $langWallUser, $langComments,
           $course_code, $is_editor, $uid, $course_id, $langModify, $langDelete, $head_content, $langWallPostDelConfirm,
           $langWallPinPost, $langWallUnPinPost;

    $head_content .= '<script>
                          $(document).on("click", ".link", function(e) {
                              var link = $(this).attr("href");
                              e.preventDefault();
                              bootbox.confirm("'.$langWallPostDelConfirm.'", function(result) {
                                  if (result) {
                                      document.location.href = link;
                                  }
                              });
                          });
                      </script>';

    $ret = '<div class="infinite-container">';
    foreach ($posts as $post) {
        $user_id = $post->user_id;
        $id = $post->id;
        $content = $post->content;
        $pinned = $post->pinned;
        $token = token_generate($user_id, true);
        $datetime = format_locale_date(strtotime($post->datetime));
        $extvideo = $post->extvideo;
        if ($extvideo == '') {
            $shared = $langWallSharedPost;
            $extvideo_block = '';
        } else {
            $shared = $langWallSharedVideo;
            $extvideo_embed = ExtVideoUrlParser::get_embed_url($extvideo);
            if ($extvideo_embed[0] == 'youtube') {
                $extvideo_block = '<div class="video_status">
                                       <iframe  scrolling="no" width="445" height="250" src="'.$extvideo_embed[1].'" frameborder="0" allowfullscreen></iframe>
                                   </div>';
            } elseif ($extvideo_embed[0] == 'vimeo') {
                $extvideo_block = '<div class="video_status">
                                       <iframe  scrolling="no" width="445" height="250" src="'.$extvideo_embed[1].'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                   </div>';
            }
        }

        $rating = new Rating('thumbs_up', 'wallpost', $id);
        $rating_content = $rating->put($is_editor, $uid, $course_id);

        $comm = new Commenting('wallpost', $id);
        $comm_content = "<a class='btn btn-primary btn-xs pull-right' href='".$urlServer."modules/wall/index.php?course=$course_code&amp;showPost=".$id."#comments_title'>$langComments (".$comm->getCommentsNum().")</a>";

        if (allow_to_edit($id, $uid, $is_editor)) {
            $post_actions = '<div class="action-btns pull-right">';
            $post_actions .= '<a class="link" href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;delete='.$id.'">
                              <span class="fa fa-fw fa-times text-danger pull-right" data-original-title="'.$langDelete.'" title="" data-toggle="tooltip"></span></a>';
            if ($is_editor) { //add link for pin post
                $post_actions .= '<a href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;pin='.$id.'">';
                if ($pinned == 0) {
                    $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
                } elseif ($pinned == 1) {
                    $post_actions .= '<span class="fa fa-fw fa-thumb-tack text-danger pull-right" data-original-title="'.$langWallUnPinPost.'" title="" data-toggle="tooltip"></span></a>';
                }
            }
            if (!$is_editor) {
                if ($pinned == 1) {
                    $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
                }
            }
            $post_actions .= '<a href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;edit='.$id.'">
                              <span class="fa fa-fw fa-edit pull-right" data-original-title="'.$langModify.'" title="" data-toggle="tooltip"></span></a>';
            if (abuse_report_show_flag('wallpost', $id, $course_id, $is_editor)) {
                if ($next_page == 2) { //needed only for the first page and not for dynamically added content
                    $head_content .= abuse_report_add_js(".infinite-container");
                }
                $post_actions .= abuse_report_icon_flag ('wallpost', $id, $course_id);
            }
            $post_actions .= '</div>';
        } else {
            $post_actions = '<div class="action-btns pull-right">';
            if ($pinned == 1) {
                $post_actions .= '<span class="fa fa-fw fa-thumb-tack pull-right" data-original-title="'.$langWallPinPost.'" title="" data-toggle="tooltip"></span></a>';
            }
            if (abuse_report_show_flag('wallpost', $id, $course_id, $is_editor)) {
                if ($next_page == 2) { //needed only for the first page and not for dynamically added content
                    $head_content .= abuse_report_add_js(".infinite-container");
                }
                $post_actions .= abuse_report_icon_flag ('wallpost', $id, $course_id);
            }
            $post_actions .= '</div>';
        }



        $ret .= '<div class="infinite-item">';

        $ret .= '<div class="row margin-right-thin margin-left-thin margin-top-thin">
                              <div class="col-sm-12">
                                  <div class="media">
                                      <a class="media-left" href="'.$urlServer.'main/profile/display_profile.php?id='.$user_id.'&amp;token='.$token.'">
                                        '. profile_image($user_id, IMAGESIZE_SMALL, 'img-circle') .'
                                      </a>
                                      <div class="media-body bubble">
                                          <div class="label label-success media-heading">'.$datetime.'</div>
                                          <small>'.$langWallUser.display_user($user_id, false, false).$shared.'</small>
                                          '.$post_actions.'
                                          <div class="margin-top-thin" style="padding:20px">
                                              '.$extvideo_block.'
                                              <div class="userContent">'.nl2br(standard_text_escape($content)).'</div>
                                          </div>
                                          '.show_resources($id).'
                                          '.$rating_content.'
                                          '.$comm_content.'
                                      </div>
                                  </div>
                              </div>
                          </div>';

        $ret .= '</div>';
    }
    $ret .= '</div>';
    if (count($posts) == $posts_per_page) {
        $ret .= '<a class="infinite-more-link" href="'.$urlServer.'modules/wall/loadMore.php?course='.$course_code.'&amp;page='.$next_page.'"></a>';
    }

    return $ret;
}

function insert_video($post_id) {
    global $course_id;

    if (isset($_POST['video']) and count($_POST['video']) > 0) {
        foreach ($_POST['video'] as $video_id) {
            list($table, $res_id) = explode(':', $video_id);
            $table = ($table == 'video') ? 'video' : 'videolink';
            $row = Database::get()->querySingle("SELECT * FROM $table WHERE course_id = ?d AND id = ?d", $course_id, $res_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, $table, $row->title, $res_id);
        }
    }
}

function insert_docs($post_id, $subsystem = NULL) {
    global $course_id, $uid;

    if (is_null($subsystem)) { //main documents
        if (isset($_POST['doc_ids']) and !empty($_POST['doc_ids'])) {
            $docs = explode(',', $_POST['doc_ids']);
        }
        $sql = "course_id = $course_id";
    } else if ($subsystem == 'mydocs') { //mydocuments
        if (isset($_POST['mydoc_ids']) and !empty($_POST['mydoc_ids'])) {
            $docs = explode(',', $_POST['mydoc_ids']);
        }
        $sql = "subsystem = ".MYDOCS." AND subsystem_id = $uid";
    }

    if (isset($docs)) {
        foreach ($docs as $doc) {
            $row = Database::get()->querySingle("SELECT title, filename FROM document WHERE $sql AND id = ?d", $doc);
            $text = (empty($row->title))? $row->filename : $row->title;
            $q = Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'document', $text, $doc);
        }
    }
}

function insert_links($post_id) {
    global $course_id;

    if (isset($_POST['link']) and count($_POST['link']) > 0) {
        foreach ($_POST['link'] as $link_id) {
            $row = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, 'link', $row->title, $link_id);
        }
    }
}

function insert_exercises($post_id) {
    global $course_id;

    if (isset($_POST['exercise']) and count($_POST['exercise']) > 0) {
        foreach ($_POST['exercise'] as $exercise_id) {
            $row = Database::get()->querySingle("SELECT * FROM exercise WHERE course_id = ?d AND id = ?d", $course_id, $exercise_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, 'exercise', $row->title, $exercise_id);
        }
    }
}

function insert_assignments($post_id) {
    global $course_id;

    if (isset($_POST['assignment']) and count($_POST['assignment']) > 0) {
        foreach ($_POST['assignment'] as $assignment_id) {
            $row = Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $assignment_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, 'assignment', $row->title, $assignment_id);
        }
    }
}

function insert_chats($post_id) {
    global $course_id;

    if (isset($_POST['chat']) and count($_POST['chat']) > 0) {
        foreach ($_POST['chat'] as $chat_id) {
            $row = Database::get()->querySingle("SELECT * FROM conference WHERE course_id = ?d AND conf_id = ?d", $course_id, $chat_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, 'chat', $row->conf_title, $chat_id);
        }
    }
}

function insert_polls($post_id) {
    global $course_id;

    if (isset($_POST['poll']) and count($_POST['poll']) > 0) {
        foreach ($_POST['poll'] as $poll_id) {
            $row = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $poll_id);
            Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                $post_id, 'poll', $row->name, $poll_id);
        }
    }
}

function insert_forum($post_id) {
    global $course_id;

    if (isset($_POST['forum']) and count($_POST['forum']) > 0) {
        foreach ($_POST['forum'] as $for_id) {
            $ids = explode(':', $for_id);
            if (count($ids) == 2) {
                list($forum_id, $topic_id) = $ids;
                $topic = Database::get()->querySingle("SELECT * FROM forum_topic WHERE id = ?d AND forum_id = ?d", $topic_id, $forum_id);
                Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'topic', $topic->title, $topic->id);
            } else {
                $forum_id = $ids[0];
                $forum = Database::get()->querySingle("SELECT * FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);
                Database::get()->query("INSERT INTO wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'forum', $forum->name, $forum->id);
            }
        }
    }
}

function show_resources($post_id) {
    global $langWallAttachedResources;

    $ret_str = '';

    $req = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d", $post_id);
    if (count($req) > 0) {
        $ret_str .= '<div class="table-responsive">';
        $ret_str .= '<table class="table">';
        $ret_str .= '<thead><tr><th colspan="2"><span style="font-size:12px">'.$langWallAttachedResources.'</span></th></tr></thead>';
        foreach ($req as $info) {
            $ret_str .= show_resource($info);
        }
        $ret_str .= '</table></div>';
    }
    return $ret_str;
}

function show_resource($info) {
    global $is_editor;

    switch ($info->type) {
        case 'video':
        case 'videolink':
            $ret_str = show_video($info->type, $info->title, $info->id, $info->res_id);
            break;
        case 'document' :
            $ret_str = show_document($info->title, $info->id, $info->res_id);
            break;
        case 'link' :
            $ret_str = show_link($info->title, $info->id, $info->res_id);
            break;
        case 'exercise' :
            $ret_str = show_exercise($info->title, $info->id, $info->res_id);
            break;
        case 'assignment' :
            $ret_str = show_assignment($info->title, $info->id, $info->res_id);
            break;
        case 'chat' :
            $ret_str = show_chat($info->title, $info->id, $info->res_id);
            break;
        case 'poll' :
            $ret_str = show_poll($info->title, $info->id, $info->res_id);
            break;
        case 'forum':
        case 'topic':
            $ret_str = show_forum($info->type, $info->title, $info->id, $info->res_id);
            break;
    }
    return $ret_str;
}

function show_document($title, $resource_id, $doc_id) {
    global $is_editor, $langWasDeleted;

    $file = Database::get()->querySingle("SELECT * FROM document WHERE id = ?d", $doc_id);

    if (!$file) {
        if (!$is_editor) {
            return '';
        }
        $image = 'fa-times';
        $link = "<span class='not_visible'>" . q($title) . " ($langWasDeleted)</span>";
    } else {
        $file->title = $title;
        $image = choose_image('.' . $file->format);
        $file_obj = MediaResourceFactory::initFromDocument($file);
        $file_obj->setAccessURL(file_url_replacement($file->path, $file->filename, $file->subsystem, $file->subsystem_id));
        $file_obj->setPlayURL(file_playurl_replacement($file->path, $file->filename, $file->subsystem, $file->subsystem_id));
        $link = MultimediaHelper::chooseMediaAhref($file_obj);
    }

    return "
    <tr>
    <td width='1'>" . icon($image, '') . "</td>
    <td class='text-left' style='font-size:12px'>$link</td></tr>";
}

function show_video($table, $title, $resource_id, $video_id) {
    global $is_editor, $course_id, $course_code, $urlServer;

    $row = Database::get()->querySingle("SELECT * FROM `$table` WHERE course_id = ?d AND id = ?d", $course_id, $video_id);
    if ($row) {
        if (!$is_editor and (!resource_access(1, $row->public))) {
            return '';
        }
        $row->title = $title;
        if ($table == 'video') {
            $videoplayurl = "{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=video&amp;id=$video_id";
            $vObj = MediaResourceFactory::initFromVideo($row);
            $vObj->setPlayURL($videoplayurl);
            $videolink = MultimediaHelper::chooseMediaAhref($vObj);
        } else {
            $videoplayurl = "{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=videolink&amp;id=$video_id";
            $vObj = MediaResourceFactory::initFromVideoLink($row);
            $vObj->setPlayURL($videoplayurl);
            $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
        }
        $imagelink = "fa-film";
    } else { // resource was deleted
        if (!$is_editor) {
            return '';
        }
        $videolink = q($title);
        $imagelink = "fa-times";
    }

    return "<tr><td width='1'>".icon($imagelink)."</td><td>$videolink</td></tr>";
}

function show_link($title, $resource_id, $link_id) {
    global $course_id, $is_editor;
    $row = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
    if ($row) {
        $visibility = 1;
        if ($row->title == '') {
            $title = q($row->url);
        } else {
            $title = q($title);
        }
        $linktitle = "<a href='" . q($row->url) . "' target='_blank'>$title</a>";
        $imagelink = 'fa-link';
    } else {
        if (!$is_editor) {
            return '';
        }
        $linktitle = q($title);
        $imagelink = "fa-times";
        $visibility = 0;
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='1'>".icon($imagelink)."</td><td>".$linktitle."</td></tr>";
}

function show_exercise($title, $resource_id, $exercise_id) {
    global $course_id, $course_code, $urlServer, $is_editor, $uid;
    $row = Database::get()->querySingle("SELECT * FROM exercise WHERE course_id = ?d AND id = ?d", $course_id, $exercise_id);
    if ($row) {
        if (!$is_editor and ( !resource_access($row->active, $row->public))) {
            return '';
        }
        $visibility = 1;
        // check if exercise is in `paused` state
        $paused_exercises = Database::get()->querySingle("SELECT eurid, attempt "
            . "FROM exercise_user_record "
            . "WHERE eid = ?d AND uid = ?d "
            . "AND attempt_status = ?d", $exercise_id, $uid, ATTEMPT_PAUSED);
        if ($paused_exercises) {
            $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=exercise&amp;exerciseId=$exercise_id&amp;eurId=$paused_exercises->eurid'>";
        } else {
            $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=exercise&amp;exerciseId=$exercise_id'>";
        }
        $exlink = $link.q($title)."</a>";
        $imagelink = 'fa-pencil-square-o';
    } else {
        if (!$is_editor) {
            return '';
        }
        $exlink = q($title);
        $imagelink = "fa-times";
        $visibility = 0;
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='3'>".icon($imagelink)."</td><td>".$exlink."</td></tr>";
}

function show_assignment($title, $resource_id, $assignment_id) {
    global $course_id, $course_code, $urlServer, $is_editor;
    $row = Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $assignment_id);
    if ($row) {
        $visibility = 1;
        $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;id=$assignment_id'>";
        $exlink = $link.q($title)."</a>";
        $imagelink = 'fa-flask';
    } else {
        if (!$is_editor) {
            return '';
        }
        $exlink = q($title);
        $imagelink = "fa-times";
        $visibility = 0;
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='1'>".icon($imagelink)."</td><td>".$exlink."</td></tr>";
}

function show_chat($title, $resource_id, $chat_id) {
    global $course_id, $course_code, $urlServer, $is_editor;
    $row = Database::get()->querySingle("SELECT * FROM conference WHERE course_id = ?d AND conf_id = ?d", $course_id, $chat_id);
    if ($row) {
        $visibility = 1;
        $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=chat&amp;conference_id=$chat_id'>";
        $chatlink = $link.q($title)."</a>";
        $imagelink = 'fa-exchange';
    } else {
        if (!$is_editor) {
            return '';
        }
        $chatlink = q($title);
        $imagelink = "fa-times";
        $visibility = 0;
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='1'>".icon($imagelink)."</td><td>".$chatlink."</td></tr>";
}

function show_poll($title, $resource_id, $poll_id) {
    global $course_id, $course_code, $urlServer, $is_editor;
    $row = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $poll_id);
    if ($row) {
        $visibility = 1;
        $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=questionnaire&amp;pid=$poll_id&amp;UseCase=1'>";
        $polllink = $link.q($title)."</a>";
        $imagelink = 'fa-question-circle';
    } else {
        if (!$is_editor) {
            return '';
        }
        $polllink = q($title);
        $imagelink = "fa-times";
        $visibility = 0;
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='1'>".icon($imagelink)."</td><td>".$polllink."</td></tr>";
}

function show_forum($type, $title, $resource_id, $ft_id) {
    global $is_editor, $course_id, $course_code, $urlServer;
    $title = q($title);
    if ($type == 'forum') {
        $visibility = 1;
        $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=forum&amp;forum=$ft_id'>";
        $forumlink = $link.q($title)."</a>";
        $imagelink = 'fa-comments';
    } else {
        $row = Database::get()->querySingle("SELECT forum_id FROM forum_topic WHERE id = ?d", $ft_id);
        if ($row) {
            $visibility = 1;
            $forum_id = $row->forum_id;
            $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$ft_id&amp;forum=$forum_id'>";
            $forumlink = $link.q($title)."</a>";
            $imagelink = 'fa-comments';
        } else {
            if (!$is_editor) {
                return '';
            }
            $forumlink = q($title);
            $imagelink = "fa-times";
            $visibility = 0;
        }
    }
    $class_vis = ($visibility === 0) ? ' class="not_visible"' : ' ';
    return "<tr$class_vis><td width='1'>".icon($imagelink)."</td><td>".$forumlink."</td></tr>";
}

function file_playurl_replacement($path, $filename, $subsystem, $uid) {
    global $urlServer, $group_sql;

    if ($subsystem == MYDOCS) {
        $course_code = '';
        $group_sql = "subsystem = $subsystem AND subsystem_id = $uid";
    } else {
        global $course_code, $course_id;
        $group_sql = "course_id = $course_id AND subsystem = $subsystem";
    }

    return htmlspecialchars($urlServer .
            "modules/document/play.php/$course_code" .
            public_file_path($path, $filename), ENT_QUOTES);
}

function file_url_replacement($path, $filename, $subsystem, $uid) {
    global $urlServer, $group_sql;

    if ($subsystem == MYDOCS) {
        $course_code = "user,$uid";
        $group_sql = "subsystem = $subsystem AND subsystem_id = $uid";
    } else {
        global $course_code, $course_id;
        $group_sql = "course_id = $course_id AND subsystem = $subsystem";
    }

    return htmlspecialchars($urlServer .
            "modules/document/file.php/$course_code" .
            public_file_path($path, $filename), ENT_QUOTES);
}
