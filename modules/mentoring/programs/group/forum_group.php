<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['forum_group_id']) and intval(getDirectReference($_GET['forum_group_id'])) != 0){
    put_session_forum_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['forum_group_id']));
}

if(isset($_GET['forum_group_id']) and intval(getDirectReference($_GET['forum_group_id'])) == 0){
    after_reconnect_go_to_mentoring_homepage();
}else{
    $data['group_id'] = $group_id = getDirectReference($_GET['forum_group_id']);
}


$data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);
$data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
$data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

if($is_tutor_of_mentoring_program or $is_editor_current_group or $is_mentee){

    $toolName = $langForumMentoringGroups.' ('.get_name_for_current_group($group_id).')';

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

    //create
    if(isset($_POST['create_topic']) and isset($_POST['message']) and !empty($_POST['message'])){
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        $message = purify($message);
        $poster_ip = Mentoring_Log::get_client_ip();
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

        $forum_id = Database::get()->querySingle("SELECT forum_id FROM mentoring_group
                                                  WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id);
        $forum_id = $forum_id->forum_id;

        $topic_id = Database::get()->query("INSERT INTO mentoring_forum_topic (title, poster_id, forum_id, topic_time) VALUES (?s, ?d, ?d, ?t)"
                                            , $subject, $uid, $forum_id, $time)->lastInsertID;
       

        $post_id = Database::get()->query("INSERT INTO mentoring_forum_post (topic_id, post_text, poster_id, post_time, poster_ip, topic_filepath, topic_filename) VALUES (?d, ?s, ?d, ?t, ?s, ?s, ?s)"
                                            , $topic_id, $message, $uid, $time, $poster_ip, $topic_filepath, $topic_real_filename)->lastInsertID;


        $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM mentoring_forum_post
            INNER JOIN mentoring_forum_topic ON mentoring_forum_post.topic_id = mentoring_forum_topic.id
            INNER JOIN mentoring_forum ON mentoring_forum.id = mentoring_forum_topic.forum_id
            WHERE mentoring_forum_post.poster_id = ?d AND mentoring_forum.mentoring_program_id = ?d", $uid, $mentoring_program_id);

        Database::get()->query("DELETE FROM mentoring_forum_user_stats WHERE user_id = ?d AND mentoring_program_id = ?d", $uid, $mentoring_program_id);
        Database::get()->query("INSERT INTO mentoring_forum_user_stats (user_id, num_posts, mentoring_program_id) VALUES (?d,?d,?d)", $uid, $forum_user_stats->c, $mentoring_program_id);

        Database::get()->query("UPDATE mentoring_forum_topic
                                        SET last_post_id = ?d
                                        WHERE id = ?d
                                        AND forum_id = ?d", $post_id, $topic_id, $forum_id);

        Database::get()->query("UPDATE mentoring_forum
                                        SET num_topics = num_topics+1,
                                        num_posts = num_posts+1,
                                        last_post_id = ?d
                                        WHERE id = ?d", $post_id, $forum_id);

        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_FORUM, MENTORING_LOG_INSERT, array('title' => $subject, 'old_title' => ''));
        
        Session::flash('message',$langTopicCreate);
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/forum_group.php?forum_group_id=".getInDirectReference($group_id));
        
    }
    else{
        if(isset($_POST['empty_message_topic'])){
            Session::flash('message',$langTopicDontCreateEmptyMessage);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/mentoring/programs/group/forum_group.php?forum_group_id=".getInDirectReference($group_id));
        }
    }

    //edit
    if(isset($_POST['edit_topic']) and isset($_POST['subject']) and !empty($_POST['subject'])){
        $time = date("Y-m-d H:i:s");
        $old_subject = Database::get()->querySingle("SELECT title FROM mentoring_forum_topic WHERE id = ?d",$_POST['topic_id'])->title;

        Database::get()->query("UPDATE mentoring_forum_topic SET title = ?s,topic_time = ?t WHERE id = ?d"
                                            , trim($_POST['subject']), $time, $_POST['topic_id']);

        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_FORUM, MENTORING_LOG_MODIFY, array('title' => trim($_POST['subject']),
                                                                                                             'old_title' => $old_subject));
        
        Session::flash('message',$langTopicEditSuccessName);
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/forum_group.php?forum_group_id=".getInDirectReference($group_id));
    }else{
        if(isset($_POST['empty_subject_topic'])){
            Session::flash('message',$langTopicDontCreateEmptyMessage);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/mentoring/programs/group/forum_group.php?forum_group_id=".getInDirectReference($group_id));
        }
    }

    //delete
    if(isset($_POST['delete_topic'])){
        $file_paths = Database::get()->queryArray("SELECT topic_filepath FROM mentoring_forum_post WHERE topic_id IN (?d)",$_POST['topic_id']);
        if(count($file_paths) > 0){
            $target_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
            foreach($file_paths as $file){
                if(!empty($file->topic_filepath)){
                    unlink($target_dir.$file->topic_filepath);
                }
            }
            
        }

        $del_num_posts = Database::get()->querySingle("SELECT COUNT(*) AS total FROM mentoring_forum_post WHERE topic_id = ?d AND poster_id = ?d", $_POST['topic_id'],$_POST['topic_poster_id'])->total;
        Database::get()->query("UPDATE mentoring_forum_user_stats SET num_posts = num_posts-$del_num_posts WHERE user_id = ?d AND mentoring_program_id = ?d",$_POST['topic_poster_id'],$mentoring_program_id);

        Database::get()->query("DELETE FROM mentoring_forum_post WHERE topic_id IN (?d)",$_POST['topic_id']);
        $title_topic = Database::get()->querySingle("SELECT title FROM mentoring_forum_topic WHERE id = ?d",$_POST['topic_id'])->title;
        Database::get()->query("DELETE FROM mentoring_forum_topic WHERE id = ?d",$_POST['topic_id']);

        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_FORUM, MENTORING_LOG_DELETE, array('title' => $title_topic,
                                                                                                             'old_title' => ''));

        Session::flash('message',$langSubjectMentoringDeleteSuccess); 
        Session::flash('alert-class', 'alert-success');
    }


    $data['rich_text_editor'] = rich_text_editor('message', 4, 20, '');
    
    $forum_id = Database::get()->querySingle("SELECT forum_id FROM mentoring_group
                                                  WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id);
    $forum_id = $forum_id->forum_id;
    $data['all_topics'] = Database::get()->queryArray("SELECT t.*, p.post_time, t.poster_id AS topic_poster_id, p.poster_id AS poster_id
                                        FROM mentoring_forum_topic t
                                        LEFT JOIN mentoring_forum_post p ON t.last_post_id = p.id
                                        WHERE t.forum_id = ?d
                                        ORDER BY topic_time DESC", $forum_id);

    $data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($group_id),
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);
    

}else{
    redirect_to_home_page("modules/mentoring/programs/show_programs.php");
}


view('modules.mentoring.programs.group.forum_group', $data);
