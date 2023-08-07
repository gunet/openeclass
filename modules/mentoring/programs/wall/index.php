<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/programs/wall/wall_wrapper.php';
require_once 'modules/mentoring/programs/wall/MentoringExtVideoUrlParser.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['group_id']);
    $program_group_id = getDirectReference($_GET['group_id']);
}

$toolName = $langWall.' ('.get_name_for_current_group($program_group_id).')';

ModalBoxHelper::loadModalBox(false);

load_js('waypoints-infinite');

if(intval(getDirectReference($_GET['group_id'])) != 0){
    $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
    $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
    if(count($check_group) == 0){
        redirect_to_home_page($urlServer.'/mentoring_programs/'.$mentoring_program_code.'/index.php');
    }
}else{
    after_reconnect_go_to_mentoring_homepage();
}

$data['action_bar'] = '';
$data['tool_content'] = '';

//handle submit
if (isset($_POST['submit'])) {
    if (allow_to_post($mentoring_program_id, $uid, $is_editor_wall)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['extvideo'])) {
                
                $content = links_autodetection($_POST['message']);
                $id = Database::get()->query("INSERT INTO mentoring_wall_post (mentoring_program_id, user_id, content, timestamp, group_id) VALUES (?d,?d,?s,UNIX_TIMESTAMP(),?d)",
                        $mentoring_program_id, $uid, $content, $program_group_id)->lastInsertID;

                Session::flash('message',$langWallPostSaved);
                Session::flash('alert-class', 'alert-success');
            } else {
                if (MentoringExtVideoUrlParser::validateUrl($_POST['extvideo']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('extvideo', $_POST['extvideo']);
                    //Session::Messages($langWallExtVideoLinkNotValid);
                    Session::flash('message',$langWallExtVideoLinkNotValid);
                    Session::flash('alert-class', 'alert-warning');
                } else {
                    $content = links_autodetection($_POST['message']);
                    $id = Database::get()->query("INSERT INTO mentoring_wall_post (mentoring_program_id, user_id, content, extvideo, timestamp, group_id) VALUES (?d,?d,?s,?s, UNIX_TIMESTAMP(),?d)",
                            $mentoring_program_id, $uid, $content, $_POST['extvideo'], $program_group_id)->lastInsertID;
                    
                    Session::flash('message',$langWallPostSaved);
                    Session::flash('alert-class', 'alert-success');
                }
            }
            if (isset($id)) { //check if wall resources need to get saved

                //save documents
                if ($is_editor_wall or $is_member_common_group) {
                    insert_docs($id);
                }
                //save my documents
                if (($is_editor_wall && get_config('mydocs_teacher_enable')) || (!$is_editor_wall && get_config('mydocs_student_enable'))) {                    
                    insert_docs($id,'mydocs');                    
                }
                //save forums
                if ($is_editor_wall or $is_member_common_group) {
                    insert_forum($id);
                }
               
            }
        } else {
        // Session::Messages($langWallMessageEmpty);
            Session::flash('message',$langWallMessageEmpty);
            Session::flash('alert-class', 'alert-warning');
            if (!empty($_POST['extvideo'])) {
                Session::flash('extvideo', $_POST['extvideo']);
            }
        }
        
        // if(isset($_GET['fromCommonWall'])){
        //     redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getIndirectReference($program_group_id));
        // }

        decide_wall_redirect();
    }
} elseif (isset($_GET['delete'])) { //handle delete
    $id = intval($_GET['delete']);
    if (allow_to_edit($id, $uid, $is_editor_wall)) {
        Database::get()->query("DELETE FROM mentoring_wall_post_resources WHERE post_id = ?d", $id);
        Database::get()->query("DELETE FROM mentoring_wall_post WHERE id = ?d", $id);
        // Session::Messages($langWallPostDeleted, 'alert-success');
        Session::flash('message',$langWallPostDeleted);
        Session::flash('alert-class', 'alert-success');
    }
    decide_wall_redirect();
} elseif (isset($_POST['edit_submit'])) { //handle edit form submit
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor_wall)) {
        if (!empty($_POST['message'])) {
            if (empty($_POST['extvideo'])) {
                $content = links_autodetection($_POST['message']);
                $extvideo = '';
                Database::get()->query("UPDATE mentoring_wall_post SET content = ?s, extvideo = ?s WHERE id = ?d AND mentoring_program_id = ?d AND group_id = ?d",
                    $content, $extvideo, $id, $mentoring_program_id, $program_group_id);
                Database::get()->query("DELETE FROM mentoring_wall_post_resources WHERE post_id = ?d", $id);

            } else {
                if (MentoringExtVideoUrlParser::validateUrl($_POST['extvideo']) === FALSE) {
                    Session::flash('content', $_POST['message']);
                    Session::flash('extvideo', $_POST['extvideo']);
                    Session::flash('message',$langWallExtVideoLinkNotValid);
                    Session::flash('alert-class', 'alert-warniig');
                    redirect_to_home_page("modules/mentoring/programs/wall/index.php?edit=$id");
                } else {
                    $content = links_autodetection($_POST['message']);
                    $extvideo = $_POST['extvideo'];
                    Database::get()->query("UPDATE mentoring_wall_post SET content = ?s, extvideo = ?s WHERE id = ?d AND mentoring_program_id = ?d AND group_id = ?d",
                        $content, $extvideo, $id, $mentoring_program_id,$program_group_id);
                    Database::get()->query("DELETE FROM mentoring_wall_post_resources WHERE post_id = ?d", $id);
                }
            }

            //save documents
            if ($is_editor_wall or $is_member_common_group) {
                insert_docs($id);
            }

            $post_author = Database::get()->querySingle("SELECT user_id FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $id)->user_id;

            //save my documents
            if (($post_author == $uid) && (($is_editor_wall && get_config('mydocs_teacher_enable')) || (!$is_editor_wall && get_config('mydocs_student_enable'))) ) {
                insert_docs($id,'mydocs');
            }

            //save forums
            if ($is_editor_wall or $is_member_common_group) {
                insert_forum($id);
            }
            Session::flash('message',$langWallPostSaved);
            Session::flash('alert-class', 'alert-success');
            decide_wall_redirect();

        } else {
            // Session::Messages($langWallMessageEmpty);
            Session::flash('message',$langWallMessageEmpty);
            Session::flash('alert-class', 'alert-warning');
            if (!empty($_POST['extvideo'])) {
                Session::flash('extvideo', $_POST['extvideo']);
                redirect_to_home_page("modules/mentoring/programs/wall/index.php?edit=$id");
            }
        }
    }
} elseif (isset($_GET['pin'])) {
    $id = intval($_GET['pin']);
    if ($is_editor_wall && allow_to_edit($id, $uid, $is_editor_wall)) {
        Database::get()->query("UPDATE mentoring_wall_post SET pinned = !pinned WHERE id = ?d", $id);
        Session::flash('message',$langWallGeneralSuccess);
        Session::flash('alert-class', 'alert-success');
        decide_wall_redirect();
    }
}


