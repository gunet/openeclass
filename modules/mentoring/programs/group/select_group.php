<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

$toolName = $langMentoringSpace;

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'mentoring_programs/'.$mentoring_program_code.'/index.php',
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);


view('modules.mentoring.programs.group.select_group', $data);
        
   
    





