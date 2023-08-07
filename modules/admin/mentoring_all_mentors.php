<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// $require_login = TRUE;

// require_once '../../include/baseTheme.php';


// $toolName = $langlistMentor.' ('.$langDelMentorFromTag.')';

// $data['all_mentors'] = Database::get()->queryArray("SELECT *FROM user WHERE is_mentor = ?d",1);

// if(isset($_POST['deleteTagFromMentor'])){

//     if(!empty($_POST['tags_ids']) > 0){
//         $tags_ids = implode(',',$_POST['tags_ids']);

//         Database::get()->query("DELETE FROM mentoring_mentor_tag WHERE tag_id IN ($tags_ids) AND user_id = ?d",$_POST['mentor_id']);
        
//         Session::flash('message',$langDeleteCurrentTagsFromMentor); 
//         Session::flash('alert-class', 'alert-success');

//         redirect_to_home_page('modules/admin/mentoring_all_mentors.php');
//     }else{
//         Session::flash('message',$langSelectTagMsg); 
//         Session::flash('alert-class', 'alert-warning');

//         redirect_to_home_page('modules/admin/mentoring_all_mentors.php');
//     }

// }

// $data['action_bar'] = action_bar([
//     [ 'title' => trans('langBack'),
//         'url' => $urlServer.'modules/admin/mentoring_platform_enable.php',
//         'icon' => 'fa-reply',
//         'level' => 'primary-label',
//         'button-class' => 'btn-secondary' ]
//     ], false);


// view('admin.mentoring_platform.mentoring_all_mentors', $data);


