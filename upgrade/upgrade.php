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
//require_once 'upgradeHelper.php';

stop_output_buffering();

require_once 'upgrade/functions.php';

$head_content .= "<script>
    $(document).ready(function() {
        $('#submit_upgrade').click(function (e) {            
            window.open('upgrade_db_popup.php', 'Αναβάθμιση', 'height=700,width=800,scrollbars=no,status=no');            
            return false;
        });
    });
</script>";


set_time_limit(0);


load_global_messages();

if ($urlAppend[strlen($urlAppend) - 1] != '/') {
    $urlAppend .= '/';
}

// include_messages
require "lang/$language/common.inc.php";
$extra_messages = "config/{$language_codes[$language]}.inc.php";
if (file_exists($extra_messages)) {
    include $extra_messages;
} else {
    $extra_messages = false;
}
require "lang/$language/messages.inc.php";
if ($extra_messages) {
    include $extra_messages;
}

$pageName = $langUpgrade;

$auth_methods = array('imap', 'pop3', 'ldap', 'db');

if (version_compare(ECLASS_VERSION, '3.12', '<=')) {
    $tbl_options = 'DEFAULT CHARACTER SET=utf8 ENGINE=InnoDB';
} else {
    $tbl_options = 'DEFAULT CHARACTER SET=utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
}

// Coming from the admin tool or stand-alone upgrade?
$fromadmin = !isset($_POST['submit_upgrade']);

if (isset($_POST['login']) and isset($_POST['password'])) {
    if (!is_admin($_POST['login'], $_POST['password'])) {
        Session::Messages($langUpgAdminError, 'alert-warning');
        redirect_to_home_page('upgrade/');
    }
}

if (!(isset($_SESSION['is_admin']) and $_SESSION['is_admin'])) {
    redirect_to_home_page('upgrade/');
}

// upgrade from versions < 3.0 is not possible
if ((!defined('ECLASS_VERSION')) or (!DBHelper::tableExists('config'))) {
    if (version_compare(ECLASS_VERSION, '3.0', '<')) {
        set_config('upgrade_begin', '');
        $tool_content .= "<div class='alert alert-danger'>$langUpgTooOld</div>";
        draw($tool_content, 0);
        exit;
    }
    $tool_content .= "<div class='alert alert-warning'>$langUpgTooOld</div>";
    draw($tool_content, 0);
    exit;
}

if (!check_engine()) {
    $tool_content .= "<div class='alert alert-warning'>$langInnoDBMissing</div>";
    draw($tool_content, 0);
    exit;
}

