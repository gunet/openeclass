<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
require_once 'upgradeHelper.php';

stop_output_buffering();

// set default storage engine
Database::get()->query("SET default_storage_engine = InnoDB");

require_once 'upgrade/functions.php';

set_time_limit(0);

if (php_sapi_name() == 'cli' and ! isset($_SERVER['REMOTE_ADDR'])) {
    $command_line = true;
} else {
    $command_line = false;
}

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
$OK = "[<font color='green'> $langSuccessOk </font>]";
$BAD = "[<font color='red'> $langSuccessBad </font>]";

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// Coming from the admin tool or stand-alone upgrade?
$fromadmin = !isset($_POST['submit_upgrade']);

if (isset($_POST['login']) and isset($_POST['password'])) {
    if (!is_admin($_POST['login'], $_POST['password'])) {
        Session::Messages($langUpgAdminError, 'alert-warning');
        redirect_to_home_page('upgrade/');
    }
}

if (!$command_line and !(isset($_SESSION['is_admin']) and $_SESSION['is_admin'])) {
    redirect_to_home_page('upgrade/');
}

if (!DBHelper::tableExists('config')) {
    $tool_content .= "<div class='alert alert-warning'>$langUpgTooOld</div>";
    draw($tool_content, 0);
    exit;
}

if (!check_engine()) {
    $tool_content .= "<div class='alert alert-warning'>$langInnoDBMissing</div>";
    draw($tool_content, 0);
    exit;
}

