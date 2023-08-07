<?php

$require_login = TRUE;

require_once '../../include/baseTheme.php';


$toolName = $langMentoringPlatform;

if(isset($_POST['enable_disable_mentoring'])){

    $html_message = "";
    $html_message_mentor = "";
    $html_message_mentor_exist = "";
    $users = array();
    $mentor_cant_be_tutor = 0;
    $mentor_as_tutor_exist = 0;

    set_config('mentoring_always_active', $_POST['always_active_mentoring']);
    set_config('mentoring_platform', $_POST['enable_mentoring']);

    

    if(isset($_POST['mentor_as_tutorProgram'])){
        if($_POST['mentor_as_tutorProgram'] == 1){
            $checkIfMentorIsTutorProgram = Database::get()->queryArray("SELECT *FROM mentoring_programs_user
                                                                    WHERE tutor = ?d
                                                                    AND status = ?d
                                                                    AND user_id IN (SELECT id FROM user WHERE is_mentor = ?d)",1,USER_TEACHER,1);
            if(count($checkIfMentorIsTutorProgram) > 0){
                $mentor_cant_be_tutor = 1;
                $html_message_mentor .= "<ul class='mt-3'>";
                foreach($checkIfMentorIsTutorProgram as $ch){
                    $nameMentor = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$ch->user_id)->givenname;
                    $surnameMentor = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$ch->user_id)->surname;

                    $html_message_mentor .= "
                                            <li>
                                                <div>
                                                    $nameMentor $surnameMentor
                                                </div>
                                            </li>
                                        ";
                }
                $html_message_mentor .= "</ul>";
                
            }else{
                set_config('mentoring_mentor_as_tutorProgram',$_POST['mentor_as_tutorProgram']);
            }
        }else{
            $checkIfMentorExistAsTutor = Database::get()->queryArray("SELECT *FROM mentoring_programs_user
                                                                    WHERE tutor = ?d
                                                                    AND status = ?d
                                                                    AND user_id IN (SELECT id FROM user WHERE is_mentor = ?d)",1,USER_TEACHER,1);
            if(count($checkIfMentorExistAsTutor) > 0){
                $mentor_as_tutor_exist = 1;
                $html_message_mentor_exist .= "<ul class='mt-3'>";
                foreach($checkIfMentorExistAsTutor as $ch){
                    $nameMentorExist = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$ch->user_id)->givenname;
                    $surnameMentorExist = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$ch->user_id)->surname;

                    $html_message_mentor_exist .= "
                                            <li>
                                                <div>
                                                    $nameMentorExist $surnameMentorExist
                                                </div>
                                            </li>
                                        ";
                }
                $html_message_mentor_exist .= "</ul>";
            }

            set_config('mentoring_mentor_as_tutorProgram',$_POST['mentor_as_tutorProgram']);
        }
    }
    
    
    

    if(isset($_POST['tutor_as_mentee']) and $_POST['tutor_as_mentee'] == 0){
        $users = Database::get()->queryArray("SELECT mentoring_program_id,user_id FROM mentoring_programs_user
                                                WHERE user_id IN (SELECT id FROM user WHERE status = ?d)
                                                AND tutor = ?d AND mentor = ?d AND is_guided = ?d",USER_TEACHER,0,0,1);
        if(count($users) > 0){
            $html_message .= "<ul>";
            foreach($users as $u){
                $name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$u->user_id)->givenname;
                $surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$u->user_id)->surname;

                $program = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$u->mentoring_program_id)->title;
                $program_code = Database::get()->querySingle("SELECT code FROM mentoring_programs WHERE id = ?d",$u->mentoring_program_id)->code;


                $html_message .= "<li>
                                    <div class='d-flex'>
                                        $name $surname
                                        <a class='ms-2' href='{$urlAppend}mentoring_programs/$program_code/index.php'>$program</a>
                                    </div>
                                  </li>";
            }
            $html_message .= "</ul>";
            Session::flash('message',$langNoTutorAsMenteeFromAdminTool.$html_message); 
            Session::flash('alert-class', 'alert-warning');
        }else{//1
            set_config('mentoring_tutor_as_mentee', 0);
        }
    }else if(isset($_POST['tutor_as_mentee']) and $_POST['tutor_as_mentee'] == 1){
        set_config('mentoring_tutor_as_mentee', 1);
    }

    if(!get_config('mentoring_platform')){
        set_config('mentoring_always_active', 0);
    }

    if(count($users) == 0){
        Session::flash('message',$langFaqEditSuccess); 
        Session::flash('alert-class', 'alert-success');
    }
    
    if($mentor_cant_be_tutor == 1){
        Session::flash('message',$langNoMentorAsTutorProgram.$html_message_mentor); 
        Session::flash('alert-class', 'alert-warning');
    }

    if($mentor_as_tutor_exist == 1){
        Session::flash('message',$langMentorAsTutorExistMsg.$html_message_mentor_exist); 
        Session::flash('alert-class', 'alert-info');
    }

    if(get_config('mentoring_always_active')){
        redirect_to_home_page("modules/admin/mentoring_platform_enable.php?goToMentoring=true");
    }
    if(!get_config('mentoring_always_active')){
        redirect_to_home_page("modules/admin/mentoring_platform_enable.php?goToMentoring=false");
    }
    
}

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBack'),
        'url' => $urlServer.'modules/admin/index.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary'],
    [ 'title' => trans('langTextHomePage'),
        'url' => $urlServer.'modules/admin/mentoring_homepageTexts_create.php',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary',
        'show' => (get_config('mentoring_platform') and get_config('mentoring_platform') == 1)],
    [ 'title' => trans('langAddMentors'),
        'url' => $urlServer.'modules/admin/listusers.php?search=yes',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary',
        'show' => (get_config('mentoring_platform') and get_config('mentoring_platform') == 1)],
    [ 'title' => trans('langAddfiltersformentor').' -- '.trans('langListFilters'),
        'url' => $urlServer.'modules/admin/mentoring_mentor_filters.php',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary',
        'show' => (get_config('mentoring_platform') and get_config('mentoring_platform') == 1)],
    [ 'title' => trans('langRelationshipMentorsFilter'),
        'url' => $urlServer.'modules/admin/mentoring_mentor_filters_relationship.php',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary',
        'show' => (get_config('mentoring_platform') and get_config('mentoring_platform') == 1)],
    [ 'title' => trans('langListPrograms'),
        'url' => $urlServer.'modules/admin/mentoring_list_programs.php',
        'icon' => 'fa-times',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary',
        'show' => (get_config('mentoring_platform') and get_config('mentoring_platform') == 1)
    ]
    
    ], false);

view('admin.mentoring_platform.enable', $data);