// Make sure 'video' subdirectory exists and is writable
$videoDir = $webDir . '/video';
if (!file_exists($videoDir)) {
    if (!make_dir($videoDir)) {
        die($langUpgNoVideoDir);
    }
} elseif (!is_dir($videoDir)) {
    die($langUpgNoVideoDir2);
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

$default_student_upload_whitelist = 'pdf, ps, eps, tex, latex, dvi, texinfo, texi, zip, rar, tar, bz2, gz, 7z, xz, lha, 
                            lzh, z, Z, doc, docx, odt, ott, sxw, stw, fodt, txt, rtf, dot, mcw, wps, xls, xlsx, xlt, 
                            ods, ots, cpp, c, sxc, stc, fods, uos, csv, ppt, pps, pot, pptx, ppsx, odp, otp, sxi, sti, 
                            fodp, uop, potm, odg, otg, sxd, std, fodg, odb, mdb, ttf, otf, jpg, jpeg, png, gif, bmp, tif, 
                            tiff, psd, dia, svg, ppm, xbm, xpm, ico, avi, asf, asx, wm, wmv, wma, dv, mov, moov, movie, 
                            mp4, mpg, mpeg, 3gp, 3g2, m2v, aac, m4a, flv, f4v, m4v, mp3, swf, webm, ogv, ogg, mid, midi, 
                            aif, rm, rpm, ram, wav, mp2, m3u, qt, vsd, vss, vst, cg3, ggb, psc, dir, dcr, sb, sb2, sb3, 
                            sbx, Kodu, html, htm, wlmp, mswmm, aia, apk, py, ev3, psg, glo, psd, gsp, xml, a3p, ypr, 
                            mw2, dtd, aia, hex,mscz, pages, heic, piv, stk, pptm, gfar, lab, lmsp, qrs, cpp, c';
$default_teacher_upload_whitelist = 'html, htm, js, css, xml, xsl, cpp, c, java, m, h, tcl, py, sgml, sgm, ini, ds_store, 
                            cg3, ggb, psc, dir, dcr, mw2, mom, sb, sb2, sb3, sbx, Kodu, gsp, kid, wlmp, mswmm, aia, apk, 
                            py, psg, glo, psc, woff, xsd, cur, lxf, a3p, ypr, mw2, h5p, dtd, xsd, woff2, ppsm, aia, hex, 
                            jqz, jm, data, jar, glo,mscz, heic, piv, stk, gfar, lab, lmsp, qrs';


if (!isset($_POST['submit2']) and isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
    if (version_compare(PHP_VERSION, '7.4') < 0) {
        $tool_content .= "<div class='alert alert-danger'>$langWarnAboutPHP</div>";
    }
    if (!in_array(get_config('email_transport'), array('smtp', 'sendmail')) and
            !get_config('email_announce')) {
        $tool_content .= "<div class='alert alert-info'>$langEmailSendWarn</div>";
    }

    $tool_content .= "<h5>$langRequiredPHP</h5>";
    $tool_content .= "<ul class='list-unstyled'>";
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
    $tool_content .= "</ul><h5>$langOptionalPHP</h5>";
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

    /* error handling */
    $logdate = date("Y-m-d_G.i.s");
    $logfile = "log-$logdate.html";
    $logfile_path = "$webDir/courses";
    if (!($logfile_handle = @fopen("$logfile_path/$logfile", 'w'))) {
        $error = error_get_last();
        Session::Messages($langLogFileWriteError .
            '<br><i>' . q($error['message']) . '</i>');
        draw($tool_content, 0);
        exit;
    }

    fwrite($logfile_handle, "<!DOCTYPE html><html><head><meta charset='UTF-8'>
      <title>Open eClass upgrade log of $logdate</title></head><body>\n");

    /*if (!$command_line) {
        $tool_content .= getInfoAreas();
        define('TEMPLATE_REMOVE_CLOSING_TAGS', true);
        draw($tool_content, 0);
    }*/

    Debug::setOutput(function ($message, $level) use ($logfile_handle, &$debug_error) {
        fwrite($logfile_handle, $message);
        if ($level > Debug::WARNING) {
            $debug_error = true;
        }
    });
    Debug::setLevel(Debug::WARNING);

    set_config('institution', $_POST['Institution']);
    set_config('postaddress', $_POST['postaddress']);
    set_config('phone', $_POST['telephone']);
    set_config('fax', $_POST['fax']);

    if (isset($emailhelpdesk)) {
        // Upgrade to 3.x-style config
        if (!copy('config/config.php', 'config/config_backup.php')) {
            die($langConfigError1);
        }

        if (!isset($durationAccount)) {
            $durationAccount = 4 * 30 * 24 * 60 * 60; // 4 years
        }

        set_config('site_name', $siteName);
        set_config('account_duration', $durationAccount);
        set_config('institution_url', $InstitutionUrl);
        set_config('email_sender', $emailAdministrator);
        set_config('admin_name', $administratorName . ' ' . $administratorSurname);
        set_config('email_helpdesk', $emailhelpdesk);
        if (isset($emailAnnounce) and $emailAnnounce) {
            set_config('email_announce', $emailAnnounce);
        }
        set_config('base_url', $urlServer);
        set_config('default_language', $language);
        if (isset($active_ui_languages)) {
            set_config('active_ui_languages', implode(' ', $active_ui_languages));
        } else {
            set_config('active_ui_languages', 'el en');
        }
        set_config('phpMyAdminURL', $phpMyAdminURL);
        set_config('phpSysInfoURL', $phpSysInfoURL);

        $new_conf = '<?php
/* ========================================================
 * Open eClass 3.x configuration file
 * Created by upgrade on ' . date('Y-m-d H:i') . '
 * ======================================================== */

$mysqlServer = ' . quote($mysqlServer) . ';
$mysqlUser = ' . quote($mysqlUser) . ';
$mysqlPassword = ' . quote($mysqlPassword) . ';
$mysqlMainDb = ' . quote($mysqlMainDb) . ';
';
        $fp = @fopen('config/config.php', 'w');
        if (!$fp) {
            $tool_content .= "<div class='alert alert-danger'>$langConfigError3</div>";
            draw($tool_content, 0);
            exit;
        }
        fwrite($fp, $new_conf);
        fclose($fp);
    }
    // ****************************************************
    //      upgrade database
    // ****************************************************


    // Create or upgrade config table
    if (DBHelper::fieldExists('config', 'id')) {
        Database::get()->query("RENAME TABLE config TO old_config");
        Database::get()->query("CREATE TABLE `config` (
                         `key` VARCHAR(32) NOT NULL,
                         `value` VARCHAR(255) NOT NULL,
                         PRIMARY KEY (`key`)) $tbl_options");
        Database::get()->query("INSERT INTO config
                         SELECT `key`, `value` FROM old_config
                         GROUP BY `key`");
        Database::get()->query("DROP TABLE old_config");
    }
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


    $tool_content .= "
        <div class='col-sm-12'>
            <div class='alert alert-info text-center'>$langUpgradeBase</div>
                <div class='text-center'>
                    <a class='btn btn-success' data-placement='bottom' data-toggle='tooltip' id='submit_upgrade' title='$langUpgrade'>
                        <span class='fa fa-refresh space-after-icon'></span>
                        <span class='hidden-xs'>$langUpgrade</span>                    
                    </a>
                </div>
            </div>";

    fwrite($logfile_handle, "\n</body>\n</html>\n");
    fclose($logfile_handle);

} // end of if not submit


draw($tool_content, 0, null, $head_content);


/*

    $output_result = "<br/><div class='alert alert-success'>$langUpgradeSuccess<br/><b>$langUpgReady</b><br/><a href=\"../courses/$logfile\" target=\"_blank\">$langLogOutput</a></div><p/>";
    if ($command_line) {
        if ($debug_error) {
            echo " * $langUpgSucNotice\n";
        }
        echo $langUpgradeSuccess, "\n", $langLogOutput, ": $logfile_path/$logfile\n";
    } else {
        if ($debug_error) {
            $output_result .= "<div class='alert alert-danger'>" . $langUpgSucNotice . "</div>";
        }
        updateInfo(1, $output_result, false);
        // Close HTML body
        echo "</body></html>\n";
    }

    fwrite($logfile_handle, "\n</body>\n</html>\n");
    fclose($logfile_handle);
 */
