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


require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/mentoring/programs/group/document/mentoring_doc_init.php';
require_once 'modules/mentoring/programs/wall/MentoringExtVideoUrlParser.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_doc_init();

function allow_to_post($mentoring_program_id, $user_id, $is_editor_wall) {

    global $session, $program_group_id, $is_member_common_group;

    if ($is_editor_wall) {
        return true;
    } else {
        $sql = "SELECT COUNT(`user_id`) as c FROM `mentoring_group_members` WHERE `group_id` = ?d AND `user_id` = ?d AND is_tutor = ?d AND status_request = ?d";
        $result = Database::get()->querySingle($sql, $program_group_id, $user_id, 0, 1);
        if ($result->c > 0) {//user is common group member
            $is_member_common_group = true;
            return $is_member_common_group;
        } else {//user is not common group member
            return false;
        }
    }
}

function allow_to_edit($post_id, $user_id, $is_editor_wall) {
    global $session, $is_member_common_group;

    if ($is_editor_wall) {
        global $mentoring_program_id;
        $sql = "SELECT COUNT(`id`) as c FROM `mentoring_wall_post` WHERE `id` = ?d AND `mentoring_program_id` = ?d";
        $result = Database::get()->querySingle($sql, $post_id, $mentoring_program_id);
        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        $sql = "SELECT COUNT(`user_id`) as c FROM `mentoring_wall_post` WHERE `id` = ?d AND `user_id` = ?d";
        $result = Database::get()->querySingle($sql, $post_id, $user_id);
        if ($result->c > 0) {
            $is_member_common_group = true;
            return $is_member_common_group;
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


function insert_docs($post_id, $subsystem = NULL) {
    global $mentoring_program_id, $uid, $program_group_id, $group_sql;

    if (is_null($subsystem)) { //MENTORING_GROUP
        if (isset($_POST['doc_ids']) and !empty($_POST['doc_ids'])) {
            $docs = explode(',', $_POST['doc_ids']);
        }
       
        $sql = "mentoring_program_id = $mentoring_program_id AND subsystem = ".MENTORING_GROUP." AND subsystem_id = $program_group_id";
    } else if ($subsystem == 'mydocs') { //mydocuments
        if (isset($_POST['mydoc_ids']) and !empty($_POST['mydoc_ids'])) {
            $docs = explode(',', $_POST['mydoc_ids']);
        }
        $sql = "subsystem = ".MYDOCS." AND subsystem_id = $uid";
    }

    if (isset($docs)) {
        foreach ($docs as $doc) {
            $row = Database::get()->querySingle("SELECT title, filename FROM mentoring_document WHERE $sql AND id = ?d", $doc);
            $text = (empty($row->title))? $row->filename : $row->title;
            $q = Database::get()->query("INSERT INTO mentoring_wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'mentoring_document', $text, $doc);
        }
    }
}


function insert_forum($post_id) {
    global $mentoring_program_id;

    if (isset($_POST['forum']) and count($_POST['forum']) > 0) {
        foreach ($_POST['forum'] as $for_id) {
            $ids = explode(':', $for_id);
            if (count($ids) == 2) {
                list($forum_id, $topic_id) = $ids;
                $topic = Database::get()->querySingle("SELECT * FROM mentoring_forum_topic WHERE id = ?d AND forum_id = ?d", $topic_id, $forum_id);
                Database::get()->query("INSERT INTO mentoring_wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'mentoring_topic', $topic->title, $topic->id);
            } else {
                $forum_id = $ids[0];
                $forum = Database::get()->querySingle("SELECT * FROM mentoring_forum WHERE id = ?d AND mentoring_program_id = ?d", $forum_id, $mentoring_program_id);
                Database::get()->query("INSERT INTO mentoring_wall_post_resources SET post_id = ?d, type = ?s, title = ?s, res_id = ?d",
                    $post_id, 'mentoring_forum', $forum->name, $forum->id);
            }
        }
    }
}

function addComment($post_id,$token){
    global $uid, $mentoring_program_id, $langComments, $is_admin, $is_editor_mentoring_program, $urlAppend, $program_group_id;

    $htmlAddComment = "";

    $countPosts = Database::get()->querySingle("SELECT COUNT(rid) as c FROM mentoring_comments WHERE rid = ?d",$post_id)->c;

    $postIdBelongToUser = Database::get()->querySingle("SELECT user_id FROM mentoring_wall_post WHERE id = ?d AND mentoring_program_id = ?d",$post_id,$mentoring_program_id)->user_id;
    
    $htmlAddComment .= "<a class='TextSemiBold blackBlueText' 
                            href='{$urlAppend}modules/mentoring/programs/wall/comments_post_wall.php?post_id=".getInDirectReference($post_id)."&amp;addComment&amp;postUser=".getInDirectReference($postIdBelongToUser)."&amp;group_id=".getInDirectReference($program_group_id)."&amp;token=".getInDirectReference($token)."&amp;countPosts=".getInDirectReference($countPosts)."'>
                            <span class='fa-solid fa-paper-plane'></span>&nbsp$langComments&nbsp(<span class='TextBold'>$countPosts</span>)
                        </a>";
    

    return $htmlAddComment;
}


function show_resources($post_id) {
    global $langWallAttachedResources;

    $ret_str = '';

    $req = Database::get()->queryArray("SELECT * FROM mentoring_wall_post_resources WHERE post_id = ?d", $post_id);
    if (count($req) > 0) {
        $ret_str .= '<div class="table-responsive">';
        $ret_str .= '<table class="table table-default">';
        $ret_str .= '<thead><tr><th colspan="2"><span>'.$langWallAttachedResources.'</span></th></tr></thead>';
        foreach ($req as $info) {
            $ret_str .= show_resource($info);
        }
        $ret_str .= '</table></div>';
    }
    return $ret_str;
}

function show_resource($info) {
    global $is_editor_wall;

    switch ($info->type) {
        case 'mentoring_document' :
            $ret_str = show_document($info->title, $info->id, $info->res_id);
            break;
        case 'mentoring_forum':
        case 'mentoring_topic':
            $ret_str = show_forum($info->type, $info->title, $info->id, $info->res_id);
            break;
    }
    return $ret_str;
}

function show_document($title, $resource_id, $doc_id) {
    global $is_editor_wall, $langWasDeleted, $mentoring_program_code;

    $fromFile = '';
    $file = Database::get()->querySingle("SELECT * FROM mentoring_document WHERE id = ?d", $doc_id);

    if (!$file) {
        if (!$is_editor_wall) {
            return '';
        }
        $image = 'fa-times';
        $link = "<span class='not_visible'>" . q($title) . " ($langWasDeleted)</span>";
    } else {
        $file->title = $title;
        $image = choose_image('.' . $file->format);
        $file_obj = MediaResourceFactory::initFromDocument($file);
        $file_obj->setAccessURL(wall_file_url_replacement($file->path, $file->filename, $file->subsystem, $file->subsystem_id));
        $file_obj->setPlayURL(wall_file_playurl_replacement($file->path, $file->filename, $file->subsystem, $file->subsystem_id));
        $link = MultimediaHelper::chooseMediaAhref($file_obj);
    }

    return "
    <tr>
    <td width='1'>" . icon($image, '') . "</td>
    <td class='text-start' style='font-size:12px'>$link</td></tr>";
}

function show_forum($type, $title, $resource_id, $ft_id) {
    global $is_editor_wall, $mentoring_program_id, $mentoring_program_code, $urlServer,$program_group_id;
    $title = q($title);
    if ($type == 'mentoring_forum') {
        $visibility = 1;
        $link = "<a href='{$urlServer}modules/mentoring/programs/group/forum_group.php?res_type=mentoring_forum&amp;forum_group_id=".getInDirectReference($ft_id)."'>";
        $forumlink = $link.q($title)."</a>";
        $imagelink = 'fa-comments';
    } else {
        $row = Database::get()->querySingle("SELECT forum_id FROM mentoring_forum_topic WHERE id = ?d", $ft_id);
        if ($row) {
            $visibility = 1;
            $forum_id = $row->forum_id;
            $link = "<a href='{$urlServer}modules/programs/group/view_topic.php?res_type=mentoring_topic&amp;group_id=".getInDirectReference($program_group_id)."&amp;topic_id=".getInDirectReference($ft_id)."&amp;forum_id=".getInDirectReference($forum_id)."'>";
            $forumlink = $link.q($title)."</a>";
            $imagelink = 'fa-comments';
        } else {
            if (!$is_editor_wall) {
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


function wall_file_playurl_replacement($path, $filename, $subsystem, $uid) {
    global $urlServer, $group_sql;

    $gid = '';

    if ($subsystem == MYDOCS) {
        $mentoring_program_code = '';
        $group_sql = "subsystem = $subsystem AND subsystem_id = $uid";
    } else {
        global $mentoring_program_code, $mentoring_program_id, $program_group_id;
        $gid = ",$program_group_id";
        $group_sql = "mentoring_program_id = $mentoring_program_id AND subsystem = $subsystem AND subsystem_id = $program_group_id";
    }

    return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/play.php?$mentoring_program_code$gid" .
            public_file_path($path, $filename), ENT_QUOTES);
}

function wall_file_url_replacement($path, $filename, $subsystem, $uid) {
    global $urlServer, $group_sql, $program_group_id;

    $gid = '';

    if ($subsystem == MYDOCS) {
        $mentoring_program_code = 'user';
        $gid = ",$uid";
        $group_sql = "subsystem = $subsystem AND subsystem_id = $uid";
    } else {
        global $mentoring_program_code, $mentoring_program_id, $program_group_id;
        $gid = ",$program_group_id";
        $group_sql = "mentoring_program_id = $mentoring_program_id AND subsystem = $subsystem AND subsystem_id = $program_group_id";
    }

    return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/file.php?$mentoring_program_code$gid" .
            public_file_path($path, $filename), ENT_QUOTES);
}


function generate_infinite_container_html($posts, $next_page) {
    global $posts_per_page, $urlServer, $langWallSharedPost, $langWallSharedVideo, $langWallUser, $langComments,
    $mentoring_program_code, $langMore, $is_editor_wall, $uid, $mentoring_program_id, $langModify, $langDelete, $head_content, $langWallPostDelConfirm,
    $langWallPinPost, $langWallUnPinPost, $program_group_id;

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
    $ret = '
            <div class="infinite-container">';

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
            $extvideo_embed = MentoringExtVideoUrlParser::get_embed_url($extvideo);
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

        

        if (allow_to_edit($id, $uid, $is_editor_wall)) {
            $post_actions = '<div class="action-btns float-end mt-2 d-flex gap-3">';
            $post_actions .= '<a class="link" href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;delete='.$id.'">
                              <span class="fa fa-times fa-lg text-danger float-end" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></span></a>';
            if ($is_editor_wall) { //add link for pin post
                $post_actions .= '<a href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;pin='.$id.'">';
                if ($pinned == 0) {
                    $post_actions .= '<span class="fa fa-thumb-tack fa-lg float-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
                } elseif ($pinned == 1) {
                    $post_actions .= '<span class="fafa-thumb-tack fa-lg text-danger float-end" data-bs-original-title="'.$langWallUnPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
                }
            }
            if (!$is_editor_wall) {
                if ($pinned == 1) {
                    $post_actions .= '<span class="fa fa-thumb-tack fa-lg loat-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
                }
            }
            $post_actions .= '<a href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;edit='.$id.'">
                              <span class="fa fa-edit text-primary fa-lg float-end" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></span></a>';
           
            $post_actions .= '</div>';
        } else {
            $post_actions = '<div class="action-btns float-end mt-2 d-flex gap-3">';
            if ($pinned == 1) {
                $post_actions .= '<span class="fa fa-thumb-tack fa-lg float-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
            }
         
            $post_actions .= '</div>';
        }

        $ret .= '
              <div class="infinite-item mb-3">
                
                  <div class="col-12">
                    <div class="panel panel-admin rounded-2">
                      <div class="panel-heading bg-white rounded-0">
                        <span class="panel-title text-uppercase MentoringHelp-text-panel-heading">
                            <a class="media-left p-0" href="'.$urlServer.'modules/mentoring/profile/user_profile.php?user_id='.getInDirectReference($user_id).'&amp;token='.$token.'">' .
                            profile_image($user_id, IMAGESIZE_SMALL, 'img-circle') . '
                            </a>' .
                            $post_actions . '
                        </span>
                      </div>

                      <div class="panel-body rounded-2 bg-white">
                        <p class="blackBlueText TextSemiBold">'.$datetime.'</p>
                        <small>'.$langWallUser.mentoring_display_user($user_id,$token).$shared.'</small>
                        <div class="margin-top-thin" style="padding:20px">' .
                          $extvideo_block . '
                        <div class="userContent control-label-notes">'.nl2br(standard_text_escape($content)).'</div>
                      </div>' .
                      show_resources($id) . '
                      <div class="col-12 d-flex justify-content-end align-items-center">
                         '.addComment($id,$token).'
                      </div>
                    </div>
                  </div>
                
              </div>
            </div>';
    }
    $ret .= '</div>';
    if (count($posts) == $posts_per_page) {
        $ret .= '<a class="infinite-more-link" href="loadMore.php?page='.$next_page.'"></a>';
    }

    return $ret;
}

function mentoring_display_user($user_id,$token){
    global $urlServer;

    $if_user_exist = Database::get()->querySingle("SELECT *FROM user WHERE id = ?d",$user_id);
    if($if_user_exist){
        $name = $if_user_exist->givenname;
        $surname = $if_user_exist->surname;

        $show_profile = "<a href='{$urlServer}modules/mentoring/profile/user_profile.php?user_id=".getInDirectReference($user_id)."&amp;token=".$token."'>$name $surname</a>";
        
    }else{
        $show_profile = "<span>(---)</span>";
    }
    
    return $show_profile;
}