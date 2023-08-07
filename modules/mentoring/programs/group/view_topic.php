<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['topic_id']) and intval(getDirectReference($_GET['topic_id'])) != 0){
    put_session_topic_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['topic_id']));
}

//after destroy getDirectReference session then get values from table old_sessions in db 
if(isset($_GET['topic_id']) and isset($_GET['group_id']) and isset($_GET['forum_id'])
    and intval(getDirectReference($_GET['topic_id'])) == 0 and intval(getDirectReference($_GET['group_id'])) == 0
    and intval(getDirectReference($_GET['forum_id'])) == 0){
        after_reconnect_go_to_mentoring_homepage();
}
else{
    if(isset($_GET['topic_id']) and isset($_GET['group_id']) and isset($_GET['forum_id'])){
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
        $data['topic_id'] = $topic_id = getDirectReference($_GET['topic_id']);
        $data['forum_id'] = $forum_id = getDirectReference($_GET['forum_id']);

        $check_topic = Database::get()->queryArray("SELECT *FROM mentoring_forum_topic WHERE
                                      id = ?d",$topic_id);
        if(count($check_topic) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($group_id));
        }
    }
}


$data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);
$data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
$data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

if($is_tutor_of_mentoring_program or $is_editor_current_group or $is_mentee or $is_admin){

    $title = Database::get()->querySingle("SELECT title FROM mentoring_forum_topic WHERE id = ?d",$topic_id);
    $toolName = $title->title; 

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

    //download file
    // get attached forum topic file (if any)
    if (isset($_GET['get'])) {
        $file_id = getDirectReference($_GET['get']);
        if (!mentoring_send_forum_post_file($file_id)) {
            Session::flash('message',$langFileNotFound); 
            Session::flash('alert-class', 'alert-danger');
        }
    }

    //del file from post id
    if(isset($_GET['del_fileid'])){
        $del_fileid = getDirectReference($_GET['del_fileid']);
        $file_name_from_del_fileid = Database::get()->querySingle("SELECT topic_filepath FROM mentoring_forum_post 
                                                                   WHERE id = ?d",$del_fileid)->topic_filepath;
        
        Database::get()->query("UPDATE mentoring_forum_post SET
                                topic_filepath = ?s, topic_filename = ?s
                                WHERE id = ?d",'','',$del_fileid);

        $target_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
        unlink($target_dir.$file_name_from_del_fileid);

        Session::flash('message',$langFileDeleted); 
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
    }

    //delete all messages of topic
    if(isset($_POST['delete_all_messages_of_topic'])){
        Database::get()->query("DELETE FROM mentoring_forum_post WHERE topic_id IN (?d)",$topic_id);
        Database::get()->query("UPDATE mentoring_forum_topic SET num_replies = ?d WHERE id = ?d",0,$topic_id);
        Session::flash('message',$langMessagesOfTopicHasDeleted); 
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
    }

    // answer to topic
    if (isset($_POST['create_answer']) and isset($_POST['message']) and !empty($_POST['message'])) {

        $message = trim($_POST['message']);
        $message = purify($message);
        $poster_ip = Mentoring_Log::get_client_ip();
        $parent_post = $_POST['parent_post'];
        $time = date("Y-m-d H:i:s");

        // upload attached file
        if (isset($_FILES['topic_file']) and is_uploaded_file($_FILES['topic_file']['tmp_name'])) { // upload comments file
            $topic_filename = $_FILES['topic_file']['name'];
            validateUploadedFile($topic_filename); // check file type
            $topic_filename = add_ext_on_mime($topic_filename);
            // File name used in file system and path field
            $safe_topic_filename = safe_filename(get_file_extension($topic_filename));
            $forum_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
            if (!file_exists($forum_dir)) {
                mkdir("$webDir/mentoring_programs/$mentoring_program_code/forum/", 0755);
            }
            if (move_uploaded_file($_FILES['topic_file']['tmp_name'], "$webDir/mentoring_programs/$mentoring_program_code/forum/$safe_topic_filename")) {
                @chmod("$webDir/mentoring_programs/$mentoring_program_code/forum/$safe_topic_filename", 0644);
                $topic_real_filename = $_FILES['topic_file']['name'];
                $topic_filepath = $safe_topic_filename;
            }
        } else {
            $topic_filepath = $topic_real_filename = '';
        }

        $this_post = Database::get()->query("INSERT INTO mentoring_forum_post (topic_id, post_text, poster_id, post_time, poster_ip, parent_post_id, topic_filepath, topic_filename) VALUES (?d, ?s , ?d, ?t, ?s, ?d, ?s, ?s)"
                        , $topic_id, $message, $uid, $time, $poster_ip, $parent_post, $topic_filepath, $topic_real_filename)->lastInsertID;
        
        $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM mentoring_forum_post
                            INNER JOIN mentoring_forum_topic ON mentoring_forum_post.topic_id = mentoring_forum_topic.id
                            INNER JOIN mentoring_forum ON mentoring_forum.id = mentoring_forum_topic.forum_id
                            WHERE mentoring_forum_post.poster_id = ?d AND mentoring_forum.mentoring_program_id = ?d", $uid, $mentoring_program_id);

        Database::get()->query("DELETE FROM mentoring_forum_user_stats WHERE user_id = ?d AND mentoring_program_id = ?d", $uid, $mentoring_program_id);
        Database::get()->query("INSERT INTO mentoring_forum_user_stats (user_id, num_posts, mentoring_program_id) VALUES (?d,?d,?d)", $uid, $forum_user_stats->c, $mentoring_program_id);
       
        $forum_id = Database::get()->querySingle("SELECT forum_id FROM mentoring_group
                                                 WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id);

        Database::get()->query("UPDATE mentoring_forum_topic SET topic_time = ?t,
                                            num_replies = num_replies+1,
                                            last_post_id = ?d
                                WHERE id = ?d AND forum_id = ?d", $time, $this_post, $topic_id, $forum_id->forum_id);

        $result = Database::get()->query("UPDATE mentoring_forum SET num_posts = num_posts+1,
                                        last_post_id = ?d
                                        WHERE id = ?d
                                        AND mentoring_program_id = ?d", $this_post, $forum_id->forum_id, $mentoring_program_id);

       
        Session::flash('message',$langStored); 
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));

    }else{
        if(isset($_POST['parent_post'])){
            Session::flash('message',$langTopicDontAnswerEmptyMessage); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
        }
        
    }

    //edit topic
    if(isset($_POST['edit_topic']) and isset($_POST['message']) and !empty($_POST['message'])){

        $message = $_POST['message'];
        $post_id = $_POST['post_id'];
        $poster_id = $_POST['poster_id'];
        $time = date("Y-m-d H:i:s");

        // upload attached file
        if (isset($_FILES['topic_file']) and is_uploaded_file($_FILES['topic_file']['tmp_name'])) { // upload comments file
            $topic_filename = $_FILES['topic_file']['name'];
            validateUploadedFile($topic_filename); // check file type
            $topic_filename = add_ext_on_mime($topic_filename);
            // File name used in file system and path field
            $safe_topic_filename = safe_filename(get_file_extension($topic_filename));
            $forum_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
            if (!file_exists($forum_dir)) {
                mkdir("$webDir/mentoring_programs/$mentoring_program_code/forum/", 0755);
            }
            if (move_uploaded_file($_FILES['topic_file']['tmp_name'], "$webDir/mentoring_programs/$mentoring_program_code/forum/$safe_topic_filename")) {
                @chmod("$webDir/mentoring_programs/$mentoring_program_code/forum/$safe_topic_filename", 0644);
                $topic_real_filename = $_FILES['topic_file']['name'];
                $topic_filepath = $safe_topic_filename;
            }
        } else {
            //check if exist filepath so dont edit it else set empty
            $check_topic_filepath = Database::get()->querySingle("SELECT topic_filepath FROM mentoring_forum_post
                                                        WHERE id = ?d AND poster_id = ?d",$_POST['post_id'],$_POST['poster_id'])->topic_filepath;
            if($check_topic_filepath){
                $topic_filepath = $check_topic_filepath;
            }else{
                $topic_filepath = '';
            }
            $check_topic_filename = Database::get()->querySingle("SELECT topic_filename FROM mentoring_forum_post
                                                        WHERE id = ?d AND poster_id = ?d",$_POST['post_id'],$_POST['poster_id'])->topic_filename;
            if($check_topic_filename){
                $topic_real_filename = $check_topic_filename;
            }else{
                $topic_real_filename = '';
            }
        }

        $result = Database::get()->query("UPDATE mentoring_forum_post SET post_text = ?s, post_time = ?t, topic_filepath = ?s, topic_filename = ?s
                            WHERE id = ?d AND poster_id = ?d", purify($message),$time, $topic_filepath, $topic_real_filename, $post_id, $poster_id);
    
        Session::flash('message',$langStored); 
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
    }else{
        if(isset($_POST['editt_post'])){
            Session::flash('message',$langTopicDontAnswerEmptyMessage); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
        }
        
    }

    //delete topic
    if(isset($_POST['delete_topic'])){
        $file_path = Database::get()->querySingle("SELECT topic_filepath FROM mentoring_forum_post WHERE id = ?d",$_POST['post_id'])->topic_filepath;
        if($file_path){
            $target_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
            unlink($target_dir.$file_path);
        }
        Database::get()->query("DELETE from mentoring_forum_post WHERE id = ?d AND poster_id = ?d",$_POST['post_id'], $_POST['poster_id']);
        Database::get()->query("UPDATE mentoring_forum_topic SET num_replies = num_replies-1 WHERE id = ?d",$topic_id);
        Database::get()->query("UPDATE mentoring_forum_user_stats SET num_posts = num_posts-1 WHERE user_id = ?d AND mentoring_program_id = ?d",$_POST['poster_id'],$mentoring_program_id);
        
        Session::flash('message',$langDeleteTopicMentoringSuccess); 
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/view_topic.php?group_id=".getInDirectReference($group_id)."&topic_id=".getInDirectReference($topic_id)."&forum_id=".getInDirectReference($forum_id));
    }





    
    $data['rich_text_editor'] = rich_text_editor('message', 4, 20, '');
    //get answers topic
    $data['answers_topic'] = Database::get()->queryArray("SELECT *FROM mentoring_forum_post WHERE topic_id = ?d",$topic_id);

    $data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/group/forum_group.php?forum_group_id='.getInDirectReference($group_id),
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);

    view('modules.mentoring.programs.group.view_topic', $data);

}else{
    redirect_to_home_page("modules/mentoring/programs/show_programs.php");
}

