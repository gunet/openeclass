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
require_once 'modules/wall/wall_functions.php';
require_once 'insert_video.php';
require_once 'insert_doc.php';
require_once 'include/log.php';

ModalBoxHelper::loadModalBox(false);

$head_content .= '<link rel="stylesheet" type="text/css" href="css/wall.css">';

load_js('waypoints-infinite');

$posts_per_page = 10;

$pageName = $langWall;

//handle submit
if (isset($_POST['submit'])) {
    if (allow_to_post($course_id, $uid, $is_editor)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['youtube'])) {
                $content = links_autodetection($_POST['message']);
                $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, timestamp) VALUES (?d,?d,?s,UNIX_TIMESTAMP())",
                        $course_id, $uid, $content)->lastInsertID;
                Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                    array('id' => $id,
                          'content' => $content));
                Session::Messages($langWallPostSaved, 'alert-success');
            } else {
                if (validate_youtube_link($_POST['youtube']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('youtube', $_POST['youtube']);
                    Session::Messages($langWallYoutubeVideoLinkNotValid);
                } else {
                    $content = links_autodetection($_POST['message']);
                    $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, youtube, timestamp) VALUES (?d,?d,?s,?s, UNIX_TIMESTAMP())",
                            $course_id, $uid, $content, $_POST['youtube'])->lastInsertID;
                    Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                        array('id' => $id,
                              'content' => $content,
                              'youtube' => $_POST['youtube']));
                    Session::Messages($langWallPostSaved, 'alert-success');
                }
            }
            if (isset($id)) { //check if wall resources need to get saved
                //save multimedia content
                if (visible_module(MODULE_ID_VIDEO)) {
                    insert_video($id);
                }
                //save documents
                if (visible_module(MODULE_ID_DOCS)) {
                    insert_docs($id);
                }
                //save my documents
                if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
                    insert_docs($id,'mydocs');
                }
            }
        } else {
            Session::Messages($langWallMessageEmpty);
            if (!empty($_POST['youtube'])) {
                Session::flash('youtube', $_POST['youtube']);
            }
        }
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
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
        
        $post = Database::get()->querySingle("SELECT content, youtube FROM wall_post WHERE id = ?d", $id);
        $content = $post->content;
        $youtube = $post->youtube;
        
        Log::record($course_id, MODULE_ID_WALL, LOG_DELETE, 
            array('id' => $id,
                  'content' => $content,
                  'youtube' => $youtube));
        
        Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);
        Database::get()->query("DELETE FROM wall_post WHERE id = ?d", $id);
        Session::Messages($langWallPostDeleted, 'alert-success');
    }
    redirect_to_home_page("modules/wall/index.php?course=$course_code");
} elseif (isset($_POST['edit_submit'])) { //handle edit form submit
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['youtube'])) {
                $content = links_autodetection($_POST['message']);
                $youtube = '';
                Database::get()->query("UPDATE wall_post SET content = ?s, youtube = ?s WHERE id = ?d AND course_id = ?d",
                    $content, $youtube, $id, $course_id);
                Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);
                
                Log::record($course_id, MODULE_ID_WALL, LOG_MODIFY,
                array('id' => $id,
                'content' => $content));
                
            } else {
                if (validate_youtube_link($_POST['youtube']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('youtube', $_POST['youtube']);
                    Session::Messages($langWallYoutubeVideoLinkNotValid);
                    redirect_to_home_page("modules/wall/index.php?course=$course_code&edit=$id");
                } else {
                    $content = links_autodetection($_POST['message']);
                    $youtube = $_POST['youtube'];
                    Database::get()->query("UPDATE wall_post SET content = ?s, youtube = ?s WHERE id = ?d AND course_id = ?d",
                        $content, $youtube, $id, $course_id);
                    Database::get()->query("DELETE FROM wall_post_resources WHERE post_id = ?d", $id);
                    
                    Log::record($course_id, MODULE_ID_WALL, LOG_MODIFY,
                    array('id' => $id,
                    'content' => $content,
                    'youtube' => $youtube));
                    
                }
            }
            
            //save multimedia content
            if (visible_module(MODULE_ID_VIDEO)) {
                insert_video($id);
            }
            //save documents
            if (visible_module(MODULE_ID_DOCS)) {
                insert_docs($id);
            }
            //save my documents
            if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
                insert_docs($id,'mydocs');
            }
            
            Session::Messages($langWallPostSaved, 'alert-success');
            redirect_to_home_page("modules/wall/index.php?course=$course_code");
        } else {
            Session::Messages($langWallMessageEmpty);
            if (!empty($_POST['youtube'])) {
                Session::flash('youtube', $_POST['youtube']);
                redirect_to_home_page("modules/wall/index.php?course=$course_code&edit=$id");
            }
        }
    }
} elseif (isset($_GET['pin'])) {
    $id = intval($_GET['pin']);
    if ($is_editor && allow_to_edit($id, $uid, $is_editor)) {
        Database::get()->query("UPDATE wall_post SET pinned = !pinned WHERE id = ?d", $id);
        Session::Messages($langWallGeneralSuccess, 'alert-success');
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
}

if (isset($_GET['showPost'])) { //show comments case
    $id = intval($_GET['showPost']);
    $post = Database::get()->querySingle("SELECT id, user_id, content, youtube, FROM_UNIXTIME(timestamp) as datetime, pinned FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($post) {
        $tool_content .= action_bar(array(
                  array('title' => $langBack,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')
        ),false);
        $tool_content .= generate_single_post_html($post);
    } else {
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
} elseif (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor)) {
        $tool_content .= action_bar(array(
                             array('title' => $langBack,
                                   'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                   'icon' => 'fa-reply',
                                   'level' => 'primary-label')
                          ),false);
        
        $post = Database::get()->querySingle("SELECT content, youtube FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $content = Session::has('content')? Session::get('content') : $post->content;
        $youtube = Session::has('youtube')? Session::get('youtube') : $post->youtube;
        
        if (visible_module(MODULE_ID_VIDEO)) {
            $video_div = '<div class="form-group tab-pane fade" id="videos_div" style="padding:10px">
                              '.list_videos($id).'
                          </div>';
            $video_li = '<li><a data-toggle="tab" href="#videos_div">'.$langVideo.'</a></li>';
        } else {
            $video_div = '';
            $video_li = '';
        }
        
        if (visible_module(MODULE_ID_DOCS)) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" style="padding:10px">
                              <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs($id).'
                          </div>';
            $docs_li = '<li><a data-toggle="tab" href="#docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }
        
        if (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable'))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs($id,'mydocs').'
                          </div>';
            $mydocs_li = '<li><a data-toggle="tab" href="#mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }
        
        $tool_content .= '<div class="row">
            <div class="col-sm-12">
                <div class="form-wrapper">
                    <form id="wall_form" method="post" action="" enctype="multipart/form-data">
                        <fieldset>
                            <div class="form-group">
                                <label for="message_input">'.$langMessage.'</label>
                                <textarea class="form-control" rows="6" name="message" id="message_input">'.strip_tags($content).'</textarea>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a data-toggle="tab" href="#youtube_video_div">'.$langWallYoutubeVideo.'</a></li>
                                        '.$video_li.'
                                        '.$docs_li.'
                                        '.$mydocs_li.'        
                                    </ul>
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade in active" id="youtube_video_div" style="padding:10px">
                                            <label for="youtube_video">'.$langWallYoutubeVideoLink.'</label>
                                            <input class="form-control" type="url" name="youtube" id="youtube_video" value="'.$youtube.'">
                                        </div>
                                        '.$video_div.'
                                        '.$docs_div.'
                                        '.$mydocs_div.'
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">'.
                            form_buttons(array(
                                array(
                                    'text'  =>  $langSubmit,
                                    'name'  =>  'edit_submit',
                                    'value' =>  $langSubmit
                                )
                            ))
                        .'</div>
                    </form>
                </div>
            </div>
        </div>';
    } else {
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
} else {
    //show post form
    if (allow_to_post($course_id, $uid, $is_editor)) {
        
        load_js('autosize');
        
        $content = Session::has('content')? Session::get('content'): '';
        $youtube = Session::has('youtube')? Session::get('youtube'): '';
        
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
        
        $head_content .= '<script>
                              function expand_form() {
                                  document.getElementById(\'resources_panel\').style.display=\'\';
                              }
                          </script>';
        
        $tool_content .= '<div class="row">
            <div class="col-sm-12">
                <div class="form-wrapper">
                    <form id="wall_form" method="post" action="" enctype="multipart/form-data">
                        <fieldset> 
                            <div class="form-group">
                                <label for="message_input">'.$langMessage.'</label>
                                <textarea id="textr" onfocus="expand_form();" class="form-control" rows="1" name="message" id="message_input">'.$content.'</textarea>
                            </div>
                            <div id="resources_panel" class="panel panel-default" style="display:none">
                                <div class="panel-body">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a data-toggle="tab" href="#youtube_video_div">'.$langWallYoutubeVideo.'</a></li>
                                        '.$video_li.'
                                        '.$docs_li.'
                                        '.$mydocs_li.'
                                    </ul>
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade in active" id="youtube_video_div" style="padding:10px">
                                            <label for="youtube_video">'.$langWallYoutubeVideoLink.'</label>
                                            <input class="form-control" type="url" name="youtube" id="youtube_video" value="'.$youtube.'">
                                        </div>
                                        '.$video_div.'
                                        '.$docs_div.'
                                        '.$mydocs_div.'
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
    
    //show wall posts
    $posts = Database::get()->queryArray("SELECT id, user_id, content, youtube, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $course_id, $posts_per_page);
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

draw($tool_content, 2, null, $head_content);
