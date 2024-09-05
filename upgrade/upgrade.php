<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

define('UPGRADE', true);

require_once '../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/course_settings.php';
require_once 'include/mailconfig.php';
require_once 'modules/db/recycle.php';
require_once 'modules/db/foreignkeys.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/h5p/classes/H5PHubUpdater.php';
require_once 'upgrade/functions.php';

$command_line = (php_sapi_name() == 'cli' && !isset($_SERVER['REMOTE_ADDR']));

if (!$command_line) {
    if (isset($_POST['login']) and isset($_POST['password'])) {
        if (!is_admin($_POST['login'], $_POST['password'])) {
            Session::Messages($langUpgAdminError, 'alert-warning');
            redirect_to_home_page('upgrade/');
        }
    }
    if (!$is_admin) {
        redirect_to_home_page('upgrade/');
    }
}

$ajax_call = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($ajax_call and (!isset($_POST['token']) or !validate_csrf_token($_POST['token']))) csrf_token_error();

stop_output_buffering();
$error_message = null;
set_time_limit(0);
    $tbl_options = 'DEFAULT CHARACTER SET=utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE=InnoDB';

load_global_messages();

function fatal_error($message) {
    global $command_line, $ajax_call, $error_message;

    set_config('upgrade_begin', '');
    if ($command_line) {
        if ($error_message) {
            $message .= "\n\n$error_message\n";
        }
        die("$message\n");
    } elseif ($ajax_call) {
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'error' => $error_message
        ], JSON_UNESCAPED_UNICODE);
        flush();
    } else {
        if ($error_message) {
            $message .= "<br>\n$error_message";
        }
        $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$message</span></div>";
        draw($tool_content, 0);
    }
    exit;
}

function message($message, $tag, $status = 'ok') {
    global $command_line, $ajax_call, $error_message;

    if ($command_line) {
        // On the command line, just display the message
        if ($error_message) {
            $message .= "\n\n$error_message\n";
        }
        echo("$message\n");
    } elseif ($ajax_call) {
        // When called from the front-end...
        // If this message has already been seen, continue with upgrade
        if (isset($_SESSION['upgrade_tag']) and $_SESSION['upgrade_tag'] == $tag) {
            unset($_SESSION['upgrade_tag']);
            return;
        }
        // Else display message and wait to be called again
        $_SESSION['upgrade_tag'] = $tag;
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'error' => $error_message
        ], JSON_UNESCAPED_UNICODE);
        flush();
        exit;
    }
}

function steps_finished() {
    global $step, $ajax_call, $version;
    // all steps finished for this version, record current version in the DB
    unset($step);
    unset($_SESSION['upgrade_step']);
    set_config('version', $version);

    if ($ajax_call) {
        echo json_encode([
            'status' => 'ok',
            'message' => null,
            'error' => null]);
        exit;
    }
}

function break_on_step() {
    global $ajax_call, $step;
    $step += 1;
    if ($ajax_call) {
        $_SESSION['upgrade_step'] = $step;
        echo json_encode([
            'status' => 'ok',
            'message' => null,
            'error' => null]);
        exit;
    }
}

if(isset($_POST['action']) and $_POST['action'] == 'preview_theme'){
    if(get_config('theme_options_id') != $_POST['selected_theme_id']){
        set_config('theme_options_id',$_POST['selected_theme_id']);
        echo 1;
    }else{
        echo 0;
    }
    exit();
}

if ($command_line and isset($argv[1])) {
    $logfile_path = $argv[1];
} else {
    $logfile_path = "$webDir/courses";
}
// error logging
$logdate = date("Y-m-d_G.i.s");
if (!isset($_SESSION['upgrade_logfile_path'])) {
    $logfile = "log-$logdate.html";
    $_SESSION['upgrade_logfile_path'] = "$logfile_path/$logfile";
    $_SESSION['upgrade_logfile_name'] = $logfile;
    $logfile_begin = true;
    set_config('upgrade_begin', time());
}
$upgrade_logfile_path = $_SESSION['upgrade_logfile_path'];
$logfile = $_SESSION['upgrade_logfile_name'];

