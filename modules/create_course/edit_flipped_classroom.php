<?php

$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'edit_course';

require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) { // if we are not teachers or department managers
    redirect_to_home_page();
}

require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

$tmp_pageName = $langCourseEdit;

load_js('jstree3');
load_js('pwstrength.js');
load_js('tools.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */


$(document).ready(function() {
    var goals = document.getElementsByName('goals[]');
    var units = document.getElementsByName('units[]');

    var i = parseInt(goals.length) + 1;
    var j = parseInt(units.length) +1;
    $('#add_g').click(function() {
        if (i <= 20) {
            $('#dynamic_goals').append('<div class=\"col-12 d-inline-flex mb-4"\ id=\"row_g_' + i + '\"><label for=\"goal_' + i + '\" id=\"gtitle_'+i+'\" class="control-label-notes mt-2">' + i + ':</label><input type=\"text\" name=\"goals[]\" class=\"form-control w-100 ms-2\" value=\"\" placeholder=\"$langGoals\"><a href=\"#!\" class=\"btn_remove text-danger mt-2\" name=\"remove_g\" id=\"rm_g_' + i + '\"><span class=\"fa fa-minus-circle\"></span></a></div>')
            i++;
        }
        document.getElementById('goal_count').value = i-1;
    });
    $('#add_u').click(function() {
        if (j <= 20) {
            $('#dynamic_units').append('<div id=\"row_u_' + j + '\" class="col-12 d-inline-flex mb-4"><label for=\"unit_' + j + '\" id=\"utitle_'+j+'\" class="control-label-notes mt-2">' + j + ':</label><input type=\"text\" name=\"units[]\" class=\"form-control ms-2\" value=\"\" placeholder=\"$langUnits\"><a href=\"#!\" class=\"btn_remove text-danger mt-2\" name=\"remove_u\" id=\"rm_u_' + j + '\"><span class=\"fa fa-minus-circle\"></span></a></div>')
            j++;
        }
        document.getElementById('unit_count').value = j-1;
    });
    $(document).on('click', '.btn_remove', function() {
        var button_id = $(this).attr('id');
        var buttonName = $(this).attr('name');

        if (buttonName == "remove_g") {
            var confirm = window.confirm('" . js_escape($langConfirmDeleteGoal) . "');
            if (confirm) {
                var nrlz_button_id = button_id.split("_")
                i--;
                document.getElementById('goal_count').value = i-1;
                $('#row_g_' + nrlz_button_id[2] + '').remove();
                for (let k=parseInt(nrlz_button_id[2])+1; k <= i ; k++){
                    document.getElementById('rm_g_'+String(k)).setAttribute('id','rm_g_'+String(k-1));
                    document.getElementById('row_g_'+String(k)).setAttribute('id','row_g_'+String(k-1));
                    document.getElementById('gtitle_'+String(k)).setAttribute('for', 'goal_' + String(k-1));
                    document.getElementById('gtitle_'+String(k)).innerHTML = String(k-1) + ':';
                    document.getElementById('gtitle_'+String(k)).setAttribute('id', 'gtitle_' + String(k-1));

                }
            }
        } else if (buttonName == "remove_u") {
            var confirm = window.confirm('" . js_escape($langConfirmDeleteUnit) . "');
            if(confirm){
                var nrlz_button_id = button_id.split("_")
                j--;
                document.getElementById('unit_count').value = j-1;
                $('#row_u_' + nrlz_button_id[2] + '').remove();
                for (let k=parseInt(nrlz_button_id[2])+1; k <= j ; k++){
                    document.getElementById('rm_u_'+String(k)).setAttribute('id','rm_u_'+String(k-1));
                    document.getElementById('row_u_'+String(k)).setAttribute('id','row_u_'+String(k-1));
                    document.getElementById('utitle_'+String(k)).setAttribute('for', 'unit_' + String(k-1));
                    document.getElementById('utitle_'+String(k)).innerHTML = String(k-1) + ':';
                    document.getElementById('utitle_'+String(k)).setAttribute('id', 'utitle_' + String(k-1));

                }
            }
        }
    });
});

function hoursSum() {
    var lecthours = parseInt(document.getElementById('lecthours').value);
    var homehours = parseInt(document.getElementById('homehours').value);
    var numlect = parseInt(document.getElementById('lectnum').value);
    var totalhours = document.getElementById('totalhours');
    totalhours.value = (homehours*numlect) + (lecthours*numlect);
}

function checkedBoxes() {

    var checkboxes_in_class = document.getElementsByName('in_class');
    var checkboxes_after_class = document.getElementsByName('after_class');
    var checkboxes_in_home = document.getElementsByName('in_home');
    var checked_in_class = [];
    var checked_after_class = [];
    var checked_in_home = [];

    for(let i=0; i<parseInt(checkboxes_in_class.length); i++) {
        if(checkboxes_in_class[i].checked){
            checked_in_class.push(checkboxes[i].attr('id'));
        }
    }

    for(let j=1; j<parseInt(checkboxes_in_home.length); j++) {
        if(checkboxes_in_home[j].checked){
            checked_in_home.push(checkboxes[j].attr('id'));
        }
    }

    for(let k=1; k<parseInt(checkboxes_after_class.length); k++) {
        if(checkboxes_after_class[k].checked){
            checked_after_class.push(checkboxes[k].attr('id'));
        }
    }

    document.getElementsByName('checked_in_class').value=checked_in_class;
    document.getElementsByName('checked_in_home').value=checked_in_home;
    document.getElementsByName('checked_after_class').value=checked_after_class;
}

/* ]]> */
</script>
hContent;

register_posted_variables(array('title' => true, 'password' => true, 'prof_names' => true));
if (empty($prof_names)) {
    $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
}

if (!isset($_POST['next'])) {
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'url' => $urlServer."courses/$course_code/",
            'icon' => 'fa-reply',
            'level' => 'primary',
            'button-class' => 'btn-secondary'
        )
    ), false);
}else if(!isset($_POST['final_submit'])){
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'url' => $urlServer."modules/create_course/edit_flipped_classroom.php?course=$course_code&fromFlipped=1",
            'icon' => 'fa-reply',
            'level' => 'primary',
            'button-class' => 'btn-secondary'
        )
    ), false);
}