if (isset($_GET['showPost'])) { //show comments case
    $id = intval($_GET['showPost']);
    $post = Database::get()->querySingle("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $id);
    if ($post) {
        $data['tool_content'] .= generate_single_post_html($post);
    } else {
        decide_wall_redirect();
    }
}elseif (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if (allow_to_edit($id, $uid, $is_editor_wall)) {

        $post = Database::get()->querySingle("SELECT content, extvideo FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $id);
        $content = Session::has('content')? Session::get('content') : $post->content;
        $extvideo = Session::has('extvideo')? Session::get('extvideo') : $post->extvideo;

        if ($is_editor_wall or $is_member_common_group) {
            $docs_div = '<div class="form-group tab-pane fade" id="docs_div" role="tabpanel" aria-labelledby="nav_edit_docs" style="padding:10px">
                                <input type="hidden" name="doc_ids" id="docs">
                                '.list_docs($id, NULL, TRUE).'
                            </div>';
            $docs_li = '<li><a id="nav_edit_docs" class="mentoring_program_nav_item_nav_linkProgram nav-link rounded-0" data-bs-toggle="tab" href="#docs_div">'.$langDoc.'</a></li>';
        } else {
            $docs_div = '';
            $docs_li = '';
        }

        $post_author = Database::get()->querySingle("SELECT user_id FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $id)->user_id;

        if (($post_author == $uid) && (($is_editor_wall && get_config('mydocs_teacher_enable')) || (!$is_editor_wall && get_config('mydocs_student_enable')))) {
            $mydocs_div = '<div class="form-group tab-pane fade" id="mydocs_div" role="tabpanel" aria-labelledby="nav_edit_mydocs" style="padding:10px">
                            <input type="hidden" name="mydoc_ids" id="mydocs">
                                '.list_docs($id,'mydocs', TRUE).'
                            </div>';
            $mydocs_li = '<li><a id="nav_edit_mydocs" class="mentoring_program_nav_item_nav_linkProgram nav-link rounded-0" data-bs-toggle="tab" href="#mydocs_div">'.$langMyDocs.'</a></li>';
        } else {
            $mydocs_div = '';
            $mydocs_li = '';
        }

        if ($is_editor_wall or $is_member_common_group) {
            $forums_div = '<div class="form-group tab-pane fade" id="forums_div" role="tabpanel" aria-labelledby="nav_edit_forums" style="padding:10px">
                                '.list_forums($id).'
                            </div>';
            $forums_li = '<li><a id="nav_edit_forums" class="mentoring_program_nav_item_nav_linkProgram nav-link rounded-0" data-bs-toggle="tab" href="#forums_div">'.$langForum.'</a></li>';
        } else {
            $forums_div = '';
            $forums_li = '';
        }

        $data['tool_content'] .= '
            <div class="col-12">
                <div class="form-wrapper form-edit rounded-2 bg-transparent">
                    <form id="wall_form" method="post" action="" enctype="multipart/form-data">
                        <fieldset>
                            <div class="form-group">
                                <label class="control-label-notes" for="message_input">'.$langMessage.'</label>
                                <textarea class="form-control rounded-2" rows="6" name="message" id="message_input">'.strip_tags($content).'</textarea>
                            </div>
                            <div class="panel panel-default mt-3 rounded-2 border-0">
                                <div class="panel-body rounded-2">
                                    <ul class="nav nav-pills mb-3 mentoring_program_ul rounded-0">
                                        <li class="active"><a id="nav_edit_extvideo" class="mentoring_program_nav_item_nav_linkProgram nav-link rounded-0" data-bs-toggle="tab" href="#extvideo_video_div">'.$langWallExtVideo.'</a></li>

                                        '.$docs_li.'
                                        '.$mydocs_li.'
                                        '.$forums_li.'
                                    </ul>
                                    <div class="tab-content">
                                        <div class="form-group tab-pane fade show active" id="extvideo_video_div" role="tabpanel" aria-labelledby="nav_edit_extvideo" style="padding:10px">
                                            <label class="control-label-notes" for="extvideo_video">'.$langWallExtVideoLink.'</label>
                                            <input class="form-control rounded-pill bgEclass" type="url" name="extvideo" id="extvideo_video" value="'.$extvideo.'">
                                        </div>
                                        
                                        '.$docs_div.'
                                        '.$mydocs_div.'
                                        '.$forums_div.'
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">'.
                            form_buttons(array(
                                array(
                                    'class' => 'btnSubmitWallPost',
                                    'text'  =>  $langSubmit,
                                    'name'  =>  'edit_submit',
                                    'value' =>  $langSubmit
                                )
                            ))
                        .'</div>
                        </fieldset>
                    </form>
                </div>
            </div>';
    } else {
        decide_wall_redirect();
    }
} else {
    //show post form
    $data['tool_content'] = show_post_form();

    //show wall posts
    $data['tool_content'] = show_wall_posts();
}

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($program_group_id),
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);

$checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

if($checkIsCommon == 1){
    $data['isCommonGroup'] = 1;
}else{
    $data['isCommonGroup'] = 0;
}

view('modules.mentoring.programs.wall.index', $data);
        
   
    





