<?php

$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

$toolName = $langListPrograms;

if(isset($_POST['delete_program'])){

    $del = delete_mentoring_program($_POST['del_program_code'], $_POST['del_program_id'], $webDir);
    if($del){
        Session::flash('message',$langDelMentoringComplete);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langNoEditMentoring);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/admin/mentoring_list_programs.php');
}


$data['action_bar'] = action_bar([
    [ 'title' => trans('langBack'),
        'url' => $urlServer.'modules/admin/mentoring_platform_enable.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'button-class' => 'btn-secondary']
    ], false);

$data['all_programs'] = Database::get()->queryArray("SELECT *FROM mentoring_programs");

view('admin.mentoring_platform.list_programs', $data);
