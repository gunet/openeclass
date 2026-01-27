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

$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'wall';
require_once '../../include/baseTheme.php';
require_once 'modules/wall/wall_wrapper.php';

ModalBoxHelper::loadModalBox(false);

$head_content .= '<link rel="stylesheet" type="text/css" href="css/wall.css">';

load_js('waypoints-infinite');
load_js('screenfull/screenfull.min.js');

$head_content .= "<script>
            $(function() {
                var infinite = new Waypoint.Infinite({
                  element: $('.infinite-container')[0]
                })
                
                $('.coloboxframe').click(function() {
                    $('.colorboxframe').colorbox();
                })
    
                $('.colobox').click(function() {
                    $('.colorbox').colorbox();
                })
                
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
                                className: 'submitAdminBtn gap-1',
                                callback: function (d) {
                                    window.location = downloadURL;
                                }
                        };
                    }
                    buttons.print = {
                                label: '<i class=\"fa fa-print\"></i> $langPrint',
                                className: 'submitAdminBtn gap-1',
                                callback: function (d) {
                                    var iframe = document.getElementById('fileFrame');
                                    iframe.contentWindow.print();
                                }
                            };
                    if (screenfull.enabled) {
                        buttons.fullscreen = {
                            label: '<i class=\"fa fa-arrows-alt\"></i> $langFullScreen',
                            className: 'submitAdminBtn gap-1',
                            callback: function() {
                                screenfull.request(document.getElementById('fileFrame'));
                                return false;
                            }
                        };
                    }
                    buttons.newtab = {
                        label: '<i class=\"fa fa-plus\"></i> $langNewTab',
                        className: 'submitAdminBtn gap-1',
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
                                        '<div class=\"iframe-container\"><iframe title=\"'+fileTitle+'\" id=\"fileFrame\" src=\"'+fileURL+'\" style=\"width:100%; height:500px;\"></iframe></div>'+
                                    '</div>'+
                                '</div>',
                        buttons: buttons
                    });
                });
            });
        </script>";

$pageName = $langWall;

