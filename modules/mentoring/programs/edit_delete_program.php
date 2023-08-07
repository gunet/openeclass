<?php

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#edit-select-tutors').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

$title_program = show_mentoring_program_title($mentoring_program_code);
$toolName = $langMentoringEdit.' ('.$title_program.')';

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

$data['users_mentors'] = Database::get()->queryArray("SELECT *FROM user WHERE is_mentor = ?d",1);

$data['all_specializations'] = Database::get()->queryArray("SELECT *FROM mentoring_specializations");

//delete program_image from edit program
if(isset($_GET['del_img'])){
    $del = delete_mentoring_program_image($mentoring_program_code);
    if($del){
        $target_dir = "$webDir/mentoring_programs/$mentoring_program_code/image/";
        unlink($target_dir.$_GET['del_img']);
    }
    Session::flash('message',$langFaqEditSuccess);
    Session::flash('alert-class', 'alert-success');
}

$details = show_mentoring_program_details($mentoring_program_code, $mentoring_program_id);
foreach($details as $r){
    $data['mentoring_program_id'] = $r->id;
    $data['title'] = $r->title;
    $data['public_code'] = $r->public_code;
    // $data['tutor_name'] = $r->tutor;
    $data['lang_select_options'] = lang_select_options('localize', "class='form-control'",$r->lang);
    $data['rich_text_editor'] = rich_text_editor('description', 4, 20, $r->description);
    $data['check_mentor_edit'] = true;
    $data['startdate'] = $r->start_date;
    $data['enddate'] = $r->finish_date;
    $data['program_image'] = $r->program_image;
    $data['allow_unreg_mentee_from_program'] = $r->allow_unreg_mentee;
}

