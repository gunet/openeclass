<?php

/*
 * ========================================================================
 * Open eClass 3.6 - E-learning and Course Management System
 * ========================================================================
  Copyright(c) 2003-2017  Greek Universities Network - GUnet
  A full copyright notice can be read in "/info/copyright.txt".

  Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
  Yannis Exidaridis <jexi@noc.uoa.gr>
  Alexandros Diamantidis <adia@noc.uoa.gr>

  For a full list of contributors, see "credits.txt".
 */

/**
 * @file main.lib.php
 * @brief General useful functions for eClass
 * @authors many...
 * Standard header included by all eClass files
 * Defines standard functions and validates variables
 */
define('ECLASS_VERSION', '3.6-dev');

// better performance while downloading very large files
define('PCLZIP_TEMPORARY_FILE_RATIO', 0.2);

// mPDF library temporary file path and font path 
if (isset($webDir)) { // needed for avoiding 'notices' in some files
    define("_MPDF_TEMP_PATH", $webDir . '/courses/temp/pdf/');
    define("_MPDF_TTFONTDATAPATH", $webDir . '/courses/temp/pdf/');
}

/* course status */
define('COURSE_OPEN', 2);
define('COURSE_REGISTRATION', 1);
define('COURSE_CLOSED', 0);
define('COURSE_INACTIVE', 3);

/* hierarchy node status */
define('NODE_OPEN', 2);
define('NODE_SUBSCRIBED', 1);
define('NODE_CLOSED', 0);

/* user status */
define('USER_TEACHER', 1);
define('USER_STUDENT', 5);
define('USER_GUEST', 10);
define('USER_DEPARTMENTMANAGER', 11);

// resized user image
define('IMAGESIZE_LARGE', 256);
define('IMAGESIZE_MEDIUM', 155);
define('IMAGESIZE_SMALL', 32);

// profile info access
define('ACCESS_PRIVATE', 0);
define('ACCESS_PROFS', 1);
define('ACCESS_USERS', 2);

// user admin rights
define('ADMIN_USER', 0); // admin user can do everything
define('POWER_USER', 1); // poweruser can admin only users and courses
define('USERMANAGE_USER', 2); // usermanage user can admin only users
define('DEPARTMENTMANAGE_USER', 3); // departmentmanage user can admin departments
// user email status
define('EMAIL_VERIFICATION_REQUIRED', 0);  /* email verification required. User cannot login */
define('EMAIL_VERIFIED', 1); // email is verified. User can login.
define('EMAIL_UNVERIFIED', 2); // email is unverified. User can login but cannot receive mail.
// course modules
define('MODULE_ID_AGENDA', 1);
define('MODULE_ID_LINKS', 2);
define('MODULE_ID_DOCS', 3);
define('MODULE_ID_VIDEO', 4);
define('MODULE_ID_ASSIGN', 5);
define('MODULE_ID_ANNOUNCE', 7);
define('MODULE_ID_USERS', 8);
define('MODULE_ID_FORUM', 9);
define('MODULE_ID_EXERCISE', 10);
define('MODULE_ID_COURSEINFO', 14);
define('MODULE_ID_GROUPS', 15);
define('MODULE_ID_MESSAGE', 16);
define('MODULE_ID_GLOSSARY', 17);
define('MODULE_ID_EBOOK', 18);
define('MODULE_ID_CHAT', 19);
define('MODULE_ID_DESCRIPTION', 20);
define('MODULE_ID_QUESTIONNAIRE', 21);
define('MODULE_ID_LP', 23);
define('MODULE_ID_USAGE', 24);
define('MODULE_ID_TOOLADMIN', 25);
define('MODULE_ID_WIKI', 26);
define('MODULE_ID_UNITS', 27);
define('MODULE_ID_SEARCH', 28);
define('MODULE_ID_CONTACT', 29);
define('MODULE_ID_ATTENDANCE', 30);
define('MODULE_ID_GRADEBOOK', 32);
define('MODULE_ID_TC', 34);
define('MODULE_ID_BLOG', 37);
define('MODULE_ID_COMMENTS', 38);
define('MODULE_ID_RATING', 39);
define('MODULE_ID_SHARING', 40);
define('MODULE_ID_WEEKS', 41);
define('MODULE_ID_ABUSE_REPORT', 42);
define('MODULE_ID_WALL', 46);
define('MODULE_ID_MINDMAP', 47);
define('MODULE_ID_PROGRESS', 48);

// user modules

// not used only for backward compatibility in logs
define('MODULE_ID_SETTINGS', 31); // use MODULE_ID_COURSEINFO instead !
define('MODULE_ID_NOTES', 35);
define('MODULE_ID_PERSONALCALENDAR',36);
define('MODULE_ID_ADMINCALENDAR', 43);

// Available course settings
define('SETTING_BLOG_COMMENT_ENABLE', 1);
define('SETTING_BLOG_STUDENT_POST', 2);
define('SETTING_BLOG_RATING_ENABLE', 3);
define('SETTING_BLOG_SHARING_ENABLE', 4);
define('SETTING_COURSE_SHARING_ENABLE', 5);
define('SETTING_COURSE_RATING_ENABLE', 6);
define('SETTING_COURSE_COMMENT_ENABLE', 7);
define('SETTING_COURSE_ANONYMOUS_RATING_ENABLE', 8);
define('SETTING_FORUM_RATING_ENABLE', 9);
define('SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE', 10);
define('SETTING_COURSE_ABUSE_REPORT_ENABLE', 11);
define('SETTING_GROUP_MULTIPLE_REGISTRATION', 12);
define('SETTING_GROUP_STUDENT_DESCRIPTION', 13);
define('SETTING_COURSE_USER_REQUESTS_DISABLE', 20);
define('SETTING_COURSE_FORUM_NOTIFICATIONS', 21);
define('SETTING_DOCUMENTS_PUBLIC_WRITE', 22);

// exercise answer types
define('UNIQUE_ANSWER', 1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);
define('FREE_TEXT', 6);
define('FILL_IN_BLANKS_TOLERANT', 7);

// exercise attempt types
define('ATTEMPT_ACTIVE', 0);
define('ATTEMPT_COMPLETED', 1);
define('ATTEMPT_PENDING', 2);
define('ATTEMPT_PAUSED', 3);
define('ATTEMPT_CANCELED', 4);

// for fill in blanks questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL', 2); //

// gradebook activity type
define('GRADEBOOK_ACTIVITY_ASSIGNMENT', 1);
define('GRADEBOOK_ACTIVITY_EXERCISE', 2);
define('GRADEBOOK_ACTIVITY_LP', 3);
define('GRADEBOOK_ACTIVITY_TC', 4);

// Subsystem types (used in documents)
define('MAIN', 0);
define('GROUP', 1);
define('EBOOK', 2);
define('COMMON', 3);
define('MYDOCS', 4);

// path for certificates / badges templates
define('CERT_TEMPLATE_PATH', "/courses/user_progress_data/cert_templates/");
define('BADGE_TEMPLATE_PATH', "/courses/user_progress_data/badge_templates/");

// interval in minutes for counting online users
define('MAX_IDLE_TIME', 10);

define('JQUERY_VERSION', '2.1.1');

require_once 'lib/session.class.php';

// ----------------------------------------------------------------------
// for safety reasons use the functions below
// ---------------------------------------------------------------------

// Shortcut for htmlspecialchars()
function q($s) {
    return htmlspecialchars($s, ENT_QUOTES);
}

// Escape HTML special characters and expand math tags
function q_math($s) {
    global $urlAppend;
    $text = preg_replace_callback('/\[m\].*?\[\/m\]/s', 'math_unescape', q($s));
    return mathfilter($text, 12, $urlAppend . 'courses/mathimg/');
}

// Escape string to use as JavaScript argument
function js_escape($s) {
    return q(str_replace("'", "\\'", $s));
}

function js_link($file) {
    global $urlAppend;
    $v = '?v=' . ECLASS_VERSION;
    return "<script type='text/javascript' src='{$urlAppend}js/$file$v'></script>\n";
}

function css_link($file) {
    global $urlAppend;
    $v = '?v=' . ECLASS_VERSION;
    return "<link href='{$urlAppend}js/$file$v' rel='stylesheet' type='text/css'>\n";
}

/**
 * @brief  include a JavaScript file from the main js directory
 * @global type $head_content
 * @global type $theme_settings
 * @global type $language
 * @global type $langReadMore
 * @global type $langReadLess
 * @global type $langViewHide
 * @global type $langViewShow
 * @staticvar boolean $loaded
 * @param type $file
 * @param type $init
 * @return type
 */
function load_js($file, $init='') {
    global $head_content, $theme_settings, $language,
            $langReadMore, $langReadLess, $langViewHide, $langViewShow;
    static $loaded;

    if (isset($loaded[$file])) {
        return;
    } else {
        $loaded[$file] = true;
    }

    // Load file only if not provided by template
    if (!(isset($theme_settings['js_loaded']) and
          in_array($file, $theme_settings['js_loaded']))) {
        if ($file == 'jstree') {
            $head_content .= js_link('jstree/jquery.cookie.min.js');
            $file = 'jstree/jquery.jstree.min.js';
        } elseif ($file == 'jstree3') {
            $head_content .= css_link('jstree3/themes/proton/style.min.css');
            $file = 'jstree3/jstree.min.js';
        } elseif ($file == 'jstree3d') {
            $head_content .= css_link('jstree3/themes/default/style.min.css');
            $file = 'jstree3/jstree.min.js';
        } elseif ($file == 'shadowbox') {
            $head_content .= css_link('shadowbox/shadowbox.css');
            $file = 'shadowbox/shadowbox.js';
        } elseif ($file == 'fancybox2') {
            $head_content .= css_link('fancybox2/jquery.fancybox.css');
            $file = 'fancybox2/jquery.fancybox.pack.js';
        } elseif ($file == 'colorbox') {
            $head_content .= css_link('colorbox/colorbox.css');
            $file = 'colorbox/jquery.colorbox.min.js';
        } elseif ($file == 'slick') {
            $head_content .= css_link('slick-master/slick/slick.css');
            $file = 'slick-master/slick/slick.min.js';
        } elseif ($file == 'datatables') {
            $head_content .= css_link('datatables/media/css/jquery.dataTables.css');
            $file = 'datatables/media/js/jquery.dataTables.min.js';
        } elseif ($file == 'datatables_bootstrap') {
            $head_content .= css_link('datatables/media/css/dataTables.bootstrap.css');
            $file = 'datatables/media/js/dataTables.bootstrap.js';
        } elseif ($file == 'jszip') {
            $file = 'jszip/dist/jszip.min.js';
        } elseif ($file == 'pdfmake') {
            $file = 'pdfmake/build/pdfmake.js';
        } elseif ($file == 'vfs_fonts') {
            $file = 'pdfmake/build/vfs_fonts.js';
        } elseif ($file == 'datatables_buttons') {
            $file = 'datatables/extensions/Buttons/js/dataTables.buttons.js';
            $head_content .= css_link('datatables/extensions/Buttons/css/buttons.dataTables.css');
        } elseif ($file == 'datatables_buttons_jqueryui') {
            $file = 'datatables/extensions/Buttons/js/buttons.jqueryui.js';
            $head_content .= css_link('datatables/extensions/Buttons/css/buttons.jqueryui.css');
        } elseif ($file == 'datatables_buttons_bootstrap') {
            $file = 'datatables/extensions/Buttons/js/buttons.bootstrap.js';
            $head_content .= css_link('datatables/extensions/Buttons/css/buttons.bootstrap.css');
        } elseif ($file == 'datatables_buttons_print') {
            $file = 'datatables/extensions/Buttons/js/buttons.print.js';
        } elseif ($file == 'datatables_buttons_flash') {
            $file = 'datatables/extensions/Buttons/js/buttons.flash.js';
        } elseif ($file == 'datatables_buttons_html5') {
            $file = 'datatables/extensions/Buttons/js/buttons.html5.js';
        } elseif ($file == 'datatables_buttons_colVis') {
            $file = 'datatables/extensions/Buttons/js/buttons.colVis.js';
        } elseif ($file == 'datatables_buttons_foundation') {
            $file = 'datatables/extensions/Buttons/js/buttons.foundation.js';
            $head_content .= css_link('datatables/extensions/Buttons/css/buttons.foundation.css');
        } elseif ($file == 'RateIt') {
            $file = 'jquery.rateit.min.js';
        } elseif ($file == 'autosize') {
            $file = 'autosize/autosize.min.js';
        } elseif ($file == 'waypoints-infinite') {
            $head_content .= js_link('waypoints/jquery.waypoints.min.js');
            $file = 'waypoints/shortcuts/infinite.min.js';
        } elseif ($file == 'select2') {
            $head_content .= css_link('select2-4.0.3/css/select2.min.css') .
                css_link('select2-4.0.3/css/select2-bootstrap.min.css') .
                js_link('select2-4.0.3/js/select2.full.min.js');
            $file = "select2-4.0.3/js/i18n/$language.js";
        } elseif ($file == 'bootstrap-datetimepicker') {
            $head_content .= css_link('bootstrap-datetimepicker/css/bootstrap-datetimepicker.css') .
            js_link('bootstrap-datetimepicker/js/bootstrap-datetimepicker.js');
            $file = "bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.$language.js";
        } elseif ($file == 'bootstrap-timepicker') {
            $head_content .= css_link('bootstrap-timepicker/css/bootstrap-timepicker.min.css');
            $file = 'bootstrap-timepicker/js/bootstrap-timepicker.min.js';
        } elseif ($file == 'bootstrap-datepicker') {
            $head_content .= css_link('bootstrap-datepicker/css/bootstrap-datepicker3.css') .
                js_link('bootstrap-datepicker/js/bootstrap-datepicker.js');
            $file = "bootstrap-datepicker/js/locales/bootstrap-datepicker.$language.min.js";
        } elseif ($file == 'bootstrap-validator') {
            $file = "bootstrap-validator/validator.js";
        } elseif ($file == 'bootstrap-slider') {
            $head_content .= css_link('bootstrap-slider/css/bootstrap-slider.min.css');
            $file = 'bootstrap-slider/js/bootstrap-slider.min.js';
        } elseif ($file == 'bootstrap-colorpicker') {
            $head_content .= css_link('bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css');
            $file = 'bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js';
        }   elseif ($file == 'spectrum') {
            $head_content .= css_link('spectrum/spectrum.css');
            $file = 'spectrum/spectrum.js';
        } elseif ($file == 'sortable') {
            $file = "sortable/Sortable.min.js";
        } elseif ($file == 'filetree') {
            $head_content .= css_link('jquery_filetree/jqueryFileTree.css');
            $file = 'jquery_filetree/jqueryFileTree.js';
        } elseif ($file == 'trunk8') {
            $head_content .= "
<script>
    var readMore = '".js_escape($langReadMore)."';
    var readLess = '".js_escape($langReadLess)."';
    $(function () { $('.trunk8').trunk8({
        lines: 3,
        fill: '&hellip; <a class=\"read-more\" href=\"#\">" . js_escape($langViewShow) . "</a>',
    });

    $(document).on('click', '.read-more', function (event) {
        $(this).parent().trunk8('revert').append(' <a class=\"read-less\" href=\"#\">" . js_escape($langViewHide) . "</a>');
        event.preventDefault();
    });

    $(document).on('click', '.read-less', function (event) {
console.log('aaa');
        $(this).parent().trunk8();
        event.preventDefault();
    });

});
</script>";
            $file = 'trunk8.js';
        } elseif ($file == 'clipboard.js') {
            $file = 'clipboard.js/clipboard.min.js';
        }

        $head_content .= js_link($file);
    }

    if (strlen($init) > 0) {
        $head_content .= $init;
    }
}

// Return HTML for a user - first parameter is either a user id (so that the
// user's info is fetched from the DB) or a hash with user_id, surname, givenname,
// email, or an array of user ids or user info arrays
function display_user($user, $print_email = false, $icon = true, $class = "", $code = "") {
    global $langAnonymous, $urlAppend;

    $course_code_link = "";

    if (count($user) == 0) {
        return '-';
    } elseif (is_array($user)) {
        $begin = true;
        $html = '';
        foreach ($user as $user_data) {
            if ($begin) {
                $begin = false;
            } else {
                $html .= '<br>';
            }
            if (isset($user->user_id)) {
                $html .= display_user($user_data->user_id, $print_email);
            } else {
                $html .= display_user($user_data, $print_email);
            }
        }
        return $html;
    } elseif (!is_array($user)) {
        $r = Database::get()->querySingle("SELECT id, surname, givenname, username, email, has_icon FROM user WHERE id = ?d", $user);
        if ($r) {
            $user = $r;
        } else {
            if ($icon) {
                return profile_image(0, IMAGESIZE_SMALL) . '&nbsp;' . $langAnonymous;
            } else {
                return $langAnonymous;
            }
        }
    }

    if ($print_email) {
        $email = trim($user->email);
        $print_email = $print_email && !empty($email);
    }
    if ($icon) {
        $icon = profile_image($user->id, IMAGESIZE_SMALL, 'img-circle') . '&nbsp;';
    }

    if (!empty($class)) {
        $class_str = "class='$class'";
    } else {
        $class_str = "";
    }

    $token = token_generate($user->id, true);
    $student_name = $user->surname || $user->givenname ? q($user->surname) . " " .  q($user->givenname) : $user->username;
    if (!empty($code)) {
      $course_code_link = "&amp;course=$GLOBALS[course_code]";
    }
    return "$icon<a $class_str href='{$urlAppend}main/profile/display_profile.php?id=$user->id$course_code_link&amp;token=$token'>" .
            $student_name . "</a>" .
            ($print_email ? (' (' . mailto(trim($user->email), 'e-mail address hidden') . ')') : '');
}