// Upgrade user table first if needed
if (!DBHelper::fieldExists('user', 'id')) {
    // check for multiple usernames
    fix_multiple_usernames();

    if (DBHelper::indexExists('user', 'user_username')) {
        Database::get()->query("ALTER TABLE user DROP INDEX user_username");
    }
    if (!DBHelper::fieldExists('user', 'whitelist')) {
        Database::get()->query("ALTER TABLE `user` ADD `whitelist` TEXT");
        Database::get()->query("UPDATE `user` SET `whitelist` = '*,,' WHERE user_id = 1");
    }
    if (!DBHelper::fieldExists('user', 'description')) {
        Database::get()->query("ALTER TABLE `user` ADD description TEXT");
    }
    Database::get()->query("ALTER TABLE user
                        CHANGE registered_at ts_registered_at int(10) NOT NULL DEFAULT 0,
                        CHANGE expires_at ts_expires_at INT(10) NOT NULL DEFAULT 0,
                        ADD registered_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        ADD expires_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    Database::get()->query("UPDATE user
                        SET registered_at = FROM_UNIXTIME(ts_registered_at),
                            expires_at = FROM_UNIXTIME(ts_expires_at)");
    Database::get()->query("UPDATE user SET email = '' WHERE email IS NULL");
    Database::get()->query("ALTER TABLE user
                        CHANGE user_id id INT(11) NOT NULL AUTO_INCREMENT,
                        CHANGE nom surname VARCHAR(100) NOT NULL DEFAULT '',
                        CHANGE prenom givenname VARCHAR(100) NOT NULL DEFAULT '',
                        CHANGE username username VARCHAR(100) NOT NULL UNIQUE KEY COLLATE utf8_bin,
                        CHANGE password password VARCHAR(60) NOT NULL DEFAULT 'empty',
                        CHANGE email email VARCHAR(100) NOT NULL DEFAULT '',
                        CHANGE statut status TINYINT(4) NOT NULL DEFAULT " . USER_STUDENT . ",
                        CHANGE phone phone VARCHAR(20) DEFAULT '',
                        CHANGE am am VARCHAR(20) DEFAULT '',
                        DROP ts_registered_at,
                        DROP ts_expires_at,
                        DROP perso,
                        CHANGE description description TEXT,
                        CHANGE whitelist whitelist TEXT,
                        DROP forum_flag,
                        DROP announce_flag,
                        DROP doc_flag");
    Database::get()->query("ALTER TABLE admin
                        CHANGE idUser user_id INT(11) NOT NULL PRIMARY KEY");
}

// Make sure 'video' subdirectory exists and is writable
$videoDir = $webDir . '/video';
if (!file_exists($videoDir)) {
    if (!make_dir($videoDir)) {
        die($langUpgNoVideoDir);
    }
} elseif (!is_dir($videoDir)) {
    die($langUpgNoVideoDir2);
} elseif (!is_writable($videoDir)) {
    die($langUpgNoVideoDir3);
}

mkdir_or_error('courses/temp');
touch_or_error('courses/temp/index.php');
mkdir_or_error('courses/userimg');
touch_or_error('courses/userimg/index.php');
touch_or_error($webDir . '/video/index.php');
mkdir_or_error('courses/eportfolio');
touch_or_error('courses/eportfolio/index.php');
mkdir_or_error('courses/eportfolio/userbios');
touch_or_error('courses/eportfolio/userbios/index.php');
mkdir_or_error('courses/eportfolio/work_submissions');
touch_or_error('courses/eportfolio/work_submissions/index.php');
mkdir_or_error('courses/eportfolio/mydocs');
touch_or_error('courses/eportfolio/mydocs/index.php');

// ********************************************
// upgrade config.php
// *******************************************

$default_student_upload_whitelist = 'pdf, ps, eps, tex, latex, dvi, texinfo, texi, zip, rar, tar, bz2, gz, 7z, xz, lha, lzh, z, Z, doc, docx, odt, ott, sxw, stw, fodt, txt, rtf, dot, mcw, wps, xls, xlsx, xlt, ods, ots, sxc, stc, fods, uos, csv, ppt, pps, pot, pptx, ppsx, odp, otp, sxi, sti, fodp, uop, potm, odg, otg, sxd, std, fodg, odb, mdb, ttf, otf, jpg, jpeg, png, gif, bmp, tif, tiff, psd, dia, svg, ppm, xbm, xpm, ico, avi, asf, asx, wm, wmv, wma, dv, mov, moov, movie, mp4, mpg, mpeg, 3gp, 3g2, m2v, aac, m4a, flv, f4v, m4v, mp3, swf, webm, ogv, ogg, mid, midi, aif, rm, rpm, ram, wav, mp2, m3u, qt, vsd, vss, vst';
$default_teacher_upload_whitelist = 'html, js, css, xml, xsl, cpp, c, java, m, h, tcl, py, sgml, sgm, ini, ds_store';

if (!isset($_POST['submit2']) and isset($_SESSION['is_admin']) and $_SESSION['is_admin'] and !$command_line) {
    if (ini_get('register_globals')) { // check if register globals is Off
        $tool_content .= "<div class='alert alert-danger'>$langWarningInstall1</div>";
    }
    if (ini_get('short_open_tag')) { // check if short_open_tag is Off
        $tool_content .= "<div class='alert alert-danger'>$langWarningInstall2</div>";
    }
    if (version_compare(PHP_VERSION, '5.4.0') < 0) {
        $tool_content .= "<div class='alert alert-danger'>$langWarnAboutPHP</div>";
    }
    if (!in_array(get_config('email_transport'), array('smtp', 'sendmail')) and
            !get_config('email_announce')) {
        $tool_content .= "<div class='alert alert-info'>$langEmailSendWarn</div>";
    }

    $tool_content .= "<h5>$langRequiredPHP</h5>";
    $tool_content .= "<ul class='list-unstyled'>";
    warnIfExtNotLoaded('standard');
    warnIfExtNotLoaded('session');
    warnIfExtNotLoaded('pdo');
    warnIfExtNotLoaded('pdo_mysql');
    warnIfExtNotLoaded('gd');
    warnIfExtNotLoaded('mbstring');
    warnIfExtNotLoaded('xml');
    warnIfExtNotLoaded('dom');
    warnIfExtNotLoaded('zlib');
    warnIfExtNotLoaded('pcre');
    warnIfExtNotLoaded("curl");
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
              <input class='pull-right btn btn-primary' name='submit2' value='$langCont &raquo;' type='submit'>
                </div>
              </div>
            </fieldset>
            </div>
          </div>
        </form>
      </div>";
    draw($tool_content, 0, null, $head_content);
} else {
    // Main part of upgrade starts here
    if ($command_line) {
        setGlobalContactInfo();
        $_POST['Institution'] = $Institution;
        $_POST['postaddress'] = $postaddress;
        $_POST['telephone'] = $telephone;
        $_POST['fax'] = $fax;
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = parse_url($urlServer, PHP_URL_HOST);
        }
    }

    if (isset($_POST['email_transport'])) {
        store_mail_config();
    }
    $logdate = date("Y-m-d_G.i:s");
    $logfile = "log-$logdate.html";
    if (!($logfile_handle = @fopen("$webDir/courses/$logfile", 'w'))) {
        $error = error_get_last();
        Session::Messages($langLogFileWriteError .
            '<br><i>' . q($error['message']) . '</i>');
        draw($tool_content, 0);
        exit;
    }

    fwrite($logfile_handle, "<!DOCTYPE html><html><head><meta charset='UTF-8'>
      <title>Open eClass upgrade log of $logdate</title></head><body>\n");

    set_config('upgrade_begin', time());

    if (!$command_line) {
        $tool_content .= getInfoAreas();
        define('TEMPLATE_REMOVE_CLOSING_TAGS', true);
        draw($tool_content, 0);
    }
    updateInfo(0.01, $langUpgradeStart . " : " . $langUpgradeConfig);
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
 * Open eClass 3.0 configuration file
 * Created by upgrade on ' . date('Y-m-d H:i') . '
 * ======================================================== */

$mysqlServer = ' . quote($mysqlServer) . ';
$mysqlUser = ' . quote($mysqlUser) . ';
$mysqlPassword = ' . quote($mysqlPassword) . ';
$mysqlMainDb = ' . quote($mysqlMainDb) . ';
';
        $fp = @fopen('config/config.php', 'w');
        if (!$fp) {
            updateInfo(0.01, $langConfigError3);
            exit;
        }
        fwrite($fp, $new_conf);
        fclose($fp);
    }
    // ****************************************************
    //      upgrade eclass main database
    // ****************************************************

    updateInfo(-1, $langUpgradeBase . " " . $mysqlMainDb);

    // Create or upgrade config table
    if (DBHelper::fieldExists('config', 'id')) {
        Database::get()->query("RENAME TABLE config TO old_config");
        Database::get()->query("CREATE TABLE `config` (
                         `key` VARCHAR(32) NOT NULL,
                         `value` VARCHAR(255) NOT NULL,
                         PRIMARY KEY (`key`)) $charset_spec");
        Database::get()->query("INSERT INTO config
                         SELECT `key`, `value` FROM old_config
                         GROUP BY `key`");
        Database::get()->query("DROP TABLE old_config");
    }
    $oldversion = get_config('version');
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

    // upgrade from versions < 2.1.3 is not possible
    if (version_compare($oldversion, '2.1.3', '<') or ( !isset($oldversion))) {
        updateInfo(1, $langUpgTooOld);
        exit;
    }
    // upgrade from version 2.x to 3.0
    if (version_compare($oldversion, '2.2', '<')) {
        // course units
        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_units` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `comments` MEDIUMTEXT,
                `visibility` CHAR(1) NOT NULL DEFAULT 'v',
                `order` INT(11) NOT NULL DEFAULT 0,
                `course_id` INT(11) NOT NULL) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `unit_resources` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `unit_id` INT(11) NOT NULL ,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `comments` MEDIUMTEXT,
                `res_id` INT(11) NOT NULL,
                `type` VARCHAR(255) NOT NULL DEFAULT '',
                `visibility` CHAR(1) NOT NULL DEFAULT 'v',
                `order` INT(11) NOT NULL DEFAULT 0,
                `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00') $charset_spec");
    }

    if (version_compare($oldversion, '2.2.1', '<')) {
        Database::get()->query("ALTER TABLE `cours` CHANGE `doc_quota` `doc_quota` FLOAT NOT NULL DEFAULT '104857600'");
        Database::get()->query("ALTER TABLE `cours` CHANGE `video_quota` `video_quota` FLOAT NOT NULL DEFAULT '104857600'");
        Database::get()->query("ALTER TABLE `cours` CHANGE `group_quota` `group_quota` FLOAT NOT NULL DEFAULT '104857600'");
        Database::get()->query("ALTER TABLE `cours` CHANGE `dropbox_quota` `dropbox_quota` FLOAT NOT NULL DEFAULT '104857600'");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
                        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `user_id` INT NOT NULL DEFAULT '0',
                        `cat_id` INT NULL ,
                        `forum_id` INT NULL ,
                        `topic_id` INT NULL ,
                        `notify_sent` BOOL NOT NULL DEFAULT '0',
                        `course_id` INT NOT NULL DEFAULT '0') $charset_spec");

        if (!DBHelper::fieldExists('cours_user', 'course_id')) {
            Database::get()->query('ALTER TABLE cours_user ADD course_id int(11) DEFAULT 0 NOT NULL FIRST');
            $t = Database::get()->queryArray("SELECT cours_id, code FROM cours");
            foreach ($t as $entry) {
              Database::get()->query("UPDATE cours_user SET course_id = $entry->cours_id WHERE code_cours = '$entry->code'");
            }
            Database::get()->query("ALTER TABLE cours_user DROP PRIMARY KEY, ADD PRIMARY KEY (course_id, user_id)");
            Database::get()->query("CREATE INDEX course_user_id ON cours_user (user_id, course_id)");
            Database::get()->query("ALTER TABLE cours_user DROP code_cours");
        }

        if (!DBHelper::fieldExists('annonces', 'cours_id')) {
            Database::get()->query('ALTER TABLE annonces ADD cours_id int(11) DEFAULT 0 NOT NULL AFTER code_cours');
            $t = Database::get()->queryArray("SELECT cours_id, code FROM cours");
            foreach ($t as $entry) {
                Database::get()->query("UPDATE annonces SET cours_id = $entry->cours_id WHERE code_cours = '$entry->code'");
            }
            Database::get()->query('ALTER TABLE annonces DROP code_cours');
        }
    }
    if (version_compare($oldversion, '2.3.1', '<')) {
        if (!DBHelper::fieldExists('prof_request', 'am')) {
            Database::get()->query('ALTER TABLE `prof_request` ADD `am` VARCHAR(20) NULL AFTER profcomm');
        }
    }

    DBHelper::fieldExists('user', 'email_public') or
            Database::get()->query("ALTER TABLE `user`
                        ADD `email_public` TINYINT(1) NOT NULL DEFAULT 0,
                        ADD `phone_public` TINYINT(1) NOT NULL DEFAULT 0,
                        ADD `am_public` TINYINT(1) NOT NULL DEFAULT 0");

    if (version_compare($oldversion, '2.4', '<')) {
        if (DBHelper::fieldExists('cours', 'faculte')) {
            delete_field('cours', 'faculte');
            updateInfo(-1, $langDeleteField);
        }

        Database::get()->query("ALTER TABLE user CHANGE lang lang VARCHAR(16) NOT NULL DEFAULT 'el'");
        DBHelper::fieldExists('annonces', 'visibility') or
                Database::get()->query("ALTER TABLE `annonces` ADD `visibility` CHAR(1) NOT NULL DEFAULT 'v'");
        DBHelper::fieldExists('user', 'description') or
                Database::get()->query("ALTER TABLE `user` ADD description TEXT,
                                         ADD has_icon BOOL NOT NULL DEFAULT 0");
        DBHelper::fieldExists('user', 'verified_mail') or
                Database::get()->query("ALTER TABLE `user` ADD verified_mail BOOL NOT NULL DEFAULT " . EMAIL_UNVERIFIED . ",
                                         ADD receive_mail BOOL NOT NULL DEFAULT 1");
        DBHelper::fieldExists('cours_user', 'receive_mail') or
                Database::get()->query("ALTER TABLE `cours_user` ADD receive_mail BOOL NOT NULL DEFAULT 1");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `document` (
                        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `course_id` INT(11) NOT NULL,
                        `subsystem` TINYINT(4) NOT NULL,
                        `subsystem_id` INT(11) DEFAULT NULL,
                        `path` VARCHAR(255) NOT NULL,
                        `filename` VARCHAR(255) NOT NULL,
                        `visibility` CHAR(1) NOT NULL DEFAULT 'v',
                        `comment` TEXT,
                        `category` TINYINT(4) NOT NULL DEFAULT 0,
                        `title` TEXT,
                        `creator` TEXT,
                        `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `date_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `subject` TEXT,
                        `description` TEXT,
                        `author` VARCHAR(255) NOT NULL DEFAULT '',
                        `format` VARCHAR(32) NOT NULL DEFAULT '',
                        `language` VARCHAR(16) NOT NULL DEFAULT '',
                        `copyrighted` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `group_properties` (
                        `course_id` INT(11) NOT NULL,
                        `group_id` TINYINT(4) NOT NULL  PRIMARY KEY,
                        `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
                        `multiple_registration` TINYINT(4) NOT NULL DEFAULT 0,
                        `allow_unregister` TINYINT(4) NOT NULL DEFAULT 0,
                        `forum` TINYINT(4) NOT NULL DEFAULT 1,
                        `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
                        `documents` TINYINT(4) NOT NULL DEFAULT 1,
                        `wiki` TINYINT(4) NOT NULL DEFAULT 0,
                        `agenda` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `group` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `course_id` INT(11) NOT NULL DEFAULT 0,
                        `name` varchar(100) NOT NULL DEFAULT '',
                        `description` TEXT,
                        `forum_id` INT(11) NULL,
                        `max_members` INT(11) NOT NULL DEFAULT 0,
                        `secret_directory` varchar(30) NOT NULL DEFAULT '0') $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `group_members` (
                        `group_id` INT(11) NOT NULL,
                        `user_id` INT(11) NOT NULL,
                        `is_tutor` INT(11) NOT NULL DEFAULT 0,
                        `description` TEXT,
                        PRIMARY KEY (`group_id`, `user_id`)) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `glossary` (
                       `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                       `term` VARCHAR(255) NOT NULL,
                       `definition` text NOT NULL,
                       `url` text,
                       `order` INT(11) NOT NULL DEFAULT 0,
                       `datestamp` DATETIME NOT NULL,
                       `course_id` INT(11) NOT NULL) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `link` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `course_id` INT(11) NOT NULL,
                            `url` VARCHAR(255),
                            `title` VARCHAR(255),
                            `description` TEXT NOT NULL,
                            `category` INT(6) DEFAULT 0 NOT NULL,
                            `order` INT(6) DEFAULT 0 NOT NULL,
                            `hits` INT(6) DEFAULT 0 NOT NULL,
                            PRIMARY KEY (`id`, `course_id`)) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `link_category` (
                            `id` INT(6) NOT NULL AUTO_INCREMENT,
                            `course_id` INT(11) NOT NULL,
                            `name` VARCHAR(255) NOT NULL,
                            `description` TEXT,
                            `order` INT(6) NOT NULL DEFAULT 0,
                            PRIMARY KEY (`id`, `course_id`)) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS ebook (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `order` INT(11) NOT NULL,
                            `title` TEXT) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS ebook_section (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `ebook_id` INT(11) NOT NULL,
                            `public_id` VARCHAR(11) NOT NULL,
                            `file` VARCHAR(128),
                            `title` TEXT) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS ebook_subsection (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `section_id` VARCHAR(11) NOT NULL,
                            `public_id` VARCHAR(11) NOT NULL,
                            `file_id` INT(11) NOT NULL,
                            `title` TEXT) $charset_spec");

        if (DBHelper::tableExists('prof_request')) {
            Database::get()->query("RENAME TABLE prof_request TO user_request");
            Database::get()->query("ALTER TABLE user_request
                                    CHANGE rid id INT(11) NOT NULL auto_increment,
                                    CHANGE profname name VARCHAR(100) NOT NULL DEFAULT '',
                                    CHANGE profsurname surname VARCHAR(100) NOT NULL DEFAULT '',
                                    CHANGE profuname uname VARCHAR(100) NOT NULL DEFAULT '',
                                    CHANGE profpassword password VARCHAR(255) NOT NULL DEFAULT '',
                                    CHANGE profemail email varchar(255) NOT NULL DEFAULT '',
                                    CHANGE proftmima faculty_id INT(11) NOT NULL DEFAULT 0,
                                    CHANGE profcomm phone VARCHAR(20) NOT NULL DEFAULT '',
                                    CHANGE lang lang VARCHAR(16) NOT NULL DEFAULT 'el',
                                    ADD request_ip varchar(45) NOT NULL DEFAULT ''");
        }

        // Upgrade table admin_announcements if needed
        if (DBHelper::fieldExists('admin_announcements', 'gr_body')) {
            Database::get()->query("RENAME TABLE `admin_announcements` TO `admin_announcements_old`");
            Database::get()->query("CREATE TABLE IF NOT EXISTS `admin_announcements` (
                                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                                    `title` VARCHAR(255) DEFAULT NULL,
                                    `body` TEXT,
                                    `date` DATETIME NOT NULL,
                                    `begin` DATETIME DEFAULT NULL,
                                    `end` DATETIME DEFAULT NULL,
                                    `visible` ENUM('V','I') NOT NULL,
                                    `lang` VARCHAR(10) NOT NULL DEFAULT 'el',
                                    `ordre` MEDIUMINT(11) NOT NULL DEFAULT 0,
                                    PRIMARY KEY (`id`))");

            Database::get()->query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                    SELECT gr_title AS title, CONCAT_WS('  ', gr_body, gr_comment) AS body, `date`, visible, 'el'
                                    FROM admin_announcements_old WHERE gr_title <> '' OR gr_body <> ''");
            Database::get()->query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                     SELECT en_title AS title, CONCAT_WS('  ', en_body, en_comment) AS body, `date`, visible, 'en'
                                     FROM admin_announcements_old WHERE en_title <> '' OR en_body <> ''");
            Database::get()->query("DROP TABLE admin_announcements_old");
        }
        DBHelper::fieldExists('admin_announcements', 'ordre') or
                Database::get()->query("ALTER TABLE `admin_announcements` ADD `ordre` MEDIUMINT(11) NOT NULL DEFAULT 0 AFTER `lang`");
        // not needed anymore
        if (DBHelper::tableExists('cours_faculte')) {
            Database::get()->query("DROP TABLE cours_faculte");
        }
    }

    if (version_compare($oldversion, '2.5', '<')) {
        Database::get()->query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                    ('disable_eclass_stud_reg', '0'),
                    ('disable_eclass_prof_reg', '0'),
                    ('email_verification_required', '1'),
                    ('dont_mail_unverified_mails', '1'),
                    ('close_user_registration', '0'),
                    ('max_glossary_terms', '250'),
                    ('code_key', '" . generate_secret_key(32) . "')");

        // old users have their email verified
        if (DBHelper::fieldExists('user', 'verified_mail')) {
            Database::get()->query('ALTER TABLE `user` MODIFY `verified_mail` TINYINT(1) NOT NULL DEFAULT ' . EMAIL_UNVERIFIED);
            Database::get()->query('UPDATE `user` SET `verified_mail`= ' . EMAIL_VERIFIED);
        }
        DBHelper::fieldExists('user_request', 'verified_mail') or
                Database::get()->query("ALTER TABLE `user_request` ADD `verified_mail` TINYINT(1) NOT NULL DEFAULT " . EMAIL_UNVERIFIED . " AFTER `email`");

        Database::get()->query("UPDATE `user` SET `email`=LOWER(TRIM(`email`))");
        Database::get()->query("UPDATE `user` SET `username`=TRIM(`username`)");
    }

    if (version_compare($oldversion, '2.5.2', '<')) {
        Database::get()->query("ALTER TABLE `user` MODIFY `password` VARCHAR(60) DEFAULT 'empty'");
        Database::get()->query("DROP TABLE IF EXISTS passwd_reset");
    }

    if (version_compare($oldversion, '2.6', '<')) {
        Database::get()->query("ALTER TABLE `config` CHANGE `value` `value` TEXT NOT NULL");
        $old_close_user_registration = Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = 'close_user_registration'")->value;
        if ($old_close_user_registration == 0) {
            $eclass_stud_reg = 2;
        } else {
            $eclass_stud_reg = 1;
        }
        Database::get()->query("UPDATE `config`
                              SET `key` = 'eclass_stud_reg',
                                  `value`= $eclass_stud_reg
                              WHERE `key` = 'close_user_registration'");

        $old_disable_eclass_prof_reg = !Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = 'disable_eclass_prof_reg'")->value;
        Database::get()->query("UPDATE `config` SET `key` = 'eclass_prof_reg',
                                           `value` = $old_disable_eclass_prof_reg
                                      WHERE `key` = 'disable_eclass_prof_reg'");
        Database::get()->query("DELETE FROM `config` WHERE `key` = 'disable_eclass_stud_reg'");
        Database::get()->query("DELETE FROM `config` WHERE `key` = 'alt_auth_student_req'");
        $old_alt_auth_stud_req = Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = 'alt_auth_student_req'")->value;
        if ($old_alt_auth_stud_req == 1) {
            $alt_auth_stud_req = 1;
        } else {
            $alt_auth_stud_req = 2;
        }
        Database::get()->query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('user_registration', 1),
                                        ('alt_auth_prof_reg', 1),
                                        ('alt_auth_stud_reg', $alt_auth_stud_req)");

        Database::get()->query("DELETE FROM `config` WHERE `key` = 'alt_auth_student_req'");

        if (!DBHelper::fieldExists('user', 'whitelist')) {
            Database::get()->query("ALTER TABLE `user` ADD `whitelist` TEXT AFTER `am_public`");
            Database::get()->query("UPDATE `user` SET `whitelist` = '*,,' WHERE user_id = 1");
        }
        Database::get()->query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                            ('student_upload_whitelist', ?s),
                            ('teacher_upload_whitelist', ?s)", $default_student_upload_whitelist, $teacher_upload_whitelist);
        if (!DBHelper::fieldExists('user', 'last_passreminder')) {
            Database::get()->query("ALTER TABLE `user` ADD `last_passreminder` DATETIME DEFAULT NULL AFTER `whitelist`");
        }
        Database::get()->query("CREATE TABLE IF NOT EXISTS login_failure (
                id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ip varchar(45) NOT NULL,
                count tinyint(4) unsigned NOT NULL default '0',
                last_fail datetime NOT NULL,
                UNIQUE KEY ip (ip)) $charset_spec");
    }

    if (version_compare($oldversion, '2.6.1', '<')) {
        Database::get()->query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('login_fail_check', 1),
                                        ('login_fail_threshold', 15),
                                        ('login_fail_deny_interval', 5),
                                        ('login_fail_forgive_interval', 24)");
    }

    if (version_compare($oldversion, '2.7', '<')) {
        DBHelper::fieldExists('document', 'extra_path') or
                Database::get()->query("ALTER TABLE `document` ADD `extra_path` VARCHAR(255) NOT NULL DEFAULT '' AFTER `path`");
        DBHelper::fieldExists('user', 'parent_email') or
                Database::get()->query("ALTER TABLE `user` ADD `parent_email` VARCHAR(100) NOT NULL DEFAULT '' AFTER `email`");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `parents_announcements` (
                        `id` mediumint(9) NOT NULL auto_increment,
                        `title` varchar(255) default NULL,
                        `content` text,
                        `date` datetime default NULL,
                        `sender_id` int(11) NOT NULL,
                        `recipient_id` int(11) NOT NULL,
                        `course_id` int(11) NOT NULL,
                         PRIMARY KEY (`id`)) $charset_spec");
    }

    if (version_compare($oldversion, '2.8', '<')) {
        Database::get()->query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('course_metadata', 0),
                                        ('opencourses_enable', 0)");

        DBHelper::fieldExists('document', 'public') or
                Database::get()->query("ALTER TABLE `document` ADD `public` TINYINT(4) NOT NULL DEFAULT 1 AFTER `visibility`");
        DBHelper::fieldExists('cours', 'course_license') or
                Database::get()->query("ALTER TABLE `cours` ADD COLUMN `course_license` TINYINT(4) NOT NULL DEFAULT '0' AFTER `course_addon`");
        DBHelper::fieldExists("cours_user", "reviewer") or
                Database::get()->query("ALTER TABLE `cours_user` ADD `reviewer` INT(11) NOT NULL DEFAULT '0'");

        // prevent dir list under video storage
        if ($handle = opendir($webDir . '/video/')) {
            while (false !== ($entry = readdir($handle))) {
                if (is_dir($webDir . '/video/' . $entry) && $entry != "." && $entry != "..") {
                    touch_or_error($webDir . '/video/' . $entry . '/index.php');
                }
            }
            closedir($handle);
        }
    }

    if (version_compare($oldversion, '2.8.3', '<')) {
        Database::get()->query("CREATE TABLE course_review (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `is_certified` BOOL NOT NULL DEFAULT 0,
                `level` TINYINT(4) NOT NULL DEFAULT 0,
                `last_review` DATETIME NOT NULL,
                `last_reviewer` INT(11) NOT NULL,
                PRIMARY KEY (id)) $charset_spec");

        require_once 'modules/course_metadata/CourseXML.php';
        Database::get()->queryFunc("SELECT cours_id, code FROM cours", function ($course) {
            $xml = CourseXMLElement::initFromFile($course->code);
            if ($xml !== false) {
                $xmlData = $xml->asFlatArray();

                $is_certified = 0;
                if ((isset($xmlData['course_confirmAMinusLevel']) && $xmlData['course_confirmAMinusLevel'] == 'true') ||
                        (isset($xmlData['course_confirmALevel']) && $xmlData['course_confirmALevel'] == 'true') ||
                        (isset($xmlData['course_confirmAPlusLevel']) && $xmlData['course_confirmAPlusLevel'] == 'true')) {
                    $is_certified = 1;
                }

                $level = CourseXMLElement::NO_LEVEL;
                if (isset($xmlData['course_confirmAMinusLevel']) && $xmlData['course_confirmAMinusLevel'] == 'true') {
                    $level = CourseXMLElement::A_MINUS_LEVEL;
                }
                if (isset($xmlData['course_confirmALevel']) && $xmlData['course_confirmALevel'] == 'true') {
                    $level = CourseXMLElement::A_LEVEL;
                }
                if (isset($xmlData['course_confirmAPlusLevel']) && $xmlData['course_confirmAPlusLevel'] == 'true') {
                    $level = CourseXMLElement::A_PLUS_LEVEL;
                }

                $last_review = date('Y-m-d H:i:s');
                if (isset($xmlData['course_lastLevelConfirmation']) &&
                        strlen($xmlData['course_lastLevelConfirmation']) > 0 &&
                        ($ts = strtotime($xmlData['course_lastLevelConfirmation'])) > 0) {
                    $last_review = date('Y-m-d H:i:s', $ts);
                }

                Database::get()->query("INSERT INTO course_review (course_id, is_certified, level, last_review, last_reviewer)
                                VALUES (" . $course->cours_id . ", $is_certified, $level, '$last_review', $uid)");
            }
        });
    }

    if (version_compare($oldversion, '2.10', '<')) {
        DBHelper::fieldExists('course_units', 'public') or
                Database::get()->query("ALTER TABLE `course_units` ADD `public` TINYINT(4) NOT NULL DEFAULT '1' AFTER `visibility`");

        if (!DBHelper::tableExists('course_description_type')) {
            Database::get()->query("CREATE TABLE `course_description_type` (
                            `id` smallint(6) NOT NULL AUTO_INCREMENT,
                            `title` mediumtext,
                            `syllabus` tinyint(1) DEFAULT 0,
                            `objectives` tinyint(1) DEFAULT 0,
                            `bibliography` tinyint(1) DEFAULT 0,
                            `teaching_method` tinyint(1) DEFAULT 0,
                            `assessment_method` tinyint(1) DEFAULT 0,
                            `prerequisites` tinyint(1) DEFAULT 0,
                            `featured_books` tinyint(1) DEFAULT 0,
                            `instructors` tinyint(1) DEFAULT 0,
                            `target_group` tinyint(1) DEFAULT 0,
                            `active` tinyint(1) DEFAULT 1,
                            `order` int(11) NOT NULL,
                            `icon` varchar(255) NOT NULL,
                            PRIMARY KEY (`id`)) $charset_spec");

            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `syllabus`, `order`, `icon`) VALUES (1, 'a:2:{s:2:\"el\";s:41:\"Περιεχόμενο μαθήματος\";s:2:\"en\";s:15:\"Course Syllabus\";}', 1, 1, '0.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `objectives`, `order`, `icon`) VALUES (2, 'a:2:{s:2:\"el\";s:33:\"Μαθησιακοί στόχοι\";s:2:\"en\";s:23:\"Course Objectives/Goals\";}', 1, 2, '1.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `bibliography`, `order`, `icon`) VALUES (3, 'a:2:{s:2:\"el\";s:24:\"Βιβλιογραφία\";s:2:\"en\";s:12:\"Bibliography\";}', 1, 3, '2.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `teaching_method`, `order`, `icon`) VALUES (4, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι διδασκαλίας\";s:2:\"en\";s:21:\"Instructional Methods\";}', 1, 4, '3.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `assessment_method`, `order`, `icon`) VALUES (5, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι αξιολόγησης\";s:2:\"en\";s:18:\"Assessment Methods\";}', 1, 5, '4.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `prerequisites`, `order`, `icon`) VALUES (6, 'a:2:{s:2:\"el\";s:28:\"Προαπαιτούμενα\";s:2:\"en\";s:29:\"Prerequisites/Prior Knowledge\";}', 1, 6, '5.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `instructors`, `order`, `icon`) VALUES (7, 'a:2:{s:2:\"el\";s:22:\"Διδάσκοντες\";s:2:\"en\";s:11:\"Instructors\";}', 1, 7, '6.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `target_group`, `order`, `icon`) VALUES (8, 'a:2:{s:2:\"el\";s:23:\"Ομάδα στόχος\";s:2:\"en\";s:12:\"Target Group\";}', 1, 8, '7.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `featured_books`, `order`, `icon`) VALUES (9, 'a:2:{s:2:\"el\";s:47:\"Προτεινόμενα συγγράμματα\";s:2:\"en\";s:9:\"Textbooks\";}', 1, 9, '8.png')");
            Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `order`, `icon`) VALUES (10, 'a:2:{s:2:\"el\";s:22:\"Περισσότερα\";s:2:\"en\";s:15:\"Additional info\";}', 11, 'default.png')");
        }

        // Drop obsolete course_description table if needed
       if (DBHelper::tableExists('course_description') and
           DBHelper::fieldExists('course_description', 'upDate')) {
           Database::get()->query('DROP TABLE course_description');
       }

        if (!DBHelper::tableExists('course_description')) {
            Database::get()->query("CREATE TABLE IF NOT EXISTS `course_description` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `course_id` int(11) NOT NULL,
                            `title` varchar(255) NOT NULL,
                            `comments` mediumtext,
                            `type` smallint(6),
                            `visible` tinyint(4) DEFAULT 0,
                            `order` int(11) NOT NULL,
                            `update_dt` datetime NOT NULL,
                            PRIMARY KEY (`id`)) $charset_spec");

            Database::get()->query('CREATE INDEX `cid` ON course_description (course_id)');
            Database::get()->query('CREATE INDEX `cd_type_index` ON course_description (type)');
            Database::get()->query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, type)');

            Database::get()->queryFunc("SELECT ur.id, ur.res_id, ur.title, ur.comments, ur.order, ur.visibility, ur.date, cu.course_id
                                FROM unit_resources ur LEFT JOIN course_units cu ON (cu.id = ur.unit_id) WHERE cu.order = -1 AND ur.res_id <> -1", function($ures) {
                $newvis = ($ures->visibility == 'i') ? 0 : 1;
                Database::get()->query("INSERT INTO course_description SET
                                course_id = ?d, title = ?s, comments = ?s,
                                visible = ?d, `order` = ?d, update_dt = ?t", intval($ures->course_id), $ures->title, $ures->comments, intval($newvis), intval($ures->order), $ures->date);
                Database::get()->query("DELETE FROM unit_resources WHERE id = ?d", intval($ures->id));
            });
        }

        if (!DBHelper::tableExists('oai_record')) {
            Database::get()->query("CREATE TABLE `oai_record` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `course_id` int(11) NOT NULL UNIQUE,
                            `oai_identifier` varchar(255) DEFAULT NULL,
                            `oai_metadataprefix` varchar(255) DEFAULT 'oai_dc',
                            `oai_set` varchar(255) DEFAULT 'class:course',
                            `datestamp` datetime DEFAULT NULL,
                            `deleted` tinyint(1) NOT NULL DEFAULT 0,
                            `dc_title` text DEFAULT NULL,
                            `dc_description` text DEFAULT NULL,
                            `dc_syllabus` text DEFAULT NULL,
                            `dc_subject` text DEFAULT NULL,
                            `dc_subsubject` text DEFAULT NULL,
                            `dc_objectives` text DEFAULT NULL,
                            `dc_level` text DEFAULT NULL,
                            `dc_prerequisites` text DEFAULT NULL,
                            `dc_instructor` text DEFAULT NULL,
                            `dc_department` text DEFAULT NULL,
                            `dc_institution` text DEFAULT NULL,
                            `dc_coursephoto` text DEFAULT NULL,
                            `dc_coursephotomime` text DEFAULT NULL,
                            `dc_instructorphoto` text DEFAULT NULL,
                            `dc_instructorphotomime` text DEFAULT NULL,
                            `dc_url` text DEFAULT NULL,
                            `dc_identifier` text DEFAULT NULL,
                            `dc_language` text DEFAULT NULL,
                            `dc_date` datetime DEFAULT NULL,
                            `dc_format` text DEFAULT NULL,
                            `dc_rights` text DEFAULT NULL,
                            `dc_videolectures` text DEFAULT NULL,
                            `dc_code` text DEFAULT NULL,
                            `dc_keywords` text DEFAULT NULL,
                            `dc_contentdevelopment` text DEFAULT NULL,
                            `dc_formattypes` text DEFAULT NULL,
                            `dc_recommendedcomponents` text DEFAULT NULL,
                            `dc_assignments` text DEFAULT NULL,
                            `dc_requirements` text DEFAULT NULL,
                            `dc_remarks` text DEFAULT NULL,
                            `dc_acknowledgments` text DEFAULT NULL,
                            `dc_coteaching` text DEFAULT NULL,
                            `dc_coteachingcolleagueopenscourse` text DEFAULT NULL,
                            `dc_coteachingautonomousdepartment` text DEFAULT NULL,
                            `dc_coteachingdepartmentcredithours` text DEFAULT NULL,
                            `dc_yearofstudy` text DEFAULT NULL,
                            `dc_semester` text DEFAULT NULL,
                            `dc_coursetype` text DEFAULT NULL,
                            `dc_credithours` text DEFAULT NULL,
                            `dc_credits` text DEFAULT NULL,
                            `dc_institutiondescription` text DEFAULT NULL,
                            `dc_curriculumtitle` text DEFAULT NULL,
                            `dc_curriculumdescription` text DEFAULT NULL,
                            `dc_outcomes` text DEFAULT NULL,
                            `dc_curriculumkeywords` text DEFAULT NULL,
                            `dc_sector` text DEFAULT NULL,
                            `dc_targetgroup` text DEFAULT NULL,
                            `dc_curriculumtargetgroup` text DEFAULT NULL,
                            `dc_featuredbooks` text DEFAULT NULL,
                            `dc_structure` text DEFAULT NULL,
                            `dc_teachingmethod` text DEFAULT NULL,
                            `dc_assessmentmethod` text DEFAULT NULL,
                            `dc_eudoxuscode` text DEFAULT NULL,
                            `dc_eudoxusurl` text DEFAULT NULL,
                            `dc_kalliposurl` text DEFAULT NULL,
                            `dc_numberofunits` text DEFAULT NULL,
                            `dc_unittitle` text DEFAULT NULL,
                            `dc_unitdescription` text DEFAULT NULL,
                            `dc_unitkeywords` text DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `oai_identifier` (`oai_identifier`)) $charset_spec");

            Database::get()->query('CREATE INDEX `cid` ON oai_record (course_id)');
            Database::get()->query('CREATE INDEX `oaiid` ON oai_record (oai_identifier)');
        }

        // unique course_id for course_review
        $crevres = Database::get()->queryArray("SELECT DISTINCT course_id FROM course_review");
        foreach ($crevres as $crev) {
            $crevres2 = Database::get()->queryArray("SELECT * FROM course_review WHERE course_id = ?d ORDER BY last_review DESC", intval($crev->course_id));
            $crevcnt = 0;
            foreach ($crevres2 as $crev2) {
                if ($crevcnt > 0) {
                    Database::get()->query("DELETE FROM course_review WHERE id = ?d", intval($crev2['id']));
                }
                $crevcnt++;
            }
        }
        Database::get()->query("ALTER TABLE course_review ADD UNIQUE crid (course_id)");

        if (!DBHelper::fieldExists('document', 'editable')) {
            Database::get()->query("ALTER TABLE `document` ADD editable TINYINT(1) NOT NULL DEFAULT 0,
                                                         ADD lock_user_id INT(11) NOT NULL DEFAULT 0");
        }
    }

    if (version_compare($oldversion, '3.0b2', '<')) {
        // Check whether new tables already exist and delete them if empty,
        // rename them otherwise
        $new_tables = array('cron_params', 'log', 'log_archive', 'forum',
            'forum_category', 'forum_post', 'forum_topic',
            'video', 'videolink', 'dropbox_msg', 'dropbox_attachment', 'dropbox_index',
            'lp_module', 'lp_learnPath', 'lp_rel_learnPath_module', 'lp_asset',
            'lp_user_module_progress', 'wiki_properties', 'wiki_acls', 'wiki_pages',
            'wiki_pages_content', 'poll', 'poll_answer_record', 'poll_question',
            'poll_question_answer', 'assignment', 'assignment_submit',
            'exercise', 'exercise_user_record', 'exercise_question',
            'exercise_answer', 'exercise_with_questions', 'course_module',
            'actions', 'actions_summary', 'logins', 'wiki_locks', 'bbb_servers', 'bbb_session',
            'blog_post', 'comments', 'rating', 'rating_cache', 'forum_user_stats');
        foreach ($new_tables as $table_name) {
            if (DBHelper::tableExists($table_name)) {
                if (Database::get()->querySingle("SELECT COUNT(*) AS c FROM `$table_name`")->c > 0) {
                    echo "Warning: Database inconsistent - table '$table_name' already",
                    " exists in $mysqlMainDb - renaming it to 'old_$table_name'<br>\n";
                    Database::get()->query("RENAME TABLE `$table_name` TO `old_$table_name`");
                } else {
                    Database::get()->query("DROP TABLE `$table_name`");
                }
            }
        }

        Database::get()->query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                                        ('actions_expire_interval', 12),
                                        ('course_metadata', 0)");

        if (!DBHelper::fieldExists('user_request', 'state')) {
            Database::get()->query("ALTER TABLE `user_request`
                    CHANGE `name` `givenname` VARCHAR(100) NOT NULL DEFAULT '',
                    CHANGE `surname` `surname` VARCHAR(100) NOT NULL DEFAULT '',
                    CHANGE `uname` `username` VARCHAR(100) NOT NULL DEFAULT '',
                    CHANGE `email` `email` VARCHAR(100) NOT NULL DEFAULT '',
                    CHANGE `status` `state` INT(11) NOT NULL DEFAULT 0,
                    CHANGE `statut` `status` TINYINT(4) NOT NULL DEFAULT 1");
        }

        Database::get()->query("CREATE TABLE `cron_params` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `name` VARCHAR(255) NOT NULL UNIQUE,
                        `last_run` DATETIME NOT NULL)
                        $charset_spec");

        Database::get()->query("DROP TABLE IF EXISTS passwd_reset");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `log` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `user_id` INT(11) NOT NULL DEFAULT 0,
                        `course_id` INT(11) NOT NULL DEFAULT 0,
                        `module_id` INT(11) NOT NULL default 0,
                        `details` TEXT NOT NULL,
                        `action_type` INT(11) NOT NULL DEFAULT 0,
                        `ts` DATETIME NOT NULL,
                        `ip` VARCHAR(45) NOT NULL DEFAULT '',
                        PRIMARY KEY (`id`))
                        $charset_spec");


        Database::get()->query("CREATE TABLE IF NOT EXISTS `log_archive` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `user_id` INT(11) NOT NULL DEFAULT 0,
                        `course_id` INT(11) NOT NULL DEFAULT 0,
                        `module_id` INT(11) NOT NULL default 0,
                        `details` TEXT NOT NULL,
                        `action_type` INT(11) NOT NULL DEFAULT 0,
                        `ts` DATETIME NOT NULL,
                        `ip` VARCHAR(45) NOT NULL DEFAULT '',
                        PRIMARY KEY (`id`))
                        $charset_spec");

        // add index on `loginout`.`id_user` for performace
        Database::get()->query("ALTER TABLE `loginout` ADD INDEX (`id_user`)");

        // update table admin_announcement
        if (!DBHelper::tableExists('admin_announcement')) {
            Database::get()->query("RENAME TABLE `admin_announcements` TO `admin_announcement`");
            Database::get()->query("ALTER TABLE admin_announcement CHANGE `ordre` `order` MEDIUMINT(11)");
            Database::get()->query("ALTER TABLE admin_announcement CHANGE `visible` `visible` TEXT");
            Database::get()->query("UPDATE admin_announcement SET visible = '1' WHERE visible = 'V'");
            Database::get()->query("UPDATE admin_announcement SET visible = '0' WHERE visible = 'I'");
            Database::get()->query("ALTER TABLE admin_announcement CHANGE `visible` `visible` TINYINT(4)");
        }

        // update table course_units and unit_resources
        if (!DBHelper::fieldExists('course_units', 'visible')) {
            Database::get()->query("UPDATE `course_units` SET visibility = '1' WHERE visibility = 'v'");
            Database::get()->query("UPDATE `course_units` SET visibility = '0' WHERE visibility = 'i'");
            Database::get()->query("ALTER TABLE `course_units` CHANGE `visibility` `visible` TINYINT(4) DEFAULT 0");
        }

        if (!DBHelper::fieldExists('unit_resources', 'visible')) {
            Database::get()->query("UPDATE `unit_resources` SET visibility = '1' WHERE visibility = 'v'");
            Database::get()->query("UPDATE `unit_resources` SET visibility = '0' WHERE visibility = 'i'");
            Database::get()->query("ALTER TABLE `unit_resources` CHANGE `visibility` `visible` TINYINT(4) DEFAULT 0");
        }

        // update table document
        if (!DBHelper::fieldExists('document', 'visible')) {
            Database::get()->query("UPDATE `document` SET visibility = '1' WHERE visibility = 'v'");
            Database::get()->query("UPDATE `document` SET visibility = '0' WHERE visibility = 'i'");
            Database::get()->query("ALTER TABLE `document`
                                CHANGE `visibility` `visible` TINYINT(4) NOT NULL DEFAULT 1");
        }

        // Rename table `annonces` to `announcements`
        if (!DBHelper::tableExists('announcement')) {
            if (DBHelper::indexExists('annonces', 'annonces')) {
                Database::get()->query("ALTER TABLE annonces DROP INDEX annonces");
            }
            Database::get()->query("RENAME TABLE annonces TO announcement");
            Database::get()->query("UPDATE announcement SET visibility = '0' WHERE visibility <> 'v'");
            Database::get()->query("UPDATE announcement SET visibility = '1' WHERE visibility = 'v'");
            Database::get()->query("ALTER TABLE announcement CHANGE `contenu` `content` TEXT,
                                       CHANGE `temps` `date` DATETIME,
                                       CHANGE `cours_id` `course_id` INT(11),
                                       CHANGE `ordre` `order` MEDIUMINT(11),
                                       CHANGE `visibility` `visible` TINYINT(4) DEFAULT 0,
                                       ADD `start_display` DATE NOT NULL DEFAULT '2014-01-01',
                                       ADD `stop_display` DATE NOT NULL DEFAULT '2094-12-31'");
        } else {
            Database::get()->query("ALTER TABLE announcement
                                       ADD `start_display` DATE NOT NULL DEFAULT '2014-01-01',
                                       ADD `stop_display` DATE NOT NULL DEFAULT '2094-12-31'");
        }

        // create forum tables
        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum` (
                            `id` INT(10) NOT NULL AUTO_INCREMENT,
                            `name` VARCHAR(150) DEFAULT '' NOT NULL,
                            `desc` MEDIUMTEXT NOT NULL,
                            `num_topics` INT(10) DEFAULT 0 NOT NULL,
                            `num_posts` INT(10) DEFAULT 0 NOT NULL,
                            `last_post_id` INT(10) DEFAULT 0 NOT NULL,
                            `cat_id` INT(10) DEFAULT 0 NOT NULL,
                            `course_id` INT(11) NOT NULL,
                            PRIMARY KEY (`id`))
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_category` (
                            `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `cat_title` VARCHAR(100) DEFAULT '' NOT NULL,
                            `cat_order` INT(11) DEFAULT 0 NOT NULL,
                            `course_id` INT(11) NOT NULL,
                            KEY `forum_category_index` (`id`, `course_id`))
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                            `user_id` INT(11) DEFAULT 0 NOT NULL,
                            `cat_id` INT(11) DEFAULT 0 NOT NULL ,
                            `forum_id` INT(11) DEFAULT 0 NOT NULL,
                            `topic_id` INT(11) DEFAULT 0 NOT NULL ,
                            `notify_sent` BOOL DEFAULT 0 NOT NULL ,
                            `course_id` INT(11) DEFAULT 0 NOT NULL)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_post` (
                            `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `topic_id` INT(10) NOT NULL DEFAULT 0,
                            `post_text` MEDIUMTEXT NOT NULL,
                            `poster_id` INT(10) NOT NULL DEFAULT 0,
                            `post_time` DATETIME,
                            `poster_ip` VARCHAR(45) DEFAULT '' NOT NULL,
                            `parent_post_id` INT(10) NOT NULL DEFAULT 0)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_topic` (
                            `id` int(10) NOT NULL auto_increment,
                            `title` varchar(100) DEFAULT NULL,
                            `poster_id` int(10) DEFAULT NULL,
                            `topic_time` datetime,
                            `num_views` int(10) NOT NULL default '0',
                            `num_replies` int(10) NOT NULL default '0',
                            `last_post_id` int(10) NOT NULL default '0',
                            `forum_id` int(10) NOT NULL default '0',
                            `locked` TINYINT DEFAULT 0 NOT NULL,
                            PRIMARY KEY (`id`))
                            $charset_spec");

        if (!DBHelper::fieldExists('forum_topic', 'locked')) {
            Database::get()->query("ALTER TABLE `forum_topic` ADD `locked` TINYINT DEFAULT 0 NOT NULL");
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_user_stats` (
                            `user_id` INT(11) NOT NULL,
                            `num_posts` INT(11) NOT NULL,
                            `course_id` INT(11) NOT NULL,
                            PRIMARY KEY (`user_id`,`course_id`)) $charset_spec");

        $forum_stats = Database::get()->queryArray("SELECT forum.course_id, forum_post.poster_id, count(*) as c FROM forum_post
                            INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                            INNER JOIN forum ON forum.id = forum_topic.forum_id
                            GROUP BY forum.course_id, forum_post.poster_id");

        if ($forum_stats) {
            $query = "INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES ";
            $vars_to_flatten = array();
            foreach ($forum_stats as $forum_stat) {
                $query .= "(?d,?d,?d),";
                $vars_to_flatten[] = $forum_stat->poster_id;
                $vars_to_flatten[] = $forum_stat->c;
                $vars_to_flatten[] = $forum_stat->course_id;
            }
            $query = rtrim($query, ',');
            Database::get()->query($query, $vars_to_flatten);
        }

        // create video tables
        Database::get()->query("CREATE TABLE IF NOT EXISTS video (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `path` VARCHAR(255),
                            `url` VARCHAR(200),
                            `title` VARCHAR(200),
                            `category` INT(6) DEFAULT NULL,
                            `description` TEXT,
                            `creator` VARCHAR(200),
                            `publisher` VARCHAR(200),
                            `date` DATETIME,
                            `visible` TINYINT(4) NOT NULL DEFAULT 1,
                            `public` TINYINT(4) NOT NULL DEFAULT 1)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS videolink (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `url` VARCHAR(200) NOT NULL DEFAULT '',
                            `title` VARCHAR(200) NOT NULL DEFAULT '',
                            `category` INT(6) DEFAULT NULL,
                            `description` TEXT NOT NULL,
                            `creator` VARCHAR(200) NOT NULL DEFAULT '',
                            `publisher` VARCHAR(200) NOT NULL DEFAULT '',
                            `date` DATETIME,
                            `visible` TINYINT(4) NOT NULL DEFAULT 1,
                            `public` TINYINT(4) NOT NULL DEFAULT 1)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS video_category (
                            id INT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            name VARCHAR(255) NOT NULL,
                            description TEXT DEFAULT NULL)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_msg (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `author_id` INT(11) UNSIGNED NOT NULL,
                            `subject` VARCHAR(250) NOT NULL,
                            `body` LONGTEXT NOT NULL,
                            `timestamp` INT(11) NOT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_attachment (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `msg_id` INT(11) UNSIGNED NOT NULL,
                            `filename` VARCHAR(250) NOT NULL,
                            `real_filename` varchar(255) NOT NULL,
                            `filesize` INT(11) UNSIGNED NOT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_index (
                            `msg_id` INT(11) UNSIGNED NOT NULL,
                            `recipient_id` INT(11) UNSIGNED NOT NULL,
                            `is_read` BOOLEAN NOT NULL DEFAULT 0,
                            `deleted` BOOLEAN NOT NULL DEFAULT 0,
                            PRIMARY KEY (`msg_id`, `recipient_id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_module` (
                            `module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `name` VARCHAR(255) NOT NULL DEFAULT '',
                            `comment` TEXT NOT NULL,
                            `accessibility` enum('PRIVATE','PUBLIC') NOT NULL DEFAULT 'PRIVATE',
                            `startAsset_id` INT(11) NOT NULL DEFAULT 0,
                            `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK','MEDIA','MEDIALINK') NOT NULL,
                            `launch_data` TEXT NOT NULL)
                            $charset_spec");
        //COMMENT='List of available modules used in learning paths';
        Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_learnPath` (
                            `learnPath_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `name` VARCHAR(255) NOT NULL DEFAULT '',
                            `comment` TEXT NOT NULL,
                            `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
                            `visible` TINYINT(4) NOT NULL DEFAULT 0,
                            `rank` INT(11) NOT NULL DEFAULT 0)
                            $charset_spec");
        //COMMENT='List of learning Paths';
        Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_rel_learnPath_module` (
                            `learnPath_module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `learnPath_id` INT(11) NOT NULL DEFAULT 0,
                            `module_id` INT(11) NOT NULL DEFAULT 0,
                            `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
                            `visible` TINYINT(4),
                            `specificComment` TEXT NOT NULL,
                            `rank` INT(11) NOT NULL DEFAULT '0',
                            `parent` INT(11) NOT NULL DEFAULT '0',
                            `raw_to_pass` TINYINT(4) NOT NULL DEFAULT '50')
                            $charset_spec");
        //COMMENT='This table links module to the learning path using them';
        Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_asset` (
                            `asset_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `module_id` INT(11) NOT NULL DEFAULT '0',
                            `path` VARCHAR(255) NOT NULL DEFAULT '',
                            `comment` VARCHAR(255) default NULL)
                            $charset_spec");
        //COMMENT='List of resources of module of learning paths';
        Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_user_module_progress` (
                            `user_module_progress_id` INT(22) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
                            `learnPath_module_id` INT(11) NOT NULL DEFAULT '0',
                            `learnPath_id` INT(11) NOT NULL DEFAULT '0',
                            `lesson_location` VARCHAR(255) NOT NULL DEFAULT '',
                            `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
                            `entry` enum('AB-INITIO','RESUME','') NOT NULL DEFAULT 'AB-INITIO',
                            `raw` TINYINT(4) NOT NULL DEFAULT '-1',
                            `scoreMin` TINYINT(4) NOT NULL DEFAULT '-1',
                            `scoreMax` TINYINT(4) NOT NULL DEFAULT '-1',
                            `total_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
                            `session_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
                            `suspend_data` TEXT NOT NULL,
                            `credit` enum('CREDIT','NO-CREDIT') NOT NULL DEFAULT 'NO-CREDIT')
                            $charset_spec");
        //COMMENT='Record the last known status of the user in the course';
        DBHelper::indexExists('lp_user_module_progress', 'optimize') or
                Database::get()->query('CREATE INDEX `optimize` ON lp_user_module_progress (user_id, learnPath_module_id)');

        Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_properties` (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `description` TEXT NULL,
                            `group_id` INT(11) NOT NULL DEFAULT 0 )
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_acls` (
                            `wiki_id` INT(11) UNSIGNED NOT NULL,
                            `flag` VARCHAR(255) NOT NULL,
                            `value` ENUM('false','true') NOT NULL DEFAULT 'false',
                            PRIMARY KEY (wiki_id, flag))
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_pages` (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `wiki_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                            `owner_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `ctime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `last_version` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                            `last_mtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' )
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_pages_content` (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                            `editor_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `mtime` DATETIME NOT NULL default '0000-00-00 00:00:00',
                            `content` TEXT NOT NULL,
                            `changelog` VARCHAR(200) )  $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_locks` (
                            `ptitle` VARCHAR(255) NOT NULL DEFAULT '',
                            `wiki_id` INT(11) UNSIGNED NOT NULL,
                            `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `ltime_created` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `ltime_alive` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (ptitle, wiki_id) ) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `blog_post` (
                            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `content` TEXT NOT NULL,
                            `time` DATETIME NOT NULL,
                            `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
                            `commenting` TINYINT NOT NULL DEFAULT '1',
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `course_id` INT(11) NOT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `comments` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `rid` INT(11) NOT NULL,
                            `rtype` VARCHAR(50) NOT NULL,
                            `content` TEXT NOT NULL,
                            `time` DATETIME NOT NULL,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `rating` (
                            `rate_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `rid` INT(11) NOT NULL,
                            `rtype` VARCHAR(50) NOT NULL,
                            `value` TINYINT NOT NULL,
                            `widget` VARCHAR(30) NOT NULL,
                            `time` DATETIME NOT NULL,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `rating_source` VARCHAR(50) NOT NULL,
                            INDEX `rating_index_1` (`rid`, `rtype`, `widget`),
                            INDEX `rating_index_2` (`rid`, `rtype`, `user_id`, `widget`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `rating_cache` (
                            `rate_cache_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `rid` INT(11) NOT NULL,
                            `rtype` VARCHAR(50) NOT NULL,
                            `value` FLOAT NOT NULL DEFAULT 0,
                            `count` INT(11) NOT NULL DEFAULT 0,
                            `tag` VARCHAR(50),
                            INDEX `rating_cache_index_1` (`rid`, `rtype`, `tag`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook` (
                            `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
                            `range` TINYINT(4) NOT NULL DEFAULT 10) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook_activities` (
                            `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `gradebook_id` MEDIUMINT(11) NOT NULL,
                            `title` VARCHAR(250) DEFAULT NULL,
                            `activity_type` INT(11) DEFAULT NULL,
                            `date` DATETIME DEFAULT NULL,
                            `description` TEXT NOT NULL,
                            `weight` MEDIUMINT(11) NOT NULL DEFAULT 0,
                            `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
                            `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
                            `auto` TINYINT(4) NOT NULL DEFAULT 0,
                            `visible` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook_book` (
                            `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `gradebook_activity_id` MEDIUMINT(11) NOT NULL,
                            `uid` int(11) NOT NULL DEFAULT 0,
                            `grade` FLOAT NOT NULL DEFAULT -1,
                            `comments` TEXT NOT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook_users` (
                            `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `gradebook_id` MEDIUMINT(11) NOT NULL,
                            `uid` int(11) NOT NULL DEFAULT 0) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance` (
                                `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `limit` TINYINT(4) NOT NULL DEFAULT 0,
                                `students_semester` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance_activities` (
                                `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `attendance_id` MEDIUMINT(11) NOT NULL,
                                `title` VARCHAR(250) DEFAULT NULL,
                                `date` DATETIME DEFAULT NULL,
                                `description` TEXT NOT NULL,
                                `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
                                `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
                                `auto` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance_book` (
                                `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `attendance_activity_id` MEDIUMINT(11) NOT NULL,
                                `uid` int(11) NOT NULL DEFAULT 0,
                                `attend` TINYINT(4) NOT NULL DEFAULT 0,
                                `comments` TEXT NOT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance_users` (
                                `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `attendance_id` MEDIUMINT(11) NOT NULL,
                                `uid` int(11) NOT NULL DEFAULT 0) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll` (
                            `pid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `creator_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `name` VARCHAR(255) NOT NULL DEFAULT '',
                            `creation_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `end_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `active` INT(11) NOT NULL DEFAULT 0,
                            `description` MEDIUMTEXT NULL DEFAULT NULL,
                            `end_message` MEDIUMTEXT NULL DEFAULT NUll,
                            `anonymized` INT(1) NOT NULL DEFAULT 0)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_answer_record` (
                            `arid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `pid` INT(11) NOT NULL DEFAULT 0,
                            `qid` INT(11) NOT NULL DEFAULT 0,
                            `aid` INT(11) NOT NULL DEFAULT 0,
                            `answer_text` TEXT NOT NULL,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `submit_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00')
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_question` (
                            `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `pid` INT(11) NOT NULL DEFAULT 0,
                            `question_text` VARCHAR(250) NOT NULL DEFAULT '',
                            `qtype` tinyint(3) UNSIGNED NOT NULL,
                            `q_position` INT(11) DEFAULT 1,
                            `q_scale` INT(11) NULL DEFAULT NULL)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_question_answer` (
                            `pqaid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `pqid` INT(11) NOT NULL DEFAULT 0,
                            `answer_text` TEXT NOT NULL)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `title` VARCHAR(200) NOT NULL DEFAULT '',
                            `description` TEXT NOT NULL,
                            `comments` TEXT NOT NULL,
                            `deadline` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `late_submission` TINYINT NOT NULL DEFAULT '0',
                            `submission_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `active` CHAR(1) NOT NULL DEFAULT 1,
                            `secret_directory` VARCHAR(30) NOT NULL,
                            `group_submissions` CHAR(1) DEFAULT 0 NOT NULL,
                            `max_grade` FLOAT DEFAULT NULL,
                            `assign_to_specific` CHAR(1) DEFAULT '0' NOT NULL,
                            `file_path` VARCHAR(200) DEFAULT '' NOT NULL,
                            `file_name` VARCHAR(200) DEFAULT '' NOT NULL)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment_submit` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `assignment_id` INT(11) NOT NULL DEFAULT 0,
                            `submission_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
                            `file_path` VARCHAR(200) NOT NULL DEFAULT '',
                            `file_name` VARCHAR(200) NOT NULL DEFAULT '',
                            `comments` TEXT NOT NULL,
                            `grade` FLOAT DEFAULT NULL,
                            `grade_comments` TEXT NOT NULL,
                            `grade_submission_date` DATE NOT NULL DEFAULT '1000-10-10',
                            `grade_submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
                            `group_id` INT( 11 ) DEFAULT NULL )
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment_to_specific` (
                            `user_id` int(11) NOT NULL,
                            `group_id` int(11) NOT NULL,
                            `assignment_id` int(11) NOT NULL,
                            PRIMARY KEY (user_id, group_id, assignment_id)
                          ) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `title` varchar(200) NOT NULL,
                            `description` text NOT NULL,
                            `preview_rubric` tinyint(1) NOT NULL DEFAULT '0',
                            `rubric_during_evaluation` tinyint(1) NOT NULL DEFAULT '0',
                            `rubric_to_graded` tinyint(1) NOT NULL DEFAULT '0',
                            `points_during_evaluation` tinyint(1) NOT NULL DEFAULT '0',
                            `points_to_graded` tinyint(1) NOT NULL DEFAULT '0',
                            `uid` INT(11) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric_rel` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `rubric_id` INT(11) NOT NULL,
                            `course_id` INT(11) NOT NULL,
                            `module_id` INT(11) NOT NULL,
                            `resource_id` INT(11) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric_criteria` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `rubric_id` int(11) NOT NULL,
                            `sortorder` varchar(30) NOT NULL,
                            `description` text,
                            PRIMARY KEY (`id`)
                         ) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric_levels` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `rubric_id` int(11) NOT NULL,
                            `criterionid` int(10) unsigned NOT NULL,
                            `score` decimal(5,0) NOT NULL,
                            `definition` text NOT NULL,
                            PRIMARY KEY (`id`)
                          ) $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric_assesment` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `as_sub_id` int(11) NOT NULL,
                            `uid` int(11) NOT NULL,
                            `level_chosen_id` int(11) NOT NULL,
                            `level_feedback` varchar(60) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) $charset_spec ");


        Database::get()->query("DROP TABLE IF EXISTS agenda");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `agenda` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `title` VARCHAR(200) NOT NULL,
                            `content` TEXT NOT NULL,
                            `start` DATETIME NOT NULL DEFAULT '0000-00-00',
                            `duration` VARCHAR(20) NOT NULL,
                            `visible` TINYINT(4),
                             recursion_period varchar(30) DEFAULT NULL,
                             recursion_end date DEFAULT NULL,
                             `source_event_id` int(11) DEFAULT NULL)
                             $charset_spec");


        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `title` VARCHAR(250) DEFAULT NULL,
                            `description` TEXT,
                            `type` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1',
                            `start_date` DATETIME DEFAULT NULL,
                            `end_date` DATETIME DEFAULT NULL,
                            `temp_save` TINYINT(1) NOT NULL DEFAULT 0,
                            `time_constraint` INT(11) DEFAULT 0,
                            `attempts_allowed` INT(11) DEFAULT 0,
                            `random` SMALLINT(6) NOT NULL DEFAULT 0,
                            `active` TINYINT(4) NOT NULL DEFAULT 1,
                            `public` TINYINT(4) NOT NULL DEFAULT 1,
                            `results` TINYINT(1) NOT NULL DEFAULT 1,
                            `score` TINYINT(1) NOT NULL DEFAULT 1)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_user_record` (
                            `eurid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `eid` INT(11) NOT NULL DEFAULT '0',
                            `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
                            `record_start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `record_end_date` DATETIME DEFAULT NULL,
                            `total_score` FLOAT(5,2) NOT NULL DEFAULT '0',
                            `total_weighting` FLOAT(5,2) DEFAULT '0',
                            `attempt` INT(11) NOT NULL DEFAULT '0',
                            `attempt_status` TINYINT(4) NOT NULL DEFAULT '1',
                            `secs_remaining` INT(11) NOT NULL DEFAULT '0')
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_answer_record` (
                            `answer_record_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `eurid` int(11) NOT NULL,
                            `question_id` int(11) NOT NULL,
                            `answer` text,
                            `answer_id` int(11) NOT NULL,
                            `weight` float(5,2) DEFAULT NULL,
                            `is_answered` TINYINT NOT NULL DEFAULT '1')
                             $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_question` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `question` TEXT,
                            `description` TEXT,
                            `weight` FLOAT(11,2) DEFAULT NULL,
                            `q_position` INT(11) DEFAULT 1,
                            `type` INT(11) DEFAULT 1,
                            `difficulty` INT(1) DEFAULT 0,
                            `category` INT(11) DEFAULT 0)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_question_cats` (
                            `question_cat_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `question_cat_name` VARCHAR(300) NOT NULL,
                            `course_id` INT(11) NOT NULL)
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_answer` (
                            `id` INT(11) NOT NULL DEFAULT '0',
                            `question_id` INT(11) NOT NULL DEFAULT '0',
                            `answer` TEXT,
                            `correct` INT(11) DEFAULT NULL,
                            `comment` TEXT,
                            `weight` FLOAT(5,2),
                            `r_position` INT(11) DEFAULT NULL,
                            PRIMARY KEY (id, question_id) )
                            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_with_questions` (
                            `question_id` INT(11) NOT NULL DEFAULT '0',
                            `exercise_id` INT(11) NOT NULL DEFAULT '0',
                            PRIMARY KEY (question_id, exercise_id) )");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_module` (
                            `id` int(11) NOT NULL auto_increment,
                            `module_id` int(11) NOT NULL,
                            `visible` tinyint(4) NOT NULL,
                            `course_id` int(11) NOT NULL,
                            PRIMARY KEY  (`id`),
                            UNIQUE KEY `module_course` (`module_id`,`course_id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `actions` (
                          `id` int(11) NOT NULL auto_increment,
                          `user_id` int(11) NOT NULL,
                          `module_id` int(11) NOT NULL,
                          `action_type_id` int(11) NOT NULL,
                          `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
                          `duration` int(11) NOT NULL default '900',
                          `course_id` INT(11) NOT NULL,
                          PRIMARY KEY  (`id`),
                          KEY `actionsindex` (`module_id`,`date_time`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `actions_summary` (
                          `id` int(11) NOT NULL auto_increment,
                          `module_id` int(11) NOT NULL,
                          `visits` int(11) NOT NULL,
                          `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
                          `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
                          `duration` int(11) NOT NULL,
                          `course_id` INT(11) NOT NULL,
                          PRIMARY KEY  (`id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `logins` (
                          `id` int(11) NOT NULL auto_increment,
                          `user_id` int(11) NOT NULL,
                          `ip` char(45) NOT NULL default '0.0.0.0',
                          `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
                          `course_id` INT(11) NOT NULL,
                          PRIMARY KEY  (`id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `note` (
                         `id` int(11) NOT NULL auto_increment,
                         `user_id` int(11) NOT NULL,
                         `title` varchar(300),
                         `content` text NOT NULL,
                         `date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                         `order` mediumint(11) NOT NULL default 0,
                         `reference_obj_module` mediumint(11) default NULL,
                         `reference_obj_type` enum('course','personalevent','user','course_ebook','course_event','course_assignment','course_document','course_link','course_exercise','course_learningpath','course_video','course_videolink') default NULL,
                         `reference_obj_id` int(11) default NULL,
                         `reference_obj_course` int(11) default NULL,
                        PRIMARY KEY  (`id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_settings` (
                          `setting_id` INT(11) NOT NULL,
                          `course_id` INT(11) NOT NULL,
                          `value` INT(11) NOT NULL DEFAULT 0,
                          PRIMARY KEY (`setting_id`, `course_id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `personal_calendar` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `title` varchar(200) NOT NULL,
                        `content` text NOT NULL,
                        `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `duration` time NOT NULL,
                        `recursion_period` varchar(30) DEFAULT NULL,
                        `recursion_end` date DEFAULT NULL,
                        `source_event_id` int(11) DEFAULT NULL,
                        `reference_obj_module` mediumint(11) DEFAULT NULL,
                        `reference_obj_type` enum('course','personalevent','user','course_ebook','course_event','course_assignment','course_document','course_link','course_exercise','course_learningpath','course_video','course_videolink') DEFAULT NULL,
                        `reference_obj_id` int(11) DEFAULT NULL,
                        `reference_obj_course` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`))");

        Database::get()->query("CREATE TABLE  IF NOT EXISTS `personal_calendar_settings` (
                        `user_id` int(11) NOT NULL,
                        `view_type` enum('day','month','week') DEFAULT 'month',
                        `personal_color` varchar(30) DEFAULT '#5882fa',
                        `course_color` varchar(30) DEFAULT '#acfa58',
                        `deadline_color` varchar(30) DEFAULT '#fa5882',
                        `admin_color` varchar(30) DEFAULT '#eeeeee',
                        `show_personal` bit(1) DEFAULT b'1',
                        `show_course` bit(1) DEFAULT b'1',
                        `show_deadline` bit(1) DEFAULT b'1',
                        `show_admin` bit(1) DEFAULT b'1',
                        PRIMARY KEY (`user_id`))");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `admin_calendar` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `user_id` int(11) NOT NULL,
                                `title` varchar(200) NOT NULL,
                                `content` text NOT NULL,
                                `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                                `duration` time NOT NULL,
                                `recursion_period` varchar(30) DEFAULT NULL,
                                `recursion_end` date DEFAULT NULL,
                                `source_event_id` int(11) DEFAULT NULL,
                                `visibility_level` int(11) DEFAULT '1',
                                `email_notification` time DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                KEY `user_events` (`user_id`),
                                KEY `admin_events_dates` (`start`))");

        //create triggers
        Database::get()->query("DROP TRIGGER IF EXISTS personal_calendar_settings_init");
        Database::get()->query("CREATE TRIGGER personal_calendar_settings_init "
                . "AFTER INSERT ON `user` FOR EACH ROW "
                . "INSERT INTO personal_calendar_settings(user_id) VALUES (NEW.id)");
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) SELECT id FROM user");

        // bbb_sessions tables
        Database::get()->query('CREATE TABLE IF NOT EXISTS `bbb_session` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `course_id` int(11) DEFAULT NULL,
                      `title` varchar(255) DEFAULT NULL,
                      `description` text,
                      `start_date` datetime DEFAULT NULL,
                      `public` enum("0","1") DEFAULT NULL,
                      `active` enum("0","1") DEFAULT NULL,
                      `running_at` int(11) DEFAULT NULL,
                      `meeting_id` varchar(255) DEFAULT NULL,
                      `mod_pw` varchar(255) DEFAULT NULL,
                      `att_pw` varchar(255) DEFAULT NULL,
                      `unlock_interval` int(11) DEFAULT NULL,
                      `external_users` varchar(255) DEFAULT "",
                      `participants` varchar(255) DEFAULT "",
                      `record` enum("true","false") DEFAULT "false",
                       `sessionUsers` int(11) DEFAULT 0,
                      PRIMARY KEY (`id`))');

        Database::get()->query('CREATE TABLE IF NOT EXISTS `bbb_servers` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `hostname` varchar(255) DEFAULT NULL,
                        `ip` varchar(255) NOT NULL,
                        `enabled` enum("true","false") DEFAULT NULL,
                        `server_key` varchar(255) DEFAULT NULL,
                        `api_url` varchar(255) DEFAULT NULL,
                        `max_rooms` int(11) DEFAULT NULL,
                        `max_users` int(11) DEFAULT NULL,
                        `enable_recordings` enum("true","false") DEFAULT NULL,
                        `weight` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `idx_bbb_servers` (`hostname`))');

        Database::get()->query("CREATE TABLE IF NOT EXISTS `idx_queue` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `course_id` int(11) NOT NULL UNIQUE,
                        PRIMARY KEY (`id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `idx_queue_async` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `request_type` VARCHAR(255) NOT NULL,
                        `resource_type` VARCHAR(255) NOT NULL,
                        `resource_id` int(11) NOT NULL,
                        PRIMARY KEY (`id`)) $charset_spec");

        Database::get()->query("CREATE TABLE `course_weekly_view` (
                        `id` INT(11) NOT NULL auto_increment,
                        `course_id` INT(11) NOT NULL,
                        `title` VARCHAR(255) NOT NULL DEFAULT '',
                        `comments` MEDIUMTEXT,
                        `start_week` DATE NOT NULL default '0000-00-00',
                        `finish_week` DATE NOT NULL default '0000-00-00',
                        `visible` TINYINT(4) NOT NULL DEFAULT 1,
                        `public` TINYINT(4) NOT NULL DEFAULT 1,
                        `order` INT(11) NOT NULL DEFAULT 0,
                        PRIMARY KEY  (`id`)) $charset_spec");

        Database::get()->query("CREATE TABLE `course_weekly_view_activities` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `course_weekly_view_id` INT(11) NOT NULL ,
                        `title` VARCHAR(255) NOT NULL DEFAULT '',
                        `comments` MEDIUMTEXT,
                        `res_id` INT(11) NOT NULL,
                        `type` VARCHAR(255) NOT NULL DEFAULT '',
                        `visible` TINYINT(4),
                        `order` INT(11) NOT NULL DEFAULT 0,
                        `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00') $charset_spec");

        // hierarchy tables
        $n = Database::get()->queryArray("SHOW TABLES LIKE 'faculte'");
        $root_node = null;
        $rebuildHierarchy = (count($n) == 1) ? true : false;
        // Whatever code $rebuildHierarchy wraps, can only be executed once.
        // Everything else can be executed several times.

        if ($rebuildHierarchy) {
            Database::get()->query("DROP TABLE IF EXISTS `hierarchy`");
            Database::get()->query("DROP TABLE IF EXISTS `course_department`");
            Database::get()->query("DROP TABLE IF EXISTS `user_department`");
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `hierarchy` (
                             `id` INT(11) NOT NULL auto_increment PRIMARY KEY,
                             `code` VARCHAR(20),
                             `name` TEXT NOT NULL,
                             `number` INT(11) NOT NULL DEFAULT 1000,
                             `generator` INT(11) NOT NULL DEFAULT 100,
                             `lft` INT(11) NOT NULL,
                             `rgt` INT(11) NOT NULL,
                             `allow_course` BOOLEAN NOT NULL DEFAULT FALSE,
                             `allow_user` BOOLEAN NOT NULL DEFAULT FALSE,
                             `order_priority` INT(11) DEFAULT NULL,
                             KEY `lftindex` (`lft`),
                             KEY `rgtindex` (`rgt`) ) $charset_spec");

        if ($rebuildHierarchy) {
            // copy faculties into the tree
            $max = Database::get()->querySingle("SELECT MAX(id) as max FROM `faculte`")->max;
            $i = 0;
            Database::get()->queryFunc("SELECT * FROM `faculte`", function ($r) use (&$i, &$max, $langpre, $langpost, $langother) {
                $lft = 2 + 8 * $i;
                $rgt = $lft + 7;
                Database::get()->query("INSERT INTO `hierarchy` (id, code, name, number, generator, lft, rgt, allow_course, allow_user)
                                VALUES (?d, ?s, ?s, ?d, ?d, ?d, ?d, false, true)", $r->id, $r->code, $r->name, $r->number, $r->generator, $lft, $rgt);

                Database::get()->query("INSERT INTO `hierarchy` (id, code, name, lft, rgt, allow_course, allow_user)
                                VALUES (?d, ?s, ?s, ?d, ?d, true, false)", ( ++$max), $r->code, $langpre, ($lft + 1), ($lft + 2));
                Database::get()->query("INSERT INTO `hierarchy` (id, code, name, lft, rgt, allow_course, allow_user)
                                VALUES (?d, ?s, ?s, ?d, ?d, true, false)", ( ++$max), $r->code, $langpost, ($lft + 3), ($lft + 4));
                Database::get()->query("INSERT INTO `hierarchy` (id, code, name, lft, rgt, allow_course, allow_user)
                                VALUES (?d, ?s, ?s, ?d, ?d, true, false)", ( ++$max), $r->code, $langother, ($lft + 5), ($lft + 6));
                $i++;
            });

            $root_rgt = 2 + 8 * intval(Database::get()->querySingle("SELECT COUNT(*) as value FROM `faculte`")->value);
            $rnode = Database::get()->query("INSERT INTO `hierarchy` (code, name, lft, rgt)
                            VALUES ('', ?s, 1, ?d)", $_POST['Institution'], $root_rgt);
            $root_node = $rnode->lastInsertID;
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_department` (
                             `id` int(11) NOT NULL auto_increment PRIMARY KEY,
                             `course` int(11) NOT NULL references course(id),
                             `department` int(11) NOT NULL references hierarchy(id) )");

        if ($rebuildHierarchy) {
            Database::get()->queryFunc("SELECT cours_id, faculteid, type FROM `cours`", function ($r) use($langpre, $langpost, $langother) {
                // take care of courses with not type
                if (!empty($r->type) && strlen($r->type) > 0) {
                    $qlike = ${'lang' . $r->type};
                } else {
                    $qlike = $langother;
                }
                // take care of courses with no parent
                if (!empty($r->faculteid)) {
                    $qfaculteid = $r->faculteid;
                } else {
                    $qfaculteid = $root_node;
                }

                $node = Database::get()->querySingle("SELECT node.id FROM `hierarchy` AS node, `hierarchy` AS parent
                                            WHERE node.name LIKE ?s AND
                                                  parent.id = ?d AND
                                                  node.lft BETWEEN parent.lft AND parent.rgt", $qlike, $qfaculteid);
                if ($node) {
                    Database::get()->query("INSERT INTO `course_department` (course, department) VALUES (?d, ?d)", $r->cours_id, $node->id);
                }
            });
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `user_department` (
                             `id` int(11) NOT NULL auto_increment PRIMARY KEY,
                             `user` mediumint(8) unsigned NOT NULL references user(user_id),
                             `department` int(11) NOT NULL references hierarchy(id) )");

        if ($rebuildHierarchy) {
            Database::get()->queryFunc("SELECT id, department FROM `user` WHERE department IS NOT NULL", function ($r) {
                Database::get()->query("INSERT INTO `user_department` (user, department) VALUES(?d, ?d)", $r->id, $r->department);
            });
        }

        if ($rebuildHierarchy) {
            // drop old way of referencing course type and course faculty
            Database::get()->query("ALTER TABLE `user` DROP COLUMN department");
            Database::get()->query("DROP TABLE IF EXISTS `faculte`");
        }

        // hierarchy stored procedures
        refreshHierarchyProcedures();

        // Update ip-containing fields to support IPv6 addresses
        Database::get()->query("ALTER TABLE `log` CHANGE COLUMN `ip` `ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `login_failure` CHANGE COLUMN `ip` `ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `loginout` CHANGE `ip` `ip` CHAR(45) NOT NULL DEFAULT '0.0.0.0'");
        Database::get()->query("ALTER TABLE `log_archive` CHANGE COLUMN `ip` `ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `assignment_submit`
                            CHANGE COLUMN `submission_ip` `submission_ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `assignment_submit`
                            CHANGE COLUMN `grade_submission_ip` `grade_submission_ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `forum_post`
                            CHANGE COLUMN `poster_ip` `poster_ip` VARCHAR(45)");
        Database::get()->query("ALTER TABLE `logins` CHANGE COLUMN `ip` `ip` VARCHAR(45)");

        // There is a special case with user_request storing its IP in numeric format

        $fields_user_request = Database::get()->queryArray("SHOW COLUMNS FROM user_request");
        foreach ($fields_user_request as $row2) {
            if ($row2->Field == "ip_address") {
                Database::get()->query("ALTER TABLE `user_request` ADD `request_ip` varchar(45) NOT NULL DEFAULT ''");
                Database::get()->queryFunc("SELECT id,INET_NTOA(ip_address) as ip_addr FROM user_request", function ($row) {
                    Database::get()->query("UPDATE `user_request` SET `request_ip` = ?s WHERE `id` = ?s", $row->ip_addr, $row->id);
                });
                Database::get()->query("ALTER TABLE `user_request` DROP `ip_address`");
                break;
            }
        }

        // oai_metadata
        Database::get()->query("ALTER TABLE `oai_record` DROP COLUMN `dc_title`,
            DROP COLUMN `dc_description`,
            DROP COLUMN `dc_syllabus`,
            DROP COLUMN `dc_subject`,
            DROP COLUMN `dc_subsubject`,
            DROP COLUMN `dc_objectives`,
            DROP COLUMN `dc_level`,
            DROP COLUMN `dc_prerequisites`,
            DROP COLUMN `dc_instructor`,
            DROP COLUMN `dc_department`,
            DROP COLUMN `dc_institution`,
            DROP COLUMN `dc_coursephoto`,
            DROP COLUMN `dc_coursephotomime`,
            DROP COLUMN `dc_instructorphoto`,
            DROP COLUMN `dc_instructorphotomime`,
            DROP COLUMN `dc_url`,
            DROP COLUMN `dc_identifier`,
            DROP COLUMN `dc_language`,
            DROP COLUMN `dc_date`,
            DROP COLUMN `dc_format`,
            DROP COLUMN `dc_rights`,
            DROP COLUMN `dc_videolectures`,
            DROP COLUMN `dc_code`,
            DROP COLUMN `dc_keywords`,
            DROP COLUMN `dc_contentdevelopment`,
            DROP COLUMN `dc_formattypes`,
            DROP COLUMN `dc_recommendedcomponents`,
            DROP COLUMN `dc_assignments`,
            DROP COLUMN `dc_requirements`,
            DROP COLUMN `dc_remarks`,
            DROP COLUMN `dc_acknowledgments`,
            DROP COLUMN `dc_coteaching`,
            DROP COLUMN `dc_coteachingcolleagueopenscourse`,
            DROP COLUMN `dc_coteachingautonomousdepartment`,
            DROP COLUMN `dc_coteachingdepartmentcredithours`,
            DROP COLUMN `dc_yearofstudy`,
            DROP COLUMN `dc_semester`,
            DROP COLUMN `dc_coursetype`,
            DROP COLUMN `dc_credithours`,
            DROP COLUMN `dc_credits`,
            DROP COLUMN `dc_institutiondescription`,
            DROP COLUMN `dc_curriculumtitle`,
            DROP COLUMN `dc_curriculumdescription`,
            DROP COLUMN `dc_outcomes`,
            DROP COLUMN `dc_curriculumkeywords`,
            DROP COLUMN `dc_sector`,
            DROP COLUMN `dc_targetgroup`,
            DROP COLUMN `dc_curriculumtargetgroup`,
            DROP COLUMN `dc_featuredbooks`,
            DROP COLUMN `dc_structure`,
            DROP COLUMN `dc_teachingmethod`,
            DROP COLUMN `dc_assessmentmethod`,
            DROP COLUMN `dc_eudoxuscode`,
            DROP COLUMN `dc_eudoxusurl`,
            DROP COLUMN `dc_kalliposurl`,
            DROP COLUMN `dc_numberofunits`,
            DROP COLUMN `dc_unittitle`,
            DROP COLUMN `dc_unitdescription`,
            DROP COLUMN `dc_unitkeywords`,
            DROP INDEX cid,
            DROP INDEX oaiid
            ");

        if (!DBHelper::tableExists('oai_metadata')) {
            Database::get()->query("CREATE TABLE `oai_metadata` (
                `id` int(11) NOT NULL auto_increment PRIMARY KEY,
                `oai_record` int(11) NOT NULL references oai_record(id),
                `field` varchar(255) NOT NULL,
                `value` text,
                INDEX `field_index` (`field`) )");
        }
    }

    if (version_compare($oldversion, '3.1.6', '<')) {
        refreshHierarchyProcedures();
    }

    // Rename table `cours` to `course` and `cours_user` to `course_user`
    if (!DBHelper::tableExists('course')) {

        if (DBHelper::indexExists('cours', 'cours')) {
            Database::get()->query("ALTER TABLE cours DROP INDEX cours");
        }

        DBHelper::fieldExists('cours', 'expand_glossary') or
                Database::get()->query("ALTER TABLE `cours` ADD `expand_glossary` BOOL NOT NULL DEFAULT 0");
        DBHelper::fieldExists('cours', 'glossary_index') or
                Database::get()->query("ALTER TABLE `cours` ADD `glossary_index` BOOL NOT NULL DEFAULT 1");
        Database::get()->query("RENAME TABLE `cours` TO `course`");
        Database::get()->query("UPDATE course SET description = '' WHERE description IS NULL");
        Database::get()->query("UPDATE course SET course_keywords = '' WHERE course_keywords IS NULL");
        if (DBHelper::fieldExists('course', 'course_objectives')) {
            Database::get()->query("ALTER TABLE course DROP COLUMN `course_objectives`,
                                                DROP COLUMN `course_prerequisites`,
                                                DROP COLUMN `course_references`");
        }
        Database::get()->query("ALTER TABLE course CHANGE `cours_id` `id` INT(11) NOT NULL AUTO_INCREMENT,
                                             CHANGE `languageCourse` `lang` VARCHAR(16) DEFAULT 'el',
                                             CHANGE `intitule` `title` VARCHAR(250) NOT NULL DEFAULT '',
                                             CHANGE `description` `description` MEDIUMTEXT DEFAULT NULL,
                                             CHANGE `course_keywords` `keywords` TEXT NOT NULL,
                                             DROP COLUMN `course_addon`,
                                             CHANGE `titulaires` `prof_names` varchar(200) NOT NULL DEFAULT '',
                                             CHANGE `fake_code` `public_code` varchar(20) NOT NULL DEFAULT '',
                                             DROP COLUMN `departmentUrlName`,
                                             DROP COLUMN `departmentUrl`,
                                             DROP COLUMN `lastVisit`,
                                             DROP COLUMN `lastEdit`,
                                             DROP COLUMN `expirationDate`,
                                             DROP COLUMN `type`,
                                             DROP COLUMN `faculteid`,
                                             CHANGE `first_create` `created` datetime NOT NULL default '0000-00-00 00:00:00',
                                             CHANGE `expand_glossary` `glossary_expand` BOOL NOT NULL DEFAULT 0,
                                             ADD `view_type` VARCHAR(255) NOT NULL DEFAULT 'units',
                                             ADD `start_date` DATE NOT NULL default '0000-00-00',
                                             ADD `finish_date` DATE NOT NULL default '0000-00-00'");
        Database::get()->queryFunc("SELECT DISTINCT lang from course", function ($old_lang) {
            Database::get()->query("UPDATE course SET lang = ?s WHERE lang = ?s", langname_to_code($old_lang->lang), $old_lang->lang);
        });
        Database::get()->query("RENAME TABLE `cours_user` TO `course_user`");
        Database::get()->query('ALTER TABLE `course_user`
                                            CHANGE `statut` `status` TINYINT(4) NOT NULL DEFAULT 0,
                                            CHANGE `cours_id` `course_id` INT(11) NOT NULL DEFAULT 0');
        if (DBHelper::fieldExists('course_user', 'code_cours')) {
            Database::get()->query('ALTER TABLE `course_user`
                                        DROP COLUMN `code_cours`');
        }
    }

    DBHelper::fieldExists('ebook', 'visible') or
            Database::get()->query("ALTER TABLE `ebook` ADD `visible` BOOL NOT NULL DEFAULT 1");
    DBHelper::fieldExists('admin', 'privilege') or
            Database::get()->query("ALTER TABLE `admin` ADD `privilege` INT NOT NULL DEFAULT '0'");
    DBHelper::fieldExists('course_user', 'editor') or
            Database::get()->query("ALTER TABLE `course_user` ADD `editor` INT NOT NULL DEFAULT '0' AFTER `tutor`");
    if (!DBHelper::fieldExists('glossary', 'category_id')) {
        Database::get()->query("ALTER TABLE glossary
                                ADD category_id INT(11) DEFAULT NULL,
                                ADD notes TEXT NOT NULL");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `glossary_category` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `name` VARCHAR(255) NOT NULL,
                                `description` TEXT NOT NULL,
                                `order` INT(11) NOT NULL DEFAULT 0)");
    }

    Database::get()->query("CREATE TABLE IF NOT EXISTS `actions_daily` (
                `id` int(11) NOT NULL auto_increment,
                `user_id` int(11) NOT NULL,
                `module_id` int(11) NOT NULL,
                `course_id` int(11) NOT NULL,
                `hits` int(11) NOT NULL,
                `duration` int(11) NOT NULL,
                `day` date NOT NULL,
                `last_update` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `actionsdailyindex` (`module_id`, `day`),
                KEY `actionsdailyuserindex` (`user_id`),
                KEY `actionsdailydayindex` (`day`),
                KEY `actionsdailymoduleindex` (`module_id`),
                KEY `actionsdailycourseindex` (`course_id`) )");

    Database::get()->query("ALTER TABLE monthly_summary CHANGE details details MEDIUMTEXT");

    // drop stale full text indexes
    if (DBHelper::indexExists('document', 'document')) {
        Database::get()->query("ALTER TABLE document DROP INDEX document");
    }
    if (DBHelper::indexExists('course_units', 'course_units_title')) {
        Database::get()->query("ALTER TABLE course_units DROP INDEX course_units_title");
    }
    if (DBHelper::indexExists('course_units', 'course_units_comments')) {
        Database::get()->query("ALTER TABLE course_units DROP INDEX course_units_comments");
    }
    if (DBHelper::indexExists('unit_resources', 'unit_resources_title')) {
        Database::get()->query("ALTER TABLE unit_resources DROP INDEX unit_resources_title");
    }


    // // ----------------------------------
    // creation of indices
    // ----------------------------------
    updateInfo(-1, $langIndexCreation);

    DBHelper::indexExists('actions_daily', 'actions_daily_index') or
            Database::get()->query("CREATE INDEX `actions_daily_index` ON actions_daily(user_id, module_id, course_id)");
    DBHelper::indexExists('actions_summary', 'actions_summary_index') or
            Database::get()->query("CREATE INDEX `actions_summary_index` ON actions_summary(module_id, course_id)");
    DBHelper::indexExists('admin', 'admin_index') or
            Database::get()->query("CREATE INDEX `admin_index` ON admin(user_id)");
    DBHelper::indexExists('agenda', 'agenda_index') or
            Database::get()->query("CREATE INDEX `agenda_index` ON agenda(course_id)");
    DBHelper::indexExists('announcement', 'ann_index') or
            Database::get()->query("CREATE INDEX `ann_index` ON announcement(course_id)");
    DBHelper::indexExists('assignment', 'assignment_index') or
            Database::get()->query("CREATE INDEX `assignment_index` ON assignment(course_id)");
    DBHelper::indexExists('assignment_submit', 'assign_submit_index') or
            Database::get()->query("CREATE INDEX `assign_submit_index` ON assignment_submit(uid, assignment_id)");
    DBHelper::indexExists('assignment_to_specific', 'assign_spec_index') or
            Database::get()->query("CREATE INDEX `assign_spec_index` ON assignment_to_specific(user_id)");
    DBHelper::indexExists('attendance', 'att_index') or
            Database::get()->query("CREATE INDEX `att_index` ON attendance(course_id)");
    DBHelper::indexExists('attendance_activities', 'att_act_index') or
            Database::get()->query("CREATE INDEX `att_act_index` ON attendance_activities(attendance_id)");
    DBHelper::indexExists('attendance_book', 'att_book_index') or
            Database::get()->query("CREATE INDEX `att_book_index` ON attendance_book(attendance_activity_id)");
    DBHelper::indexExists('course', 'course_index') or
            Database::get()->query("CREATE INDEX `course_index` ON course(code)");
    DBHelper::indexExists('course_description', 'cd_type_index') or
            Database::get()->query('CREATE INDEX `cd_type_index` ON course_description(`type`)');
    DBHelper::indexExists('course_description', 'cd_cid_type_index') or
            Database::get()->query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, `type`)');
    DBHelper::indexExists('course_description', 'cid') or
            Database::get()->query('CREATE INDEX `cid` ON course_description (course_id)');
    DBHelper::indexExists('course_module', 'visible_cid') or
            Database::get()->query('CREATE INDEX `visible_cid` ON course_module (visible, course_id)');
    DBHelper::indexExists('course_review', 'crev_index') or
            Database::get()->query("CREATE INDEX `crev_index` ON course_review(course_id)");
    DBHelper::indexExists('course_units', 'course_units_index') or
            Database::get()->query('CREATE INDEX `course_units_index` ON course_units (course_id, `order`)');
    DBHelper::indexExists('course_user', 'cu_index') or
            Database::get()->query("CREATE INDEX `cu_index` ON course_user (course_id, user_id, status)");
    DBHelper::indexExists('document', 'doc_path_index') or
            Database::get()->query('CREATE INDEX `doc_path_index` ON document (course_id, subsystem,path)');
    DBHelper::indexExists('dropbox_attachment', 'drop_att_index') or
            Database::get()->query("CREATE INDEX `drop_att_index` ON dropbox_attachment(msg_id)");
    DBHelper::indexExists('dropbox_index', 'drop_index') or
            Database::get()->query("CREATE INDEX `drop_index` ON dropbox_index(recipient_id, is_read)");
    DBHelper::indexExists('dropbox_msg', 'drop_msg_index') or
            Database::get()->query("CREATE INDEX `drop_msg_index` ON dropbox_msg(course_id, author_id)");
    DBHelper::indexExists('ebook', 'ebook_index') or
            Database::get()->query("CREATE INDEX `ebook_index` ON ebook(course_id)");
    DBHelper::indexExists('ebook_section', 'ebook_sec_index') or
            Database::get()->query("CREATE INDEX `ebook_sec_index` ON ebook_section(ebook_id)");
    DBHelper::indexExists('ebook_subsection', 'ebook_sub_sec_index') or
            Database::get()->query("CREATE INDEX `ebook_sub_sec_index` ON ebook_subsection(section_id)");
    DBHelper::indexExists('exercise', 'exer_index') or
            Database::get()->query('CREATE INDEX `exer_index` ON exercise (course_id)');
    DBHelper::indexExists('exercise_user_record', 'eur_index1') or
            Database::get()->query('CREATE INDEX `eur_index1` ON exercise_user_record (eid)');
    DBHelper::indexExists('exercise_user_record', 'eur_index2') or
            Database::get()->query('CREATE INDEX `eur_index2` ON exercise_user_record (uid)');
    DBHelper::indexExists('exercise_answer_record', 'ear_index1') or
            Database::get()->query('CREATE INDEX `ear_index1` ON exercise_answer_record (eurid)');
    DBHelper::indexExists('exercise_answer_record', 'ear_index2') or
            Database::get()->query('CREATE INDEX `ear_index2` ON exercise_answer_record (question_id)');
    DBHelper::indexExists('exercise_question', 'eq_index') or
            Database::get()->query('CREATE INDEX `eq_index` ON exercise_question (course_id)');
    DBHelper::indexExists('exercise_answer', 'ea_index') or
            Database::get()->query('CREATE INDEX `ea_index` ON exercise_answer (question_id)');
    DBHelper::indexExists('forum', 'for_index') or
            Database::get()->query("CREATE INDEX `for_index` ON forum(course_id)");
    DBHelper::indexExists('forum_category', 'for_cat_index') or
            Database::get()->query("CREATE INDEX `for_cat_index` ON forum_category(course_id)");
    DBHelper::indexExists('forum_notify', 'for_not_index') or
            Database::get()->query("CREATE INDEX `for_not_index` ON forum_notify(course_id)");
    DBHelper::indexExists('forum_post', 'for_post_index') or
            Database::get()->query("CREATE INDEX `for_post_index` ON forum_post(topic_id)");
    DBHelper::indexExists('forum_topic', 'for_topic_index') or
            Database::get()->query("CREATE INDEX `for_topic_index` ON forum_topic(forum_id)");
    DBHelper::indexExists('glossary', 'glos_index') or
            Database::get()->query("CREATE INDEX `glos_index` ON glossary(course_id)");
    DBHelper::indexExists('glossary_category', 'glos_cat_index') or
            Database::get()->query("CREATE INDEX `glos_cat_index` ON glossary_category(course_id)");
    DBHelper::indexExists('gradebook', 'grade_index') or
            Database::get()->query("CREATE INDEX `grade_index` ON gradebook(course_id)");
    DBHelper::indexExists('gradebook_activities', 'grade_act_index') or
            Database::get()->query("CREATE INDEX `grade_act_index` ON gradebook_activities(gradebook_id)");
    DBHelper::indexExists('gradebook_book', 'grade_book_index') or
            Database::get()->query("CREATE INDEX `grade_book_index` ON gradebook_book(gradebook_activity_id)");
    DBHelper::indexExists('group', 'group_index') or
            Database::get()->query("CREATE INDEX `group_index` ON `group`(course_id)");
    DBHelper::indexExists('group_properties', 'gr_prop_index') or
            Database::get()->query("CREATE INDEX `gr_prop_index` ON group_properties(course_id)");
    DBHelper::indexExists('hierarchy', 'hier_index') or
            Database::get()->query("CREATE INDEX `hier_index` ON hierarchy(code,name(20))");
    DBHelper::indexExists('link', 'link_index') or
            Database::get()->query("CREATE INDEX `link_index` ON link(course_id)");
    DBHelper::indexExists('link_category', 'link_cat_index') or
            Database::get()->query("CREATE INDEX `link_cat_index` ON link_category(course_id)");
    DBHelper::indexExists('log', 'cmid') or
            Database::get()->query('CREATE INDEX `cmid` ON log (course_id, module_id)');
    DBHelper::indexExists('logins', 'logins_id') or
            Database::get()->query("CREATE INDEX `logins_id` ON logins(user_id, course_id)");
    DBHelper::indexExists('loginout', 'loginout_id') or
            Database::get()->query("CREATE INDEX `loginout_id` ON loginout(id_user)");
    DBHelper::indexExists('lp_asset', 'lp_as_id') or
            Database::get()->query("CREATE INDEX `lp_as_id` ON lp_asset(module_id)");
    DBHelper::indexExists('lp_learnPath', 'lp_id') or
            Database::get()->query("CREATE INDEX `lp_id` ON lp_learnPath(course_id)");
    DBHelper::indexExists('lp_module', 'lp_mod_id') or
            Database::get()->query("CREATE INDEX `lp_mod_id` ON lp_module(course_id)");
    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_lp_id') or
            Database::get()->query("CREATE INDEX `lp_rel_lp_id` ON lp_rel_learnPath_module(learnPath_id, module_id)");
    DBHelper::indexExists('lp_user_module_progress', 'optimize') or
            Database::get()->query("CREATE INDEX `optimize` ON lp_user_module_progress (user_id, learnPath_module_id)");
    DBHelper::indexExists('poll', 'poll_index') or
            Database::get()->query("CREATE INDEX `poll_index` ON poll(course_id)");
    DBHelper::indexExists('poll_question', 'poll_q_id') or
            Database::get()->query("CREATE INDEX `poll_q_id` ON poll_question(pid)");
    DBHelper::indexExists('poll_question_answer', 'poll_qa_id') or
            Database::get()->query("CREATE INDEX `poll_qa_id` ON poll_question_answer(pqid)");
    DBHelper::indexExists('unit_resources', 'unit_res_index') or
            Database::get()->query('CREATE INDEX `unit_res_index` ON unit_resources (unit_id, visibility,res_id)');
    DBHelper::indexExists('user', 'u_id') or
            Database::get()->query("CREATE INDEX `u_id` ON user(username)");
    DBHelper::indexExists('video', 'cid') or
            Database::get()->query('CREATE INDEX `cid` ON video (course_id)');
    DBHelper::indexExists('videolink', 'cid') or
            Database::get()->query('CREATE INDEX `cid` ON videolink (course_id)');
    DBHelper::indexExists('wiki_locks', 'wiki_id') or
            Database::get()->query("CREATE INDEX `wiki_id` ON wiki_locks(wiki_id)");
    DBHelper::indexExists('wiki_pages', 'wiki_pages_id') or
            Database::get()->query("CREATE INDEX `wiki_pages_id` ON wiki_pages(wiki_id)");
    DBHelper::indexExists('wiki_pages_content', 'wiki_pcon_id') or
            Database::get()->query("CREATE INDEX `wiki_pcon_id` ON wiki_pages_content(pid)");
    DBHelper::indexExists('wiki_properties', 'wik_prop_id') or
            Database::get()->query("CREATE INDEX `wik_prop_id` ON  wiki_properties(course_id)");
    DBHelper::indexExists('idx_queue', 'idx_queue_cid') or
            Database::get()->query("CREATE INDEX `idx_queue_cid` ON `idx_queue` (course_id)");
    DBHelper::indexExists('idx_queue_async', 'idx_queue_async_uid') or
            Database::get()->query("CREATE INDEX `idx_queue_async_uid` ON idx_queue_async(user_id)");

    DBHelper::indexExists('attendance_users', 'attendance_users_aid') or
            Database::get()->query('CREATE INDEX `attendance_users_aid` ON `attendance_users` (attendance_id)');
    DBHelper::indexExists('gradebook_users', 'gradebook_users_gid') or
            Database::get()->query('CREATE INDEX `gradebook_users_gid` ON `gradebook_users` (gradebook_id)');

    DBHelper::indexExists('actions_daily', 'actions_daily_mcd') or
            Database::get()->query('CREATE INDEX `actions_daily_mcd` ON `actions_daily` (module_id, course_id, day)');
    DBHelper::indexExists('actions_daily', 'actions_daily_hdi') or
            Database::get()->query('CREATE INDEX `actions_daily_hdi` ON `actions_daily` (hits, duration, id)');
    DBHelper::indexExists('loginout', 'loginout_ia') or
            Database::get()->query('CREATE INDEX `loginout_ia` ON `loginout` (id_user, action)');
    DBHelper::indexExists('announcement', 'announcement_cvo') or
            Database::get()->query('CREATE INDEX `announcement_cvo` ON `announcement` (course_id, visible, `order`)');

    DBHelper::indexExists('actions_summary', 'actions_summary_module_id') or
            Database::get()->query("CREATE INDEX `actions_summary_module_id` ON actions_summary(module_id)");
    DBHelper::indexExists('actions_summary', 'actions_summary_course_id') or
            Database::get()->query("CREATE INDEX `actions_summary_course_id` ON actions_summary(course_id)");

    DBHelper::indexExists('document', 'doc_course_id') or
            Database::get()->query('CREATE INDEX `doc_course_id` ON document (course_id)');
    DBHelper::indexExists('document', 'doc_subsystem') or
            Database::get()->query('CREATE INDEX `doc_subsystem` ON document (subsystem)');
    DBHelper::indexExists('document', 'doc_path') or
            Database::get()->query('CREATE INDEX `doc_path` ON document (path)');

    DBHelper::indexExists('dropbox_index', 'drop_index_recipient_id') or
            Database::get()->query("CREATE INDEX `drop_index_recipient_id` ON dropbox_index(recipient_id)");
    DBHelper::indexExists('dropbox_index', 'drop_index_recipient_id') or
            Database::get()->query("CREATE INDEX `drop_index_is_read` ON dropbox_index(is_read)");

    DBHelper::indexExists('dropbox_msg', 'drop_msg_index_course_id') or
            Database::get()->query("CREATE INDEX `drop_msg_index_course_id` ON dropbox_msg(course_id)");
    DBHelper::indexExists('dropbox_msg', 'drop_msg_index_author_id') or
            Database::get()->query("CREATE INDEX `drop_msg_index_author_id` ON dropbox_msg(author_id)");

    DBHelper::indexExists('exercise_with_questions', 'ewq_index_question_id') or
            Database::get()->query('CREATE INDEX `ewq_index_question_id` ON exercise_with_questions (question_id)');
    DBHelper::indexExists('exercise_with_questions', 'ewq_index_exercise_id') or
            Database::get()->query('CREATE INDEX `ewq_index_exercise_id` ON exercise_with_questions (exercise_id)');

    DBHelper::indexExists('group_members', 'gr_mem_user_id') or
            Database::get()->query("CREATE INDEX `gr_mem_user_id` ON group_members(user_id)");
    DBHelper::indexExists('group_members', 'gr_mem_group_id') or
            Database::get()->query("CREATE INDEX `gr_mem_group_id` ON group_members(group_id)");

    DBHelper::indexExists('log', 'log_course_id') or
            Database::get()->query("CREATE INDEX `log_course_id` ON log (course_id)");
    DBHelper::indexExists('log', 'log_module_id') or
            Database::get()->query("CREATE INDEX `log_module_id` ON log (module_id)");

    DBHelper::indexExists('logins', 'logins_id_user_id') or
            Database::get()->query("CREATE INDEX `logins_id_user_id` ON logins(user_id)");
    DBHelper::indexExists('logins', 'logins_id_course_id') or
            Database::get()->query("CREATE INDEX `logins_id_course_id` ON logins(course_id)");

    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_learnPath_id') or
            Database::get()->query("CREATE INDEX `lp_rel_learnPath_id` ON lp_rel_learnPath_module(learnPath_id)");
    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_learnPath_id') or
            Database::get()->query("CREATE INDEX `lp_rel_module_id` ON lp_rel_learnPath_module(module_id)");

    DBHelper::indexExists('lp_user_module_progress', 'lp_learnPath_module_id') or
            Database::get()->query("CREATE INDEX `lp_learnPath_module_id` ON lp_user_module_progress (learnPath_module_id)");
    DBHelper::indexExists('lp_user_module_progress', 'lp_user_id') or
            Database::get()->query("CREATE INDEX `lp_user_id` ON lp_user_module_progress (user_id)");

    DBHelper::indexExists('unit_resources', 'unit_res_unit_id') or
            Database::get()->query("CREATE INDEX `unit_res_unit_id` ON unit_resources (unit_id)");
    DBHelper::indexExists('unit_resources', 'unit_res_visible') or
            Database::get()->query("CREATE INDEX `unit_res_visible` ON unit_resources (visible)");
    DBHelper::indexExists('unit_resources', 'unit_res_res_id') or
            Database::get()->query("CREATE INDEX `unit_res_res_id` ON unit_resources (res_id)");

    DBHelper::indexExists('personal_calendar', 'pcal_start') or
            Database::get()->query('CREATE INDEX `pcal_start` ON personal_calendar (start)');

    DBHelper::indexExists('agenda', 'agenda_start') or
            Database::get()->query('CREATE INDEX `agenda_start` ON agenda (start)');

    DBHelper::indexExists('assignment', 'assignment_deadline') or
            Database::get()->query('CREATE INDEX `assignment_deadline` ON assignment (deadline)');

    // **********************************************
    // upgrade courses databases
    // **********************************************
    $res = Database::get()->queryArray("SELECT id, code, lang FROM course ORDER BY code");
    $total = count($res);
    $i = 1;
    foreach ($res as $row) {
        updateInfo($i / ($total + 1), $langUpgCourse);

        if (version_compare($oldversion, '2.2', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.2");
            upgrade_course_2_2($row->code, $row->lang);
        }
        if (version_compare($oldversion, '2.3', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.3");
            upgrade_course_2_3($row->code);
        }
        if (version_compare($oldversion, '2.4', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.4");
            upgrade_course_index_php($row->code);
            upgrade_course_2_4($row->code, $row->id, $row->lang);
        }
        if (version_compare($oldversion, '2.5', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.5");
            upgrade_course_2_5($row->code, $row->lang);
        }
        if (version_compare($oldversion, '2.8', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.8");
            upgrade_course_2_8($row->code, $row->lang);
        }
        if (version_compare($oldversion, '2.9', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.9");
            upgrade_course_2_9($row->code, $row->lang);
        }
        if (version_compare($oldversion, '2.10', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.10");
            upgrade_course_2_10($row->code, $row->id);
        }
        if (version_compare($oldversion, '2.11', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 2.11");
            upgrade_course_2_11($row->code);
        }
        if (version_compare($oldversion, '3.0b2', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 3.0b2");
            upgrade_course_3_0($row->code, $row->id);
        }
        if (version_compare($oldversion, '3.0rc2', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 3.0rc2");
            upgrade_course_3_0_rc2($row->code, $row->id);
        }
        if (version_compare($oldversion, '3.0', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 3.0");
            upgrade_course_3_0_rc2($row->code, $row->id);
        }
        if (version_compare($oldversion, '3.1.3', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 3.1.3");
            upgrade_course_3_0_rc2($row->code, $row->id);
        }
        if (version_compare($oldversion, '3.1.4', '<')) {
            updateInfo(-1, $langUpgCourse . " " . $row->code . " 3.1.4");
            upgrade_course_3_0_rc2($row->code, $row->id);
        }
        $i++;
    }

    if (version_compare($oldversion, '2.1.3', '<')) {
        updateInfo(0.98, $langChangeDBCharset . " " . $mysqlMainDb . " " . $langToUTF);
        convert_db_utf8($mysqlMainDb);
    }

    if (version_compare($oldversion, '3.0b2', '<')) { // special procedure, must execute after course upgrades
        Database::get()->query("USE `$mysqlMainDb`");

        Database::get()->query("CREATE VIEW `actions_daily_tmpview` AS
                SELECT
                `user_id`,
                `module_id`,
                `course_id`,
                COUNT(`id`) AS `hits`,
                SUM(`duration`) AS `duration`,
                DATE(`date_time`) AS `day`
                FROM `actions`
                GROUP BY DATE(`date_time`), `user_id`, `module_id`, `course_id`");

        Database::get()->queryFunc("SELECT * FROM `actions_daily_tmpview`", function ($row) {
            Database::get()->query("INSERT INTO `actions_daily`
                    (`id`, `user_id`, `module_id`, `course_id`, `hits`, `duration`, `day`, `last_update`)
                    VALUES
                    (NULL, ?d, ?d, ?d, ?d, ?d, ?t, NOW())", $row->user_id, $row->module_id, $row->course_id, $row->hits, $row->duration, $row->day);
        });

        Database::get()->query("DROP VIEW IF EXISTS `actions_daily_tmpview`");
        Database::get()->query("DROP TABLE IF EXISTS `actions`");

        // improve primary key for table exercise_answer
        Database::get()->query("ALTER TABLE `exercise_answer` CHANGE id oldid INT(11)");
        Database::get()->query("ALTER TABLE `exercise_answer` DROP PRIMARY KEY");
        Database::get()->query("ALTER TABLE `exercise_answer` ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        Database::get()->query("ALTER TABLE `exercise_answer` DROP `oldid`");

        if (get_config('enable_search')) {
            set_config('enable_search', 0);
            set_config('enable_indexing', 0);
            echo "<hr><p class='alert alert-info'>$langUpgIndexingNotice</p>";
        }

        // convert tables to InnoDB storage engine
        $result = Database::get()->queryArray("SELECT Table_name, Engine, Table_type
            FROM information_schema.TABLES WHERE Table_schema = ?s", $mysqlMainDb);
        foreach ($result as $table) {
            if (($table->Table_type === 'BASE TABLE') && ($table->Engine != 'InnoDB')) {
                Database::get()->query("ALTER TABLE `" . $table->Table_name . "` ENGINE = InnoDB");
            }
        }
    }

    if (version_compare($oldversion, '3.0', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.0'));
        Database::get()->query("USE `$mysqlMainDb`");

        if (!DBHelper::fieldExists('auth', 'auth_title')) {
            Database::get()->query("ALTER table `auth` ADD `auth_title` TEXT");
        }
        if (!DBHelper::fieldExists('gradebook', 'active')) {
            Database::get()->query("ALTER table `gradebook` ADD `active` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!DBHelper::fieldExists('gradebook', 'title')) {
            Database::get()->query("ALTER table `gradebook` ADD `title` VARCHAR(250) DEFAULT NULL");
        }
        if (!DBHelper::fieldExists('attendance', 'active')) {
            Database::get()->query("ALTER table `attendance` ADD `active` TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!DBHelper::fieldExists('attendance', 'title')) {
            Database::get()->query("ALTER table `attendance` ADD `title` VARCHAR(250) DEFAULT NULL");
        }
        Database::get()->query("INSERT IGNORE INTO `auth` VALUES (7, 'cas', '', '', '', 0)");
        Database::get()->query("CREATE TABLE IF NOT EXISTS tags (
                `id` MEDIUMINT(11) NOT NULL auto_increment,
                `element_type` VARCHAR(255) NOT NULL DEFAULT '',
                `element_id` MEDIUMINT(11) NOT NULL ,
                `user_id` VARCHAR(255) NOT NULL DEFAULT '',
                `tag` TEXT,
                `date` DATE DEFAULT NULL,
                `course_id` INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (id)) $charset_spec");

        if (DBHelper::fieldExists('course_user', 'team')) {
            Database::get()->query('ALTER TABLE `course_user` DROP COLUMN `team`');
        }
        if (!DBHelper::fieldExists('exercise_question', 'difficulty')) {
            Database::get()->query("ALTER table `exercise_question` ADD difficulty INT(1) DEFAULT 0");
        }
        if (!DBHelper::fieldExists('exercise_question', 'category')) {
            Database::get()->query("ALTER table `exercise_question` ADD category INT(11) DEFAULT 0");
        }
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_question_cats` (
                            `question_cat_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `question_cat_name` VARCHAR(300) NOT NULL,
                            `course_id` INT(11) NOT NULL)
                            $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `theme_options` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` VARCHAR(300) NOT NULL,
                                `styles` LONGTEXT NOT NULL,
                                PRIMARY KEY (`id`)) $charset_spec");

        if (!DBHelper::fieldExists('poll_question', 'q_scale')) {
            Database::get()->query("ALTER TABLE poll_question ADD q_scale INT(11) NULL DEFAULT NULL");
        }

        // Add course home_layout fiels
        if (!DBHelper::fieldExists('course', 'home_layout')) {
            Database::get()->query("ALTER TABLE course ADD home_layout TINYINT(1) NOT NULL DEFAULT 1");
            Database::get()->query("UPDATE course SET home_layout = 3");
        }
        // Add course image field
        if (!DBHelper::fieldExists('course', 'course_image')) {
            Database::get()->query("ALTER TABLE course ADD course_image VARCHAR(400) NULL");
        }

        // Move course description from unit_resources to new course.description field
        $moveDesc = false;
        if (!DBHelper::fieldExists('course', 'description')) {
            Database::get()->query("ALTER TABLE course ADD description MEDIUMTEXT NOT NULL");
            $moveDesc = true;
        }
        if (!$moveDesc) {
            // Check if descriptions need to be moved
            $descCount = Database::get()->querySingle("SELECT COUNT(*) AS descCount
                FROM course_units, unit_resources
                WHERE unit_resources.comments <> '' AND
                      course_units.id = unit_resources.unit_id AND
                      course_units.`order` = -1 AND
                      unit_resources.res_id = -1")->descCount;
            $moveDesc = ($descCount > 0);
        }
        if ($moveDesc) {
            $result = Database::get()->query("UPDATE course, course_units, unit_resources
                SET course.description = unit_resources.comments
                WHERE course.id = course_units.course_id AND
                      course_units.id = unit_resources.unit_id AND
                      course_units.`order` = -1 AND
                      unit_resources.res_id = -1");
        }
        Database::get()->query("DELETE FROM unit_resources WHERE res_id = -1");
        Database::get()->query("DELETE FROM course_units WHERE `order` = -1");

        // loosen poll schema, mediumtext columns can be allowed to be null
        if (DBHelper::fieldExists('poll', 'description')) {
            Database::get()->query("ALTER TABLE `poll` CHANGE `description` `description` MEDIUMTEXT NULL DEFAULT NULL");
        } else {
            Database::get()->query("ALTER TABLE `poll` ADD `description` MEDIUMTEXT NULL DEFAULT NULL");
        }
        if (DBHelper::fieldExists('poll', 'end_message')) {
            Database::get()->query("ALTER TABLE `poll` CHANGE `end_message` `end_message` MEDIUMTEXT NULL DEFAULT NULL");
        } else {
            Database::get()->query("ALTER TABLE `poll` ADD `end_message` MEDIUMTEXT NULL DEFAULT NULL");
        }

        Database::get()->query("ALTER TABLE `bbb_session` CHANGE `participants` `participants` VARCHAR(1000)");
        set_config('theme', 'default');
        set_config('theme_options_id', get_config('theme_options_id', 0));

        // delete stale course licenses (if exist)
        Database::get()->query("UPDATE course SET course_license = 0 WHERE course_license = 20");
        // delete stale course units entries from course modules (27 -> MODULE_ID_UNITS)
        Database::get()->query("DELETE FROM course_module WHERE module_id = 27");
        // move secure_url (aka $urlSecure) to base_url if not empty
        $old_secure_url = get_config('secure_url');
        if (!empty($old_secure_url)) {
            set_config('base_url', $old_secure_url);
        }
        Database::get()->query("DELETE FROM config WHERE `key` = 'secure_url'");
        // fix calendar entries (if any)
        Database::get()->query("UPDATE agenda SET source_event_id = id WHERE source_event_id IS NULL");
        Database::get()->query("UPDATE admin_calendar SET source_event_id = id WHERE source_event_id IS NULL");
        Database::get()->query("UPDATE personal_calendar SET source_event_id = id WHERE source_event_id IS NULL");

    }

    // -----------------------------------
    // upgrade queries for 3.1
    // -----------------------------------
    if (version_compare($oldversion, '3.1', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.1'));
        if (!DBHelper::fieldExists('course_user', 'document_timestamp')) {
            Database::get()->query("ALTER TABLE `course_user` ADD document_timestamp DATETIME NOT NULL,
                CHANGE `reg_date` `reg_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `course_user` SET document_timestamp = NOW()");
        }

        if (get_config('course_guest') == '') {
            set_config('course_guest', 'link');
        }

        // fix agenda entries without duration
        Database::get()->query("UPDATE agenda SET duration = '0:00' WHERE duration = ''");
        // Fix wiki last_version id's
        Database::get()->query("UPDATE wiki_pages SET last_version = (SELECT MAX(id) FROM wiki_pages_content WHERE pid = wiki_pages.id)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS module_disable (module_id int(11) NOT NULL PRIMARY KEY)");
        DBHelper::fieldExists('assignment', 'submission_type') or
            Database::get()->query("ALTER TABLE `assignment` ADD `submission_type` TINYINT NOT NULL DEFAULT '0' AFTER `comments`");
        DBHelper::fieldExists('assignment_submit', 'submission_text') or
            Database::get()->query("ALTER TABLE `assignment_submit` ADD `submission_text` MEDIUMTEXT NULL DEFAULT NULL AFTER `file_name`");
        Database::get()->query("UPDATE `assignment` SET `max_grade` = 10 WHERE `max_grade` IS NULL");
        Database::get()->query("ALTER TABLE `assignment` CHANGE `max_grade` `max_grade` FLOAT NOT NULL DEFAULT '10'");
        // default assignment end date value should be null instead of 0000-00-00 00:00:00
        Database::get()->query("ALTER TABLE `assignment` CHANGE `deadline` `deadline` DATETIME NULL DEFAULT NULL");
        Database::get()->query("UPDATE `assignment` SET `deadline` = NULL WHERE `deadline` = '0000-00-00 00:00:00'");
        // improve primary key for table exercise_answer
        Database::get()->query("CREATE TABLE IF NOT EXISTS `tag_element_module` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `course_id` int(11) NOT NULL,
                    `module_id` int(11) NOT NULL,
                    `element_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `date` DATETIME DEFAULT NULL,
                    `tag_id` int(11) NOT NULL)");
        DBHelper::indexExists('tag_element_module', 'tag_element_index') or
            Database::get()->query("CREATE INDEX `tag_element_index` ON `tag_element_module` (course_id, module_id, element_id)");
        // Tag tables upgrade
        if (DBHelper::fieldExists('tags', 'tag')) {
            $tags = Database::get()->queryArray("SELECT * FROM tags");
            $module_ids = array(
                'work'          =>  MODULE_ID_ASSIGN,
                'announcement'  =>  MODULE_ID_ANNOUNCE,
                'exe'           =>  MODULE_ID_EXERCISE
            );
            foreach ($tags as $tag) {
                $first_tag_id = Database::get()->querySingle("SELECT `id` FROM `tags` WHERE `tag` = ?s ORDER BY `id` ASC", $tag->tag)->id;
                Database::get()->query("INSERT INTO `tag_element_module` (`module_id`,`element_id`, `tag_id`)
                                        VALUES (?d, ?d, ?d)", $module_ids[$tag->element_type], $tag->element_id, $first_tag_id);
            }
            // keep one instance of each tag (the one with the lowest id)
            Database::get()->query("DELETE t1 FROM tags t1, tags t2 WHERE t1.id > t2.id AND t1.tag = t2.tag");
            Database::get()->query("ALTER TABLE tags DROP COLUMN `element_type`, "
                    . "DROP COLUMN `element_id`, DROP COLUMN `user_id`, DROP COLUMN `date`, DROP COLUMN `course_id`");
            Database::get()->query("ALTER TABLE tags CHANGE `tag` `name` varchar (255)");
            Database::get()->query("ALTER TABLE tags ADD UNIQUE KEY (name)");
            Database::get()->query("RENAME TABLE `tags` TO `tag`");
        }
        Database::get()->query("CREATE TABLE IF NOT EXISTS tag (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            UNIQUE KEY (name)) $charset_spec");

        if (!DBHelper::fieldExists('blog_post', 'commenting')) {
            Database::get()->query("ALTER TABLE `blog_post` ADD `commenting` TINYINT NOT NULL DEFAULT '1' AFTER `views`");
        }
        Database::get()->query("UPDATE unit_resources SET type = 'videolink' WHERE type = 'videolinks'");

        //importing new themes
        importThemes();
        //unlinking files that were used with the old theme import mechanism
        @unlink("$webDir/template/default/img/bcgr_lines_petrol_les saturation.png");
        @unlink("$webDir/template/default/img/eclass-new-logo_atoms.png");
        @unlink("$webDir/template/default/img/OpenCourses_banner_Color_theme1-1.png");
        @unlink("$webDir/template/default/img/banner_Sketch_empty-1-2.png");
        @unlink("$webDir/template/default/img/eclass-new-logo_sketchy.png");
        @unlink("$webDir/template/default/img/Light_sketch_bcgr2-1.png");
        @unlink("$webDir/template/default/img/Open-eClass-4-1-1.jpg");
        @unlink("$webDir/template/default/img/eclass_ice.png");
        @unlink("$webDir/template/default/img/eclass-new-logo_ice.png");
        @unlink("$webDir/template/default/img/ice.png");
        @unlink("$webDir/template/default/img/eclass_classic2-1-1.png");
        @unlink("$webDir/template/default/img/eclass-new-logo_classic.png");
    }

    // -----------------------------------
    // upgrade queries for 3.2
    // -----------------------------------
    if (version_compare($oldversion, '3.2', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.2'));
        set_config('ext_bigbluebutton_enabled',
            Database::get()->querySingle("SELECT COUNT(*) AS count FROM bbb_servers WHERE enabled='true'")->count > 0? '1': '0');

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `shortname` VARCHAR(255) NOT NULL,
                                `name` MEDIUMTEXT NOT NULL,
                                `description` MEDIUMTEXT NULL DEFAULT NULL,
                                `datatype` VARCHAR(255) NOT NULL,
                                `categoryid` INT(11) NOT NULL DEFAULT 0,
                                `sortorder`  INT(11) NOT NULL DEFAULT 0,
                                `required` TINYINT NOT NULL DEFAULT 0,
                                `visibility` TINYINT NOT NULL DEFAULT 0,
                                `user_type` TINYINT NOT NULL,
                                `registration` TINYINT NOT NULL DEFAULT 0,
                                `data` TEXT NULL DEFAULT NULL) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data` (
                                `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                                `field_id` INT(11) NOT NULL,
                                `data` TEXT NOT NULL,
                                PRIMARY KEY (`user_id`, `field_id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data_pending` (
                                `user_request_id` INT(11) NOT NULL DEFAULT 0,
                                `field_id` INT(11) NOT NULL,
                                `data` TEXT NOT NULL,
                                PRIMARY KEY (`user_request_id`, `field_id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_category` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `name` MEDIUMTEXT NOT NULL,
                                `sortorder`  INT(11) NOT NULL DEFAULT 0) $charset_spec");


        // Autojudge fields
        if (!DBHelper::fieldExists('assignment', 'auto_judge')) {
            Database::get()->query("ALTER TABLE `assignment`
                ADD `auto_judge` TINYINT(1) NOT NULL DEFAULT 0,
                ADD `auto_judge_scenarios` TEXT,
                ADD `lang` VARCHAR(10) NOT NULL DEFAULT ''");
            Database::get()->query("ALTER TABLE `assignment_submit`
                ADD `auto_judge_scenarios_output` TEXT");
        }

        if (!DBHelper::fieldExists('link', 'user_id')) {
            Database::get()->query("ALTER TABLE `link` ADD `user_id` INT(11) DEFAULT 0 NOT NULL");
        }
        if (!DBHelper::fieldExists('exercise', 'ip_lock')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `ip_lock` TEXT NULL DEFAULT NULL");
        }
        if (!DBHelper::fieldExists('exercise', 'password_lock')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `password_lock` VARCHAR(255) NULL DEFAULT NULL");
        }
        // Recycle object table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `recyclebin` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `tablename` varchar(100) NOT NULL,
            `entryid` int(11) NOT NULL,
            `entrydata` varchar(4000) NOT NULL,
            KEY `entryid` (`entryid`), KEY `tablename` (`tablename`)) $charset_spec");

        // Auto-enroll rules tables
        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `status` TINYINT(4) NOT NULL DEFAULT 0)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule_department` (
            `rule` INT(11) NOT NULL,
            `department` INT(11) NOT NULL,
            PRIMARY KEY (rule, department),
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_course` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rule` INT(11) NOT NULL DEFAULT 0,
            `course_id` INT(11) NOT NULL,
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_department` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rule` INT(11) NOT NULL DEFAULT 0,
            `department_id` INT(11) NOT NULL,
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES hierarchy(id) ON DELETE CASCADE)");

        // Abuse report table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `abuse_report` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rid` INT(11) NOT NULL,
            `rtype` VARCHAR(50) NOT NULL,
            `course_id` INT(11) NOT NULL,
            `reason` VARCHAR(50) NOT NULL DEFAULT '',
            `message` TEXT NOT NULL,
            `timestamp` INT(11) NOT NULL DEFAULT 0,
            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            INDEX `abuse_report_index_1` (`rid`, `rtype`, `user_id`, `status`),
            INDEX `abuse_report_index_2` (`course_id`, `status`)) $charset_spec");

        // Delete old key 'language' (it has been replaced by 'default_language')
        Database::get()->query("DELETE FROM config WHERE `key` = 'language'");

        // Add grading scales table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `grading_scale` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255) NOT NULL,
            `scales` text NOT NULL,
            `course_id` int(11) NOT NULL,
            KEY `course_id` (`course_id`)) $charset_spec");

        // Add grading_scale_id field to assignments
        if (!DBHelper::fieldExists('assignment', 'grading_scale_id')) {
            Database::get()->query("ALTER TABLE `assignment` ADD `grading_scale_id` INT(11) NOT NULL DEFAULT '0' AFTER `max_grade`");
        }

        // Add show results to participants field
        if (!DBHelper::fieldExists('poll', 'show_results')) {
            Database::get()->query("ALTER TABLE `poll` ADD `show_results` TINYINT NOT NULL DEFAULT '0'");
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_to_specific` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` int(11) NULL,
            `group_id` int(11) NULL,
            `poll_id` int(11) NOT NULL ) $charset_spec");

        if (!DBHelper::fieldExists('poll', 'assign_to_specific')) {
            Database::get()->query("ALTER TABLE `poll` ADD `assign_to_specific` TINYINT NOT NULL DEFAULT '0'");
        }
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_to_specific` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `user_id` int(11) NULL,
                    `group_id` int(11) NULL,
                    `exercise_id` int(11) NOT NULL ) $charset_spec");
        if (!DBHelper::fieldExists('exercise', 'assign_to_specific')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `assign_to_specific` TINYINT NOT NULL DEFAULT '0'");
        }
        // This is needed for ALTER IGNORE TABLE
        Database::get()->query('SET SESSION old_alter_table = 1');

        // Unique and foreign keys for user_department table
        if (DBHelper::indexExists('user_department', 'udep_id')) {
            Database::get()->query('DROP INDEX `udep_id` ON user_department');
        }

        if (!DBHelper::indexExists('user_department', 'udep_unique')) {
            Database::get()->queryFunc('SELECT user_department.id FROM user
                        RIGHT JOIN user_department ON user.id = user_department.user
                    WHERE user.id IS NULL', function ($item) {
                Recycle::deleteObject('user_department', $item->id, 'id');
            });
            Database::get()->queryFunc('SELECT user_department.id FROM hierarchy
                        RIGHT JOIN user_department ON hierarchy.id = user_department.department
                    WHERE hierarchy.id IS NULL', function ($item) {
                Recycle::deleteObject('user_department', $item->id, 'id');
            });
            Database::get()->query('ALTER TABLE user_department CHANGE `user` `user` INT(11) NOT NULL');
            Database::get()->query('ALTER IGNORE TABLE `user_department`
                ADD UNIQUE KEY `udep_unique` (`user`,`department`),
                ADD FOREIGN KEY (user) REFERENCES user(id) ON DELETE CASCADE,
                ADD FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE');
        }

        // Unique and foreign keys for course_department table
        if (DBHelper::indexExists('course_department', 'cdep_index')) {
            Database::get()->query('DROP INDEX `cdep_index` ON course_department');
        }
        if (!DBHelper::indexExists('course_department', 'cdep_unique')) {
            Database::get()->queryFunc('SELECT course_department.id FROM course
                        RIGHT JOIN course_department ON course.id = course_department.course
                    WHERE course.id IS NULL', function ($item) {
                Recycle::deleteObject('course_department', $item->id, 'id');
            });
            Database::get()->queryFunc('SELECT course_department.id FROM hierarchy
                        RIGHT JOIN course_department ON hierarchy.id = course_department.department
                    WHERE hierarchy.id IS NULL', function ($item) {
                Recycle::deleteObject('course_department', $item->id, 'id');
            });
            Database::get()->query('ALTER IGNORE TABLE `course_department`
                ADD UNIQUE KEY `cdep_unique` (`course`,`department`),
                ADD FOREIGN KEY (course) REFERENCES course(id) ON DELETE CASCADE,
                ADD FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE');
        }

        // External authentication via Hybridauth
        Database::get()->query("INSERT IGNORE INTO `auth`
            (auth_id, auth_name, auth_title, auth_settings, auth_instructions, auth_default)
            VALUES
            (8, 'facebook', '', '', '', 0),
            (9, 'twitter', '', '', '', 0),
            (10, 'google', '', '', '', 0),
            (11, 'live', 'Microsoft Live Account', '', 'does not work locally', 0),
            (12, 'yahoo', '', '', 'does not work locally', 0),
            (13, 'linkedin', '', '', '', 0)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `user_ext_uid` (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            auth_id INT(2) NOT NULL,
            uid VARCHAR(64) NOT NULL,
            UNIQUE KEY (user_id, auth_id),
            KEY (uid),
            FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE)
            $charset_spec");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `user_request_ext_uid` (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_request_id INT(11) NOT NULL,
            auth_id INT(2) NOT NULL,
            uid VARCHAR(64) NOT NULL,
            UNIQUE KEY (user_request_id, auth_id),
            FOREIGN KEY (`user_request_id`) REFERENCES `user_request` (`id`) ON DELETE CASCADE)
            $charset_spec");

        if (!DBHelper::fieldExists('gradebook', 'start_date')) {
            Database::get()->query("ALTER TABLE `gradebook` ADD `start_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `gradebook` SET `start_date` = " . DBHelper::timeAfter(-6*30*24*60*60) . ""); // 6 months before
            $q = Database::get()->queryArray("SELECT gradebook_book.id, grade,`range` FROM gradebook, gradebook_activities, gradebook_book
                                                    WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                                                    AND gradebook_activities.gradebook_id = gradebook.id");
            foreach ($q as $data) {
                Database::get()->query("UPDATE gradebook_book SET grade = $data->grade/$data->range WHERE id = $data->id");
            }
        }
        if (!DBHelper::fieldExists('gradebook', 'end_date')) {
            Database::get()->query("ALTER TABLE `gradebook` ADD `end_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `gradebook` SET `end_date` = " . DBHelper::timeAfter(6*30*24*60*60) . ""); // 6 months after
        }

        if (!DBHelper::fieldExists('attendance', 'start_date')) {
            Database::get()->query("ALTER TABLE `attendance` ADD `start_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `attendance` SET `start_date` = " . DBHelper::timeAfter(-6*30*24*60*60) . ""); // 6 months before
        }
        if (!DBHelper::fieldExists('attendance', 'end_date')) {
            Database::get()->query("ALTER TABLE `attendance` ADD `end_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `attendance` SET `end_date` = " . DBHelper::timeAfter(6*30*24*60*60) . ""); // 6 months after
        }
        // Cancelled exercises total weighting fix
        $exercises = Database::get()->queryArray("SELECT exercise.id AS id, exercise.course_id AS course_id, exercise_user_record.eurid AS eurid "
                . "FROM exercise_user_record, exercise "
                . "WHERE exercise_user_record.eid = exercise.id "
                . "AND exercise_user_record.total_weighting = 0 "
                . "AND exercise_user_record.attempt_status = 4");
        foreach ($exercises as $exercise) {
            $totalweight = Database::get()->querySingle("SELECT SUM(exercise_question.weight) AS totalweight
                                            FROM exercise_question, exercise_with_questions
                                            WHERE exercise_question.course_id = ?d
                                            AND exercise_question.id = exercise_with_questions.question_id
                                            AND exercise_with_questions.exercise_id = ?d", $exercise->course_id, $exercise->id)->totalweight;
            Database::get()->query("UPDATE exercise_user_record SET total_weighting = ?f WHERE eurid = ?d", $totalweight, $exercise->eurid);
        }

        if (DBHelper::fieldExists('link', 'hits')) { // not needed
           delete_field('link', 'hits');
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `group_category` (
                                `id` INT(6) NOT NULL AUTO_INCREMENT,
                                `course_id` INT(11) NOT NULL,
                                `name` VARCHAR(255) NOT NULL,
                                `description` TEXT,
                                PRIMARY KEY (`id`, `course_id`)) $charset_spec");

        if (!DBHelper::fieldExists('group', 'category_id')) {
            Database::get()->query("ALTER TABLE `group` ADD `category_id` INT(11) NULL");
        }
        //Group Mapping due to group_id addition in group_properties table
        if (!DBHelper::fieldExists('group_properties', 'group_id')) {
            Database::get()->query("ALTER TABLE `group_properties` ADD `group_id` INT(11) NOT NULL DEFAULT 0");
            Database::get()->query("ALTER TABLE `group_properties` DROP PRIMARY KEY");

            $group_info = Database::get()->queryArray("SELECT * FROM group_properties");
            foreach ($group_info as $group) {
                $cid = $group->course_id;
                $self_reg = $group->self_registration;
                $multi_reg = $group->multiple_registration;
                $unreg = $group->allow_unregister;
                $forum = $group->forum;
                $priv_forum = $group->private_forum;
                $documents = $group->documents;
                $wiki = $group->wiki;
                $agenda = $group->agenda;

                Database::get()->query("DELETE FROM group_properties WHERE course_id = ?d", $cid);

                $num = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d", $cid);

                foreach ($num as $group_num) {
                    $group_id = $group_num->id;
                    Database::get()->query("INSERT INTO `group_properties` (course_id, group_id, self_registration, allow_unregister, forum, private_forum, documents, wiki, agenda)
                                                    VALUES  (?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d)", $cid, $group_id, $self_reg, $unreg, $forum, $priv_forum, $documents, $wiki, $agenda);
                }
                setting_set(SETTING_GROUP_MULTIPLE_REGISTRATION, $multi_reg, $cid);
            }
            Database::get()->query("ALTER TABLE `group_properties` ADD PRIMARY KEY (group_id)");
            delete_field('group_properties', 'multiple_registration');
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_user_request` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `uid` int(11) NOT NULL,
                        `course_id` int(11) NOT NULL,
                        `comments` text,
                        `status` int(11) NOT NULL,
                        `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        PRIMARY KEY (`id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_user_record` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(255) DEFAULT NULL,
            `email_verification` TINYINT(1) DEFAULT NULL,
            `verification_code` VARCHAR(255) DEFAULT NULL) $charset_spec");
        if (!DBHelper::fieldExists('poll_answer_record', 'poll_user_record_id')) {
            Database::get()->query("ALTER TABLE `poll_answer_record` "
                    . "ADD `poll_user_record_id` INT(11) NOT NULL AFTER `arid`");

            if ($user_records = Database::get()->queryArray("SELECT DISTINCT `pid`, `user_id` FROM poll_answer_record")) {
                foreach ($user_records as $user_record) {
                    $poll_user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid) VALUES (?d, ?d)", $user_record->pid, $user_record->user_id)->lastInsertID;
                    Database::get()->query("UPDATE poll_answer_record SET poll_user_record_id = ?d WHERE pid = ?d AND user_id = ?d", $poll_user_record_id, $user_record->pid, $user_record->user_id);
                }
            }
            Database::get()->query("ALTER TABLE `poll_answer_record` ADD FOREIGN KEY (`poll_user_record_id`) REFERENCES `poll_user_record` (`id`) ON DELETE CASCADE");
            delete_field('poll_answer_record', 'pid');
            delete_field('poll_answer_record', 'user_id');
        }
        DBHelper::indexExists('poll_user_record', 'poll_user_rec_id') or
            Database::get()->query("CREATE INDEX `poll_user_rec_id` ON poll_user_record(pid, uid)");
        //Removing Course Home Layout 2
        Database::get()->query("UPDATE course SET home_layout = 1 WHERE home_layout = 2");
    }

    // -----------------------------------
    // upgrade queries for 3.3
    // -----------------------------------
    if (version_compare($oldversion, '3.3', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.3'));

        // Remove '0000-00-00' default dates and fix exercise weight fields
        Database::get()->query('ALTER TABLE `announcement`
            MODIFY `date` DATETIME NOT NULL,
            MODIFY `start_display` DATETIME DEFAULT NULL,
            MODIFY `stop_display` DATETIME DEFAULT NULL');
        Database::get()->query("UPDATE IGNORE announcement SET start_display=null
            WHERE start_display='0000-00-00 00:00:00'");
        Database::get()->query("UPDATE IGNORE announcement SET stop_display=null
            WHERE stop_display='0000-00-00 00:00:00'");
        Database::get()->query('ALTER TABLE `agenda`
            CHANGE `start` `start` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `course`
            MODIFY `created` DATETIME DEFAULT NULL,
            MODIFY `start_date` DATE DEFAULT NULL,
            MODIFY `finish_date` DATE DEFAULT NULL');
        Database::get()->query("UPDATE IGNORE course SET start_date=null
                            WHERE start_date='0000-00-00 00:00:00'");
        Database::get()->query("UPDATE IGNORE course SET finish_date=null
                            WHERE finish_date='0000-00-00 00:00:00'");
        Database::get()->query('ALTER TABLE `course_weekly_view`
            MODIFY `start_week` DATE DEFAULT NULL,
            MODIFY `finish_week` DATE DEFAULT NULL');
        Database::get()->query('ALTER TABLE `course_weekly_view_activities`
            CHANGE `date` `date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `course_user_request`
            CHANGE `ts` `ts` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `user`
            CHANGE `registered_at` `registered_at` DATETIME NOT NULL,
            CHANGE `expires_at` `expires_at` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `loginout`
            CHANGE `when` `when` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `personal_calendar`
            CHANGE `start` `start` datetime NOT NULL');
        Database::get()->query('ALTER TABLE `admin_calendar`
            CHANGE `start` `start` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `loginout_summary`
            CHANGE `start_date` `start_date` DATETIME NOT NULL,
            CHANGE `end_date` `end_date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `document`
            CHANGE `date` `date` DATETIME NOT NULL,
            CHANGE `date_modified` `date_modified` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `wiki_pages`
            CHANGE `ctime` `ctime` DATETIME NOT NULL,
            CHANGE `last_mtime` `last_mtime` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `wiki_pages_content`
            CHANGE `mtime` `mtime` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `wiki_locks`
            MODIFY `ltime_created` DATETIME DEFAULT NULL,
            MODIFY `ltime_alive` DATETIME DEFAULT NULL;');
        Database::get()->query('ALTER TABLE `poll`
            CHANGE `creation_date` `creation_date` DATETIME NOT NULL,
            CHANGE `start_date` `start_date` DATETIME DEFAULT NULL,
            CHANGE `end_date` `end_date` DATETIME DEFAULT NULL');
        Database::get()->query('ALTER TABLE `poll_answer_record`
            CHANGE `submit_date` `submit_date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `assignment`
            CHANGE `submission_date` `submission_date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `assignment_submit`
            CHANGE `submission_date` `submission_date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `exercise_user_record`
            CHANGE `record_start_date` `record_start_date` DATETIME NOT NULL,
            CHANGE `total_score` `total_score` FLOAT(11,2) NOT NULL DEFAULT 0,
            CHANGE `total_weighting` `total_weighting` FLOAT(11,2) DEFAULT 0');
        Database::get()->query('ALTER TABLE `exercise_answer_record`
            CHANGE `weight` `weight` FLOAT(11,2) DEFAULT NULL');
        Database::get()->query('ALTER TABLE `unit_resources`
            CHANGE `date` `date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `actions_summary`
            CHANGE `start_date` `start_date` DATETIME NOT NULL,
            CHANGE `end_date` `end_date` DATETIME NOT NULL');
        Database::get()->query('ALTER TABLE `logins`
            CHANGE `date_time` `date_time` DATETIME NOT NULL');

        // Fix incorrectly-graded fill-in-blanks questions
        Database::get()->queryFunc('SELECT question_id, answer, type
            FROM exercise_question, exercise_answer
            WHERE question_id = exercise_question.id AND
                  type in (?d, ?d) AND answer LIKE ?s',
            function ($item) {
                if (preg_match('#\[/?m[^]]#', $item->answer)) {
                    Database::get()->queryFunc('SELECT answer_record_id, answer, answer_id, weight
                        FROM exercise_user_record, exercise_answer_record
                        WHERE exercise_user_record.eurid = exercise_answer_record.eurid AND
                              exercise_answer_record.question_id = ?d AND
                              attempt_status IN (?d, ?d)',
                        function ($a) use ($item) {
                            static $answers, $weights;
                            $qid = $item->question_id;
                            if (!isset($answers[$qid])) {
                                // code from modules/exercise/exercise.class.php lines 865-878
                                list($answer, $answerWeighting) = explode('::', $item->answer);
                                $weights[$qid] = explode(',', $answerWeighting);
                                preg_match_all('#(?<=\[)(?!/?m])[^\]]+#', $answer, $match);
                                if ($item->type == FILL_IN_BLANKS_TOLERANT) {
                                    $expected = array_map(function ($m) {
                                           return strtr(mb_strtoupper($m, 'UTF-8'), 'ΆΈΉΊΌΎΏ', 'ΑΕΗΙΟΥΩ');
                                        }, $match[0]);
                                } else {
                                    $expected = $match[0];
                                }
                                $answers[$qid] = array_map(function ($str) {
                                        return preg_split('/\s*\|\s*/', $str);
                                    }, $expected);
                            }
                            if ($item->type == FILL_IN_BLANKS_TOLERANT) {
                                $choice = strtr(mb_strtoupper($a->answer, 'UTF-8'), 'ΆΈΉΊΌΎΏ', 'ΑΕΗΙΟΥΩ');
                            } else {
                                $choice = $a->answer;
                            }
                            $aid = $a->answer_id - 1;
                            $weight = in_array($choice, $answers[$qid][$aid]) ? $weights[$qid][$aid] : 0;
                            if ($weight != $a->weight) {
                                Database::get()->query('UPDATE exercise_answer_record
                                    SET weight = ?f WHERE answer_record_id = ?d',
                                    $weight, $a->answer_record_id);
                            }
                        }, $item->question_id, ATTEMPT_COMPLETED, ATTEMPT_PAUSED);
                }
            }, FILL_IN_BLANKS, FILL_IN_BLANKS_TOLERANT, '%[m%');

        // Fix duplicate exercise answer records
        Database::get()->queryFunc('SELECT COUNT(*) AS cnt,
                    MIN(answer_record_id) AS min_answer_record_id
                FROM exercise_answer_record
                GROUP BY eurid, question_id, answer, answer_id
                HAVING cnt > 1',
            function ($item) {
                $details = Database::get()->querySingle('SELECT * FROM exercise_answer_record
                    WHERE answer_record_id = ?d', $item->min_answer_record_id);
                if (is_null($details->answer)) {
                    Database::get()->query('DELETE FROM exercise_answer_record
                        WHERE eurid = ?d AND question_id = ?d AND answer IS NULL AND
                              answer_id = ?d AND answer_record_id > ?d',
                        $details->eurid, $details->question_id, $details->answer_id,
                        $item->min_answer_record_id);
                } else {
                    Database::get()->query('DELETE FROM exercise_answer_record
                        WHERE eurid = ?d AND question_id = ?d AND answer = ?s AND
                              answer_id = ?d AND answer_record_id > ?d',
                        $details->eurid, $details->question_id, $details->answer,
                        $details->answer_id, $item->min_answer_record_id);
                }
            });

        // Fix incorrect exercise answer grade totals
        Database::get()->query('CREATE TEMPORARY TABLE exercise_answer_record_total AS
            SELECT SUM(weight) AS TOTAL, exercise_answer_record.eurid AS eurid
                FROM exercise_user_record, exercise_answer_record
                WHERE exercise_user_record.eurid = exercise_answer_record.eurid AND
                      attempt_status = ?d
                GROUP BY eurid',
                ATTEMPT_COMPLETED);
        Database::get()->query('UPDATE exercise_user_record, exercise_answer_record_total
            SET exercise_user_record.total_score = exercise_answer_record_total.total
            WHERE exercise_user_record.eurid = exercise_answer_record_total.eurid AND
                  exercise_user_record.total_score <> exercise_answer_record_total.total');
        Database::get()->query('DROP TEMPORARY TABLE exercise_answer_record_total');

        // Fix duplicate link orders
        Database::get()->queryFunc('SELECT DISTINCT course_id, category FROM link
            GROUP BY course_id, category, `order` HAVING COUNT(*) > 1',
            function ($item) {
                $order = 0;
                foreach (Database::get()->queryArray('SELECT id FROM link
                    WHERE course_id = ?d AND category = ?d
                    ORDER BY `order`',
                    $item->course_id, $item->category) as $link) {
                        Database::get()->query('UPDATE link SET `order` = ?d
                            WHERE id = ?d', $order++, $link->id);
                }
            });

        Database::get()->query("UPDATE link SET `url` = '' WHERE `url` IS NULL");
        Database::get()->query("UPDATE link SET `title` = '' WHERE `title` IS NULL");
        Database::get()->query('ALTER TABLE link
            CHANGE `url` `url` TEXT NOT NULL,
            CHANGE `title` `title` TEXT NOT NULL');

        // Fix duplicate poll_question orders
        Database::get()->queryFunc('SELECT `pid`
                FROM `poll_question`
                GROUP BY `pid`, `q_position` HAVING COUNT(`pqid`) > 1',
                function ($item) {
                    $poll_questions = Database::get()->queryArray("SELECT * FROM `poll_question` WHERE pid = ?d", $item->pid);
                    $order = 1;
                    foreach ($poll_questions as $poll_question) {
                        Database::get()->query('UPDATE `poll_question` SET `q_position` = ?d
                                                    WHERE pqid = ?d', $order++, $poll_question->pqid);
                    }
                });
        if (!DBHelper::fieldExists('poll', 'public')) {
            Database::get()->query("ALTER TABLE `poll` ADD `public` TINYINT(1) NOT NULL DEFAULT 1 AFTER `active`");
            Database::get()->query("UPDATE `poll` SET `public` = 0");
        }

        // If Shibboleth auth is enabled, try reading current settings and
        // regenerate secure index if successful
        if (Database::get()->querySingle('SELECT auth_default FROM auth
                WHERE auth_name = ?s', 'shibboleth')->auth_default) {
            $secureIndexPath = $webDir . '/secure/index.php';
            $shib_vars = get_shibboleth_vars($secureIndexPath);
            if (count($shib_vars) and isset($shib_vars['uname'])) {
                $shib_config = array();
                foreach ($shib_vars as $shib_var => $shib_value) {
                    $shib_config['shib_' . $shib_var] = $shib_value;
                }
                update_shibboleth_endpoint($shib_config);
            }
        }
    }

    // -----------------------------------
    // upgrade queries for 3.4
    // -----------------------------------
    if (version_compare($oldversion, '3.4', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.4'));

        // Conference table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `conference` (
                        `conf_id` int(11) NOT NULL AUTO_INCREMENT,
                        `course_id` int(11) NOT NULL,
                        `conf_title` text NOT NULL,
                        `conf_description` text DEFAULT NULL,
                        `status` enum('active','inactive') DEFAULT 'active',
                        `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `user_id` varchar(255) default '0',
                        `group_id` varchar(255) default '0',
                        PRIMARY KEY (`conf_id`,`course_id`)) $charset_spec");

        // create db entries about old chats
        $query = Database::get()->queryArray("SELECT id, code FROM course");
        foreach ($query as $codes) {
            $c = $codes->code;
            $chatfile = "$webDir/courses/$c/chat.txt";
            if (file_exists($chatfile) and filesize($chatfile) > 0) {
                $q_ins = Database::get()->query("INSERT INTO conference SET
                                                course_id = $codes->id,
                                                conf_title = '$langUntitledChat',
                                                status = 'active'");
                $last_conf_id = $q_ins->lastInsertID;
                $newchatfile = "$webDir/courses/$c/" . $last_conf_id . "_chat.txt";
                rename($chatfile, $newchatfile);
            }
        }

        // upgrade poll table (COLLES and ATTLS support)
        if (!DBHelper::fieldExists('poll', 'type')) {
            Database::get()->query("ALTER TABLE `poll` ADD `type` TINYINT(1) NOT NULL DEFAULT 0");
        }

        // upgrade bbb_session table
        if (DBHelper::tableExists('bbb_session')) {
            if (!DBHelper::fieldExists('bbb_session', 'end_date')) {
                Database::get()->query("ALTER TABLE `bbb_session` ADD `end_date` datetime DEFAULT NULL AFTER `start_date`");
            }
            Database::get()->query("RENAME TABLE bbb_session TO tc_session");
        }

        // upgrade bbb_servers table
        if (DBHelper::tableExists('bbb_servers')) {
            if (!DBHelper::fieldExists('bbb_servers', 'all_courses')) {
                Database::get()->query("ALTER TABLE bbb_servers ADD `type` varchar(255) NOT NULL DEFAULT 'bbb' AFTER id");
                Database::get()->query("ALTER TABLE bbb_servers ADD port varchar(255) DEFAULT NULL AFTER ip");
                Database::get()->query("ALTER TABLE bbb_servers ADD username varchar(255) DEFAULT NULL AFTER server_key");
                Database::get()->query("ALTER TABLE bbb_servers ADD password varchar(255) DEFAULT NULL AFTER username");
                Database::get()->query("ALTER TABLE bbb_servers ADD webapp varchar(255) DEFAULT NULL AFTER api_url");
                Database::get()->query("ALTER TABLE bbb_servers ADD screenshare varchar(255) DEFAULT NULL AFTER weight");
                Database::get()->query("ALTER TABLE bbb_servers ADD all_courses TINYINT(1) NOT NULL DEFAULT 1");
            }
            // rename `bbb_servers` to `tc_servers`
            if (DBHelper::tableExists('bbb_servers')) {
                Database::get()->query("RENAME TABLE bbb_servers TO tc_servers");
            }
        }

        // course external server table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_external_server` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `course_id` int(11) NOT NULL,
            `external_server` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY (`external_server`, `course_id`)) $charset_spec");

        // drop trigger
        Database::get()->query("DROP TRIGGER IF EXISTS personal_calendar_settings_init");

        //Create Sticky Announcements
        $arr_date = Database::get()->queryArray("SELECT id FROM announcement ORDER BY `date` ASC");
        $arr_order_objects = Database::get()->queryArray("SELECT id FROM announcement ORDER BY `order` ASC");
        $arr_order = [];
        foreach ($arr_order_objects as $key=>$value) {
            $arr_order[$key] = $value->id;
        }

        $length = count($arr_order);

        $offset = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($arr_date[$i]->id != $arr_order[$i-$offset]) {
                $offset++;
            }
        }

        $zero = $length - $offset;
        $arr_sticky = array_slice($arr_order, -$offset);
        $arr_default = array_slice($arr_order, 0, $zero);

        $default_placeholders = implode(',', array_fill(0, count($arr_default), '?d'));
        if ($default_placeholders) {
            Database::get()->query("UPDATE `announcement` SET `order` = 0 WHERE `id` IN ($default_placeholders)", $arr_default);
        }

        $ordering = 0;
        foreach ($arr_sticky as $announcement_id) {
            $ordering++;
            Database::get()->query("UPDATE `announcement` SET `order` = ?d where `id`= ?d", $ordering, $announcement_id);
        }

        //Create FAQ table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `faq` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `title` text NOT NULL,
                            `body` text NOT NULL,
                            `order` int(11) NOT NULL,
                            PRIMARY KEY (`id`)) $charset_spec");

        //wall tables
        Database::get()->query("CREATE TABLE IF NOT EXISTS `wall_post` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `content` TEXT DEFAULT NULL,
                            `extvideo` VARCHAR(250) DEFAULT '',
                            `timestamp` INT(11) NOT NULL DEFAULT 0,
                            `pinned` TINYINT(1) NOT NULL DEFAULT 0,
                            INDEX `wall_post_index` (`course_id`)) $charset_spec");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `wall_post_resources` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `post_id` INT(11) NOT NULL,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `res_id` INT(11) NOT NULL,
                            `type` VARCHAR(255) NOT NULL DEFAULT '',
                            INDEX `wall_post_resources_index` (`post_id`)) $charset_spec");
    }

    if (version_compare($oldversion, '3.5', '<')) {
        updateInfo(-1, sprintf($langUpgForVersion, '3.5'));

        // Fix multiple equal orders for the same unit if needed
        Database::get()->queryFunc('SELECT course_id FROM course_units
            GROUP BY course_id, `order` HAVING COUNT(`order`) > 1',
            function ($course) {
                $i = 0;
                Database::get()->queryFunc('SELECT id
                    FROM course_units WHERE course_id = ?d
                    ORDER BY `order`',
                    function ($unit) use (&$i) {
                        $i++;
                        Database::get()->query('UPDATE course_units SET `order` = ?d
                            WHERE id = ?d', $i, $unit->id);
                    }, $course->course_id);
            });
        if (!DBHelper::indexExists('course_units', 'course_units_order')) {
            Database::get()->query('ALTER TABLE course_units
                ADD UNIQUE KEY `course_units_order` (`course_id`,`order`)');
        }
        Database::get()->queryFunc('SELECT unit_id FROM unit_resources
            GROUP BY unit_id, `order` HAVING COUNT(`order`) > 1',
            function ($unit) {
                $i = 0;
                Database::get()->queryFunc('SELECT id
                    FROM unit_resources WHERE unit_id = ?d
                    ORDER BY `order`',
                    function ($resource) use (&$i) {
                        $i++;
                        Database::get()->query('UPDATE unit_resources SET `order` = ?d
                            WHERE id = ?d', $i, $resource->id);
                    }, $unit->unit_id);
            });
        if (!DBHelper::indexExists('unit_resources', 'unit_resources_order')) {
            Database::get()->query('ALTER TABLE unit_resources
                ADD UNIQUE KEY `unit_resources_order` (`unit_id`,`order`)');
        }
        //eportfolio tables and data
        Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `shortname` VARCHAR(255) NOT NULL,
                        `name` MEDIUMTEXT NOT NULL,
                        `description` MEDIUMTEXT NULL DEFAULT NULL,
                        `datatype` VARCHAR(255) NOT NULL,
                        `categoryid` INT(11) NOT NULL DEFAULT 0,
                        `sortorder`  INT(11) NOT NULL DEFAULT 0,
                        `required` TINYINT NOT NULL DEFAULT 0,
                        `data` TEXT NULL DEFAULT NULL) $charset_spec");
        
        Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_data` (
                        `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                        `field_id` INT(11) NOT NULL,
                        `data` TEXT NOT NULL,
                        PRIMARY KEY (`user_id`, `field_id`)) $charset_spec");
        
        Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_category` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `name` MEDIUMTEXT NOT NULL,
                        `sortorder`  INT(11) NOT NULL DEFAULT 0) $charset_spec");
        
        Database::get()->query("INSERT INTO `eportfolio_fields_category` (`id`, `name`, `sortorder`) VALUES
                        (1, '$langPersInfo', 0),
                        (2, '$langEduEmpl', -1),
                        (3, '$langAchievements', -2),
                        (4, '$langGoalsSkills', -3),
                        (5, '$langContactInfo', -4)");
        
        Database::get()->query("INSERT INTO `eportfolio_fields` (`id`, `shortname`, `name`, `description`, `datatype`, `categoryid`, `sortorder`, `required`, `data`) VALUES
                        (1, 'birth_date', '$langBirthDate', '', '3', 1, 0, 0, ''),
                        (2, 'birth_place', '$langBirthPlace', '', '1', 1, -1, 0, ''),
                        (3, 'gender', '$langGender', '', '4', 1, -2, 0, 'a:2:{i:0;s:".strlen($langMale).":\"$langMale\";i:1;s:".strlen($langFemale).":\"$langFemale\";}'),
                        (4, 'about_me', '$langAboutMe', '$langAboutMeDescr', '2', 1, -3, 0, ''),
                        (5, 'personal_website', '$langPersWebsite', '', '5', 1, -4, 0, ''),
                        (6, 'education', '$langEducation', '$langEducationDescr', '2', 2, 0, 0, ''),
                        (7, 'employment', '$langEmployment', '', '2', 2, -1, 0, ''),
                        (8, 'certificates_awards', '$langCertAwards', '', '2', 3, 0, 0, ''),
                        (9, 'publications', '$langPublications', '', '2', 3, -1, 0, ''),
                        (10, 'personal_goals', '$langPersGoals', '', '2', 4, 0, 0, ''),
                        (11, 'academic_goals', '$langAcademicGoals', '', '2', 4, -1, 0, ''),
                        (12, 'career_goals', '$langCareerGoals', '', '2', 4, -2, 0, ''),
                        (13, 'personal_skills', '$langPersSkills', '', '2', 4, -3, 0, ''),
                        (14, 'academic_skills', '$langAcademicSkills', '', '2', 4, -4, 0, ''),
                        (15, 'career_skills', '$langCareerSkills', '', '2', 4, -5, 0, ''),
                        (16, 'email', '$langEmail', '', '1', 5, 0, 0, ''),
                        (17, 'phone_number', '$langPhone', '', '1', 5, -1, 0, ''),
                        (18, 'Address', '$langAddress', '', '1', 5, -2, 0, ''),
                        (19, 'fb', '$langFBProfile', '', '5', 5, -3, 0, ''),
                        (20, 'twitter', '$langTwitterAccount', '', '5', 5, -4, 0, ''),
                        (21, 'linkedin', '$langLinkedInProfile', '', '5', 5, -5, 0, '')");
        
        Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_resource` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                        `resource_id` INT(11) NOT NULL,
                        `resource_type` VARCHAR(50) NOT NULL,
                        `course_id` INT(11) NOT NULL,
                        `course_title` VARCHAR(250) NOT NULL DEFAULT '',
                        `time_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `data` TEXT NOT NULL,
                        INDEX `eportfolio_res_index` (`user_id`,`resource_type`)) $charset_spec");
                
        Database::get()->query("INSERT INTO `config` (`key`, `value`) VALUES ('bio_quota', '4')");
        
        if (!DBHelper::fieldExists('user', 'eportfolio_enable')) {
            Database::get()->query("ALTER TABLE `user` ADD eportfolio_enable TINYINT(1) NOT NULL DEFAULT 0");
        }
        
        if (!DBHelper::fieldExists('user', 'public_blog')) {
            Database::get()->query("ALTER TABLE `user` ADD public_blog TINYINT(1) NOT NULL DEFAULT 0");
        }
                
        // fix wrong entries in statistics
        Database::get()->query("UPDATE actions_daily SET module_id = " .MODULE_ID_VIDEO . " WHERE module_id = 0");


        // hierarchy extra fields
        Database::get()->query("ALTER TABLE hierarchy ADD `description` TEXT AFTER name");
        Database::get()->query("ALTER TABLE hierarchy ADD `visible` tinyint(4) not null default 2 AFTER order_priority");

        Database::get()->query("DROP PROCEDURE IF EXISTS `add_node`");
        Database::get()->query("CREATE PROCEDURE `add_node` (IN name TEXT CHARSET utf8, IN description TEXT CHARSET utf8, IN parentlft INT(11),
                    IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN,
                    IN p_allow_user BOOLEAN, IN p_order_priority INT(11), IN p_visible TINYINT(4))
                LANGUAGE SQL
                BEGIN
                    DECLARE lft, rgt INT(11);

                    SET lft = parentlft + 1;
                    SET rgt = parentlft + 2;

                    CALL shift_right(parentlft, 2, 0);

                    INSERT INTO `hierarchy` (name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible) VALUES (name, description, lft, rgt, p_code, p_allow_course, p_allow_user, p_order_priority, p_visible);
                END");
        Database::get()->query("DROP PROCEDURE IF EXISTS `add_node_ext`");
        Database::get()->query("DROP PROCEDURE IF EXISTS `update_node`");
        Database::get()->query("CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name TEXT CHARSET utf8, IN p_description TEXT CHARSET utf8,
                    IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11),
                    IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN,
                    IN p_order_priority INT(11), IN p_visible TINYINT(4))
                LANGUAGE SQL
                BEGIN
                    UPDATE `hierarchy` SET name = p_name, description = p_description, lft = p_lft, rgt = p_rgt,
                        code = p_code, allow_course = p_allow_course, allow_user = p_allow_user,
                        order_priority = p_order_priority, visible = p_visible WHERE id = p_id;

                    IF nodelft <> parentlft THEN
                        CALL move_nodes(nodelft, p_lft, p_rgt);
                    END IF;
                END");

        // Gamification Tables
        Database::get()->query("CREATE TABLE `certificate_template` (
            `id` mediumint(8) not null auto_increment primary key,
            `name` varchar(255) not null,
            `description` text,
            `preview_image` varchar(255)
        )");

        Database::get()->query("CREATE TABLE `certificate` (
          `id` int(11) not null auto_increment primary key,
          `course` int(11) not null,
          `author` int(11) not null,
          `template` mediumint(8),
          `title` varchar(255) not null,
          `description` text,
          `autoassign` tinyint(1) not null default 1,
          `active` tinyint(1) not null default 1,
          `created` datetime,
          `expires` datetime,
          index `certificate_course` (`course`),
          foreign key (`course`) references `course` (`id`),
          foreign key (`author`) references `user`(`id`),
          foreign key (`template`) references `certificate_template`(`id`)
        )");

        Database::get()->query("CREATE TABLE `badge` (
          `id` int(11) not null auto_increment primary key,
          `course` int(11) not null,
          `author` int(11) not null,
          `title` varchar(255) not null,
          `description` text,
          `autoassign` tinyint(1) not null default 1,
          `active` tinyint(1) not null default 1,
          `created` datetime,
          `expires` datetime,
          index `badge_course` (`course`),
          foreign key (`course`) references `course` (`id`),
          foreign key (`author`) references `user`(`id`)
        )");

        Database::get()->query("CREATE TABLE `user_certificate` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `certificate` int(11) not null,
          `completed` boolean default false,
          `completed_criteria` int(11),
          `total_criteria` int(11),
          `updated` datetime,
          `assigned` datetime,
          unique key `user_certificate` (`user`, `certificate`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`certificate`) references `certificate` (`id`)
        )");

        Database::get()->query("CREATE TABLE `user_badge` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `badge` int(11) not null,
          `completed` boolean default false,
          `completed_criteria` int(11),
          `total_criteria` int(11),
          `updated` datetime,
          `assigned` datetime,
          unique key `user_badge` (`user`, `badge`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`badge`) references `badge` (`id`)
        )");

        Database::get()->query("CREATE TABLE `certificate_criterion` (
          `id` int(11) not null auto_increment primary key,
          `certificate` int(11) not null,
          `activity_type` varchar(255),
          `module` int(11),
          `resource` int(11),
          `threshold` decimal(7,2),
          `operator` varchar(20),
          foreign key (`certificate`) references `certificate`(`id`)
        )");

        Database::get()->query("CREATE TABLE `badge_criterion` (
          `id` int(11) not null auto_increment primary key,
          `badge` int(11) not null,
          `activity_type` varchar(255),
          `module` int(11),
          `resource` int(11),
          `threshold` decimal(7,2),
          `operator` varchar(20),
          foreign key (`badge`) references `badge`(`id`)
        )");

        Database::get()->query("CREATE TABLE `user_certificate_criterion` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `certificate_criterion` int(11) not null,
          `created` datetime,
          unique key `user_certificate_criterion` (`user`, `certificate_criterion`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`certificate_criterion`) references `certificate_criterion`(`id`)
        )");

        Database::get()->query("CREATE TABLE `user_badge_criterion` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `badge_criterion` int(11) not null,
          `created` datetime,
          unique key `user_badge_criterion` (`user`, `badge_criterion`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`badge_criterion`) references `badge_criterion`(`id`)
        )");
    }

    // update eclass version
    Database::get()->query("UPDATE config SET `value` = ?s WHERE `key`='version'", ECLASS_VERSION);

    // add new modules to courses by reinserting all modules
    Database::get()->queryFunc("SELECT id, code FROM course", function ($course) {
        global $modules;
        $modules_count = count($modules);
        $placeholders = implode(', ', array_fill(0, $modules_count, '(?d, ?d, ?d)'));
        $values = array();
        foreach($modules as $mid => $minfo) {
            $values[] = array($mid, 0, $course->id);
        }
        Database::get()->query("INSERT IGNORE INTO course_module (module_id, visible, course_id) VALUES " .
            $placeholders, $values);
    });

    set_config('upgrade_begin', '');
    updateInfo(1, $langUpgradeSuccess);

    $output_result = "<br/><div class='alert alert-success'>$langUpgradeSuccess<br/><b>$langUpgReady</b><br/><a href=\"../courses/$logfile\" target=\"_blank\">$langLogOutput</a></div><p/>";
    if ($command_line) {
        if ($debug_error) {
            echo " * $langUpgSucNotice\n";
        }
        echo $langUpgradeSuccess, "\n", $langLogOutput, ": courses/$logfile\n";
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

} // end of if not submit
