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

require_once 'modules/wall/wall_functions.php';
require_once 'modules/wall/ExtVideoUrlParser.class.php';
require_once 'modules/wall/insert_video.php';
require_once 'modules/wall/insert_doc.php';
require_once 'modules/wall/insert_link.php';
require_once 'include/log.class.php';

function show_post_form() {
    global $head_content, $tool_content, $urlServer, $course_id, $course_code, $uid, $is_editor,
           $langVideo, $langDoc, $langMyDocs, $langMessage, $langWallExtVideo, $langWallExtVideoLink,
           $langLinks, $langSubmit;

    if (allow_to_post($course_id, $uid, $is_editor)) {

        load_js('autosize');

        $content = Session::has('content')? Session::get('content'): '';
        $extvideo = Session::has('extvideo')? Session::get('extvideo'): '';

        if (visible_module(MODULE_ID_VIDEO)) {
            $video_div = '<div class="form-group tab-pane fade" id="videos_div" style="padding:10px">
                              '.list_videos().'
                          </div>';
            $video_li = '<li><a data-toggle="tab" href="#videos_div">'.$langVideo.'</a></li>';
        } else {
            $video_div = '';
            $video_li = '';
        }

        if (visible_module(MODULE_ID_DOCS)) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" style="padding:10px">
                            <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs().'
                          </div>';
            $docs_li = '<li><a data-toggle="tab" href="#docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }

        if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs(NULL,'mydocs').'
                          </div>';
            $mydocs_li = '<li><a data-toggle="tab" href="#mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }

        if (visible_module(MODULE_ID_LINKS)) {
            $links_div = '<div class="form-group tab-pane fade" id="links_div" style="padding:10px">
                              '.list_links().'
                          </div>';
            $links_li = '<li><a data-toggle="tab" href="#links_div">'.$langLinks.'</a></li>';
        } else {
            $links_div = '';
            $links_li = '';
        }

        $head_content .= '<script>
                              function expand_form() {
                                  $("#resources_panel").collapse(\'show\');
                              }
                          </script>';

        $tool_content .= '<div class="row">
            <div class="col-sm-12">
                <div class="form-wrapper">
                    <form id="wall_form" method="post" action="'.$urlServer.'modules/wall/?course='.$course_code.'" enctype="multipart/form-data">
                        <fieldset> 
                            <div class="form-group">
                                <label for="message_input">'.$langMessage.'</label>
                                <textarea id="textr" onfocus="expand_form();" class="form-control" rows="1" name="message" id="message_input">'.$content.'</textarea>
                            </div>
                            <div id="resources_panel" class="panel panel-default collapse">
                                <div class="panel-body">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a data-toggle="tab" href="#extvideo_video_div">'.$langWallExtVideo.'</a></li>
                                        '.$video_li.'
                                        '.$docs_li.'
                                        '.$mydocs_li.'
                                        '.$links_li.'
                                    </ul>
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade in active" id="extvideo_video_div" style="padding:10px">
                                            <label for="extvideo_video">'.$langWallExtVideoLink.'</label>
                                            <input class="form-control" type="url" name="extvideo" id="extvideo_video" value="'.$extvideo.'">
                                        </div>
                                        '.$video_div.'
                                        '.$docs_div.'
                                        '.$mydocs_div.'
                                        '.$links_div.'
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">'.
            form_buttons(array(
                array(
                    'text'  =>  $langSubmit,
                    'name'  =>  'submit',
                    'value' =>  $langSubmit
                )
            ))
            .'</div>        
                    </form>
                </div>
            </div>
        </div>';

        //auto-expand textarea while typing
        $tool_content .= "<script>autosize(document.querySelector('textarea'));</script>";
    }
}

function show_wall_posts() {
    global $tool_content, $course_id, $langNoWallPosts;

    $posts_per_page = 10;

    //show wall posts
    $posts = Database::get()->queryArray("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $course_id, $posts_per_page);
    if (count($posts) == 0) {
        $tool_content .= '<div class="alert alert-warning">'.$langNoWallPosts.'</div>';
    } else {
        $tool_content .= generate_infinite_container_html($posts, 2);

        $tool_content .= '<script>
                              var infinite = new Waypoint.Infinite({
                                  element: $(".infinite-container")[0]
                              })
                          </script>';

        $tool_content .= "<script>
                            $('body').on('click', '.colorboxframe', function() {
                              $('.colorboxframe').colorbox();
                            });
                            $('body').on('click', '.colorbox', function() {
                              $('.colorbox').colorbox();
                            });
                          </script>";
    }
}

function decide_wall_redirect() {
    global $course_code;

    if (strpos($_SERVER['HTTP_REFERER'], "courses/".$course_code)) {
        redirect_to_home_page("courses/$course_code");
    } else {
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
}