if ($command_line or $ajax_call) {

    $logfile_begin = !file_exists($upgrade_logfile_path);
    if (!($logfile_handle = @fopen($upgrade_logfile_path, 'a'))) {
        $error_message = q(error_get_last()['message']);
        fatal_error($langLogFileWriteError);
    }

    if ($logfile_begin) {
        fwrite($logfile_handle, "<!DOCTYPE html><html><head><meta charset='UTF-8'>
          <title>Open eClass upgrade log of $logdate</title></head><body>\n");
    }

    Debug::setOutput(function ($message, $level) use ($logfile_handle, &$debug_error) {
        fwrite($logfile_handle, $message);
        if ($level > Debug::WARNING) {
            $debug_error = true;
        }
        message($message, md5($message));
    });
    Debug::setLevel(Debug::WARNING);

    if ($ajax_call) {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            fatal_error("$errno: $errstr (line: $errline)");
        });
    }

    $oldversion = get_config('version');
    if (!isset($_SESSION['upgrade_started']) and version_compare($oldversion, '3.15', '>') and version_compare($oldversion, '4.0', '<')) {
        // When upgrading 4.0 pre-releases, re-run all steps from 3.15+
        $oldversion = '3.14';
        $_SESSION['upgrade_started'] = true;
    }
    $versions = ['3.1', '3.2', '3.3', '3.4', '3.5', '3.6', '3.7', '3.8', '3.9', '3.10', '3.11', '3.12', '3.13', '3.14', '3.15', '3.16', '4.0'];

    if (isset($_SESSION['upgrade_step'])) {
        $step = $_SESSION['upgrade_step'];
    }

    foreach ($versions as $version) {
        if (version_compare($oldversion, $version, '<')) {

            if (!isset($step)) {
                $step = $_SESSION['upgrade_step'] = 1;
                message("$langUpgForVersion $version", "start-$version");
            }

            if ($version === '3.1') {
                Database::get()->query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                    ('dont_display_login_form', '0'),
                    ('email_required', '0'),
                    ('email_from', '1'),
                    ('am_required', '0'),
                    ('dropbox_allow_student_to_student', '0'),
                    ('dropbox_allow_personal_messages', '0'),
                    ('enable_social_sharing_links', '0'),
                    ('block_username_change', '0'),
                    ('enable_mobileapi', '0'),
                    ('display_captcha', '0'),
                    ('insert_xml_metadata', '0'),
                    ('doc_quota', '200'),
                    ('dropbox_quota', '100'),
                    ('video_quota', '100'),
                    ('group_quota', '100'),
                    ('course_multidep', '0'),
                    ('user_multidep', '0'),
                    ('restrict_owndep', '0'),
                    ('restrict_teacher_owndep', '0'),
                    ('allow_teacher_clone_course', '0')");
                upgrade_to_3_1($tbl_options);

            } elseif ($version === '3.2') {
                upgrade_to_3_2($tbl_options);
                steps_finished();

            } elseif ($version === '3.3') {
                upgrade_to_3_3($tbl_options);
                steps_finished();

            } elseif ($version === '3.4') {
                upgrade_to_3_4($tbl_options);
                steps_finished();

            } elseif ($version === '3.5') {
                upgrade_to_3_5($tbl_options);
                steps_finished();

            } elseif ($version === '3.6') {
                upgrade_to_3_6($tbl_options);
                steps_finished();

            } elseif ($version === '3.7') {
                upgrade_to_3_7($tbl_options);
                steps_finished();

            } elseif ($version === '3.8') {
                upgrade_to_3_8($tbl_options);
                steps_finished();

            } elseif ($version === '3.9') {
                upgrade_to_3_9($tbl_options);
                steps_finished();

            } elseif ($version === '3.10') {
                upgrade_to_3_10($tbl_options);
                steps_finished();

            } elseif ($version === '3.11') {
                upgrade_to_3_11($tbl_options);
                steps_finished();

            } elseif ($version === '3.12') {
                if ($step == 1) {
                    upgrade_to_3_12($tbl_options);
                    break_on_step();
                }

                if ($step == 2) {
                    // create directory indexes to hinder directory traversal in misconfigured servers
                    message($langAddDirectoryIndexes, "$version-2");
                    addDirectoryIndexFiles();
                    break_on_step();
                }

                if ($step == 3) {
                    // install h5p content if needed
                    /*$hp5_last_update = get_config('h5p_update_content_ts');
                    if ($hp5_last_update) {
                        $hp5_last_update = date_create_from_format('Y-m-d H:i', $hp5_last_update);
                        $date_diff = date_diff($hp5_last_update, date_create());
                    }
                    if (!$hp5_last_update or $date_diff->days > 2) {
                        message($langH5pInstall, "$version-3");
                        $hubUpdater = new H5PHubUpdater();
                        $hubUpdater->fetchLatestContentTypes();
                        set_config('h5p_update_content_ts', date('Y-m-d H:i', time()));
                    } */
                    steps_finished();
                }

            } elseif ($version === '3.13') {
                if ($step == 1) {
                    upgrade_to_3_13($tbl_options);
                    break_on_step();
                }

                if ($step == 2) {
                    message($langUpgUTF8MB4, "$version-convert");
                    break_on_step();
                }

                if ($step > 2) {
                    convert_db_encoding_to_utf8mb4();
                    steps_finished();
                }
            }  elseif ($version === '3.14') {
                if ($step == 1) {
                    upgrade_to_3_14($tbl_options);
                    break_on_step();
                }
                if ($step == 2) {
                    message($langEncodeUserProfilePics, "$version-encode");
                    encode_user_profile_pics();
                    steps_finished();
                }
            } elseif ($version === '3.15') {
                upgrade_to_3_15($tbl_options);
                steps_finished();
            } elseif ($version === '3.16') {
                upgrade_to_3_16($tbl_options);
                steps_finished();
            } elseif ($version === '4.0') {
                upgrade_to_4_0($tbl_options);
                steps_finished();
            }
        }
        if ($command_line) {
            unset($step);
        }
    }

    finalize_upgrade();
    fwrite($logfile_handle, "<hr><p>End of upgrade log</p></body></html>\n");
    fclose($logfile_handle);
    message($langUpgFinished, "$version-finished", 'done');
    if ($command_line) {
        message("$langLogOutput: $_SESSION[upgrade_logfile_path]", 'done');
    }
    exit;
}

