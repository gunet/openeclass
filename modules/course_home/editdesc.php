<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/*
 * Edit, Course Description
 *
 */

$require_current_course = TRUE;
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';

$toolName = $langCourseProgram;

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
        if($connector->isEnabled()){
            $output=$connector->check("$webDir/courses/$course_code/image/$file_name");
            if($output->status==$output::STATUS_INFECTED){
                AntivirusApp::block($output->output);
            }
        }
        $extra_sql = ", course_image = ?s";
        $db_vars[] = $file_name;
    }

    if(!empty($_POST['choose_from_list'])) {
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
        } else {
            $extra_sql = ", course_image = ?s";
            $db_vars[] = $image_without_ext.".".$ext;
        }
    }

    $db_vars[] = $course_id;
    Database::get()->query("UPDATE course SET description = ?s, home_layout = ?d$extra_sql WHERE id = ?d", $db_vars);
    // update index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    header("Location: {$urlAppend}courses/$course_code/");
    exit;
}
$data['enableCheckFileSize'] = enableCheckFileSize();
$data['fileSizeHidenInput'] = fileSizeHidenInput();
$data['course_image'] = $course_image = $course->course_image;
$data['layout'] = $layout = $course->home_layout;
$data['selection'] = selection(array(1 => $langCourseLayout1, 3 => $langCourseLayout3), 'layout', $layout, 'class="form-control"');
$data['rich_text_editor'] = rich_text_editor('description', 8, 20, $course->description);

// Get all images from dir courses_images
$image_content = '';
$dir_images = scandir($webDir . '/template/modern/images/courses_images');
foreach($dir_images as $image) {
    $extension = pathinfo($image, PATHINFO_EXTENSION);
    $imgExtArr = ['jpg', 'jpeg', 'png'];
    if (in_array($extension, $imgExtArr)) {
        $image_content .= "
            <div class='col'>
                <div class='card panelCard card-default h-100'>
                    <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/courses_images/$image' alt='image course'/>
                    <div class='card-body'>                                
                        <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseCourseImage mt-3' value='$langSelect'>
                    </div>
                </div>
            </div>
        ";
    }
}
$data['image_content'] = $image_content;

view('modules.course_home.editdesc', $data);