//handle submit
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (allow_to_post($course_id, $uid, $is_editor)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['extvideo'])) {

                $content = links_autodetection($_POST['message']);
                $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, timestamp) VALUES (?d,?d,?s,UNIX_TIMESTAMP())",
                        $course_id, $uid, $content)->lastInsertID;
                Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                    array('id' => $id,
                          'content' => $content));
                Session::flash('message',$langWallPostSaved);
                Session::flash('alert-class', 'alert-success');
            } else {
                if (ExtVideoUrlParser::validateUrl($_POST['extvideo']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('extvideo', $_POST['extvideo']);
                    Session::flash('message',$langWallExtVideoLinkNotValid);
                    Session::flash('alert-class', 'alert-warning');
                } else {
                    $content = links_autodetection($_POST['message']);
                    $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, extvideo, timestamp) VALUES (?d,?d,?s,?s, UNIX_TIMESTAMP())",
                            $course_id, $uid, $content, $_POST['extvideo'])->lastInsertID;
                    Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                        array('id' => $id,
                              'content' => $content,
                              'extvideo' => $_POST['extvideo']));

                    Session::flash('message',$langWallPostSaved);
                    Session::flash('alert-class', 'alert-success');
                }
            }
            if (isset($id)) { //check if wall resources need to get saved
                // multimedia content
                if ($is_editor || visible_module(MODULE_ID_VIDEO)) {
                    insert_video($id);
                }
                //save documents
                if ($is_editor || visible_module(MODULE_ID_DOCS)) {
                    insert_docs($id);
                }
                //save my documents
                if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
                    insert_docs($id,'mydocs');
                }
                //save links
                if ($is_editor || visible_module(MODULE_ID_LINKS)) {
                    insert_links($id);
                }
                //save exercises
                if ($is_editor || visible_module(MODULE_ID_EXERCISE)) {
                    insert_exercises($id);
                }
                //save assignments
                if ($is_editor || visible_module(MODULE_ID_ASSIGN)) {
                    insert_assignments($id);
                }
                //save chats
                if ($is_editor || visible_module(MODULE_ID_CHAT)) {
                    insert_chats($id);
                }
                //save polls
                if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE)) {
                    insert_polls($id);
                }
                //save forums
                if ($is_editor || visible_module(MODULE_ID_FORUM)) {
                    insert_forum($id);
                }
                echo ('hello');
            }
        } else {
            Session::flash('message',$langWallMessageEmpty);
            Session::flash('alert-class', 'alert-warning');
            if (!empty($_POST['extvideo'])) {
                Session::flash('extvideo', $_POST['extvideo']);
            }
        }

        decide_wall_redirect();
    }
} elseif (isset($_GET['delete'])) { //handle delete
    $id = intval($_GET['delete']);
    if (allow_to_edit($id, $uid, $is_editor)) {
        //delete abuse reports for this wall post first and log action
        $res = Database::get()->queryArray("SELECT * FROM abuse_report WHERE `rid` = ?d AND `rtype` = ?s", $id, 'wallpost');
        foreach ($res as $r) {
            Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
            array('id' => $r->id,
            'user_id' => $r->user_id,
            'reason' => $r->reason,
            'message' => $r->message,
            'rtype' => 'wallpost',
            'rid' => $id,
            'rcontent' => Database::get()->querySingle("SELECT content FROM wall_post WHERE id = ?d", $id)->content,
            'status' => $r->status
            ));
        }
        Database::get()->query("DELETE FROM abuse_report WHERE rid = ?d AND rtype = ?s", $id, 'wallpost');

        //delete comments and ratings
        Commenting::deleteComments('wallpost', $id);
        Rating::deleteRatings('wallpost', $id);

        $post = Database::get()->querySingle("SELECT content, extvideo FROM wall_post WHERE id = ?d", $id);
        $content = $post->content;
        $extvideo = $post->extvideo;

        Log::record($course_id, MODULE_ID_WALL, LOG_DELETE,
            array('id' => $id,
                  'content' => $content,
                  'extvideo' => $extvideo));

        Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);
        Database::get()->query("DELETE FROM wall_post WHERE id = ?d", $id);

        Session::flash('message',$langWallPostDeleted);
        Session::flash('alert-class', 'alert-success');
    }
    decide_wall_redirect();
} elseif (isset($_POST['edit_submit'])) { //handle edit form submit
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['extvideo'])) {
                $content = links_autodetection($_POST['message']);
                $extvideo = '';
                Database::get()->query("UPDATE wall_post SET content = ?s, extvideo = ?s WHERE id = ?d AND course_id = ?d",
                    $content, $extvideo, $id, $course_id);
                Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);

                Log::record($course_id, MODULE_ID_WALL, LOG_MODIFY,
                array('id' => $id,
                'content' => $content));

            } else {
                if (ExtVideoUrlParser::validateUrl($_POST['extvideo']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('extvideo', $_POST['extvideo']);
                    Session::flash('message',$langWallExtVideoLinkNotValid);
                    Session::flash('alert-class', 'alert-warniig');
                    redirect_to_home_page("modules/wall/index.php?course=$course_code&edit=$id");
                } else {
                    $content = links_autodetection($_POST['message']);
                    $extvideo = $_POST['extvideo'];
                    Database::get()->query("UPDATE wall_post SET content = ?s, extvideo = ?s WHERE id = ?d AND course_id = ?d",
                        $content, $extvideo, $id, $course_id);
                    Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);

                    Log::record($course_id, MODULE_ID_WALL, LOG_MODIFY,
                    array('id' => $id,
                    'content' => $content,
                    'extvideo' => $extvideo));

                }
            }

            //save multimedia content
            if ($is_editor || visible_module(MODULE_ID_VIDEO)) {
                insert_video($id);
            }
            //save documents
            if ($is_editor || visible_module(MODULE_ID_DOCS)) {
                insert_docs($id);
            }

            $post_author = Database::get()->querySingle("SELECT user_id FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id)->user_id;

            //save my documents
            if (($post_author == $uid) && (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) ) {
                insert_docs($id,'mydocs');
            }

            //save links
            if ($is_editor || visible_module(MODULE_ID_LINKS)) {
                insert_links($id);
            }

            //save exercises
            if ($is_editor || visible_module(MODULE_ID_EXERCISE)) {
                insert_exercises($id);
            }

            //save assignments
            if ($is_editor || visible_module(MODULE_ID_ASSIGN)) {
                insert_assignments($id);
            }

            //save chats
            if ($is_editor || visible_module(MODULE_ID_CHAT)) {
                insert_chats($id);
            }

            //save polls
            if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE)) {
                insert_polls($id);
            }

            //save forums
            if ($is_editor || visible_module(MODULE_ID_FORUM)) {
                insert_forum($id);
            }

            Session::flash('message',$langWallPostSaved);
            Session::flash('alert-class', 'alert-success');
            decide_wall_redirect();

        } else {
            Session::flash('message',$langWallMessageEmpty);
            Session::flash('alert-class', 'alert-warning');
            if (!empty($_POST['extvideo'])) {
                Session::flash('extvideo', $_POST['extvideo']);
                redirect_to_home_page("modules/wall/index.php?course=$course_code&edit=$id");
            }
        }
    }
} elseif (isset($_GET['pin'])) {
    $id = intval($_GET['pin']);
    if ($is_editor && allow_to_edit($id, $uid, $is_editor)) {
        Database::get()->query("UPDATE wall_post SET pinned = !pinned WHERE id = ?d", $id);
        Session::flash('message',$langWallGeneralSuccess);
        Session::flash('alert-class', 'alert-success');
        decide_wall_redirect();
    }
}