//update mentoring program
if(isset($_POST['edit_mentoring_program'])){

    if(isset($_POST['startdate']) and isset($_POST['enddate']) and $_POST['startdate'] > $_POST['enddate']){
        Session::flash('message',$langInvalidDates);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/mentoring/programs/edit_delete_program.php?edit='.getInDirectReference($mentoring_program_id));
    }

    if(empty($_POST['title']) or empty($_POST['code']) or !isset($_POST['mentoring_tutor'])){
        Session::flash('message',$langFieldsMissing);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/mentoring/programs/edit_delete_program.php?edit='.getInDirectReference($mentoring_program_id));
    }

    $uuser = $uid;
    $existing_program_id = $mentoring_program_id;
    $mentors_ids = array();
    $new_tutors = array();
    $title = $_POST['title'];
    $public_code = $_POST['code'];
    $new_tutors = isset($_POST['mentoring_tutor']) ? $_POST['mentoring_tutor'] : array();
    $language_mentoring_program = $_POST['localize'];
    $description = purify($_POST['description']);
    $allow_unreg_mentee_from_program = isset($_POST['yes_allow_unreg']) ? $_POST['yes_allow_unreg'] : 0;
    if(count($_POST['check_mentor']) == 1){
        foreach($_POST['check_mentor'] as $c){
            if($c == ''){
                $_POST['check_mentor'] = array();
            }
        }
    }
    if(isset($_POST['check_mentor']) and count($_POST['check_mentor']) > 0){
        foreach($_POST['check_mentor'] as $m_ids){
            $mentors_ids = explode(',', $m_ids);
        }
        if(count($mentors_ids) > 0){
            $mentors_ids = array_unique($mentors_ids);
        }
    }
    
    

    $start_date = $_POST['startdate'];
    $end_date = $_POST['enddate'];
    $keywords = '';
    $old_image_program = '';
    $tmp_name = '';
    $size = '';

    if($_FILES["image_mentoring_program"]["error"] == 4){//NO UPLOAD
        $program_image = show_mentoring_program_image($mentoring_program_code);
    }else{
        if(isset($_POST['old_program_image'])){
            $old_image_program = $_POST['old_program_image'];
        }
        $program_image = $_FILES['image_mentoring_program']['name'];
        $tmp_name = $_FILES["image_mentoring_program"]["tmp_name"];
        $size = $_FILES["image_mentoring_program"]["size"];
    }
    
    $old_tutors = array();
    $old_tutors = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user
                                                                                      WHERE mentoring_program_id = ?d
                                                                                      AND status = ?d
                                                                                      AND tutor = ?d",$mentoring_program_id,USER_TEACHER,1);

    $old_mentors = array();
    $old_mentors = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND status = ?d AND mentor = ?d",$existing_program_id,1,1);

    if(!empty($mentors_ids)){
            $old_men = array();
            foreach($old_mentors as $old){
                $old_men[] = $old->user_id;
            }
            $deleted_mentors = array_diff($old_men,$mentors_ids);
            $exist_new_mentor_as_old_mentor = 0;
            foreach($deleted_mentors as $del_m){
                if(!in_array($del_m,$mentors_ids)){
                    $exist_new_mentor_as_old_mentor++;
                }
            }
            $existing_mentors_groups = check_if_mentors_of_programs_participate_to_group_as_tutor_group($mentoring_program_id);
        
            $del_mentor_not_tutor_of_group = 0;
            if(count($deleted_mentors) > 0 and $exist_new_mentor_as_old_mentor > 0){
                foreach($deleted_mentors as $del_m){
                    if(in_array($del_m,$existing_mentors_groups)){
                        $del_mentor_not_tutor_of_group++;
                    }
                }
            }
            
            if($del_mentor_not_tutor_of_group == 0){
                 $updated_mentoring_program_id = update_mentoring_program($mentoring_program_code, $public_code, $language_mentoring_program, $title, $new_tutors, $start_date, $end_date,
                                                                        $keywords, $program_image, $description, $mentors_ids, $webDir, $existing_program_id, $uuser , $old_image_program, $tmp_name, $size,$old_tutors,$allow_unreg_mentee_from_program);
            }else{
                Session::flash('message',$langNotDeleteMentorAsTutorOfGroup);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/mentoring/programs/edit_delete_program.php?edit='.getInDirectReference($mentoring_program_id));
            }
    }else{
        Session::flash('message',$langNoMentorsAdded);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/mentoring/programs/edit_delete_program.php');
    }

    if($updated_mentoring_program_id == 1){

        //add new mentors in common group automatically
        $all_mentors_of_program = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user
                                                                WHERE mentoring_program_id = ?d
                                                                AND mentor = ?d",$mentoring_program_id,1);
        if(count($all_mentors_of_program) > 0){
            $theCommonGroupId = Database::get()->querySingle("SELECT id FROM mentoring_group
                                                                WHERE mentoring_program_id = ?d
                                                                AND common = ?d",$mentoring_program_id,1)->id;
            
            // delete old mentors from common group
            if(count($old_mentors) > 0){
                foreach($old_mentors as $um){
                    Database::get()->query("DELETE FROM mentoring_group_members 
                                            WHERE group_id = ?d 
                                            AND user_id = ?d",$theCommonGroupId, $um->user_id);

                }
            }

            //add new mentors in common group
            $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$theCommonGroupId,$mentoring_program_id)->max_members;
            foreach($all_mentors_of_program as $nm){
                $is_mentor_the_tutor_of_program = Database::get()->querySingle("SELECT tutor FROM mentoring_programs_user
                                                                                WHERE mentoring_program_id = ?d 
                                                                                AND user_id = ?d",$mentoring_program_id, $nm->user_id)->tutor;

                $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                        WHERE group_id = ?d
                                                                        AND is_tutor = ?d AND status_request = ?d",$theCommonGroupId,0,1)->ui;

                if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){
                    Database::get()->query("INSERT INTO mentoring_group_members SET
                                            group_id = ?d, user_id = ?d, is_tutor = ?d , status_request = ?d",$theCommonGroupId, $nm->user_id, $is_mentor_the_tutor_of_program, 1);
                }
            }


            
        }


        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_PROGRAM, MENTORING_LOG_MODIFY_PROGRAM, array('title' => $title,
                                                                                                                      'public_code' => $public_code,
                                                                                                                      'mentors' => $mentors_ids,
                                                                                                                    'type' => 'modify_program'));
        Session::flash('message',$langFaqEditSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('mentoring_programs/'.$mentoring_program_code.'/index.php');
    }else{
        Session::flash('message',$langNoEditMentoring);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/mentoring/programs/edit_delete_program.php?edit='.getInDirectReference($mentoring_program_id));
    }
    

}

if(isset($_POST['delete_mentoring_program'])){
    $title_program_del = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->title;
    $code_program_del = $mentoring_program_code;
    Mentoring_Log::record(0, MENTORING_MODULE_ID_PROGRAM, MENTORING_LOG_DELETE_PROGRAM, array('title' => $title_program_del,'code' => $code_program_del, 'type' => 'delete_program'));
    $del = delete_mentoring_program($mentoring_program_code, $mentoring_program_id, $webDir);
    if($del){
        Session::flash('message',$langDelMentoringComplete);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/mentoring/programs/show_programs.php');
    }else{
        Session::flash('message',$langNoEditMentoring);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('mentoring_programs/'.$mentoring_program_code.'/index.php');
    }
}

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
      'url' => $urlAppend.'mentoring_programs/'.$mentoring_program_code.'/index.php',
      'icon' => 'fa-chevron-left',
      'level' => 'primary-label',
      'button-class' => 'backButtonMentoring' ]
  ], false);

load_js('tools.js');
load_js('bootstrap-datetimepicker');


view('modules.mentoring.programs.edit_delete_program', $data);