// Translate uid to givenname , surname, fullname or nickname
function uid_to_name($uid, $name_type = 'fullname') {
    global $langAnonymous, $langUser;
    if ($name_type == 'fullname') {
        $user = Database::get()->querySingle("SELECT CONCAT(surname, ' ', givenname) AS fullname FROM user WHERE id = ?d", $uid);
    } elseif ($name_type == 'givenname') {
        $user = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d", $uid);
    } elseif ($name_type == 'surname') {
        $user = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d", $uid);
    } elseif ($name_type == 'username') {
        $user = Database::get()->querySingle("SELECT username FROM user WHERE id = ?d", $uid);
    } else {
        $user = false;
    }
    if ($user) {
        return $user->{$name_type};
    } else {
        return "$langAnonymous $langUser";
    }
}


/**
 * @brief Translate uid to user email
 * @param type $uid
 * @return boolean
 */
function uid_to_email($uid) {

    $r = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d", $uid);
    if ($r) {
        return $r->email;
    } else {
        return false;
    }
}


/**
 * @brief Translate uid to AM (student number)
 * @param type $uid
 * @return boolean
 */
function uid_to_am($uid) {

    $r = Database::get()->querySingle("SELECT am from user WHERE id = ?d", $uid);
    if ($r) {
        return $r->am;
    } else {
        return false;
    }
}


/**
 * @brief returns group name
 * @param type $gid
 * @return boolean
 */
function gid_to_name($gid) {

    $res = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $gid);
    if ($res) {
        return $res->name;
    } else {
        return false;
    }
}

function display_group($gid) {
    global $course_code, $urlAppend, $themeimg, $langGroup;
    $res = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $gid);
    if ($res) {
        return "<span title='$langGroup' class='fa-stack fa-lg'>
                    <i style='color:#f3f3f3;' class='fa fa-circle fa-stack-2x'></i>
                    <i style='color:#cdcdcd;' class='fa fa-users fa-stack-1x'></i>
                </span>
                <a href='{$urlAppend}modules/group/group_space.php?course=$course_code&amp;group_id=$gid'>$res->name</a>";
    } else {
        return false;
    }
}
/**
 * @brief Return the URL for a user profile image
 * @param int $uid user id
 * @param int $size optional image size in pixels (IMAGESIZE_SMALL or IMAGESIZE_LARGE)
 * @return string
 */
function user_icon($uid, $size = null) {
    global $themeimg, $urlAppend;

    if (DBHelper::fieldExists("user", "id")) {
        $user = Database::get()->querySingle("SELECT has_icon FROM user WHERE id = ?d", $uid);
        if ($user) {
            if (!$size) {
                $size = IMAGESIZE_SMALL;
            }
            if ($user->has_icon) {
                return "${urlAppend}courses/userimg/${uid}_$size.jpg";
            } else {
                return "$themeimg/default_$size.png";
            }
        }
    }
    return '';
}

/**
 * @brief Display links to the groups a user is member of
 * @global type $urlAppend
 * @param type $course_id
 * @param type $user_id
 * @param type $format
 * @return string
 */
