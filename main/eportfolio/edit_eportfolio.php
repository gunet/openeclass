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

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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
require_once 'main/eportfolio/eportfolio_functions.php';
require_once 'include/lib/fileUploadLib.inc.php';

$image_path = $webDir . '/courses/userimg/' . $uid;

check_uid();
check_guest();

$toolName = $langMyePortfolio;
$pageName = $langEditChange;
$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$navigation[] = array('url' => "index.php", 'name' => $langMyePortfolio);

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger alert-dismissible'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    draw($tool_content, 1);
    exit;
}

$userdata = Database::get()->querySingle("SELECT eportfolio_enable FROM user WHERE id = ?d", $uid);

if ($userdata->eportfolio_enable == 0) {
    $tool_content .= "<div class='alert alert-warning alert-dismissible'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
}

load_js('tools.js');

// Handle AJAX image delete
if (isset($_POST['delete_userimage'])) {
    $images = glob($image_path . '_*');
    if ($images) {
        foreach ($images as $img) { unlink($img); }
    }
    Database::get()->query("UPDATE user SET has_icon = 0 WHERE id = ?d", $uid);
    $_SESSION['profile_image_cache_buster'] = time();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    //check for validation errors in e-portfolio fields
    $v = new Valitron\Validator($_POST);
    epf_validate($v);
    if (!$v->validate()) {
        Session::flashPost();
        Session::flashPost()->Messages($langFormErrors, 'alert-danger')->Errors($v->errors());
        redirect_to_home_page("main/eportfolio/edit_eportfolio.php");
    } else {
        // Handle photo upload
        if (isset($_FILES['userimage']) && is_uploaded_file($_FILES['userimage']['tmp_name'])) {
            if (!file_exists($webDir . '/courses/userimg/')) {
                make_dir($webDir . '/courses/userimg/');
            }
            validateUploadedFile($_FILES['userimage']['name'], 1);
            $type = $_FILES['userimage']['type'];
            $image_base = $image_path . '_' . profile_image_hash($uid) . '_';
            copy_resized_image($_FILES['userimage']['tmp_name'], $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE, $image_base . IMAGESIZE_LARGE . '.jpg');
            copy_resized_image($_FILES['userimage']['tmp_name'], $type, IMAGESIZE_SMALL, IMAGESIZE_SMALL, $image_base . IMAGESIZE_SMALL . '.jpg');
            Database::get()->query("UPDATE user SET has_icon = 1 WHERE id = ?d", $uid);
            $_SESSION['profile_image_cache_buster'] = time();
        }
        process_eportfolio_fields_data();
        Session::flash('message', $langePortfolioChangeSucc);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("main/eportfolio/index.php");
    }
}

$head_content .= eportfolio_alert_css();

$head_content .= "
        <script>
        $(function() {
            var navLinks = \$('#navbar-examplePortfolioEdit .nav-link');
            var scrollLock = false;

            navLinks.on('click', function() {
                navLinks.removeClass('active');
                \$(this).addClass('active');
                scrollLock = true;
                setTimeout(function() { scrollLock = false; }, 1000);
            });

            function updateActive() {
                if (scrollLock) return;
                var scrollTop = \$(window).scrollTop();
                var offset = 90;
                var current = null;

                \$('[id^=\"EditPortfolio\"]').each(function() {
                    if (\$(this).offset().top - offset <= scrollTop) {
                        current = \$(this).attr('id');
                    }
                });

                navLinks.removeClass('active');
                if (current) {
                    navLinks.filter('[href=\"#' + current + '\"]').addClass('active');
                } else {
                    navLinks.first().addClass('active');
                }
            }

            \$(window).on('scroll', updateActive);
            updateActive();
        });
        </script>
    ";

$user_has_icon_js = (int) Database::get()->querySingle("SELECT has_icon FROM user WHERE id = ?d", $uid)->has_icon;

$sec = $urlServer . 'main/eportfolio/edit_eportfolio.php';

$tool_content .=
    "<div class='row mt-4'>
        <div class='col-sm-9'>
            <form class='form-horizontal' action='$sec' method='post' enctype='multipart/form-data'>
            <div tabindex='0'>";

//add custom profile fields
$ret_str = render_eportfolio_fields_form();
$tool_content .= $ret_str['panels'];

$tool_content .= "
    <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
        <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>
        <a href='{$urlAppend}main/eportfolio/index.php' class='btn cancelAdminBtn'>$langCancel</a>
    </div>
    ". generate_csrf_token_form_field() ."
    </div></form>
    </div>
    ".$ret_str['right_menu']."
    </div>";

$head_content .= "
    <script>
        $(document).ready(function() {
            $('.visibility_select').on('change', function() {
                var selectName = $(this).attr('name');    // Get the select's name
                var selectValue = $(this).val();          // Get the select's value
                $('#' + selectName + '_hidden').val(selectValue);     // Set the hidden input's value
                if (selectValue == 2) {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-users\"></i>');
                } else if (selectValue == 3) {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-lock\"></i>');
                } else {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-globe\"></i>');
                }
            });
        });
    </script>
";


$head_content .= "
    <script>
    $(function() {
        var originalSrc = \$('#profile-img-preview').attr('src');
        var hasExistingIcon = " . $user_has_icon_js . ";

        // Preview new image immediately on file select
        \$('input[name=\"userimage\"]').on('change', function() {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(e) {
                \$('#profile-img-preview').attr('src', e.target.result);
                \$('#delete-profile-img').show();
                \$('.pic-label').text('$langReplacePicture');
                syncInputWidth();
            };
            reader.readAsDataURL(file);
        });

        function syncInputWidth() {
            if (\$('#delete-profile-img').is(':visible')) {
                \$('input[name=\"userimage\"]').css('max-width', 'calc(100% - 54px)');
            } else {
                \$('input[name=\"userimage\"]').css('max-width', '');
            }
        }
        syncInputWidth();

        // Delete button: if a new file was selected, just clear the input and revert preview
        // If it's an existing saved image, do AJAX delete
        \$('#delete-profile-img').on('click', function() {
            var \$input = \$('input[name=\"userimage\"]');
            var hasPendingFile = \$input[0].files && \$input[0].files.length > 0;

            if (hasPendingFile) {
                // Just clear the pending selection
                \$input.val('');
                if (hasExistingIcon) {
                    \$('#profile-img-preview').attr('src', originalSrc);
                    \$('.pic-label').text('$langReplacePicture');
                    syncInputWidth();
                } else {
                    \$('#profile-img-preview').attr('src', '$themeimg/default_" . IMAGESIZE_LARGE . ".png');
                    \$('.pic-label').text('$langAddPicture');
                    \$(this).hide();
                    syncInputWidth();
                }
            } else {
                // Delete the saved image via AJAX
                var \$btn = \$(this);
                $.post('{$urlAppend}main/eportfolio/edit_eportfolio.php', { delete_userimage: 1 }, function() {
                    hasExistingIcon = 0;
                    originalSrc = '$themeimg/default_" . IMAGESIZE_LARGE . ".png';
                    \$('#profile-img-preview').attr('src', originalSrc);
                    \$('.pic-label').text('$langAddPicture');
                    \$btn.hide();
                    syncInputWidth();
                });
            }
        });
    });
    </script>
";

draw($tool_content, 1, null, $head_content);
