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
        $tool_content .= "<div class='alert alert-danger'>$message</div>";
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
    $versions = ['3.1', '3.2', '3.3', '3.4', '3.5', '3.6', '3.7', '3.8', '3.9', '3.10', '3.11', '3.12', '3.13', '3.14', '3.15', '3.16'];
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
            } elseif ($version === '3.14') {
                if ($step == 1) {
                    upgrade_to_3_14($tbl_options);
                    break_on_step();
                }

                if ($step == 2) {
                    message($langEncodeUserProfilePics, "$version-encode");
                    encode_user_profile_pics();
                    steps_finished();
                }
            } elseif ($version == '3.15') {
                if ($step == 1) {
                    upgrade_to_3_15($tbl_options);
                    break_on_step();
                }
            } elseif ($version == '3.16') {
                if ($step == 1) {
                    upgrade_to_3_16($tbl_options);
                    break_on_step();
                }
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

mkdir_or_error('courses/temp');
touch_or_error('courses/temp/index.php');
mkdir_or_error('courses/temp/pdf');
mkdir_or_error('courses/userimg');
touch_or_error('courses/userimg/index.php');
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

    $tool_content .= "<ul class='list-unstyled'>";
    $tool_content .= "<strong>$langPHPVersion</strong>";
    checkPHPVersion('8.0');
    $tool_content .= "</ul>";
    $tool_content .= "<ul class='list-unstyled'>";
    $tool_content .= "<strong>$langRequiredPHP</strong>";
    warnIfExtNotLoaded('session');
    warnIfExtNotLoaded('pdo');
    warnIfExtNotLoaded('pdo_mysql');
    warnIfExtNotLoaded('gd');
    warnIfExtNotLoaded('mbstring');
    warnIfExtNotLoaded('xml');
    warnIfExtNotLoaded('dom');
    warnIfExtNotLoaded('zlib');
    warnIfExtNotLoaded('pcre');
    warnIfExtNotLoaded('curl');
    warnIfExtNotLoaded('zip');
    warnIfExtNotLoaded('intl');
    $tool_content .= "</ul><strong>$langOptionalPHP</strong>";
    $tool_content .= "<ul class='list-unstyled'>";
    warnIfExtNotLoaded('soap');
    warnIfExtNotLoaded('ldap');
    $tool_content .= "</ul>";

    $tool_content .= "
      <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

    if (get_config('email_transport', 'mail') == 'mail' and
            !get_config('email_announce')) {
        $head_content .= '<script>$(function () {' . $mail_form_js . '});</script>';
        mail_settings_form();
    }

    setGlobalContactInfo();
    $tool_content .= "
        <div class='panel panel-default'>
          <div class='panel-heading'>
            <h2 class='panel-title'>$langUpgContact</h2>
          </div>
          <div class='panel-body'>
            <fieldset>
          <div class='form-group'>
                <label class='col-sm-2 control-label' for='id_Institution'>$langInstituteShortName:</label>
                <div class='col-sm-10'>
              <input class='form-control' type='text' name='Institution' id='id_Institution' value='" . q($Institution) . "'>
            </div>
          </div>
          <div class='form-group'>
                <label class='col-sm-2 control-label' for='id_postaddress'>$langUpgAddress</label>
                <div class='col-sm-10'>
              <textarea class='form-control' rows='3' name='postaddress' id='id_postaddress'>" . q($postaddress) . "</textarea>
            </div>
          </div>
          <div class='form-group'>
                <label class='col-sm-2 control-label' for='id_telephone'>$langUpgTel</label>
                <div class='col-sm-10'>
              <input class='form-control' type='text' name='telephone' id='id_telephone' value='" . q($telephone) . "'>
            </div>
          </div>
          <div class='form-group'>
                <label class='col-sm-2 control-label' for='id_fax'>Fax:</label>
                <div class='col-sm-10'>
              <input class='form-control' type='text' name='fax' id='id_fax' value='" . q($fax) . "'>
            </div>
          </div>
          <div class='form-group'>
            <div class='col-md-12'>
              <input class='pull-right btn btn-primary' name='submit2' value='$langContinue &raquo;' type='submit'>
                </div>
              </div>
            </fieldset>
            </div>
          </div>
        </form>
      </div>";

} else {

    // Main part of upgrade starts here
    set_config('upgrade_begin', time());
    setGlobalContactInfo();
    $_POST['Institution'] = $Institution;
    $_POST['postaddress'] = $postaddress;
    $_POST['telephone'] = $telephone;
    $_POST['fax'] = $fax;
    if (!isset($_SERVER['SERVER_NAME'])) {
        $_SERVER['SERVER_NAME'] = parse_url($urlServer, PHP_URL_HOST);
    }

    if (isset($_POST['email_transport'])) {
        store_mail_config();
    }

    set_config('institution', $_POST['Institution']);
    set_config('postaddress', $_POST['postaddress']);
    set_config('phone', $_POST['telephone']);
    set_config('fax', $_POST['fax']);

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