//Get all the user's info
//Get Class Info

$q1 = Database::get()->querySingle("SELECT student_number,lessons_number,lesson_hours,home_hours,total_hours FROM course_class_info WHERE course_code = ?s", $course_code);

$q2 = Database::get()->queryArray("SELECT title FROM course_learning_objectives WHERE course_code = ?s", $course_code);

$q3 = Database::get()->queryArray("SELECT count(*) AS num_goals FROM course_learning_objectives WHERE course_code = ?s", $course_code);

$q4 = Database::get()->querySingle("SELECT title,`description`,lectures_model FROM course WHERE id = ?d", $course_id);

$q5_a =  Database::get()->querySingle("SELECT id, lang, visible FROM course WHERE id=?s", $course_id);

$q5_b =  Database::get()->queryArray("SELECT id, title FROM course_units WHERE course_id = ?d", $course_id);

$q7 = Database::get()->querySingle("SELECT count(*) AS num_units FROM course_units WHERE course_id = ?d", $course_id);

$q8 = Database::get()->queryArray("SELECT id, title, comments, start_week, finish_week, visible, public, `order`, course_id FROM course_units WHERE course_id = ?d", $course_id);

$q9 = Database::get()->queryArray("SELECT course_code, activity_id, unit_id, tool_ids, activity_type, visible FROM course_units_activities WHERE course_code = ?s", $course_code);

$num_of_new_units = 0;

