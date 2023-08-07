<?php

$require_login = TRUE;

require_once '../../include/baseTheme.php';


$toolName = $langRelationshipMentorsFilter;

load_js('select2');

$head_content .= "
<script>
    $(function () {
        $('#select-mentors').select2();
    });
</script>";

if(isset($_POST['submitSkills'])){
    if(!empty($_POST['skills']) and !empty($_POST['mentor_ids'])){
        foreach($_POST['mentor_ids'] as $mentor_id){
            $mentor_id = getDirectReference($mentor_id);
            // foreach($_POST['skills'] as $skill_id){
            //     //$specialization_id = Database::get()->querySingle("SELECT specialization_id FROM mentoring_skills WHERE id = ?d",$skill_id)->specialization_id;
            //     $specialization_id = Database::get()->querySingle("SELECT specialization_id FROM mentoring_specializations_skills
            //                                                         WHERE skill_id = ?d",$skill_id)->specialization_id;
            //     $checkIfExist = Database::get()->querySingle("SELECT *FROM mentoring_mentor_skills WHERE specialization_id = ?d AND skill_id = ?d AND user_id = ?d",$specialization_id,$skill_id,$mentor_id);
            //     if(!$checkIfExist){
            //         $insert = Database::get()->query("INSERT INTO mentoring_mentor_skills SET specialization_id = ?d, skill_id = ?d, user_id = ?d",$specialization_id,$skill_id,$mentor_id);
            //     }
            // }

            foreach($_POST['skills'] as $specialization_id => $key){
                foreach($key as $skill_id){
                    $checkIfExist = Database::get()->querySingle("SELECT *FROM mentoring_mentor_skills WHERE specialization_id = ?d AND skill_id = ?d AND user_id = ?d",$specialization_id,$skill_id,$mentor_id);
                    if(!$checkIfExist){
                        $insert = Database::get()->query("INSERT INTO mentoring_mentor_skills SET specialization_id = ?d, skill_id = ?d, user_id = ?d",$specialization_id,$skill_id,$mentor_id);
                    }
                }
            }

        }
        if($insert){
            Session::flash('message',$langformSubmitSuccess); 
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langformSubmitNoSuccess); 
            Session::flash('alert-class', 'alert-danger');
        }
        
    }else{
        Session::flash('message',$langformSubmitWarning); 
        Session::flash('alert-class', 'alert-warning');
    }

    redirect_to_home_page('modules/admin/mentoring_mentor_filters_relationship.php');
}

if(isset($_POST['deleteUserMentorFromSkill'])){

    $mentor_participate_to_program = Database::get()->queryArray("SELECT *FROM mentoring_programs_user
                                                                    WHERE user_id = ?d AND mentor = ?d",$_POST['UserdelMentorFromFilter'],1);
    
    if(count($mentor_participate_to_program) == 0){
        $del = Database::get()->query("DELETE FROM mentoring_mentor_skills WHERE id = ?d",$_POST['delMentorFromFilter']);
        if($del){
            Session::flash('message',$langMentorDelFromSkillSuccess); 
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langDelSpecializationNoSuccessMsg); 
            Session::flash('alert-class', 'alert-warning');
        }
    }else{
        Session::flash('message',$langDelSpecializationNoSuccessMsgMentor); 
        Session::flash('alert-class', 'alert-danger');
    }

    

    redirect_to_home_page('modules/admin/mentoring_mentor_filters_relationship.php');
}

$data['all_mentors'] = Database::get()->queryArray("SELECT *FROM user WHERE is_mentor = ?d",1);

$data['list_specializations'] = Database::get()->queryArray("SELECT *FROM mentoring_specializations");

$data['list_skills_mentors'] = Database::get()->queryArray("SELECT DISTINCT specialization_id,skill_id FROM mentoring_mentor_skills");

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBack'),
        'url' => $urlServer.'modules/admin/mentoring_platform_enable.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary']
    ], false);

view('admin.mentoring_platform.mentoring_mentor_filters_relationship', $data);