if (isset($_GET['showPost'])) { //show comments case
    $id = intval($_GET['showPost']);
    $post = Database::get()->querySingle("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($post) {
        $tool_content .= action_bar(array(
                  array('title' => $langBack,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
        ),false);
        $tool_content .= generate_single_post_html($post);
    } else {
        decide_wall_redirect();
    }
} elseif (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor)) {
        $tool_content .= action_bar(array(
                             array('title' => $langBack,
                                   'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                   'icon' => 'fa-reply',
                                   'level' => 'primary')
                          ),false);

        $post = Database::get()->querySingle("SELECT content, extvideo FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $content = Session::has('content')? Session::get('content') : $post->content;
        $extvideo = Session::has('extvideo')? Session::get('extvideo') : $post->extvideo;

        if ($is_editor || visible_module(MODULE_ID_VIDEO)) {
            $video_div = '<div class="form-group tab-pane fade" id="videos_div" role="tabpanel" aria-labelledby="nav_edit_video" style="padding:10px">
                              '.list_videos($id).'
                          </div>';
            $video_li = '<li class="nav-item"><a id="nav_edit_video" class="nav-link" data-bs-toggle="tab" href="#videos_div" role="tab" aria-controls="videos_div">'.$langVideo.'</a></li>';
        } else {
            $video_div = '';
            $video_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_DOCS)) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_edit_docs" style="padding:10px">
                              <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs($id, NULL, TRUE).'
                          </div>';
            $docs_li = '<li class="nav-item"><a id="nav_edit_docs" class="nav-link" data-bs-toggle="tab" href="#docs_div" role="tab" aria-controls="docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }

        $post_author = Database::get()->querySingle("SELECT user_id FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id)->user_id;

        if (($post_author == $uid) && (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable')))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_edit_mydocs" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs($id,'mydocs', TRUE).'
                          </div>';
            $mydocs_li = '<li class="nav-item"><a id="nav_edit_mydocs" class="nav-link" data-bs-toggle="tab" href="#mydocs_div" role="tab" aria-controls="mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_LINKS)) {
            $links_div = '<div class="form-group tab-pane fade" id="links_div" role="tabpanel" aria-labelledby="nav_edit_links" style="padding:10px">
                              '.list_links($id).'
                          </div>';
            $links_li = '<li class="nav-item"><a id="nav_edit_links" class="nav-link" data-bs-toggle="tab" href="#links_div" role="tab" aria-controls="links_div">'.$langLinks.'</a></li>';
        } else {
            $links_div = '';
            $links_li = '';
        }

        if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
            if ($is_editor || visible_module(MODULE_ID_EXERCISE)) {
                $exercises_div = '<div class="form-group tab-pane fade" id="exercises_div" role="tabpanel" aria-labelledby="nav_edit_exercises" style="padding:10px">
                                '.list_exercises($id).'
                            </div>';
                $exercises_li = '<li class="nav-item"><a id="nav_edit_exercises" class="nav-link" data-bs-toggle="tab" href="#exercises_div" role="tab" aria-controls="exercises_div">'.$langExercises.'</a></li>';
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
                $assignments_div = '<div class="form-group tab-pane fade" id="assignments_div" role="tabpanel" aria-labelledby="nav_edit_assigments" style="padding:10px">
                                '.list_assignments($id).'
                            </div>';
                $assignments_li = '<li class="nav-item"><a id="nav_edit_assigments" class="nav-link" data-bs-toggle="tab" href="#assignments_div" role="tab" aria-controls="assignments_div">'.$langWorks.'</a></li>';
            } else {
                $assignments_div = '';
                $assignments_li = '';
            }
        }else{
            $assignments_div = '';
            $assignments_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_CHAT)) {
            $chats_div = '<div class="form-group tab-pane fade" id="chats_div" role="tabpanel" aria-labelledby="nav_edit_chats" style="padding:10px">
                              '.list_chats($id).'
                          </div>';
            $chats_li = '<li class="nav-item"><a id="nav_edit_chats" class="nav-link" data-bs-toggle="tab" href="#chats_div" role="tab" aria-controls="chats_div">'.$langChat.'</a></li>';
        } else {
            $chats_div = '';
            $chats_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE)) {
            $polls_div = '<div class="form-group tab-pane fade" id="polls_div" role="tabpanel" aria-labelledby="nav_edit_polls" style="padding:10px">
                              '.list_polls($id).'
                          </div>';
            $polls_li = '<li class="nav-item"><a id="nav_edit_polls" class="nav-link" data-bs-toggle="tab" href="#polls_div" role="tab" aria-controls="polls_div">'.$langQuestionnaire.'</a></li>';
        } else {
            $polls_div = '';
            $polls_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_FORUM)) {
            $forums_div = '<div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_edit_forums" style="padding:10px">
                              '.list_forums($id).'
                          </div>';
            $forums_li = '<li class="nav-item"><a id="nav_edit_forums" class="nav-link" data-bs-toggle="tab" href="#forums_div" role="tab" aria-controls="forums_div">'.$langForum.'</a></li>';
        } else {
            $forums_div = '';
            $forums_li = '';
        }

        $tool_content .= '<div class="row">
            <div class="col-12">
                <div class="form-wrapper form-edit py-lg-4 px-lg-5 py-0 px-0 wallWrapper ">
                    <form id="wall_form" method="post" action="" enctype="multipart/form-data">
                        <fieldset>
                            <legend class="mb-0" aria-label="'.$langForm.'"></legend>
                            <div class="form-group">
                                <label for="message_input" class="control-label-notes">'.$langMessage.'</label>
                                <textarea class="form-control" rows="6" name="message" id="message_input">'.strip_tags($content).'</textarea>
                            </div>
                            <div class="panel panel-default mt-3 border-0">
                                <div class="panel-body border-0">
                                    <ul class="nav nav-tabs border-0" role="tablist">
                                        <li class="nav-item"><a id="nav_edit_extvideo" class="nav-link active" data-bs-toggle="tab" href="#extvideo_video_div" role="tab" aria-controls="extvideo_video_div">'.$langWallExtVideo.'</a></li>
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
                                        <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_edit_extvideo" style="padding:10px">
                                            <label for="extvideo_video">'.$langWallExtVideoLink.'</label>
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
                            <div class="form-group mt-3">'.
                            form_buttons(array(
                                array(
                                    'class' => 'submitAdminBtn',
                                    'text'  =>  $langSubmit,
                                    'name'  =>  'edit_submit',
                                    'value' =>  $langSubmit
                                )
                            ))
                        .'</div>
                        </fieldset>
                        ' . generate_csrf_token_form_field() . '
                    </form>
                </div>
            </div>
        </div>';
    } else {
        decide_wall_redirect();
    }
} else {
    //show post form
    show_post_form();
    //show wall posts
    show_wall_posts();
}

draw($tool_content, 2, null, $head_content);