if (!isset($_POST['next'])) {
    $stuNum = $lectNum = $lectHours = $homeHours = $lectTotalHours = '';
     $tool_content .= "
     <div class='row'>
        
        <div class='col-lg-6 col-12'>
        <div class='form-wrapper form-edit border-0 px-0'>
        <form class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]?fromFlipped=1' onsubmit=\"return validateNodePickerForm();\">
            <fieldset>
                <div class='form-group'>
                    <label for='title' class='col-sm-12 control-label-notes mb-1'>$langTitle</label>
                    <div class='col-sm-12'>
                        <input name='title' id='title' type='text' class='form-control' value='" . q($q4->title) . "' placeholder='$langTitle' readonly>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='stunum' class='col-sm-12 control-label-notes mb-1'>$langStuNum</label>
                    <div class='col-sm-12'>
                        <input name='stunum' id='stunum' type='text' class='form-control' value='". (isset($q1->student_number)? q($q1->student_number) : "") ."' >
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='lectnum' class='col-sm-12 control-label-notes mb-1'>$langLectNum</label>
                    <div class='col-sm-12'>
                        <input name='lectnum' id='lectnum' type='number' min='1' max='50' class='form-control' value='" . (isset($q1->lessons_number)? q($q1->lessons_number) : "") . "' >
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='lecthours' class='col-sm-12 control-label-notes'>$langLectHours <small>($langHoursSmall)</small></label>
                    <div class='col-sm-12'>
                        <input name='lecthours' id='lecthours' type='number' min='1' max='150' class='form-control' value='" . (isset($q1->lesson_hours)? q($q1->lesson_hours) : "") . "' onchange='hoursSum()'>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='homehours' class='col-sm-12 control-label-notes mb-1'>$langHomeHours <small>($langHoursSmall)</small></label>
                    <div class='col-sm-12'>
                        <input name='homehours' id='homehours' type='number' min='1' max='150' class='form-control' value='" . (isset($q1->home_hours)? q($q1->home_hours) : "") . "' onchange='hoursSum()'>
                    </div>
                </div>        
                <div class='form-group mt-4'>
                    <label for='totalhours' class='col-sm-12 control-label-notes mb-1'>$langTotalHours</label>
                    <div class='col-sm-12'>
                        <input name='totalhours' id='totalhours' type='number' min='1' max='650' class='form-control' value='" . (isset($q1->total_hours)? q($q1->total_hours) : "") . "' readonly>
                    </div>
                </div>
            
                <div class='form-group mt-4'>
                    <div class='col-12 d-inline-flex mb-2'>
                        <label for='goals' class='control-label-notes me-2'>$langGoals:</label>
                        <a href='#!' name='add_g' id='add_g'>
                            <span class='fa fa-plus-circle add-unit-btn'>
                            </span>
                        </a>
                    </div>";

                    $count_goals =1;
                    foreach($q2 as $goal){

                        $tool_content .="<div class='col-12 d-inline-flex mb-4' id='row_g_".$count_goals."'>
                                            <label for='goal_$count_goals' class='control-label-notes mt-2' id='gtitle_$count_goals'>$count_goals: </label>
                                            <input name='goals[]' id='goal_$count_goals' type='text' class='form-control w-100 ms-2' value='".$goal->title."' placeholder='$langGoals'>
                                        ";
                                    if($count_goals > 1){

                                        $tool_content.="<a href='#!' class='btn_remove mt-2' name='remove_g' id='rm_g_".$count_goals."'>
                                                <span class='fa fa-minus-circle text-danger'>
                                                </span>
                                            </a>";

                                    }
                                    $tool_content .= "</div>";
                        $count_goals +=1;
                    }

                    $tool_content.="
                        <div id='dynamic_goals'></div>
                            
                        <input id='goal_count' type='hidden' name='goal_count' value='1'>
                    </div>
                    
                <div class='form-group mt-4'>
                    <label for='description' class='col-sm-12 control-label-notes mb-1'>$langCont <small>$langOptional</small>:</label>
                    <div class='col-sm-12'>
                          " .  rich_text_editor('description', 4, 20, purify($q4->description)) . "
                    </div>
                </div>";
                if($q4->lectures_model==2){
                    $tool_content .="<div class='form-group mt-4'>
                        <label for='localize' class='col-sm-12 control-label-notes mb-1'>$langLectModel:</label>
                        <div class='row'>
                            <div class='col-md-6 col-12 radio'>
                                <label>
                                    <input id='fromHome' type='radio' name='lectModel' value='2' checked>
                                        $langLectFromHome
                                </label>
                            </div>
                            <div class='col-md-6 col-12 radio'>
                                <label>
                                    <input id='eLearn' type='radio' name='lectModel' value='1'>
                                        $langLectMixed
                                </label>
                            </div>
                        </div>
                    </div>";
                }else{
                    $tool_content .="<div class='form-group mt-4'>
                        <label for='localize' class='col-sm-12 control-label-notes'>$langLectModel:</label>
                        <div class='row'>
                            <div class='col-md-6 col-12 radio'>
                                <label>
                                    <input id='fromHome' type='radio' name='lectModel' value='2' >
                                        $langLectFromHome
                                </label>
                            </div>
                            <div class='col-md-6 col-12 radio'>
                                <label>
                                    <input id='eLearn' type='radio' name='lectModel' value='1' checked>
                                        $langLectMixed
                                </label>
                            </div>
                        </div>
                    </div>";
                }
            $tool_content .="
                    <div class='form-group mt-4'>
                        <div class='col-12 mb-2'>
                            <label for='units' class='control-label-notes'>$langUnits:</label>
                        </div>
                    </div>
                    <div class='form-group'>
                    <div id='row_u_1'>";
                    $count_units =1;
                    if (count($q5_b) > 0) {
                        foreach ($q5_b as $unit) {
                            $tool_content .= "
                                            
                                            <div class='col-12 d-inline-flex mb-4' id='row_u_".$count_units."'>
                                            <label for='unit_$count_units' id='utitle_$count_units' class= 'control-label-notes mt-2'>$count_units: </label>
                                            <input name='units[]' id='unit_$count_units' type='text' class='form-control ms-2' value='" . $unit->title . "' placeholder='$langUnits'>
                                            <input name='ids[]' type='hidden' value='$unit->id'>";
                            if ($count_units == 1) {
                                $tool_content .= "<a href='#!' id='add_u' class='mt-2'>
                                                    <span class='fa fa-plus-circle add-unit-btn'></span>
                                                </a>";
                            } else {
                                $tool_content .= "<a href='#!' class='btn_remove p-0 mt-2 btn disabled' name='remove_u' id='rm_u_" . $count_units . "' disabled>
                                                    <span class='fa fa-minus-circle text-danger'></span>
                                                  </a>";
                            }
                            $tool_content .= "</div>";
                            $count_units += 1;
                        }
                    } else {
                        $tool_content .= "
                                        <label for='unit_$count_units' id='utitle_$count_units' class='control-label mt-2'>$count_units: </label>
                                        <div class='col-12 d-inline-flex mb-4' id='row_u_".$count_units."'>
                                        <input name='units[]' id='unit_$count_units' type='text' class='form-control w-100 ms-2' value='' placeholder='$langUnits'>";
                        $tool_content .= "<a href='#!' id='add_u'>
                                                                <span class='fa fa-plus-circle add-unit-btn'>
                                                                </span>
                                                            </a>";
                        $tool_content .= "</div>";
                        $count_units += 1;
                    }
                    $tool_content .="
                    <div id='dynamic_units'></div>
                    <input id='unit_count' type='hidden' name='unit_count' value='1'>
                </div>
                <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
                            <input class='btn submitAdminBtn' type='submit' name='next' value='" . q($langNext) . "'>
                            <a href='{$urlServer}courses/".$course_code."' class='btn cancelAdminBtn'>$langCancel</a>
                </div>
            </fieldset>" . generate_csrf_token_form_field() . "
        </form>
        </div>
        </div>
        <div class='col-lg-6 col-12 d-none d-lg-block text-end'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
        </div>";

} else if (!isset($_POST['final_submit'])){
    $_SESSION['lectures_model'] = $_POST['lectModel'];
    $_SESSION['stunum'] = $_POST['stunum'];
    $_SESSION['lectnum'] = $_POST['lectnum'];
    $_SESSION['lecthours'] = $_POST['lecthours'];
    $_SESSION['homehours'] = $_POST['homehours'];
    $_SESSION['totalhours'] = $_POST['totalhours'];

    $validationFailed = false;

    $_SESSION['units'] = $_POST['units'];
    $_SESSION['ids'] = $_POST['ids'];
    $_SESSION['goals'] =$_POST['goals'];
    $_SESSION['title'] = $_POST['title'];
    $_SESSION['description'] = purify($_POST['description']);

    $_SESSION['units_old'] = $_SESSION['units'];

    $num_of_new_units = count($_SESSION['units']);

    if ($num_of_new_units > $q7->num_units) { //new unit is added

            if (empty($_SESSION['goals']) or array_search("",$_SESSION['goals'])) {
                Session::flash('message', $langEmptyGoal);
                Session::flash('alert-class', 'alert-warning');
                $validationFailed = true;
            }

            if (empty($_SESSION['units']) or array_search("",$_SESSION['units'])) {
                Session::flash('message', $langEmptyUnit);
                Session::flash('alert-class', 'alert-warning');
                $validationFailed = true;
            }

            if ($validationFailed) {
                redirect_to_home_page("modules/create_course/edit_flipped_classroom.php?course=$course_code&fromFlipped=1");
            }
            $mtitles_in_home = $mtitles_in_class = $mtitles_after_class = $mids_in_home = $mids_in_class = $mids_after_class = array();

            $maxUnitId = Database::get()->querySingle("SELECT MAX(id) as muid FROM course_units");

            $act_list_in_home = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_HOME."'");

            $act_list_after_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_AFTER_CLASS."'");

            $act_list_in_class = Database::get()->queryArray("SELECT DISTINCT activity_ID FROM course_activities WHERE activity_type ='".MODULE_IN_CLASS."'");


            foreach ($act_list_in_home as $item_in_home) {
                if ($item_in_home->activity_ID == MODULE_ID_TC and !is_enabled_tc_server($course_id)) { // hide teleconference when no tc servers are enabled
                    continue;
                }

                $mid = getIndirectReference($item_in_home->activity_ID);
                $mtitle = q($activities[$item_in_home->activity_ID]['title']);

                $mtitles_in_home[$item_in_home->activity_ID] =$mtitle;
            }

            foreach ($act_list_after_class as $item_after_class) {
                if ($item_after_class->activity_ID == MODULE_ID_TC and !is_enabled_tc_server($course_id)) { // hide teleconference when no tc servers are enabled
                    continue;
                }
                $mid = getIndirectReference($item_after_class->activity_ID);
                $mtitle = q($activities[$item_after_class->activity_ID]['title']);

                $mtitles_after_class[$item_after_class->activity_ID]=$mtitle;

            }

            foreach ($act_list_in_class as $item_in_class) {
                if ($item_in_class->activity_ID == MODULE_ID_TC and !is_enabled_tc_server($course_id)) { // hide teleconference when no tc servers are enabled
                    continue;
                }
                $mid = getIndirectReference($item_in_class->activity_ID);
                $mtitle = q($activities[$item_in_class->activity_ID]['title']);

                $mtitles_in_class[$item_in_class->activity_ID]=$mtitle;

            }

            $tool_content .= " <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]?fromFlipped=1' onsubmit=\"return validateNodePickerForm();\">        
                <div class='card border-0'>
                    <div class='card-header border-0 px-0'>
                        <h3>
                            $langActSelect
                        </h3>
                    </div>
                </div>    
               
                <fieldset>
                    <div class='table-responsive mt-0'>
                        <table class='table table-default'>
                        <thead>
                        <tr class='list-header'><td></td>
                        <th class='px-0' scope='col'><label for='title' class='col-sm-2 '>$langActivities</th>
                ";
            $i=1;

            $count_ids = 0;

            foreach ($_SESSION['units'] as $utitle){
                if(!isset($_SESSION['ids'][$count_ids])){

                    $tool_content .= "
                        <th class='px-0' scope='col'><label for='title' class='col-md-10' title='$utitle'>".$i.' '.ellipsize($utitle,20).":</label></th>";
                    $i++;
                }
                $count_ids += 1;
            }

            $tool_content .= "</tr></thead><tr><th scope='row'>$langActInHome:</th>";

                $end=end($mtitles_in_home);
                foreach($mtitles_in_home as $title_home) {
                    $j=1;
                    $tool_content .= "<td>$title_home</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    $count_ids = 0;
                    foreach ($_SESSION['units'] as $utitle){
                        if(!isset($_SESSION['ids'][$count_ids])){
                             $tool_content .= "
                                  <td><label class='label-container'><input type='checkbox' name='in_home[]' id='".$j."_".$newUnitId."_".array_search($title_home,$mtitles_in_home)."' value='".$j."_".$newUnitId."_".array_search($title_home,$mtitles_in_home)."'></input><span class='checkmark'></span></label></td>";
                              $newUnitId ++;
                              $j++;
                        }
                        $count_ids +=1;
                    }
                    if($title_home == $end){
                        $tool_content .= "</tr><tr><td style='background-color:#ffffff;'></td>";
                    }else{
                        $tool_content .= "</tr><tr><td></td>";
                    }

                }

                $tool_content .="<td style='background-color:#ffffff;'></td>";
                    $count_ids =0;
                    foreach ($_SESSION['units'] as $utitle) {
                        if(!isset($_SESSION['ids'][$count_ids])){
                            $tool_content .="<td style='background-color:#ffffff;'></td>";
                        }
                        $count_ids += 1;
                    }

                $tool_content .= "</tr><tr><th scope='row'>$langActInClass:</th>";

                $end=end($mtitles_in_class);
                foreach($mtitles_in_class as $title_class) {
                    $k=1;
                    $tool_content .= "<td>$title_class</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    $count_ids = 0;
                    foreach ( $_SESSION['units'] as $utitle) {

                        if(!isset($_SESSION['ids'][$count_ids])){
                             $tool_content .= "
                                    <td><label class='label-container'><input type='checkbox' name='in_class[]' id='".$k."_".$newUnitId."_".array_search($title_class,$mtitles_in_class)."' value='".$k."_".$newUnitId."_".array_search($title_class,$mtitles_in_class)."'></input><span class='checkmark'></span></label></td>";
                             $newUnitId ++;
                                $k++;
                        }
                        $count_ids += 1;
                    }

                    if ($title_class == $end) {
                        $tool_content .= "</tr><tr><td style='background-color:#ffffff;'></td>";
                    } else {
                        $tool_content .= "</tr><tr><td></td>";
                    }
                }

                $tool_content .="<td style='background-color:#ffffff;'></td>
                    ";
                    $count_ids = 0;
                    foreach ($_SESSION['units'] as $utitle) {

                        if(!isset($_SESSION['ids'][$count_ids])){
                            $tool_content .="<td style='background-color:#ffffff;'></td>";
                        }
                        $count_ids += 1;
                    }

                $tool_content .= "</tr><tr><th scope='row'>$langActAfterClass:</th>";

                $end=end($mtitles_after_class);
                foreach($mtitles_after_class as $title_after_class) {
                    $z=1;
                    $tool_content .= "<td>$title_after_class</td>";
                    $newUnitId =$maxUnitId->muid +1;
                    $count_ids = 0;
                    foreach( $_SESSION['units'] as $utitle) {


                        if(!isset($_SESSION['ids'][$count_ids])){
                             $tool_content .= "
                                  <td><label class='label-container'><input type='checkbox' name='after_class[]' id='".$z."_".$newUnitId."_".array_search($title_after_class,$mtitles_after_class)."' value='".$z."_".$newUnitId."_".array_search($title_after_class,$mtitles_after_class)."'></input><span class='checkmark'></span></label></td>";
                              $newUnitId++;
                              $z++;
                        }
                        $count_ids += 1;
                    }
                    if($title_after_class == $end){
                        $tool_content .= "</tr><tr><td style='background-color:#f5f5f5;'></td>";
                    } else {
                        $tool_content .= "</tr><tr><td></td>";
                    }
                }

                $tool_content .="<td style='background-color:#f5f5f5;'></td>";
                    $count_ids =0;
                    foreach ($_SESSION['units'] as $utitle) {
                        if(!isset($_SESSION['ids'][$count_ids])){
                            $tool_content .="<td style='background-color:#f5f5f5;'></td>";
                        }
                        $count_ids += 1;
                    }


                $tool_content .= "</tr>
                        </table>
                    </div>
                    <div class='form-group mt-5 d-flex justify-content-center align-items-center gap-2'>
                        <input id='final_sub' class='btn submitAdminBtn' type='submit' name='final_submit' value='" . q($langFinalSubmit) . "'>
                        <a href='{$urlServer}courses/".$course_code."' class='btn cancelAdminBtn'>$langCancel</a>                       
                    </div>
                    <input type='hidden' name='next'>
                    <input name='checked_in_class' type='hidden' value='1'></input>
                    <input name='checked_in_home' type='hidden' value='2'></input>
                    <input name='checked_after_class' type='hidden' value='3'></input>

                </fieldset>". generate_csrf_token_form_field() ." 
            </form>
        </div>
        ";

    } else {
        if (empty($_SESSION['goals']) or array_search("",$_SESSION['goals'])) {
            Session::flash('message', $langEmptyGoal);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;
        }

        if (empty($_SESSION['units']) or array_search("",$_SESSION['units'])) {
            Session::flash('message', $langEmptyUnit);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;
        }


        if ($validationFailed) {
            redirect_to_home_page("modules/create_course/edit_flipped_classroom.php?course=$course_code&fromFlipped=1");
        }
        $commentsGoals = "";

        $stunum = $_SESSION['stunum'];
        $lectnum = $_SESSION['lectnum'];
        $lecthours = $_SESSION['lecthours'];
        $homehours = $_SESSION['homehours'];
        $totalhours = $_SESSION['totalhours'];

        $validationFailed = false;

        $validationFailed = false;
        if(empty($stunum)||empty($lectnum)||empty($lecthours)||empty($homehours)){
            Session::flash('message', $langFieldsMissing);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;
        }

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        if (empty($_SESSION['title'])) {
            Session::flash('message', $langFieldsMissing);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;
        }

        if ($validationFailed) {
            redirect_to_home_page("modules/create_course/edit_flipped_classroom.php?course=$course_code&fromFlipped=1");
        }

        $result = Database::get()->query(
            "UPDATE course SET
                            lectures_model = ?d, `description`=?s where id = ?d",
            $_SESSION['lectures_model'], $_SESSION['description'], $course_id
        );

        $result = Database::get()->query(
            "DELETE FROM course_learning_objectives WHERE course_code = ?s",
            $course_code
        );

        $result = Database::get()->query(
            "DELETE FROM course_description WHERE course_id = ?d",
            $course_id
        );

        $result = Database::get()->query(
            "DELETE FROM course_class_info WHERE course_code = ?s",
            $course_code
        );


        $i = 0;
        foreach ($q8 as $unit_info) {
            Database::get()->query(
                "UPDATE course_units SET title = ?s WHERE id = ?d",
                $_POST['units'][$i],
                $unit_info->id,
            );
            $i += 1;

        }
        $maxOrderGoal = Database::get()->querySingle("SELECT MAX(`order`) as morder FROM course_description WHERE course_id=?d", $course_id);


        if (empty($maxOrderGoal->morder)) {
            $maxOrderGoal->morder = 1;
        }

        $commentsGoals .= "<ul>";
        foreach ($_SESSION['goals'] as $goal){
            $commentsGoals .= "<li>$goal</li>";
            Database::get()->query("INSERT INTO course_learning_objectives SET
                    course_code = ?s,
                    title = ?s", $course_code, $goal
            );
        }
        $commentsGoals .= "</ul>";

        $commentLectModel ="<p>";

        if($_SESSION['lectures_model']==1){
            $commentLectModel .="$langLectMixed";

        }else if($_SESSION['lectures_model']==2){
            $commentLectModel .="$langLectFromHome";
        }

        $commentsClassInfo ="<ul>
                                <li>$langStuNum: $stunum </li>
                                <li>$langLectNum: $lectnum </li>
                                <li>$langLectHours: $lecthours </li>
                                <li>$langHomeHours: $homehours </li>
                                <li>$langTotalHours: $totalhours</li>
                            </ul>";



        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langGoals, purify($commentsGoals), 2, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langLectModel, purify($commentLectModel), 10, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langClassInfoTitle, purify($commentsClassInfo), 10, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_class_info SET
                    student_number = ?s,
                    lessons_number = ?d,
                    lesson_hours = ?d,
                    home_hours = ?d,
                    total_hours = ?d,
                    course_code =?s", $stunum, $lectnum, $lecthours, $homehours, $totalhours, $course_code
        );

        $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span><b>$langJustEdited:</b> " . q($_SESSION['title']) . "<br></span></div>";
        $tool_content .= action_bar(array(
            array(
                'title' => $langEnter,
                'url' => $urlAppend . "courses/$course_code/",
                'icon' => 'fa-arrow-right',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            )
        ));

        // logging
        Log::record(0, 0, LOG_MODIFY_COURSE, array(
            'id' => $course_id,
            'code' => $course_code,
            'title' => $_SESSION['title'],
            'language' => $q5_a->lang,
            'visible' => $q5_a->visible
        ));

    }
}else if(!isset($_POST['agenda'])){

        $commentsGoals = "";

        $stunum = $_SESSION['stunum'];
        $lectnum = $_SESSION['lectnum'];
        $lecthours = $_SESSION['lecthours'];
        $homehours = $_SESSION['homehours'];
        $totalhours = $_SESSION['totalhours'];

        $validationFailed = false;

        $validationFailed = false;
        if(empty($stunum)||empty($lectnum)||empty($lecthours)||empty($homehours)){
            Session::flash('message', $langFieldsMissing);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;

        }

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        if (empty($_SESSION['title'])) {
            Session::flash('message', $langFieldsMissing);
            Session::flash('alert-class', 'alert-warning');
            $validationFailed = true;
        }

        if ($validationFailed) {
            redirect_to_home_page("modules/create_course/edit_flipped_classroom.php?course=$course_code&fromFlipped=1");
        }

        $result = Database::get()->query(
            "UPDATE course SET lectures_model = ?d, `description`=?s
                    WHERE id = ?d",
            $_SESSION['lectures_model'], $_SESSION['description'], $course_id
        );

        $result = Database::get()->query(
            "DELETE FROM course_learning_objectives WHERE course_code = ?s",
            $course_code
        );

        $result = Database::get()->query(
            "DELETE FROM course_description WHERE course_id = ?d",
            $course_id
        );

        $result = Database::get()->query(
            "DELETE FROM course_class_info WHERE course_code = ?s",
            $course_code
        );

        $maxOrderUnit = Database::get()->querySingle("SELECT MAX(`order`) as morder FROM course_units WHERE course_id=?d",$course_id);

        if (is_null($maxOrderUnit->morder)) {
            $maxOrderUnit->morder = 1;
        }

        $existing_units = 0;
        $changed_units = array("start");
        foreach ($q5_b as $unit){
            if ($unit->title != $_SESSION['units'][$existing_units]){
                $result = Database::get()->query(
                    "UPDATE course_units SET
                                    title = ?s WHERE (title=?s AND course_id=?d)",
                    $_SESSION['units'][$existing_units], $unit->title, $course_id
                );
                array_push($changed_units,$_SESSION['units'][$existing_units]);
            }
            $existing_units += 1;
        }

        foreach ($_SESSION['units'] as $unit){
            if (!array_search($unit, $changed_units)){
                $q = Database::get()->querySingle("SELECT id FROM course_units WHERE title=?s and course_id=?d",$unit,$course_id);
                if (!$q){
                    $maxOrderUnit->morder += 1;
                    Database::get()->query(
                        "INSERT INTO course_units SET
                                                title = ?s,
                                                visible = 1,
                                                public = 1,
                                                `order` = ?d,
                                                course_id = ?d",
                        $unit,
                        $maxOrderUnit->morder,
                        $course_id,
                    );
                }
            }
        }

        $maxOrderGoal = Database::get()->querySingle("SELECT MAX(`order`) as morder FROM course_description WHERE course_id=?d",$course_id);

        if (empty($maxOrderGoal->morder)){
            $maxOrderGoal->morder = 1;
        }

        $commentsGoals .= "<ul>";
        foreach ($_SESSION['goals'] as $goal){
            $commentsGoals .= "<li>".$goal."</li>";
            Database::get()->query("INSERT INTO course_learning_objectives SET
                    course_code = ?s,
                    title = ?s", $course_code, $goal
            );
        }
        $commentsGoals .= "</ul>";

        $commentLectModel = "<p>";

        if ($_SESSION['lectures_model']==1){
            $commentLectModel .="$langLectMixed";

        } else if($_SESSION['lectures_model']==2) {
            $commentLectModel .="$langLectFromHome";
        }

        $commentsClassInfo ="<ul>
                                <li>$langStuNum: $stunum </li>
                                <li>$langLectNum: $lectnum </li>
                                <li>$langLectHours: $lecthours </li>
                                <li>$langHomeHours: $homehours </li>
                                <li>$langTotalHours: $totalhours</li>
                            </ul>
        ";

        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langGoals, purify($commentsGoals), 2, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langLectModel, purify($commentLectModel), 10, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = ?d,
                    `order` = ?d,
                    `visible` =?d,
                    update_dt = NOW()", $course_id, $langClassInfoTitle, purify($commentsClassInfo), 10, $maxOrderGoal->morder, 1
        );

        Database::get()->query("INSERT INTO course_class_info SET
                    student_number = ?s,
                    lessons_number = ?d,
                    lesson_hours = ?d,
                    home_hours = ?d,
                    total_hours = ?d,
                    course_code = ?s", $stunum, $lectnum, $lecthours, $homehours, $totalhours, $course_code
        );

        $nrlz_tools_in_class ="";
        $nrlz_tools_in_home ="";
        $nrlz_tools_after_class = "";


        if(isset($_POST['in_class'])){
            foreach($_POST['in_class'] as $in_class){
                $nrlz_in_class = explode("_",$in_class);

                $activity_id = $nrlz_in_class[2];
                $unit_id = $nrlz_in_class[1];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_in_class .=$ids." ";
                }


                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type = ?d,
                                                visible = ?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_in_class,
                    0,
                    1
                );

                $nrlz_tools_in_class ="";

            }
        }

        if(isset($_POST['in_home'])){
            foreach($_POST['in_home'] as $in_home){
                $nrlz_in_home = explode("_",$in_home);

                $activity_id = $nrlz_in_home[2];
                $unit_id = $nrlz_in_home[1];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_in_home .=$ids." ";
                }


                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type=?d,
                                                visible=?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_in_home,
                    1,
                    1
                );
                $nrlz_tools_in_home ="";
            }
        }

        if(isset($_POST['after_class'])){
            foreach($_POST['after_class'] as $after_class){
                $nrlz_after_class = explode("_",$after_class);

                $activity_id = $nrlz_after_class[2];
                $unit_id = $nrlz_after_class[1];

                $tool_ids = $activities[$activity_id]['tools'];

                foreach ($tool_ids as $ids){
                    $nrlz_tools_after_class .=$ids." ";
                }


                Database::get()->query(
                    "INSERT INTO course_units_activities SET
                                                course_code = ?s,
                                                activity_id = ?s,
                                                unit_id = ?d,
                                                tool_ids = ?s,
                                                activity_type=?d,
                                                visible=?d",
                    $course_code,
                    $activity_id,
                    $unit_id,
                    $nrlz_tools_after_class,
                    2,
                    1
                );
                $nrlz_tools_after_class ="";
            }
        }

        $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span><b>$langJustEdited:</b> " . q($_SESSION['title']) . "<br></span></div>";
        $tool_content .= action_bar(array(
            array(
                'title' => $langEnter,
                'url' => $urlAppend . "courses/$course_code/",
                'icon' => 'fa-arrow-right',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            )
        ));

        // logging
        Log::record(0, 0, LOG_MODIFY_COURSE, array(
            'id' => $course_id,
            'code' => $course_code,
            'title' => $_SESSION['title'],
            'language' => $q5_a->lang,
            'visible' => $q5_a->visible
        ));
}

draw($tool_content, 1, null, $head_content);
