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
require_once 'modules/wall/wall_wrapper.php';

ModalBoxHelper::loadModalBox(false);

$head_content .= '<link rel="stylesheet" type="text/css" href="css/wall.css">';

load_js('waypoints-infinite');

$pageName = $langWall;

//handle submit
if (isset($_POST['submit'])) {
    if (allow_to_post($course_id, $uid, $is_editor)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['extvideo'])) {
                $content = links_autodetection($_POST['message']);
                $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, timestamp) VALUES (?d,?d,?s,UNIX_TIMESTAMP())",
                        $course_id, $uid, $content)->lastInsertID;
                Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                    array('id' => $id,
                          'content' => $content));
                Session::Messages($langWallPostSaved, 'alert-success');
            } else {
                if (ExtVideoUrlParser::validateUrl($_POST['extvideo']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('extvideo', $_POST['extvideo']);
                    Session::Messages($langWallExtVideoLinkNotValid);
                } else {
                    $content = links_autodetection($_POST['message']);
                    $id = Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, extvideo, timestamp) VALUES (?d,?d,?s,?s, UNIX_TIMESTAMP())",
                            $course_id, $uid, $content, $_POST['extvideo'])->lastInsertID;
                    Log::record($course_id, MODULE_ID_WALL, LOG_INSERT,
                        array('id' => $id,
                              'content' => $content,
                              'extvideo' => $_POST['extvideo']));
                    Session::Messages($langWallPostSaved, 'alert-success');
                }
            }
            if (isset($id)) { //check if wall resources need to get saved
                //save multimedia content
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
            }
        } else {
            Session::Messages($langWallMessageEmpty);
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
        Session::Messages($langWallPostDeleted, 'alert-success');
    }
    decide_wall_redirect();
} elseif (isset($_POST['edit_submit'])) { //handle edit form submit
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
                    Session::Messages($langWallExtVideoLinkNotValid);
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
            
            Session::Messages($langWallPostSaved, 'alert-success');
            decide_wall_redirect();
        } else {
            Session::Messages($langWallMessageEmpty);
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
        Session::Messages($langWallGeneralSuccess, 'alert-success');
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
                        'level' => 'primary-label')
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
                                   'level' => 'primary-label')
                          ),false);
        
        $post = Database::get()->querySingle("SELECT content, extvideo FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $content = Session::has('content')? Session::get('content') : $post->content;
        $extvideo = Session::has('extvideo')? Session::get('extvideo') : $post->extvideo;
        
        if ($is_editor || visible_module(MODULE_ID_VIDEO)) {
            $video_div = '<div class="form-group tab-pane fade" id="videos_div" style="padding:10px">
                              '.list_videos($id).'
                          </div>';
            $video_li = '<li><a data-toggle="tab" href="#videos_div">'.$langVideo.'</a></li>';
        } else {
            $video_div = '';
            $video_li = '';
        }
        
        if ($is_editor || visible_module(MODULE_ID_DOCS)) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" style="padding:10px">
                              <input type="hidden" name="doc_ids" id="docs">
                              '.list_docs($id, NULL, TRUE).'
                          </div>';
            $docs_li = '<li><a data-toggle="tab" href="#docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }
        
        $post_author = Database::get()->querySingle("SELECT user_id FROM wall_post WHERE course_id = ?d AND id = ?d", $course_id, $id)->user_id;
        
        if (($post_author == $uid) && (($is_editor && get_config('mydocs_teacher_enable')) || (!$is_editor && get_config('mydocs_student_enable')))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                              '.list_docs($id,'mydocs', TRUE).'
                          </div>';
            $mydocs_li = '<li><a data-toggle="tab" href="#mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_LINKS)) {
            $links_div = '<div class="form-group tab-pane fade" id="links_div" style="padding:10px">
                              '.list_links($id).'
                          </div>';
            $links_li = '<li><a data-toggle="tab" href="#links_div">'.$langLinks.'</a></li>';
        } else {
            $links_div = '';
            $links_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_EXERCISE)) {
            $exercises_div = '<div class="form-group tab-pane fade" id="exercises_div" style="padding:10px">
                              '.list_exercises($id).'
                          </div>';
            $exercises_li = '<li><a data-toggle="tab" href="#exercises_div">'.$langExercises.'</a></li>';
        } else {
            $exercises_div = '';
            $exercises_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_ASSIGN)) {
            $assignments_div = '<div class="form-group tab-pane fade" id="assignments_div" style="padding:10px">
                              '.list_assignments($id).'
                          </div>';
            $assignments_li = '<li><a data-toggle="tab" href="#assignments_div">'.$langWorks.'</a></li>';
        } else {
            $assignments_div = '';
            $assignments_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_CHAT)) {
            $chats_div = '<div class="form-group tab-pane fade" id="chats_div" style="padding:10px">
                              '.list_chats($id).'
                          </div>';
            $chats_li = '<li><a data-toggle="tab" href="#chats_div">'.$langChat.'</a></li>';
        } else {
            $chats_div = '';
            $chats_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_QUESTIONNAIRE)) {
            $polls_div = '<div class="form-group tab-pane fade" id="polls_div" style="padding:10px">
                              '.list_polls($id).'
                          </div>';
            $polls_li = '<li><a data-toggle="tab" href="#polls_div">'.$langQuestionnaire.'</a></li>';
        } else {
            $polls_div = '';
            $polls_li = '';
        }

        if ($is_editor || visible_module(MODULE_ID_FORUM)) {
            $forums_div = '<div class="form-group tab-pane fade" id="forums_div" style="padding:10px">
                              '.list_forums($id).'
                          </div>';
            $forums_li = '<li><a data-toggle="tab" href="#forums_div">'.$langForum.'</a></li>';
        } else {
            $forums_div = '';
            $forums_li = '';
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
                                        <li class="active"><a data-toggle="tab" href="#extvideo_video_div">'.$langWallExtVideo.'</a></li>
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
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade in active" id="extvideo_video_div" style="padding:10px">
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
        decide_wall_redirect();
    }
} else {
    //show post form
    show_post_form();

    //show wall posts
    show_wall_posts();
}

draw($tool_content, 2, null, $head_content);
