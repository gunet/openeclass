<?php

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-tutor').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

// get mentor id
if(isset($_GET['showcalMentor']) and isset($_GET['showGroup'])){


    if((isset($_GET['showGroup']) and intval(getDirectReference($_GET['showGroup'])) == 0)
        or (isset($_GET['showcalMentor']) and intval(getDirectReference($_GET['showcalMentor'])) == 0)){
            after_reconnect_go_to_mentoring_homepage();
    }

    $group_id = getDirectReference($_GET['showGroup']);
    $data['group_id'] = $group_id;

    $mentor_id = getDirectReference($_GET['showcalMentor']);
    $data['mentor_id'] = $mentor_id;
    
    $MentorGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$mentor_id)->givenname;
    $MentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$mentor_id)->surname;

    $toolName = $langAvailableDatesContact.' '.$MentorGivenname.' '.$MentorSurname;

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

}

$data['action_bar'] = action_bar([
    
    [ 'title' => trans('langBackPage'),
      'url' => $urlAppend.'modules/mentoring/programs/group/group_space.php?space_group_id='.getIndirectReference($group_id),
      'icon' => 'fa-chevron-left',
      'level' => 'primary-label',
      'button-class' => 'backButtonMentoring' ]
  ], false);

view('modules.mentoring.programs.show_calendar_mentor_rentezvous', $data);


