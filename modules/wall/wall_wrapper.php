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

require_once 'modules/wall/wall_functions.php';
require_once 'modules/wall/ExtVideoUrlParser.class.php';
require_once 'modules/wall/insert_video.php';
require_once 'modules/wall/insert_doc.php';
require_once 'modules/wall/insert_link.php';
require_once 'modules/wall/insert_exercise.php';
require_once 'modules/wall/insert_work.php';
require_once 'modules/wall/insert_chat.php';
require_once 'modules/wall/insert_poll.php';
require_once 'modules/wall/insert_forum.php';
require_once 'include/log.class.php';

function show_post_form(): void
{
    global $head_content, $tool_content, $urlServer, $course_id, $course_code, $uid, $is_editor,
           $langVideo, $langDoc, $langMyDocs, $langWallExtVideo, $langWallExtVideoLink, $langTypeOutMessage,
           $langLinks, $langExercises, $langWorks, $langChat, $langQuestionnaire, $langForum, $langSubmit, $langWall,
           $langOfCourse, $is_collaborative_course, $langForm;

    if (allow_to_post($course_id, $uid, $is_editor)) {

        load_js('autosize');

        $content = Session::has('content')? Session::get('content'): '';
        $extvideo = Session::has('extvideo')? Session::get('extvideo'): '';

        if ($is_editor || visible_module(MODULE_ID_VIDEO)) {
            $video_div = '<div class="form-group tab-pane fade" id="videos_div" role="tabpanel" aria-labelledby="nav_video" style="padding:10px">
                              '.list_videos().'
                          </div>';
            $video_li = '<li class="nav-item"><a id="nav_video" class="nav-link" data-bs-toggle="tab" href="#videos_div" role="tab" aria-controls="videos_div" tabindex="-1">'.$langVideo.'</a></li>';
        } else {
            $video_div = '';
            $video_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_DOCS)) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_docs" style="padding:10px">
                            <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs().'
                          </div>';
            $docs_li = '<li class="nav-item"><a id="nav_docs" class="nav-link" data-bs-toggle="tab" href="#docs_div" role="tab" aria-controls="docs_div" tabindex="-1">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }

        if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_mydocs" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs(NULL,'mydocs').'
                          </div>';
            $mydocs_li = '<li class="nav-item"><a id="nav_mydocs" class="nav-link" data-bs-toggle="tab" href="#mydocs_div" role="tab" aria-controls="mydocs_div" tabindex="-1">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_LINKS)) {
            $links_div = '<div class="form-group tab-pane fade" id="links_div" role="tabpanel" aria-labelledby="nav_links" style="padding:10px">
                              '.list_links().'
                          </div>';
            $links_li = '<li class="nav-item"><a id="nav_links" class="nav-link" data-bs-toggle="tab" href="#links_div" role="tab" aria-controls="links_div" tabindex="-1">'.$langLinks.'</a></li>';
        } else {
            $links_div = '';
            $links_li = '';
        }

        if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
            if ($is_editor || visible_module(MODULE_ID_EXERCISE)) {
                $exercises_div = '<div class="form-group tab-pane fade" id="exercises_div" role="tabpanel" aria-labelledby="nav_exercises" style="padding:10px">
                                '.list_exercises().'
                            </div>';
                $exercises_li = '<li class="nav-item"><a id="nav_exercises" class="nav-link" data-bs-toggle="tab" href="#exercises_div" role="tab" aria-controls="exercises_div" tabindex="-1">'.$langExercises.'</a></li>';
            } else {
                $exercises_div = '';
                $exercises_li = '';
            }
        }else{
            $exercises_div = '';
            $exercises_li = '';
        }

        if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
            if ($is_editor || visible_module(MODULE_ID_ASSIGN)) {
                $assignments_div = '<div class="form-group tab-pane fade" id="assignments_div" role="tabpanel" aria-labelledby="nav_assigments" style="padding:10px">
                                '.list_assignments().'
                            </div>';
                $assignments_li = '<li class="nav-item"><a id="nav_assigments" class="nav-link" data-bs-toggle="tab" href="#assignments_div" role="tab" aria-controls="assignments_div" tabindex="-1">'.$langWorks.'</a></li>';
            } else {
                $assignments_div = '';
                $assignments_li = '';
            }
        }else{
            $assignments_div = '';
            $assignments_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_CHAT)) {
            $chats_div = '<div class="form-group tab-pane fade" id="chats_div" role="tabpanel" aria-labelledby="nav_chats" style="padding:10px">
                              '.list_chats().'
                          </div>';
            $chats_li = '<li class="nav-item"><a id="nav_chats" class="nav-link" data-bs-toggle="tab" href="#chats_div" role="tab" aria-controls="chats_div" tabindex="-1">'.$langChat.'</a></li>';
        } else {
            $chats_div = '';
            $chats_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE)) {
            $polls_div = '<div class="form-group tab-pane fade" id="polls_div" role="tabpanel" aria-labelledby="nav_polls" style="padding:10px">
                              '.list_polls().'
                          </div>';
            $polls_li = '<li class="nav-item"><a id="nav_polls" class="nav-link" data-bs-toggle="tab" href="#polls_div" role="tab" aria-controls="polls_div" tabindex="-1">'.$langQuestionnaire.'</a></li>';
        } else {
            $polls_div = '';
            $polls_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_FORUM)) {
            $forums_div = '<div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_forums" style="padding:10px">
                              '.list_forums().'
                          </div>';
            $forums_li = '<li class="nav-item"><a id="nav_forums" class="nav-link" data-bs-toggle="tab" href="#forums_div" role="tab" aria-controls="forums_div" tabindex="-1">'.$langForum.'</a></li>';
        } else {
            $forums_div = '';
            $forums_li = '';
        }

        $head_content .= '<script>
                              function expand_form() {
                                  $("#resources_panel").collapse(\'show\');
                              }
                          </script>';

        $tool_content .= '
            <div class="col-12 mt-3">
                <div class="card panelCard px-lg-4 py-lg-3 wallWrapper">
                    <div class="card-header border-0">
                        <h3>'.$langWall.'&nbsp;'.$langOfCourse.'</h3>
                    </div>
                    <div class="card-body">
                        <form id="wall_form" method="post" action="'.$urlServer.'modules/wall/index.php?course='.$course_code.'" enctype="multipart/form-data">
                            <fieldset> 
                                <legend class="mb-0" aria-label="'.$langForm.'"></legend>
                                <div class="form-group">
                                    <textarea aria-label="'.$langTypeOutMessage.'" style="min-height:100px;" id="textr" onfocus="expand_form();" class="form-control" placeholder="'.$langTypeOutMessage.'" rows="1" name="message" id="message_input">'.$content.'</textarea>
                                </div>
                                <div id="resources_panel" class="card panelCard collapse mt-3 border-0">
                                    <div class="card-body border-0">
                                        <ul class="nav nav-tabs border-0" role="tablist">
                                            <li class="nav-item"><a id="nav_extvideo" class="nav-link active" data-bs-toggle="tab" href="#extvideo_video_div" role="tab" aria-controls="extvideo_video_div" tabindex="0">'.$langWallExtVideo.'</a></li>
                                            '.$video_li.'
                                            '.$docs_li.'
                                            '.$mydocs_li.'
                                            '.$links_li.'
                                            '.$exercises_li.'
                                            '.$assignments_li.'
                                            '.$chats_li.'
                                            '.$polls_li.'
                                            '.$forums_li.'
                                        </ul>
                                        <div class="tab-content mt-4">
                                            <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_extvideo" style="padding:10px">
                                                <label for="extvideo_video" class="mb-1 TextBold">'.$langWallExtVideoLink.'</label>
                                                <input class="form-control" type="url" name="extvideo" id="extvideo_video" value="'.$extvideo.'">
                                            </div>
                                            '.$video_div.'
                                            '.$docs_div.'
                                            '.$mydocs_div.'
                                            '.$links_div.'
                                            '.$exercises_div.'
                                            '.$assignments_div.'
                                            '.$chats_div.'
                                            '.$polls_div.'
                                            '.$forums_div.'
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-5"><div class="col-12 d-flex justify-content-center aling-items-center">'.
                                form_buttons(array(
                                    array(
                                        'class' => 'btn submitAdminBtn',
                                        'text'  =>  $langSubmit,
                                        'name'  =>  'submit',
                                        'value' =>  $langSubmit
                                    )
                                ))
                                .'</div></div>  
                            </fieldset>
                            ' . generate_csrf_token_form_field() . '
                        </form>
                    </div>
                </div>
            </div>';

        //auto-expand textarea while typing
        $tool_content .= "<script>autosize(document.querySelector('textarea'));</script>";

        // Accessibility
        $head_content .= "
        <script type='text/javascript'>
            $(document).ready(function(){
                const tabs = document.querySelectorAll('.nav-link');
                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => {
                            if (t.getAttribute('aria-selected') === 'true') {
                                t.setAttribute('tabindex', '0');
                            } else {
                                t.setAttribute('tabindex', '-1');
                            }
                            
                        });
                    });
                });
            });
        </script>
        ";
    }
}

function show_wall_posts() {
    global $tool_content, $course_id, $langNoWallPosts;

    $posts_per_page = 10;

    //show wall posts
    $posts = Database::get()->queryArray("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $course_id, $posts_per_page);
    if (count($posts) == 0) {
        $tool_content .= '<div class="col-12 mt-3">
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                <span>'.$langNoWallPosts.'</span>
                            </div>
                        </div>';
    } else {
        $tool_content .= generate_infinite_container_html($posts, $posts_per_page, 2);
    }
}

function decide_wall_redirect() {
    global $course_code;

    if (strpos($_SERVER['HTTP_REFERER'], "courses/".$course_code)) {
        redirect_to_home_page("courses/$course_code/");
    } else {
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
}
