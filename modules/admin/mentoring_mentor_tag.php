<?php

// $require_login = TRUE;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// require_once '../../include/baseTheme.php';


// $toolName = $langAddTagMentor;

// load_js('select2');

// $head_content .= "
// <script>
//     $(function () {
//         $('#tag-mentor').select2({
                
//                 minimumInputLength: 2,
//                 tags: true,
//                 tokenSeparators: [','],
//                 width: '100%',
//                 selectOnClose: true,
//                 createSearchChoice: function(term, data) {
//                   if ($(data).filter(function() {
//                     return this.text.localeCompare(term) === 0;
//                   }).length === 0) {
//                     return {
//                       id: term,
//                       text: term
//                     };
//                   }
//                 },
//                 ajax: {
//                     url: 'mentoring_mentor_feed.php',
//                     dataType: 'json',
//                     data: function(term, page) {
//                         return {
//                             q: term
//                         };
//                     },
//                     processResults: function(data, page) {
//                         return {results: data};
//                     }
//                 }
//         });

//         $('#select-mentors').select2();
//     });
// </script>";

// if(isset($_POST['submitTag'])){
//     if(!empty($_POST['tags'])){
//         foreach($_POST['tags'] as $tag){
//             $tag_exist = Database::get()->queryArray('SELECT * FROM mentoring_tag WHERE name = ?s',$tag);

//             if(count($tag_exist) > 0){
//                 if(isset($_POST['mentor_id']) and !empty($_POST['mentor_id'])){
//                     foreach($tag_exist as $e){
//                         foreach($_POST['mentor_id'] as $mentor_id){
//                             $mentor = getDirectReference($mentor_id);
//                             Database::get()->query("INSERT INTO mentoring_mentor_tag SET 
//                                                     user_id = $mentor, 
//                                                     date = " . DBHelper::timeAfter() . ", 
//                                                     tag_id = $e->id");
//                         }
                        
//                     }
//                 }
                
//             }else{
//                 $result = Database::get()->query("INSERT into mentoring_tag SET name = ?s",$tag);
//                 if(isset($_POST['mentor_id']) and !empty($_POST['mentor_id'])){
//                     foreach($_POST['mentor_id'] as $mentor_id){
//                         $mentor = getDirectReference($mentor_id);
//                         Database::get()->query("INSERT INTO mentoring_mentor_tag SET 
//                                                 user_id = $mentor, 
//                                                 date = " . DBHelper::timeAfter() . ", 
//                                                 tag_id = $result->lastInsertID");
//                     }
//                 }
//             }
//         }
    
//         if(empty($_POST['mentor_id'])){
//             Session::flash('message',$langAddTagSuccessMsg);
//             Session::flash('alert-class', 'alert-success');
//         }else{
//             Session::flash('message',$langAddTagMentorSuccessMsg);
//             Session::flash('alert-class', 'alert-success');
//         }
//     }else{
//         Session::flash('message',$langChooseTagMentorMsg);
//         Session::flash('alert-class', 'alert-warning');
//     }
//     redirect_to_home_page('modules/admin/mentoring_mentor_tag.php');
// }

// if(isset($_POST['deleteTag'])){
//     Database::get()->query("DELETE FROM mentoring_tag WHERE id = ?d",$_POST['tag_id']);
//     Database::get()->query("DELETE FROM mentoring_mentor_tag WHERE tag_id = ?d",$_POST['tag_id']);

//     Session::flash('message',$langDelTagSuccessMsg);
//     Session::flash('alert-class', 'alert-success');

//     redirect_to_home_page('modules/admin/mentoring_mentor_tag.php');
// }


// $data['all_mentors'] = Database::get()->queryArray("SELECT *FROM user WHERE is_mentor = ?d",1);

// $data['list_tags'] = Database::get()->queryArray("SELECT *FROM mentoring_tag");

// $data['action_bar'] = action_bar([
//     [ 'title' => trans('langBack'),
//         'url' => $urlServer.'modules/admin/mentoring_platform_enable.php',
//         'icon' => 'fa-reply',
//         'level' => 'primary-label',
//         'button-class' => 'btn-secondary' ]
//     ], false);


// view('admin.mentoring_platform.mentoring_mentor_tag', $data);


