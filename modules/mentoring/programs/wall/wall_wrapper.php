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

require_once 'modules/mentoring/programs/wall/wall_functions.php';
require_once 'modules/mentoring/programs/wall/insert_doc.php';
require_once 'modules/mentoring/programs/wall/insert_forum.php';
require_once 'modules/mentoring/mentoring_log.class.php';

function show_post_form() {
    global $head_content, $tool_content, $urlServer, $mentoring_program_id, $mentoring_program_code, $uid, $is_editor_wall,$is_member_common_group,
           $langVideo, $langDoc, $langMyDocs, $langPressMessage, $langWallExtVideo, $langWallExtVideoLink,
           $langLinks, $langExercises, $langWorks, $langChat, $langQuestionnaire, $langForum, $langSubmit, $program_group_id;

    if (allow_to_post($mentoring_program_id, $uid, $is_editor_wall)) {

        load_js('autosize');

        $content = Session::has('content')? Session::get('content'): '';
        $extvideo = Session::has('extvideo')? Session::get('extvideo'): '';


        if ($is_editor_wall or $is_member_common_group) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_docs" style="padding:10px">
                            <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs().'
                          </div>';
            $docs_li = '<li class="nav-item"><a id="nav_docs" class="nav-link" data-bs-toggle="tab" href="#docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }

        if (($is_editor_wall && get_config('mydocs_teacher_enable')) || (!$is_editor_wall && get_config('mydocs_student_enable'))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_mydocs" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs(NULL,'mydocs').'
                          </div>';
            $mydocs_li = '<li class="nav-item"><a id="nav_mydocs" class="nav-link" data-bs-toggle="tab" href="#mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }


        if ($is_editor_wall or $is_member_common_group) {
            $forums_div = '<div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_forums" style="padding:10px">
                              '.list_forums().'
                          </div>';
            $forums_li = '<li class="nav-item"><a id="nav_forums" class="nav-link" data-bs-toggle="tab" href="#forums_div">'.$langForum.'</a></li>';
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
            <div class="col-12 mb-3">
                <div class="form-wrapper form-edit rounded bg-white">
                    <form id="wall_form" method="post" action="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'" enctype="multipart/form-data">
                        <fieldset> 
                            <div class="form-group">
                                <textarea id="textr" onfocus="expand_form();" class="form-control rounded-2" rows="1" placeholder='.$langPressMessage.' name="message">'.$content.'</textarea>
                            </div>
                            <div id="resources_panel" class="panel panel-default collapse mt-3 rounded-2 border-0">
                                <div class="panel-body rounded-2">
                                    <ul class="nav nav-tabs mb-3">
                                        <li class="nav-item"><a id="nav_extvideo" class="nav-link active" data-bs-toggle="tab" href="#extvideo_video_div">'.$langWallExtVideo.'</a></li>
                                        '.$docs_li.'
                                        '.$mydocs_li.'
                                        '.$forums_li.'
                                    </ul>
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_extvideo" style="padding:10px">
                                            <label class="control-label-notes" for="extvideo_video">'.$langWallExtVideoLink.'</label>
                                            <input class="form-control rounded-2" type="url" name="extvideo" id="extvideo_video" value="'.$extvideo.'">
                                        </div>
                                        '.$docs_div.'
                                        '.$mydocs_div.'
                                        '.$forums_div.'
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3 d-flex justify-content-start align-items-center">'.
                            form_buttons(array(
                                array(
                                    'class' => 'btnSubmitWallPost',
                                    'text'  =>  $langSubmit,
                                    'name'  =>  'submit',
                                    'value' =>  $langSubmit
                                )
                            ))
                            .'</div>  
                        </fieldset>      
                    </form>
                </div>
            </div>';

        //auto-expand textarea while typing
        $tool_content .= "<script>autosize(document.querySelector('textarea'));</script>";
    }

    return $tool_content;
}

function show_wall_posts() {
    global $tool_content, $head_content, $mentoring_program_id, $langNoWallPostsMentoring, $langDownload, $langPrint, $langCancel, $langFullScreen, $langNewTab, $program_group_id;

    $posts_per_page = 10;

    //show wall posts
    $posts = Database::get()->queryArray("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND group_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $mentoring_program_id, $program_group_id, $posts_per_page);
    if (count($posts) == 0) {
        $tool_content .= '<div class="col-12 mt-4"><div class="alert alert-warning rounded-2"><i class="fa-solid fa-triangle-exclamation fa-lg"></i><span>'.$langNoWallPostsMentoring.'</span></div></div>';
    } else {
        $tool_content .= generate_infinite_container_html($posts, $posts_per_page, 2);

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

        load_js('screenfull/screenfull.min.js');
        $head_content .= "<script>
        $(function(){
            $('.fileModal').click(function (e)
            {
                e.preventDefault();
                var fileURL = $(this).attr('href');
                var downloadURL = $(this).prev('input').val();
                var fileTitle = $(this).attr('title');
                var buttons = {};
                if (downloadURL) {
                    buttons.download = {
                            label: '<i class=\"fa fa-download\"></i> $langDownload',
                            className: 'submitAdminBtn',
                            callback: function (d) {
                                window.location = downloadURL;
                            }
                    };
                }
                buttons.print = {
                            label: '<i class=\"fa fa-print\"></i> $langPrint',
                            className: 'submitAdminBtn',
                            callback: function (d) {
                                var iframe = document.getElementById('fileFrame');
                                iframe.contentWindow.print();
                            }
                        };
                if (screenfull.enabled) {
                    buttons.fullscreen = {
                        label: '<i class=\"fa fa-arrows-alt\"></i> $langFullScreen',
                        className: 'submitAdminBtn',
                        callback: function() {
                            screenfull.request(document.getElementById('fileFrame'));
                            return false;
                        }
                    };
                }
                buttons.newtab = {
                    label: '<i class=\"fa fa-plus\"></i> $langNewTab',
                    className: 'submitAdminBtn',
                    callback: function() {
                        window.open(fileURL);
                        return false;
                    }
                };
                buttons.cancel = {
                            label: '$langCancel',
                            className: 'cancelAdminBtn'
                        };
                bootbox.dialog({
                    size: 'large',
                    title: fileTitle,
                    message: '<div class=\"row\">'+
                                '<div class=\"col-sm-12\">'+
                                    '<div class=\"iframe-container\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\" style=\"width:100%; height:500px;\"></iframe></div>'+
                                '</div>'+
                            '</div>',
                    buttons: buttons
                });
            });
        });

        </script>";
    }

    return $tool_content;
}

function decide_wall_redirect() {
    global $mentoring_program_code,$program_group_id;

    if (strpos($_SERVER['HTTP_REFERER'], "mentoring_programs/".$mentoring_program_code)) {
        redirect_to_home_page("mentoring_programs/$mentoring_program_code/");
    } else {
        redirect_to_home_page("modules/mentoring/programs/wall/index.php?group_id=".getInDirectReference($program_group_id));
    }
}



function generate_single_post_html($post) {
    global $urlServer, $langWallSharedPost, $langWallSharedVideo, $langWallUser,
    $mentoring_program_code, $is_editor_wall, $uid, $mentoring_program_id, $langModify, $langDelete, $head_content, $langWallPostDelConfirm,
    $langWallPinPost, $langWallUnPinPost,$program_group_id;

    //commenting_add_js();

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
        $post_actions = '<div class="action-btns float-end mt-2 d-flex gap-3">';
        $post_actions .= '<a class="link" href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;delete='.$id.'">
                          <span class="fa fa-times fa-lg text-danger float-end" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></span></a>';
        if ($is_editor_wall) { //add link for pin post
            $post_actions .= '<a href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;pin='.$id.'">';
            if ($pinned == 0) {
                $post_actions .= '<span class="fa fa-thumb-tack fa-lg float-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
            } elseif ($pinned == 1) {
                $post_actions .= '<span class="fa fa-thumb-tack fa-lg text-danger float-end" data-bs-original-title="'.$langWallUnPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
            }
        }
        if (!$is_editor_wall) {
            if ($pinned == 1) {
                $post_actions .= '<span class="fa fa-thumb-tack fa-lg float-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
            }
        }
        $post_actions .= '<a href="'.$urlServer.'modules/mentoring/programs/wall/index.php?group_id='.getInDirectReference($program_group_id).'&amp;edit='.$id.'">
                          <span class="f fa-edit fa-lg float-end" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></span></a>';

       
        $post_actions .= '</div>';
    } else {
        $post_actions = '<div class="action-btns float-end mt-2 d-flex gap-3">';
        if ($pinned == 1) {
            $post_actions .= '<span class="fa fa-thumb-tack fa-lg float-end" data-bs-original-title="'.$langWallPinPost.'" title="" data-bs-toggle="tooltip"></span></a>';
        }
       
        $post_actions .= '</div>';
    }
    $ret = '
        <div class="col-12 mb-3">
            <div class="panel panel-default p-0">
                <div class="panel-heading p-1">
                    <a class="media-left p-0" href="'.$urlServer.'main/profile/display_profile.php?id='.$user_id.'&amp;token='.$token.'">
                        '. profile_image($user_id, IMAGESIZE_SMALL, 'img-circle') .'
                    </a>
                    '.$post_actions.'
                </div>
                <div class="panel-body bubble overflow-auto Borders">
                    <p class="blackBlueText TextSemiBold">'.$datetime.'</p>
                    <small>'.$langWallUser.display_user($user_id, false, false).$shared.'</small>

                    <div class="margin-top-thin" style="padding:20px">
                        '.$extvideo_block.'
                        <div class="userContent control-label-notes">'.nl2br(standard_text_escape($content)).'</div>
                    </div>
                    '.show_resources($id).'
                    
                </div>
            </div>
        </div>';
    return $ret;
}