<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * Edit, Course Description
 *
 */

$require_current_course = TRUE;
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'modules/units/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';

$toolName = $langEditCourseProgram;
$pageName = $langCourseProgram;

$course = Database::get()->querySingle('SELECT description, home_layout, course_image FROM course WHERE id = ?d', $course_id);

if (isset($_GET['delete_image'])) { 
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    Database::get()->query("UPDATE course SET course_image = NULL WHERE id = ?d", $course_id);
    unlink("$webDir/courses/$course_code/image/$course->course_image");
    redirect_to_home_page('modules/course_home/editdesc.php');
} elseif (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $db_vars = array(purify($_POST['description']), $_POST['layout']);
    $extra_sql = '';
    if (isset($_FILES['course_image']) && is_uploaded_file($_FILES['course_image']['tmp_name'])) {
        $file_name = $_FILES['course_image']['name'];
        validateUploadedFile($file_name, 2);
        $i=0;
        while (is_file("$webDir/courses/$course_code/image/$file_name")) {
            $i++;
            $name = pathinfo($file_name, PATHINFO_FILENAME);
            $ext =  get_file_extension($file_name);
            $file_name = "$name-$i.$ext";
        }
        $file_name = php2phps($file_name);
        move_uploaded_file($_FILES['course_image']['tmp_name'], "$webDir/courses/$course_code/image/$file_name");
        require_once 'modules/admin/extconfig/externals.php';
        $connector = AntivirusApp::getAntivirus();
        if($connector->isEnabled() == true ){
            $output=$connector->check("$webDir/courses/$course_code/image/$file_name");
            if($output->status==$output::STATUS_INFECTED){
                AntivirusApp::block($output->output);
            }
        }
        $extra_sql = ", course_image = ?s";
        array_push($db_vars, $file_name);
    }
    array_push($db_vars, $course_id);
    Database::get()->query("UPDATE course SET description = ?s, home_layout = ?d$extra_sql WHERE id = ?d", $db_vars);
    // update index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    header("Location: {$urlServer}courses/$course_code");
    exit;
}
    $head_content .= "
    <script>
        $(function(){
            $('select[name=layout]').change(function ()
            {
                if($(this).val() == 1) {
                    $('#image_field').removeClass('hidden');
                } else {
                    $('#image_field').addClass('hidden');
                }
            });          
        });
    </script>";        
$layouts = array(1 => $langCourseLayout1, 3 => $langCourseLayout3);
$description = $course->description;
$layout = $course->home_layout;


if (isset($course->course_image)) {
    $course_image = "
        <img src='{$urlAppend}courses/$course_code/image/".urlencode($course->course_image)."' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=true&" .  generate_csrf_token_link_parameter() . "'>$langDelete</a>
        <input type='hidden' name='course_image' value='".q($course->course_image)."'>
    ";
} else {
    enableCheckFileSize();
    $course_image = fileSizeHidenInput() . "<input type='file' name='course_image' id='course_image'>"; 
}


$tool_content = action_bar(array(
        array(
            'title' => $langBack,
            'url' => $urlAppend."courses/".$course_code,
            'icon' => 'fa-reply',
            'level' => 'primary-label',
        )
    ),false)."
    <div class='row'>
        <div class='col-xs-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='editdesc.php?course=$course_code' enctype='multipart/form-data'>
                    <fieldset>
                    <div class='form-group'>
                        <label for='description' class='col-sm-2 control-label'>$langCourseLayout:</label>
                        <div class='col-sm-10'>
                            ".  selection($layouts, 'layout', $layout, 'class="form-control"')."
                        </div>
                    </div>
                    <div id='image_field' class='form-group".(($layout == 1)?"":" hidden")."'>
                        <label for='course_image' class='col-sm-2 control-label'>$langCourseImage:</label>
                        <div class='col-sm-10'>
                            $course_image
                        </div>
                    </div>                  
                    <div class='form-group'>
                        <label for='description' class='col-sm-2 control-label'>$langDescription:</label>
                        <div class='col-sm-10'>
                            " . rich_text_editor('description', 8, 20, $description) . "
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                            <a href='{$urlAppend}courses/$course_code' class='btn btn-default'>$langCancel</a>
                        </div>
                    </div>
                  </fieldset>
                  ". generate_csrf_token_form_field() ."
                </form>
    </div></div></div>";

draw($tool_content, 2, null, $head_content);
