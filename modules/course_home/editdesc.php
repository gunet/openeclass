<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
    header("Location: {$urlAppend}courses/$course_code/");
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
        $db_vars[] = $file_name;
    }


    if(isset($_POST['choose_from_list']) && !empty($_POST['choose_from_list'])){
        $imageName = $_POST['choose_from_list'];
        $imagePath = "$webDir/template/modern/images/courses_images/$imageName";
        $newPath = "$webDir/courses/$course_code/image/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            $extra_sql = ", course_image = ?s";
            $db_vars[] = $image_without_ext.".".$ext;
        }
    }


    array_push($db_vars, $course_id);
    Database::get()->query("UPDATE course SET description = ?s, home_layout = ?d$extra_sql WHERE id = ?d", $db_vars);
    // update index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    header("Location: {$urlAppend}courses/$course_code/");
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
            $('.chooseCourseImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_list').value = id_img;
                $('#CoursesImagesModal').modal('hide');
                document.getElementById('selectedImage').value = '$langSelect:'+id_img;

            });
        });
    </script>";
$layouts = array(1 => $langCourseLayout1, 3 => $langCourseLayout3);
$description = $course->description;
$layout = $course->home_layout;


if (isset($course->course_image)) {
    $course_image = "
        <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
            <img src='{$urlAppend}courses/$course_code/image/".urlencode($course->course_image)."' style='max-height:100px;max-width:150px;' alt='Course image'>
            <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&delete_image=true&" .  generate_csrf_token_link_parameter() . "'>$langDelete</a>
        </div>
        <input type='hidden' name='course_image' value='".q($course->course_image)."'>
    ";
} else {
    enableCheckFileSize();
    $course_image = fileSizeHidenInput() . "
            <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent'>
                <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                     <input type='file' name='course_image' id='course_image'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#CoursesImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_list' name='choose_from_list'>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                </div>
            </div>
        ";
}


// Get all images from dir courses_images
$dirname = getcwd();
$dirname = $dirname . '/template/modern/images/courses_images';
$dir_images = scandir($dirname);


$tool_content = "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' method='post' action='editdesc.php?course=$course_code' enctype='multipart/form-data'>
                    <fieldset>
                        <div class='row form-group'>
                            <label for='description' class='col-12 control-label-notes'>$langCourseLayout</label>
                            <div class='col-12'>
                                ".  selection($layouts, 'layout', $layout, 'class="form-control"')."
                            </div>
                        </div>
                        <div id='image_field' class='row form-group".(($layout == 1)?"":" hidden")." mt-4'>
                            <label for='course_image' class='col-12 control-label-notes'>$langCourseImage</label>
                            <div class='col-12'>
                                $course_image
                            </div>
                        </div>
                        <div class='row form-group mt-4'>
                            <label for='description' class='col-12 control-label-notes'>$langDescription</label>
                            <div class='col-12'>
                                " . rich_text_editor('description', 8, 20, $description) . "
                            </div>
                        </div>
                        <div class='row form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>
                                    <a href='{$urlAppend}courses/$course_code' class='btn cancelAdminBtn'>$langCancel</a>
                            </div>
                        </div>
                    </fieldset>

                    <div class='modal fade' id='CoursesImagesModal' tabindex='-1' aria-labelledby='CoursesImagesModalLabel' aria-hidden='true'>
                        <div class='modal-dialog modal-lg'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <div class='modal-title' id='CoursesImagesModalLabel'>$langCourseImage</div>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                <div class='modal-body'>
                                    <div class='row row-cols-1 row-cols-md-2 g-4'>";
                                        foreach($dir_images as $image) {
                                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                                            $imgExtArr = ['jpg', 'jpeg', 'png'];
                                            if(in_array($extension, $imgExtArr)){
                                                $tool_content .= "
                                                    <div class='col'>
                                                        <div class='card panelCard h-100'>
                                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/courses_images/$image' alt='image course'/>
                                                            <div class='card-body'>
                                                                <p>$image</p>

                                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseCourseImage mt-3' value='$langSelect'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ";
                                            }
                                        }
                $tool_content .= "
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>" .
                    generate_csrf_token_form_field() . "
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);
