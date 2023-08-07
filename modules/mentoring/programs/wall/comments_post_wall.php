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

if(intval(getDirectReference($_GET['group_id'])) != 0){
    $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
    $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
    if(count($check_group) == 0){
        redirect_to_home_page($urlServer.'/mentoring_programs/'.$mentoring_program_code.'/index.php');
    }
}else{
    after_reconnect_go_to_mentoring_homepage();
}

$toolName = $langComments;

$data['AddCommentContinue'] = false;

if(isset($_GET['addComment'])){
    $data['AddCommentContinue'] = true;
    $data['post_id'] = getDirectReference($_GET['post_id']);
    $data['postUser'] = getDirectReference($_GET['postUser']);
    $data['token'] = getDirectReference($_GET['token']);
    $data['countPosts'] = getDirectReference($_GET['countPosts']);
    $data['post_details'] = Database::get()->queryArray("SELECT *FROM mentoring_wall_post WHERE id = ?d",$data['post_id']);
    $data['allCommentForCurrentPost'] = Database::get()->queryArray("SELECT *FROM mentoring_comments WHERE rid = ?d",$data['post_id']);
}

if(isset($_POST['submitComment'])){
    if(!empty($_POST['contentComment'])){
        $add = Database::get()->query("INSERT INTO mentoring_comments SET
                                rid = ?d,
                                rtype = ?s,
                                content = ?s,
                                time = ". DBHelper::timeAfter() .",
                                user_id = ?d",$_POST['post_id'],'wallpost',$_POST['contentComment'],$_POST['fromUser']);
        if($add){
            Session::flash('message',$langCommentAddSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }else{
        Session::flash('message',$langEmptyComment);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page("modules/mentoring/programs/wall/my_doc_wall.php?group_id=".getInDirectReference($group_id)."&wall");
}

if(isset($_POST['updateComment'])){
    if(!empty($_POST['contentComment'])){
        $update = Database::get()->query("UPDATE mentoring_comments SET 
                                        content = ?s,time = ". DBHelper::timeAfter() ." 
                                        WHERE id = ?d AND user_id = ?d",$_POST['contentComment'],$_POST['comment_id'],$_POST['fromUser']);
        if($update){
            Session::flash('message',$langCommentUpdateSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langCommentUpdateNoSuccess);
            Session::flash('alert-class', 'alert-danger');
        }
    }else{
        Session::flash('message',$langEmptyComment);
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page("modules/mentoring/programs/wall/my_doc_wall.php?group_id=".getInDirectReference($group_id)."&wall");
}

if(isset($_POST['deleteComment'])){

    $del = Database::get()->query("DELETE FROM mentoring_comments WHERE id = ?d",$_POST['comment_id']);
    if($del){
        Session::flash('message',$langCommentDeleteSuccess);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langCommentDeleteNoSuccess);
        Session::flash('alert-class', 'alert-danger');
    }

    redirect_to_home_page("modules/mentoring/programs/wall/my_doc_wall.php?group_id=".getInDirectReference($group_id)."&wall");
}

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/wall/my_doc_wall.php?group_id='.getInDirectReference($group_id).'&wall',
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);

$data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);

$checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

if($checkIsCommon == 1){
    $data['isCommonGroup'] = 1;
}else{
    $data['isCommonGroup'] = 0;
}

view('modules.mentoring.programs.wall.comments_post_wall', $data);
        
   
    





