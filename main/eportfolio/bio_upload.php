<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_login = true;
$require_valid_uid = true;

require_once '../../include/baseTheme.php';

check_uid();
check_guest();

$toolName = $langMyePortfolio;
$pageName = $langUploadBio;
$token = token_generate('eportfolio' . $uid);
$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$navigation[] = array('url' => "index.php?id=$uid&token=$token", 'name' => $langMyePortfolio);

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span></div></div>";
    draw($tool_content, 1);
    exit;
}

$userdata = Database::get()->querySingle("SELECT eportfolio_enable FROM user WHERE id = ?d", $uid);

if ($userdata->eportfolio_enable == 0) {
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span></div></div>";
}

if (isset($_GET['delete_bio'])) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    @unlink("$webDir/courses/eportfolio/userbios/$uid/bio.pdf");
    $tool_content .= "<div class='col-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langBioDeletedSuccess</span></div></div>";
    redirect_to_home_page('main/eportfolio/bio_upload.php');
}
elseif (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (!file_exists($webDir . '/courses/eportfolio/userbios/'.$uid)) {
        @mkdir($webDir . '/courses/eportfolio/userbios/'.$uid, 0777);
    }
    if (isset($_FILES['bio']) && is_uploaded_file($_FILES['bio']['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (finfo_file($finfo, $_FILES['bio']['tmp_name']) == 'application/pdf') {
            if ($_FILES['bio']['size'] <= get_config('bio_quota')*1024*1024) {
                @unlink("$webDir/courses/eportfolio/userbios/$uid/bio.pdf");
                move_uploaded_file($_FILES['bio']['tmp_name'], "$webDir/courses/eportfolio/userbios/$uid/bio.pdf");
                $tool_content .= "<div class='col-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langUploadBioSuccess</span></div></div>";
            } else {
                $tool_content .= "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langUploadBioFailSize ".get_config('bio_quota')."</span></div></div>";
            }
        } else {
            $tool_content .= "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langUploadBioFailType</span></div></div>";
        }
        redirect_to_home_page('main/eportfolio/bio_upload.php');
    }
}

$head_content .= "<script>
                    function confirmDel(url) {

                    //   bootbox.confirm('$langConfirmDelete', function(okay) {
                    //     if(okay)
                    //       location.href = url;
                    //   });


                      bootbox.confirm({ 
                        closeButton: false,
                        title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</div>',
                        message: '<p class=\'text-center\'>".js_escape($langConfirmDelete)."</p>',
                        buttons: {
                            cancel: {
                                label: '".js_escape($langCancel)."',
                                className: 'cancelAdminBtn position-center'
                            },
                            confirm: {
                                label: '".js_escape($langDelete)."',
                                className: 'deleteAdminBtn position-center',
                            }
                        },
                        callback: function (okay) {
                            if(okay) {
                                location.href = url;
                            }
                        }
                    });


                      return false;
                    } 
                  </script>";

$tool_content .=
    action_bar(array(
        array('title' => $langBack,
            'url' => "{$urlAppend}main/eportfolio/index.php?id=$uid&amp;token=$token",
            'icon' => 'fa-reply'
            )));

$tool_content .=
   "<div class='row'>
        <div class='col-lg-6 col-12'>
                <form class='form-wrapper form-edit border-0 px-0' role='form' method='post' enctype='multipart/form-data' action='' onsubmit='return validateNodePickerForm();'>
                    <fieldset><legend class='mb-0' aria-label='$langForm'></legend>";
enableCheckFileSize();
if (file_exists("$webDir/courses/eportfolio/userbios/$uid/bio.pdf")) {
    $label = $langReplace;
    $bio = "<a href='{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$uid&amp;token=$token'>$langBio</a>&nbsp;&nbsp;
        <a class='btn deleteAdminBtn' onclick='return confirmDel(this.href)' href='$_SERVER[SCRIPT_NAME]?delete_bio=true&" .  generate_csrf_token_link_parameter() . "'>$langDelete</a>";
} else {
    $label = $langPathUploadFile;
    $bio = '';
}
$tool_content .=
    "<div class='col-12'>
        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langBioPermFileType ".sprintf($langBioMaxSize, get_config('bio_quota'))."</span></div></div>
            <div class='row'>
                <label for='bio' class='control-label-notes'>$label <span class='Accent-200-cl'>(*)</span></label>
                <div class='d-inline-flex'>$bio" . fileSizeHidenInput() . "</div>
                <div class='col-12 mt-3'><input type='file' name='bio' id='bio' class='form-control'></div>
            </div>

            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                    <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>
                    <a href='{$urlAppend}main/eportfolio/index.php?id=$uid&amp;token=$token' class='btn cancelAdminBtn'>$langCancel</a>
                </div>
            </div>";

$tool_content .=
                "</fieldset>
                ". generate_csrf_token_form_field() ."  
            </form>
        </div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
        <img class='form-image-modules' src='". get_form_image() ."' alt='$langImgFormsDes'>
        </div>
    </div>
    ";

draw($tool_content, 1, null, $head_content);
