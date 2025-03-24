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


/**
 * @file main.lib.php
 * @brief General useful functions for eClass
 * Standard header included by all eClass files
 * Defines standard functions and validates variables
 */

define('ECLASS_VERSION', '4.0.2');

// mPDF library temporary file path and font path
if (isset($webDir)) { // needed for avoiding 'notices' in some files
    define("_MPDF_TEMP_PATH", $webDir . '/courses/temp/pdf/');
    define("_MPDF_TTFONTDATAPATH", $webDir . '/courses/temp/pdf/');
}
require_once 'constants.php';
require_once 'log.class.php';
require_once 'lib/session.class.php';
require_once 'lib/file_cache.class.php';

// ----------------------------------------------------------------------
// for safety reasons use the functions below
// ---------------------------------------------------------------------

// Shortcut for htmlspecialchars()
function q($s) {
    if (is_null($s)) {
        return '';
    } else {
        return htmlspecialchars($s, ENT_QUOTES);
    }
}

// Escape HTML special characters and expand math tags
function q_math($s) {
    global $urlAppend;
    $text = preg_replace_callback('/\[m\].*?\[\/m\]/s', 'math_unescape', q($s));
    return mathfilter($text, 12, $urlAppend . 'courses/mathimg/');
}

// Escape string to use as JavaScript argument
function js_escape($s) {
    return q(str_replace(["'", "\n"], ["\\'", '\n'], canonicalize_whitespace($s)));
}

function js_link($file) {
    global $urlAppend;
    $v = '?v=' . CACHE_SUFFIX;
    if (strpos($file, 'node_modules') === 0) {
        $root = 'node_modules';
    } else {
        $root = 'js';
    }
    return "<script type='text/javascript' src='{$urlAppend}$root/$file$v'></script>\n";
}

function css_link($file) {
    global $urlAppend;
    $v = '?v=' . CACHE_SUFFIX;
    return "<link href='{$urlAppend}js/$file$v' rel='stylesheet' type='text/css'>\n";
}
function widget_js_link($file, $folder) {
    global $urlAppend, $head_content;
    $v = '?v=' . ECLASS_VERSION;
    $head_content .= "<script type='text/javascript' src='$urlAppend{$folder}/js/$file$v'></script>\n";
}

