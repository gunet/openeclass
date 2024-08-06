<?php


$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'portfolio';
$helpSubTopic = 'create_course';

require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) { // if we are not teachers or department managers
    redirect_to_home_page();
}

require_once 'include/log.class.php';
require_once 'functions.php';

$toolName = $langCourseCreate;

load_js('tools.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */


$(document).ready(function() {
    var i = 2;
    var j = 2;
    $('#add_g').click(function() {
        if (i <= 20) {
            
            $('#dynamic_goals').append('<div id=\"row_g_' + i + '\" class="col-12 d-inline-flex mt-3"><label for=\"goal_' + i + '\" id=\"gtitle_'+i+'\"  class= "control-label-notes" >' + i + ':</label><input type=\"text\" name=\"goals[]\" class=\"form-control ms-2\" value=\"\" placeholder=\"$langGoals\"><a href=\"#!\" class=\"btn_remove text-danger\" name=\"remove_g\" id=\"rm_g_' + i + '\"><span class=\"fa fa-minus-circle \"></span></a></div>')
            i++;
        }
        document.getElementById('goal_count').value = i-1;
    });
    $('#add_u').click(function() {
        if (j <= 20) {
            
            $('#dynamic_units').append('<div id=\"row_u_' + j + '\" class="col-12 d-inline-flex mt-3"><label for=\"unit_' + j + '\" id=\"utitle_'+j+'\"class= "control-label-notes"  >' + j + ':</label><input type=\"text\" name=\"units[]\" class=\"form-control ms-2\" value=\"\" placeholder=\"$langUnits\"><a href=\"#!\" class=\"btn_remove text-danger\" name=\"remove_u\" id=\"rm_u_' + j + '\"><span class=\"fa fa-minus-circle\"></span></a></div>')
            j++;
        }
        document.getElementById('unit_count').value = j-1;
    });
    $(document).on('click', '.btn_remove', function() {
        
        var button_id = $(this).attr('id');
        var buttonName = $(this).attr('name');
        
        if (buttonName=="remove_g"){
            var confirm = window.confirm('$langConfirmDeleteGoal');
            if(confirm){
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
        } else {
            var confirm = window.confirm('$langConfirmDeleteUnit');
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
    var totalhourshid = document.getElementById('total_hours_hid');
    totalhours.value = (homehours*numlect) + (lecthours*numlect);
    totalhourshid.value = (homehours*numlect) + (lecthours*numlect);
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
            'url' => $urlServer.'modules/create_course/create_course.php',
            'icon' => 'fa-reply',
            'level' => 'primary',
            'button-class' => 'btn-secondary'
        )
    ), false);
}

if (!isset($_POST['next'])) {

    $stuNum = $lectNum = $lectHours = $homeHours = $lectTotalHours ='';
     $tool_content .= "
     <h1 aria-label='$toolName'></h1>
     <div class='row'>
       
        <div class='col-lg-6 col-12'>
        <div class='form-wrapper form-edit border-0 px-0'>
        <form class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]'>
            <fieldset>
                <div class='form-group'>
                    <label for='title' class='col-sm-6 control-label-notes'>$langTitle</label>
                    <div class='col-sm-12'>
                        <input name='title' id='title' type='text' class='form-control pe-none greyColor text-info fw-bold' value='" . q($_SESSION['title']) . "' placeholder='$langTitle' readonly>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='stunum' class='col-sm-12 control-label-notes'>$langStuNum</label>
                    <div class='col-sm-12'>  
                            <input name='stunum' id='stunum' type='text' class='form-control' value='".q($stuNum)."' >
                    </div>
                </div>
                <div class='form-group mt-4'>                    
                    <label for='lectnum' class='col-sm-12 control-label-notes'>$langLectNum</label>
                    <div class='col-sm-12'>

                        <input name='lectnum' id='lectnum' type='number' min='1' max='50' class='form-control' value='".q($lectNum)."' >
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='lecthours' class='col-sm-12 control-label-notes'>$langLectHours <small>($langHoursSmall)</small></label>
                    <div class='col-sm-12'>
                        <input name='lecthours' id='lecthours' type='number' min='1' max='150' class='form-control' value='".q($lectHours)."' onchange='hoursSum()' >
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='homehours' class='col-sm-12 control-label-notes'>$langHomeHours <small>($langHoursSmall)</small></label>
                    <div class='col-sm-12'>
                        <input name='homehours' id='homehours' type='number' min='1' max='150' class='form-control' value='".q($homeHours)."' onchange='hoursSum()' >
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='totalhours' class='col-sm-12 control-label-notes'>$langTotalHours</label>
                    <div class='col-sm-12'>
                        <input name='totalhours' id='totalhours' type='number' min='1' max='650' class='form-control' value='".q($lectTotalHours)."' readonly><input type='hidden' id= 'total_hours_hid' name='total_hours_hid' value=''>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-12 d-inline-flex mb-2'>
                        <div class='control-label-notes me-2'>$langGoals</div>
                        <a href='#!' name='add_g' id='add_g' aria-label='$langAdd'>
                            <span class='fa fa-plus-circle add-unit-btn'>
                            </span>
                        </a>
                    </div>
                        
                    <div id='row_g_1' class='col-12 d-inline-flex'>
                        <label for='goal_1' id='gtitle_1' class= 'control-label-notes'>1: </label>
                        <input name='goals[]' id='goal_1' type='text' class='form-control w-100 ms-2' value='"."' placeholder='$langGoals'>
                    </div>   
                    <div id='dynamic_goals'></div>
                    <input id='goal_count' type='hidden' name='goal_count' value='1'>
                </div>                                    
                <div class='form-group mt-4'>
                    <label for='description' class='col-sm-12 control-label-notes'>$langCont <small>$langOptional</small></label>
                    <div class='col-sm-12'>
                          " .  rich_text_editor('description', 4, 20, purify($_SESSION['description'])) . "
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-sm-12 control-label-notes mb-1'>$langLectModel</div>
                    <div class='row'>
                        <div class='col-md-6 col-12 radio'>

                            <label>
                                <input id='fromHome' type='radio' name='lectModel' value='2'>
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
                </div>
                <div class='form-group mt-4'>
                    <div class='col-12 d-inline-flex mb-2'>
                        <div class='control-label-notes me-2'>$langUnits</div>
                        <a href='#!' name='add_u' id='add_u' aria-label='$langAdd'>
                            <span class='fa fa-plus-circle add-unit-btn'>
                            </span>
                        </a>
                    </div>
                    <div id='row_u_1' class='col-12 d-inline-flex'>
                        <label for='unit_1' id='utitle_1' class='control-label-notes'>1: </label>
                        <input name='units[]' id='unit_1' type='text' class='form-control w-100 ms-2' value='"."' placeholder='$langUnits'>               
                    </div>

                    <div id='dynamic_units'></div>

                    <input id='unit_count' type='hidden' name='unit_count' value='1'>     
                </div>
                <div class='form-group mt-5 d-flex justify-content-end align-items-center'>                                       
                       <a href='{$urlServer}main/portfolio.php' class='btn cancelAdminBtn me-1'>$langCancel</a>
                       <input class='btn submitAdminBtn' type='submit' name='next' value='" . q($langNext) . "&nbsp;&nbsp;&raquo;'>
                </div>     
            </fieldset>" . generate_csrf_token_form_field() . "
        </form>
        </div></div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div></div>";

} else if (!isset($_POST['final_submit'])) {
    $_SESSION['lectures_model'] = $_POST['lectModel'];
    $_SESSION['content'] = $_POST['description'];
    $_SESSION['stunum'] = $_POST['stunum'];
    $_SESSION['lectnum'] = $_POST['lectnum'];
    $_SESSION['lecthours'] = $_POST['lecthours'];
    $_SESSION['homehours'] = $_POST['homehours'];
    $_SESSION['totalhours'] = $_POST['total_hours_hid'];

    $validationFailed = false;
    $_SESSION['units'] = $_POST['units'];
    $_SESSION['goals'] = $_POST['goals'];
    redirect_to_home_page('modules/create_course/course_units_activities.php');
}

draw($tool_content, 1, null, $head_content);
