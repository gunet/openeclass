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

function allow_to_post($course_id, $user_id, $is_editor) {
    if ($is_editor) {
        return true;
    }
    
    $sql = "SELECT COUNT(`user_id`) as c FROM `course_user` WHERE `course_id` = ?d AND `user_id` = ?d";
    $result = Database::get()->querySingle($sql, $course_id, $user_id);
    if ($result->c > 0) {//user is course member
        return true;
    } else {//user is not course member
        return false;
    }
}

function allow_to_edit($post_id, $user_id, $is_editor) {
    if ($is_editor) {
        global $course_id;
        $sql = "SELECT COUNT(`id`) as c FROM `wall_post` WHERE `id` = ?d AND `course_id` = ?d";
        $result = Database::get()->querySingle($sql, $post_id, $course_id);
        if ($result->c > 0) {
            return true;
        }
    }
    
    $sql = "SELECT COUNT(`user_id`) as c FROM `wall_post` WHERE `id` = ?d AND `user_id` = ?d";
    $result = Database::get()->querySingle($sql, $post_id, $user_id);
    if ($result->c > 0) {
        return true;
    } else {
        return false;
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
    
            // Print the hyperlink.
            $ret_text .= sprintf('<a href="%s">%s</a>', htmlspecialchars($completeUrl), htmlspecialchars("$domain$port$path"));
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

function validate_youtube_link($video_url) {
    
    if (strrpos($video_url, 'v=', -1) === FALSE) {
        return false;
    }
    
    if (stristr($video_url, 'www.youtube.com/') === FALSE) {
        return false;
    }
    
    return true;
}

function generate_infinite_container_html($posts, $next_page) {
    global $posts_per_page, $urlServer, $langWallSharedPost, $langWallSharedVideo, $langWallUser,
    $course_code, $langMore, $is_editor, $uid, $course_id, $langModify, $langDelete, $head_content, $langWallPostDelConfirm;
    
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
        $datetime = nice_format($post->datetime, true);
        $video_link = $post->video_link;
        if ($video_link == '') {
            $shared = $langWallSharedPost;
            $video_block = '';
        } else {
            $shared = $langWallSharedVideo;
            $pos_v = strrpos ($video_link, 'v=', - 1);
            $video_link = 'http://www.youtube.com/embed/'.mb_substr($video_link, $pos_v+2);
            $video_block = '<div class="video_status">
                               <iframe  scrolling="no" width="445" height="250" src="'.$video_link.'" frameborder="0" allowfullscreen></iframe>
                            </div>';
        }
        
        $rating = new Rating('thumbs_up', 'wallpost', $id);
        $rating_content = $rating->put($is_editor, $uid, $course_id);
        
        $comm = new Commenting('wallpost', $id);
        $comm_content = $comm->put($course_code, $is_editor, $uid, true);
        
        if (allow_to_edit($id, $uid, $is_editor)) {
            $post_actions = '<div class="edit_delete"><a href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;edit='.$id.'">
                    '.icon('fa-edit', $langModify).'</a><a class="link" href="'.$urlServer.'modules/wall/index.php?course='.$course_code.'&amp;delete='.$id.'">
                    '.icon('fa-times', $langDelete).'</a></div>';
        } else {
            $post_actions = '';
        }
        
        $ret .= '<div class="infinite-item">';
    
        $ret .= '<div class="row margin-right-thin margin-left-thin margin-top-thin">
                              <div class="col-sm-12">
                                  <div class="media">
                                      <a class="media-left" href="'.$urlServer.'main/profile/display_profile.php?id='.$user_id.'&amp;token='.$token.'">
                                        '. profile_image($user_id, IMAGESIZE_SMALL) .'
                                      </a>
                                      <div class="media-body bubble">
                                          <div class="label label-success media-heading">'.$datetime.'</div>
                                          <small>'.$langWallUser.display_user($user_id, false, false).$shared.'</small>
                                          '.$post_actions.'
                                          <div class="margin-top-thin">
                                              '.$video_block.'
                                              <div class="userContent">'.nl2br(q($content)).'</div>
                                          </div>
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
        $ret .= '<a class="infinite-more-link" href="loadMore.php?course='.$course_code.'&amp;page='.$next_page.'"></a>';
    }

    return $ret;
}