$pageName = $langUpgrade;

// Coming from the admin tool or stand-alone upgrade?
$fromadmin = !isset($_POST['submit_upgrade']);

// upgrade from versions < 3.0 is not possible
if (!defined('ECLASS_VERSION') or !DBHelper::tableExists('config') or version_compare(ECLASS_VERSION, '3.0', '<')) {
    fatal_error($langUpgTooOld);
}

if (!check_engine()) {
    fatal_error($langInnoDBMissing);
}

// Make sure 'video' subdirectory exists and is writable
$videoDir = $webDir . '/video';
if (!file_exists($videoDir)) {
    if (!make_dir($videoDir)) {
        fatal_error($langUpgNoVideoDir);
    }
} elseif (!is_dir($videoDir)) {
    fatal_error($langUpgNoVideoDir2);
}

mkdir_or_error('storage');
mkdir_or_error('storage/views');
mkdir_or_error('courses/temp');
touch_or_error('courses/temp/index.php');
mkdir_or_error('courses/temp/pdf');
mkdir_or_error('courses/userimg');
touch_or_error('courses/userimg/index.php');
mkdir_or_error('courses/facultyimg');
touch_or_error($webDir . '/video/index.php');
mkdir_or_error('courses/user_progress_data');
mkdir_or_error('courses/user_progress_data/cert_templates');
touch_or_error('courses/user_progress_data/cert_templates/index.php');
mkdir_or_error('courses/user_progress_data/badge_templates');
touch_or_error('courses/user_progress_data/badge_templates/index.php');
mkdir_or_error('courses/eportfolio');
touch_or_error('courses/eportfolio/index.php');
mkdir_or_error('courses/eportfolio/userbios');
touch_or_error('courses/eportfolio/userbios/index.php');
mkdir_or_error('courses/eportfolio/work_submissions');
touch_or_error('courses/eportfolio/work_submissions/index.php');
mkdir_or_error('courses/eportfolio/mydocs');
touch_or_error('courses/eportfolio/mydocs/index.php');

