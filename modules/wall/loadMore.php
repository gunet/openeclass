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

require_once '../../include/baseTheme.php';

$posts_per_page = 5;

if (visible_module(MODULE_ID_WALL)) {
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
        if ($page > 1) {//first page is shown in index.php
            $posts = Database::get()->queryArray("SELECT id, user_id, content, video_link, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY timestamp DESC LIMIT ?d,?d", $course_id, ($page-1)*$posts_per_page, $posts_per_page);
            if (count($posts) != 0) {
                $html = '<div class="infinite-container">';
                foreach ($posts as $post) {
                    $user_id = $post->user_id;
                    $id = $post->id;
                    $content = $post->content;
                    $pinned = $post->pinned;
                    $token = token_generate($user_id, true);
                    $datetime = nice_format($post->datetime, true);
                    if ($post->video_link == '') {
                        $shared = $langWallSharedPost;
                    } else {
                        $shared = $langWallSharedVideo;
                    }
            
                    $html .= '<div class="infinite-item">';
            
                    $html .= '<div class="row margin-right-thin margin-left-thin margin-top-thin">
                              <div class="col-sm-12">
                                  <div class="media">
                                      <a class="media-left" href="'.$urlServer.'main/profile/display_profile.php?id='.$user_id.'&amp;token='.$token.'">
                                        '. profile_image($user_id, IMAGESIZE_SMALL) .'
                                      </a>
                                      <div class="media-body bubble">
                                          <div class="label label-success media-heading">'.$datetime.'</div>
                                          <small>'.$langWallUser.display_user($user_id, false, false).$shared.'</small>
                                          <div class="margin-top-thin">
                                              '.standard_text_escape($content).'
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>';
            
                    $html .= '</div>';
                }
                $html .= '</div>';
                if (count($posts) == $posts_per_page) {
                    $html .= '<a class="infinite-more-link" href="loadMore.php?course='.$course_code.'&amp;page='.(++$page).'">'.$langMore.'</a>';
                }
                echo $html;
            }  
        }
    }
}