function widget_css_link($file, $folder) {
    global $urlAppend, $head_content;
    $v = '?v=' . ECLASS_VERSION;
    $head_content .= "<link href='$urlAppend{$folder}/css/$file$v' rel='stylesheet' type='text/css'>\n";
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
            $langReadMore, $langReadLess, $langViewHide, $langViewShow, $urlAppend, $webDir;
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
            $head_content .= css_link('datatables/media/css/override_jquery.dataTables.css?v=4.0-dev');
            $file = 'datatables/media/js/jquery.dataTables.min.js';
        } elseif ($file == 'datatables_bootstrap') {
            $head_content .= css_link('datatables/media/css/dataTables.bootstrap.css');
            $file = 'datatables/media/js/dataTables.bootstrap.js';
        } elseif ($file == 'datatables_tabletools') {
            $head_content .= css_link('datatables/extensions/TableTools/css/dataTables.tableTools.css');
            $file = 'datatables/extensions/TableTools/js/dataTables.tableTools.js';
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
            css_link('select2-4.0.3/css/override_select2_design.css?v=4.0-dev') .
            js_link('select2-4.0.3/js/select2.full.min.js');
            $file = "select2-4.0.3/js/i18n/$language.js";
        } elseif ($file == 'bootstrap-calendar') {
            $file = 'bootstrap-calendar-master/js/calendar.js';
            if ($language != 'en') {
                switch ($language) {
                    case 'el': $head_content .= js_link('bootstrap-calendar-master/js/language/el-GR.js'); break;
                    case 'fr': $head_content .= js_link('bootstrap-calendar-master/js/language/fr-FR.js'); break;
                    case 'de': $head_content .= js_link('bootstrap-calendar-master/js/language/de-DE.js'); break;
                    case 'it': $head_content .= js_link('bootstrap-calendar-master/js/language/it-IT.js'); break;
                    case 'es': $head_content .= js_link('bootstrap-calendar-master/js/language/es-ES.js'); break;
                    default: break;
                }
            }
            $head_content .= css_link('bootstrap-calendar-master/css/calendar_small.css');
            $head_content .= "<link href='{$urlAppend}template/modern/css/new_calendar.css' rel='stylesheet' type='text/css'>";
        } elseif ($file == 'bootstrap-datetimepicker') {
            $head_content .= css_link('bootstrap-datetimepicker/css/bootstrap-datetimepicker.css') .
            js_link('bootstrap-datetimepicker/js/bootstrap-datetimepicker.js');
            if ($language != 'en') {
                $file = "bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.$language.js";
            } else {
                $file = "bootstrap-datetimepicker/js/bootstrap-datetimepicker.js";
            }
        } elseif ($file == 'bootstrap-timepicker') {
            $head_content .= css_link('bootstrap-timepicker/css/bootstrap-timepicker.min.css');
            $file = 'bootstrap-timepicker/js/bootstrap-timepicker.min.js';
        } elseif ($file == 'bootstrap-datepicker') {
            $head_content .= css_link('bootstrap-datepicker/css/bootstrap-datepicker3.css') .
            js_link('bootstrap-datepicker/js/bootstrap-datepicker.js');
            if ($language == 'en') {
                $file = "bootstrap-datepicker/locales/bootstrap-datepicker.$language-GB.min.js";
            } else {
                $file = "bootstrap-datepicker/locales/bootstrap-datepicker.$language.min.js";
            }
        } elseif ($file == 'bootstrap-validator') {
            $file = "bootstrap-validator/validator.js";
        } elseif ($file == 'bootstrap-slider') {
            $head_content .= css_link('bootstrap-slider/css/bootstrap-slider.min.css');
            $file = 'bootstrap-slider/js/bootstrap-slider.min.js';
        } elseif ($file == 'bootstrap-colorpicker') {
            $head_content .= css_link('bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css');
            $file = 'bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js';
        } elseif ($file == 'bootstrap-combobox') {
            $head_content .= css_link('bootstrap-combobox/css/bootstrap-combobox.css');
            $file = 'bootstrap-combobox/js/bootstrap-combobox.js';
        } elseif ($file == 'spectrum') {
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
    global $langAnonymous, $urlAppend, $langUserProfile;

    $course_code_link = "";

    if (is_array($user) and count($user) == 0) {
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
        $icon = profile_image($user->id, IMAGESIZE_SMALL, 'img-circle rounded-circle') . '';
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

    $padding_link = "";
    if($icon){
        $padding_link = "padding-top:7px;";
    }
    return "<div class='d-flex justify-content-start align-items-start gap-2'>
                $icon
                <a style='$padding_link' $class_str href='{$urlAppend}main/profile/display_profile.php?id=$user->id$course_code_link&amp;token=$token'
                    data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langUserProfile'>"
                     . $student_name .
                "</a>
            </div>" .
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
 * @brief get registered users to course (except teachers)
 * @param $cid
 * @return array
 */
function get_course_users($cid) {

    $users = array();

    $q = Database::get()->queryArray("SELECT user_id FROM course_user WHERE
                                                        course_id = ?d AND
                                                        status = " . USER_STUDENT . " AND
                                                        tutor = 0 AND
                                                        editor = 0 AND
                                                        reviewer = 0", $cid);
    if (count($q) > 0) {
        foreach ($q as $data) {
            $users[] = $data->user_id;
        }
    }
    return $users;
}

/**
 * @brief Return the URL for a user profile image
 * @param int $uid user id
 * @param int $size optional image size in pixels (IMAGESIZE_SMALL or IMAGESIZE_LARGE)
 * @return string
 */

function user_icon($user_id, $size = IMAGESIZE_SMALL) {
    global $webDir, $themeimg, $urlAppend, $course_id, $is_editor, $uid;

    if (isset($_SESSION['profile_image_cache_buster'])) {
        $suffix = '?v=' . $_SESSION['profile_image_cache_buster'];
    } else {
        $suffix = '';
    }

    $user = Database::get()->querySingle("SELECT has_icon, pic_public
        FROM user WHERE id = ?d", $user_id);
    if ($user and
        ($user->pic_public or $uid == $user_id or
         $_SESSION['status'] == USER_TEACHER or
         (isset($course_id) and $course_id and $is_editor))) {
        $hash = profile_image_hash($user_id);
        $hashed_file = "courses/userimg/{$user_id}_{$hash}_$size.jpg";
        if (file_exists($hashed_file)) {
           return $urlAppend . $hashed_file;
        } elseif (file_exists("courses/userimg/{$user_id}_$size.jpg")) {
           return "{$urlAppend}courses/userimg/{$user_id}_$size.jpg";
        }
    }
    return "$themeimg/default_$size.png$suffix";
}

/**
 * @brief Display links to the groups a user is member of
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
        $visibility = '';
        if (!is_group_visible($r->id, $course_id)) {
            $visibility = 'not_visible';
        }
        if ($format == 'html') {
            $groups .= ((count($q) > 1) ? '<li>' : '') .
                    "<a href='{$urlAppend}modules/group/group_space.php?group_id=$r->id' class='$visibility' aria-label='" .
                    q($r->name) . "'>" .
                    q(ellipsize($r->name, 40)) . "</a>" .
                    ((count($q) > 1) ? '</li>' : '');
        } else {
            $groups .= (empty($groups) ? '' : ', ') . q($r->name);
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
 * @brief get group visibility
 * @param $group_id
 * @param $course_id
 * @return bool
 */
function is_group_visible($group_id, $course_id) {

    $q = Database::get()->querySingle("SELECT visible FROM `group` WHERE
                                        id = ?d AND course_id = ?d", $group_id, $course_id);

    if ($q->visible == 1) {
        return true;
    } else {
        return false;
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
 * @param array $entries an array of (value => label)
 * @param type $name the name of the selection element
 * @param type $default if it matches one of the values, specifies the default entry
 * @param type $extra
 * @return string
 */
function selection($entries, $name, $default = '', $extra = '') {
    global $langSelect;
    $retString = "";
    $retString .= "\n<select class='form-select' name='$name' $extra aria-label='$langSelect'>\n";
    foreach ($entries as $value => $label) {
        if (isset($default) && ($value == $default)) {
            $retString .= "<option selected value='" . q($value) . "'>" .
                    q($label) . "</option>\n";
        } else {
            $retString .= "<option value='" . q($value) . "'>" .
                    q($label) . "</option>\n";
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
            $retString .= "<option selected value='" . q($value) . "'>" .
                    q($label) . "</option>\n";
        } else {
            $retString .= "<option value='" . q($value) . "'>" .
                    q($label) . "</option>\n";
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
function check_guest($id = null) {
    if ($id) {
        $uid = $id;
    } else {
        $uid = $GLOBALS['uid'];
    }
    if (isset($uid) and $uid) {
        $status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $uid);
        if ($status && $status->status == USER_GUEST) {
            return true;
        }
    }
    return false;
}

/**
 * @brief function to check if user is a course editor
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
 * @brief check if user is course reviewer
 * @return bool
 */
function check_course_reviewer() {
    global $uid, $course_id, $is_admin;

    if ($is_admin) {
        return true;
    }

    $s = Database::get()->querySingle("SELECT status, course_reviewer FROM course_user
                                        WHERE user_id = ?d AND
                                        course_id = ?d", $uid, $course_id);
    if ($s and ($s->status == USER_TEACHER or $s->course_reviewer == 1)) {
        return true;
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
 * @brief check if user is registered to specific course
 * @param $uid
 * @param $course_id
 * @return bool
 */

function user_is_registered_to_course($uid, $course_id) {

    $q = Database::get()->querySingle('SELECT status FROM course_user
                                      WHERE user_id = ?d AND course_id = ?d', $uid, $course_id);
    if (!$q or !$q->status) {
           return FALSE;
    } else {
        return TRUE;
    }

}

/**
 * @brief Check if a user with username $login already exists
 * @param type $login
 * @return boolean
 */
function user_exists($login) {

    if (get_config('case_insensitive_usernames')) {
        $qry = "COLLATE utf8mb4_general_ci = ?s";
    } else {
        $qry = "COLLATE utf8mb4_bin = ?s";
    }
    $username_check = Database::get()->querySingle("SELECT id FROM user WHERE username $qry", $login);
    if ($username_check) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief check if course has expired
 * @param $cid
 * @return bool
 */
function course_has_expired($cid) {
    $end_date = Database::get()->querySingle("SELECT end_date FROM course WHERE id = ?d", $cid)->end_date;
    if (!is_null($end_date)) {
        if (date("Y-m-d") > $end_date) {
            return true;
        } else {
            return false;
        }
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
        $qry = "COLLATE utf8mb4_general_ci = ?s";
    } else {
        $qry = "COLLATE utf8mb4_bin = ?s";
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
    //$trans_tbl = get_html_translation_table(HTML_ENTITIES);
    //$trans_tbl = array_flip($trans_tbl);
    $string = html_entity_decode(strip_tags($string));
    $text = preg_replace('/<(div|p|pre|br)[^>]*>/i', "\n", $string);
    return canonicalize_whitespace(strip_tags($text));
    // return strtr (strip_tags($string), $trans_tbl);
}


/**
 * @brie   completes url contained in the text with "<a href ...".
 *         However the function simply returns the submitted text without any
 *         transformation if it already contains some "<a href:" or "<img src=".
 * @params string $text text to be converted
 * @return text after conversion
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 *         completed by Hugues Peeters - July 22, 2002
 *         Regex fixes by Alexandros Diamantidis - Jan 22, 2008
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 * 		to that email address
 */

function make_clickable($text) {

    // If the user has decided to deeply use html and manage himself
    // hyperlink cancel the make clickable() function and return the text
    // untouched.

    if (preg_match("<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text)) {
        return $text;
    }

    // matches an "xxxx://yyyy" URL
    // xxxx can only be alphanumeric characters
    // yyyy is anything up to the first space, newline, ()<>

    $text = preg_replace("#\b([a-z0-9]+?://[^, \n\r()<>]+)#i", "<a href='$1'>$1</a>", $text);

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // yyyy contains either alphanum, "-", or "."
    // zzzz is optional.. will contain everything up to the first space, newline, or comma.
    // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
    // This is to keep it from getting annoying and matching stuff that's not meant to be a link.

    $text = preg_replace("#\b((?<!://)www\.([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,}(/[^, \n\r()<>]*)?)#i", "<a href='http://$1'>$1</a>", $text);

    // matches an email@domain type address

    $text = preg_replace("#\b([0-9a-z_\.\+-]+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,})\b#i", "<a href='mailto:$1'>$1</a>", $text);

    return($text);
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

/**
 * @brief formats the date according to the locale settings
 * @params unix_time_stamp $datetime_stamp
 * @params string $format pattern. default is 'full' format. Also, available is 'short', 'full' format.
 * @params boolean $display_time. default is true, that is, display time, otherwise don't display time.
 * @return formatted date

 */

function format_locale_date($datetime_stamp, $format = null, $display_time = true) {

    global $language;

    $locale = 'el'; // default locale
    $format_date_style = IntlDateFormatter::RELATIVE_FULL; // default date formatting style
    $format_time_style = IntlDateFormatter::SHORT; // default time formatting style

    if (isset($_GET['localize'])) {
        $locale = $_GET['localize'];
    }
    if (isset($language)) {
        $locale = $language;
    }

    if ($format == 'short') {
        $format_date_style = IntlDateFormatter::SHORT;
    } else if ($format == 'full') {
        $format_date_style = IntlDateFormatter::FULL;
    }

    if (!$display_time) {
        $format_time_style = IntlDateFormatter::NONE;
    }
    /* PHP reference
        https://www.php.net/manual/en/intldateformatter.create.php
        https://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
    */
    $fmt = datefmt_create($locale, $format_date_style, $format_time_style, 'Europe/Athens', IntlDateFormatter::TRADITIONAL);

    return (datefmt_format($fmt, $datetime_stamp));
}



/**
 * @brief remove seconds from a given datetime
 * @param type $datetime
 * @return datetime without seconds
 */
function datetime_remove_seconds($datetime) {
    return preg_replace('/:\d\d$/', '', $datetime);
}

/**
 * @brief returns user's previous login date, or today's date if no previous login
 * @param $uid
 * @param $time
 * @return string
 */
function last_login($uid, $time = false) {

    if ($time) {
        $last_login = Database::get()->querySingle("SELECT DATE_FORMAT(MAX(`when`), '%Y-%m-%d %H:%i') AS last_login FROM loginout
                          WHERE id_user = ?d AND action = 'LOGIN'", $uid)->last_login;
    } else {
        $last_login = Database::get()->querySingle("SELECT DATE_FORMAT(MAX(`when`), '%Y-%m-%d') AS last_login FROM loginout
                          WHERE id_user = ?d AND action = 'LOGIN'", $uid)->last_login;
    }

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
function visible_module($module_id, $course_id = null): bool
{
    if ($course_id == null) {
        $course_id = $GLOBALS['course_id'];
    } else {
        $course_id = $course_id;
    }

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
 * @brief Check if a module is disabled.
 *        If called inside of a course, takes in account course / collaboration setting
 * @return boolean
 */
function is_module_disable($module_id) {
    global $require_current_course, $is_collaborative_course;

    if ((get_config('show_collaboration') && get_config('show_always_collaboration')) or
        ($require_current_course && $is_collaborative_course)) {
        $q = Database::get()->querySingle("SELECT * FROM module_disable_collaboration WHERE module_id = ?d", $module_id);
        if ($q) {
            return true;
        }
    } elseif (!$require_current_course && get_config('show_collaboration') && !get_config('show_always_collaboration')){
        $q1 = Database::get()->querySingle("SELECT * FROM module_disable WHERE module_id = ?d", $module_id);
        $q2 = Database::get()->querySingle("SELECT * FROM module_disable_collaboration WHERE module_id = ?d", $module_id);
        if ($q1 or $q2) {
            return true;
        }
    } else {
        $q = Database::get()->querySingle("SELECT * FROM module_disable WHERE module_id = ?d", $module_id);
        if ($q) {
            return true;
        }
    }
    return false;
}

// Flipped Classroom
function is_module_disable_FC($module_id, $course_code, $unit_id, $act_id) {
    $q = Database::get()->queryArray("SELECT unit_id,tool_ids FROM course_units_activities WHERE course_code = ?s and unit_id = ?d and activity_id=?s", $course_code, $unit_id,$act_id);

    $nrlz_ids_final =array("start");
    foreach ($q as $record) {
        $nrlz_ids = explode(" ", $record->tool_ids);
        foreach($nrlz_ids as $f_id){
            array_push($nrlz_ids_final,$f_id);
        }
    }

    $is_in_list = empty(array_search($module_id,$nrlz_ids_final));

    if ($is_in_list) {
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

/**
 * @brief fills an array with user groups (group_id => group_name)
 * passing $as_id will give back only the groups that have been given the specific assignment
 * @param type $uid
 * @param type $course_id
 * @param type $as_id
 * @return type
 */
function user_group_info($uid, $course_id, $as_id = NULL) {
    $gids = array();

    if ($uid != null) {
        $q = Database::get()->queryArray("SELECT group_members.group_id AS grp_id, `group`.name AS grp_name FROM group_members,`group`
            WHERE group_members.group_id = `group`.id
            AND `group`.course_id = ?d AND group_members.user_id = ?d", $course_id, $uid);
    } else {
        if (!is_null($as_id) && Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $as_id)->assign_to_specific) {
            $q = Database::get()->queryArray("SELECT `group`.name AS grp_name,`group`.id AS grp_id FROM `group`, assignment_to_specific WHERE `group`.id = assignment_to_specific.group_id AND `group`.course_id = ?d AND assignment_to_specific.assignment_id = ?d", $course_id, $as_id);
        } else {
            $q = Database::get()->queryArray("SELECT name AS grp_name,id AS grp_id FROM `group` WHERE course_id = ?d", $course_id);
        }
    }

    foreach ($q as $r) {
        $gids[$r->grp_id] = $r->grp_name;
    }
    return $gids;
}



// Returns true if a string is invalid UTF-8
function invalid_utf8($s) {
    return !mb_check_encoding($s, 'UTF-8');
}

// Remove invalid bytes from UTF-8 string
function sanitize_utf8($s) {
    if (is_null($s)) {
        return '';
    } else {
        return mb_convert_encoding($s, 'UTF-8', 'UTF-8');
    }
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
    if (!$filename) {
        return '';
    }
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
    if (is_null($s)) {
        return '';
    }
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

/**
 * @brief return course access icon
 * @param $visibility
 * @return string
 */
function course_access_icon($visibility) {

    global $langTypeRegistration, $langTypeOpen, $langTypeClosed, $langTypeInactive;

    switch ($visibility) {
        case COURSE_OPEN: {
            $access_icon = "<span class='fa fa-lock-open fa-lg fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeOpen'></span>";
            break;
        }
        case COURSE_REGISTRATION: {
            $access_icon = "<div class='d-inline-flex align-items-center'><span class='fa fa-lock fa-lg fa-fw access' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeRegistration'></span>
            <span class='fa fa-pencil text-danger fa-custom-lock mt-0' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeRegistration' style='margin-left:-5px;'></span></div>";
            break;
        }
        case COURSE_CLOSED: {
            $access_icon = "<span class='fa fa-lock fa-lg fa-fw fa-access' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeClosed'></span>";
            break;
        }
        case COURSE_INACTIVE: {
            $access_icon = "<span class='fa fa-ban fa-lg fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeInactive'></span>";
            break;
        }
    }
    return $access_icon;
}

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
function format_time_duration($sec, $hourLimit = 24, $display_days = true) {
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

    if ($display_days) {
        $day = floor($hour / 24);
        $hour = $hour % 24;
        return (($day == 0) ? '' : (' ' . append_units($day, $langDay, $langDays))) .
            (($hour == 0) ? '' : (' ' . append_units($hour, $langhour, $langhours))) .
            (($min == 0) ? '' : (' ' . append_units($min, $langminute, $langminutes)));
    } else {
        return (($hour == 0) ? '' : (' ' . append_units($hour, $langhour, $langhours))) .
            (($min == 0) ? '' : (' ' . append_units($min, $langminute, $langminutes)));
    }
}


/**
 * @brief: convert a 'HH:MM:SS' string to seconds
 * (https://stackoverflow.com/questions/4834202/convert-time-in-hhmmss-format-to-seconds-only)
 * @param string $time
 * @return int
 */
function timeToSeconds(string $time): int {
    return array_reduce(explode(':', $time), fn ($x, $i) => $x * 60 + $i, 0);
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
// $limitValue and $limitField can also be arrays, in which case all apply
// $limitValue should be integer or array of integers
function reorder_table($table, $limitField, $limitValue, $toReorder, $prevReorder = null, $idField = 'id', $orderField = 'order') {
    Database::get()->transaction(function ()
            use ($table, $limitField, $limitValue, $toReorder, $prevReorder, $idField, $orderField) {
        if (is_array($limitField)) {
            $where = 'WHERE (' . implode(' AND ',
                array_map(function ($field) {
                    return "`$field` = ?d";
                }, $limitValue)) . ')';
        } elseif ($limitField) {
            $where = "WHERE `$limitField` = ?d";
        } else {
            $where = '';
            $limitValue = [];   // no limit, so no arguments to compare with
        }
        $max = Database::get()->querySingle("SELECT MAX(`$orderField`) AS max_order
            FROM `$table` $where", $limitValue)->max_order;

        if ($where) {
            $where .= ' AND';
        } else {
            $where = 'WHERE';
        }

        if (!is_null($prevReorder)) {
            $prevRank = Database::get()->querySingle("SELECT `$orderField` AS `rank`
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

/**
 * @global type $course_id
 * @param int $uid
 * @param type $all_units
 * @return array
 */
function findUserVisibleUnits($uid, $all_units, $course_id = null) {

    if ($course_id == null) {
        $course_id = $GLOBALS['course_id'];
    } else {
        $course_id = $course_id;
    }

    $user_units = [];
    $userInBadges = Database::get()->queryArray("SELECT cu.id, cu.title, cu.comments, cu.start_week, cu.finish_week, cu.visible, cu.public, cu.assign_to_specific, ub.completed
                                                          FROM course_units cu
                                                          INNER JOIN badge b ON (b.unit_id = cu.id)
                                                          INNER JOIN user_badge ub ON (b.id = ub.badge)
                                                          WHERE ub.user = ?d
                                                          AND cu.course_id = ?d
                                                          AND cu.visible = 1
                                                          AND cu.public = 1
                                                          AND cu.order >= 0", $uid, $course_id);
    if ( isset($userInBadges) and $userInBadges ) {
        foreach ($userInBadges as $userInBadge) {
            if ($userInBadge->completed == 0) {
                $userIncompleteUnits[] = $userInBadge->id;
            }
        }
    }
    foreach ($all_units as $unit) {
        $unitPrereq = Database::get()->querySingle("SELECT prerequisite_unit FROM unit_prerequisite
                                                                WHERE unit_id = ?d", $unit->id);

        if ( $unitPrereq and isset($userIncompleteUnits) and in_array($unitPrereq->prerequisite_unit, $userIncompleteUnits) ) {
            continue;
        }
        $user_units[] = $unit;
    }
    return $user_units;
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
 * @brief Find the course professor from its id
 * @param type $cid
 * @return boolean
 */
function course_id_to_prof($cid) {
    $r = Database::get()->querySingle("SELECT prof_names FROM course WHERE id = ?d", $cid);
    if ($r) {
        return $r->prof_names;
    } else {
        return false;
    }
}

/**
 * @global type $webDir
 * @param type $cid
 * @brief Delete course with id = $cid
 */
function delete_course($cid): void
{
    global $webDir;

    $course_code = course_id_to_code($cid);
    if (!isset($webDir) or !$webDir or !$course_code) { // security check
        return;
    }

    Database::get()->query("DELETE d FROM h5p_content_dependency d WHERE d.content_id IN (SELECT id FROM h5p_content WHERE course_id = ?d)", $cid);
    Database::get()->query("DELETE d FROM h5p_content d WHERE d.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM user_badge_criterion d,badge_criterion s,badge s2 WHERE d.badge_criterion=s.id AND s.badge=s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM badge_criterion d,badge s WHERE d.badge=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM user_badge d,badge s WHERE d.badge=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM badge WHERE course_id = ?d", $cid);

    Database::get()->query("DELETE d FROM user_certificate_criterion d,certificate_criterion s,certificate s2 WHERE d.certificate_criterion=s.id AND s.certificate=s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM certificate_criterion d,certificate s WHERE d.certificate=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM user_certificate d,certificate s WHERE d.certificate=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM certificate WHERE course_id = ?d", $cid);

    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM document WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM ebook_subsection d,ebook_section s,ebook s2 WHERE d.section_id=s.id AND s.ebook_id = s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM ebook_section d,ebook s WHERE d.ebook_id=s.id AND s.course_id = ?d", $cid);
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
    Database::get()->query("DELETE d FROM group_members d,`group` s WHERE d.group_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM group_properties d,`group` s WHERE d.group_id=s.id AND s.course_id = ?d", $cid);
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
    Database::get()->query("DELETE d FROM unit_resources d,course_units s WHERE d.unit_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM course_units_to_specific d,course_units s WHERE d.unit_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_units WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM abuse_report WHERE course_id = ?d", $cid);
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
    Database::get()->query("DELETE d FROM dropbox_attachment d,dropbox_msg s WHERE d.msg_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM dropbox_index d,dropbox_msg s WHERE d.msg_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM dropbox_msg WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM lp_asset d,lp_module s WHERE d.module_id=s.module_id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM lp_rel_learnPath_module d,lp_learnPath s WHERE d.learnPath_id=s.learnPath_id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM lp_user_module_progress d,lp_learnPath s WHERE d.learnPath_id=s.learnPath_id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM lp_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM lp_learnPath WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM wiki_pages_content d,wiki_pages s,wiki_properties s2 WHERE d.pid=s.id AND s.wiki_id=s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM wiki_pages d,wiki_properties s WHERE d.wiki_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM wiki_acls d,wiki_properties s WHERE d.wiki_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM wiki_properties WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM poll_question_answer d,poll_question s,poll s2 WHERE d.pqid=s.pqid AND s.pid=s2.pid AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM poll_answer_record d,poll_user_record s,poll s2 WHERE d.poll_user_record_id=s.id AND s.pid=s2.pid AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM poll_user_record d,poll s WHERE d.pid=s.pid AND course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM poll_question d,poll s WHERE d.pid=s.pid AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM poll WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM assignment_submit d,assignment s WHERE d.assignment_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM assignment_to_specific d,assignment s WHERE d.assignment_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM assignment WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM exercise_with_questions d,exercise_question s WHERE d.question_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM exercise_with_questions d,exercise s WHERE d.exercise_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM exercise_answer d,exercise_question s WHERE d.question_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM exercise_answer_record d,exercise_question s WHERE d.question_id =s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise_question WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise_question_cats WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM exercise_user_record d,exercise s WHERE d.eid=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM exercise WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_settings WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM tag d,tag_element_module s WHERE d.id=s.tag_id AND NOT EXISTS (SELECT 1 FROM tag_element_module v WHERE v.tag_id=s.tag_id AND v.course_id != s.course_id ) AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM tag_element_module WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM gradebook_book d,gradebook_activities s,gradebook s2 WHERE d.gradebook_activity_id=s.id AND s.gradebook_id=s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM gradebook_activities d,gradebook s WHERE d.gradebook_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM gradebook_users d,gradebook s WHERE d.gradebook_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE FROM gradebook WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM attendance_book d,attendance_activities s,attendance s2 WHERE d.attendance_activity_id=s.id AND s.attendance_id=s2.id AND s2.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM attendance_activities d,attendance s WHERE d.attendance_id=s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM attendance_users d,attendance s WHERE d.attendance_id=s.id AND s.course_id = ?d", $cid);
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

    require_once 'modules/course_info/archive_functions.php';

    $u = intval($id);

    if (!isset($webDir) or empty($webDir)) { // security
        return false;
    }
    if ($u == 1) { // don't delete admin user
        return false;
    } else {
        if (Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $u)) { // validate existing user

            $q = Database::get()->queryArray("SELECT * FROM course_user WHERE user_id = ?d AND status = " . USER_TEACHER, $u);
            if (count($q) > 0) {  // user has courses as admin?
                foreach ($q as $user_courses) {
                    $q2 = Database::get()->queryArray("SELECT * FROM course_user WHERE course_id = ?d AND status = " . USER_TEACHER, $user_courses->course_id);
                    if (count($q2) > 1) { // course has co admins
                        continue; // don't delete course
                    } else {
                        $course_code = course_id_to_code($user_courses->course_id);
                        $course_title = course_id_to_title($user_courses->course_id);
                        // first archive course
                        $zipfile = doArchive($user_courses->course_id, $course_code);
                        $garbage = "$webDir/courses/garbage";
                        $target = "$garbage/$course_code.$_SESSION[csrf_token]";
                        is_dir($target) or make_dir($target);
                        touch("$garbage/index.html");
                        rename($zipfile, "$target/$course_code.zip");
                        // delete course
                        delete_course($user_courses->course_id);
                        // logging
                        Log::record(0, 0, LOG_DELETE_COURSE, array('id' => $user_courses->course_id,
                            'code' => $course_code,
                            'title' => $course_title));
                    }
                }
            }

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
            Database::get()->query("DELETE FROM gradebook_users WHERE uid = ?d", $u);
            Database::get()->query("DELETE FROM gradebook_book WHERE uid = ?d", $u);
            Database::get()->query("DELETE FROM attendance_users WHERE uid = ?d", $u);
            Database::get()->query("DELETE FROM attendance_book WHERE uid = ?d", $u);
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
 */
function get_config($key, $default = null) {
    $cache = new FileCache('config', 300);
    $config = $cache->get();
    if ($config === false) {
        $config = [];
        $q = Database::get()->queryArray('SELECT `key`, `value` FROM config ORDER BY `key`');
        foreach ($q as $item) {
            $config[$item->key] = $item->value;
        }
        $cache->store($config);
    }
    if (isset($config[$key])) {
        return $config[$key];
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
    $cache = new FileCache('config', 300);
    $cache->clear();
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
 * @param type $name
 * @param type $rows
 * @param type $cols
 * @param type $text
 * @param type $extra
 * @return type
 */
function rich_text_editor($name, $rows, $cols, $text, $onFocus = false, $options = []) {
    global $head_content, $language, $urlAppend, $course_code, $langPopUp, $langPopUpFrame, $is_editor, $is_admin, $langResourceBrowser, $langMore, $tinymce_color_text, $langInputTextEditor;
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
                    parent.find('.mce-toolbar-grp, .mce-statusbar').attr('style','border:0px');
                    if (typeof tinyMceCallback !== 'undefined') {
                        tinyMceCallback(editor);
                    }";
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
        $head_content .= css_link('tinymce/css/re-style-richTextEditor.css');
        if (in_array('prevent_copy_paste', $options)) {
            $copy_paste = '';
            $paste_plugin = ' paste';
            $paste_preprocess = '
                paste_preprocess: (plugin, args) => {
                    args.stopImmediatePropagation();
                    args.stopPropagation();
                    args.preventDefault();
                },';

        } else {
            $copy_paste = '| pastetext cut copy paste ';
            $paste_plugin = $paste_preprocess = '';
        }
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
        popup_css: false,
        buttons: [{text: 'Cancel', onclick: 'close'}]
    }, {
        window: win,
        input: field_name
    });
    return false;
}

tinymce.init({
    // General options
    selector: 'textarea.mceEditor',
    content_css: [
        '{$urlAppend}template/modern/css/bootstrap.min.css',
        '{$urlAppend}template/modern/css/font-awesome-6.4.0/css/all.css',
        '{$urlAppend}template/modern/css/default.css',
    ],
    content_style: 'body { margin: 8px; background: none !important; color: $tinymce_color_text;  }',
    extended_valid_elements: 'span[*]',
    noneditable_noneditable_class: 'fa',
    language: '$language',
    cache_suffix: '?v=" . CACHE_SUFFIX . "',
    theme: 'modern',
    skin: 'light',
    branding: false,
    font_formats:
    'Open Sans=open sans; Roboto=roboto; Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
    image_advtab: true,
    image_class_list: [
        {title: 'Responsive', value: 'img-responsive mx-auto d-block'},
        {title: 'Responsive Center', value: 'img-responsive mx-auto d-block'},
        {title: 'Float left', value: 'float-start'},
        {title: 'Float left and responsive', value: 'float-start img-responsive'},
        {title: 'Float right', value: 'float-end'},
        {title: 'Float right and responsive', value: 'float-end img-responsive'},
        {title: 'Rounded image', value: 'img-rounded'},
        {title: 'Rounded image and responsive', value: 'rounded img-responsive'},
        {title: 'Circle image', value: 'img-circle'},
        {title: 'Circle image and responsive', value: 'rounded-circle img-responsive'},
        {title: 'Thumbnail image', value: 'img-thumbnail'},
        {title: 'Thumbnail image and responsive', value: 'img-thumbnail img-responsive'},
        {title: 'None', value: ' '}
    ],
    plugins: 'fullscreen pagebreak save image link media eclmedia print contextmenu paste noneditable visualchars nonbreaking wordcount emoticons preview searchreplace table code textcolor colorpicker lists advlist charmap fontawesome$paste_plugin',
    $paste_preprocess
    entity_encoding: 'raw',
    relative_urls: false,
    link_class_list: [
        {title: 'None', value: ''},
        {title: '".js_escape($langPopUp)."', value: 'colorbox'},
        {title: '".js_escape($langPopUpFrame)."', value: 'colorboxframe'}
    ],
    $filebrowser
    menu: true,
    menubar: false,
    // Toolbar options
    toolbar1: 'toggle bold italic underline | forecolor backcolor | link image media eclmedia | alignleft aligncenter alignright alignjustify | bullist numlist | fullscreen preview',
    toolbar2: 'formatselect | fontselect fontsizeselect | outdent indent | emoticons fontawesome strikethrough superscript subscript table $copy_paste| removeformat | searchreplace undo redo | code'
    $focus_init
});
</script>";
    }
    if (!is_null($text)) {
        $textarea_text = q(str_replace('{', '&#123;', $text));
    } else {
        $textarea_text = '';
    }
    return "<textarea class='mceEditor' name='$name' rows='$rows' cols='$cols' aria-label='$langInputTextEditor'>" . $textarea_text . "</textarea>\n";
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
 * @global type $course_id
 */
function units_get_maxorder() {

    global $course_id;

    $q = Database::get()->querySingle("SELECT MAX(`order`) AS max_order FROM course_units
            WHERE course_id = ?d", $course_id);

    $maxorder = $q->max_order;

    if ($maxorder == null or $maxorder <=0) {
        $maxorder = 1;
    }
    return $maxorder;
}

function math_unescape($matches) {
    return html_entity_decode($matches[0]);
}

// Standard function to prepare some HTML text, possibly with math escapes, for display
function standard_text_escape($text, $mathimg = null) {
    global $purifier, $urlAppend;

    if (is_null($mathimg)) {
        $mathimg = $urlAppend . 'courses/mathimg/';
    }
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
        $definition = ' title="'.$matches[0].'" data-bs-trigger="focus" data-bs-html="true" data-bs-content="' . q($_SESSION['glossary'][$term]) . $term_notes . $term_url .'"';
    } else {
        $definition = '';
    }

    return '<a href="#" data-bs-="popover"' .
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

// Try to guess the platform installation base url (i.e. $urlAppend)
// when the platform is not functional
function guess_base_url() {
    $uri = rtrim(str_replace(['/?.*$/', '/\.php/'], ['', '.php'], $_SERVER['REQUEST_URI']), '/');
    $path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    $base_path = realpath(dirname(__FILE__, 2));
    while ($uri and $base_path != $path) {
        $uri = preg_replace('|/[^/]+$|', '', $uri);
        $path = preg_replace('|/[^/]+$|', '', $path);
    }
    return $uri . '/';
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

// Translate Greek characters to Latin
function greek_to_latin($string) {
    return str_replace(
            array(
        'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π',
        'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ',
        'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω',
        'ς', 'ά', 'έ', 'ή', 'ί', 'ύ', 'ό', 'ώ', 'Ά', 'Έ', 'Ή', 'Ί', 'Ύ', 'Ό', 'Ώ', 'ϊ',
        'ΐ', 'ϋ', 'ΰ', 'ϊ', 'Ϊ', 'Ϋ', '–'), array(
        'a', 'b', 'g', 'd', 'e', 'z', 'i', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p',
        'r', 's', 't', 'y', 'f', 'x', 'ps', 'o', 'A', 'B', 'G', 'D', 'E', 'Z', 'H', 'Th',
        'I', 'K', 'L', 'M', 'N', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'X', 'Ps', 'O',
        's', 'a', 'e', 'i', 'i', 'y', 'o', 'o', 'A', 'E', 'H', 'I', 'Y', 'O', 'O', 'i',
        'i', 'y', 'y', 'I', 'I', 'Y', '-'), $string);
}

// Convert to uppercase and remove accent marks
// Limited coverage for now
function remove_accents($string) {
    return strtr(mb_strtoupper($string, 'UTF-8'),
        ['Ά' => 'Α', 'Έ' => 'Ε', 'Ί' => 'Ι', 'Ή' => 'Η', 'Ύ' => 'Υ',
         'Ό' => 'Ο', 'Ώ' => 'Ω', 'Ϊ' => 'Ι', 'Ϋ' => 'Υ',
         'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
         'Ç' => 'C', 'Ñ' => 'N', 'Ý' => 'Y',
         'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
         'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
         'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
         'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
         '’' => "'", '‘' => "'"]);
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

    if (isset($title)) {
        $title = q($title);
        $extra = "title data-bs-original-title='$title' data-bs-toggle='tooltip' data-bs-placement='bottom'";
    } else {
        $extra = '';
    }
    if (isset($title) && $with_title) {
        $img = $sr_only ? "<span class='fa $name' $extra></span><span class='sr-only'>$title</span>" : "<span class='fa $name' $extra></span> $title";
    } else {
        $img = "<span class='fa $name' $extra></span>";
    }
    if (isset($link)) {
        return "<a href='$link'$link_attrs aria-label='$title' role='button'>$img</a>";
    } else {
        return $img;
    }
}

/**
 * Link / img tag for displaying user profile
 * @param int $uid
 * @param int $size
 * @param string $class
 * @return string
 */

function profile_image($user_id, $size, $class=null) {
    global $urlServer, $themeimg, $uid, $course_id, $is_editor, $langUser;

    if (isset($_SESSION['profile_image_cache_buster'])) {
        $suffix = '?v=' . $_SESSION['profile_image_cache_buster'];
    } else {
        $suffix = '';
    }

    // makes $class argument optional

    $class_attr = ($class == null)? '': (" class='" . q($class) . "'");
    $size_width = ($size != IMAGESIZE_SMALL || $size != IMAGESIZE_LARGE)? "style='width:{$size}px; height:{$size}px;'":'';
    $size = ($size == IMAGESIZE_SMALL or $size == IMAGESIZE_LARGE)? $size: IMAGESIZE_LARGE;
    $imageurl = $username = '';

    if ($user_id) {
        $user = Database::get()->querySingle("SELECT has_icon, pic_public,
            CONCAT(surname, ' ', givenname) AS fullname
            FROM user WHERE id = ?d", $user_id);
        $username = q(trim($user->fullname ?? ''));
        if (($user and $user->pic_public) or $_SESSION['status'] == USER_TEACHER or
            $uid == $user_id or
            (isset($course_id) and $course_id and $is_editor)) {
                $hash = profile_image_hash($user_id);
                $hashed_file = "courses/userimg/{$user_id}_{$hash}_$size.jpg";
                if (file_exists($hashed_file)) {
                    $imageurl = $urlServer . $hashed_file;
                } elseif (file_exists("courses/userimg/{$user_id}_$size.jpg")) {
                    $imageurl = "{$urlServer}courses/userimg/{$user_id}_$size.jpg";
                }
        }
    }

    if (!$imageurl) {
        $imageurl = "$themeimg/default_$size.png";
    }
    return "<img src='$imageurl$suffix' $class_attr alt='$langUser:$username' $size_width>";
}

/**
 * Profile image hash to make image files unpredictable
 * @param int $uid
 * @return string
 */
function profile_image_hash($uid) {
    static $code_key;

    if (!isset($code_key)) {
        $code_key = get_config('code_key');
    }
    return str_replace(['/', '+', '='], ['-', '.', ''],
        base64_encode(substr(hash_hmac('ripemd128', $uid, $code_key, true), 0, 10)));
}

function canonicalize_url($url) {
    if (!preg_match('/^[a-zA-Z0-9_-]+:/', $url)) {
        return 'http://' . $url;
    } else {
        return $url;
    }
}

function is_url_accepted($url, $protocols = null){
    $url_nospace = preg_replace('/\s+/', '', $url);
    if ($url === 'http://' or
        empty($url) or
        !filter_var($url, FILTER_VALIDATE_URL) or
        preg_match('/^javascript/i', $url_nospace) or
        ($protocols and !preg_match("/^$protocols/i", $url_nospace))) {
        return false;
    } else {
        return true;
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

// check if username match for both case-sensitive/insensitive
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


function is_enabled_course_registration($uid) {
    $q = Database::get()->querySingle("SELECT disable_course_registration FROM user WHERE id = ?d", $uid);
    if ($q) {
        $disable_course_reg = $q->disable_course_registration;
        if ($disable_course_reg) {
            return false;
        } else {
            return true;
        }
    } else {
        return true;
    }
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
 * @param  - $dirPath (String) - the path of the directory to delete
 * @return - boolean - true if the delete succeed, false otherwise.
 */

function removeDir($dirPath) {
    global $webDir;

    // Don't delete root directories
    $dirPath = rtrim($dirPath, '/\\');
    if ($dirPath == $webDir or $dirPath == "$webDir/courses" or $dirPath == "$webDir/video" or $dirPath === '/' or !is_dir($webDir)) {
        return false;
    }

    // Try to remove the directory recursively if it exists
    if (!file_exists($dirPath)) {
        return true;
    } else {
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath,
                        RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            if ($file->isDir()) {
                if (!rmdir($filePath)) {
                    return false;
                }
            } else {
                if (!unlink($filePath)) {
                    return false;
                }
            }
        }
        return rmdir($dirPath);
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

    public $tempDiv;
    public $newDiv;
    public $charCount;
    public $postfix;
    public $limit;
    public $postfix_text;
    public $encoding;

    function __construct($string, $limit, $postfix) {
        // create dom element using the html string
        $this->tempDiv = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $this->tempDiv->loadHTML('<?xml version="1.0" encoding="UTF-8" ?><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><div>' . $string . '</div>', LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR);
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
        $newhtml = preg_replace(['/^.*<body>\s*/', '/\s*<\/body>$/'], '', $this->newDiv->saveHTML());
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

    $count = 0;
    if (ini_get('session.save_handler') == 'redis') {
        $redis = new Redis();
        $path = ini_get('session.save_path');
        $url = parse_url($path);
	try { // is Redis alive ?
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
                }
                $count = floor($redis->dbSize() / 12);
	} catch (RedisException $e) {
		unset($e);
	}
    } else {
        if ($directory_handle = @opendir(session_save_path())) {
            while (false !== ($file = readdir($directory_handle))) {
                if ($file != '.' and $file != '..') {
                    if (time() - fileatime(session_save_path() . '/' . $file) < MAX_IDLE_TIME * 60) {
                        $count++;
                    }
                }
            }
           @closedir($directory_handle);
        }
    }
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
function copyright_info($id, $noImg = 1, $type = 'course'): string
{

    global $language, $license, $langOpenNewTab;

    $lang = langname_to_code($language);
    $link = '';
    if ($type == 'documents') {
        $lic = Database::get()->querySingle("SELECT copyrighted FROM document WHERE id = ?d", $id)->copyrighted;
    } else {
        $lic = Database::get()->querySingle("SELECT course_license FROM course WHERE id = ?d", $id)->course_license;
    }

    if ($noImg == 1) {
        if (in_array($lic, ['1', '2', '3', '4', '5', '6'])) {
            if ($language != 'en') {
                $link_suffix = 'deed.' . $lang;
            } else {
                $link_suffix = '';
            }
            $link = "<a href='" . $license[$lic]['link'] . "$link_suffix' target='_blank' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='" . q($license[$lic]['title']) . "' aria-label='$langOpenNewTab'>
                        <span class='" . $license[$lic]['image'] . "'></span>
                    </a>";
        } else if ($lic == 10) {
            $link = "<span data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='" . q($license[$lic]['title']) . "' class='" . $license[$lic]['image'] . "'></span>";
        }
    }
    return $link;
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
    require_once 'modules/admin/extconfig/ltipublishapp.php';
    $ltipublishapp = ExtAppManager::getApp('ltipublish');
    $framebustheader = $ltipublishapp->getFramebustHeader();
    header($framebustheader);
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
function form_buttons($btnArray): string
{

    global $langCancel;

    $buttons = "";

    foreach ($btnArray as $btn) {
        if(!isset($btn['show']) || ($btn['show'])) {
            $id = isset($btn['id'])?"id='$btn[id]'": '';
            $custom_field = isset($btn['custom_field'])?"onclick='$btn[custom_field]'": '';
            if (isset($btn['icon'])) {
                $text = "<span class='fa $btn[icon] space-after-icon'></span>" . $text;
            }

            if (isset($btn['href'])) {
                $class = $btn['class'] ?? 'btn-secondary';
                $title = isset($btn['title'])?"title='$btn[title]'": '';
                $text = $btn['text'] ?? $langCancel;
                $target = isset($btn['target'])?"target='$btn[target]'": '';
                $javascript = isset($btn['javascript'])?"onclick=\"$btn[javascript]\"": '';
                $buttons .= "<a class='btn $class' $id href='$btn[href]' $target $title $javascript $custom_field>$text</a>&nbsp;&nbsp;";
            } elseif(isset($btn['javascript'])) {
                $class = $btn['class'] ?? 'btn-primary';
                $type = isset($btn['type'])?"type='$btn[type]'":'type="submit"';
                $name = isset($btn['name'])?"name='$btn[name]'": null;
                $value = isset($btn["value"])?"value='$btn[value]'": null;
                $javascript = isset($btn['javascript'])?"onclick=\"$btn[javascript]\"": '';
                $buttons .= "<input class='btn $class' $type $id $name $value $custom_field $javascript />&nbsp;&nbsp;";
            } else {
                $class = $btn['class'] ?? 'btn-primary';
                $type = isset($btn['type'])?"type='$btn[type]'":'type="submit"';
                $text = $btn['text'] ?? '';
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
    global $langConfirmDelete, $langCancel, $langDelete,
           $course_code, $toolName, $pageName, $langListChoices;

    $out_primary = $out_secondary = array();
    $i=0;
    $page_title = "";

    $temporary_button_class = "";

    if (isset($pageName) and !empty($pageName) and $page_title_flag) {
        $page_title = "<div class='form-label TextBold text-capitalize mb-0'><span class='fa-solid fa-check text-success pe-2'></span>".q($pageName)."</div>";
    }

    foreach (array_reverse($options) as $option) {

        if(isset($option['temporary-button-class'])){
            $temporary_button_class = $option['temporary-button-class'];
        }

        // skip items with show=false
        if (isset($option['show']) and !$option['show']) {
            continue;
        }
        $wrapped_class = isset($option['class']) ? " class='$option[class]'" : '';
        $url = $option['url'] ?? "#";
        $title = q($option['title']);
        $level = $option['level'] ?? 'secondary';
        if (isset($option['confirm'])) {
            $data_action_class = $option['data_action_class'] ?? 'deleteAdminBtn';
            $title_conf = $option['confirm_title'] ?? $langConfirmDelete;
            $accept_conf = $option['confirm_button'] ?? $langDelete;
            $confirm_extra = " data-title='$title_conf' data-message='" .
                q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept_conf' data-action-class='$data_action_class'";
            $confirm_modal_class = ' confirmAction text-wrap';
            $form_begin = "<form class='form-action-button-mydropdowns mb-0' method=post action='$url' class='mb-0'>";
            $form_end = '</form>';
            $href = '';
        } else {
            $confirm_extra = $confirm_modal_class = $form_begin = $form_end = '';
            $href = " href='$url'";
        }
        if (isset($option['text-class'])) {
            $text_class = $option['text-class'];
        } else {
            $text_class = '';
        }
        if (isset($option['modal-class'])){
            $modal_class = $option['modal-class'];
        } else {
            $modal_class = '';
        }
        if (!isset($option['button-class'])) {
            $button_class = 'submitAdminBtn '.$modal_class.'';
        } else {
            $oldButton = '';
            if(strpos($option['button-class'],'btn-success') !== false){
                $oldButton = 'btn-success';
            }else if(strpos($option['button-class'], 'btn-secondary') !== false){
                $oldButton = 'btn-secondary';
            }else if(strpos($option['button-class'], 'btn-default') !== false){
                $oldButton = 'btn-default';
            }else if(strpos($option['button-class'], 'btn-primary') !== false){
                $oldButton = 'btn-primary';
            }else if(strpos($option['button-class'], 'btn-danger') !== false){
                $oldButton = 'btn-danger';
            }else if(strpos($option['button-class'], 'btn-warning') !== false){
                $oldButton = 'btn-warning';
            }else if(strpos($option['button-class'], 'btn-info') !== false){
                $oldButton = 'btn-info';
            }else if(strpos($option['button-class'], 'btn-light') !== false){
                $oldButton = 'btn-light';
            }else if(strpos($option['button-class'], 'btn-dark') !== false){
                $oldButton = 'btn-dark';
            }
            //replace button-class with myclass;
            $button_class = $option['button-class'];
            if($oldButton == 'btn-danger'){
                $new_button = str_replace($oldButton,'deleteAdminBtn',$button_class);
            }else{
                $new_button = str_replace($oldButton,'submitAdminBtn '.$modal_class.'',$button_class);
            }

            $button_class = $new_button;
        }
        if (isset($option['link-attrs'])) {
            $link_attrs = " ".$option['link-attrs'];
        } else {
            $link_attrs = "";
        }
        $caret = '';
        $primaryTag = 'a';
        if ($level != 'primary-label' or isset($option['icon'])) {
            $dataAttrs = "data-bs-placement='bottom' data-bs-toggle='tooltip'";
        } else {
            $dataAttrs = '';
        }
        $subMenu = '';
        if (isset($option['options']) and ($level == 'primary' or $level == 'primary-label')) {
            $href = '';
            $primaryTag = 'button';
            $button_class .= ' dropdown-toggle';
            $caret = ' <span class="caret"></span>';
            $dataAttrs = 'data-bs-display="static" data-bs-toggle="dropdown" data-bs-placement="bottom" aria-haspopup="true" aria-expanded="false"';
            $form_begin = '<div class="btn-group" role="group">';
            $form_end = '</div>';
            $subMenu = '<div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border">
                            <ul class="list-group list-group-flush">';
                foreach ($option['options'] as $subOption) {
                    $subMenu .= '<li><a class="'.$subOption['class'].' list-group-item d-flex justify-content-start align-items-start gap-2 py-3" href="' . $subOption['url'] . '">';
                    $subMenu .= isset($subOption['icon']) ? '<span class="'.$subOption['icon'].' settings-icons"></span>' : '';
                    $subMenu .= q($subOption['title']) . '</a></li>';
                }
                $subMenu .= '</ul>
                        </div>';
        }
        $iconTag = '';
        if ($level == 'primary' or $level == 'primary-label') {
            if (isset($option['text-class'])) {
                $text_class = $option['text-class'];
            } else {
                $text_class = '';
            }
            if (isset($option['icon'])) {
                $iconTag = "<span class='fa $option[icon] settings-icons'></span>";
            }
            array_unshift($out_primary,
                "$form_begin<$primaryTag$confirm_extra class='$text_class btn $button_class$confirm_modal_class'" . $href .
                ' ' . $dataAttrs .
                " title='$title'$link_attrs>" . $iconTag . $caret .
                "</$primaryTag>$subMenu$form_end");
        }

        if (count($options) > 1) {
            array_unshift($out_secondary,
                "<li$wrapped_class>$form_begin<a$confirm_extra  class='$text_class $modal_class $confirm_modal_class $temporary_button_class list-group-item d-flex justify-content-start align-items-start gap-2 py-3'" . $href .
                " $link_attrs>" .
                "<span class='fa $option[icon] settings-icons'></span> $title</a>$form_end</li>");
        } else {
            $out_secondary = [];
        }
        $i++;
    }
    $out = '';
    if (count($out_primary)) {
        $out .= implode('', $out_primary);
    }

    $action_button = '';
    $secondary_title = isset($secondary_menu_options['secondary_title']) ? $secondary_menu_options['secondary_title'] : "";
    $secondary_icon = isset($secondary_menu_options['secondary_icon']) ? $secondary_menu_options['secondary_icon'] : "fa-solid fa-gear";

    if (count($out_secondary) > 0) {
        $action_button .= "<button type='button' id='toolDropdown' class='btn submitAdminBtn' data-bs-toggle='dropdown' aria-expanded='false' aria-label='$langListChoices'>
                                <span class='fa $secondary_icon'></span>
                                <span class='fa-solid fa-chevron-down ps-2'></span>
                                <span class='hidden-xs TextBold'>$secondary_title</span>
                                <span class='caret'></span><span class='hidden'></span>
                            </button>";
        $action_button .= " <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='toolDropdown'>
                                <ul class='list-group list-group-flush'>
                                    ".implode('', $out_secondary)."
                                </ul>
                            </div>";
    }

    $pageTitleActive = "";
    if (($action_button || $out) && $i!=0) {
        if(isset($course_code) and $course_code) {
            $titleHeader = (!empty($pageName) ? $pageName : $toolName);
            if(!empty($titleHeader)) {
                return "<div class='col-12 d-md-flex justify-content-md-between align-items-lg-start my-3'>
                            <div class='col-lg-5 col-md-6 col-12'><div class='action-bar-title mb-0'>$titleHeader</div></div>
                            <div class='col-lg-7 col-md-6 col-12 action_bar d-flex justify-content-md-end justify-content-start align-items-start px-0 mt-md-0 mt-4'>
                                <div class='margin-top-thin margin-bottom-fat hidden-print w-100'>
                                    <div class='ButtonsContent d-flex justify-content-end align-items-center flex-wrap gap-2'>
                                        $out
                                        $action_button
                                    </div>
                                </div>
                            </div>
                            $pageTitleActive
                        </div>";
            } else {
                return "<div class='col-12 actionCont mb-4'>
                            <div class='col-12 action_bar d-flex justify-content-start'>
                                <div class='margin-top-thin margin-bottom-fat hidden-print w-100'>
                                    <div class='ButtonsContent d-flex justify-content-lg-end justify-content-end align-items-center flex-wrap gap-2'>
                                        $out
                                        $action_button
                                    </div>
                                </div>
                            </div>
                            $pageTitleActive
                        </div>";
            }
        } else {
            $marginBottom = 'mb-4';
            if(isset($_SESSION['uid'])){
                $marginBottom = 'my-4';
            }
            $titleHeader = (!empty($pageName) ? $pageName : '');
            return "<div class='col-12 d-md-flex justify-content-md-between align-items-lg-start $marginBottom'>
                        <div class='col-lg-5 col-md-6 col-12'><div class='action-bar-title mb-0'>$titleHeader</div></div>
                        <div class='col-lg-7 col-md-6 col-12 action_bar d-flex justify-content-md-end justify-content-start align-items-start px-0 mt-md-0 mt-4'>
                            <div class='margin-top-thin margin-bottom-fat hidden-print w-100'>
                                <div class='ButtonsContent d-flex justify-content-end align-items-center flex-wrap gap-2'>
                                    $out
                                    $action_button
                                </div>
                            </div>
                        </div>
                        $pageTitleActive
                    </div>";
        }
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
function action_button($options, $secondary_menu_options = array(), $fc=false) {
    global $langConfirmDelete, $langCancel, $langDelete, $langListChoices;
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
            $btn_class = ' submitAdminBtn';
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
            $form_begin = "<form class='form-action-button-popover list-group-item-action list-group-item' method=post action='$option[url]'>";
            $form_end = '</form>';
            if ($level == 'primary-label' or $level == 'primary') {
                $primary_form_begin = $form_begin;
                $primary_form_end = $form_end;
                $form_begin = $form_end = '';
                $primary_icon_class = " confirmAction' data-title='$title' data-message='" .
                    q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept' data-action-class='deleteAdminBtn'";
            } else {
                $icon_class .= " confirmAction' data-title='$title' data-message='" .
                    q($option['confirm']) . "' data-cancel-txt='$langCancel' data-action-txt='$accept' data-action-class='deleteAdminBtn'";
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
            array_unshift($out_primary, "<a href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon] space-after-icon$primary_icon_class'></span>" . q($option['title']) . "<span class='hidden'></span></a>");
        } elseif ($level == 'primary') {
            array_unshift($out_primary, "<a aria-label='" . q($option['title']) . "' data-bs-placement='bottom' data-bs-toggle='tooltip' title data-bs-original-title='" . q($option['title']) . "' href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon]$primary_icon_class'></span><span class='hidden'></span></a>");
        } else {
            array_unshift($out_secondary, $form_begin . icon($option['icon'], $option['title'], $url, $icon_class.$link_attrs, true) . $form_end);
        }
    }
    $primary_buttons = "";
    if (count($out_primary)) {
        $primary_buttons = implode('', $out_primary);
    }
    $action_button = "";
    $secondary_title = isset($secondary_menu_options['secondary_title']) ? $secondary_menu_options['secondary_title'] : "<span class='hidden'></span>";

    if($fc){
        $secondary_icon = isset($secondary_menu_options['secondary_icon']) ? $secondary_menu_options['secondary_icon'] : "fa-wrench";
    }else{
        $secondary_icon = isset($secondary_menu_options['secondary_icon']) ? $secondary_menu_options['secondary_icon'] : "fa-solid fa-gear";
    }
    $secondary_btn_class = isset($secondary_menu_options['secondary_btn_class']) ? $secondary_menu_options['secondary_btn_class'] : "submitAdminBtn";
    if (count($out_secondary)) {
        $action_list = q("<div class='list-group' id='action_button_menu'>".implode('', $out_secondary)."</div>");
        if(!empty($secondary_title)){
            $tmp_class_title = "<span class='hidden-xs'>$secondary_title</span>";
        }else{
            $tmp_class_title = "";
        }
        $action_button = "
                <a tabindex='0' role='button' class='menu-popover btn $secondary_btn_class d-flex justify-content-center align-items-center' data-bs-toogle='popover' data-bs-container='body' data-bs-placement='left' data-bs-html='true' data-bs-trigger='manual' data-bs-content='$action_list' aria-label='$langListChoices'>
                    <span class='fa $secondary_icon'></span>
                    $tmp_class_title

                </a>";
    }

    return $primary_form_begin .
         "<div class='btn-group btn-group-sm btn-group-default gap-2' role='group' aria-label='...'>
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
    if (!$dir) {
        return;
    }
    make_dir($dst);
    while (false !== ( $file = readdir($dir))) {
        if ($file != '.' and $file != '..') {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
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
        $langCourses,$langCourse,$langNationalOpenCourses,
        $themeimg, $langOpenCourses;

    $openCoursesNum = Database::get()->querySingle("SELECT COUNT(id) as count FROM course_review WHERE is_certified = 1")->count;

        $openFacultiesUrl = $urlAppend . 'modules/course_metadata/openfaculties.php';
        $openCoursesExtraHTML = "

                <div class='card card-transparent border-0 bg-transparent h-100'>
                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                        <div class='d-flex justify-content-start align-items-center'>
                            <div class='text-heading-h3 mb-4'>$langOpenCourses</div>
                        </div>
                    </div>
                    <div class='card-body p-0'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3 h-100 '>
                            <div class='card-body'>
                                <div class='row row-cols-1 row-cols-md-2 g-4'>

                                    <div class='col d-flex justify-content-center align-items-center'>
                                        <img style='width:650px;' class='openCoursesImg' src='$themeimg/openCoursesImg.png' alt='".q($langListOpenCourses)."'>
                                    </div>

                                    <div class='col d-flex justify-content-center align-items-center'>
                                        <div>
                                            <div class='d-flex justify-content-center align-items-center w-100 mt-4'>
                                                <a class='d-flex gap-1 align-items-center' target='_blank' href='$openFacultiesUrl' aria-label='$langCourses - (opens in a new tab)'>
                                                    <i class='fa-solid fa-book-open fa-xl'></i>
                                                    <span class='text-uppercase TextBold Primary-500-cl fs-5'>$openCoursesNum</span>
                                                    <span class='text-uppercase TextBold Primary-500-cl fs-5'>
                                                        " .(($openCoursesNum == 1)? $langCourses: $langCourse) . "
                                                    </span>
                                                </a>
                                            </div>

                                            <div class='d-flex justify-content-center align-items-center w-100 mt-4'>
                                                <a class='btn opencourses_btn d-inline-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank' aria-label='$langNationalOpenCourses - (opens in a new tab)'>
                                                    $langNationalOpenCourses
                                                    <span class='fa-solid fa-chevron-right ms-2'></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ";

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
    global $urlAppend, $uid, $course_code, $course_id, $module_id, $modules;

    $course_status = course_status($course_id);
    if ($course_status != COURSE_INACTIVE) { // No RSS feed for inactive courses
        $module_name = $modules[$module_id]['link'];
        $link = 'modules/' . $module_name . '/rss.php?c=' . $course_code;
        if ($course_status != COURSE_OPEN or $_SESSION['courses'][$course_code]) {
            $link .= '&uid=' . $uid .  '&token=' .
                token_generate($module_name . $uid . $course_code);
        }
        define('RSS', $urlAppend . $link);
    }
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


/**
 * @brief check PHP version
 * @param $version
 * @return string
 */
function checkPHPVersion($version) {

    if (version_compare(PHP_VERSION, $version) > 0) {
        $content = "<li class='list-group-item element'>" . icon('fa-check') . " " . PHP_VERSION . "</li>";
    } else {
        $content = "<li class='list-group-item element text-danger'>" . icon('fa-xmark') . " " . PHP_VERSION . "</li>";
    }
    return $content;
}


/**
 * @brief check if given PHP extension is installed
 * @param $extensionName
 * @return string
 */
function warnIfExtNotLoaded($extensionName) {

    global $langModuleNotInstalled, $langReadHelp, $langHere;

    if (extension_loaded($extensionName)) {
        $content = '<li class="list-group-item element">' . icon('fa-check') . ' ' . $extensionName . '</li>';
    } else {
        $content = "
                <li class='list-group-item element text-danger'>" . icon('fa-xmark') . " $extensionName
                <strong>$langModuleNotInstalled</strong>
                (<a href='http://www.php.net/$extensionName' target=_blank>$langReadHelp $langHere</a>)
                </li>";
    }
    return $content;
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
 * @return array
 */
function array_value_recursive($key, array $arr){
    $val = array();
    array_walk_recursive($arr, function($v, $k) use($key, &$val){
        if($k == $key) array_push($val, $v);
    });
    return $val;
}

/**
 @brief reindex array keys so that they start from 1 (not from 0)
 * @param $a array
 * @return array
 */
function reindex_array_keys_from_one($a) {

    return array_combine(range(1, count($a)), array_values($a));
}


/**
 * @brief convert ',' to '.'
 * @param $str
 * @return array|string|string[]
 */
function fix_float($str) {
    if (!$str) {
        return 0.0;
    }
    return str_replace(',', '.', $str);
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

    // Non-ASCII characters in email are considered invalid
    if (!ctype_print($email)) {
        return false;
    }

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
                "<a href='{$urlAppend}modules/course_info/index.php?course=$course_code'>", '</a>');
        } else {
            $message = $langCourseInvalidDepartment;
        }
        Session::flash('message',$message);
        Session::flash('alert-class', 'alert-warning');
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
    session_regenerate_id();

    if (get_config('double_login_lock')) {
        Database::get()->query('INSERT INTO login_lock
            SET user_id = ?d, session_id = ?s, ts = NOW()
            ON DUPLICATE KEY UPDATE user_id = ?d, ts = NOW()',
            $options['user_id'], session_id(), $options['user_id']);
        Database::get()->query('DELETE FROM login_lock
            WHERE ts < ' . DBHelper::timeAfter(-ini_get('session.gc_maxlifetime')));
    }

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

    if (!isset($_SESSION['courses']) and $options['user_id']) {
        Database::get()->queryFunc('SELECT course.code, course_user.status
            FROM course JOIN course_user
              ON course.id = course_user.course_id
             AND course_user.user_id = ?d
             AND (course.visible != ?d OR course_user.status = ?d)',
            function ($course) {
                $_SESSION['courses'][$course->code] = $course->status;
            }, $options['user_id'], COURSE_INACTIVE, USER_TEACHER);
    }

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


/**
 * @brief Enable display of bootbox password dialog for assignments and
 *        exercises and warn about paused exercises
 */
function enable_password_bootbox() {
    global $head_content, $langCancel, $langSubmit,
        $langAssignmentPasswordModalTitle, $langExercisePasswordModalTitle,
        $langTheFieldIsRequired, $langTemporarySaveNotice2,
        $langContinueAttemptNotice, $langContinueAttempt;

    static $enabled = false;

    if ($enabled) {
        return;
    } else {
        $enabled = true;
        $head_content .= "
        <script>
            var lang = {
                assignmentPasswordModalTitle: '" . js_escape($langAssignmentPasswordModalTitle). "',
                exercisePasswordModalTitle: '" . js_escape($langExercisePasswordModalTitle). "',
                theFieldIsRequired: '" . js_escape($langTheFieldIsRequired). "',
                temporarySaveNotice: '" . js_escape($langTemporarySaveNotice2). "',
                continueAttemptNotice: '" . js_escape($langContinueAttemptNotice). "',
                continueAttempt: '" . js_escape($langContinueAttempt). "',
                cancel: '" . js_escape($langCancel). "',
                submit: '" . js_escape($langSubmit). "',
            };
            $(function () {
                $(document).on('click', '.ex_settings, .password_protected', unit_password_bootbox);
            });
        </script>";
    }
}


/*
 * @brief return exercise user attempt status legend
 */
function get_exercise_attempt_status_legend($status) {

    global $langAttemptActive, $langAttemptCompleted, $langAttemptPending, $langAttemptPaused, $langAttemptCanceled;

    switch ($status) {
        case ATTEMPT_ACTIVE:
            return $langAttemptActive;
        case ATTEMPT_COMPLETED:
            return $langAttemptCompleted;
        case ATTEMPT_PENDING:
            return $langAttemptPending;
        case ATTEMPT_PAUSED:
            return $langAttemptPaused;
        case ATTEMPT_CANCELED:
            return $langAttemptCanceled;
    }
}

/**
 * @brief translate messages in blade views
 * @param $var_name
 * @param $var_array
 * @return mixed|string
 */
function trans($var_name, $var_array = []) {
    if (preg_match("/\['.+'\]/", $var_name)) {
        preg_match_all("([^\['\]]+)", $var_name, $matches);
        global ${$matches[0][0]};

        if ($var_array) {
            return vsprintf(${$matches[0][0]}[$matches[0][1]], $var_array);
        } else {
            return ${$matches[0][0]}[$matches[0][1]];
        }
    } else {
        global ${$var_name};

        if ($var_array) {
            return vsprintf(${$var_name}, $var_array);
        } else {
            return ${$var_name};
        }
    }
}

function get_platform_logo($size='normal') {
    global $themeimg, $urlAppend;

    if ($size == 'small') {
        $logo_img = $themeimg . '/eclass-new-logo.svg';
    } else {
        $logo_img = $themeimg . '/eclass-new-logo.svg';
    }

    $theme_id = get_config('theme_options_id');
    $bg_color = '#ffffff';
    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $bg_color = $theme_options_styles['leftNavBgColor'];

        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        if ($size == 'small') {
            if (isset($theme_options_styles['imageUploadSmall'])) {
                $logo_img = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
            }
        } else {
            if (isset($theme_options_styles['imageUpload'])) {
                $logo_img = "$urlThemeData/$theme_options_styles[imageUpload]";
            }

        }
    }
    $logo = "<div style='clear: right; background-color: $bg_color; padding: 1rem; margin-bottom: 2rem;'>
                <img style='float: left; height:6rem;' src='$logo_img'>
            </div>";

    return $logo;
}


/**
 * @brief Set the content disposition header for file display / download, with
 *        appropriate encoding
 */
function set_content_disposition($disposition, $filename) {
    $filename = strtr($filename, ['"\'' => '__', ',' => '__']);
    //$filename = strtr($filename, '"\'', '__');
    header("Content-Disposition: $disposition; filename*=UTF-8''" . rawurlencode($filename));
}


/**
 * @brief Show the form image regarding active theme
 */
function get_form_image() {
    global $urlAppend, $themeimg, $theme_id;

    $form_image = $themeimg.'/form-image.png';

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        if (isset($theme_options_styles['imageUploadForm'])) {
            $form_image = "$urlThemeData/$theme_options_styles[imageUploadForm]";
        }

    }

    return $form_image;
}

/**
 * @brief Show the registration form image regarding active theme
 */
function get_registration_form_image() {
    global $urlAppend, $themeimg, $theme_id;

    $reg_image = $themeimg.'/RegImg.png';

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        if (isset($theme_options_styles['imageUploadRegistration'])) {
            $reg_image = "$urlThemeData/$theme_options_styles[imageUploadRegistration]";
        }

    }

    return $reg_image;
}

/**
 * @brief Show the FAQ image regarding active theme
 */
function get_FAQ_image() {
    global $urlAppend, $themeimg, $theme_id;

    $faq_image = $themeimg.'/faqImg.png';

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        if (isset($theme_options_styles['imageUploadFaq'])) {
            $faq_image = "$urlThemeData/$theme_options_styles[imageUploadFaq]";
        }

    }

    return $faq_image;
}


/**
 * @brief Tinymce inside widget
 */
function tinymce_widget($type){
    global $uid,$course_id;

    $data_widget = array();
    $tmp_data_widget = array();
    $final_data_widget = array();
    $active_lang_codes = explode(' ', get_config('active_ui_languages'));
    $view_data['final_data_portfolioSide_widget'] = array();

    if($type == 1){
        $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                    WHERE widget_area_id = ?d
                                                    ORDER BY position ASC", HOME_PAGE_MAIN);
    }elseif($type == 2){
        $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                    WHERE widget_area_id = ?d
                                                    ORDER BY position ASC", HOME_PAGE_SIDEBAR);

    }elseif($type == 3){
        $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                    WHERE (user_id = ?d OR user_id IS NULL)
                                                    AND widget_area_id = ?d
                                                    ORDER BY position ASC",$uid, PORTFOLIO_PAGE_MAIN);
    }elseif($type == 4){
        $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                    WHERE (user_id = ?d OR user_id IS NULL)
                                                    AND widget_area_id = ?d
                                                    ORDER BY position ASC",$uid, PORTFOLIO_PAGE_SIDEBAR);
    }elseif($type == 5){
        if(isset($course_id) and $course_id){
            $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                        WHERE widget_area_id = ?d
                                                        AND (course_id = ?d OR (course_id IS NULL AND position >= ?d))
                                                        ORDER BY position ASC", COURSE_HOME_PAGE_MAIN, $course_id,0);
        }else{
            $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                            WHERE widget_area_id = ?d AND course_id IS NULL
                                                            AND user_id IS NULL AND position >= ?d
                                                            ORDER BY position ASC",COURSE_HOME_PAGE_MAIN, 0);
        }
    }elseif($type == 6){
        if(isset($course_id) and $course_id){
            $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                        WHERE widget_area_id = ?d
                                                        AND (course_id = ?d OR (course_id IS NULL AND position >= ?d))
                                                        ORDER BY position ASC",COURSE_HOME_PAGE_SIDEBAR, $course_id,0);
        }else{
            $getWidget = database::get()->queryArray("SELECT options FROM widget_widget_area
                                                        WHERE widget_area_id = ?d AND course_id IS NULL
                                                        AND user_id IS NULL AND position >= ?d
                                                        ORDER BY position ASC",COURSE_HOME_PAGE_SIDEBAR, 0);
        }

    }

    if(count($getWidget) > 0){
        foreach ($getWidget as $key => $option) {
            $data_widget[] = unserialize($option->options);
        }
    }
    if(count($data_widget) > 0){
        foreach($data_widget as $widget){
            if($widget){
                foreach(array_keys($widget) as $key){
                    $tmp_data_widget[$key] = rich_text_editor($key,3,40,$widget[$key]);
                }
            }else{
                foreach($active_lang_codes as $c){
                    $empty_Key = 'text_'.$c;
                    $tmp_data_widget[$empty_Key] = rich_text_editor($empty_Key,3,40,'');
                }
            }

            $final_data_widget[] = $tmp_data_widget;
        }
    }

    return $final_data_widget;
}


/**
 * @brief Getting the current text color for the tinymce regarding the active theme
 */
function get_tinymce_color_text() {
    global $urlAppend, $tinymce_color_text;

    $theme_id = get_config('theme_options_id');
    if ($theme_id > 0) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        if (isset($theme_options_styles['ClTextEditor'])) {
            $tinymce_color_text = "$theme_options_styles[ClTextEditor]";
        }
    }
}



/**
 * @brief get user courses
 * @param $uid
 * @return array|DBResult|null
 */
function getUidCourses($uid)
{
    global $uid;

    $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.course_license course_license,
                             course.lang,
                             course.visible visible,
                             course.description description,
                             course.course_image course_image,
                             course.popular_course popular_course,
                             course_user.status status,
                             course_user.favorite favorite
                        FROM course JOIN course_user
                            ON course.id = course_user.course_id
                            AND course_user.user_id = ?d
                            AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                        ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid);

    return $myCourses;
}

/*
 * Function module_path
 *
 * Returns a canonicalized form of the current request path to use in matching
 * the current module
 *
 */
function module_path($path) {
    global $urlAppend, $urlServer;

    if (strpos($path, 'modules/units/insert.php') !== false) {
        if (strpos($path, '&dir=') !== false) {
            return 'document';
        }
    }
    if (strpos($path, 'listreq.php') !== false) {
        if (strpos($path, '?type=user') !== false) {
            return 'listreq-user';
        } else {
            return 'listreq';
        }
    }

    $original_path = $path;
    $path = preg_replace('/\?[a-zA-Z0-9=&;]+$/', '', $path);
    $path = str_replace(array($urlServer, $urlAppend, 'index.php'),
        array('/', '/', ''), $path);
    if (strpos($path, '/course_info/restore_course.php') !== false) {
        return 'course_info/restore_course.php';
    } elseif (strpos($path, '/info/') !== false) {
        return preg_replace('|^.*(info/.*\.php)|', '\1', $path);
    } elseif (strpos($path, '/admin/') !== false) {
        $new_path = preg_replace('|^.*(/admin/.*)|', '\1', $path);
        if ($new_path == '/admin/auth_process.php') {
            return '/admin/auth.php';
        } elseif ($new_path == '/admin/listusers.php' or $new_path == '/admin/edituser.php') {
            return '/admin/search_user.php';
        }
        return $new_path;
    } elseif (strpos($path, '/main/unreguser.php') !== false or
        (strpos($path, '/main/profile') !== false and
            strpos($path, 'personal_stats') === false)) {
        return 'main/profile';
    } elseif (strpos($path, '/main/') !== false) {
        return preg_replace('|^.*(main/.*\.php)|', '\1', $path);
    } elseif (preg_match('+/auth/(opencourses|listfaculte)\.php+', $path)) {
        return '/auth/courses.php';
    } elseif (preg_match('+/auth/(registration|newuser|altnewuser|formuser|altsearch)\.php+', $path)) {
        return '/auth/registration.php';
    } elseif (isset($GLOBALS['course_code']) and
        strpos($path, '/courses/' . $GLOBALS['course_code']) !== false) {
        return 'course_home';
    } elseif (strpos($path, '/lti_consumer/launch.php') !== false or
        strpos($path, '/lti_consumer/load.php') !== false) {
        $lti_path = str_replace(array($urlServer, $urlAppend, '&amp;'), array('/', '/', '&'), $original_path);
        return $lti_path;
    }
    return preg_replace('|^.*modules/([^/]+)/.*$|', '\1', $path);
}


/**
 * @brief Theme initialization
*/
function theme_initialization() {

    global $urlAppend, $urlServer, $langRegistration, $langFaq,
           $head_content, $webDir, $theme_id, $container,
           $leftsideImg, $eclass_banner_value, $PositionFormLogin,
           $logo_img, $image_footer, $loginIMG, $themeimg, $favicon_img,
           $logo_img_small;

    // Add Theme Options styles
    $styles_str = '';
    $leftsideImg = '';
    $image_footer = '';
    $PositionFormLogin = 0;
    $eclass_banner_value = 1;
    $container = 'container';
    $forms_image = 'form-image-modules';
    $logo_img = $themeimg.'/eclass-new-logo.svg';
    $logo_img_small = $themeimg.'/eclass-new-logo.svg';
    $loginIMG = $themeimg.'/loginIMG.png';
    $favicon_img = $urlAppend . 'resources/favicon/openeclass_128x128.png';

    //////////////////////////////////////////  Theme creation  ///////////////////////////////////////////////

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;

        $styles_str .= "

            #submitSearch{
                gap: 8px;
            }
            #search_terms{
                border-color: transparent;
                background-color: transparent;
            }
            .inputSearch::placeholder{
                background-color: transparent;
            }

            .diffEqual {
                background-color: transparent !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover{
                color: #C44601;
            }

            .calendarViewDatesTutorGroup .fc-list-table .fc-list-heading .fc-widget-header {
                background: transparent;
            }

        ";

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND COLOR OF BRIEF PROFILE IN PORTOFOLIO /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BriefProfilePortfolioBgColor']) && !empty($theme_options_styles['BriefProfilePortfolioBgColor_gr'])){
            $new_gradient_str_bpr = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[BriefProfilePortfolioBgColor], $theme_options_styles[BriefProfilePortfolioBgColor_gr])";
            $styles_str .= "
                .portfolio-profile-container{
                    background: $new_gradient_str_bpr;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// TEXT COLOR OF BRIEF PROFILE IN PORTOFOLIO ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BriefProfilePortfolioTextColor'])){
            $styles_str .= "
                .portofolio-text-intro{
                    color: $theme_options_styles[BriefProfilePortfolioTextColor] !important;
                  }

                  .portfolio-texts *{
                    color: $theme_options_styles[BriefProfilePortfolioTextColor] !important;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OR BACKGROUND IMAGE OF BODY ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColor']) || !empty($theme_options_styles['bgImage'])) {
            $background_type = "";
            if (isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'stretch') {
                $background_type .= "background-size: 100% 100%;";
            } elseif(isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'fix') {
                $background_type .= "background-size: 100% 100%;background-attachment: fixed;";
            }
            $bg_image = isset($theme_options_styles['bgImage']) ? " url('$urlThemeData/$theme_options_styles[bgImage]')" : "";
            $bg_color = isset($theme_options_styles['bgColor']) ? $theme_options_styles['bgColor'] : "#ffffff";
            $LinearGr = (isset($theme_options_styles['bgOpacityImage']) && isset($theme_options_styles['bgColor'])) ? "linear-gradient($bg_color,$bg_color)," : "";

            if(isset($theme_options_styles['bgOpacityImage'])){
                $styles_str .= "
                    body{
                        background: $LinearGr$bg_image;$background_type
                    }
                ";
            }else{
                $styles_str .= "
                    body{
                        background: $bg_color$bg_image;$background_type
                    }
                ";
            }

        }

        $gradient_str = 'radial-gradient(closest-corner at 30% 60%, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0))';

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// BACKGROUND COLOR OF JUMBOTRON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['loginJumbotronBgColor']) && !empty($theme_options_styles['loginJumbotronRadialBgColor'])) {
            $gradient_str = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[loginJumbotronRadialBgColor], $theme_options_styles[loginJumbotronBgColor])";
            $styles_str .= "
                .jumbotron.jumbotron-login{
                    background: $gradient_str;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BACKGROUND IMAGE OF JUMBOTRON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['loginImg'])){
                $styles_str .= "
                    .jumbotron.jumbotron-login{
                        background: $gradient_str, url('$urlThemeData/$theme_options_styles[loginImg]');
                        border:0px;
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-position: center;
                    }
                ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// MAX HEIGHT OF JUMBOTRON ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['maxHeightJumbotron'])){
            $styles_str .= "
                @media(min-width:992px){
                    .jumbotron.jumbotron-login{
                        min-height: $theme_options_styles[maxHeightJumbotron]px;
                    }
                }
            ";
        }

        if (isset($theme_options_styles['MaxHeightMaxScreenJumbotron'])){
            $styles_str .= "
                @media(min-width:992px){
                    .jumbotron.jumbotron-login{
                        min-height: calc(100vh - 80px);
                    }
                    body:has(.fixed-announcement) .jumbotron.jumbotron-login{
                        min-height: calc(100vh - 80px - 60px);
                    }
                }
                @media(max-width:991px){
                    .jumbotron.jumbotron-login{
                        min-height: calc(100vh - 56px);
                    }
                    body:has(.fixed-announcement) .jumbotron.jumbotron-login{
                        min-height: calc(100vh - 56px - 60px);
                    }
                }
            ";
        }

        if (isset($theme_options_styles['MaxHeightHalfMaxScreenJumbotron'])){
            $styles_str .= "
                @media(min-width:992px){
                    .jumbotron.jumbotron-login{
                        min-height: calc((100vh - 80px)/2);
                    }
                    body:has(.fixed-announcement) .jumbotron.jumbotron-login{
                        min-height: calc((100vh - 80px - 60px)/2);
                    }
                }
                @media(max-width:991px){
                    .jumbotron.jumbotron-login{
                        min-height: calc((100vh - 56px)/2);
                    }
                    body:has(.fixed-announcement) .jumbotron.jumbotron-login{
                        min-height: calc((100vh - 56px - 60px)/2);
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BACKGROUND IMAGE OF LOGIN FORM ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['loginImgL'])){
            $loginIMG =  "$urlThemeData/$theme_options_styles[loginImgL]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
         /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////// FAVICON UPLOAD //////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['faviconUpload'])){
            $favicon_img =  "$urlThemeData/$theme_options_styles[faviconUpload]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// TEXT COLOR OF HOMEPAGE_INTRO ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['loginTextColor'])){
            $styles_str .= "
                .jumbotron-intro-text *{
                    color: $theme_options_styles[loginTextColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// MAX WIDTH OF HOMEPAGE_INTRO ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['maxWidthTextJumbotron'])){
            $styles_str .= "
                .jumbotron-intro-text{
                    max-width: $theme_options_styles[maxWidthTextJumbotron]px;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR OF HOMEPAGE_INTRO ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['loginTextBgColor'])){
            $styles_str .= "
                @media(min-width:992px){
                    .jumbotron-intro-text{
                        border-radius:8px;
                        padding: 5px 15px 15px 15px;
                        background-color: $theme_options_styles[loginTextBgColor];
                    }
                }
            ";
            // If jumbotron-intro-text has rgba which contains zero at the end (a) then change padding(left-right) to zero
            preg_match_all('!\d+!', $theme_options_styles['loginTextBgColor'], $matches);
            if(count($matches) > 0){
                $counterRgb = 0;
                foreach($matches as $match){
                    foreach($match as $value){
                        if(count($match) == 4 && $counterRgb == 3 && $value == 0){
                            $styles_str .= "
                                @media(min-width:992px){
                                    .jumbotron-intro-text{
                                        padding: 5px 0px 15px 0px;
                                    }
                                }
                            ";
                        }
                        $counterRgb++;
                    }
                }
            }

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF HOMEPAGE_INTRO FOR SMALL SCREENS /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['loginTextBgColorSmallScreen'])){
            $styles_str .= "
                @media(max-width:991px){
                    .jumbotron-intro-text{
                        border-radius:8px;
                        padding: 5px 15px 15px 15px;
                        background-color: $theme_options_styles[loginTextBgColorSmallScreen];
                    }
                }
            ";
            // If jumbotron-intro-text has rgba which contains zero at the end (a) then change padding(left-right) to zero
            preg_match_all('!\d+!', $theme_options_styles['loginTextBgColorSmallScreen'], $matches_small);
            if(count($matches_small) > 0){
                $counterRgbSmall = 0;
                foreach($matches_small as $match_s){
                    foreach($match_s as $value){
                        if(count($match_s) == 4 && $counterRgbSmall == 3 && $value == 0){
                            $styles_str .= "
                                @media(max-width:991px){
                                    .jumbotron-intro-text{
                                        padding: 5px 0px 15px 0px;
                                    }
                                }
                            ";
                        }
                        $counterRgbSmall++;
                    }
                }
            }
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// POSITION OF HOMEPAGE_INTRO //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(isset($theme_options_styles['PositionJumbotronText'])){
            if($theme_options_styles['PositionJumbotronText'] == 0){
                $styles_str .= "
                    @media(min-width:992px){
                        .jumbotron.jumbotron-login{
                            display: flex;
                            align-items: top;
                        }
                    }
                ";
            }elseif($theme_options_styles['PositionJumbotronText'] == 1){
                $styles_str .= "
                    @media(min-width:992px){
                        .jumbotron.jumbotron-login{
                            display: flex;
                            align-items: center;
                        }
                    }
                ";
            }elseif($theme_options_styles['PositionJumbotronText'] == 2){
                $styles_str .= "
                    @media(min-width:992px){
                        .jumbotron.jumbotron-login{
                            display: flex;
                            align-items: end;
                        }
                    }
                ";
            }

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// POSITION OF LOGIN-FORM ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement']=='center-position') {
            $PositionFormLogin = 1;
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// FLUID OR BOXED SIZE OF PLATFORM ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['fluidContainerWidth'])){
            $container = 'container-fluid';
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// SHOW - HIDE ECLASS_BANNER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['openeclassBanner'])){
            $styles_str .= "#openeclass-banner {display: none;}";
            $eclass_banner_value = 0;
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// LINK BACKGROUND OF BANNER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorLinkBanner'])){
            $styles_str .= "
                .banner-link{
                    background-color: $theme_options_styles[BgColorLinkBanner];
                    padding: 10px 8px 14px 8px;
                    border-radius: 6px;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////// TYPOGRAPHY ///////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ColorHyperTexts'])){
            $styles_str .= "
                caption,
                body,
                h1,h2,h3,h4,h5,h6,
                p,strong,.li-indented,li,small,
                .Neutral-900-cl,
                .agenda-comment,
                .form-label,
                .default-value,
                label,
                th,
                td,
                .panel-body,
                .card-body,
                div,
                .visibleFile,
                .list-group-item,
                .help-block,
                .control-label-notes,
                .title-default,
                .modal-title-default,
                .text-heading-h2,
                .text-heading-h3,
                .text-heading-h4,
                .text-heading-h5,
                .text-heading-h6,
                .action-bar-title,
                .breadcrumb-item.active,
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    color:$theme_options_styles[ColorHyperTexts];
                }


                .dataTables_wrapper .dataTables_length,
                .dataTables_wrapper .dataTables_filter,
                .dataTables_wrapper .dataTables_info,
                .dataTables_wrapper .dataTables_processing,
                .dataTables_wrapper .dataTables_paginate {
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .circle-img-contant{
                    border: solid 1px $theme_options_styles[ColorHyperTexts];
                }

                .text-muted,
                .input-group-text{
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .c3-tooltip-container *{
                    background-color: #ffffff;
                    color: #2B3944;
                }

                .panel-default .panel-heading .panel-title,
                .panel-action-btn-default .panel-heading .panel-title {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }

                .panel-default .panel-heading,
                .panel-action-btn-default .panel-heading {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }

                .text-muted{
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .showCoursesBars:not(:has(.active)) i,
                .showCoursesPics:not(:has(.active)) i {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }
            ";
        }

        if(!empty($theme_options_styles['ColorRedText'])){
            $styles_str .= "
                .text-danger,
                .Accent-200-cl,
                .label.label-danger{
                    color: $theme_options_styles[ColorRedText] !important;
                }
            ";
        }
        if(!empty($theme_options_styles['ColorGreenText'])){
            $styles_str .= "
                .text-success,
                .Success-200-cl,
                .label.label-success{
                    color: $theme_options_styles[ColorGreenText] !important;
                }
                .active-unit::after{
                    background: $theme_options_styles[ColorGreenText] !important;
                }
            ";
        }

        if(!empty($theme_options_styles['ColorBlueText'])){
            $styles_str .= "
                .text-primary,
                .Primary-600-cl{
                    color: $theme_options_styles[ColorBlueText] !important;
                }
            ";
        }

        if(!empty($theme_options_styles['ColorOrangeText'])){
            $styles_str .= "
                .text-warning,
                .Warning-200-cl,
                .label.label-warning{
                    color: $theme_options_styles[ColorOrangeText] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND-COLOR HEADER'S WRAPPER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['BgColorWrapperHeader'])) {
            $styles_str .= "

                #bgr-cheat-header{
                    background-color: $theme_options_styles[BgColorWrapperHeader];
                }

                .offCanvas-Tools{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

                .navbar-learningPath,
                .header-container-learningPath{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR FOOTER'S WRAPPER /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorWrapperFooter'])) {
            $styles_str .= "

                #bgr-cheat-footer,
                .div_social{
                    background-color: $theme_options_styles[bgColorWrapperFooter];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// LINKS COLOR OF HEADER ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorHeader'])){
            $styles_str .= "


                .link-selection-language,
                .link-bars-options,
                .user-menu-btn .user-name,
                .user-menu-btn .fa-chevron-down{
                    color: $theme_options_styles[linkColorHeader];
                }

                .container-items .menu-item{
                    color: $theme_options_styles[linkColorHeader];
                }

                #search_terms,
                #search_terms::placeholder{
                    color:$theme_options_styles[linkColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass{
                    color:$theme_options_styles[linkColorHeader];
                }

                @media(max-width:991px){
                    .header-login-text{
                        color:$theme_options_styles[linkColorHeader];
                    }
                }

                .header-mobile-link{
                    color:$theme_options_styles[linkColorHeader];
                }

                .split-left,
                .split-content{
                    border-left: solid 1px $theme_options_styles[linkColorHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF ACTIVE LINK HEADER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveBgColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    background-color: $theme_options_styles[linkActiveBgColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR OF ACTIVE LINK HEADER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    color: $theme_options_styles[linkActiveColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// COLOR OF HOVER LINK IN HEADER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorHeader'])){
            $styles_str .= "
                .link-selection-language:hover,
                .link-selection-language:focus,
                .link-bars-options:hover,
                .link-bars-options:focus,
                .container-items .menu-item:hover,
                .container-items .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                #bgr-cheat-header:not(:has(.fixed)) .user-menu-btn:hover,
                #bgr-cheat-header:not(:has(.fixed)) .user-menu-btn:focus{
                    border-top: solid 4px $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .user-name,
                .user-menu-btn:focus .user-name{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .fa-chevron-down,
                .user-menu-btn:focus .fa-chevron-down{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass:hover,
                #bgr-cheat-header .fa-magnifying-glass:focus {
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                @media(max-width:991px){
                    .header-login-text:hover,
                    .header-login-text:focus{
                        color:$theme_options_styles[linkHoverColorHeader];
                    }
                }

                .header-mobile-link:hover,
                .header-mobile-link:focus{
                    color:$theme_options_styles[linkHoverColorHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO ACTIVE LINK IN HEADER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['HoveredActiveLinkColorHeader'])){
            $styles_str .= "

                .container-items .menu-item.active:hover,
                .container-items .menu-item.active:focus,
                .container-items .menu-item.active2:hover,
                .container-items .menu-item.active2:focus{
                    color: $theme_options_styles[HoveredActiveLinkColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// SHADOW TO THE BOTTOM SIDE INTO HEADER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['shadowHeader'])){
            $styles_str .= "
                #bgr-cheat-header{ box-shadow: none; }
            ";
        }else{
            $styles_str .= "
                #bgr-cheat-header{ box-shadow: 1px 2px 6px rgba(43,57,68,0.04); }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// LINKS COLOR OF FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item {
                    color: $theme_options_styles[linkColorFooter];
                }

                .copyright,
                .social-icon-tool,
                .a_tools_site_footer {
                    color:$theme_options_styles[linkColorFooter];
                }

                .footer-text *{
                    color: $theme_options_styles[linkColorFooter] ;
                }
                .border-bottom-footer-text{
                    border-bottom: solid 1px $theme_options_styles[linkColorFooter] ;
                    opacity: 0.3;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR OF HOVER LINK IN FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item:hover,
                .container-items-footer .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorFooter];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus {
                    color: $theme_options_styles[linkHoverColorFooter];
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////// TEXT COLOR OF TABS /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link{
                    color: $theme_options_styles[clTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// HOVERED TEXT COLOR OF TABS /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link:hover{
                    color: $theme_options_styles[clHoveredTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACTIVE TABS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link.active{
                    color: $theme_options_styles[clActiveTabs];
                    border-bottom: solid 2px $theme_options_styles[clActiveTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACCORDIONS  //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn{
                    color: $theme_options_styles[clAccordions];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TEXT OF ACCORDIONS  ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomAccordions'])){
            $styles_str .= "
                .group-section .list-group-item{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }

                .border-bottom-default{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TEXT OF ACCORDIONS  /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn:hover{
                    color: $theme_options_styles[clHoveredAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR TEXT OF ACTIVE ACCORDIONS  //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn[aria-expanded='true'],
                .group-section .list-group-item .accordion-btn.showAll{
                    color: $theme_options_styles[clActiveAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF LIST GROUP //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgLists'])){
            $styles_str .= "
                .list-group-item.list-group-item-action{
                    background-color: $theme_options_styles[bgLists];
                }
                .list-group-item.list-group-item-action:hover{
                    background-color: $theme_options_styles[bgLists];
                }

                .list-group-item.element{
                    background-color: $theme_options_styles[bgLists];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }

                .profile-pers-info-row{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR LINK OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a,
                .list-group-item.element a{
                    color: $theme_options_styles[clLists];
                }

                .list-group-item.list-group-action a span,
                .list-group-item.element a span{
                    color: $theme_options_styles[clLists];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// HOVERED COLOR LINK OF LIST GROUP ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a:hover,
                .list-group-item.element a:hover{
                    color: $theme_options_styles[clHoveredLists];
                }

                .list-group-item.list-group-item-action a span:hover,
                .list-group-item.element a span:hover{
                    color: $theme_options_styles[clHoveredLists];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// ADD PADDING TO THE LIST GROUP /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['AddPaddingListGroup'])){
            $styles_str .= "
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .homepage-annnouncements-container .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            ";
        }else{
            $styles_str .= "
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR OF SECONDARY BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgWhiteButtonColor'])) {
            $styles_str .= "
                .submitAdminBtn,
                .cancelAdminBtn,
                .opencourses_btn {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn-outline-primary {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .quickLink{
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .menu-popover{
                    background: $theme_options_styles[bgWhiteButtonColor];
                }

                .bs-placeholder.submitAdminBtn{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .showSettings{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn.btn-default {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .pagination-glossary .page-item .page-link{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .mycourses-pagination .page-item .page-link {
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .btn.btn-secondary{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .btn-exercise-nav[type=submit] {
                    background-color:  $theme_options_styles[bgWhiteButtonColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF SECONDARY BUTTON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn,
                .cancelAdminBtn,
                .opencourses_btn {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn-outline-primary {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .submitAdminBtn .fa-solid::before,
                .submitAdminBtn .fa-regular::before,
                .submitAdminBtn .fa-brands::before,
                .submitAdminBtn span.fa::before{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .quickLink{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .menu-popover{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .bs-placeholder .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .showSettings{
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn.btn-default {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .pagination-glossary .page-item .page-link{
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .showCoursesBars,
                .showCoursesBars:hover,
                .showCoursesBars:focus,
                .showCoursesPics,
                .showCoursesPics:hover,
                .showCoursesPics:focus{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .mycourses-pagination .page-item .page-link {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .btn.btn-secondary{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .btn-exercise-nav[type=submit] {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR OF SECONDARY BUTTON ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonBorderTextColor'])) {
            $styles_str .= "
                .submitAdminBtn,
                .cancelAdminBtn,
                .opencourses_btn {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor] !important;
                }

                .btn-outline-primary {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .quickLink{
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .menu-popover{
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .btn.btn-default {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button{
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .pagination-glossary .page-item .page-link{
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .showSettings{
                    border-color: $theme_options_styles[whiteButtonBorderTextColor] !important;
                }

                .mycourses-pagination .page-item .page-link {
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .btn.btn-secondary{
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .btn-exercise-nav[type=submit] {
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED TEXT COLOR OF SECONDARY BUTTON ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover,
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus,
                .cancelAdminBtn:focus,
                .opencourses_btn:focus,
                .submitAdminBtn:active,
                .cancelAdminBtn:active,
                .opencourses_btn:active {
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .submitAdminBtn:hover .fa-solid::before,
                .submitAdminBtn:hover .fa-regular::before,
                .submitAdminBtn:hover .fa-brands::before,
                .submitAdminBtn:hover span.fa::before,
                .submitAdminBtn:focus .fa-solid::before,
                .submitAdminBtn:focus .fa-regular::before,
                .submitAdminBtn:focus .fa-brands::before,
                .submitAdminBtn:focus span.fa::before,
                .submitAdminBtn:active .fa-solid::before,
                .submitAdminBtn:active .fa-regular::before,
                .submitAdminBtn:active .fa-brands::before,
                .submitAdminBtn:active span.fa::before{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid,
                .quickLink:focus,
                .quickLink:focus .fa-solid,
                .quickLink:active,
                .quickLink:active .fa-solid{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .menu-popover:hover,
                .menu-popover:focus,
                .menu-popover:active{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .bs-placeholder:hover .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .showSettings:hover{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .mycourses-pagination .page-item .page-link:hover,
                .mycourses-pagination .page-item .page-link:focus {
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .btn.btn-secondary:hover,
                .btn.btn-secondary:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .btn-exercise-nav[type=submit]:hover,
                .btn-exercise-nav[type=submit]:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// HOVERED BORDER COLOR OF SECONDARY BUTTON ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredBorderTextColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover,
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus,
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    border: solid 1px $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .showSettings:hover{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .mycourses-pagination .page-item .page-link:hover,
                .mycourses-pagination .page-item .page-link:focus {
                    border: solid 1px $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .btn.btn-secondary:hover,
                .btn.btn-secondary:focus{
                    border: solid 1px $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .btn-exercise-nav[type=submit]:hover,
                .btn-exercise-nav[type=submit]:focus{
                    border: solid 1px $theme_options_styles[whiteButtonHoveredBorderTextColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// HOVERED BACKGROUND COLOR OF SECONDARY BUTTON //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover,
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus,
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .bs-placeholder.submitAdminBtn:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .showSettings:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .mycourses-pagination .page-item .page-link:hover,
                .mycourses-pagination .page-item .page-link:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .btn.btn-secondary:hover,
                .btn.btn-secondary:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .btn-exercise-nav[type=submit]:hover,
                .btn-exercise-nav[type=submit]:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF PRIMARY BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonBgColor'])) {
            $styles_str .= "
                .submitAdminBtn.active{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .login-form-submit{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnDefault,
                input[type=submit],
                button[type=submit]{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnClassic.active {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }


                .carousel-indicators>button.active {
                    border-color: tranparent;
                    background-color: $theme_options_styles[buttonBgColor];
                }


                .pagination-glossary .page-item.active .page-link {
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .bootbox.show .modal-footer .submitAdminBtn,
                .modal.show .modal-footer .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .btn.btn-primary{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonBgColor];
                }


                .searchGroupBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .myProfileBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .showCoursesBars.active,
                .showCoursesPics.active{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .pagination-glossary .page-item.active .page-link{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .exist_event_session{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                    border-color: $theme_options_styles[buttonBgColor] !important;
                }

                .list-group-upgrade .list-group-item.element.active{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                }

                .btnScrollToTop{
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                .pagination page-link.active{
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                .mycourses-pagination .page-item .page-link.active {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                @media(min-width:992px){
                    .header-login-text{
                        border-color: $theme_options_styles[buttonBgColor] ;
                        background-color: $theme_options_styles[buttonBgColor] ;
                    }
                }

            ";

            $colorChevronLeftRight = "$theme_options_styles[buttonBgColor]";

            $FirstLeftSVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'%3E%3Cpath fill='$colorChevronLeftRight' d='M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z'/%3E%3C/svg";
            $SecondLeftSVG = 'url("data:image/svg+xml,%3C' . $FirstLeftSVG .'%3E")';

            $FirstRightSVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'%3E%3Cpath fill='$colorChevronLeftRight' d='M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z'/%3E%3C/svg";
            $SecondRightSVG = 'url("data:image/svg+xml,%3C' . $FirstRightSVG .'%3E")';

            $styles_str .= "
                .testimonials .slick-prev.slick-arrow {
                    background: $SecondLeftSVG no-repeat center;
                    background-size: contain;
                    height: 24px;
                    width: 24px;
                    border-radius: 50%;
                    z-index: 1;
                }

                .testimonials .slick-next.slick-arrow {
                    background: $SecondRightSVG no-repeat center;
                    background-size: contain;
                    height: 24px;
                    width: 24px;
                    border-radius: 50%;
                    z-index: 1;
                }

                .mce-btn{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                }

                .personal-calendar-header .btn-group .btn.active{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                }
            ";


        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED BACKCKGROUND COLOR OF PRIMARY BUTTON /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonHoverBgColor'])) {
            $styles_str .= "

                submitAdminBtn.active:hover{
                border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .login-form-submit:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .submitAdminBtnDefault:hover,
                input[type=submit]:hover,
                button[type=submit]:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor] ;
                    background-color: $theme_options_styles[buttonHoverBgColor] ;
                }

                .pagination-glossary .page-item.active .page-link:hover {
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }



                .bootbox.show .modal-footer .submitAdminBtn:hover,
                .modal.show .modal-footer .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .btn.btn-primary:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .searchGroupBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }


                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .showCoursesBars.active:hover,
                .showCoursesBars.active:focus,
                .showCoursesPics.active:hover,
                .showCoursesPics.active:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .mce-btn:hover,
                .mce-btn:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor] !important;
                }

                .personal-calendar-header .btn-group .btn.active:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor] !important;
                }

                .pagination-glossary .page-item.active:hover .page-link{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .exist_event_session:hover,
                .exist_event_session:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor] !important;
                    border-color: $theme_options_styles[buttonHoverBgColor] !important;
                }

                .btnScrollToTop:hover,
                .btnScrollToTop:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .mycourses-pagination .page-item .page-link.active:hover,
                .mycourses-pagination .page-item .page-link.active:focus {
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                @media(min-width:992px){
                    .header-login-text:hover,
                    .header-login-text:focus{
                        border-color: $theme_options_styles[buttonHoverBgColor] ;
                        background-color: $theme_options_styles[buttonHoverBgColor] ;
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF COLORFUL BUTTON //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn.active,
                .submitAdminBtn.active:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnDefault,
                .submitAdminBtnDefault:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .login-form-submit,
                .login-form-submit:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                input[type=submit],
                input[type=submit]:hover,
                button[type=submit],
                button[type=submit]:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnClassic.active {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor];
                }
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .pagination-glossary .page-item.active .page-link,
                .pagination-glossary .page-item.active .page-link:hover {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .bootbox.show .modal-footer .submitAdminBtn,
                .bootbox.show .modal-footer .submitAdminBtn:hover,
                .modal.show .modal-footer .submitAdminBtn,
                .modal.show .modal-footer .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .btn.btn-primary,
                .btn.btn-primary:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .submitAdminBtnDefault span,
                .submitAdminBtnDefault span:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .submitAdminBtnDefault .fa-solid::before,
                .submitAdminBtnDefault .fa-solid::before:hover,
                .submitAdminBtnDefault .fa-regular::before,
                .submitAdminBtnDefault .fa-regular::before:hover,
                .submitAdminBtnDefault .fa-brands::before,
                .submitAdminBtnDefault .fa-brands::before:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .searchGroupBtn span{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .myProfileBtn,
                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    color: $theme_options_styles[buttonTextColor] ;
                }


                .showCoursesBars.active,
                .showCoursesBars.active:hover,
                .showCoursesBars.active:focus,
                .showCoursesPics.active,
                .showCoursesPics.active:hover,
                .showCoursesPics.active:focus{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .showCoursesBars.active i,
                .showCoursesBars.active:hover i,
                .showCoursesBars.active:focus i,
                .showCoursesPics.active i,
                .showCoursesPics.active:hover i,
                .showCoursesPics.active:focus i {
                    color:$theme_options_styles[buttonTextColor];
                }

                .mce-btn,
                .mce-btn i{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .personal-calendar-header .btn-group .btn.active{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .pagination-glossary .page-item.active .page-link{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .calendarAddDaysCl .exist_event_session .fc-time span{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .list-group-upgrade .list-group-item.element.active span{
                   color: $theme_options_styles[buttonTextColor] !important;
                }

                .btnScrollToTop i,
                .btnScrollToTop:hover i,
                .btnScrollToTop:focus i,
                .btnScrollToTop:active i{
                    color:$theme_options_styles[buttonTextColor];
                }

                .mycourses-pagination .page-item .page-link.active,
                .mycourses-pagination .page-item .page-link.active:hover,
                .mycourses-pagination .page-item .page-link.active:focus {
                    color:$theme_options_styles[buttonTextColor];
                }

                @media(min-width:992px){
                    .header-login-text,
                    .header-login-text:hover,
                    .header-login-text:focus{
                         color:$theme_options_styles[buttonTextColor];
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO THE DELETION BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn,
                button[type=submit].deleteAdminBtn,
                input[type=submit].deleteAdminBtn {
                    border-color: $theme_options_styles[bgDeleteButtonColor];
                    background-color: $theme_options_styles[bgDeleteButtonColor];
                }

                .btn.btn-danger,
                .delete.confirmAction,
                .delete.delete_btn{
                    border-color: $theme_options_styles[bgDeleteButtonColor];
                    background-color: $theme_options_styles[bgDeleteButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO THE DELETION BUTTON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn,
                button[type=submit].deleteAdminBtn,
                input[type=submit].deleteAdminBtn {
                    color: $theme_options_styles[clDeleteButtonColor];
                }

                .btn.btn-danger,
                .delete.confirmAction,
                .delete.delete_btn{
                    color: $theme_options_styles[clDeleteButtonColor];
                }

                .deleteAdminBtn .fa-solid::before,
                .deleteAdminBtn .fa-regular::before,
                .deleteAdminBtn .fa-brands::before,
                .deleteAdminBtn .fa::before{
                    color: $theme_options_styles[clDeleteButtonColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND HOVERED COLOR TO THE DELETION BUTTON ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn:hover,
                button[type=submit].deleteAdminBtn:hover,
                input[type=submit].deleteAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                    background-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                }

                .btn.btn-danger:hover,
                .delete.confirmAction:hover,
                .delete.delete_btn:hover{
                    border-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                    background-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// TEXT HOVERED COLOR TO THE DELETION BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn:hover,
                button[type=submit].deleteAdminBtn:hover,
                input[type=submit].deleteAdminBtn:hover {
                    color: $theme_options_styles[clHoveredDeleteButtonColor];
                }

                .btn.btn-danger:hover,
                .delete.confirmAction:hover,
                .delete.delete_btn:hover{
                    color: $theme_options_styles[clHoveredDeleteButtonColor];
                }

                .deleteAdminBtn:hover .fa-solid::before,
                .deleteAdminBtn:hover .fa-regular::before,
                .deleteAdminBtn:hover .fa-brands::before,
                .deleteAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredDeleteButtonColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE SUCCESS BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn,
                button[type=submit].successAdminBtn,
                input[type=submit].successAdminBtn {
                    border-color: $theme_options_styles[bgSuccessButtonColor];
                    background-color: $theme_options_styles[bgSuccessButtonColor];
                }

                .btn.btn-success{
                    border-color: $theme_options_styles[bgSuccessButtonColor];
                    background-color: $theme_options_styles[bgSuccessButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO THE SUCCESS BUTTON //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn,
                button[type=submit].successAdminBtn,
                input[type=submit].successAdminBtn {
                    color: $theme_options_styles[clSuccessButtonColor];
                }

                .btn.btn-success{
                    color: $theme_options_styles[clSuccessButtonColor];
                }

                .successAdminBtn .fa-solid::before,
                .successAdminBtn .fa-regular::before,
                .successAdminBtn .fa-brands::before,
                .successAdminBtn .fa::before{
                    color: $theme_options_styles[clSuccessButtonColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND HOVERED COLOR TO THE SUCCESS BUTTON ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn:hover,
                button[type=submit].successAdminBtn:hover,
                input[type=submit].successAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                    background-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                }

                .btn.btn-success:hover{
                    border-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                    background-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// TEXT HOVERED COLOR TO THE SUCCESS BUTTON ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn:hover,
                button[type=submit].successAdminBtn:hover,
                input[type=submit].successAdminBtn:hover {
                    color: $theme_options_styles[clHoveredSuccessButtonColor];
                }

                .btn.btn-success:hover{
                    color: $theme_options_styles[clHoveredSuccessButtonColor];
                }

                .successAdminBtn:hover .fa-solid::before,
                .successAdminBtn:hover .fa-regular::before,
                .successAdminBtn:hover .fa-brands::before,
                .successAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredSuccessButtonColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE HELP BUTTON ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn {
                    border-color: $theme_options_styles[bgHelpButtonColor];
                    background-color: $theme_options_styles[bgHelpButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// TEXT COLOR TO THE HELP BUTTON ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn {
                    color: $theme_options_styles[clHelpButtonColor];
                }

                .helpAdminBtn .fa-solid::before,
                .helpAdminBtn .fa-regular::before,
                .helpAdminBtn .fa-brands::before,
                .helpAdminBtn .fa::before{
                    color: $theme_options_styles[clHelpButtonColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND HOVERED COLOR TO THE HELP BUTTON /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredHelpButtonColor];
                    background-color: $theme_options_styles[bgHoveredHelpButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// TEXT HOVERED COLOR TO THE HELP BUTTON ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn:hover {
                    color: $theme_options_styles[clHoveredHelpButtonColor];
                }

                .helpAdminBtn:hover .fa-solid::before,
                .helpAdminBtn:hover .fa-regular::before,
                .helpAdminBtn:hover .fa-brands::before,
                .helpAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredHelpButtonColor] !important;
                }

            ";
        }

        // Override button with white background if needed
        if (empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    border-color: #0073E6;
                    background-color: #ffffff;
                    color: #0073E6;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR CONTEXTUAL MENU ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    background-color: $theme_options_styles[bgContextualMenu];
                }

                .contextual-menu-user::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[bgContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR CONTEXTUAL MENU /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgBorderContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-menu-user{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-border{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BACKGROUND COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    background-color: $theme_options_styles[bgColorListMenu];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomListMenu];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// COLOR TOOL CONTEXTUAL MENU //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    color: $theme_options_styles[clListMenu];
                }

                .contextual-menu .list-group-item .settings-icons::before{
                    color: $theme_options_styles[clListMenu];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    background-color: $theme_options_styles[bgHoveredListMenu];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    color: $theme_options_styles[clHoveredListMenu];
                }
                .contextual-menu .list-group-item:hover .settings-icons::before{
                    color: $theme_options_styles[clHoveredListMenu];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// USERNAME COLOR CONTEXTUAL MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuUsername'])) {
            $styles_str .= "
                .contextual-menu-user .username-text,
                .contextual-menu-user .username-paragraph{
                    color:$theme_options_styles[clListMenuUsername];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// LOGOUT COLOR CONTEXTUAL MENU //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuLogout'])) {
            $styles_str .= "
                .contextual-menu-user .logout-list-item *{
                    color:$theme_options_styles[clListMenuLogout] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// DELETE OPTION COLOR CONTEXTUAL MENU ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuDeletion'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:has(.fa-xmark),
                .contextual-menu .list-group-item:has(.fa-trash),
                .contextual-menu .list-group-item:has(.fa-eraser),
                .contextual-menu .list-group-item:has(.fa-times),
                .contextual-menu .list-group-item:has(.fa-xmark) .fa::before,
                .contextual-menu .list-group-item:has(.fa-trash) .fa::before,
                .contextual-menu .list-group-item:has(.fa-eraser) .fa::before,
                .contextual-menu .list-group-item:has(.fa-times) .fa::before{
                    color: $theme_options_styles[clListMenuDeletion] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgRadios'])){
            $styles_str .= "
                input[type='radio']{
                    background-color: $theme_options_styles[BgRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderRadios'])){
            $styles_str .= "
                input[type='radio']{
                    border: solid 1px $theme_options_styles[BgBorderRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// TEXT COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClRadios'])){
            $styles_str .= "
                .radio label{
                    color: $theme_options_styles[ClRadios];
                }

                input[type='radio']{
                    color:  $theme_options_styles[ClRadios];
                }

                .radio:not(:has(input[type='radio']:checked)) .help-block{
                    color: $theme_options_styles[ClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND AND TEXT COLOR TO ACTIVE RADIO COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgClRadios'])){
            $styles_str .= "
                input[type='radio']:checked {
                    border: solid 6px $theme_options_styles[BgClRadios];
                }
                .input-StatusCourse:checked{
                    box-shadow: inset 0 0 0 0px #e8e8e8;
                    border: 0px solid #e8e8e8;
                    background-color: $theme_options_styles[BgClRadios];
                }
                .form-wrapper.form-edit label:has(input[type='radio']:checked){
                    color: $theme_options_styles[BgClRadios];
                }

                .radio label:has(input[type='radio']:checked),
                .radio:has(input[type='radio']:checked) .help-block{
                    color: $theme_options_styles[BgClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// ICON COLOR TO ACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconRadios'])){
            $styles_str .= "
                .radio:has(.input-StatusCourse:checked) .fa{
                    color: $theme_options_styles[ClIconRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO INACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveRadios'])){
            $styles_str .= "
                label:has(input[type='radio']:disabled){
                    color: $theme_options_styles[ClInactiveRadios];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    background-color: $theme_options_styles[BgCheckboxes];
                }
                #display_sessions_switcher{
                    background-color: $theme_options_styles[BgCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO CHECKBOX COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    border: 1px solid $theme_options_styles[BgBorderCheckboxes];
                }
                #display_sessions_switcher{
                    border: 1px solid $theme_options_styles[BgBorderCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO CHECKBOX COMPOENENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClCheckboxes'])){
            $styles_str .= "
                .label-container {
                    color: $theme_options_styles[ClCheckboxes];
                }
                #display_sessions_switcher{
                    color: $theme_options_styles[ClCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgActiveCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                    background-color: $theme_options_styles[BgActiveCheckboxes];
                }
                .label-container > input[type='checkbox']:active {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                }
                #display_sessions_switcher:checked{
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                    background-color: $theme_options_styles[BgActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClActiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:checked),
                .label-container:has(input[type='checkbox']:checked) .fa{
                    color: $theme_options_styles[ClActiveCheckboxes];
                }
                #display_sessions_switcher:checked{
                    color: $theme_options_styles[ClActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// ICON COLOR TO ACTIVE CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked + .checkmark::before {
                    color: $theme_options_styles[ClIconCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO INACTIVE CHECKBOX COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:disabled){
                    color: $theme_options_styles[ClInactiveCheckboxes];
                }
                #display_sessions_switcher:disabled{
                    color: $theme_options_styles[ClInactiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO INPUT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'],
                input[type='password'],
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    background-color: $theme_options_styles[BgInput];
                }

                textarea,
                textarea.form-control{
                    background-color: $theme_options_styles[BgInput];
                }

                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {
                    background-color: $theme_options_styles[BgInput];
                }

                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .add-on,
                .add-on1,
                .add-on2{
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .input-group-text.bg-input-default{
                    background-color: $theme_options_styles[BgInput];
                }

                .form-control:disabled,
                .form-control[readonly] {
                    background-color: $theme_options_styles[BgInput];
                }

                input:-webkit-autofill,
                input:-webkit-autofill:hover,
                input:-webkit-autofill:focus,
                textarea:-webkit-autofill,
                textarea:-webkit-autofill:hover,
                textarea:-webkit-autofill:focus {
                    background-color: $theme_options_styles[BgInput];
                    -webkit-box-shadow: 0 0 0 30px $theme_options_styles[BgInput] inset !important;
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BORDER COLOR TO INPUT COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'],
                input[type='password'],
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    border-color: $theme_options_styles[clBorderInput];
                }

                textarea,
                textarea.form-control{
                    border-color: $theme_options_styles[clBorderInput];
                }

                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {
                    border-color: $theme_options_styles[clBorderInput];
                }

                input:-webkit-autofill,
                input:-webkit-autofill:hover,
                input:-webkit-autofill:focus,
                textarea:-webkit-autofill,
                textarea:-webkit-autofill:hover,
                textarea:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderInput];
                }


                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .input-border-color {
                    border-color: $theme_options_styles[clBorderInput] ;
                }

                .form-control:disabled,
                .form-control[readonly] {
                    border-color: $theme_options_styles[clBorderInput] ;
                }


                .wallWrapper textarea:focus{
                    border-color: $theme_options_styles[clBorderInput] ;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO INPUT COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clInputText'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .form-control::placeholder,
                .login-input::placeholder,
                .login-input,
                input[type='text'],
                input[type='password'],
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    color: $theme_options_styles[clInputText];
                }

                textarea,
                textarea::placeholder,
                textarea.form-control{
                    color: $theme_options_styles[clInputText];
                }

                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {
                    color: $theme_options_styles[clInputText];
                }

                input:-webkit-autofill,
                input:-webkit-autofill:hover,
                input:-webkit-autofill:focus,
                textarea:-webkit-autofill,
                textarea:-webkit-autofill:hover,
                textarea:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clInputText];
                }



                .dataTables_wrapper input::placeholder{
                    color: $theme_options_styles[clInputText] !important;
                }

                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    color: $theme_options_styles[clInputText] !important;
                }

                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {
                    color: $theme_options_styles[clInputText] !important;
                }

                .input-group-text .fa-calendar{
                    color: $theme_options_styles[clInputText];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO SELECT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgSelect'])){
            $styles_str .= "

                select.form-select {
                    background-color: $theme_options_styles[BgSelect];
                }

                select.form-select:focus {
                    background-color: $theme_options_styles[BgSelect];
                }

                .dataTables_wrapper select {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }


                .select2-selection.select2-selection--multiple{
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-dropdown--below {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-selection--single{
                    background-color: $theme_options_styles[BgSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO SELECT COMBONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderSelect'])){
            $styles_str .= "

                select.form-select {
                    border-color: $theme_options_styles[clBorderSelect];
                }

                select.form-select:focus {
                    border-color: $theme_options_styles[clBorderSelect];
                }

                .dataTables_wrapper select {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{
                    border-color: $theme_options_styles[clBorderSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;
                }

                select:-webkit-autofill:hover,
                select:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderSelect];
                }

                .mce-floatpanel {
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;;
                }

                .select2-container--default .select2-selection--single{
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelect'])){
            $colorChevronDown = "$theme_options_styles[clOptionSelect]";
            $mySVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='$colorChevronDown' d='M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z'/%3E%3C/svg";
            $mysvg2 = 'url("data:image/svg+xml,%3C' . $mySVG .'%3E")';
            $styles_str .= "

                select.form-select {
                    color: $theme_options_styles[clOptionSelect];
                    background-image: $mysvg2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }

                select.form-select:focus {
                    color: $theme_options_styles[clOptionSelect];
                }

                select.form-select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect];
                }

                .dataTables_wrapper select {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .select2-selection--multiple:before {
                    border-top: 5px solid $theme_options_styles[clOptionSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                select:-webkit-autofill:hover,
                select:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clOptionSelect];
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .mce-menu-item{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .mce-menu-item .mce-text {
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .select2-container--default .select2-selection--single .select2-selection__rendered{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// HOVERED BACKGROUND COLOR TO OPTION OF SELECT COMBONENT /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                .mce-menu-item:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }



            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// HOVERED TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item:hover{
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item-normal.mce-active:hover .mce-text {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item:hover .mce-text {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// BACKGROUND COLOR TO ACTIVE OPTION OF SELECT COMBONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

                .mce-menu-item:hover,
                .mce-menu-item.mce-selected,
                .mce-menu-item:focus {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

                .dropdown-item.active,
                .dropdown-item:active {
                    background-color:  $theme_options_styles[bgOptionSelected] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR TO ACTIVE OPTION OF SELECT COMBONENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    color: $theme_options_styles[clOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    color: $theme_options_styles[clOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item:hover,
                .mce-menu-item.mce-selected,
                .mce-menu-item:focus {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active .mce-text {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item:hover .mce-text,
                .mce-menu-item.mce-selected .mce-text {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .dropdown-item.active,
                .dropdown-item:active {
                    color: $theme_options_styles[clOptionSelected] !important;
                }


            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO FORM COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgForms'])){
            $styles_str .= "
                .form-wrapper.form-edit {
                    background-color: $theme_options_styles[BgForms];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BORDER COLOR TO FORM COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderForms'])){
            $styles_str .= "
                .form-wrapper.form-edit {
                    border: solid 1px $theme_options_styles[BgBorderForms] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BOX SHADOW TO FORM COMPONENT //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['FormsBoxShadow'])){
            $styles_str .= "
                .form-wrapper.form-edit {
                    box-shadow: 0px 0 30px $theme_options_styles[FormsBoxShadow] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// ADD PADDING TO THE FORM COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AddPaddingFormWrapper'])){
            $styles_str .= "
                .form-wrapper.form-edit{
                    padding: 16px 24px 16px 24px !important;
                }
            ";
        }

        if(isset($theme_options_styles['widthOfForm']) && isset($theme_options_styles['sliderWidthImgForm'])){
            $MainContentWidth = $theme_options_styles['fluidContainerWidth'] ?? 1140;
            // The number 300 is the max width of left menu in a course.
            $MainContentWidth = ($MainContentWidth - 300);
            // Now we calculate the % of the width of a form.
            $t_width = 100 - $theme_options_styles['sliderWidthImgForm'];
            $MainContentWidth = $MainContentWidth*$t_width/100;
            $FormWidth = $MainContentWidth ."px";
            $styles_str .= "
                @media(min-width:992px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        width: $FormWidth;
                        float:right;
                        padding-bottom: 0px;
                    }
                }
                .main-section:not(:has(.course-wrapper)) .form-image-modules{
                    width:100%;
                    float:right;
                    padding-bottom: 0px;
                }
            ";
        }

        if(isset($theme_options_styles['strechedImgOfForm'])){
            $imgRegistrationForm = get_registration_form_image();
            $imgFaq = get_FAQ_image();
            $imgForm = get_form_image();
            $head_content .= "
                <script>
                    $(function() {
                        $('.form-image-modules').attr('src','');
                        $('.form-image-modules').attr('alt','');
                        $('.form-image-registration').attr('src','$imgRegistrationForm');
                        $('.form-image-registration').attr('alt','$langRegistration');
                        $('.form-image-faq').attr('src','$imgFaq');
                        $('.form-image-faq').attr('alt','$langFaq');
                    });
                </script>
            ";
            $typeImage = "";
            if(isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'fixed'){
                $typeImage = "background-repeat: no-repeat; background-attachment: fixed; background-size: 100% 100%;";
            }elseif(isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'repeated'){
                $typeImage = "background-repeat: repeat;";
            }elseif(isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'streched'){
                $typeImage = "background-repeat: no-repeat; background-size: cover;";
            }
            $styles_str .= "
                @media(min-width:992px){
                    .form-image-modules{
                        display: block;
                        float:right;
                        flex-shrink: 0;
                        padding-bottom: 0px;
                        min-height: 100%;
                        background-image: url('$imgForm');
                        $typeImage
                    }
                    .form-image-registration,
                    .form-image-faq{
                        min-height: auto;
                        background-image: none;
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// LABEL COLOR IN FORM COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLabelForms'])){
            $styles_str .= "

                .form-wrapper.form-edit .control-label-notes,
                .form-group .control-label-notes{
                    color:$theme_options_styles[clLabelForms];
                }

                form small,
                form strong,
                form p,
                form span,
                form em,
                form h1,
                form h2,
                form h3,
                form h4,
                form h5,
                form h6,
                form .li-indented,
                form li,
                form .Neutral-900-cl,
                form .form-label,
                form .default-value,
                form label,
                form th,
                form td,
                form .panel-body,
                form .card-body,
                form div,
                form .visibleFile,
                form .list-group-item,
                form .help-block,
                form .control-label-notes,
                form .title-default,
                form .modal-title-default,
                form .text-heading-h2,
                form .text-heading-h3,
                form .text-heading-h4,
                form .text-heading-h5,
                form .text-heading-h6,
                form .action-bar-title,
                form .list-group-item.list-group-item-action,
                form .list-group-item.element{
                    color:$theme_options_styles[clLabelForms];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO REQUIRED FORM FIELD //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clRequiredFieldForm'])){
            $styles_str .= "
                .asterisk,
                .help-block.Accent-200-cl{
                    color:$theme_options_styles[clRequiredFieldForm] !important;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO MODAL COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgModal'])){
            $styles_str .= "
                .bootbox.show .bootbox-close-button{
                    background-color:$theme_options_styles[BgModal];
                }
                .modal.show .close{
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses{
                    background:$theme_options_styles[BgModal];
                }
                .course-content::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses .close{
                    background-color: $theme_options_styles[BgModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderModal'])){
            $styles_str .= "
                .modal-content {
                    border: 1px solid $theme_options_styles[clBorderModal];
                }
                .modal-content-opencourses{
                    border: solid 1px $theme_options_styles[clBorderModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clTextModal'])){
            $styles_str .= "
                .bootbox.show .modal-header .modal-title,
                .modal.show .modal-header .modal-title {
                    color:  $theme_options_styles[clTextModal];
                }
                .modal-content h1,
                .modal-content h2,
                .modal-content h3,
                .modal-content h4,
                .modal-content h5,
                .modal-content h6,
                .modal-content div,
                .modal-content small,
                .modal-content span,
                .modal-content p,
                .modal-content b,
                .modal-content strong,
                .modal-content li,
                .modal-content label,
                .modal-content{
                    color:  $theme_options_styles[clTextModal];
                }

                .bootbox.show .bootbox-body,
                .modal.show .modal-body{
                    color: $theme_options_styles[clTextModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// ICON COLOR TO MODAL DELETION COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clDeleteIconModal'])){
            $styles_str .= "
                .icon-modal-default .fa-trash-can.Accent-200-cl::before{
                    color: $theme_options_styles[clDeleteIconModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// ICON COLOR TO CLOSED MODAL COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clXmarkModal'])){
            $SVGmodalClose = "svg xmlns='http://www.w3.org/2000/svg' height='20' width='15' viewBox='0 0 384 512' fill='%23000'%3e%3cpath fill='$theme_options_styles[clXmarkModal]' d='M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z'/%3e%3c/svg";
            $SVGmodalClose2 = 'transparent url("data:image/svg+xml,%3C' . $SVGmodalClose .'%3E") center / 1em auto no-repeat';

            $styles_str .= "
                .bootbox.show .bootbox-close-button,
                .modal.show .close,
                .modal-display .close{
                    background: $SVGmodalClose2;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO AGENDA COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    background-color: $theme_options_styles[bgAgenda];
                }
                .myPersonalCalendar {
                    background-color: $theme_options_styles[bgAgenda];
                }



                .myPersonalCalendar .cal-row-fluid.cal-row-head {
                    background: $theme_options_styles[bgAgenda];
                }
                #cal-day-box .cal-day-hour:nth-child(odd) {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }


                .datepicker-centuries .table-condensed,
                .datepicker-centuries .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-decades .table-condensed,
                .datepicker-decades .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-years .table-condensed,
                .datepicker-years .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-months .table-condensed,
                .datepicker-months .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-days .table-condensed,
                .datepicker-days .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }





                .datetimepicker-years .table-condensed,
                .datetimepicker-years .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-months .table-condensed,
                .datetimepicker-months .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-days .table-condensed,
                .datetimepicker-days .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-hours .table-condensed,
                .datetimepicker-hours .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-minutes .table-condensed,
                .datetimepicker-minutes .table-condensed .dow{
                    background-color: $theme_options_styles[bgAgenda];
                }



                .cal-day-today {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }

                .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top {
                    background-color: $theme_options_styles[bgAgenda];
                }

                .datetimepicker.datetimepicker-dropdown-bottom-right.dropdown-menu {
                    background-color: $theme_options_styles[bgAgenda];
                }

                .datetimepicker.dropdown-menu,
                .datepicker.dropdown-menu{
                    background: $theme_options_styles[bgAgenda] !important;
                }

                #cal-week-box{
                    background-color: $theme_options_styles[bgAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO OF AGENDA'S HEADER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading,
                #cal-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }
                #calendar-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }




                .datepicker-centuries .table-condensed thead .prev,
                .datepicker-centuries .table-condensed thead .next,
                .datepicker-centuries .table-condensed thead .datepicker-switch,
                .datepicker-centuries .table-condensed thead .prev:hover,
                .datepicker-centuries .table-condensed thead .next:hover,
                .datepicker-centuries .table-condensed thead .datepicker-switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-decades .table-condensed thead .prev,
                .datepicker-decades .table-condensed thead .next,
                .datepicker-decades .table-condensed thead .datepicker-switch,
                .datepicker-decades .table-condensed thead .prev:hover,
                .datepicker-decades .table-condensed thead .next:hover,
                .datepicker-decades .table-condensed thead .datepicker-switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-years .table-condensed thead .prev,
                .datepicker-years .table-condensed thead .next,
                .datepicker-years .table-condensed thead .datepicker-switch,
                .datepicker-years .table-condensed thead .prev:hover,
                .datepicker-years .table-condensed thead .next:hover,
                .datepicker-years .table-condensed thead .datepicker-switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-months .table-condensed thead .prev,
                .datepicker-months .table-condensed thead .next,
                .datepicker-months .table-condensed thead .datepicker-switch,
                .datepicker-months .table-condensed thead .prev:hover,
                .datepicker-months .table-condensed thead .next:hover,
                .datepicker-months .table-condensed thead .datepicker-switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-days .table-condensed thead .prev,
                .datepicker-days .table-condensed thead .next,
                .datepicker-days .table-condensed thead .datepicker-switch,
                .datepicker-days .table-condensed thead .prev:hover,
                .datepicker-days .table-condensed thead .next:hover,
                .datepicker-days .table-condensed thead .datepicker-switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }




                .datetimepicker-years .table-condensed thead .prev,
                .datetimepicker-years .table-condensed thead .next,
                .datetimepicker-years .table-condensed thead .switch,
                .datetimepicker-years .table-condensed thead .prev:hover,
                .datetimepicker-years .table-condensed thead .next:hover,
                .datetimepicker-years .table-condensed thead .switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev,
                .datetimepicker-months .table-condensed thead .next,
                .datetimepicker-months .table-condensed thead .switch,
                .datetimepicker-months .table-condensed thead .prev:hover,
                .datetimepicker-months .table-condensed thead .next:hover,
                .datetimepicker-months .table-condensed thead .switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev,
                .datetimepicker-days .table-condensed thead .next,
                .datetimepicker-days .table-condensed thead .switch,
                .datetimepicker-days .table-condensed thead .prev:hover,
                .datetimepicker-days .table-condensed thead .next:hover,
                .datetimepicker-days .table-condensed thead .switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev,
                .datetimepicker-hours .table-condensed thead .next,
                .datetimepicker-hours .table-condensed thead .switch,
                .datetimepicker-hours .table-condensed thead .prev:hover,
                .datetimepicker-hours .table-condensed thead .next:hover,
                .datetimepicker-hours .table-condensed thead .switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev,
                .datetimepicker-minutes .table-condensed thead .next,
                .datetimepicker-minutes .table-condensed thead .switch,
                .datetimepicker-minutes .table-condensed thead .prev:hover,
                .datetimepicker-minutes .table-condensed thead .next:hover,
                .datetimepicker-minutes .table-condensed thead .switch:hover{
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }

                .datepicker table tr td span.focused {
                    background: $theme_options_styles[BgColorHeaderAgenda] !important;
                }



            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF AGENDA'S HEADER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading, #cal-header {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                #current-month,
                #cal-header .fa-chevron-left,
                #cal-header .fa-chevron-right {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                .text-agenda-title,
                .text-agenda-title:hover{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }







                .datepicker-centuries .table-condensed thead tr th.next::after,
                .datepicker-decades .table-condensed thead tr th.next::after,
                .datepicker-years .table-condensed thead tr th.next::after,
                .datepicker-months .table-condensed thead tr th.next::after,
                .datepicker-days .table-condensed thead tr th.next::after{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.datepicker-switch,
                .datepicker-decades .table-condensed thead tr th.datepicker-switch,
                .datepicker-years .table-condensed thead tr th.datepicker-switch,
                .datepicker-months .table-condensed thead tr th.datepicker-switch,
                .datepicker-days .table-condensed thead tr th.datepicker-switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.prev::before,
                .datepicker-decades .table-condensed thead tr th.prev::before,
                .datepicker-years .table-condensed thead tr th.prev::before,
                .datepicker-months .table-condensed thead tr th.prev::before,
                .datepicker-days .table-condensed thead tr th.prev::before{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }




                .datetimepicker-years .table-condensed thead .prev::before,
                .datetimepicker-years .table-condensed thead .next::after,
                .datetimepicker-years .table-condensed thead .switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev::before,
                .datetimepicker-months .table-condensed thead .next::after,
                .datetimepicker-months .table-condensed thead .switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev::before,
                .datetimepicker-days .table-condensed thead .next::after,
                .datetimepicker-days .table-condensed thead .switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev::before,
                .datetimepicker-hours .table-condensed thead .next::after,
                .datetimepicker-hours .table-condensed thead .switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev::before,
                .datetimepicker-minutes .table-condensed thead .next::after,
                .datetimepicker-minutes .table-condensed thead .switch{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR OF AGENDA'S BODY ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorBodyAgenda'])){
            $styles_str .= "

                .cal-row-fluid.cal-row-head .cal-cell1,
                .cal-month-day .pull-right,
                .cal-day-weekend span[data-cal-date] {
                    color: $theme_options_styles[clColorBodyAgenda];
                }

                .myPersonalCalendar .cal-row-fluid.cal-row-head .cal-cell1,
                .myPersonalCalendar .cal-month-day .pull-right,
                .myPersonalCalendar .cal-day-hour div,
                #cal-day-box div,
                .cal-year-box div,
                .cal-month-box div,
                .cal-week-box div {
                    color: $theme_options_styles[clColorBodyAgenda];
                }




                .datepicker-centuries .table-condensed thead tr th.dow,
                .datepicker-decades .table-condensed thead tr th.dow,
                .datepicker-years .table-condensed thead tr th.dow,
                .datepicker-months .table-condensed thead tr th.dow,
                .datepicker-days .table-condensed thead tr th.dow{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td,
                .datepicker-decades .table-condensed tbody tr td,
                .datepicker-years .table-condensed tbody tr td,
                .datepicker-months .table-condensed tbody tr td,
                .datepicker-days .table-condensed tbody tr td{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }





                .datetimepicker-years .table-condensed thead tr th.dow,
                .datetimepicker-months .table-condensed thead tr th.dow,
                .datetimepicker-days .table-condensed thead tr th.dow,
                .datetimepicker-hours .table-condensed thead tr th.dow,
                .datetimepicker-minutes .table-condensed thead tr th.dow{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datetimepicker-years .table-condensed tbody tr td,
                .datetimepicker-months .table-condensed tbody tr td,
                .datetimepicker-days .table-condensed tbody tr td,
                .datetimepicker-hours .table-condensed tbody tr td,
                .datetimepicker-minutes .table-condensed tbody tr td{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }

                .datetimepicker table tr td.old,
                .datetimepicker table tr td.new,
                .datepicker table tr td.old,
                .datepicker table tr td.new{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }


                .cal-day-today span[data-cal-date],
                .cal-day-today span[data-cal-date]:hover,
                .cal-day-today span[data-cal-date]:focus{
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO AGENDA COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderColorAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    border-bottom: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-left: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-right: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                .panel-body-calendar {
                    margin-top: -0.7px;
                }
                .panel-admin-calendar .panel-heading{
                    border-top: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-left: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-right: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                #calendar_wrapper{
                    border: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }


                .fc-unthemed .fc-content,
                .fc-unthemed .fc-divider,
                .fc-unthemed .fc-list-heading td,
                .fc-unthemed .fc-list-view,
                .fc-unthemed .fc-popover,
                .fc-unthemed .fc-row,
                .fc-unthemed tbody,
                .fc-unthemed td,
                .fc-unthemed th,
                .fc-unthemed thead {
                    border-color: $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table,
                .calendarAddDaysCl table,
                .bookingCalendarByUser table,
                .myCalendarEvents table {
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup .fc-widget-header,
                .calendarAddDaysCl .fc-widget-header,
                .bookingCalendarByUser .fc-widget-header,
                .myCalendarEvents .fc-widget-header,
                .calendarViewDatesTutorGroup table .fc-head table thead tr th,
                .calendarAddDaysCl table .fc-head table thead tr th,
                .bookingCalendarByUser table .fc-head table thead tr th,
                .myCalendarEvents table .fc-head table thead tr th{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }
                .calendarViewDatesTutorGroup table .fc-head,
                .calendarAddDaysCl table .fc-head,
                .bookingCalendarByUser table .fc-head,
                .myCalendarEvents table .fc-head{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table .fc-body .fc-widget-content,
                .calendarAddDaysCl table .fc-body .fc-widget-content,
                .bookingCalendarByUser table .fc-body .fc-widget-content,
                .myCalendarEvents table .fc-body .fc-widget-content{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table .fc-body tbody tr td,
                .calendarAddDaysCl table .fc-body tbody tr td,
                .bookingCalendarByUser table .fc-body tbody tr td,
                .myCalendarEvents table .fc-body tbody tr td{
                    border-color: $theme_options_styles[BgBorderColorAgenda] ;
                }

                .calendarViewDatesTutorGroup table .fc-body tbody tr,
                .calendarAddDaysCl table .fc-body tbody tr,
                .bookingCalendarByUser table .fc-body tbody tr,
                .myCalendarEvents table .fc-body tbody tr{
                    border-color: $theme_options_styles[BgBorderColorAgenda] ;
                }

                .calendarViewDatesTutorGroup .fc-list-table  tbody tr {
                    border-bottom: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }

                #cal-week-box{
                    border: 1px solid $theme_options_styles[BgBorderColorAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BORDER COLOR SLOTS TO AGENDA EVENTS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderColorAgendaEvent'])){
            $styles_str .= "
                .calendarAddDaysCl .fc-body table tbody tr td.fc-axis,
                .calendarAddDaysCl .fc-body table tbody tr td{
                    border:solid 1px $theme_options_styles[BgBorderColorAgendaEvent] !important;
                }

                .myCalendarEvents .fc-body table tbody tr td.fc-axis,
                .myCalendarEvents .fc-body table tbody tr td{
                    border:solid 1px $theme_options_styles[BgBorderColorAgendaEvent] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO AGENDA COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorHoveredBodyAgenda'])){
            $styles_str .= "
                .datetimepicker-years .table-condensed thead tr th:hover,
                .datetimepicker-years .table-condensed tbody tr td .year:hover,
                .datetimepicker-months .table-condensed thead tr th:hover,
                .datetimepicker-months .table-condensed tbody tr td .month:hover,
                .datetimepicker-days .table-condensed thead tr th:hover,
                .datetimepicker-days .table-condensed tbody tr td:hover,
                .datetimepicker-hours .table-condensed thead tr th:hover,
                .datetimepicker-hours .table-condensed tbody tr td .hour:hover,
                .datetimepicker-minutes .table-condensed thead tr th:hover,
                .datetimepicker-minutes .table-condensed tbody tr td .minute:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th:hover,
                .datepicker-decades .table-condensed thead tr th:hover,
                .datepicker-years .table-condensed thead tr th:hover,
                .datepicker-months .table-condensed thead tr th:hover,
                .datepicker-days .table-condensed thead tr th:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td .century:hover,
                .datepicker-decades .table-condensed tbody tr td .decade:hover,
                .datepicker-years .table-condensed tbody tr td .year:hover,
                .datepicker-months .table-condensed tbody tr td .month:hover,
                .datepicker-days .table-condensed tbody tr td:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-head:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-head .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-month-box .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-month-box .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-year-box .row-fluid:hover,
                .myPersonalCalendar .cal-week-box .row-fluid:hover,
                #cal-day-box .row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-year-box .row-fluid div:hover,
                .myPersonalCalendar .cal-week-box .row-fluid div:hover,
                #cal-day-box .row-fluid div:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT HOVERED COLOR TO AGENDA COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorHoveredBodyAgenda'])){
            $styles_str .= "
                .datetimepicker-years .table-condensed thead tr th:hover,
                .datetimepicker-years .table-condensed tbody tr td .year:hover,
                .datetimepicker-months .table-condensed thead tr th:hover,
                .datetimepicker-months .table-condensed tbody tr td .month:hover,
                .datetimepicker-days .table-condensed thead tr th:hover,
                .datetimepicker-days .table-condensed tbody tr td:hover,
                .datetimepicker-hours .table-condensed thead tr th:hover,
                .datetimepicker-hours .table-condensed tbody tr td .hour:hover,
                .datetimepicker-minutes .table-condensed thead tr th:hover,
                .datetimepicker-minutes .table-condensed tbody tr td .minute:hover{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th:hover,
                .datepicker-decades .table-condensed thead tr th:hover,
                .datepicker-years .table-condensed thead tr th:hover,
                .datepicker-months .table-condensed thead tr th:hover,
                .datepicker-days .table-condensed thead tr th:hover{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td .century:hover,
                .datepicker-decades .table-condensed tbody tr td .decade:hover,
                .datepicker-years .table-condensed tbody tr td .year:hover,
                .datepicker-months .table-condensed tbody tr td .month:hover,
                .datepicker-days .table-condensed tbody tr td:hover{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-head .cal-cell1:hover,
                .panel-body-calendar .cal-month-box .cal-cell1:hover div{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }


                .myPersonalCalendar .cal-cell1:hover div,
                .myPersonalCalendar .cal-cell:hover span{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO ACTIVE DATETIME SLOT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active,
                .datetimepicker table tr td span.active:hover:active,
                .datetimepicker table tr td span.active.disabled:active,
                .datetimepicker table tr td span.active.disabled:hover:active,
                .datetimepicker table tr td span.active.active,
                .datetimepicker table tr td span.active:hover.active,
                .datetimepicker table tr td span.active.disabled.active,
                .datetimepicker table tr td span.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active,
                .datepicker table tr td.active.highlighted:active,
                .datepicker table tr td.active.active,
                .datepicker table tr td.active.highlighted.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active,
                .datetimepicker table tr td.active:hover:active,
                .datetimepicker table tr td.active.disabled:active,
                .datetimepicker table tr td.active.disabled:hover:active,
                .datetimepicker table tr td.active.active,
                .datetimepicker table tr td.active:hover.active,
                .datetimepicker table tr td.active.disabled.active,
                .datetimepicker table tr td.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active,
                .datepicker table tr td span.active:hover:active,
                .datepicker table tr td span.active.disabled:active,
                .datepicker table tr td span.active.disabled:hover:active,
                .datepicker table tr td span.active.active,
                .datepicker table tr td span.active:hover.active,
                .datepicker table tr td span.active.disabled.active,
                .datepicker table tr td span.active.disabled:hover.active {
                    color: #fff;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                    border-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }


                .cal-day-holiday span[data-cal-date]{
                    background-color:$theme_options_styles[bgColorActiveDateTime] !important;
                }

                .cal-day-today span[data-cal-date]{
                    background-color: transparent !important;
                    border: solid 1px $theme_options_styles[bgColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////// BACKGROUND COLOR TO THE DISABLED SLOTS OF SMALL CALENDAR ////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        if(!empty($theme_options_styles['bgColorDeactiveDateTime'])){
            $styles_str .= "
                .cal-day-outmonth,
                .datetimepicker table tr td.old,
                .datetimepicker table tr td.new,
                .datepicker table tr td.old,
                .datepicker table tr td.new{
                    background-color: $theme_options_styles[bgColorDeactiveDateTime] !important;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO ACTIVE DATETIME SLOT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active,
                .datetimepicker table tr td span.active:hover:active,
                .datetimepicker table tr td span.active.disabled:active,
                .datetimepicker table tr td span.active.disabled:hover:active,
                .datetimepicker table tr td span.active.active,
                .datetimepicker table tr td span.active:hover.active,
                .datetimepicker table tr td span.active.disabled.active,
                .datetimepicker table tr td span.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active,
                .datepicker table tr td.active.highlighted:active,
                .datepicker table tr td.active.active,
                .datepicker table tr td.active.highlighted.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active,
                .datetimepicker table tr td.active:hover:active,
                .datetimepicker table tr td.active.disabled:active,
                .datetimepicker table tr td.active.disabled:hover:active,
                .datetimepicker table tr td.active.active,
                .datetimepicker table tr td.active:hover.active,
                .datetimepicker table tr td.active.disabled.active,
                .datetimepicker table tr td.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active,
                .datepicker table tr td span.active:hover:active,
                .datepicker table tr td span.active.disabled:active,
                .datepicker table tr td span.active.disabled:hover:active,
                .datepicker table tr td span.active.active,
                .datepicker table tr td span.active:hover.active,
                .datepicker table tr td span.active.disabled.active,
                .datepicker table tr td span.active.disabled:hover.active {
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td span.focused:active,
                .datepicker table tr td span.focused:hover:active,
                .datepicker table tr td span.focused.disabled:active,
                .datepicker table tr td span.focused.disabled:hover:active,
                .datepicker table tr td span.focused {
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .cal-day-holiday span[data-cal-date],
                .cal-day-holiday span[data-cal-date]:hover,
                .cal-day-holiday span[data-cal-date]:focus{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// BACKGROUND COLOR OF PANEL EVENTS IN AGENDA COMPONENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgPanelEvents'])) {
            $styles_str .= "
                #cal-slide-content {
                    background: $theme_options_styles[bgPanelEvents];
                }

                #cal-slide-content:hover {
                    background-color: $theme_options_styles[bgPanelEvents] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR OF COURSE LEFT MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftNavBgColor'])) {

            $aboutLeftForm = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $aboutLeftForm[3] = '0.1';
            $aboutLeftForm = 'rgba(' . implode(',', $aboutLeftForm) . ')';


            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';

            $styles_str .= "

                .ContentLeftNav, #collapseTools{
                    background: $theme_options_styles[leftNavBgColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF COURSE LEFT MENU IN SMALL SCREEN /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftNavBgColorSmallScreen'])) {

            $styles_str .= "

                @media(max-width:991px){
                    .ContentLeftNav, #collapseTools{
                        background: $theme_options_styles[leftNavBgColorSmallScreen];
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO TABLE COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTables'])){
            $styles_str .= "

                #portfolio_lessons tbody tr{
                    background-color: $theme_options_styles[BgTables];
                }
                #portfolio_collaborations tbody tr{
                    background-color: $theme_options_styles[BgTables];
                }

                .table-default tbody tr td,
                .announcements_table tbody tr td,
                table.dataTable tbody tr td,
                .table-default tbody tr th,
                .announcements_table tbody tr th,
                table.dataTable tbody tr th {
                    background-color: $theme_options_styles[BgTables];
                }

                thead,
                .title1 {
                    background-color: $theme_options_styles[BgTables];
                }

                .row-course:hover td:first-child, .row-course:hover td:last-child{
                    background-color: $theme_options_styles[BgTables];
                }

                table.dataTable.display tbody tr.odd,
                table.dataTable.display tbody tr.odd > .sorting_1,
                table.dataTable.order-column.stripe tbody tr.odd > .sorting_1,
                table.dataTable.display tbody tr.even > .sorting_1,
                table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                table.dataTable tbody tr {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                .table-exercise-secondary {
                    background-color: $theme_options_styles[BgTables] ;
                }
                .table-exercise td, .table-exercise th {
                    background-color: transparent;
                }

                .user-details-exec{
                    background-color: $theme_options_styles[BgTables];
                }

                .border-bottom-table-head{
                    background-color: $theme_options_styles[BgTables] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM COLOR TO TABLE'S ROWS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomRowTables'])){
            $styles_str .= "
                .table-default tbody tr{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tbody td{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable.no-footer {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                .dataTables_wrapper.no-footer .dataTables_scrollBody {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tfoot th, table.dataTable tfoot td {
                    border-top: 1px solid  $theme_options_styles[BgBorderBottomRowTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// BOX SHADOW TO TABLE'S ROWS /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BoxShadowRowTables'])){
            $styles_str .= "
                .table-default tbody tr{
                   box-shadow: 0px 0 30px $theme_options_styles[BoxShadowRowTables] !important;
                }
                table.dataTable.no-footer {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowRowTables] !important;
                }
                .dataTables_wrapper.no-footer .dataTables_scrollBody {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowRowTables] !important;
                }
                table.dataTable tfoot th, table.dataTable tfoot td {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowRowTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TO TABLE'S THEAD /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomHeadTables'])){
            $styles_str .= "
                thead,
                tbody .list-header,
                tbody tr.header-pollAnswers,
                .border-bottom-table-head,
                thead tr.list-header td,
                tbody tr.list-header td,
                tbody tr.list-header th {
                    border-bottom: solid 2px $theme_options_styles[BgBorderBottomHeadTables] !important;
                }
                table.dataTable thead th,
                table.dataTable thead td {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomHeadTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{
                    background: $theme_options_styles[BgMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO MENU-POPOVER COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{
                    border: solid 1px $theme_options_styles[BgBorderMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER OPTIONS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{
                    background-color: $theme_options_styles[BgMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO MENU-POPOVER OPTIONS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{
                    color: $theme_options_styles[clMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO MENU-POPOVER OPTIONS /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{
                    background-color: $theme_options_styles[BgHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT HOVERED COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{
                    color: $theme_options_styles[clHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO MENU-POPOVER DELETE OPTION //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clDeleteMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:has(.fa-xmark),
                .menu-popover .list-group-item:has(.fa-trash),
                .menu-popover .list-group-item:has(.fa-eraser),
                .menu-popover .list-group-item:has(.fa-times),
                .menu-popover .list-group-item:has(.fa-xmark) .fa::before,
                .menu-popover .list-group-item:has(.fa-trash) .fa::before,
                .menu-popover .list-group-item:has(.fa-eraser) .fa::before,
                .menu-popover .list-group-item:has(.fa-times) .fa::before,
                .menu-popover .list-group-item.warning-delete{
                    color: $theme_options_styles[clDeleteMenuPopoverOption] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO THE TEXT EDITOR /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTextEditor'])){
            $styles_str .= "
                .mce-container,
                .mce-widget,
                .mce-widget *,
                .mce-reset {
                    background: $theme_options_styles[BgTextEditor] !important;
                }
                .mce-window .mce-container-body {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab.mce-active {
                    background: $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-textbox {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  i.mce-i-checkbox {
                    background-image: -webkit-linear-gradient(top,#fff,$theme_options_styles[BgTextEditor]) !important;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR TO THE TEXT EDITOR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderTextEditor'])){
            $styles_str .= "
                .mce-panel {
                    border: solid 1px $theme_options_styles[BgBorderTextEditor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// TEXT COLOR TO THE TEXT EDITOR ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClTextEditor'])){
            $SVGtools = "svg xmlns='http://www.w3.org/2000/svg' height='20' width='15' viewBox='0 0 384 512' fill='%23000'%3e%3cpath fill='$theme_options_styles[ClTextEditor]' d='M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z'/%3e%3c/svg";
            $SVGtools2 = 'transparent url("data:image/svg+xml,%3C' . $SVGtools .'%3E") center / 1em auto no-repeat';
            $styles_str .= "

                .mce-toolbar .mce-btn i {
                    color: $theme_options_styles[ClTextEditor] !important;
                }

                .mce-menubtn span {
                    color: $theme_options_styles[ClTextEditor] !important;
                }
                .mce-btn i {
                    text-shadow: 0px 0px $theme_options_styles[ClTextEditor] !important;
                }

                .mce-container, .mce-container *, .mce-widget, .mce-widget *, .mce-reset {
                    color: $theme_options_styles[ClTextEditor] !important;
                }

                .mce-caret {
                    border-top: 4px solid $theme_options_styles[ClTextEditor] !important;
                }

                .mce-toolbar .mce-btn i.mce-i-none{
                    background: $SVGtools2 !important;
                }

            ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND CONTAINER OF SCROLLBAR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgScrollBar'])){
            $styles_str .= "
              .container-items::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .contextual-menu::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .course-content::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .panel-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .table-responsive::-webkit-scrollbar-track,
              .dataTables_wrapper::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .chat-iframe::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .bodyChat::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              #blog_tree::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO THE SCROLLBAR COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }

              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .contextual-menu::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .panel-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .table-responsive::-webkit-scrollbar-thumb,
              .dataTables_wrapper::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .chat-iframe::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb {
                 background-color: $theme_options_styles[BgColorScrollBar];
              }

              #blog_tree::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND HOVERED COLOR TO THE SCROLLBAR COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .contextual-menu::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .panel-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .table-responsive::-webkit-scrollbar-thumb:hover,
              .dataTables_wrapper::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .chat-iframe::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }


              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover{
                    background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              #blog_tree::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// PROGRESSBAR COMPONENT ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BackProgressBar']) && !empty($theme_options_styles['BgProgressBar']) &&
                            !empty($theme_options_styles['BgColorProgressBarAndText'])){

            $styles_str .= "
                .progress-circle-bar{
                    --size: 9rem;
                    --fg: $theme_options_styles[BgColorProgressBarAndText];
                    --bg: $theme_options_styles[BgProgressBar];
                    --pgPercentage: var(--value);
                    animation: growProgressBar 3s 1 forwards;
                    width: var(--size);
                    height: var(--size);
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    background:
                        radial-gradient(closest-side, $theme_options_styles[BackProgressBar] 80%, transparent 0 99.9%, $theme_options_styles[BackProgressBar] 0),
                        conic-gradient(var(--fg) calc(var(--pgPercentage) * 1%), var(--bg) 0)
                        ;
                    font-weight: 700; font-style: normal;
                    font-size: calc(var(--size) / 5);
                    color: var(--fg);

                }

                .progress-bar {
                    background-color: $theme_options_styles[BgColorProgressBarAndText];
                }

                .progress-line{
                    background-color: $theme_options_styles[BgProgressBar];
                }
                .progress-line-bar{
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    overflow: hidden;
                    color: $theme_options_styles[BgProgressBar];
                    text-align: center;
                    white-space: nowrap;
                    background-color: $theme_options_styles[BgColorProgressBarAndText];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE TOOLTIP COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    background-color: $theme_options_styles[bgColorTooltip];

                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE TOOLTIP COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    color: $theme_options_styles[TextColorTooltip];

                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR AND TEXT COLOR TO ALERT COMPONENT /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgAlertInfo'])){
            $styles_str .= "
                .alert-info {
                    background-color:$theme_options_styles[bgAlertInfo];
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertInfo'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertInfo]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-info,
                .alert-info h1,
                .alert-info h2,
                .alert-info h3,
                .alert-info h4,
                .alert-info h5,
                .alert-info h6,
                .alert-info div,
                .alert-info small,
                .alert-info span,
                .alert-info p,
                .alert-info b,
                .alert-info strong,
                .alert-info li,
                .alert-info label{
                    color: $theme_options_styles[clAlertInfo] !important;
                }

                .alert-info .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertWarning'])){
            $styles_str .= "
                .alert-warning {
                    background-color:$theme_options_styles[bgAlertWarning];
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertWarning'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertWarning]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-warning,
                .alert-warning h1,
                .alert-warning h2,
                .alert-warning h3,
                .alert-warning h4,
                .alert-warning h5,
                .alert-warning h6,
                .alert-warning div,
                .alert-warning small,
                .alert-warning span,
                .alert-warning p,
                .alert-warning b,
                .alert-warning strong,
                .alert-warning li,
                .alert-warning label{
                    color: $theme_options_styles[clAlertWarning] !important;
                }

                .alert-warning .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertSuccess'])){
            $styles_str .= "
                .alert-success {
                    background-color:$theme_options_styles[bgAlertSuccess];
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertSuccess'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertSuccess]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-success,
                .alert-success h1,
                .alert-success h2,
                .alert-success h3,
                .alert-success h4,
                .alert-success h5,
                .alert-success h6,
                .alert-success div,
                .alert-success small,
                .alert-success span,
                .alert-success p,
                .alert-success b,
                .alert-success strong,
                .alert-success li,
                .alert-success label{
                    color: $theme_options_styles[clAlertSuccess] !important;
                }

                .alert-success .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertDanger'])){
            $styles_str .= "
                .alert-danger {
                    background-color:$theme_options_styles[bgAlertDanger];
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertDanger'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertDanger]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-danger,
                .alert-danger h1,
                .alert-danger h2,
                .alert-danger h3,
                .alert-danger h4,
                .alert-danger h5,
                .alert-danger h6,
                .alert-danger div,
                .alert-danger small,
                .alert-danger span,
                .alert-danger p,
                .alert-danger b,
                .alert-danger strong,
                .alert-danger li,
                .alert-danger label{
                    color: $theme_options_styles[clAlertDanger] !important;
                }

                .alert-danger .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// LINKS COLOR OF PLATFORM ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColor'])){
            $styles_str .= "

                a, .toolAdminText{
                    color: $theme_options_styles[linkColor];
                }


                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button.fc-state-active,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaDay-button.fc-state-active{
                    background:$theme_options_styles[linkColor] !important;
                }

                .Primary-600-cl,
                .Primary-500-cl {
                    color: $theme_options_styles[linkColor];
                }

                .Primary-500-bg {
                    background-color:  $theme_options_styles[linkColor];
                }

                .menu-item.active,
                .menu-item.active2{
                    color:  $theme_options_styles[linkColor];
                }

                .portfolio-tools a{
                    color: $theme_options_styles[linkColor];
                }

                .nav-link-adminTools{
                    color: $theme_options_styles[linkColor];
                }

                #cal-slide-content a.event-item{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_paginate.paging_simple_numbers span .paginate_button,
                .dataTables_paginate.paging_full_numbers span .paginate_button{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.current,
                .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:active {
                    background: transparent !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover,
                .dataTables_wrapper .dataTables_paginate .paginate_button.last:hover{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.next.disabled:hover,
                .dataTables_wrapper .dataTables_paginate .paginate_button.last.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover,
                .dataTables_wrapper .dataTables_paginate .paginate_button.first:hover{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.previous.disabled:hover,
                .dataTables_wrapper .dataTables_paginate .paginate_button.first.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
                .dataTables_wrapper .dataTables_paginate .paginate_button.first {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.next,
                .dataTables_wrapper .dataTables_paginate .paginate_button.last{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
                    color: $theme_options_styles[linkColor] !important;
                }

                .commentPress:hover{
                    color: $theme_options_styles[linkColor];
                }

                #cal-slide-content a.event-item {
                    color: $theme_options_styles[linkColor] !important;
                }

                .tree-units summary::before,
                .tree-sessions summary::before{
                    background: $theme_options_styles[linkColor] url($urlServer/resources/img/units-expand-collapse.svg) 0 0;
                }

                .more-enabled-login-methods div{
                    color: $theme_options_styles[linkColor];
                }

                .ClickCourse,
                .ClickCourse:hover{
                    color: $theme_options_styles[linkColor];
                }

                .carousel-prev-btn,
                .carousel-prev-btn:hover,
                .carousel-next-btn,
                .carousel-next-btn:hover{
                    color: $theme_options_styles[linkColor];
                }

                .link-color{
                    color: $theme_options_styles[linkColor];
                }

                .appIcon span{
                   color: $theme_options_styles[linkColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO THE PLATFORM'S LINKS ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColor'])){
            $styles_str .= "
                a:hover, a:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                #btn-search:hover, #btn-search:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                .portfolio-tools a:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .nav-link-adminTools:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .link-color:hover,
                .link-color:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                .appIcon:hover{
                    background-color: $theme_options_styles[linkHoverColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// DELETE PLATFORM LINK COLOR ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkDeleteColor'])){
            $styles_str .= "
                .link-delete,
                .link-delete:hover,
                .link-delete:focus{
                    color: $theme_options_styles[linkDeleteColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// LINKS INSIDE ALERT COMPONENT  ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLinkAlertInfo'])){
            $styles_str .= "
                .alert-info a{
                    color: $theme_options_styles[clLinkAlertInfo];
                    font-weight: 700;
                    text-decoration: underline;
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertInfo'])){
            $styles_str .= "
                .alert-info a:hover,
                .alert-info a:focus{
                    color: $theme_options_styles[clLinkHoveredAlertInfo];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertWarning'])){
            $styles_str .= "
                .alert-warning a{
                    color: $theme_options_styles[clLinkAlertWarning];
                    font-weight: 700;
                    text-decoration: underline;
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertWarning'])){
            $styles_str .= "
                .alert-warning a:hover,
                .alert-warning a:focus{
                    color: $theme_options_styles[clLinkHoveredAlertWarning];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertSuccess'])){
            $styles_str .= "
                .alert-success a{
                    color: $theme_options_styles[clLinkAlertSuccess];
                    font-weight: 700;
                    text-decoration: underline;
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertSuccess'])){
            $styles_str .= "
                .alert-success a:hover,
                .alert-success a:focus{
                        color: $theme_options_styles[clLinkHoveredAlertSuccess];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertDanger'])){
            $styles_str .= "
                .alert-danger a{
                    color: $theme_options_styles[clLinkAlertDanger];
                    font-weight: 700;
                    text-decoration: underline;
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertDanger'])){
            $styles_str .= "
                .alert-danger a:hover,
                .alert-danger a:focus{
                        color: $theme_options_styles[clLinkHoveredAlertDanger];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// SETTINGS TO THE LEFT MENU OF COURSE /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .toolSidebarTxt{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if (!empty($theme_options_styles['leftMenuFontColor'])){
            $styles_str .= "
                #leftnav .panel a.parent-menu{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu span{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #collapse-left-menu-icon path{
                    fill: $theme_options_styles[leftMenuFontColor] !important;
                }

            ";
        }

        if (!empty($theme_options_styles['leftMenuHoverFontColor'])){
            $styles_str .= "
                #leftnav .panel .panel-sidebar-heading:hover{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover span{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item, .menu_btn_button .fa-bars{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    color:$theme_options_styles[leftSubMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    background-color:$theme_options_styles[leftSubMenuHoverBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    background-color: $theme_options_styles[leftMenuSelectedBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedLinkColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    color: $theme_options_styles[leftMenuSelectedLinkColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////// UPLOAD LOGO ////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['imageUpload'])){
            $logo_img =  "$urlThemeData/$theme_options_styles[imageUpload]";
        }

        if (isset($theme_options_styles['imageUploadSmall'])){
            $logo_img_small = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
        }

        if (isset($theme_options_styles['imageUploadFooter'])){
            $image_footer = "$urlThemeData/$theme_options_styles[imageUploadFooter]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OF HOMEPAGE ANNOUNCEMENTS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorAnnouncementHomepage']) && !empty($theme_options_styles['BgColorAnnouncementHomepage_gr'])){
            $new_gradient_str1 = "linear-gradient(105deg, $theme_options_styles[BgColorAnnouncementHomepage] 40%, $theme_options_styles[BgColorAnnouncementHomepage_gr] 60%)";
            $styles_str .= "
                .homepage-annnouncements-container{
                    background: $new_gradient_str1;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////// BACKGROUND COLOR TO THE LIST ANNOUNCEMENT IN HOMEPAGE ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorAnnouncementHomepageLink'])){
            $styles_str .= "
                .homepage-annnouncements-container .list-group-item.element{
                    background-color: $theme_options_styles[BgColorAnnouncementHomepageLink];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////// BORDER COLOR TO THE BOTTOM SIDE OF LIST ANNOUNCEMENT IN HOMEPAGE ////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderColorAnnouncementHomepageLink'])){
            $styles_str .= "
                .homepage-annnouncements-container .list-group-item.element{
                    border-bottom: solid 1px $theme_options_styles[BgBorderColorAnnouncementHomepageLink];
                }

                .homepage-annnouncements-container .list-group-item.element:last-child{
                    border-bottom: none;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// LINK COLOR OF LIST ANNOUNCEMENT IN HOMEPAGE //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorAnnouncementHomepageLinkElement'])){
            $styles_str .= "
                .homepage-annnouncements-container a,
                .homepage-annnouncements-container .list-group-item.element a{
                    color:  $theme_options_styles[clColorAnnouncementHomepageLinkElement];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// LINK HOVERED COLOR OF LIST ANNOUNCEMENT IN HOMEPAGE /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredColorAnnouncementHomepageLinkElement'])){
            $styles_str .= "
                .homepage-annnouncements-container a:hover,
                .homepage-annnouncements-container a:focus,
                .homepage-annnouncements-container .list-group-item.element a:hover,
                .homepage-annnouncements-container .list-group-item.element a:focus{
                    color:  $theme_options_styles[clHoveredColorAnnouncementHomepageLinkElement];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// TEXT COLOR OF HOMEPAGE ANNOUNCEMENTS ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorAnnouncementHomepage'])){
            $styles_str .= "
                .homepage-annnouncements-container .card h3,
                .homepage-annnouncements-container .card .text-heading-h3,
                .homepage-annnouncements-container .card .text-content,
                .homepage-annnouncements-container .card .truncate-announcement *{
                    color: $theme_options_styles[TextColorAnnouncementHomepage];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////// BACKGROUND COLOR OF HOMEPAGE CARD ANNOUNCEMENTS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgCardAnnouncementDate'])){
            $styles_str .= "
                .card-announcement-date {
                    background-color: $theme_options_styles[bgCardAnnouncementDate] !important;
                    border: solid 1px $theme_options_styles[bgCardAnnouncementDate];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR OF HOMEPAGE CARD ANNOUNCEMENTS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorCardAnnouncementDate'])){
            $styles_str .= "
                .card-announcement-date * {
                    color: $theme_options_styles[TextColorCardAnnouncementDate];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// ADD PADDING TO THE HOMEPAGE ANNOUNCEMENTS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AddPaddingAnnouncementsListGroup'])){
            $styles_str .= "
                .homepage-annnouncements-container .list-group-item.element{
                    padding-left: 15px;
                    padding-right: 15px;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// BACKGROUND COLOR OF HOMEPAGE STATISTICS CONTAINER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorStatisticsHomepage']) && !empty($theme_options_styles['BgColorStatisticsHomepage_gr'])){
            $new_gradient_str2 = "linear-gradient(105deg, $theme_options_styles[BgColorStatisticsHomepage] 40%, $theme_options_styles[BgColorStatisticsHomepage_gr] 60%)";
            $styles_str .= "
                .homepage-statistics-container{
                    background: $new_gradient_str2;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// TEXT COLOR OF HOMEPAGE STATISTICS CONTAINER ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorStatisticsHomepage'])){
            $styles_str .= "
                .homepage-statistics-container .card-header h3,
                .homepage-statistics-container .card-header .text-heading-h3{
                    color: $theme_options_styles[TextColorStatisticsHomepage];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////// BACKGROUND COLOR OF HOMEPAGE POPULAR COURSES CONTAINER ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorPopularCoursesHomepage']) && !empty($theme_options_styles['BgColorPopularCoursesHomepage_gr'])){
            $new_gradient_str3 = "linear-gradient(105deg, $theme_options_styles[BgColorPopularCoursesHomepage] 40%, $theme_options_styles[BgColorPopularCoursesHomepage_gr] 60%)";
            $styles_str .= "
                .homepage-popoular-courses-container{
                    background: $new_gradient_str3;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// TEXT COLOR OF HOMEPAGE POPULAR COURSES CONTAINER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorPopularCoursesHomepage'])){
            $styles_str .= "
                .homepage-popoular-courses-container .card-header h3,
                .homepage-popoular-courses-container .card-header .text-heading-h3{
                    color: $theme_options_styles[TextColorPopularCoursesHomepage];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////// BACKGROUND COLOR OF HOMEPAGE TEXTS CONTAINER /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorTextsHomepage']) && !empty($theme_options_styles['BgColorTextsHomepage_gr'])){
            $new_gradient_str4 = "linear-gradient(105deg, $theme_options_styles[BgColorTextsHomepage] 40%, $theme_options_styles[BgColorTextsHomepage_gr] 60%)";
            $styles_str .= "
                .homepage-texts-container{
                    background: $new_gradient_str4;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// TEXT COLOR OF HOMEPAGE TEXTS CONTAINER /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorTextsHomepage'])){
            $styles_str .= "
                .homepage-texts-container *{
                    color: $theme_options_styles[TextColorTextsHomepage];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF PORTFOLIO - COURSES CONTAINER ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorWrapperPortfolioCourses'])){
            $styles_str .= "
                .portfolio-courses-container {
                    background-color:$theme_options_styles[BgColorWrapperPortfolioCourses];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND COLOR OF COURSE CONTAINER (RIGHT COL) ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['RightColumnCourseBgColor'])){
            $styles_str .= "
                .col_maincontent_active {
                    background-color:$theme_options_styles[RightColumnCourseBgColor];
                }

                @media(max-width:991px){
                    .module-container:has(.course-wrapper){
                        background-color:$theme_options_styles[RightColumnCourseBgColor];
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND IMAGE TO THE COURSE CONTAINER (RIGHT COL) //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['RightColumnCourseBgImage'])){
            $styles_str .= "
                .col_maincontent_active {
                    background: url('$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]');
                    background-size: 100% 100%;
                    background-attachment: fixed;
                }

                @media(max-width:991px){
                    .module-container:has(.course-wrapper){
                        background: url('$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]');
                        background-size: 100% 100%;
                        background-attachment: fixed;
                    }
                }
            ";

            if(isset($LinearGr)){
                $bg_image_course = "url('$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]')";
                $styles_str .= "
                    .col_maincontent_active{
                        background: $LinearGr$bg_image_course;
                        background-size: 100% 100%;
                        background-attachment: fixed;
                    }

                    @media(max-width:991px){
                        .module-container:has(.course-wrapper){
                            background: $LinearGr$bg_image_course;
                            background-size: 100% 100%;
                            background-attachment: fixed;
                        }
                    }
                ";
            }
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BORDER COLOR TO THE LEFT SIDE OF COURSE CONTAINER  //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BorderLeftToRightColumnCourseBgColor'])){
            $styles_str .= "
                @media(min-width:992px){
                    .col_maincontent_active {
                        border-left: solid 1px $theme_options_styles[BorderLeftToRightColumnCourseBgColor];
                    }

                    .col_maincontent_active.search-content {
                        border-left: none;
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE PANEL'S BODY //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgPanels'])){
            $styles_str .= "

                .panel-action-btn-default,
                .panel-primary,
                .panel-success,
                .panel-default,
                .panel-info,
                .panel-danger,
                .panel-admin,
                .card,
                .user-info-card,
                .panelCard,
                .cardLogin,
                .statistics-card,
                .bodyChat{
                    background-color:$theme_options_styles[BgPanels] ;
                }

                .wallWrapper{
                    background-color:$theme_options_styles[BgPanels] !important;
                }

                .testimonials .testimonial {
                    background: $theme_options_styles[BgPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #lti_label{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #jsmind_container {
                    background: $theme_options_styles[BgPanels] !important;
                }

                .card-transparent,
                .card-transparent .card-header,
                .card-transparent .card-body,
                .card-transparent .card-footer,
                .card-transparent .panel-heading,
                .card-transparent .panel-body,
                .card-transparent .panel-footer{
                    background-color: transparent ;
                }

                .panel-default .panel-heading,
                .panel-action-btn-default .panel-heading {
                    background: $theme_options_styles[BgPanels];
                }

                .card-affixed{
                    background-color: $theme_options_styles[BgPanels] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO THE PANELS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderPanels'])){
            $styles_str .= "

                .user-info-card,
                .form-homepage-login,
                .panelCard,
                .cardLogin,
                .border-card,
                .statistics-card,
                .panel-success,
                .panel-admin,
                .panel-default,
                .panel-danger,
                .panel-primary,
                .panel-info,
                .panel-action-btn-default{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .panelCard.border-card-left-default {
                    border-left: solid 7px $theme_options_styles[clBorderPanels];
                }

                .border-top-default{
                    border-top: solid 1px $theme_options_styles[clBorderPanels];
                    border-left: none;
                    border-right: none;
                    border-bottom: none;
                }

                .BorderSolidDes{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .wallWrapper{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .testimonials .testimonial {
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                #lti_label{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                #jsmind_container {
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .panel-default .panel-heading,
                .panel-action-btn-default .panel-heading {
                    border: none;
                }

                .panel-default:has(.panel-heading) {
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .panel-default:not(:has(.panel-heading)){
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .card-affixed{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACHGROUND HOVERED COLOR TO THE PANELS ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgBorderHoveredPanels'])){
            $styles_str .= "
                .card-default:hover{
                    background-color: $theme_options_styles[bgBorderHoveredPanels];
                    -webkit-transition: background-color 2s ease-out;
                    -moz-transition: background-color 2s ease-out;
                    -o-transition: background-color 2s ease-out;
                    transition: background-color 2s ease-out;
                }

                .testimonial.slick-current.slick-active.slick-center:hover{
                    background-color: $theme_options_styles[bgBorderHoveredPanels] !important;
                    -webkit-transition: background-color 2s ease-out;
                    -moz-transition: background-color 2s ease-out;
                    -o-transition: background-color 2s ease-out;
                    transition: background-color 2s ease-out;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACHGROUND HOVERED COLOR TO THE PANELS ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredTextPanels'])){
            $styles_str .= "

                .card-default:hover caption,
                .card-default:hover h1,
                .card-default:hover h2,
                .card-default:hover h3,
                .card-default:hover h4,
                .card-default:hover h5,
                .card-default:hover h6,
                .card-default:hover p,
                .card-default:hover strong,
                .card-default:hover small,
                .card-default:hover .Neutral-900-cl,
                .card-default:hover .form-label,
                .card-default:hover .default-value,
                .card-default:hover label,
                .card-default:hover th,
                .card-default:hover td,
                .card-default:hover .panel-body,
                .card-default:hover .card-body,
                .card-default:hover div,
                .card-default:hover .visibleFile,
                .card-default:hover .help-block,
                .card-default:hover .control-label-notes,
                .card-default:hover .title-default,
                .card-default:hover .modal-title-default,
                .card-default:hover .text-heading-h2,
                .card-default:hover .text-heading-h3,
                .card-default:hover .text-heading-h4,
                .card-default:hover .text-heading-h5,
                .card-default:hover .text-heading-h6,
                .card-default:hover .action-bar-title{
                    color: $theme_options_styles[clHoveredTextPanels];
                    -webkit-transition: background-color 2s ease-out;
                    -moz-transition: background-color 2s ease-out;
                    -o-transition: background-color 2s ease-out;
                    transition: background-color 2s ease-out;
                }

                .card-default:hover .text-muted{
                    color: $theme_options_styles[clHoveredTextPanels] !important;
                    -webkit-transition: background-color 2s ease-out;
                    -moz-transition: background-color 2s ease-out;
                    -o-transition: background-color 2s ease-out;
                    transition: background-color 2s ease-out;
                }

                .testimonial.slick-current.slick-active.slick-center:hover *{
                    color: $theme_options_styles[clHoveredTextPanels] !important;
                    -webkit-transition: background-color 2s ease-out;
                    -moz-transition: background-color 2s ease-out;
                    -o-transition: background-color 2s ease-out;
                    transition: background-color 2s ease-out;
                }

                .card-default:hover .circle-img-contant{
                    border: solid 1px $theme_options_styles[clHoveredTextPanels];
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// BOX SHADOW TO THE PANELS ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BoxShadowPanels'])){
            $styles_str .= "
                .panel-action-btn-default,
                .panel-primary,
                .panel-success,
                .panel-default,
                .panel-info,
                .panel-danger,
                .panel-admin,
                .card,
                .user-info-card,
                .panelCard,
                .cardLogin,
                .statistics-card,
                .bodyChat{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels];
                }

                .wallWrapper{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels] !important;
                }

                .testimonials .testimonial {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels];
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels];
                }

                #lti_label{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels];
                }

                #jsmind_container {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels] !important;
                }

                .panel-default .panel-heading,
                .panel-action-btn-default .panel-heading {
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels];
                }

                .card-affixed{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels] !important;
                }

                .panelCard-comments,
                .panelCard-questionnaire,
                .cardReports,
                .panelCard-exercise{
                    box-shadow: 0px 0 30px $theme_options_styles[BoxShadowPanels] !important;
                }


                .card-transparent,
                .card-transparent .card-header,
                .card-transparent .card-body,
                .card-transparent .card-footer,
                .card-transparent .panel-heading,
                .card-transparent .panel-body,
                .card-transparent .panel-footer{
                   box-shadow: none !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// HOVERED BOX SHADOW TO THE DEFAULT PANELS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgHoveredBoxShadowPanels'])){
            $styles_str .= "
                .card-default:hover{
                    transition: .3s ease;
                    box-shadow: 0px 0 30px $theme_options_styles[bgHoveredBoxShadowPanels];
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE COMMENTS PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    background-color: $theme_options_styles[BgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BORDER COLOR TO THE COMMENTS PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    border: solid 1px $theme_options_styles[clBorderBgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE QUESTIONNAIRE PANELS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire,
                .cardReports{
                    background-color: $theme_options_styles[BgQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE QUESTIONNAIRE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire,
                .cardReports{
                    border: solid 1px $theme_options_styles[clBorderQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE REPORTS PANELS //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgReportsPanels'])){
            $styles_str .= "
                .cardReports{
                    background-color: $theme_options_styles[BgReportsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO THE REPORTS PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderReportsPanels'])){
            $styles_str .= "
                .cardReports{
                    border: solid 1px $theme_options_styles[clBorderReportsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE EXERCISE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    background-color: $theme_options_styles[BgExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER COLOR TO THE EXERCISES PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    border: solid 1px $theme_options_styles[clBorderExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE CHAT CONTAINER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutChatContainer'])){
            $styles_str .= "
                .bodyChat{
                    background: none;
                    background-color: $theme_options_styles[AboutChatContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO THE CHAT CONTAINER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderChatContainer'])){
            $styles_str .= "
                .embed-responsive-item{
                    border: solid 1px $theme_options_styles[AboutBorderChatContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BOX SHADOW TO THE CHAT CONTAINER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutChatContainerBoxShadow'])){
            $styles_str .= "
                .bodyChat{
                    box-shadow: 0px 0 30px $theme_options_styles[AboutChatContainerBoxShadow] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE INFO CONTAINER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    background-color: $theme_options_styles[AboutCourseInfoContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE INFO CONTAINER ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    border: solid 1px $theme_options_styles[AboutBorderCourseInfoContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BOX SHADOW TO THE COURSE INFO CONTAINER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutCourseInfoContainerBoxShadow'])){
            $styles_str .= "
                .card-course-info{
                    box-shadow: 0px 0 30px $theme_options_styles[AboutCourseInfoContainerBoxShadow] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE UNITS CONTAINER /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutUnitsContainer'])){
            $styles_str .= "
                .card-units,
                .card-sessions{
                    background-color: $theme_options_styles[AboutUnitsContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE UNITS CONTAINER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderUnitsContainer'])){
            $styles_str .= "
                .card-units,
                .card-sessions{
                    border: solid 1px $theme_options_styles[AboutBorderUnitsContainer];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BOX SHADOW TO THE COURSE UNITS CONTAINER ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutUnitsContainerBoxShadow'])){
            $styles_str .= "
                .card-units,
                .card-sessions{
                    box-shadow: 0px 0 30px $theme_options_styles[AboutUnitsContainerBoxShadow] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgContainerImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar{
                    background: $theme_options_styles[bgContainerImportantAnnouncement];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clContainerImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar .title-announcement,
                .notification-top-bar i.fa-bell{
                    color: $theme_options_styles[clContainerImportantAnnouncement];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// LINK COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLinkImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar a{
                    color: $theme_options_styles[clLinkImportantAnnouncement];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// HOVERED LINK COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredLinkImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar a:hover{
                    color: $theme_options_styles[clHoveredLinkImportantAnnouncement];
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE SUCCESS BADGE //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBadgeSuccess'])){
            $styles_str .= "
                .badge.Success-200-bg{
                    background-color: $theme_options_styles[BgBadgeSuccess];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE SUCCESS BADGE ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBadgeSuccess'])){
            $styles_str .= "
                .badge.Success-200-bg *,
                .badge.Success-200-bg{
                    color: $theme_options_styles[clBadgeSuccess];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE WARNING BADGE //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBadgeWarning'])){
            $styles_str .= "
                .badge.Warning-200-bg{
                    background-color: $theme_options_styles[BgBadgeWarning];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE WARNING BADGE ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBadgeWarning'])){
            $styles_str .= "
                .badge.Warning-200-bg *,
                .badge.Warning-200-bg{
                    color: $theme_options_styles[clBadgeWarning];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE NEUTRAL BADGE //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBadgeNeutral'])){
            $styles_str .= "
                .badge.Neutral-900-bg{
                    background-color: $theme_options_styles[BgBadgeNeutral];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE NEUTRAL BADGE ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBadgeNeutral'])){
            $styles_str .= "
                .badge.Neutral-900-bg *,
                .badge.Neutral-900-bg{
                    color: $theme_options_styles[clBadgeNeutral];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE PRIMARY BADGE //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBadgePrimary'])){
            $styles_str .= "
                .badge.Primary-600-bg{
                    background-color: $theme_options_styles[BgBadgePrimary];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE PRIMARY BADGE ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBadgePrimary'])){
            $styles_str .= "
                .badge.Primary-600-bg *,
                .badge.Primary-600-bg{
                    color: $theme_options_styles[clBadgePrimary];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE ACCENT BADGE //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBadgeAccent'])){
            $styles_str .= "
                .badge.Accent-200-bg{
                    background-color: $theme_options_styles[BgBadgeAccent];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO THE ACCENT BADGE ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBadgeAccent'])){
            $styles_str .= "
                .badge.Accent-200-bg *,
                .badge.Accent-200-bg{
                    color: $theme_options_styles[clBadgeAccent];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////// BACKGROUND COLOR TO THE PLATFORM CONTENT WHEN PLATFORM IS BOXED TYPE ////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['view_platform']) && $theme_options_styles['view_platform'] == 'boxed'){

            $maxWidthPlatform = (isset($theme_options_styles['fluidContainerWidth']) ? "$theme_options_styles[fluidContainerWidth]px" : '1140px');
            $styles_str .= "

                @media (min-width: 992px) {
                    .ContentEclass{
                        margin-left: auto;
                        margin-right: auto;
                        min-width: 960px;
                        max-width: $maxWidthPlatform ;
                    }

                    #bgr-cheat-header,
                    #bgr-cheat-header-mentoring{
                        padding-left: 10px;
                        padding-right: 10px;
                        margin-left: auto;
                        margin-right: auto;
                        max-width: $maxWidthPlatform ;
                    }

                    #bgr-cheat-header.fixed,
                    #bgr-cheat-header-mentoring.fixed {
                        margin-left: auto;
                        margin-right: auto;
                        max-width: $maxWidthPlatform ;
                    }

                    .notification-top-bar{
                        left: auto;
                        max-width: $maxWidthPlatform ;
                    }
                }

            ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND COLOR TO THE MAIN SECTION ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorContentPlatform'])){
            $styles_str .= "
                .ContentEclass,
                .main-container,
                .module-container{
                    background-color: $theme_options_styles[bgColorContentPlatform];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// COLOR FOCUS IN TEXT AREA  ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ColorFocus'])){
            $styles_str .= "
                button:focus-visible,
                a:focus-visible,
                input:focus-visible,
                select:focus-visible,
                textarea:focus-visible{
                    outline: 0 !important;
                    box-shadow: none !important;
                    border: solid 1px $theme_options_styles[ColorFocus] !important;
                }

                .input-group:focus-within .input-group-text{
                    outline: 0 !important;
                    box-shadow: none !important;
                    border: solid 1px $theme_options_styles[ColorFocus] !important;
                    border-left: 0px !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// CONTACT IMAGE UPLOADED  /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(isset($theme_options_styles['contactUpload'])){
            $urlThData = $urlAppend . 'courses/theme_data/' . $theme_id;
            $contact_image = "$urlThData/$theme_options_styles[contactUpload]";
            $styles_str .= "
                .contact-content{
                    background-image: url($contact_image);
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// LEARNPATH - SPECIAL CASES  ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorHeader'])){
            $styles_str .= "
                #leftTOCtoggler,
                .prev-next-learningPath{
                    color:$theme_options_styles[linkColorHeader];
                }

            ";
        }
        if (!empty($theme_options_styles['bgColor'])){
            $styles_str .= "
                .body-learning-path,
                .iframe-learningPath,
                .body-learningPath{
                    background-color:$theme_options_styles[bgColor]; 
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// PROGRESSBAR - SPEACIAL CASES /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BackProgressBar']) && !empty($theme_options_styles['BgProgressBar']) &&
                            !empty($theme_options_styles['BgColorProgressBarAndText'])){

            $styles_str .= "
                .progress-circle-bar::before{
                    color: $theme_options_styles[BgColorProgressBarAndText];
                }
            ";
        }


        // Create .css file for the ($theme_id) in order to override the default.css file when it is necessary.
        $fileStyleStr = $webDir . "/courses/theme_data/$theme_id/style_str.css";
        if (!file_exists($fileStyleStr)) {
            file_put_contents($fileStyleStr, "");
        } else if (isset($_SESSION['theme_changed'])) { // theme has changed ?
            file_put_contents($fileStyleStr, $styles_str);
            unset($_SESSION['theme_changed']);
        }
    }
}