if (!isset($_POST['submit2']) and isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
    unset($_SESSION['upgrade_logfile_path']);
    unset($_SESSION['upgrade_logfile_name']);
    if (!in_array(get_config('email_transport'), array('smtp', 'sendmail')) and !get_config('email_announce')) {
        $tool_content .= "<div class='alert alert-info'>$langEmailSendWarn</div>";
    }

    $tool_content .= "<div class='row row-cols-lg-3 row-cols-1 g-3 mt-1'>";
    $tool_content .= "<div class='col'><div class='card panelCard px-lg-4 py-lg-3 h-100'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'><h3>$langPHPVersion</h3></div><div class='card-body'>";
    $tool_content .= "<ul class='list-group list-group-flush'>";
    $tool_content .= checkPHPVersion('8.0');
    $tool_content .= "</ul>";
    $tool_content .= "</div></div></div>";
    $tool_content .= "<div class='col'><div class='card panelCard px-lg-4 py-lg-3 h-100'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'><h3>$langRequiredPHP</h3></div><div class='card-body'>";
    $tool_content .= "<ul class='list-group list-group-flush'>";
    $tool_content .= warnIfExtNotLoaded('pdo_mysql');
    $tool_content .= warnIfExtNotLoaded('gd');
    $tool_content .= warnIfExtNotLoaded('mbstring');
    $tool_content .= warnIfExtNotLoaded('xml');
    $tool_content .= warnIfExtNotLoaded('zlib');
    $tool_content .= warnIfExtNotLoaded('pcre');
    $tool_content .= warnIfExtNotLoaded('curl');
    $tool_content .= warnIfExtNotLoaded('zip');
    $tool_content .= warnIfExtNotLoaded('intl');
    $tool_content .= "</ul></div></div></div><div class='col'><div class='card panelCard px-lg-4 py-lg-3 h-100'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'><h3>$langOptionalPHP</h3></div><div class='card-body'>";
    $tool_content .= "<ul class='list-group list-group-flush'>";
    $tool_content .= warnIfExtNotLoaded('soap');
    $tool_content .= warnIfExtNotLoaded('ldap');
    $tool_content .= "</ul></div></div></div>";
    $tool_content .= "</div>";

    $tool_content .= "
    <div class='row row-cols-lg-2 row-cols-1 g-4 mt-4 mb-3'>
        <div class='col'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

        if (get_config('email_transport', 'mail') == 'mail' and
                !get_config('email_announce')) {
            $head_content .= '<script>$(function () {' . $mail_form_js . '});</script>';
            $tool_content .= mail_settings_form();
        }

        setGlobalContactInfo();

        $theme_id = get_config('theme_options_id') ?? 0;
        $all_themes = Database::get()->queryArray("SELECT * FROM theme_options WHERE version >= 3 ORDER BY name");
        $themes_arr[0] = 'Default';
        foreach ($all_themes as $row) {
            $themes_arr[$row->id] = $row->name;
            if($row->id == $theme_id){
                $active_theme = $row->id;
            }
        }
        // Get all images from dir screenshots
        $dir_screens = getcwd();
        $dir_screens = $dir_screens . '/template/modern/images/screenshots';
        $dir_themes_images = scandir($dir_screens);

        $tool_content .= "

                    <div class='card panelCard px-lg-4 py-lg-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langThemeSettings</h2>
                        </div>
                        <div class='card-body'>
                            <fieldset>
                                <div class='form-group'>
                                    <label class='col-sm-12 control-label-notes' for='id_Institution'>$langHomePageIntroText:</label>
                                    <div class='col-sm-12'>" . rich_text_editor('homepage_intro', 5, 20, get_config('homepage_intro')) . "</div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                        <a class='link-color TextBold' type='button' href='#view_themes_screens' data-bs-toggle='modal'>$langViewScreensThemes</a>
                                        <p class='mb-3'><span class='control-label-notes'>$langActiveTheme:&nbsp;</span>" . $themes_arr[$active_theme] . "</p>
                                        <label for='themeSelection' class='control-label-notes'>$langAvailableThemes:</label>
                                        ".  selection($themes_arr, 'theme_selection', $theme_id, 'class="form-select" id="themeSelection"')."
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-12 d-flex justify-content-end'>
                                        <input class='btn btn-primary' name='submit2' value='$langContinue &raquo;' type='submit'>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class='col d-none d-lg-block text-end'>
            <img class='form-image-modules' src='" . get_form_image() . "' alt='form-image'>
        </div>
    </div>
    
    <div class='modal fade' id='view_themes_screens' tabindex='-1' aria-labelledby='view_themes_screensLabel' aria-hidden='true'>
        <div class='modal-dialog modal-fullscreen' style='margin-top:0px;'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title' id='view_themes_screensLabel'>$langAvailableThemes</div>
                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <div class='row row-cols-1 g-4'>";
                            foreach($dir_themes_images as $image) {
                                $extension = pathinfo($image, PATHINFO_EXTENSION);
                                $imgExtArr = ['jpg', 'jpeg', 'png', 'PNG'];
                                if(in_array($extension, $imgExtArr)){
                                    $tool_content .= "
                                        <div class='col-lg-8 col-md-10 m-auto py-4'>
                                            <div class='card panelCard h-100'>
                                                <img style='width:100%; height:auto; object-fit:cover; object-position:50% 50%;' class='card-img-top' src='{$urlAppend}template/modern/images/screenshots/$image' alt='Image for current theme'/>
                                                <div class='card-footer'>
                                                    <p> " . strtok($image, '.') . " </p>
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
    </div>";


    $head_content .= "
        <script type='text/javascript'>
            $(document).ready(function() {
                $('#themeSelection').on('click',function(e){
                    e.preventDefault();
                    var selectedThemeId = $(this).val();
                    $.ajax({
                        url: '$_SERVER[SCRIPT_NAME]',
                        data: 'action=preview_theme&selected_theme_id='+selectedThemeId+'&token=$_SESSION[csrf_token]',
                        type: 'POST',
                        success: function(response) {
                            if(response == 1){
                                window.location.href = '$_SERVER[SCRIPT_NAME]';
                            }
                        },
                        error:function(error){
                            console.log(error)
                        },
                    });
                })  
            });
        </script>
    ";

} else {

    // Main part of upgrade starts here
    set_config('upgrade_begin', time());
    setGlobalContactInfo();
    // $_POST['Institution'] = $Institution;
    // $_POST['postaddress'] = $postaddress;
    // $_POST['telephone'] = $telephone;
    // $_POST['fax'] = $fax;

    if (!isset($_SERVER['SERVER_NAME'])) {
        $_SERVER['SERVER_NAME'] = parse_url($urlServer, PHP_URL_HOST);
    }

    if (isset($_POST['email_transport'])) {
        store_mail_config();
    }

    // set_config('institution', $_POST['Institution']);
    // set_config('postaddress', $_POST['postaddress']);
    // set_config('phone', $_POST['telephone']);
    // set_config('fax', $_POST['fax']);

    set_config('homepage_intro', $_POST['homepage_intro']);
    set_config('theme_options_id', $_POST['theme_selection']);
    set_config('dont_display_statistics',1);
    set_config('dont_display_popular_courses',1);
    set_config('dont_display_testimonials',1);
    set_config('dont_display_texts',1);
    set_config('dont_display_open_courses',1);
    set_config('dont_display_login_form',0);
    Database::get()->query("UPDATE homepagePriorities SET visible = 0 WHERE title <> 'announcements'");

    unset($_SESSION['upgrade_step']);
    unset($_SESSION['upgrade_tag']);

    // Display upgrade feedback screen
    $head_content .= "
        <style>
            #upgrade-container { padding: 1em; overflow-y: scroll; width: 100%;}
            .upgrade-header { font-weight: bold; border-bottom: 1px solid black; }
        </style>";
    $tool_content .= "
        <div class='col-sm-12'>
            <div class='alert alert-info text-center'>
                $langUpgradeBase<br>
                <em>$langPreviousVersion: " . get_config('version') . "</em>
            </div>
            <div class='text-center'>
                <button class='btn btn-success' id='submit_upgrade'>
                    <span class='fa fa-refresh space-after-icon'></span> $langUpgrade
                </button>
            </div>
        </div>
        <div class='col-sm-12' id='upgrade-container'>
        </div>
        <script>
            $(document).ready(function() {
                $('#submit_upgrade').click(function (e) {
                    var upgradeContainer = $('#upgrade-container');
                    e.preventDefault();
                    $('#submit_upgrade').prop('disabled', true);
                    $('#submit_upgrade').find('.fa').addClass('fa-spin');
                    upgradeContainer.html('<div class=\"text-center upgrade-header\">$langUpgradeStart</div>');
                    var maxHeight = $('#background-cheat').height() - upgradeContainer.position().top;
                    upgradeContainer.height(maxHeight - 100);
                    var feedback = function () {
                        $.post('upgrade.php', {
                            token: '$_SESSION[csrf_token]'
                        }, function (data) {
                            if (!data) {
                                setTimeout(feedback, 100);
                            } else {
                                if (data.error) {
                                    data.message += '<br><em>' + data.error + '</em>';
                                }
                                if (data.status == 'ok' || data.status == 'wait') {
                                    if (data.message) {
                                        upgradeContainer.append('<p>' + data.message + '</p>');
                                    }
                                    setTimeout(feedback, (data.status == 'ok')? 100: 1000);
                                } else if (data.status == 'error') {
                                    upgradeContainer.append('<div class=\"alert alert-danger\">' + data.message + '</div>');
                                    $('#submit_upgrade').find('.fa').removeClass('fa-spin');
                                } else if (data.status == 'done') {
                                    upgradeContainer.append('<div class=\"alert alert-success\">$langUpgradeSuccess<br>$langUpgReady</div>');
                                    upgradeContainer.append('<p>$langLogOutput: <a href=\"{$urlAppend}courses/$logfile\">$logfile</a></p>');
                                    $('#submit_upgrade').find('.fa').removeClass('fa-spin');
                                }
                            }
                            upgradeContainer.scrollTop(upgradeContainer.prop('scrollHeight'));
                        }, 'json');
                    };
                    feedback();
                });
            });
        </script>";

} // end of if not submit


draw($tool_content, 0, null, $head_content);
