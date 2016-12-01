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
$navigation[] = array('url' => "index.php?id=$uid&amp;token=$token", 'name' => $langMyePortfolio);

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    draw($tool_content, 1);
    exit;
}

$userdata = Database::get()->querySingle("SELECT eportfolio_enable FROM user WHERE id = ?d", $uid);

if ($userdata->eportfolio_enable == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langePortfolioDisableWarning ".sprintf($langePortfolioEnableInfo, $urlAppend."main/eportfolio/index.php?id=$uid&amp;token=$token")."</div>";
}

if (isset($_GET['delete_bio'])) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    @unlink("$webDir/courses/eportfolio/userbios/$uid/bio.pdf");
    Session::Messages($langBioDeletedSuccess, 'alert-success');
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
                Session::Messages($langUploadBioSuccess, 'alert-success');
            } else {
                Session::Messages(sprintf($langUploadBioFailSize, get_config('bio_quota')), 'alert-danger');
            }
        } else {
            Session::Messages($langUploadBioFailType, 'alert-danger');
        }
        redirect_to_home_page('main/eportfolio/bio_upload.php');
    } 
}

$head_content .= "<script>
                    function confirmDel(url) {
                      bootbox.confirm('$langConfirmDelete', function(okay) {
                        if(okay)
                          location.href = url;
                      });
                      return false;
                    } 
                  </script>";

$tool_content .=
    action_bar(array(
        array('title' => $langBack,
            'url' => "{$urlAppend}main/eportfolio/index.php?id=$uid&amp;token=$token",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
        
$tool_content .= 
    "<div class='row'>
        <div class='col-xs-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data' action='' onsubmit='return validateNodePickerForm();'>
                    <fieldset>";
enableCheckFileSize();
if (file_exists("$webDir/courses/eportfolio/userbios/$uid/bio.pdf")) {
    $label = $langReplace;
    $bio = "<a href='{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$uid&amp;token=$token'>$langBio</a>&nbsp;&nbsp;
        <a class='btn btn-danger btn-sm' onclick='return confirmDel(this.href)' href='$_SERVER[SCRIPT_NAME]?delete_bio=true&" .  generate_csrf_token_link_parameter() . "'>$langDelete</a>";
} else {
    $label = $langPathUploadFile;
    $bio = '';
}
$tool_content .= 
    "<div class='alert alert-info'>$langBioPermFileType ".sprintf($langBioMaxSize, get_config('bio_quota'))."</div>
     <div class='form-group'>
        <label for='bio' class='col-sm-2 control-label'>$label</label>
        <div class='col-sm-10'>
            $bio" . fileSizeHidenInput() . "
            <input type='file' name='bio' size='30'>
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <input class='btn btn-primary btn-sm' type='submit' name='submit' value='$langSubmit'>
            <a href='{$urlAppend}main/eportfolio/index.php?id=$uid&amp;token=$token' class='btn btn-default btn-sm'>$langCancel</a>
        </div>
    </div>";

$tool_content .= 
                "</fieldset>
                ". generate_csrf_token_form_field() ."  
            </form>
        </div>
    </div>
</div>";
            
draw($tool_content, 1, null, $head_content);