function user_groups($course_id, $user_id, $format = 'html') {
    global $urlAppend;

    $groups = '';
    $q = Database::get()->queryArray("SELECT `group`.id, `group`.name FROM `group`, group_members
                       WHERE `group`.course_id = ?d AND
                             `group`.id = group_members.group_id AND
                             `group_members`.user_id = ?d
                       ORDER BY `group`.name", $course_id, $user_id);

    if (!$q) {
        if ($format == 'html') {
            return "<div style='padding-left: 15px'>-</div>";
        } else {
            return '-';
        }
    }
    foreach ($q as $r) {
        if ($format == 'html') {
            $groups .= ((count($q) > 1) ? '<li>' : '') .
                    "<a href='{$urlAppend}modules/group/group_space.php?group_id=$r->id' title='" .
                    q($r->name) . "'>" .
                    q(ellipsize($r->name, 40)) . "</a>" .
                    ((count($q) > 1) ? '</li>' : '');
        } else {
            $groups .= (empty($groups) ? '' : ', ') . $r->name;
        }
    }
    if ($format == 'html') {
        if (count($q) > 1) {
            return "<ul class='list-unstyled'>$groups</ul>";
        } else {
            return "<div style='padding-left: 15px'>$groups</div>";
        }
    } else {
        return $groups;
    }
}

/**
 * @brief Find secret subdir of group gid
 * @param type $gid
 * @return string
 */
function group_secret($gid) {

    $r = Database::get()->querySingle("SELECT secret_directory FROM `group` WHERE id = ?d", $gid);
    if ($r) {
        return $r->secret_directory;
    } else {
        return '';
    }
}

/**
 * displays a selection box
 * @param type $entries an array of (value => label)
 * @param type $name the name of the selection element
 * @param type $default if it matches one of the values, specifies the default entry
 * @param type $extra
 * @return string
 */
function selection($entries, $name, $default = '', $extra = '') {
    $retString = "";
    $retString .= "\n<select class='form-control' name='$name' $extra>\n";
    foreach ($entries as $value => $label) {
        if (isset($default) && ($value == $default)) {
            $retString .= "<option selected value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        } else {
            $retString .= "<option value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        }
    }
    $retString .= "</select>\n";
    return $retString;
}

/**
 * displays a multi-selection box.
 * @param type $entries an array of (value => label)
 * @param type $name the name of the selection element
 * @param type $defaults array() if it matches one of the values, specifies the default entry
 * @param type $extra
 * @return string
 */
function multiselection($entries, $name, $defaults = array(), $extra = '') {
    $retString = "";
    $retString .= "\n<select name='$name' $extra>\n";
    foreach ($entries as $value => $label) {
        if (is_array($defaults) && (in_array($value, $defaults))) {
            $retString .= "<option selected value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        } else {
            $retString .= "<option value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        }
    }
    $retString .= "</select>\n";
    return $retString;
}

/* * ******************************************************************
  Show a selection box. Taken from main.lib.php
  Difference: the return value and not just echo the select box

  $entries: an array of (value => label)
  $name: the name of the selection element
  $default: if it matches one of the values, specifies the default entry
 * ********************************************************************* */

function selection3($entries, $name, $default = '') {
    $select_box = "<select name='$name'>\n";
    foreach ($entries as $value => $label) {
        if ($value == $default) {
            $select_box .= "<option selected value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        } else {
            $select_box .= "<option value='" . htmlspecialchars($value) . "'>" .
                    htmlspecialchars($label) . "</option>\n";
        }
    }
    $select_box .= "</select>\n";

    return $select_box;
}


/**
 * @brief function to check if user is a guest user
 * @global type $uid
 * @return boolean
 */
function check_guest($id = FALSE) {
    //global $uid;

    if ($id) {
        $uid = $id;
    } else {
        $uid = $GLOBALS['uid'];
    }
    if (isset($uid) and $uid) {
        if (DBHelper::fieldExists("user", "status")) {
            $status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $uid);
            if ($status && $status->status == USER_GUEST) {
                return TRUE;
            }
        }
    }
    return false;
}

/**
 * @brief function to check if user is a course editor
 * @global type $uid
 * @global type $course_id
 * @return boolean
 */
function check_editor($user_id = null, $cid = null) {
    global $uid, $course_id, $is_admin;

    if (is_null($user_id) and isset($uid)) {
        $user_id = $uid;
    }
    if (!$user_id) {
        return false;
    }
    if ($is_admin) {
        return true;
    }
    if (!isset($cid) and isset($course_id)) {
        $cid = $course_id;
    }
    if (isset($uid) and $uid and isset($cid)) {
        $s = Database::get()->querySingle("SELECT status, editor FROM course_user
                                        WHERE user_id = ?d AND
                                        course_id = ?d", $user_id, $cid);
        if ($s and ($s->status == USER_TEACHER or $s->editor == 1)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * function to check if user is a course opencourses reviewer
 */
function check_opencourses_reviewer() {
    global $uid, $course_id, $course_code, $is_power_user;

    if (isset($uid) and $uid) {
        if ($is_power_user) {
            return true;
        }
        if ($_SESSION['courses'][$course_code] === USER_DEPARTMENTMANAGER) {
            return true;
        }
        $r = Database::get()->querySingle("SELECT reviewer FROM course_user
                                    WHERE user_id = ?d
                                    AND course_id = ?d", $uid, $course_id);
        if ($r) {
            if ($r->reviewer == 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}

/**
 * @brief just make sure that the $uid variable isn't faked
 * @global type $urlServer
 * @global type $require_valid_uid
 * @global type $uid
 */
function check_uid() {

    global $urlServer, $require_valid_uid, $uid;

    if (isset($_SESSION['uid'])) {
        $uid = $_SESSION['uid'];
    } else {
        unset($uid);
    }

    if ($require_valid_uid and !isset($uid)) {
        header("Location: $urlServer");
        exit;
    }
}


/**
 * @brief Check if a user with username $login already exists
 * @param type $login
 * @return boolean
 */
function user_exists($login) {

    if (get_config('case_insensitive_usernames')) {
        $qry = "COLLATE utf8_general_ci = ?s";
    } else {
        $qry = "COLLATE utf8_bin = ?s";
    }
    $username_check = Database::get()->querySingle("SELECT id FROM user WHERE username $qry", $login);
    if ($username_check) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief checks if a user is inactive
 * @param type $uid
 * @return boolean
 */
function is_inactive_user($uid) {
    
    $qry = Database::get()->querySingle("SELECT * FROM user 
                            WHERE id = ?d 
                        AND expires_at < " . DBHelper::timeAfter() . "", $uid);
    if ($qry) {
        return true;        
    } else {
        return false;
    }
}

/**
 * @brief Check if a user with username $login already applied for account
 * @param type $login
 * @return boolean
 */
function user_app_exists($login) {

    if (get_config('case_insensitive_usernames')) {
        $qry = "COLLATE utf8_general_ci = ?s";
    } else {
        $qry = "COLLATE utf8_bin = ?s";
    }
    $username_check = Database::get()->querySingle("SELECT id FROM user_request WHERE state = 1 AND username $qry", $login);
    if ($username_check) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief Convert HTML to plain text
 * @param type $string
 * @return type
 */
function html2text($string) {
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    $string = html_entity_decode(strip_tags($string));
    $text = preg_replace('/<(div|p|pre|br)[^>]*>/i', "\n", $string);
    return canonicalize_whitespace(strip_tags($text));
    // return strtr (strip_tags($string), $trans_tbl);
}

/*
  // IMAP authentication functions                                        |
 */

function imap_auth($server, $username, $password) {
    $auth = false;
    $fp = fsockopen($server, 143, $errno, $errstr, 10);
    if ($fp) {
        fputs($fp, "A1 LOGIN " . imap_literal($username) .
                " " . imap_literal($password) . "\r\n");
        fputs($fp, "A2 LOGOUT\r\n");
        while (!feof($fp)) {
            $line = fgets($fp, 200);
            if (substr($line, 0, 5) == 'A1 OK') {
                $auth = true;
            }
        }
        fclose($fp);
    }
    return $auth;
}

function imap_literal($s) {
    return "{" . strlen($s) . "}\r\n$s";
}

/**
 * @brief Returns next available code for a new course in faculty with id $fac
 * @param type $fac
 * @return string
 */
function new_code($fac) {

 $gencode = Database::get()->querySingle("SELECT code, MAX(generator) AS generator
       FROM hierarchy WHERE code = (SELECT code FROM hierarchy WHERE id = ?d) GROUP BY code", $fac);
   if ($gencode) {
        do {
            $code = $gencode->code . $gencode->generator;
            $gencode->generator += 1;
			$code = $gencode->code . $gencode->generator;
            Database::get()->query("UPDATE hierarchy SET generator = ?d WHERE id = ?d", $gencode->generator, $fac);
        } while (file_exists("courses/" . $code));
		Database::get()->query("UPDATE hierarchy SET generator = ?d WHERE id = ?d", $gencode->generator, $fac);

    // Make sure the code returned isn't empty!
    } else {
        die("Course Code is empty!");
    }
    return $code;
}

// due to a bug (?) to php function basename() our implementation
// handles correct multibyte characters (e.g. greek)
function my_basename($path) {
    return preg_replace('#^.*/#', '', $path);
}

/* transform the date format from "year-month-day" to "day-month-year"
 * if argument time is defined then
 * transform date time format from "year-month-day time" to "to "day-month-year time"
 */

function greek_format($date, $time = FALSE, $dont_display_time = FALSE) {
    if ($time) {
        $datetime = explode(' ', $date);
        $new_date = implode('-', array_reverse(explode('-', $datetime[0])));
        if ($dont_display_time) {
            return $new_date;
        } else {
            return $new_date . " " . $datetime[1];
        }
    } else {
        return implode('-', array_reverse(explode('-', $date)));
    }
}

/**
 * @brief format the date according to language
 * @param type $date
 * @param type $time
 * @param type $dont_display_time
 * @return type
 */
function nice_format($date, $time = FALSE, $dont_display_time = FALSE) {
    if ($GLOBALS['language'] == 'el') {
        return greek_format($date, $time, $dont_display_time);
    } else {
        return $date;
    }
}

/**
 * @brief remove seoconds from a given datetime
 * @param type $datetime
 * @return datetime without seconds
 */
function datetime_remove_seconds($datetime) {
    return preg_replace('/:\d\d$/', '', $datetime);
}

// Returns user's previous login date, or today's date if no previous login
function last_login($uid) {

    $last_login = Database::get()->querySingle("SELECT DATE_FORMAT(MAX(`when`), '%Y-%m-%d') AS last_login FROM loginout
                          WHERE id_user = ?d AND action = 'LOGIN'", $uid)->last_login;
    if (!$last_login) {
        $last_login = date('Y-m-d');
    }
    return $last_login;
}

// Create a JavaScript-escaped mailto: link
function mailto($address, $alternative = '(e-mail address hidden)') {
    if (empty($address)) {
        return '&nbsp;';
    } else {
        $prog = urlenc("var a='" . urlenc(str_replace('@', '&#64;', $address)) .
                "';document.write('<a href=\"mailto:'+unescape(a)+'\">'+unescape(a)+'</a>');");
        return "<script type='text/javascript'>eval(unescape('" .
                q($prog) . "'));</script><noscript>" . q($alternative) . "</noscript>";
    }
}

function urlenc($string) {
    $out = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $out .= sprintf("%%%02x", ord(substr($string, $i, 1)));
    }
    return $out;
}

/**
 * get user data
 * @param type $user_id
 * @return object
 */
function user_get_data($user_id) {

    $data = Database::get()->querySingle("SELECT id, surname, givenname, username, email, phone, status
                                            FROM user WHERE id = ?d", $user_id);

    if ($data) {
        return $data;
    } else {
        return null;
    }
}

/**
 * Function for generating fixed-length strings containing random characters.
 *
 * @param int $length
 * @return string
 */
function randomkeys($length) {
    $key = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for ($i = 0; $i < $length; $i++) {
        $key .= $codeAlphabet[crypto_rand_secure(0, strlen($codeAlphabet) - 1)];
    }
    return $key;
}

// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize($kbytes, $dec_places = 2) {
    global $text;

    if ($kbytes > 1048576) {
        $result = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
        $result .= '&nbsp;Gb';
    } elseif ($kbytes > 1024) {
        $result = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
        $result .= '&nbsp;Mb';
    } else {
        $result = sprintf('%.' . $dec_places . 'f', $kbytes);
        $result .= '&nbsp;Kb';
    }
    return $result;
}

/*
 * Checks if Javascript is enabled on the client browser
 * A cookie is set on the header by javascript code.
 * If this cookie isn't set, it means javascript isn't enabled.
 *
 * return boolean enabling state of javascript
 * author Hugues Peeters <hugues.peeters@claroline.net>
 */

function is_javascript_enabled() {
    return isset($_COOKIE['javascriptEnabled']) and $_COOKIE['javascriptEnabled'];
}

function add_check_if_javascript_enabled_js() {
    return '<script type="text/javascript">document.cookie="javascriptEnabled=true";</script>';
}

// Check if we can display activation link (e.g. module_id is one of our modules)
// Link is displayed only on main page of each module
function display_activation_link($module_id) {
    global $modules;

    $script = preg_replace('|.*/|', '', $_SERVER['SCRIPT_NAME']);
    if (!defined('STATIC_MODULE') and $module_id and array_key_exists($module_id, $modules) and $script == 'index.php' and count($_GET) == 1 and isset($_GET['course']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief checks if a module is visible
 * @global type $course_id
 * @param type $module_id
 * @return boolean
 */
function visible_module($module_id) {
    global $course_id;

    $v = Database::get()->querySingle("SELECT visible FROM course_module
                                WHERE module_id = ?d AND
                                course_id = ?d", $module_id, $course_id)->visible;
    if ($v == 1) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief check if a module is disabled
 * @param type $module_id
 * @return boolean
 */
function is_module_disable($module_id) {

    $q = Database::get()->querySingle("SELECT * FROM module_disable WHERE module_id = ?d", $module_id);
    if ($q) {
        return true;
    } else {
        return false;
    }
}

// Find the current module id from the script URL
function current_module_id() {
    global $modules, $urlAppend, $static_modules;
    static $module_id;

    if (isset($module_id)) {
        return $module_id;
    }

    $module_path = str_replace($urlAppend . 'modules/', '', $_SERVER['SCRIPT_NAME']);
    $link = preg_replace('|/.*$|', '', $module_path);
    foreach ($static_modules as $smid => $info) {
        if ($info['link'] == $link) {
            $module_id = $smid;
            define('STATIC_MODULE', true);
            return $module_id;
        }
    }

    foreach ($modules as $mid => $info) {
        if ($info['link'] == $link) {
            $module_id = $mid;
            return $mid;
        }
    }
    return false;
}

// Returns true if a string is invalid UTF-8
function invalid_utf8($s) {
    return !mb_detect_encoding($s, 'UTF-8', true);
}

// Remove invalid bytes from UTF-8 string
function sanitize_utf8($s) {
    return mb_convert_encoding($s, 'UTF-8', 'UTF-8');
}

function utf8_to_cp1253($s) {
    // First try with iconv() directly
    $cp1253 = @iconv('UTF-8', 'Windows-1253', $s);
    if ($cp1253 === false) {
        // ... if it fails, fall back to indirect conversion
        $cp1253 = str_replace("\xB6", "\xA2", @iconv('UTF-8', 'ISO-8859-7', $s));
    }
    return $cp1253;
}

// Converts a string from Code Page 737 (DOS Greek) to UTF-8
function cp737_to_utf8($s) {
    // First try with iconv()...
    $cp737 = @iconv('CP737', 'UTF-8', $s);
    if ($cp737 !== false) {
        return $cp737;
    } else {
        // ... if it fails, fall back to manual conversion
        return strtr($s, array("\x80" => 'Α', "\x81" => 'Β', "\x82" => 'Γ', "\x83" => 'Δ',
                               "\x84" => 'Ε', "\x85" => 'Ζ', "\x86" => 'Η', "\x87" => 'Θ',
                               "\x88" => 'Ι', "\x89" => 'Κ', "\x8a" => 'Λ', "\x8b" => 'Μ',
                               "\x8c" => 'Ν', "\x8d" => 'Ξ', "\x8e" => 'Ο', "\x8f" => 'Π',
                               "\x90" => 'Ρ', "\x91" => 'Σ', "\x92" => 'Τ', "\x93" => 'Υ',
                               "\x94" => 'Φ', "\x95" => 'Χ', "\x96" => 'Ψ', "\x97" => 'Ω',
                               "\x98" => 'α', "\x99" => 'β', "\x9a" => 'γ', "\x9b" => 'δ',
                               "\x9c" => 'ε', "\x9d" => 'ζ', "\x9e" => 'η', "\x9f" => 'θ',
                               "\xa0" => 'ι', "\xa1" => 'κ', "\xa2" => 'λ', "\xa3" => 'μ',
                               "\xa4" => 'ν', "\xa5" => 'ξ', "\xa6" => 'ο', "\xa7" => 'π',
                               "\xa8" => 'ρ', "\xa9" => 'σ', "\xaa" => 'ς', "\xab" => 'τ',
                               "\xac" => 'υ', "\xad" => 'φ', "\xae" => 'χ', "\xaf" => 'ψ',
                               "\xb0" => '░', "\xb1" => '▒', "\xb2" => '▓', "\xb3" => '│',
                               "\xb4" => '┤', "\xb5" => '╡', "\xb6" => '╢', "\xb7" => '╖',
                               "\xb8" => '╕', "\xb9" => '╣', "\xba" => '║', "\xbb" => '╗',
                               "\xbc" => '╝', "\xbd" => '╜', "\xbe" => '╛', "\xbf" => '┐',
                               "\xc0" => '└', "\xc1" => '┴', "\xc2" => '┬', "\xc3" => '├',
                               "\xc4" => '─', "\xc5" => '┼', "\xc6" => '╞', "\xc7" => '╟',
                               "\xc8" => '╚', "\xc9" => '╔', "\xca" => '╩', "\xcb" => '╦',
                               "\xcc" => '╠', "\xcd" => '═', "\xce" => '╬', "\xcf" => '╧',
                               "\xd0" => '╨', "\xd1" => '╤', "\xd2" => '╥', "\xd3" => '╙',
                               "\xd4" => '╘', "\xd5" => '╒', "\xd6" => '╓', "\xd7" => '╫',
                               "\xd8" => '╪', "\xd9" => '┘', "\xda" => '┌', "\xdb" => '█',
                               "\xdc" => '▄', "\xdd" => '▌', "\xde" => '▐', "\xdf" => '▀',
                               "\xe0" => 'ω', "\xe1" => 'ά', "\xe2" => 'έ', "\xe3" => 'ή',
                               "\xe4" => 'ϊ', "\xe5" => 'ί', "\xe6" => 'ό', "\xe7" => 'ύ',
                               "\xe8" => 'ϋ', "\xe9" => 'ώ', "\xea" => 'Ά', "\xeb" => 'Έ',
                               "\xec" => 'Ή', "\xed" => 'Ί', "\xee" => 'Ό', "\xef" => 'Ύ',
                               "\xf0" => 'Ώ', "\xf1" => '±', "\xf2" => '≥', "\xf3" => '≤',
                               "\xf4" => 'Ϊ', "\xf5" => 'Ϋ', "\xf6" => '÷', "\xf7" => '≈',
                               "\xf8" => '°', "\xf9" => '∙', "\xfa" => '·', "\xfb" => '√',
                               "\xfc" => 'ⁿ', "\xfd" => '²', "\xfe" => '■', "\xff" => ' '));
    }
}

/**
 * Return a new random filename, with the given extension
 * @param type $extension
 * @return string
 */
function safe_filename($extension = '') {
    $prefix = sprintf('%08x', time()) . randomkeys(4);
    if (empty($extension)) {
        return $prefix;
    } else {
        return $prefix . '.' . $extension;
    }
}

function get_file_extension($filename) {
    $matches = array();
    if (preg_match('/\.(tar\.(z|gz|bz|bz2))$/i', $filename, $matches)) {
        return strtolower($matches[1]);
    } elseif (preg_match('/\.([a-zA-Z0-9_-]{1,8})$/i', $filename, $matches)) {
        return strtolower($matches[1]);
    } else {
        return '';
    }
}

// Remove whitespace from start and end of string, convert
// sequences of whitespace characters to single spaces
// and remove non-printable characters, while preserving new lines
function canonicalize_whitespace($s) {
    return str_replace(array(" \1 ", " \1", "\1 ", "\1"), "\n", preg_replace('/[\t ]+/', ' ', str_replace(array("\r\n", "\n", "\r"), "\1", trim(preg_replace('/[\x00-\x08\x0C\x0E-\x1F\x7F]/', '', $s)))));
}

// Remove characters which can't appear in filenames
function remove_filename_unsafe_chars($s) {
    return preg_replace('/[<>:"\/\\\\|?*]/', '', canonicalize_whitespace($s));
}

/**
 * @brief check recourse accessibility
 * @global type $course_code
 * @param type $public
 * @return boolean
 */
function resource_access($visible, $public) {
    global $course_code;
    if ($visible) {
        if ($public) {
            return TRUE;
        } else {
            if (isset($_SESSION['uid']) and (isset($_SESSION['courses'][$course_code]) and $_SESSION['courses'][$course_code])) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    } else {
        return FALSE;
    }
}

/**
 * @brief check if a specific resource belongs to certificate / badge
 * @global type $course_id
 * @param type $module
 * @param type $resource_id
 * @return boolean
 */
function resource_belongs_to_progress_data($module, $resource_id) {
         
    global $course_id;
    
    // check if module belongs to certificate
    $sql = Database::get()->querySingle("SELECT * FROM certificate_criterion JOIN certificate "
                                            . "ON certificate.id = certificate_criterion.certificate "
                                            . "WHERE course_id = ?d AND module = ?d AND resource = ?d",
                                        $course_id, $module, $resource_id);
    if ($sql) {
        return true;
    }
    // check if module belongs to badge
    $sql2 = Database::get()->querySingle("SELECT * FROM badge_criterion JOIN badge "
                                            . "ON badge.id = badge_criterion.badge "
                                            . "WHERE course_id = ?d AND module = ?d AND resource = ?d", 
                                        $course_id, $module, $resource_id);
    if ($sql2) {
        return true;
    }
    
    return false;
}

# Only languages defined below are available for selection in the UI
# If you add any new languages, make sure they are defined in the
# next array as well
$native_language_names_init = array(
    'el' => 'Ελληνικά',
    'en' => 'English',
    'es' => 'Español',
    'cs' => 'Česky',
    'sq' => 'Shqip',
    'bg' => 'Български',
    'ca' => 'Català',
    'da' => 'Dansk',
    'nl' => 'Nederlands',
    'fi' => 'Suomi',
    'fr' => 'Français',
    'de' => 'Deutsch',
    'is' => 'Íslenska',
    'it' => 'Italiano',
    'jp' => '日本語',
    'pl' => 'Polski',
    'ru' => 'Русский',
    'tr' => 'Türkçe',
    'sv' => 'Svenska',
    'sl' => 'Slovenščina',
    'sk' => 'Slovenčina',
    'hr' => 'Hrvatski',
    'pt' => 'Português',
    'xx' => 'Variable Names',
);

$language_codes = array(
    'el' => 'greek',
    'en' => 'english',
    'es' => 'spanish',
    'cs' => 'czech',
    'sq' => 'albanian',
    'bg' => 'bulgarian',
    'ca' => 'catalan',
    'da' => 'danish',
    'nl' => 'dutch',
    'fi' => 'finnish',
    'fr' => 'french',
    'de' => 'german',
    'is' => 'icelandic',
    'it' => 'italian',
    'jp' => 'japanese',
    'pl' => 'polish',
    'ru' => 'russian',
    'tr' => 'turkish',
    'sv' => 'swedish',
    'sl' => 'slovene',
    'sk' => 'slovak',
    'hr' => 'croatian',
    'pt' => 'portuguese',
    'xx' => 'variables',
);

// Html for course access icons
global $langPublic, $langPrivOpen, $langClosedCourseShort, $langCourseInactiveShort;

$course_access_icons = array(
    COURSE_OPEN => "<div class='course_status_container'><span class='fa fa-unlock fa-fw access' data-toggle='tooltip' data-placement='top' title='$langPublic'></span><span class='sr-only'>.</span></div>",
    COURSE_REGISTRATION => "<div class='course_status_container'><span class='fa fa-lock fa-fw access' data-toggle='tooltip' data-placement='top' title='$langPrivOpen'>
                                <span class='fa fa-pencil text-danger fa-custom-lock'></span>
                            </span><span class='sr-only'>$langPrivOpen</span></div>",
    COURSE_CLOSED => "<div class='course_status_container'><span class='fa fa-lock fa-fw access' data-toggle='tooltip' data-placement='top' title='$langClosedCourseShort'></span><span class='sr-only'>$langClosedCourseShort</span></div>",
    COURSE_INACTIVE => "<div class='course_status_container'><span class='fa fa-lock fa-fw access' data-toggle='tooltip' data-placement='top' title='$langCourseInactiveShort'>
                                <span class='fa fa-times text-danger fa-custom-lock'></span>
                             </span><span class='sr-only'>$langCourseInactiveShort</span></div>"
);

// Convert language code to language name in English lowercase (for message files etc.)
// Returns 'english' if code is not in array
function langcode_to_name($langcode) {
    global $language_codes;
    if (isset($language_codes[$langcode])) {
        return $language_codes[$langcode];
    } else {
        return 'english';
    }
}

// Convert language name to language code
function langname_to_code($langname) {
    global $language_codes;
    $langcode = array_search($langname, $language_codes);
    if ($langcode) {
        return $langcode;
    } else {
        return 'en';
    }
}

function append_units($amount, $singular, $plural) {
    if ($amount == 1) {
        return $amount . ' ' . $singular;
    } else {
        return $amount . ' ' . $plural;
    }
}

// Convert $sec to days, hours, minutes, seconds;
function format_time_duration($sec, $hourLimit = 24) {
    global $langsecond, $langseconds, $langminute, $langminutes, $langhour, $langhours, $langDay, $langDays;

    if ($sec < 60) {
        return append_units($sec, $langsecond, $langseconds);
    }
    $min = floor($sec / 60);
    $sec = $sec % 60;
    if ($min < 2) {
        return append_units($min, $langminute, $langminutes) .
                (($sec == 0) ? '' : (' ' . append_units($sec, $langsecond, $langseconds)));
    }
    if ($min < 60) {
        return append_units($min, $langminute, $langminutes);
    }
    $hour = floor($min / 60);
    $min = $min % 60;
    if ($hour < $hourLimit) {
        if ($hour > 24) {
            $min = 0;
        }
        return append_units($hour, $langhour, $langhours) .
                (($min == 0) ? '' : (' ' . append_units($min, $langminute, $langminutes)));
    }
    $day = floor($hour / 24);
    $hour = $hour % 24;
    return (($day == 0) ? '' : (' ' . append_units($day, $langDay, $langDays))) .
            (($hour == 0) ? '' : (' ' . append_units($hour, $langhour, $langhours))) .
            (($min == 0) ? '' : (' ' . append_units($min, $langminute, $langminutes)));
}

// Move entry $id in $table to $direction 'up' or 'down', where
// order is in field $order_field and id in $id_field
// Use $condition as extra SQL to limit the operation
function move_order($table, $id_field, $id, $order_field, $direction, $condition = '') {
    if ($condition) {
        $condition = ' AND ' . $condition;
    }
    if ($direction == 'down') {
        $op = '>';
        $desc = '';
    } else {
        $op = '<';
        $desc = 'DESC';
    }

    $sql = Database::get()->querySingle("SELECT `$order_field` FROM `$table`
                         WHERE `$id_field` = ?d", $id);
    if (!$sql) {
        return false;
    }
    $current = $sql->$order_field;
    $sql = Database::get()->querySingle("SELECT `$id_field`, `$order_field` FROM `$table`
                        WHERE `$order_field` $op '$current' $condition
                        ORDER BY `$order_field` $desc LIMIT 1");
    if ($sql) {
        $next_id = $sql->$id_field;
        $next = $sql->$order_field;
        Database::get()->query("UPDATE `$table` SET `$order_field` = $next
                          WHERE `$id_field` = $id");
        Database::get()->query("UPDATE `$table` SET `$order_field` = $current
                          WHERE `$id_field` = $next_id");
        return true;
    }
    return false;
}

// Handle reordering of a table (from AJAX drag-and-drop) by
// updating the `order` field (or $orderField if set) in table $table
// Limit update to records with value $limitValue in `$limitField`
function reorder_table($table, $limitField, $limitValue, $toReorder, $prevReorder = null, $idField = 'id', $orderField = 'order') {
    Database::get()->transaction(function ()
            use ($table, $limitField, $limitValue, $toReorder, $prevReorder, $idField, $orderField) {
        if ($limitField) {
            $where = "WHERE `$limitField` = ?d";
        } else {
            $where = '';
            $limitValue = array();
        }
        $max = Database::get()->querySingle("SELECT MAX(`$orderField`) AS max_order
            FROM `$table` $where", $limitValue)->max_order;

        if ($where) {
            $where .= ' AND';
        } else {
            $where = 'WHERE';
        }

        if (!is_null($prevReorder)) {
            $prevRank = Database::get()->querySingle("SELECT `$orderField` AS rank
                FROM `$table` WHERE `$idField` = ?d", $prevReorder)->rank;
        } else {
            $prevRank = 0;
        }

        Database::get()->query("UPDATE `$table`
            SET `$orderField` = `$orderField` + ?d + 1
            $where `$orderField` > ?d", $max, $limitValue, $prevRank);
        Database::get()->query("UPDATE `$table`
            SET `$orderField` = ?d
            $where `$idField` = ?d", $prevRank + 1, $limitValue, $toReorder);
        Database::get()->query("UPDATE `$table`
            SET `$orderField` = `$orderField` - ?d
            $where `$orderField` > ?d", $max, $limitValue, $prevRank + 1);
    });
}

// Add a link to the appropriate course unit if the page was requested
// with a unit=ID parametere. This happens if the user got to the module
// page from a unit resource link. If entry_page == true this is the initial page of module
// and is assumed that you're exiting the current unit unless $_GET['unit'] is set
function add_units_navigation($entry_page = false) {
    global $navigation, $course_id, $is_editor, $course_code;

    if ($entry_page and !isset($_GET['unit'])) {
        unset($_SESSION['unit']);
        return false;
    } elseif (isset($_GET['unit']) or isset($_SESSION['unit'])) {
        if ($is_editor) {
            $visibility_check = '';
        } else {
            $visibility_check = "AND visible = 1";
        }
        if (isset($_GET['unit'])) {
            $unit_id = intval($_GET['unit']);
        } elseif (isset($_SESSION['unit'])) {
            $unit_id = intval($_SESSION['unit']);
        }

        $q = Database::get()->querySingle("SELECT title FROM course_units
                       WHERE id = $unit_id AND course_id = ?d $visibility_check", $course_id);
        if ($q) {
            $unit_name = $q->title;
            $navigation[] = array('url' => "../units/index.php?course=$course_code&amp;id=$unit_id", 'name' => $unit_name);
        }
        return true;
    } else {
        return false;
    }
}

// Cut a string to be no more than $maxlen characters long, appending
// the $postfix (default: ellipsis "...") if so
function ellipsize($string, $maxlen, $postfix = '...') {
    if (mb_strlen($string, 'UTF-8') > $maxlen) {
        return trim(mb_substr($string, 0, $maxlen, 'UTF-8')) . $postfix;
    } else {
        return $string;
    }
}

/*
 * Cut a string to be no more than $maxlen characters long, appending
 * the $postfix (default: ellipsis "...") if so respecting html tags
 */

function ellipsize_html($string, $maxlen, $postfix = '&hellip;') {
    $output = new HtmlCutString($string, $maxlen, $postfix);
    return $output->cut();
}

/**
 * @brief Find the title of a course from its code
 * @param type $code
 * @return boolean
 */
function course_code_to_title($code) {
    $r = Database::get()->querySingle("SELECT title FROM course WHERE code = ?s", $code);
    if ($r) {
        return $r->title;
    } else {
        return false;
    }
}

/**
 * @brief Find the course id of a course from its code
 * @param type $code
 * @return boolean
 */
function course_code_to_id($code) {
    $r = Database::get()->querySingle("SELECT id FROM course WHERE code = ?s", $code);
    if ($r) {
           return $r->id;
    } else {
        return false;
    }
}


/**
 * @brief Find the title of a course from its id
 * @param type $cid
 * @return boolean
 */
function course_id_to_title($cid) {
    $r = Database::get()->querySingle("SELECT title FROM course WHERE id = ?d", $cid);
    if ($r) {
        return $r->title;
    } else {
        return false;
    }
}

/**
 * @brief Find the course code from its id
 * @param type $cid
 * @return boolean
 */
function course_id_to_code($cid) {
    $r = Database::get()->querySingle("SELECT code FROM course WHERE id = ?d", $cid );
    if ($r) {
        return $r->code;
    } else {
        return false;
    }
}


/**
 * @brief Find the public course code from its id
 * @param type $cid
 * @return boolean
 */
function course_id_to_public_code($cid) {
    $r = Database::get()->querySingle("SELECT public_code FROM course WHERE id = ?d", $cid);
    if ($r) {
        return $r->public_code;
    } else {
        return false;
    }
}

/**
 * @global type $webDir
 * @param type $cid
 * @brief Delete course with id = $cid
 */
function delete_course($cid) {
    global $webDir;

    if (!isset($webDir) or empty($webDir)) { // security
        return;
    }
    $course_code = course_id_to_code($cid);
    
    
    Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion IN
                            (SELECT id FROM badge_criterion WHERE badge IN
                            (SELECT id FROM badge WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM badge_criterion WHERE badge IN 
                            (SELECT id FROM badge WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM user_badge WHERE badge IN 
                            (SELECT id FROM badge WHERE course_id = ?d)", $cid);    
    Database::get()->query("DELETE FROM badge WHERE course_id = ?d", $cid);
    
    Database::get()->query("DELETE FROM user_certificate_criterion WHERE certificate_criterion IN 
                            (SELECT id FROM certificate_criterion WHERE certificate IN 
                            (SELECT id FROM certificate WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM certificate_criterion WHERE certificate IN 
                            (SELECT id FROM certificate WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM user_certificate WHERE certificate IN 
                             (SELECT id FROM certificate WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM certificate WHERE course_id = ?d", $cid);
               
    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM document WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM ebook_subsection WHERE section_id IN
                         (SELECT ebook_section.id FROM ebook_section, ebook
                                 WHERE ebook_section.ebook_id = ebook.id AND
                                       ebook.course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM ebook_section WHERE id IN
                         (SELECT id FROM ebook WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM ebook WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `forum_post` ON `rating`.`rid` = `forum_post`.`id` INNER JOIN `forum_topic`
                            ON `forum_post`.`topic_id` = `forum_topic`.`id` INNER JOIN `forum` ON `forum`.`id` = `forum_topic`.`forum_id`
                            WHERE `rating`.`rtype` = ?s AND `forum`.`course_id` = ?d", 'forum_post', $cid);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `forum_post` ON `rating_cache`.`rid` = `forum_post`.`id` INNER JOIN `forum_topic`
                            ON `forum_post`.`topic_id` = `forum_topic`.`id` INNER JOIN `forum` ON `forum`.`id` = `forum_topic`.`forum_id`
                            WHERE `rating_cache`.`rtype` = ?s AND `forum`.`course_id` = ?d", 'forum_post', $cid);
    Database::get()->query("DELETE FROM forum_notify WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE forum_post FROM forum_post INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                            INNER JOIN forum ON forum_topic.forum_id = forum.id
                            WHERE forum.course_id = ?d", $cid);
    Database::get()->query("DELETE forum_topic FROM forum_topic INNER JOIN forum ON forum_topic.forum_id = forum.id
                            WHERE forum.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM forum_category WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM forum WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM forum_user_stats WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM glossary WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM group_members WHERE group_id IN
                         (SELECT id FROM `group` WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM group_properties WHERE group_id IN
                         (SELECT id FROM `group` WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM `group` WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM `group_category` WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `link` ON `rating`.`rid` = `link`.`id`
                            WHERE `rating`.`rtype` = ?s AND `link`.`course_id` = ?d", 'link', $cid);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `link` ON `rating_cache`.`rid` = `link`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `link`.`course_id` = ?d", 'link', $cid);
    Database::get()->query("DELETE FROM link WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM link_category WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM agenda WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_review WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM unit_resources WHERE unit_id IN
                         (SELECT id FROM course_units WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM course_units WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM abuse_report WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_weekly_view_activities WHERE course_weekly_view_id IN
                                (SELECT id FROM course_weekly_view WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM course_weekly_view WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE `comments` FROM `comments` INNER JOIN `blog_post` ON `comments`.`rid` = `blog_post`.`id`
                            WHERE `comments`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $cid);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `blog_post` ON `rating`.`rid` = `blog_post`.`id`
                            WHERE `rating`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $cid);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `blog_post` ON `rating_cache`.`rid` = `blog_post`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $cid);
    Database::get()->query("DELETE FROM `rating` WHERE `rtype` = ?s AND `rid` = ?d", 'course', $cid);
    Database::get()->query("DELETE FROM `rating_cache` WHERE `rtype` = ?s AND `rid` = ?d", 'course', $cid);
    Database::get()->query("DELETE FROM `blog_post` WHERE `course_id` = ?d", $cid);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `wall_post` ON `rating`.`rid` = `wall_post`.`id`
                            WHERE `rating`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $cid);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `wall_post` ON `rating_cache`.`rid` = `wall_post`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $cid);
    Database::get()->query("DELETE `comments` FROM `comments` INNER JOIN `wall_post` ON `comments`.`rid` = `wall_post`.`id`
                            WHERE `comments`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $cid);
    Database::get()->query("DELETE `wall_post_resources` FROM `wall_post_resources` INNER JOIN `wall_post` ON `wall_post_resources`.`post_id` = `wall_post`.`id`
                            WHERE `wall_post`.`course_id` = ?d", $cid);
    Database::get()->query("DELETE FROM `wall_post` WHERE `course_id` = ?d", $cid);
    // check if we have guest account. If yes delete it.
    $guest_user = Database::get()->querySingle("SELECT user_id FROM course_user WHERE course_id = ?d AND status = ?d", $cid, USER_GUEST);
    if ($guest_user) {
        deleteUser($guest_user->user_id, true);
    }
    Database::get()->query("DELETE FROM course_user WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_department WHERE course = ?d", $cid);
    Database::get()->query("DELETE FROM course WHERE id = ?d", $cid);
    Database::get()->query("DELETE FROM video WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM videolink WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM dropbox_attachment WHERE msg_id IN (SELECT id FROM dropbox_msg WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM dropbox_index WHERE msg_id IN (SELECT id FROM dropbox_msg WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM dropbox_msg WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM lp_asset WHERE module_id IN (SELECT module_id FROM lp_module WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM lp_rel_learnPath_module WHERE learnPath_id IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM lp_user_module_progress WHERE learnPath_id IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM lp_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM lp_learnPath WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM wiki_pages_content WHERE pid IN (SELECT id FROM wiki_pages WHERE wiki_id IN (SELECT id FROM wiki_properties WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM wiki_pages WHERE wiki_id IN (SELECT id FROM wiki_properties WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM wiki_acls WHERE wiki_id IN (SELECT id FROM wiki_properties WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM wiki_properties WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN (SELECT pqid FROM poll_question WHERE pid IN (SELECT pid FROM poll WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM poll_answer_record WHERE poll_user_record_id IN (SELECT id FROM poll_user_record WHERE pid IN (SELECT pid FROM poll WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM poll_user_record WHERE pid IN (SELECT pid FROM poll WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM poll_question WHERE pid IN (SELECT pid FROM poll WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM poll WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id IN (SELECT id FROM assignment WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id IN (SELECT id FROM assignment WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM assignment WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise_with_questions WHERE question_id IN (SELECT id FROM exercise_question WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM exercise_with_questions WHERE exercise_id IN (SELECT id FROM exercise WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM exercise_answer WHERE question_id IN (SELECT id FROM exercise_question WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM exercise_answer_record WHERE question_id IN (SELECT id FROM exercise_question WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM exercise_question WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise_question_cats WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise_user_record WHERE eid IN (SELECT id FROM exercise WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM exercise WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_settings WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM tag WHERE id NOT IN(SELECT DISTINCT tag_id FROM tag_element_module WHERE course_id != ?d)", $cid);
    Database::get()->query("DELETE FROM tag_element_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id IN
                                    (SELECT id FROM gradebook_activities WHERE gradebook_id IN (SELECT id FROM gradebook WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM gradebook_activities WHERE gradebook_id IN (SELECT id FROM gradebook WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM gradebook_users WHERE gradebook_id IN (SELECT id FROM gradebook WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM gradebook WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id IN
                                    (SELECT id FROM attendance_activities WHERE attendance_id IN (SELECT id FROM attendance WHERE course_id = ?d))", $cid);
    Database::get()->query("DELETE FROM attendance_activities WHERE attendance_id IN (SELECT id FROM attendance WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM attendance_users WHERE attendance_id IN (SELECT id FROM attendance WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE FROM attendance WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM tc_session WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_external_server WHERE course_id = ?d", $cid);

    removeDir("$webDir/courses/$course_code");
    removeDir("$webDir/video/$course_code");
    // refresh index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_REMOVEALLBYCOURSE, Indexer::RESOURCE_IDX, $cid);

    Database::get()->query("UPDATE oai_record SET deleted = 1, datestamp = ?t WHERE course_id = ?d", gmdate('Y-m-d H:i:s'), $cid);
}

/**
 * @brief Delete a user and all his dependencies.
 *
 * @param  integer $id - the id of the user.
 * @return boolean     - returns true if deletion was successful, false otherwise.
 */
function deleteUser($id, $log) {

    global $webDir;

    $u = intval($id);
       
    if (!isset($webDir) or empty($webDir)) { // security
        return false;
    }
    if ($u == 1) { // don't delete admin user
        return false;
    } else {
        // validate if this is an existing user
        if (Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $u)) {
            Database::get()->query("DELETE FROM actions_daily WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM admin WHERE user_id = ?d", $u);
            // delete user assignments (if any)
            $assignment_data = Database::get()->queryArray("SELECT assignment_id, file_path FROM assignment_submit WHERE uid = ?d", $u);
            if (count($assignment_data) > 0) { // if assignments found
                foreach ($assignment_data as $data) {
                    $courseid = Database::get()->querySingle("SELECT course_id FROM assignment WHERE id = $data->assignment_id")->course_id;
                    unlink($webDir . "/courses/". course_id_to_code($courseid) . "/work/" . $data->file_path);
                }
            }
            Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_badge WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_certificate_criterion WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_certificate WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM assignment_submit WHERE uid = ?d", $u);
            Database::get()->query("DELETE FROM course_user WHERE user_id = ?d", $u);
            Database::get()->query("DELETE dropbox_attachment FROM dropbox_attachment INNER JOIN dropbox_msg ON dropbox_attachment.msg_id = dropbox_msg.id
                                    WHERE dropbox_msg.author_id = ?d", $u);
            Database::get()->query("DELETE dropbox_index FROM dropbox_index INNER JOIN dropbox_msg ON dropbox_index.msg_id = dropbox_msg.id
                                    WHERE dropbox_msg.author_id = ?d", $u);
            Database::get()->query("DELETE FROM dropbox_index WHERE recipient_id = ?d", $u);
            Database::get()->query("DELETE FROM dropbox_msg WHERE author_id = ?d", $u);
            Database::get()->query("DELETE FROM exercise_user_record WHERE uid = ?d", $u);
            Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN forum_post ON abuse_report.rid = forum_post.id
                                    WHERE abuse_report.rtype = ?s AND forum_post.poster_id = ?d", 'forum_post', $u);
            Database::get()->query("DELETE FROM forum_notify WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM forum_post WHERE poster_id = ?d", $u);
            Database::get()->query("DELETE FROM forum_topic WHERE poster_id = ?d", $u);
            Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM group_members WHERE user_id = ?d", $u);
            if ($log) {
                Database::get()->query("DELETE FROM log WHERE user_id = ?d", $u);
            }
            Database::get()->query("DELETE FROM loginout WHERE id_user = ?d", $u);
            Database::get()->query("DELETE FROM logins WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM lp_user_module_progress WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM poll WHERE creator_id = ?d", $u);
            Database::get()->query("DELETE FROM poll_answer_record WHERE poll_user_record_id IN (SELECT id FROM poll_user_record WHERE uid = ?d)", $u);
            Database::get()->query("DELETE FROM poll_user_record WHERE uid = ?d", $u);
            Database::get()->query("DELETE FROM user_department WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM wiki_pages WHERE owner_id = ?d", $u);
            Database::get()->query("DELETE FROM wiki_pages_content WHERE editor_id = ?d", $u);
            Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN comments ON abuse_report.rid = comments.id
                                    WHERE abuse_report.rtype = ?s AND comments.user_id = ?d", 'comment', $u);
            Database::get()->query("DELETE FROM comments WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM blog_post WHERE user_id = ?d", $u);
            Database::get()->query("DELETE wall_post_resources FROM wall_post_resources INNER JOIN wall_post ON wall_post_resources.post_id = wall_post.id
                                    WHERE wall_post.user_id = ?d", $u);
            Database::get()->query("DELETE FROM wall_post WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM abuse_report WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM user WHERE id = ?d", $u);
            Database::get()->query("DELETE FROM note WHERE user_id = ?d" , $u);
            Database::get()->query("DELETE FROM personal_calendar WHERE user_id = ?d" , $u);
            Database::get()->query("DELETE FROM personal_calendar_settings WHERE user_id = ?d" , $u);
            Database::get()->query("DELETE FROM custom_profile_fields_data WHERE user_id = ?d", $u);
            Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN `link` ON abuse_report.rid = `link`.id
                                    WHERE abuse_report.rtype = ?s AND `link`.user_id = ?d", 'link', $u);
            Database::get()->query("DELETE FROM `link` WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM eportfolio_resource WHERE user_id = ?d", $u);
            Database::get()->query("DELETE FROM eportfolio_fields_data WHERE user_id = ?d", $u);

            // delete user images (if any)
            array_map('unlink', glob("$webDir/courses/userimg/{$u}_256.*"));
            array_map('unlink', glob("$webDir/courses/userimg/{$u}_32.*"));

            //delete user e-portfolio files (if any)
            array_map('unlink', glob("$webDir/courses/eportfolio/userbios/{$u}/bio.pdf"));
            array_map('unlink', glob("$webDir/courses/eportfolio/work_submissions/{$u}/*"));
            if (is_dir("$webDir/courses/eportfolio/userbios/{$u}")) {
                rmdir("$webDir/courses/eportfolio/userbios/{$u}");
            }
            if (is_dir("$webDir/courses/eportfolio/work_submissions/{$u}")) {
                rmdir("$webDir/courses/eportfolio/work_submissions/{$u}");
            }
            if (is_dir("$webDir/courses/eportfolio/mydocs/{$u}")) {
                rmdir("$webDir/courses/eportfolio/mydocs/{$u}");
            }

            return true;
        } else {
            return false;
        }
    }
}

/**
 * @brief Return the value of a key from the config table, or a default value (or null) if not found
 * @param type $key
 * @param type $default
 * @return type
 */
function get_config($key, $default = null) {

    $r = Database::get()->querySingle("SELECT `value` FROM config WHERE `key` = ?s", $key);
    if ($r) {
        $row = $r->value;
        return $row;
    } else {
        return $default;
    }
}

/**
 * @brief Set the value of a key in the config table
 * @param type $key
 * @param type $value
 */
function set_config($key, $value) {

    Database::get()->query("REPLACE INTO config (`key`, `value`) VALUES (?s, ?s)", $key, $value);
}

// Copy variables from $_POST[] to $GLOBALS[], trimming and canonicalizing whitespace
// $var_array = array('var1' => true, 'var2' => false, [varname] => required...)
// Returns true if all vars with required=true are set, false if not (by default)
// If $what = 'any' returns true if any variable is set
function register_posted_variables($var_array, $what = 'all', $callback = null) {
    global $missing_posted_variables;

    if (!isset($missing_posted_variables)) {
        $missing_posted_variables = array();
    }

    $all_set = true;
    $any_set = false;
    foreach ($var_array as $varname => $required) {
        if (isset($_POST[$varname])) {
            $GLOBALS[$varname] = canonicalize_whitespace($_POST[$varname]);
            if ($required and empty($GLOBALS[$varname])) {
                $missing_posted_variables[$varname] = true;
                $all_set = false;
            }
            if (!empty($GLOBALS[$varname])) {
                $any_set = true;
            }
        } else {
            $GLOBALS[$varname] = '';
            if ($required) {
                $missing_posted_variables[$varname] = true;
                $all_set = false;
            }
        }
        if (is_callable($callback)) {
            $GLOBALS[$varname] = $callback($GLOBALS[$varname]);
        }
    }
    if ($what == 'any') {
        return $any_set;
    } else {
        return $all_set;
    }
}

/**
 * Display a textarea with name $name using the rich text editor
 * Apply automatically various fixes for the text to be edited
 * @global type $head_content
 * @global type $language
 * @global type $purifier
 * @global type $urlAppend
 * @global type $course_code
 * @global type $langPopUp
 * @global type $langPopUpFrame
 * @global type $is_editor
 * @global type $is_admin
 * @param type $name
 * @param type $rows
 * @param type $cols
 * @param type $text
 * @param type $extra
 * @return type
 */
function rich_text_editor($name, $rows, $cols, $text, $onFocus = false) {
    global $head_content, $language, $urlAppend, $course_code, $langPopUp, $langPopUpFrame, $is_editor, $is_admin, $langResourceBrowser, $langMore;
    static $init_done = false;
    if (!$init_done) {
        $init_done = true;
        $filebrowser = $url = '';

        // params for tinymce embed
        $activemodule = 'document/index.php';
        $append_module = (current_module_id()) ? "&originating_module=" . q(current_module_id()) : '';
        $append_forum = (isset($_REQUEST['forum'])) ? "&originating_forum=" . q($_REQUEST['forum']) : '';

        if (isset($course_code) && $course_code) {
            $filebrowser = "file_browser_callback : openDocsPicker,";
            if (!$is_editor) {
                $cid = course_code_to_id($course_code);
                $module = Database::get()->querySingle("SELECT * FROM course_module
                            WHERE course_id = ?d
                              AND (module_id =" . MODULE_ID_DOCS . " OR module_id =" . MODULE_ID_VIDEO . " OR module_id =" . MODULE_ID_LINKS . ")
                              AND VISIBLE = 1 ORDER BY module_id", $cid);
                if ($module === false) {
                    $filebrowser = '';
                } else {
                    switch ($module->module_id) {
                    case MODULE_ID_LINKS:
                        $activemodule = 'link/index.php';
                        break;
                    case MODULE_ID_DOCS:
                        $activemodule = 'document/index.php';
                        break;
                    case MODULE_ID_VIDEO:
                        $activemodule = 'video/index.php';
                        break;
                    default:
                        $filebrowser = '';
                        break;
                    }
                }
            }
            $url = $urlAppend . "modules/" . $activemodule . "?course=" . $course_code . "&embedtype=tinymce" . $append_module . $append_forum . "&docsfilter=";
        } elseif ($is_admin) { /* special case for admin announcements */
            $filebrowser = "file_browser_callback : openDocsPicker,";
            $url = $urlAppend . "modules/admin/commondocs.php?embedtype=tinymce" . $append_module . $append_forum . "&docsfilter=";
        }
        $focus_init = ",
                init_instance_callback: function(editor) {
                    var parent = $(editor.contentAreaContainer.parentElement);
                    (editorToggleSecondToolbar(editor))();
                    parent.find('.mce-toolbar-grp, .mce-statusbar').attr('style','border:1px solid #ddd');";
        if ($onFocus) {
            $focus_init .= "parent.find('.mce-toolbar-grp').hide();";
        }
        $focus_init .= "},";
        if ($onFocus) {
            $focus_init .= "
                statusbar: false,
                setup: function (editor) {
                    var toolbarGrp;
                    editorAddButtonToggle(editor);
                    editor.on('focus', function () {
                        toolbarGrp.show();
                    });
                    editor.on('blur', function () {
                        toolbarGrp.hide();
                    });
                    editor.on('init', function() {
                        toolbarGrp = $(editor.contentAreaContainer.parentElement).find('.mce-toolbar-grp');
                    });
                }";
        } else {
            $focus_init .= "
                setup: function (editor) {
                    editorAddButtonToggle(editor);
                },
                ";
        }
        load_js('tinymce/tinymce.min.js');
        $head_content .= "
<script type='text/javascript'>

function editorToggleSecondToolbar(editor) {
    return function() {
        var toolbar = $(editor.contentAreaContainer.parentElement).find('.mce-toolbar-grp .mce-toolbar').eq(1);
        toolbar.toggle();
    }
}

function editorAddButtonToggle (editor) {
    editor.addButton('toggle', {
        title: '".js_escape($langMore)."',
        classes: 'toggle',
        image: '{$urlAppend}js/tinymce/skins/light/img/toggle.png',
        onclick: editorToggleSecondToolbar(editor),
    });
}

function openDocsPicker(field_name, url, type, win) {
    tinymce.activeEditor.windowManager.open({
        file: '$url' + type,
        title: '".js_escape($langResourceBrowser)."',
        width: 800,
        height: 600,
        resizable: 'yes',
        inline: 'yes',
        close_previous: 'no',
        popup_css: false
    }, {
        window: win,
        input: field_name
    });
    return false;
}

tinymce.init({
    // General options
    selector: 'textarea.mceEditor',
    language: '$language',
    theme: 'modern',
    skin: 'light',
    image_advtab: true,
    image_class_list: [
        {title: 'Responsive', value: 'img-responsive'},
        {title: 'Responsive Center', value: 'img-responsive center-block'},
        {title: 'Float left', value: 'pull-left'},
        {title: 'Float left and responsive', value: 'pull-left img-responsive'},
        {title: 'Float right', value: 'pull-right'},
        {title: 'Float right and responsive', value: 'pull-right img-responsive'},
        {title: 'Rounded image', value: 'img-rounded'},
        {title: 'Rounded image and responsive', value: 'img-rounded img-responsive'},
        {title: 'Circle image', value: 'img-circle'},
        {title: 'Circle image and responsive', value: 'img-circle img-responsive'},
        {title: 'Thumbnail image', value: 'img-thumbnail'},
        {title: 'Thumbnail image and responsive', value: 'img-thumbnail img-responsive'},
        {title: 'None', value: ' '}
    ],
    plugins: 'fullscreen,pagebreak,save,image,link,media,eclmedia,print,contextmenu,paste,noneditable,visualchars,nonbreaking,template,wordcount,advlist,emoticons,preview,searchreplace,table,insertdatetime,code,textcolor,colorpicker',
    entity_encoding: 'raw',
    relative_urls: false,
    link_class_list: [
        {title: 'None', value: ''},
        {title: '".js_escape($langPopUp)."', value: 'colorbox'},
        {title: '".js_escape($langPopUpFrame)."', value: 'colorboxframe'}
    ],
    $filebrowser
    // Menubar options
    menu : 'false',
    // Toolbar options
    toolbar1: 'toggle | bold | italic | forecolor | emoticons | link | image | media | eclmedia | alignleft | aligncenter | alignright | alignjustify | bullist | numlist | outdent | indent',
    toolbar2: 'underline | strikethrough | superscript | subscript | table | undo | redo | pastetext | cut | copy | paste | removeformat | formatselect | fontsizeselect | fullscreen | preview | searchreplace | code',
    // Replace values for the template plugin
     // Toolbar options
    //toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media eclmedia code',
    // Replace values for the template plugin
    template_replace_values: {
            username : 'Open eClass',
            staffid : '991234'
    }
    $focus_init
});
</script>";
    }

    /* $text = str_replace(array('<m>', '</m>', '<M>', '</M>'),
      array('[m]', '[/m]', '[m]', '[/m]'),
      $text); */

    return "<textarea class='mceEditor' name='$name' rows='$rows' cols='$cols'>" .
            q(str_replace('{', '&#123;', $text)) .
            "</textarea>\n";
}

// Display a simple textarea with name $name
// Apply automatically various fixes for the text to be edited
function text_area($name, $rows, $cols, $text, $extra = '') {

    global $purifier;

    $text = str_replace(array('<m>', '</m>', '<M>', '</M>'), array('[m]', '[/m]', '[m]', '[/m]'), $text);
    if (strpos($extra, 'class=') === false) {
        $extra .= ' class="form-control mceNoEditor"';
    }
    return "<textarea name='$name' rows='$rows' cols='$cols' $extra>" .
            q(str_replace('{', '&#123;', $text)) .
            "</textarea>\n";
}

/**
 *
 * @param type $unit_id
 * @return int
 */
function add_unit_resource_max_order($unit_id) {

    $q = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $unit_id);
    if ($q) {
        $order = $q->maxorder;
        return max(0, $order) + 1;
    } else {
        return 1;
    }
}

/**
 *
 * @param type $unit_id
 * @return type
 */
function new_description_res_id($unit_id) {

    $q = Database::get()->querySingle("SELECT MAX(res_id) AS maxresid FROM unit_resources WHERE unit_id = ?d", $unit_id);
    $max_res_id = $q->maxresid;
    return 1 + max(count($GLOBALS['titreBloc']), $max_res_id);
}

/**
 * @brief add resource to course units
 * @param type $unit_id
 * @param type $type
 * @param type $res_id
 * @param type $title
 * @param type $content
 * @param type $visibility
 * @param type $date
 * @return type
 */
function add_unit_resource($unit_id, $type, $res_id, $title, $content, $visibility = 0, $date = false) {

    if (!$date) {
        $date = "NOW()";
    }
    if ($res_id === false) {
        $res_id = new_description_res_id($unit_id);
        $order = add_unit_resource_max_order($unit_id);
    } elseif ($res_id < 0) {
        $order = $res_id;
    } else {
        $order = add_unit_resource_max_order($unit_id);
    }
    $q = Database::get()->querySingle("SELECT id FROM unit_resources WHERE
                                `unit_id` = ?d AND
                                `type` = ?s AND
                                `res_id` = ?d", $unit_id, $type, $res_id);
    if ($q) {
        $id = $q->id;
        Database::get()->query("UPDATE unit_resources SET
                                        `title` = ?s,
                                        `comments` = ?s,
                                        `date` = $date
                                 WHERE id = ?d", $title, $content, $id);
        return;
    }
    Database::get()->query("INSERT INTO unit_resources SET
                                `unit_id` = ?d,
                                `title` = ?s,
                                `comments` = ?s,
                                `date` = $date,
                                `type` = ?s,
                                `visible` = ?d,
                                `res_id` = ?d,
                                `order` = ?d", $unit_id, $title, $content, $type, $visibility, $res_id, $order);
    return;
}

/**
 *
 * @global null $maxorder
 * @global type $course_id
 */
function units_set_maxorder() {

    global $maxorder, $course_id;

    $q = Database::get()->querySingle("SELECT MAX(`order`) as max_order FROM course_units WHERE course_id = ?d", $course_id);

    $maxorder = $q->max_order;

    if ($maxorder <= 0) {
        $maxorder = null;
    }
}

/**
 *
 * @global type $langCourseUnitModified
 * @global type $langCourseUnitAdded
 * @global null $maxorder
 * @global type $course_id
 * @global type $course_code
 * @global type $webDir
 * @return type
 */
function handle_unit_info_edit() {

    global $langCourseUnitModified, $langCourseUnitAdded, $maxorder, $course_id, $course_code, $webDir;
    require_once 'modules/tags/moduleElement.class.php';
    $title = $_REQUEST['unittitle'];
    $descr = purify($_REQUEST['unitdescr']);
    if (isset($_REQUEST['unit_id'])) { // update course unit
        $unit_id = $_REQUEST['unit_id'];
        Database::get()->query("UPDATE course_units SET
                                        title = ?s,
                                        comments = ?s
                                    WHERE id = ?d AND course_id = ?d", $title, $descr, $unit_id, $course_id);
        // tags
        if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($unit_id);
            $moduleTag->syncTags($tagsArray);
        }
        $successmsg = $langCourseUnitModified;
    } else { // add new course unit
        $order = $maxorder + 1;
        $q = Database::get()->query("INSERT INTO course_units SET
                                  title = ?s, comments = ?s, visible = 1,
                                 `order` = ?d, course_id = ?d", $title, $descr, $order, $course_id);
        $successmsg = $langCourseUnitAdded;
        $unit_id = $q->lastInsertID;
        // tags
        if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($unit_id);
            $moduleTag->attachTags($tagsArray);
        }
    }
    // update index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNIT, $unit_id);
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    // refresh course metadata
    require_once 'modules/course_metadata/CourseXML.php';
    CourseXMLElement::refreshCourse($course_id, $course_code);

    Session::Messages($successmsg, 'alert-success');
    redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit_id");
}

function math_unescape($matches) {
    return html_entity_decode($matches[0]);
}

// Standard function to prepare some HTML text, possibly with math escapes, for display
function standard_text_escape($text, $mathimg = '../../courses/mathimg/') {
    global $purifier;

    $text = preg_replace_callback('/\[m\].*?\[\/m\]/s', 'math_unescape', $text);
    $html = $purifier->purify(mathfilter($text, 12, $mathimg));

    if (!isset($_SESSION['glossary_terms_regexp'])) {
        return $html;
    }

    $dom = new DOMDocument();
    // workaround because DOM doesn't handle utf8 encoding correctly.
    @$dom->loadHTML('<div>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') . '</div>');

    $xpath = new DOMXpath($dom);
    $textNodes = $xpath->query('//text()');
    foreach ($textNodes as $textNode) {
        if (!empty($textNode->data)) {
            $new_contents = glossary_expand($textNode->data);
            if ($new_contents != $textNode->data) {
                $newdoc = new DOMDocument();
                $newdoc->loadXML('<span>' . $new_contents . '</span>', LIBXML_NONET|LIBXML_DTDLOAD|LIBXML_DTDATTR);
                $newnode = $dom->importNode($newdoc->getElementsByTagName('span')->item(0), true);
                $textNode->parentNode->replaceChild($newnode, $textNode);
                unset($newdoc);
                unset($newnode);
            }
        }
    }
    $base_node = $dom->getElementsByTagName('div')->item(0);
    // iframe hack
    return preg_replace(array('|^<div>(.*)</div>$|s',
        '#(<iframe [^>]+)/>#'), array('\\1', '\\1></iframe>'), dom_save_html($dom, $base_node));
}

// Workaround for $dom->saveHTML($node) not working for PHP < 5.3.6
function dom_save_html($dom, $node) {
    if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
        return $dom->saveHTML($node);
    } else {
        return $dom->saveXML($node);
    }
}

function purify($text) {
    global $purifier;
    return $purifier->purify($text);
}

// Expand glossary terms to HTML for tooltips with the definition
function glossary_expand($text) {
    return preg_replace_callback($_SESSION['glossary_terms_regexp'], 'glossary_expand_callback', q($text));
}

function glossary_expand_callback($matches) {
    static $glossary_seen_terms;
    global $langGlossaryUrl, $langComments;

    $term = mb_strtolower(html_entity_decode($matches[0]), 'UTF-8');
    if (isset($glossary_seen_terms[$term])) {
        return $matches[0];
    }
    $glossary_seen_terms[$term] = true;

    if (!empty($_SESSION['glossary'][$term])) {
        $term_notes = isset($_SESSION['glossary_notes'][$term]) ? q('<hr><small class="text-muted">'.$langComments.': '.$_SESSION['glossary_notes'][$term].'</small>') : '';
        $term_url = isset($_SESSION['glossary_url'][$term]) ? q('<hr><a href="'.$_SESSION['glossary_url'][$term].'">'.$langGlossaryUrl.'</a>') : '';
        $definition = ' title="'.$matches[0].'" data-trigger="focus" data-html="true" data-content="' . q($_SESSION['glossary'][$term]) . $term_notes . $term_url .'"';
    } else {
        $definition = '';
    }

    return '<a href="#" data-toggle="popover"' .
            $definition . '>' . $matches[0] . '</a>';
}

function get_glossary_terms($course_id) {

    $expand = Database::get()->querySingle("SELECT glossary_expand FROM course
                                                         WHERE id = ?d", $course_id)->glossary_expand;
    if (!$expand) {
        return false;
    }

    $q = Database::get()->queryArray("SELECT term, definition, url, notes FROM glossary
                              WHERE course_id = $course_id GROUP BY term, definition, url, notes");

    if (count($q) > intval(get_config('max_glossary_terms'))) {
        return false;
    }

    $_SESSION['glossary'] = array();
    $_SESSION['glossary_url'] = array();
    $_SESSION['glossary_notes'] = array();

    foreach ($q as $row) {
        $term = mb_strtolower($row->term, 'UTF-8');
        $_SESSION['glossary'][$term] = $row->definition;
        if (!empty($row->url)) {
            $_SESSION['glossary_url'][$term] = $row->url;
        }
        if (!empty($row->notes)) {
            $_SESSION['glossary_notes'][$term] = $row->notes;
        }
    }
    $_SESSION['glossary_course_id'] = $course_id;
    return true;
}

function set_glossary_cache() {
    global $course_id;

    if (!isset($course_id)) {
        unset($_SESSION['glossary_terms_regexp']);
    } elseif (!isset($_SESSION['glossary_terms_regexp']) or
            $_SESSION['glossary_course_id'] != $course_id) {
        if (get_glossary_terms($course_id) and count($_SESSION['glossary']) > 0) {
            // Test whether \b works correctly, workaround if not
            if (preg_match('/α\b/u', 'α')) {
                $spre = $spost = '\b';
            } else {
                $spre = '(?<=[\x01-\x40\x5B-\x60\x7B-\x7F]|^)';
                $spost = '(?=[\x01-\x40\x5B-\x60\x7B-\x7F]|$)';
            }
            $_SESSION['glossary_terms_regexp'] = chr(1) . $spre . '(';
            $begin = true;
            foreach (array_keys($_SESSION['glossary']) as $term) {
                $_SESSION['glossary_terms_regexp'] .= ($begin ? '' : '|') .
                        preg_quote(q($term));
                if ($begin) {
                    $begin = false;
                }
            }
            $_SESSION['glossary_terms_regexp'] .= ')' . $spost . chr(1) . 'ui';
        } else {
            unset($_SESSION['glossary_terms_regexp']);
        }
    }
}

function invalidate_glossary_cache() {
    unset($_SESSION['glossary']);
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function redirect_to_home_page($path='', $absolute=false) {
    global $urlServer;

    if (!$absolute) {
        $path = preg_replace('+^/+', '', $path);
        $path = $urlServer . $path;
    }
    header("HTTP/1.1 303 See Other");
    header("Location: $path");
    exit;
}

function odd_even($k, $extra = '') {
    if (!empty($extra)) {
        $extra = ' ' . $extra;
    }
    if ($k % 2 == 0) {
        return " class='even$extra'";
    } else {
        return " class='odd$extra'";
    }
}

// Translate Greek characters to Latin
function greek_to_latin($string) {
    return str_replace(
            array(
        'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π',
        'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ',
        'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω',
        'ς', 'ά', 'έ', 'ή', 'ί', 'ύ', 'ό', 'ώ', 'Ά', 'Έ', 'Ή', 'Ί', 'Ύ', 'Ό', 'Ώ', 'ϊ',
        'ΐ', 'ϋ', 'ΰ', 'ϊ', 'Ϋ', '–'), array(
        'a', 'b', 'g', 'd', 'e', 'z', 'i', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p',
        'r', 's', 't', 'y', 'f', 'x', 'ps', 'o', 'A', 'B', 'G', 'D', 'E', 'Z', 'H', 'Th',
        'I', 'K', 'L', 'M', 'N', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'X', 'Ps', 'O',
        's', 'a', 'e', 'i', 'i', 'y', 'o', 'o', 'A', 'E', 'H', 'I', 'Y', 'O', 'O', 'i',
        'i', 'y', 'y', 'I', 'Y', '-'), $string);
}

// Convert to uppercase and remove accent marks
// Limited coverage for now
function remove_accents($string) {
    return strtr(mb_strtoupper($string, 'UTF-8'), array('Ά' => 'Α', 'Έ' => 'Ε', 'Ί' => 'Ι', 'Ή' => 'Η', 'Ύ' => 'Υ',
        'Ό' => 'Ο', 'Ώ' => 'Ω', 'Ϊ' => 'Ι', 'Ϋ' => 'Υ',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Ç' => 'C', 'Ñ' => 'N', 'Ý' => 'Y',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U'));
}

// resize an image ($source_file) of type $type to a new size ($maxheight and $maxwidth) and copies it to path $target_file
function copy_resized_image($source_file, $type, $maxwidth, $maxheight, $target_file) {
    if ($type == 'image/jpeg') {
        $image = @imagecreatefromjpeg($source_file);
    } elseif ($type == 'image/png') {
        $image = @imagecreatefrompng($source_file);
    } elseif ($type == 'image/gif') {
        $image = @imagecreatefromgif($source_file);
    } elseif ($type == 'image/bmp') {
        $image = @imagecreatefromwbmp($source_file);
    }
    if (!isset($image) or !$image) {
        return false;
    }
    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > $maxwidth or $height > $maxheight) {
        $xscale = $maxwidth / $width;
        $yscale = $maxheight / $height;
        if ($yscale < $xscale) {
            $newwidth = round($width * $yscale);
            $newheight = round($height * $yscale);
        } else {
            $newwidth = round($width * $xscale);
            $newheight = round($height * $xscale);
        }
        $resized = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        return imagejpeg($resized, $target_file);
    } elseif ($type != 'image/jpeg') {
        return imagejpeg($image, $target_file);
    } else {
        return copy($source_file, $target_file);
    }
}

// Produce HTML source for an icon
function icon($name, $title = null, $link = null, $link_attrs = '', $with_title = false, $sr_only = false) {
    global $themeimg;

    if (isset($title)) {
        $title = q($title);
        $extra = "title='$title' data-toggle='tooltip'";
    } else {
        $extra = '';
    }
    if (isset($title) && $with_title) {
        $img = $sr_only ? "<span class='fa $name' $extra></span><span class='sr-only'>$title</span>" : "<span class='fa $name' $extra></span> $title";
    } else {
        $img = "<span class='fa $name' $extra></span>";
    }
    if (isset($link)) {
        return "<a href='$link'$link_attrs>$img</a>";
    } else {
        return $img;
    }
}

function icon_old_style($name, $title = null, $link = null, $attrs = null, $format = 'png', $link_attrs = '') {
    global $themeimg;

    if (isset($title)) {
        $title = q($title);
        $extra = "alt='$title' title='$title'";
    } else {
        $extra = "alt=''";
    }

    if (isset($attrs)) {
        $extra .= ' ' . $attrs;
    }

    $img = "<img src='$themeimg/$name.$format' $extra>";
    if (isset($link)) {
        return "<a href='$link'$link_attrs>$img</a>";
    } else {
        return $img;
    }
}

/**
 * Link for displaying user profile
 * @param type $uid
 * @param type $size
 * @param type $class
 * @return type
 */
function profile_image($uid, $size, $class=null) {
    global $urlServer, $themeimg, $langStudent;

    // makes $class argument optional
    $class_attr = ($class == null)?'':"class='".q($class)."'";

    $name = ($uid > 0) ? q(trim(uid_to_name($uid))) : '';
    $size_width = ($size != IMAGESIZE_SMALL || $size != IMAGESIZE_LARGE)? "style='width:$size'":'';
    $size = ($size != IMAGESIZE_SMALL && $size != IMAGESIZE_LARGE)? IMAGESIZE_LARGE:$size;
    if ($uid > 0 and file_exists("courses/userimg/${uid}_$size.jpg")) {
        return "<img src='${urlServer}courses/userimg/${uid}_$size.jpg' $class_attr title='$name' alt='$name' $size_width>";
    } else {
        return "<img src='$themeimg/default_$size.png' $class_attr title='$name' alt='$name' $size_width>";
    }
}

function canonicalize_url($url) {
    if (!preg_match('/^[a-zA-Z0-9_-]+:/', $url)) {
        return 'http://' . $url;
    } else {
        return $url;
    }
}

function is_url_accepted($url,$protocols=""){
    if ($url === 'http://' || empty($url) || !filter_var($url, FILTER_VALIDATE_URL) || preg_match('/^javascript/i', preg_replace('/\s+/', '', $url)) || ($protocols!=="" && !preg_match('/^'.$protocols.'/i', preg_replace('/\s+/', '', $url)))) {
        return 0;
    }
    else{
        return 1;
    }
}

function stop_output_buffering() {
    while (@ob_end_flush());
}

// Seed mt_rand
function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

// Generate a $len length random base64 encoded alphanumeric string
// try first /dev/urandom but if not available generate pseudo-random string
function generate_secret_key($len) {
    if (($key = read_urandom($len)) == NULL) {
        // poor man's choice
        $key = poor_rand_string($len);
    }
    return base64_encode($key);
}

// Generate a $len length pseudo random base64 encoded alphanumeric string from ASCII table
function poor_rand_string($len) {
    mt_srand(make_seed());

    $c = "";
    for ($i = 0; $i < $len; $i++) {
        $c .= chr(mt_rand(0, 127));
    }

    return $c;
}

// Read $len length random string from /dev/urandom if it's available
function read_urandom($len) {
    if (@is_readable('/dev/urandom')) {
        $f = fopen('/dev/urandom', 'r');
        $urandom = fread($f, $len);
        fclose($f);
        return $urandom;
    } else {
        return NULL;
    }
}

/**
 * @brief Get user admin rights from table `admin`
 * @param type $user_id
 * @return type
 */
function get_admin_rights($user_id) {

    $r = Database::get()->querySingle("SELECT privilege FROM admin WHERE user_id = ?d", $user_id);
    if ($r) {
        return $r->privilege;
    } else {
        return -1;
    }
}

/**
 * @brief query course status
 * @param type $course_id
 * @return course status
 */
function course_status($course_id) {

    $status = Database::get()->querySingle("SELECT visible FROM course WHERE id = ?d", $course_id)->visible;

    return $status;
}

/**
 * @brief return message concerning course visibility
 * @global type $langTypeOpen
 * @global type $langTypeClosed
 * @global type $langTypeInactive
 * @global type $langTypeRegistration
 * @param type $course_id
 * @return type
 */
function course_status_message($course_id) {

    global $langTypeOpen, $langTypeClosed, $langTypeInactive, $langTypeRegistration;

    $status = Database::get()->querySingle("SELECT visible FROM course WHERE id = ?d", $course_id)->visible;
    switch ($status) {
        case COURSE_REGISTRATION: $message = $langTypeRegistration; break;
        case COURSE_OPEN: $message = $langTypeOpen; break;
        case COURSE_CLOSED: $message = $langTypeClosed; break;
        case COURSE_INACTIVE: $message = $langTypeInactive; break;
    }
    return $message;
}

/**
 * @brief get user email verification status
 * @param type $uid
 * @return verified mail or no
 */
function get_mail_ver_status($uid) {

    $q = Database::get()->querySingle("SELECT verified_mail FROM user WHERE id = ?d", $uid)->verified_mail;

    return $q;
}

// check if username match for both case sensitive/insensitive
function check_username_sensitivity($posted, $dbuser) {
    if (get_config('case_insensitive_usernames')) {
        if (mb_strtolower($posted) == mb_strtolower($dbuser)) {
            return true;
        } else {
            return false;
        }
    } else {
        if ($posted == $dbuser) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

/**
 * @brief checks if user is notified via email from a given course
 * @param type $user_id
 * @param type $course_id
 * @return boolean
 */
function get_user_email_notification($user_id, $course_id = null) {
    // check if user is active
    if (Database::get()->querySingle('SELECT expires_at < NOW() AS expired FROM user WHERE id = ?d', $user_id)->expired) {
        return false;
    }
    // check if course is active or not
    if (isset($course_id) and course_status($course_id) == COURSE_INACTIVE) {
        return false;
    }
    // check if user's email address is verified
    if (get_config('email_verification_required') && get_config('dont_mail_unverified_mails')) {
        $verified_mail = get_mail_ver_status($user_id);
        if ($verified_mail == EMAIL_VERIFICATION_REQUIRED or $verified_mail == EMAIL_UNVERIFIED) {
            return false;
        }
    }
    // check if user has chosen not to be notified by email from all courses
    if (!get_user_email_notification_from_courses($user_id)) {
        return false;
    }
    if (isset($course_id)) {
        // finally check if user has choosen not to be notified from a specific course
        $r = Database::get()->querySingle("SELECT receive_mail FROM course_user
                                            WHERE user_id = ?d
                                            AND course_id = ?d", $user_id, $course_id);
        if ($r) {
            $row = $r->receive_mail;
            return $row;
        } else {
            return false;
        }
    }
    return true;
}

/**
 * @brief checks if user is notified via email from courses
 * @param type $user_id
 * @return boolean
 */
function get_user_email_notification_from_courses($user_id) {
    $result = Database::get()->querySingle("SELECT receive_mail FROM user WHERE id = ?d", $user_id);
    if ($result && $result->receive_mail)
        return true;
    return false;
}


// Return a list of all subdirectories of $base which contain a file named $filename
function active_subdirs($base, $filename) {
    $dir = opendir($base);
    $out = array();
    while (($f = readdir($dir)) !== false) {
        if (is_dir($base . '/' . $f) and
                $f != '.' and $f != '..' and
                file_exists($base . '/' . $f . '/' . $filename)) {
            $out[] = $f;
        }
    }
    closedir($dir);
    return $out;
}

/*
 * Delete a directory and its whole content
 *
 * @author - Hugues Peeters
 * @param  - $dirPath (String) - the path of the directory to delete
 * @return - boolean - true if the delete succeed, false otherwise.
 */

function removeDir($dirPath) {
    global $webDir;

    // Don't delete root directories
    $dirPath = rtrim($dirPath, '/\\');
    if ($dirPath == $webDir or $dirPath === '') {
        return false;
    }

    /* Try to remove the directory. If it can not manage to remove it,
     * it's probable the directory contains some files or other directories,
     * and that we must first delete them to remove the original directory.
     */
    if (@rmdir($dirPath)) {
        return true;
    } else { // if directory couldn't be removed...
        $ok = true;
        $cwd = getcwd();
        chdir($dirPath);
        $handle = opendir($dirPath);

        while ($element = readdir($handle)) {
            if ($element == '.' or $element == '..') {
                continue; // skip current and parent directories
            } elseif (is_file($element)) {
                $ok = @unlink($element) && $ok;
            } elseif (is_dir($element)) {
                $dirToRemove[] = $dirPath . '/' . $element;
            }
        }

        closedir($handle);
        chdir($cwd);

        if (isset($dirToRemove) and count($dirToRemove)) {
            foreach ($dirToRemove as $j) {
                $ok = removeDir($j) && $ok;
            }
        }

        return @rmdir($dirPath) && $ok;
    }
}

/**
 * Generate a token verifying some info
 *
 * @param  string  $info           - The info that will be verified by the token
 * @param  boolean $need_timestamp - Whether the token will include a timestamp
 * @return string  $ret            - The new token
 */
function token_generate($info, $need_timestamp = false) {
    if ($need_timestamp) {
        $ts = sprintf('%x-', time());
    } else {
        $ts = '';
    }
    $code_key = get_config('code_key');
    return $ts . hash_hmac('ripemd160', $ts . $info, $code_key);
}

/**
 * Validate a token verifying some info
 *
 * @param  string  $info           - The info that will be verified by the token
 * @param  string  $token          - The token to verify
 * @param  int     $ts_valid_time  - Period of validity of token in seconds, if token includes a timestamp
 * @return boolean $ret            - True if the token is valid, false otherwise
 */
function token_validate($info, $token, $ts_valid_time = 0) {
    $data = explode('-', $token);
    if (count($data) > 1) {
        $timediff = time() - hexdec($data[0]);
        if ($timediff > $ts_valid_time) {
            return false;
        }
        $token = $data[1];
        $ts = $data[0] . '-';
    } else {
        $ts = '';
    }
    $code_key = get_config('code_key');
    return $token == hash_hmac('ripemd160', $ts . $info, $code_key);
}

/**
 * This is a class for cutting a string to be no more than $maxlen characters long, respecting the html tags
 * Based on code provided by prajwala
 * http://code.google.com/p/cut-html-string/
 */
class HtmlCutString {

    function __construct($string, $limit, $postfix) {
        // create dom element using the html string
        $this->tempDiv = new DOMDocument('1.0', 'UTF-8');
        $this->tempDiv->loadHTML('<?xml version="1.0" encoding="UTF-8" ?><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><div>' . $string . '</div>', LIBXML_NONET|LIBXML_DTDLOAD|LIBXML_DTDATTR);
        // keep the characters count till now
        $this->charCount = 0;
        // put the postfix at the end
        $this->postfix = FALSE;
        $this->postfix_text = $postfix;
        $this->encoding = 'UTF-8';
        // character limit need to check
        $this->limit = $limit;
    }

    function cut() {
        // create empty document to store new html
        $this->newDiv = new DomDocument;
        // cut the string by parsing through each element
        $this->searchEnd($this->tempDiv->documentElement, $this->newDiv);
        $newhtml = $this->newDiv->saveHTML();
        if ($this->postfix)
            return $newhtml . $this->postfix_text;
        else
            return $newhtml;
    }

    function deleteChildren($node) {
        while (isset($node->firstChild)) {
            $this->deleteChildren($node->firstChild);
            $node->removeChild($node->firstChild);
        }
    }

    function searchEnd($parseDiv, $newParent) {
        foreach ($parseDiv->childNodes as $ele) {
            // not text node
            if ($ele->nodeType != 3) {
                $newEle = $this->newDiv->importNode($ele, true);
                if (count($ele->childNodes) === 0) {
                    $newParent->appendChild($newEle);
                    continue;
                }
                $this->deleteChildren($newEle);
                $newParent->appendChild($newEle);
                $res = $this->searchEnd($ele, $newEle);
                if ($res)
                    return $res;
                else {
                    continue;
                }
            }

            // the limit of the char count reached
            if (mb_strlen($ele->nodeValue, $this->encoding) + $this->charCount >= $this->limit) {
                $newEle = $this->newDiv->importNode($ele);
                $newEle->nodeValue = mb_substr($newEle->nodeValue, 0, $this->limit - $this->charCount, $this->encoding);
                $newParent->appendChild($newEle);
                $this->postfix = TRUE;
                return true;
            }
            $newEle = $this->newDiv->importNode($ele);
            $newParent->appendChild($newEle);
            $this->charCount += mb_strlen($newEle->nodeValue, $this->encoding);
        }
        return false;
    }

}

/**
 * @brief count online users (depending on sessions)
 * @return int
 */
function getOnlineUsers() {

    if (ini_get('session.save_handler') == 'redis') {
        $redis = new Redis();
        $path = ini_get('session.save_path');
        $url = parse_url($path);
        if (isset($url['host'])) {
            if (isset($url['port'])) {
                $redis->pconnect($url['host'], $url['port']);
            } else {
                $redis->pconnect($url['host']);
            }
        } elseif (isset($url['path'])) {
            $redis->pconnect($url['path']);
        } elseif (preg_match('|^unix://(/[^?]+)|', $path, $matches)) {
            $redis->pconnect($matches[1]);
        } else {
            return 0;
        }
        return floor($redis->dbSize() / 12);
    }

    $count = 0;
    if ($directory_handle = @opendir(session_save_path())) {
        while (false !== ($file = readdir($directory_handle))) {
            if ($file != '.' and $file != '..') {
                if (time() - fileatime(session_save_path() . '/' . $file) < MAX_IDLE_TIME * 60) {
                    $count++;
                }
            }
        }
    }
    @closedir($directory_handle);
    return $count;
}

/**
 * checks if F.A.Q. exist
 * @return boolean
 */
function faq_exist() {
    if (!DBHelper::tableExists('faq')) {
        return false;
    }

    $count_faq = Database::get()->querySingle("SELECT COUNT(*) AS count FROM faq")->count;
    if ($count_faq > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Initialize copyright/license global arrays
 */
function copyright_info($cid, $noImg=1) {

    global $language, $license, $themeimg;

    $lang = langname_to_code($language);

    $lic = Database::get()->querySingle("SELECT course_license FROM course WHERE id = ?d", $cid)->course_license;
    if (($lic == 0) or ($lic >= 10)) {
        $link_suffix = '';
    } else {
        if ($language != 'en') {
            $link_suffix = 'deed.' . $lang;
        } else {
            $link_suffix = '';
        }
    }
    if ($noImg == 1) {
        $link = "<a href='" . $license[$lic]['link'] . "$link_suffix'><img src='$themeimg/" . $license[$lic]['image'] . ".png' title='" . $license[$lic]['title'] . "' alt='" . $license[$lic]['title'] . "' /></a><br>";
    } else if ($noImg == 0) {
        $link = "";
    }

    return $link . q($license[$lic]['title']);
}

/**
 * Drop in replacement for rand() or mt_rand().
 *
 * @param int $min [optional]
 * @param int $max [optional]
 * @return int
 */
function crypto_rand_secure($min = null, $max = null) {
    require_once('lib/srand.php');
    // default values for optional min/max
    if ($min === null)
        $min = 0;
    if ($max === null)
        $max = getrandmax();
    else
        $max += 1; // for being inclusive

    $range = $max - $min;
    if ($range <= 0)
        return $min; // not so random...
    $log = log($range, 2);
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(secure_random_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}


/**
 * Return a javascript code snippet to protect the page from being framed.
 *
 * return string
 */
function framebusting_code() {
    return '
    <!-- Framebusting code follows -->
    <style id="antiClickjack">body{display:none !important;}</style>
    <script type="text/javascript">
        if (self === top) {
            var antiClickjack = document.getElementById("antiClickjack");
            antiClickjack.parentNode.removeChild(antiClickjack);
        } else {
            top.location = self.location;
        }
    </script>
    ';
}


/**
 * Sets the X-Frame-Options header to disallow framing the web pages of
 * platform. The SAMEORIGIN option is used in order to allow framing from
 * other web pages of the platform in case this functionality is needed.
 */
function add_framebusting_headers() {
    header('X-Frame-Options: SAMEORIGIN');
}

/**
 * This header enables the Cross-site scripting (XSS) filter built into most
 * recent web browsers. It's usually enabled by default anyway, so the role of
 * this header is to re-enable the filter for this particular website if it
 * was disabled by the user.
 */

function add_xxsfilter_headers() {
    header('X-XSS-Protection: 1; mode=block');
}


/**
 * The nosniff header, prevents Internet Explorer and Google Chrome from
 * MIME-sniffing a response away from the declared content-type.
 */

function add_nosniff_headers() {
    header('X-Content-Type-Options: nosniff');
}

/**
 * HTTP Strict-Transport-Security (HSTS) enforces secure (HTTP over SSL/TLS)
 * connections to the server.
 */

function add_hsts_headers() {
    header('Strict-Transport-Security: max-age=16070400');
}

/**
 *
 * @param int $num_bytes [optional]
 * @return string
*/
function generate_csrf_token($num_bytes = 16) {
    require_once('lib/srand.php');

    return bin2hex(secure_random_bytes($num_bytes));
}

/**
 * Check the validity of the csrf token for the current session.
 *
 * @param string $token
 * @return Boolean
 */
function validate_csrf_token($token) {

    if ($token !== $_SESSION['csrf_token']) {
        return False;
    }
    return True;
}

/**
* Generate an input form field for the csrf token.
*
* return string
*/
function generate_csrf_token_form_field()
{
    return "<input type='hidden' name='token' value='{$_SESSION['csrf_token']}' />";
}

function generate_csrf_token_link_parameter()
{
    return "token={$_SESSION['csrf_token']}";
}

function csrf_token_error() {
   redirect_to_home_page();
}






/**
 * Indirect Reference to Direct Reference Map
 *
 * @return ArrayObject
 */
function getIndirectReferencesMap(){
    if(!isset($_SESSION['IRMAP']) || !isset($_SESSION['DRMAP'])){
        $_SESSION['IRMAP'] = new ArrayObject();
        $_SESSION['DRMAP'] = new ArrayObject();
    }
    return $_SESSION['IRMAP'];
}

/**
 * Direct Reference to Indirect Reference Map
 *
 * @return ArrayObject
 */
function getDirectReferencesMap(){
    if(!isset($_SESSION['IRMAP']) || !isset($_SESSION['DRMAP'])){
        $_SESSION['IRMAP'] = new ArrayObject();
        $_SESSION['DRMAP'] = new ArrayObject();
    }
    return $_SESSION['DRMAP'];
}

/**
 * Simple Random Number Generation for indirect References
 *
 * @param int
 * @return string
 */
function getIndirectRandom($length) {
    $allowable_characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    $pass = "";
    for ($i = 0; $i < $length; $i++) {
        $nextChar = $allowable_characters[mt_rand(0, strlen($allowable_characters) - 1)];
        $pass .= $nextChar;
    }
    return $pass;
}

/**
 * hash for object references
 *
 * @param object reference
 * @return string
 */
function directHash($direct) {
    return md5(serialize($direct));
}


/**
 * Direct reference to Indirect reference
 *
 * @param object reference
 * @return string
 */
function getIndirectReference($directReference){
    if(getDirectReferencesMap()->offsetExists(directHash($directReference))){
        return getDirectReferencesMap()->offsetGet(directHash($directReference));
    }
    else{
        $indirect = null;
        do {
            $indirect = getIndirectRandom(6);
        } while (getIndirectReferencesMap()->offsetExists($indirect));
        getIndirectReferencesMap()->offsetSet($indirect, $directReference);
        getDirectReferencesMap()->offsetSet(directHash($directReference), $indirect);
        return $indirect;
    }
}

/**
 * Indirect reference to direct reference
 *
 * @param string
 * @return object reference
 */
function getDirectReference($indirectReference){

    if (!empty($indirectReference) && getIndirectReferencesMap()->offsetExists($indirectReference) )
    {
        return getIndirectReferencesMap()->offsetGet($indirectReference);
    }
}

/**
 * Indirect reference to direct reference, Delete any relevant record
 *
 * @param string
 * @return object reference
 */
function getAndUnsetDirectReference($indirectReference){
    $direct = getDirectReference($indirectReference);
    getIndirectReferencesMap()->offsetUnset($indirectReference);
    getDirectReferencesMap()->offsetUnset(directHash($direct));
    return $direct;
}


/**
 * @brief returns HTTP 403 status code
 * @param type $path
 */
function forbidden($path = '') {
    if (empty($path)) {
        $path = $_SERVER['SCRIPT_NAME'];
    }
    header("HTTP/1.0 403 Forbidden");
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
    '<title>403 Forbidden</title></head><body>',
    '<h1>Forbidden</h1><p>You don\'t have permission to acces the requested path "',
    htmlspecialchars($path),
    '".</p></body></html>';
    exit;
}

/**
 * @brief returns HTML for an buttons
 * @param array $options options for each entry
 *
 * Each item in array is another array of the attributes for button:
 *
 */
function form_buttons($btnArray) {

    global $langCancel;

    $buttons = "";

    foreach ($btnArray as $btn){

        if(!isset($btn['show']) || (isset($btn['show']) && $btn['show'] == true)){

        $id = isset($btn['id'])?"id='$btn[id]'": '';
        $custom_field = isset($btn['custom_field'])?"onclick='$btn[custom_field]'": '';
        if (isset($btn['icon'])) {
            $text = "<span class='fa $btn[icon] space-after-icon'></span>" . $text;
        }

        if (isset($btn['href'])) {
            $class = isset($btn['class']) ? $btn['class'] : 'btn-default';
            $title = isset($btn['title'])?"title='$btn[title]'": '';
            $text = isset($btn['text'])? $btn['text']: $langCancel;
            $target = isset($btn['target'])?"target='$btn[target]'": '';
            $javascript = isset($btn['javascript'])?"onclick=\"$btn[javascript]\"": '';
            $buttons .= "<a class='btn $class' $id href='$btn[href]' $target $title $javascript $custom_field>$text</a>&nbsp;&nbsp;";
        } elseif(!isset($btn['href']) && isset($btn['javascript'])) {
            $class = isset($btn['class']) ? $btn['class'] : 'btn-primary';
            $type = isset($btn['type'])?"type='$btn[type]'":'type="submit"';
            $name = isset($btn['name'])?"name='$btn[name]'": null;
            $value = isset($btn["value"])?"value='$btn[value]'": null;
            $javascript = isset($btn['javascript'])?"onclick=\"$btn[javascript]\"": '';
            $buttons .= "<input class='btn $class' $type $id $name $value $custom_field $javascript />&nbsp;&nbsp;";
        } else {
            $class = isset($btn['class']) ? $btn['class'] : 'btn-primary';
            $type = isset($btn['type'])?"type='$btn[type]'":'type="submit"';
            $text = isset($btn['text'])? $btn['text']: '';
            $name = isset($btn['name'])?"name='$btn[name]'": null;
            $value = isset($btn["value"])?"value='$btn[value]'": null;
            $disabled = isset($btn['disabled'])?"disabled='$btn[disabled]'": '';
            $buttons .= "<button class='btn $class' $type $id $name $value $custom_field $disabled>$text</button>&nbsp;&nbsp;";
        }
    }
    }

    return $buttons;
}

/**
 * @brief returns HTML for an action bar
 * @param array $options options for each entry in bar
 *
 * Each item in array is another array of the form:
 * array('title' => 'Create', 'url' => '/create.php', 'icon' => 'create', 'level' => 'primary')
 * level is optional and can be 'primary' for primary entries or unset
 */
function action_bar($options, $page_title_flag = true, $secondary_menu_options = array()) {
    global $langConfirmDelete, $langCancel, $langDelete, $pageName;

    $out_primary = $out_secondary = array();
    $i=0;
    $page_title = "";
    if (isset($pageName) and !empty($pageName) and $page_title_flag) {
        $page_title = "<div class='pull-left' style='padding-top:15px;'><h4>".q($pageName)."</h4></div>";
    }
    foreach (array_reverse($options) as $option) {
        // skip items with show=false
        if (isset($option['show']) and !$option['show']) {
            continue;
        }
        $class = isset($option['class']) ? " ".$option['class'] : '';
        $wrapped_class = isset($option['class']) ? " class='$option[class]'" : '';
        $url = isset($option['url']) ? $option['url'] : "#";
        $title = q($option['title']);
        $level = isset($option['level'])? $option['level']: 'secondary';
        if (isset($option['confirm'])) {
            $title_conf = isset($option['confirm_title']) ? $option['confirm_title'] : $langConfirmDelete;
            $accept_conf = isset($option['confirm_button']) ? $option['confirm_button'] : $langDelete;
            $confirm_extra = " data-title='$title_conf' data-message='" .
                q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept_conf' data-action-class='btn-danger'";
            $confirm_modal_class = ' confirmAction';
            $form_begin = "<form method=post action='$url'>";
            $form_end = '</form>';
            $href = '';
        } else {
            $confirm_extra = $confirm_modal_class = $form_begin = $form_end = '';
            $href = " href='$url'";
        }
        if (!isset($option['button-class'])) {
            $button_class = 'btn-default';
        } else {
            $button_class = $option['button-class'];
        }
        if (isset($option['link-attrs'])) {
            $link_attrs = " ".$option['link-attrs'];
        } else {
            $link_attrs = "";
        }
        $caret = '';
        $primaryTag = 'a';
        if ($level != 'primary-label' or isset($option['icon'])) {
            $dataAttrs = "data-placement='bottom' data-toggle='tooltip'";
        } else {
            $dataAttrs = '';
        }
        $subMenu = '';
        if (isset($option['options']) and ($level == 'primary' or $level == 'primary-label')) {
            $href = '';
            $primaryTag = 'button';
            $button_class .= ' dropdown-toggle';
            $caret = ' <span class="caret"></span>';
            $dataAttrs = 'data-toggle="dropdown" data-placement="right" aria-haspopup="true" aria-expanded="false"';
            $form_begin = '<div class="btn-group" role="group">';
            $form_end = '</div>';
            $subMenu = '<ul class="dropdown-menu dropdown-menu-right">';
            foreach ($option['options'] as $subOption) {
               $subMenu .= '<li><a class="'.$subOption['class'].'" href="' . $subOption['url'] . '">';
               $subMenu .= isset($subOption['icon']) ? '<span class="'.$subOption['icon'].'"></span>' : '';
               $subMenu .= q($subOption['title']) . '</a></li>';

            }
            $subMenu .= '</ul>';
        }
        $iconTag = '';
        if ($level == 'primary-label') {
            if (isset($option['icon'])) {
                $iconTag = "<span class='fa $option[icon] space-after-icon'></span>";
                $link_attrs .= " title='$title'";
                $title = "<span class='hidden-xs'>$title</span>";
            }
            array_unshift($out_primary,
                "$form_begin<$primaryTag$confirm_extra class='btn $button_class$confirm_modal_class$class'" . $href .
                ' ' . $dataAttrs .
                " $link_attrs>" . $iconTag . $title . $caret .
                "</$primaryTag>$subMenu$form_end");
        } elseif ($level == 'primary') {
            if (isset($option['icon'])) {
                $iconTag = "<span class='fa $option[icon]'></span>";
            }
            array_unshift($out_primary,
                "$form_begin<$primaryTag$confirm_extra class='btn $button_class$confirm_modal_class'" . $href .
                ' ' . $dataAttrs .
                " title='$title'$link_attrs>" . $iconTag . $caret .
                "</$primaryTag>$subMenu$form_end");
        } else {
            array_unshift($out_secondary,
                "<li$wrapped_class>$form_begin<a$confirm_extra  class='$confirm_modal_class'" . $href .
                " $link_attrs>" .
                "<span class='fa $option[icon]'></span> $title</a>$form_end</li>");
        }
        $i++;
    }
    $out = '';
    if (count($out_primary)) {
        $out .= implode('', $out_primary);
    }

    $action_button = '';
    $secondary_title = isset($secondary_menu_options['secondary_title']) ? $secondary_menu_options['secondary_title'] : "";
    $secondary_icon = isset($secondary_menu_options['secondary_icon']) ? $secondary_menu_options['secondary_icon'] : "fa-gears";
    if (count($out_secondary)) {
        $action_button .= "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-expanded='false'><span class='fa $secondary_icon'></span> <span class='hidden-xs'>$secondary_title</span> <span class='caret'></span><span class='hidden'>.</span></button>";
        $action_button .= "  <ul class='dropdown-menu dropdown-menu-right' role='menu'>
                     ".implode('', $out_secondary)."
                  </ul></div>";
    }
    if ($out && $i!=0) {
        return "<div class='row action_bar'>
                    <div class='col-sm-12 clearfix'>
                        $page_title
                        <div class='margin-top-thin margin-bottom-fat pull-right'>
                            <div class='btn-group'>
                            $out
                            $action_button
                            </div>
                        </div>
                    </div>
                </div>";
    } else {
        return '';
    }
}

/**
 * @brief returns HTML for an action button
 * @param array $options options for each entry in the button
 *
 * Each item in array is another array of the form:
 * array('title' => 'Create', 'url' => '/create.php', 'icon' => 'create', 'class' => 'primary danger')
 *
 */
function action_button($options, $secondary_menu_options = array()) {
    global $langConfirmDelete, $langCancel, $langDelete;
    $out_primary = $out_secondary = array();
    $primary_form_begin = $primary_form_end = $primary_icon_class = '';
    foreach (array_reverse($options) as $option) {
        $level = isset($option['level'])? $option['level']: 'secondary';
        // skip items with show=false
        if (isset($option['show']) and !$option['show']) {
            continue;
        }
        if (isset($option['class'])) {
            $class = ' ' . $option['class'];
        } else {
            $class = '';
        }
        if (isset($option['btn_class'])) {
            $btn_class = ' ' . $option['btn_class'];
        } else {
            $btn_class = ' btn-default';
        }
        if (isset($option['link-attrs'])) {
            $link_attrs = ' ' . $option['link-attrs'];
        } else {
            $link_attrs = '';
        }
        $disabled = isset($option['disabled']) && $option['disabled'] ? ' disabled' : '';
        $icon_class = "class='list-group-item $class$disabled";
        if (isset($option['icon-class'])) {
            $icon_class .= " " . $option['icon-class'];
        }
        if (isset($option['confirm'])) {
            $title = q(isset($option['confirm_title']) ? $option['confirm_title'] : $langConfirmDelete);
            $accept = isset($option['confirm_button']) ? $option['confirm_button'] : $langDelete;
            $form_begin = "<form method=post action='$option[url]'>";
            $form_end = '</form>';
            if ($level == 'primary-label' or $level == 'primary') {
                $primary_form_begin = $form_begin;
                $primary_form_end = $form_end;
                $form_begin = $form_end = '';
                $primary_icon_class = " confirmAction' data-title='$title' data-message='" .
                    q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept' data-action-class='btn-danger'";
            } else {
                $icon_class .= " confirmAction' data-title='$title' data-message='" .
                    q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept' data-action-class='btn-danger'";
                $primary_icon_class = '';
            }
            $url = '#';
        } else {
            $icon_class .= "'";
            $confirm_extra = $form_begin = $form_end = '';
            $url = isset($option['url'])? $option['url']: '#';
        }
        if (isset($option['icon-extra'])) {
            $icon_class .= ' ' . $option['icon-extra'];
        }

        if ($level == 'primary-label') {
            array_unshift($out_primary, "<a href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon] space-after-icon$primary_icon_class'></span>" . q($option['title']) . "<span class='hidden'>.</span></a>");
        } elseif ($level == 'primary') {
            array_unshift($out_primary, "<a data-placement='bottom' data-toggle='tooltip' title='" . q($option['title']) . "' href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon]$primary_icon_class'></span><span class='hidden'>.</span></a>");
        } else {
            array_unshift($out_secondary, $form_begin . icon($option['icon'], $option['title'], $url, $icon_class.$link_attrs, true) . $form_end);
        }
    }
    $primary_buttons = "";
    if (count($out_primary)) {
        $primary_buttons = implode('', $out_primary);
    }
    $action_button = "";
    $secondary_title = isset($secondary_menu_options['secondary_title']) ? $secondary_menu_options['secondary_title'] : "<span class='hidden'>.</span>";
    $secondary_icon = isset($secondary_menu_options['secondary_icon']) ? $secondary_menu_options['secondary_icon'] : "fa-gear";
    $secondary_btn_class = isset($secondary_menu_options['secondary_btn_class']) ? $secondary_menu_options['secondary_btn_class'] : "btn-default";
    if (count($out_secondary)) {
        $action_list = q("<div class='list-group' id='action_button_menu'>".implode('', $out_secondary)."</div>");
        $action_button = "
                <a tabindex='1' class='menu-popover btn $secondary_btn_class' data-container='body' data-trigger='manual' data-html='true' data-placement='bottom' data-content='$action_list'>
                    <span class='fa $secondary_icon'></span> <span class='hidden-xs'>$secondary_title</span> <span class='caret'></span>
                </a>";
    }

    return $primary_form_begin .
         "<div class='btn-group btn-group-sm' role='group' aria-label='...'>
                $primary_buttons
                $action_button
          </div>" . $primary_form_end;
}

/**
 * Removes spcific get variable from Query String
 *
 */
function removeGetVar($url, $varname) {
    list($urlpart, $qspart) = array_pad(explode('?', $url), 2, '');
    parse_str($qspart, $qsvars);
    unset($qsvars[$varname]);
    $newqs = http_build_query($qsvars);
    return $urlpart . '?' . $newqs;
}

function recurse_copy($src, $dst) {
    $dir = opendir($src);
    make_dir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Shortcut function to create directories consistently
function make_dir($dir) {
    return @mkdir($dir, 0755, true);
}

function setOpenCoursesExtraHTML() {
    global $urlAppend, $openCoursesExtraHTML, $langListOpenCourses,
        $langOpenCoursesShort, $langListOpenCoursesShort,
        $langNumOpenCourseBanner, $langNumOpenCoursesBanner, $themeimg;
    $openCoursesNum = Database::get()->querySingle("SELECT COUNT(id) as count FROM course_review WHERE is_certified = 1")->count;
    if ($openCoursesNum > 0) {
        $openFacultiesUrl = $urlAppend . 'modules/course_metadata/openfaculties.php';
        $openCoursesExtraHTML = "
            <div class='inner_opencourses'>
                <div class='row'>
                    <div class='col-xs-6 col-xs-offset-3 col-md-12 col-md-offset-0'>
                        <img class='img-responsive center-block' src='$themeimg/banner_open_courses.png' alt='".q($langListOpenCourses)."'>
                    </div>
                </div>
                <div class='clearfix'>
                    <div class='row num_sub_wrapper center-block clearfix'>
                        <div class='col-xs-6 col-md-5 opencourse_num'><div class='pull-right'>$openCoursesNum</div></div>
                        <div class='col-xs-6 col-md-7 opencourse_num_text'>
                            <a target='_blank' href='$openFacultiesUrl'>
                            <div class='pull-left'>
                                <span class='opencourse_sub'>" .
                                    (($openCoursesNum == 1)? $langNumOpenCourseBanner: $langNumOpenCoursesBanner) . "
                                </span>
                                <span class='opencourse_triangle'></span>
                            </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>";
    }
}

/**
 * @brief returns the appropriate translation of a message depending on the current language
 * @param string $message either simple string or serialized array of [ $langCode => $locMessage ]
 * @param string $lang [optional] language to use for localization - if unset, uses global $lang
 */
function getSerializedMessage($message, $lang=null) {
    global $language;

    if (!isset($lang)) {
        $lang = $language;
    }

    // Message is simple string, not serialized array - just return it
    if (!($data = @unserialize($message))) {
        return $message;
    } else {
        if (isset($data[$lang])) {
            return $data[$lang]; // return requested language if possible...
        } elseif (isset($data['en'])) {
            return $data['en']; // ... else return English message if possible...
        } elseif (isset($data['el'])) {
            return $data['el']; // ... else return Greek message
        }
    }
    return '';
}

/**
 * @brief Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
 * @return int
 */
function fileUploadMaxSize() {
    static $max_size;

    if (!isset($max_size)) {
        // Start with post_max_size.
        $max_size = parseSize(ini_get('post_max_size'));

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parseSize(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function parseSize($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

/**
 * @brief Include JavaScript code to check file upload size
 */
function enableCheckFileSize() {
    global $langMaxFileSizeExceeded, $head_content;
    load_js('tools.js');
    $head_content .= "
<script>
var langMaxFileSizeExceeded = '" . js_escape($langMaxFileSizeExceeded) . "';
$(enableCheckFileSize);
</script>
";
}

/**
 * @brief Return the HTML code for a hidden input setting the max upload size
 * @return string
 */
function fileSizeHidenInput() {
    return "<input type='hidden' name='MAX_FILE_SIZE' value='" . fileUploadMaxSize() . "'>";
}


/**
 * @brief Return the HTML code for a link back to the current Documents page
 * @param string $path Path of the current documents directory
 * @return string
 */
function documentBackLink($path) {
    global $upload_target_url, $groupset;

    $opts = '';
    if ($groupset) {
        $opts = $groupset;
    }
    if ($path) {
        $opts .= ($opts? '&amp;': '') . "openDir=$path";
    }
    if ($opts) {
        $initial = defined('COMMON_DOCUMENTS') || defined('MY_DOCUMENTS');
        return $upload_target_url . ($initial? '?': '&amp;') . $opts;
    } else {
        return $upload_target_url;
    }
}

function stringStartsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function stringEndsWith($haystack, $needle) {
    return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

/**
 * @brief Define the RSS constant, used by the template system, to the module's RSS link
 */
function define_rss_link() {
    global $uid, $course_code, $course_id, $module_id, $modules;

    $module_name = $modules[$module_id]['link'];
    $link = 'modules/' . $module_name . '/rss.php?c=' . $course_code;
    $course_status = course_status($course_id);

    if ($course_status == COURSE_INACTIVE) {
        return;
    } elseif ($course_status != COURSE_OPEN or
              $_SESSION['courses'][$course_code]) {
        $link .= '&amp;uid=' . $uid .  '&amp;token=' .
            token_generate($module_name . $uid . $course_code);
    }

    define('RSS', $link);
}

/**
 * @brief Check whether an RSS link token is valid for the current module and user
 */
function rss_token_valid($token, $uid) {
    global $course_code, $course_id, $module_id, $modules;

    if (!token_validate($modules[$module_id]['link'] . $uid . $course_code, $token)) {
        return false;
    }
    $q = Database::get()->querySingle('SELECT status FROM course_user
        WHERE course_id = ?d AND user_id = ?d', $course_id, $uid);
    if (!$q or !$q->status) {
        return false;
    }
    return true;
}

function rss_check_access() {
    global $course_code, $course_id, $course_status, $module_id;

    if (isset($_GET['c'])) {
        $course_code = $_GET['c'];
        $course_id = course_code_to_id($course_code);
        $course_status = course_status($course_id);
    } else {
        $course_code = '';
        $course_id = false;
    }
    if ($course_id === false) {
        header("HTTP/1.0 404 Not Found");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
            '<title>404 Not Found</title></head><body>',
            '<h1>Not Found</h1><p>The requested course "',
            htmlspecialchars($course_code),
            '" does not exist.</p></body></html>';
        exit;
    }
    if ($course_status == COURSE_INACTIVE or
        !visible_module($module_id) or
        ($course_status != COURSE_OPEN and
         !(isset($_GET['token']) and isset($_GET['uid']) and
         rss_token_valid($_GET['token'], $_GET['uid'])))) {
        forbidden($_SERVER['REQUEST_URI']);
    }
}


/**
 * @brief Return the directory name of a path
 *
 * Makes sure that / is used as directory separator, returns empty for root directory
 */
function my_dirname($path) {
    $path = str_replace('\\', '/', dirname($path));
    if ($path == '/') {
        return '';
    }
    return $path;
}

/*
 * check extension and  write  if exist  in a  <LI></LI>
 * @params string       $extensionName  name  of  php extension to be checked
 * @params boolean      $echoWhenOk     true => show ok when  extension exist
 * @author Christophe Gesche
 * @desc check extension and  write  if exist  in a  <LI></LI>
 */
function warnIfExtNotLoaded($extensionName) {

    global $tool_content, $langModuleNotInstalled, $langReadHelp, $langHere;

    if (extension_loaded($extensionName)) {
        $tool_content .= '<li>' . icon('fa-check') . ' ' . $extensionName . '</li>';
    } else {
        $tool_content .= "
                <li class='bg-danger'>" . icon('fa-times') . " $extensionName
                <b>$langModuleNotInstalled</b>
                (<a href='http://www.php.net/$extensionName' target=_blank>$langReadHelp $langHere</a>)
                </li>";
    }
}


/**
 * @brief IP address and IP CIDR validation functions
 *
 */
function isIPv4($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function isIPv4cidr($ip) {
    return preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/([0-9]|[1-2][0-9]|3[0-2]))$/", $ip);
}

function isIPv6($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

function isIPv6cidr($ip) {
    return preg_match("/^((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?(\/(\d|\d\d|1[0-1]\d|12[0-8]))$/", $ip);
}

function ip_v4_cidr_match($ip, $range) {
    list ($subnet, $bits) = explode('/', $range);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
    return ($ip & $mask) == $subnet;
}

// converts inet_pton output to string with bits
function inet_to_bits($inet) {
   $unpacked = unpack('A16', $inet);
   $unpacked = str_split($unpacked[1]);
   $binaryip = '';
   foreach ($unpacked as $char) {
             $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
   }
   return $binaryip;
}

function ip_v6_cidr_match($ip, $range) {
    $ip = inet_pton($ip);
    $binaryip=inet_to_bits($ip);

    list($net,$maskbits)=explode('/',$range);
    $net=inet_pton($net);
    $binarynet=inet_to_bits($net);

    $ip_net_bits = substr($binaryip,0,$maskbits);
    $net_bits    = substr($binarynet,0,$maskbits);

    return $ip_net_bits == $net_bits;
}

function match_ip_to_ip_or_cidr($ip, $ips_or_cidr_array) {
    if (isIPv4($ip)) {
        foreach ($ips_or_cidr_array as $ip_or_cidr) {
            if (isIPv4cidr($ip_or_cidr)) {
                if (ip_v4_cidr_match($ip, $ip_or_cidr)) return true;
            } elseif (isIPv4($ip_or_cidr)) {
                if ($ip == $ip_or_cidr) return true;
            }
        }
    } else {
        foreach ($ips_or_cidr_array as $ip_or_cidr) {
            if (isIPv6cidr($ip_or_cidr)) {
                if (ip_v6_cidr_match($ip, $ip_or_cidr)) return true;
            } elseif (isIPv6($ip_or_cidr)) {
                if ($ip == $ip_or_cidr) return true;
            }
        }
    }
    return false;
}

/**
 * Get nearest value from specific key of a multidimensional array
 *
 * @param $key integer
 * @param $arr array
 * @return array
 */
function closest($search, $arr) {
   $closest = null;
   $position = null;

   foreach($arr as $key => $item) {
       if ($closest == null || abs($search - $closest) > abs($item - $search)) {
           $closest = $item;
           $position = $key;
       }
   }
   return array('key' => $position, 'value' => $closest);
}

/**
 * Get all values from specific key in a multidimensional array
 *
 * @param $key string
 * @param $arr array
 * @return null|string|array
 */
function array_value_recursive($key, array $arr){
    $val = array();
    array_walk_recursive($arr, function($v, $k) use($key, &$val){
        if($k == $key) array_push($val, $v);
    });
    return count($val) > 1 ? $val : array_pop($val);
}

/**
 * Function called whenever a user is created or changed
 *
 * @param $user_id integer
 */
function user_hook($user_id) {
    // Apply autoenroll rules
    $status = Database::get()->querySingle('SELECT status FROM user WHERE id = ?d', $user_id)->status;
    Database::get()->queryFunc('SELECT id FROM autoenroll_rule, autoenroll_rule_department
        WHERE status = ?d AND
              autoenroll_rule.id = autoenroll_rule_department.rule AND
              autoenroll_rule_department.department IN
                (SELECT department FROM user_department WHERE user = ?d)
        UNION
        SELECT id FROM autoenroll_rule
            LEFT JOIN autoenroll_rule_department
                ON autoenroll_rule.id = autoenroll_rule_department.rule
            WHERE rule IS NULL AND
                  status = ?d',
        function ($rule) use ($user_id) {
            $id = $rule->id;
            Database::get()->query('INSERT IGNORE INTO course_user
                (course_id, user_id, status, reg_date, document_timestamp)
                (SELECT course_id, ?d, ?d, NOW(), NOW()
                    FROM autoenroll_course
                    WHERE rule = ?d)', $user_id, USER_STUDENT, $id);
            Database::get()->query('INSERT IGNORE INTO course_user
                (course_id, user_id, status, reg_date, document_timestamp)
                (SELECT course, ?d, ?d, NOW(), NOW()
                    FROM autoenroll_department, course_department
                    WHERE department_id = department AND
                          rule = ?d)', $user_id, USER_STUDENT, $id);
        }, $status, $user_id, $status);
}

/**
 * @brief Check whether an email address is valid
 *
 * @param string $email - Email address to check
 */
function valid_email($email) {
    static $validator, $validation;

    if (!isset($validator)) {
        $validator = new Egulias\EmailValidator\EmailValidator();
        $validation = new Egulias\EmailValidator\Validation\RFCValidation();
    }

    return $validator->isValid($email, $validation);
}

/**
 * @brief Display a message if course is under not-allowed department
 *
 * @param boolean $prompt - Provide a link to course settings if true
 */
function warnCourseInvalidDepartment($prompt=false) {
    global $course_id, $course_code, $urlAppend, $langCourseInvalidDepartment,
        $langCourseInvalidDepartmentPrompt;
    if (Database::get()->querySingle("SELECT department
            FROM course_department, hierarchy
            WHERE course_department.department = hierarchy.id AND
                  hierarchy.allow_course = 0 AND
                  course = ?d
            LIMIT 1", $course_id)) {
        if ($prompt) {
            $message = sprintf($langCourseInvalidDepartment . ' ' . $langCourseInvalidDepartmentPrompt,
                "<a href='{$urlAppend}modules/course_info/?course=$course_code'>", '</a>');
        } else {
            $message = $langCourseInvalidDepartment;
        }
        Session::Messages($message);
    }
}

/**
 * @brief Function called for every user login
 *
 * For now, only called for CAS automatic registration to determine actual user
 * details. If function local_register_hook() is defined, calls that instead.
 *
 * @param array $options - User creation options. Possible key-value pairs are:
 * 'departments' - List of department id's requested by user
 * 'attributes'  - List of attributes retrieved via LDAP / CAS / Shibboleth
 * 'am'          - Student id number retrieved via LDAP / CAS / Shibboleth
 * 'user_id'     - Null for registration, current user id for subsequent logins
 * @return array - Actual user creation options. Key-value pairs are:
 * 'accept'      - Boolean - if false, user should be rejected
 * 'departments' - List of department id's user should be added to
 * 'status'      - User status (USER_STUDENT, USER_TEACHER)
 * 'am'          - Student id number
 */
function login_hook($options) {
    if (!isset($options['am'])) {
        $options['am'] = '';
    }
    if (!isset($options['departments'])) {
        $options['departments'] = array();
    }
    if (!isset($options['status'])) {
        $options['status'] = USER_STUDENT;
    }
    $options['accept'] = true;

    if (function_exists('local_login_hook')) {
        return local_login_hook($options);
    } else {
        return $options;
    }
}


/**
 * Show Second Factor Initialization Dialog in User Profile
 *
 * @return string
 */

function showSecondFactorUserProfile(){
    global $langSFAConf;
    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled() == true ){
        return "<div class='form-group'>
                  <label class='col-sm-2 control-label'>" . $langSFAConf . "</label>
                  <div class='col-sm-4'>". secondfaApp::showUserProfile($_SESSION['uid']) . "</div>
                </div>";
    } else {
        return "";
    }
}

/**
 * Save Second Factor Initialization in User Profile
 *
 * @param  POST variables
 * @return string
 */
function saveSecondFactorUserProfile(){
    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled() == true ){
        return secondfaApp::saveUserProfile($_SESSION['uid']);
    } else {
        return "";
    }
}


/**
 * Show Second Factor Challenge
 *
 * @return string
 */
function showSecondFactorChallenge(){
    global $langSFAType;
    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled() == true ){
        $challenge = secondfaApp::showChallenge($_SESSION['uid']);
        if ($challenge!=""){
            return "<div class='form-group'>
                    <label class='col-sm-2 control-label'>" . $langSFAType . "</label>
                    <div class='col-sm-4'>". $challenge . "</div>
                    </div>";
        }else{
            return "";
        }
    } else {
        return "";
    }
}

/**
 * Verify Second Factor Challenge
 *
 * @param  POST variables
 * @return string
 */
function checkSecondFactorChallenge(){
    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled() == true ){
        return secondfaApp::checkChallenge($_SESSION['uid']);
    } else {
        return "";
    }
}
