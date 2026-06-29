<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

// mPDF library temporary file path and font path
if (isset($webDir)) { // needed for avoiding 'notices' in some files
    define("_MPDF_TEMP_PATH", $webDir . '/courses/temp/pdf/');
    define("_MPDF_TTFONTDATAPATH", $webDir . '/courses/temp/pdf/');
}
require_once 'constants.php';
require_once 'lib/theme.php';
require_once 'log.class.php';
require_once 'lib/session.class.php';
require_once 'lib/file_cache.class.php';
require_once 'lib/hierarchy.class.php';
require_once 'modules/admin/tenant_functions.php';

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
            $head_content .= css_link('datatables/datatables.min.css');
            $file = 'datatables/datatables.min.js';
        } elseif ($file == 'jszip') {
            $file = 'jszip/dist/jszip.min.js';
        } elseif ($file == 'pdfmake') {
            $file = 'pdfmake/build/pdfmake.js';
        } elseif ($file == 'vfs_fonts') {
            $file = 'pdfmake/build/vfs_fonts.js';
        } elseif ($file == 'RateIt') {
            $file = 'jquery.rateit.min.js';
        } elseif ($file == 'autosize') {
            $file = 'autosize/autosize.min.js';
        } elseif ($file == 'waypoints-infinite') {
            $head_content .= js_link('waypoints/jquery.waypoints.min.js');
            $file = 'waypoints/shortcuts/infinite.min.js';
        } elseif ($file == 'select2') {
            // $head_content .= css_link('select2-4.0.3/css/select2.min.css') .
            // css_link('select2-4.0.3/css/select2-bootstrap.min.css') .
            // css_link('select2-4.0.3/css/override_select2_design.css?v=4.0-dev') .
            // js_link('select2-4.0.3/js/select2.full.min.js');
            // $file = "select2-4.0.3/js/i18n/$language.js";
            $head_content .= css_link('select2-4.0.13/dist/css/select2.min.css') .
            css_link('select2-4.0.13/dist/css/override_select2_design.css?v=4.0-dev') .
            js_link('select2-4.0.13/dist/js/select2.full.min.js');
            $file = "select2-4.0.13/dist/js/i18n/$language.js";
        } elseif ($file == 'slimselect') {
            $head_content .= css_link('slim-select/slimselect.css');
            $file = 'slim-select/slimselect.js';
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
        } elseif ($file == 'bootstrap-table') {
            $head_content .= css_link('bootstrap-table/bootstrap-table.min.css');
            if ($language != 'en') {
                switch ($language) {
                    case 'el': $file = 'bootstrap-table/locale/bootstrap-table-el-GR.min.js'; break;
                    case 'fr': $file = 'bootstrap-table/locale/bootstrap-table-fr-FR.min.js'; break;
                    case 'de': $file = 'bootstrap-table/locale/bootstrap-table-de-DE.min.js'; break;
                    case 'it': $file = 'bootstrap-table/locale/bootstrap-table-it-IT.min.js'; break;
                    case 'es': $file = 'bootstrap-table/locale/bootstrap-table-es-ES.min.js'; break;
                    default: break;
                }
            }
            $head_content .= js_link('bootstrap-table/bootstrap-table.min.js');
            $head_content .= js_link('bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js');
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
        } elseif ($file == 'jquery-ui') {
            $file = 'jquery-ui.min.js';
        } elseif ($file == 'jquery-touch') {
            $file = 'jquery.ui.touch-punch.min.js';
        } elseif ($file == 'drag-and-drop-shapes') {
            $file = 'drag-and-drop-shapes.js';
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
 * Retrieve the URL for a user's profile image.
 *
 * @param int $user_id The ID of the user whose profile image is being retrieved.
 * @param int $size The size of the image in pixels. Defaults to IMAGESIZE_SMALL.
 * @param bool $menu If true, the function will return false if no image is found. Defaults to false.
 *
 * @global string $webDir The root directory of the web application.
 * @global string $themeimg The directory containing theme images.
 * @global string $urlAppend The base URL of the application.
 * @global int $course_id The ID of the current course (if applicable).
 * @global bool $is_editor Whether the current user is an editor.
 * @global int $uid The ID of the currently logged-in user.
 *
 * @return string|false The URL of the user's profile image, or a default image if none exists.
 *                      Returns false if $menu is true and no image is found.
 */

function user_icon($user_id, $size = IMAGESIZE_SMALL, $menu = false) {
    global $webDir, $themeimg, $urlAppend, $course_id, $is_editor, $uid;

    // Check if a cache buster is set for the profile image and append it as a query parameter.
    if (isset($_SESSION['profile_image_cache_buster'])) {
        $suffix = '?v=' . $_SESSION['profile_image_cache_buster'];
    } else {
        $suffix = '';
    }

    // Query the database to check if the user has a profile image and if it is public.
    $user = Database::get()->querySingle("SELECT has_icon, pic_public
        FROM user WHERE id = ?d", $user_id);

    // If the user has a public image or the current user has permission to view it.
    if ($user and
        ($user->pic_public or $uid == $user_id or
         $_SESSION['status'] == USER_TEACHER or
         (isset($course_id) and $course_id and $is_editor))) {

        // Generate the hashed file name for the user's profile image.
        $hash = profile_image_hash($user_id);
        $hashed_file = "courses/userimg/{$user_id}_{$hash}_$size.jpg";

        // Check if the hashed file exists and return its URL.
        if (file_exists($hashed_file)) {
           return $urlAppend . $hashed_file;

        // Check if a non-hashed version of the file exists and return its URL.
        } elseif (file_exists("courses/userimg/{$user_id}_$size.jpg")) {
           return "{$urlAppend}courses/userimg/{$user_id}_$size.jpg";
        }
    }

    // If $menu is true and no image is found, return false.
    if ($menu) {
        return false;
    }

    // Return the URL of the default profile image.
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
    if (!is_null($id)) {
        $uid = $id;
    } else {
        $uid = $GLOBALS['uid'] ?? null;
    }
    if ($uid) {
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
 * @brief check if user has completed all prerequisites for a course
 * @param $uid
 * @param $course_id
 * @return array
 */
function check_course_prerequisites($uid, $course_id) {
    global $urlServer;
    $missing_links = [];
    $prereqs = Database::get()->queryArray("SELECT cp.prerequisite_course, c.title, c.code
                             FROM course_prerequisite cp
                             JOIN course c ON cp.prerequisite_course = c.id
                             WHERE cp.course_id = ?d", $course_id);
    if (count($prereqs) > 0) {
        foreach ($prereqs as $prereq) {
            $completed = Database::get()->querySingle("SELECT id
                                  FROM user_badge
                                  WHERE user = ?d
                                  AND badge IN (SELECT id FROM badge WHERE course_id = ?d AND bundle = -1)
                                  AND completed = 1", $uid, $prereq->prerequisite_course);
            if (!$completed) {
                $missing_links[] = "<a href='{$urlServer}courses/{$prereq->code}/'>" . q($prereq->title) . " (" . q($prereq->code) . ")</a>";
            }
        }
    }
    return $missing_links;
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
 * @brief check if the course has expired
 * @param $cid
 * @return bool
 */
function course_has_expired($cid): bool
{
    $end_date = Database::get()->querySingle("SELECT end_date FROM course WHERE id = ?d", $cid)->end_date;
    if (!is_null($end_date)) {
        if (date("Y-m-d") >= $end_date) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * @brief check if the course has started
 * @param $cid
 * @return bool
 */
function course_has_started($cid): bool
{
    $start_date = Database::get()->querySingle("SELECT start_date FROM course WHERE id = ?d", $cid)->start_date;
    if (!is_null($start_date)) {
        if (date("Y-m-d") >= $start_date) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * @brief check if registration has started for a given course
 * @param $cid
 * @return bool
 */
function course_reg_date_started($cid): bool
{
    $reg_start_date = Database::get()->querySingle("SELECT reg_start_date FROM course WHERE id = ?d", $cid)->reg_start_date;
    if (!is_null($reg_start_date)) {
        if (date("Y-m-d") >= $reg_start_date) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * @brief check if registration has ended for a given course
 * @param $cid
 * @return bool
 */
function course_reg_date_ended($cid): bool
{
    $reg_end_date = Database::get()->querySingle("SELECT reg_end_date FROM course WHERE id = ?d", $cid)->reg_end_date;
    if (!is_null($reg_end_date)) {
        if (date("Y-m-d") >= $reg_end_date) {
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
    $text = preg_replace("/<(\/div|\/p|\/pre|br)[^>]*>\s*\n?/i", "\n", $string);
    return html_entity_decode(strip_tags($text));
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
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

function format_locale_date($datetime_stamp, $format = null, $display_time = true, $pattern = null) {

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
    if ($pattern) {
        $fmt = datefmt_create($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Europe/Athens', IntlDateFormatter::TRADITIONAL, $pattern);
    } else {
        $fmt = datefmt_create($locale, $format_date_style, $format_time_style, 'Europe/Athens', IntlDateFormatter::TRADITIONAL);
    }

    return datefmt_format($fmt, $datetime_stamp);
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
    if (!defined('STATIC_MODULE') and $module_id and array_key_exists($module_id, $modules) and $script == 'index.php' and (count($_GET) == 1 or ($module_id == MODULE_ID_PROGRESS and count($_GET) == 2 and isset($_GET['tab']))) and isset($_GET['course']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief checks if a module is visible
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
                                course_id = ?d", $module_id, $course_id);
    if ($v) {
        if ($v->visible == 1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * @brief Check if a module is disabled.
 *        If called inside of a course, takes in account course / collaboration setting
 * @return boolean
 */
function is_module_disable($module_id, $extra = null) {
    global $require_current_course, $is_collaborative_course;

    if ((get_config('show_collaboration') && get_config('show_always_collaboration')) or
        ($require_current_course && $is_collaborative_course)) {
        $q = Database::get()->querySingle("SELECT * FROM module_disable_collaboration WHERE module_id = ?d", $module_id);
        if ($q) {
            return true;
        }
    } elseif (!$require_current_course && get_config('show_collaboration') && !get_config('show_always_collaboration') && is_null($extra)){
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
            $nrlz_ids_final[] = $f_id;
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

    // check if the module belongs to a certificate
    $sql = Database::get()->querySingle("SELECT * FROM certificate_criterion JOIN certificate "
                                            . "ON certificate.id = certificate_criterion.certificate "
                                            . "WHERE course_id = ?d AND module = ?d AND resource = ?d",
                                        $course_id, $module, $resource_id);
    if ($sql) {
        return true;
    }
    // check if the module belongs to the badge
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
            $access_icon = "<span tabindex='0' class='fa fa-lock-open fa-lg fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeOpen' aria-label='$langTypeOpen'></span>";
            break;
        }
        case COURSE_REGISTRATION: {
            $access_icon = "<div tabindex='0' class='d-inline-flex align-items-center' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeRegistration' aria-label='$langTypeRegistration'><span class='fa fa-lock fa-lg fa-fw access'></span>
            <span class='fa fa-pencil text-danger fa-custom-lock mt-0' style='margin-left:-5px;'></span></div>";
            break;
        }
        case COURSE_CLOSED: {
            $access_icon = "<span tabindex='0' class='fa fa-lock fa-lg fa-fw fa-access' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeClosed' aria-label='$langTypeClosed'></span>";
            break;
        }
        case COURSE_INACTIVE: {
            $access_icon = "<span tabindex='0' class='fa fa-ban fa-lg fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langTypeInactive' aria-label='$langTypeInactive'></span>";
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
    $sec = intval($sec) % 60;
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
                                                          AND (cu.visible = 1 OR cu.visible = 2)
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

    Database::get()->query("DELETE d FROM user_points_game_criterion d, points_game_criterion c, points_game s WHERE d.points_game_criterion = c.id
        AND c.points_game = s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM user_points_game_points d, points_game s WHERE d.points_game = s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM points_game_criterion d , points_game s WHERE d.points_game = s.id AND s.course_id = ?d", $cid);
    Database::get()->query("DELETE d FROM points_game_levels d, points_game s WHERE d.points_game = s.id AND s.course_id = ?d ", $cid);
    Database::get()->query("DELETE FROM points_game WHERE course_id = ?d", $cid);

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
    Database::get()->query("DELETE FROM seb_courses WHERE course_id = ?d", $cid);
    Database::get()->query("DELETE FROM course_import WHERE course_id = ?d", $cid);

    removeDir("$webDir/courses/$course_code");
    removeDir("$webDir/video/$course_code");
    // refresh index
    require_once 'modules/search/classes/ConstantsUtil.php';
    require_once 'modules/search/classes/SearchEngineFactory.php';
    $searchEngine = SearchEngineFactory::create();
    $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVEALLBYCOURSE, ConstantsUtil::RESOURCE_IDX, $cid);

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
                    if (isset($data->file_path)) {
                        unlink($webDir . "/courses/". course_id_to_code($courseid) . "/work/" . $data->file_path);
                    }
                }
            }
            Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_badge WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_certificate_criterion WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_certificate WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_points_game_criterion WHERE user = ?d", $u);
            Database::get()->query("DELETE FROM user_points_game_points WHERE user = ?d", $u);
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
 * @brief set user option.
 * @param $user_id
 * @param $option
 * @param $value
 * @return void
 */
function set_user_option($user_id, $option, $value)
{
    $options = [];

    $q = Database::get()->querySingle("SELECT options FROM user WHERE id = ?d", $user_id);
    if (!is_null($q->options)) {
        $options = json_decode($q->options, true);
    }
    if ($value) {
        $options[$option] = $value;
    } else {
        unset($options[$option]);
    }
    $user_options = json_encode($options);
    Database::get()->query("UPDATE user SET options = ?s WHERE id = ?d", $user_options, $user_id);
}

/**
 * @brief get user option
 * @param $user_id
 * @param $option
 * @return mixed|null
 */
function get_user_option($user_id, $option)
{
    $q = Database::get()->querySingle("SELECT options FROM user WHERE id = ?d", $user_id);
    if (!is_null($q->options)) {
        $options = json_decode($q->options, true);
    }
    if (isset($options[$option])) {
        return $options[$option];
    } else {
        return null;
    }
}

/**
 * @brief delete user option
 * @param $user_id
 * @param $option
 * @return void
 */
function delete_user_option($user_id, $option)
{
    $q = Database::get()->querySingle("SELECT options FROM user WHERE id = ?d", $user_id);
    if (!is_null($q->options)) {
        $options = json_decode($q->options, true);
        if (isset($options[$option])) {
            unset($options[$option]);
            if (count($options) > 0) {
                $user_options = json_encode($options);
                Database::get()->query("UPDATE user SET options = ?s WHERE id = ?d", $user_options, $user_id);
            } else {
                Database::get()->query("UPDATE user SET options = null WHERE id = ?d",$user_id);
            }
        }
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
    global $head_content, $language, $urlAppend, $course_code, $langPopUp, $langPopUpFrame, $is_editor, $is_admin, $langResourceBrowser, $langMore, $tinymce_color_text, $langInputTextEditor,
        $langLatexDialogTitle, $langLatexInput, $langLatexPreview, $langInsert, $langCancel,
        $langLatexCatGreekLetters, $langLatexCatOperators, $langLatexCatRelations, $langLatexCatArrows, $langLatexCatDelimiters,
        $langLatexCatAccents, $langLatexCatFunctions, $langLatexCatMathStructures, $langLatexCatMiscellaneous, $langLatexCatChemicalSymbols;
    static $init_done = false;
    if (!$init_done) {
        $init_done = true;
        $filebrowser = $url = '';

        // params for tinymce embed
        $activemodule = 'document/index.php';
        $append_module = (current_module_id()) ? "&originating_module=" . q(current_module_id()) : '';
        $append_forum = (isset($_REQUEST['forum'])) ? "&originating_forum=" . q($_REQUEST['forum']) : '';

        if (isset($course_code) && $course_code) {

            $url = $urlAppend . "modules/" . $activemodule . "?course=" . $course_code . "&embedtype=tinymce" . $append_module . $append_forum . "&docsfilter=";

            $filebrowser = "file_picker_callback: function (callback, value, meta) {
                        var url = '" . $url . "' + meta.filetype;
                        tinymce.activeEditor.windowManager.openUrl({
                            title: '" . js_escape($langResourceBrowser) . "',
                            url: url,
                            width: 800,
                            height: 600,
                            onMessage: function (api, data) {
                                if (data.mceAction === 'fileSelected') {
                                    callback(data.url, { title: data.title || '' });
                                    api.close();
                                }
                            }
                        });
                    },";
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
            $url = $urlAppend . "modules/admin/commondocs.php?embedtype=tinymce" . $append_module . $append_forum . "&docsfilter=";
            $filebrowser = "file_picker_callback: function (callback, value, meta) {
                        var url = '" . $url . "' + meta.filetype;
                        tinymce.activeEditor.windowManager.openUrl({
                            title: '" . js_escape($langResourceBrowser) . "',
                            url: url,
                            width: 800,
                            height: 600,
                            onMessage: function (api, data) {
                                if (data.mceAction === 'fileSelected') {
                                    callback(data.url, { title: data.title || '' });
                                    api.close();
                                }
                            }
                        });
                    },";
            $url = $urlAppend . "modules/admin/commondocs.php?embedtype=tinymce" . $append_module . $append_forum . "&docsfilter=";
        }
        $focus_init = ",
                init_instance_callback: function(editor) {
                    var rows = $(editor.getElement()).attr('rows') || 6;
                    editor.getContainer().style.height = ((rows * 30) + 100) + 'px';
                    var parent = $(editor.contentAreaContainer.parentElement);
                    (editorToggleSecondToolbar(editor))();
                    parent.find('tox-toolbar-grp, tox-statusbar').attr('style','border:0px');
                    if (typeof tinyMceCallback !== 'undefined') {
                        tinyMceCallback(editor);

                        let focusTimer;
                        let unfocusTimer;
                        let stopWritingTimer;
                        const activityDelay = 2000;
                        localStorage.setItem('isTinyMCEFocused', 'false');

                        // When editor gains focus
                        editor.on('focus', () => {
                            // Clear unfocus timer if active
                            clearTimeout(unfocusTimer);
                            // Set focused state
                            localStorage.setItem('isTinyMCEFocused', 'true');
                        });

                        // When editor loses focus
                        editor.on('blur', function () {
                            localStorage.setItem('isTinyMCEFocused', 'false');
                        });

                        // When user presses a key (writing)
                        editor.on('keydown', () => {
                            // Mark editor as focused
                            localStorage.setItem('isTinyMCEFocused', 'true');
                            // Reset the stop writing timer
                            clearTimeout(stopWritingTimer);
                            stopWritingTimer = setTimeout(() => {
                                // User stopped writing for 2 seconds
                                localStorage.setItem('isTinyMCEFocused', 'false');
                            }, activityDelay);
                        });

                    }";
        if ($onFocus) {
            $focus_init .= "parent.find('tox-toolbar-grp').hide();";
        }
        $focus_init .= "},";
        if ($onFocus) {
            $focus_init .= "
                statusbar: false,
                setup: function (editor) {
                    var toolbarGrp;
                    // editorAddButtonToggle(editor);
                    editor.on('focus', function () {
                        toolbarGrp.show();
                    });
                    editor.on('blur', function () {
                        toolbarGrp.hide();
                    });
                    editor.on('init', function() {
                        toolbarGrp = $(editor.contentAreaContainer.parentElement).find('tox-toolbar-grp');
                    });
                }";
        } else {
            $focus_init .= "
                setup: function (editor) {
                    // editorAddButtonToggle(editor);
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
        $latex_helper_categories = array(
            'Greek Letters' => $langLatexCatGreekLetters,
            'Operators' => $langLatexCatOperators,
            'Relations' => $langLatexCatRelations,
            'Arrows' => $langLatexCatArrows,
            'Delimiters' => $langLatexCatDelimiters,
            'Accents' => $langLatexCatAccents,
            'Functions' => $langLatexCatFunctions,
            'Math Structures' => $langLatexCatMathStructures,
            'Miscellaneous' => $langLatexCatMiscellaneous,
            'Chemical Symbols' => $langLatexCatChemicalSymbols
        );
        $head_content .= "
<style>
    body.tox-fullscreen {overflow: hidden !important;}
    body.tox-fullscreen header {z-index: 0 !important;}
    body.tox-fullscreen main {z-index: 99999 !important;position: relative !important;}
    .tox.tox-tinymce.tox-fullscreen {z-index: 100000 !important;}
    .tox-tinymce-aux, .tox-dialog-wrap {z-index: 100001 !important;}
    .tox-tinymce-aux div[id^='aria-controls_'] {position: relative;z-index: 100001;}
</style>
<script type='text/javascript'>
window.latexHelperLang = {
    title: '" . js_escape($langLatexDialogTitle) . "',
    latexInput: '" . js_escape($langLatexInput) . "',
    preview: '" . js_escape($langLatexPreview) . "',
    insert: '" . js_escape($langInsert) . "',
    cancel: '" . js_escape($langCancel) . "',
    categories: " . json_encode($latex_helper_categories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . "
};

function editorToggleSecondToolbar(editor) {
    return function() {
        var toolbar = $(editor.contentAreaContainer.parentElement).find('tox-toolbar-grp tox-toolbar').eq(1);
        toolbar.toggle();
    }
}

function editorAddButtonToggle (editor) {
    editor.addButton('toggle', {
        title: '".js_escape($langMore)."',
        classes: 'toggle',
        // image: '{$urlAppend}js/tinymce/skins/light/img/toggle.png',
        // onclick: editorToggleSecondToolbar(editor),
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
    
    license_key: 'gpl',
    selector: 'textarea.mceEditor',
    content_css: [
        '{$urlAppend}template/modern/css/bootstrap.min.css',
        '{$urlAppend}template/modern/css/font-awesome-6.4.0/css/all.css',
        '{$urlAppend}template/modern/css/default.css',
    ],
    content_style: 'body { margin: 8px; background: none !important; color: $tinymce_color_text;  }',
    font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 42pt',
    extended_valid_elements: 'span[*]',
    noneditable_noneditable_class: 'fa',
    fullscreen_native: true,
    language: '$language',
    cache_suffix: '?v=" . CACHE_SUFFIX . "',
    branding: false,
    font_family_formats:
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
//    plugins: 'fullscreen pagebreak save image link media eclmedia print contextmenu paste noneditable visualchars nonbreaking wordcount emoticons preview searchreplace table code textcolor colorpicker lists advlist charmap fontawesome latexhelper autosave$paste_plugin',
//    plugins: 'fullscreen pagebreak save image link media code lists eclmedia fontawesome latexhelper advlist charmap wordcount emoticons preview searchreplace visualchars nonbreaking autosave$paste_plugin',
    plugins: 'fullscreen pagebreak save image link media code lists advlist charmap wordcount emoticons preview searchreplace visualchars nonbreaking autosave eclmedia fontawesome latexhelper table',
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
    toolbar_mode: 'sliding',
    toolbar: ' bold italic underline alignleft aligncenter alignright alignjustify bullist numlist outdent indent table | link image media eclmedia | blocks fontfamily fontsize | strikethrough removeformat forecolor backcolor | emoticons fontawesome | superscript subscript latexhelper | $copy_paste  | undo redo searchreplace code preview restoredraft fullscreen'
    $focus_init
});
</script>";
    }

    $textarea_id = '';
    if (isset($options['id'])) {
        $textarea_id = "id=" . $options['id'];
    }
    
    if (!is_null($text)) {
        $textarea_text = q(str_replace('{', '&#123;', $text));
    } else {
        $textarea_text = '';
    }
    return "<textarea $textarea_id class='mceEditor' name='$name' rows='$rows' cols='$cols'>" . $textarea_text . "</textarea>\n";
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
    if (is_null($text)) {
        $text = '';
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
        $definition = ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-original-title="' . q($_SESSION['glossary'][$term]) . $term_notes . $term_url .'" data-bs-content="' . q($_SESSION['glossary'][$term]) . $term_notes . $term_url .'"';
    } else {
        $definition = '';
    }
    return '<a href="#"' . $definition . '>' . $matches[0] . '</a>';
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
    if (is_null($string)) {
        return '';
    }
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
    if (is_null($string)) {
        return '';
    }
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
function icon($name, $title = null, $link = null, $link_attrs = '', $with_title = false, $sr_only = false, $pressed = false) {

    if (isset($title)) {
        $title = q($title);
        $extra = "title data-bs-original-title='$title' data-bs-toggle='tooltip' data-bs-placement='bottom' aria-label='$title'";
    } else {
        $extra = '';
    }
    if (isset($title) && $with_title) {
        $img = $sr_only ? "<span class='fa $name' $extra></span><span class='visually-hidden'>$title</span>" : "<span class='fa $name' $extra></span> $title";
    } else {
        $img = "<span class='fa $name' $extra></span>";
    }
    if (isset($link)) {
        return "<a href='$link' $link_attrs aria-label='$title' aria-pressed='$pressed' role='button'>$img</a>";
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
    global $urlServer, $themeimg, $uid, $course_id, $is_editor, $langProfileImage;

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
    return "<img src='$imageurl$suffix' $class_attr alt='$langProfileImage:$username' $size_width>";
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
 * @brief get course type (e.g. units, wall etc)
 * @param $course_id
 * @return mixed
 */
function course_type($course_id) {

    $view_type = Database::get()->querySingle("SELECT view_type FROM course WHERE id = ?d", $course_id)->view_type;

    return $view_type;
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
function get_user_email_notification($user_id, $course_id = null): bool
{
    // check if user is active
    if (Database::get()->querySingle("SELECT expires_at < " . DBHelper::timeAfter() . " AS expired FROM user WHERE id = ?d", $user_id)->expired) {
        return false;
    }
    // check if course is active or not
    if (isset($course_id) and course_status($course_id) == COURSE_INACTIVE) {
        return false;
    }
    // check if course has expired  or not started
    if (isset($course_id) and (course_has_expired($course_id) or !course_has_started($course_id))) {
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
    if (!(file_exists($dirPath) or is_dir($dirPath))) {
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
            $link = "<a href='" . $license[$lic]['link'] . "$link_suffix' target='_blank' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='" . q($license[$lic]['title']) . "' aria-label='" . q($license[$lic]['title']) . "'>
                        <span class='" . $license[$lic]['image'] . "'></span>
                    </a>";
        } else if ($lic == 10) {
            $link = "<span data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='" . q($license[$lic]['title']) . "' class='" . $license[$lic]['image'] . "' aria-label='" . q($license[$lic]['title']) . "'></span>";
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

    $temporary_button_class = "";

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
        if (isset($option['modal-class'])) {
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

        if (count($options) > 1 && !(isset($option['options']) && ($level == 'primary' or $level == 'primary-label'))) {
            array_unshift($out_secondary,
                "<li$wrapped_class>$form_begin<a$confirm_extra  class='$text_class $modal_class $confirm_modal_class $temporary_button_class list-group-item d-flex justify-content-start align-items-start gap-2 py-3' " . $href .
                " $link_attrs>" .
                "<span class='fa $option[icon] settings-icons'></span> $title</a>$form_end</li>");
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
        $action_button .= "<button type='button' id='toolDropdown' class='btn submitAdminBtn action-bar-dropdown' data-bs-toggle='dropdown' aria-expanded='false' aria-label='$langListChoices'>
                                <span class='fa $secondary_icon'></span>
                                <span class='fa-solid fa-chevron-down ps-2'></span>
                                <span class='hidden-xs TextBold'>$secondary_title</span>
                                <span class='caret'></span><span class='hidden'></span>
                            </button>";
        $action_button .= " <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border contextual-menu-action-bar' aria-labelledby='toolDropdown'>
                                <ul class='list-group list-group-flush'>
                                    ".implode('', $out_secondary)."
                                </ul>
                            </div>";
    }

    $pageTitleActive = "";
    if (($action_button || $out) && $i!=0) {
        if(isset($course_code) and $course_code) {
            if (isset($_SESSION['mobile'])) {
                $titleHeader = '';
            } else {
                $titleHeader = (!empty($pageName) ? q($pageName) : $toolName);
            }
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
            if (isset($_SESSION['mobile'])) {
                $titleHeader = '';
            } else {
                $titleHeader = (!empty($pageName) ? q($pageName) : '');
            }

            return "<div class='col-12 d-md-flex justify-content-md-between align-items-lg-start my-4'>
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
    global $langConfirmDelete, $langCancel, $langDelete, $langConfig;
    $out_primary = $out_secondary = array();
    $primary_form_begin = $primary_form_end = $primary_icon_class = '';

    static $counter = 1;

    foreach (array_reverse($options) as $option) {
        $level = $option['level'] ?? 'secondary';
        if (isset($option['show']) and !$option['show']) {
            continue;
        }
        $class = isset($option['class']) ? ' ' . $option['class'] : '';
        $btn_class = isset($option['btn_class']) ? ' ' . $option['btn_class'] : ' submitAdminBtn';
        $link_attrs = isset($option['link-attrs']) ? ' ' . $option['link-attrs'] : '';

        $disabled = (isset($option['disabled']) && $option['disabled']) ? ' disabled' : '';

        $icon_class = "class='list-group-item d-flex justify-content-start align-items-start gap-2 py-3$class$disabled";
        if (isset($option['icon-class'])) {
            $icon_class .= " " . $option['icon-class'];
        }

        if (isset($option['confirm'])) {
            $title = q($option['confirm_title'] ?? $langConfirmDelete);
            $accept = $option['confirm_button'] ?? $langDelete;
            $form_begin = "<form class='form-action-button-mydropdowns mb-0' method=post action='$option[url]'>";
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
            $url = isset($option['url']) ? $option['url'] : '#';
        }

        if (isset($option['icon-extra'])) {
            $icon_class .= ' ' . $option['icon-extra'];
        }

        if ($level == 'primary-label') {
            array_unshift($out_primary, "<a href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon] space-after-icon$primary_icon_class'></span>" . q($option['title']) . "<span class='hidden'></span></a>");
        } elseif ($level == 'primary') {
            array_unshift($out_primary, "<a aria-label='" . q($option['title']) . "' data-bs-placement='bottom' data-bs-toggle='tooltip' title data-bs-original-title='" . q($option['title']) . "' href='$url' class='btn $btn_class$disabled' $link_attrs><span class='fa $option[icon]$primary_icon_class'></span><span class='hidden'></span></a>");
        } else {
            array_unshift($out_secondary, '<li>' . $form_begin . icon($option['icon'], $option['title'], $url, $icon_class.$link_attrs, true) . $form_end . '</li>');
        }
    }

    $primary_buttons = "";
    if (count($out_primary)) {
        $primary_buttons = implode('', $out_primary);
    }

    $action_button = "";
    $secondary_title = $secondary_menu_options['secondary_title'] ?? "<span class='hidden'></span>";

    if($fc){
        $secondary_icon = $secondary_menu_options['secondary_icon'] ?? "fa-wrench";
    }else{
        $secondary_icon = $secondary_menu_options['secondary_icon'] ?? "fa-solid fa-gear";
    }
    $secondary_btn_class = $secondary_menu_options['secondary_btn_class'] ?? "submitAdminBtn";

    // Instead of a popover menu, display list items directly
    if (count($out_secondary)) {
        $list_items = implode('', $out_secondary);
        $tmp_class_title = !empty($secondary_title) ? "<span class='hidden-xs'>$secondary_title</span>" : "";
        $action_button = "
            <button style='border-radius: 4px;' class='btn $secondary_btn_class action-button-dropdown' type='button' id='actionDropdown_$counter' data-bs-toggle='dropdown' aria-expanded='false' aria-label='$langConfig'>
                <span class='fa $secondary_icon'></span>
                $tmp_class_title
            </button>
            <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border contextual-menu-action-button' aria-labelledby='actionDropdown_$counter'>
                <ul class='list-group list-group-flush'>
                    $list_items
                </ul>
            </div>";
    }
    $counter++;

    return $primary_form_begin .
         "<div class='btn-group btn-group-sm btn-group-default dropstart gap-2' role='group' aria-label='...'>
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
                                                        " .(($openCoursesNum == 1)? $langCourse: $langCourses) . "
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

    $data = @unserialize($message, ["allowed_classes" => false]);
    // Message is simple string, not serialized array - just return it
    if ($data === false) {
        return $message;
    } elseif ($data === []) { // empty array - return empty string
        return '';
    } else {
        if (isset($data[$lang])) {
            return $data[$lang]; // return requested language if possible...
        } elseif ($lang != $language && isset($data[$language])) {
			return $data[$language]; // return default language (if different than requested) if possible...
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
    if ($course_status != COURSE_INACTIVE and course_has_started($course_id) and !course_has_expired($course_id)) { // No RSS feed for inactive courses
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
        course_has_expired($course_id) or
        !course_has_started($course_id) or
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
    if($connector->isEnabled()) {
        return "<div class='form-group'>
                  <strong>" . $langSFAConf . "</strong>
                  <div class='col-sm-12'>". secondfaApp::showUserProfile($_SESSION['uid']) . "</div>
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
function saveSecondFactorUserProfile() {

    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled()){
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
function showSecondFactorChallenge() {
    global $langSFAType;

    $connector = secondfaApp::getsecondfa();
    if($connector->isEnabled()) {
        $challenge = secondfaApp::showChallenge($_SESSION['uid']);
        if ($challenge!="") {
            return "<div class='col-sm-12 control-label-notes mb-2'>
                        $langSFAType
                    </div>
                    <div class='col-sm-4'>
                        $challenge
                    </div>";
        } else {
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
    if ($connector->isEnabled()) {
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

function get_platform_logo($size = 'normal', $position = 'header') {
    global $themeimg, $urlAppend, $course_id, $bg_color;

    require_once 'include/course_settings.php';

    if ($position == 'footer') {
        $footer_path = setting_get_print_image_disk_path(SETTING_COURSE_IMAGE_PRINT_FOOTER, $course_id);
        if (!$footer_path) {
            return '';
        }
        $logo_img = imageToBase64($footer_path);
        $image_align = setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_ALIGNMENT, $course_id);
        $image_align = ($image_align == 0) ? 'left' : (($image_align == 1) ? 'center' : 'right');
        $image_height = setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_WIDTH, $course_id);
        // for old courses
        if ($image_height > 50) {
            $image_height = 15;
        }
    } else {
        $header_path = setting_get_print_image_disk_path(SETTING_COURSE_IMAGE_PRINT_HEADER, $course_id);
        $image_height = setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_WIDTH, $course_id);
        // for old courses
        if ($image_height > 50) {
            $image_height = 20;
        }
        $image_align = setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_ALIGNMENT, $course_id);
        $image_align = ($image_align == 0) ? 'left' : (($image_align == 1) ? 'center' : 'right');
        if ($header_path) {
            $logo_img = imageToBase64($header_path);
        } else {
            $logo_img = $themeimg . '/eclass-new-logo.svg';
            $theme_id = get_config('theme_options_id');
            if ($theme_id) {
                $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
                $theme_options_styles = unserialize($theme_options->styles);
                $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
                if ($size == 'small' && isset($theme_options_styles['imageUploadSmall'])) {
                    $logo_img = "$urlThemeData/{$theme_options_styles['imageUploadSmall']}";
                } elseif (isset($theme_options_styles['imageUpload'])) {
                    $logo_img = "$urlThemeData/{$theme_options_styles['imageUpload']}";
                }
            }
        }
    }

    $logo = "<div style='clear: right; background-color: $bg_color; padding: 1rem; margin-bottom: 2rem; text-align: $image_align;'>
                <img style='height: {$image_height}mm;' src='$logo_img'>
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
 * Formats bytes into human-readable string (B, KB, MB, GB, TB).
 *
 * @param int $bytes Bytes to format
 * @param int $precision [optional] Decimal places (default: 2)
 * @return string Formatted size (e.g. "1.23 MB")
 */
function formatBytes(int $bytes, int $precision = 2): string {
    if ($bytes == 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = abs($bytes);
    $exp = (int) (log($bytes) / log(1024));
    $size = $bytes / pow(1024, $exp);

    return round($size, $precision) . ' ' . $units[$exp];
}


/**
 * @brief check if Safe Exam Browser is enabled for course
 * @return bool
 */
function CourseHasSafeExamBrowserEnabled(): bool
{
    global $course_id;

    if (get_config('ext_seb_enabled') == 1) {
        $q = Database::get()->queryArray("SELECT * FROM seb_courses");
        if (count($q) > 0) {
            $q1 = Database::get()->querySingle("SELECT * FROM seb_courses WHERE course_id = ?d", $course_id);
            if ($q1) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    } else {
        return false;
    }
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
 * Replace placeholders in a message with corresponding values.
 *
 * This function takes a message string containing placeholders in the format {name}
 * and replaces them with corresponding values provided in the substitution array.
 *
 * @param string $message The original message containing placeholders.
 * @param array $subst An associative array of substitutions, where keys are placeholder names
 *                     (without curly braces) and values are their replacements.
 * @return string The message with all placeholders replaced by their corresponding values.
 *
 * @example
 * $message = "Hello, {name}! You are {age} years old.";
 * $subst = ['name' => 'John', 'age' => 30];
 * $result = varmsg($message, $subst);
 * // Result: "Hello, John! You are 30 years old."
 */
function varmsg($message, $subst)
{
    $keys = $values = [];
    foreach ($subst as $key => $value) {
        $keys[] = '{' . $key . '}';
        $values[] = q($value);
    }
    return str_replace($keys, $values, $message);
}


/**
 * @brief display types of messages-popovers such as information message or warning message
 * @param $type
 * @param $message
 * @return string
 */
function form_popovers($type, $message): string {

    $html = '';
    switch ($type) {
        case 'help':
            $html .= "<button class='btn helpAdminBtn popovers-btn' data-bs-toggle='popover' data-bs-html='true' data-bs-content='{$message}'>
                        <i class='fa-solid fa-question-circle'></i>
                      </button>";
            break;
        case 'warning':
            $html .= "<button class='btn btn-warning popovers-btn' data-bs-toggle='popover' data-bs-html='true' data-bs-content='{$message}'>
                        <i class='fa-solid fa-triangle-exclamation'></i>
                      </button>";
            break;
        case 'success':
            $html .= "<button class='btn successAdminBtn popovers-btn' data-bs-toggle='popover' data-bs-html='true' data-bs-content='{$message}'>
                        <i class='fa-solid fa-circle-check'></i>
                      </button>";
            break;
        case 'danger':
            $html .= "<button class='btn deleteAdminBtn popovers-btn' data-bs-toggle='popover' data-bs-html='true' data-bs-content='{$message}'>
                        <i class='fa-solid fa-circle-xmark'></i>
                      </button>";
            break;
    }

    return $html;

}

/**
 * Retrieve a specific style value from the theme options.
 *
 * This function fetches the theme options from the database for the current theme
 * and retrieves the value of a specific style based on the provided style name.
 *
 * @param string $style_name The name of the style to retrieve.
 *
 * @global int $theme_id The ID of the current theme.
 *
 * @return mixed|null The value of the requested style if it exists, or null if not found.
 */
function get_style($style_name) {
    global $theme_id;

    $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);

    if ($theme_options) {
        $theme_options_styles = unserialize($theme_options->styles);
    }

    return $theme_options_styles[$style_name] ?? null;

}


function get_suppressed_words_data($action = 'words') {
    if ($action === 'version') {
        $sql = "SELECT MAX(created_at) AS last_update FROM suppressed_words";
        $result = Database::get()->querySingle($sql);

        if ($result && $result->last_update) {
            return $result->last_update;
        }
        return '2000-01-01 00:00:00';
    }

    if ($action === 'words') {
        $sql = "SELECT word FROM suppressed_words";
        $results = Database::get()->queryArray($sql);

        $words = [];
        if ($results) {
            foreach ($results as $row) {
                $words[] = $row->word;
            }
        }
        return $words;
    }

    return false;
}

/**
 * Return a set of strings from a message file for a specific language.
 *
 * This function returns for a given language code and a set of language variables
 * their values from the respective messages file.
 *
 * @param string $lang The language code.
 * @param array $strings The array of language variables.
 * @return array $arr The array with the values of the language variables for the given language.
 */
function load_lang_strings(string $lang, array $strings) : array {
    global $language_codes, $webDir;

    //add global variables to suppress warnings for undefined variables in messages.inc.php files
    $siteName = $GLOBALS['siteName'];
    $InstitutionUrl = $GLOBALS['InstitutionUrl'];
    $Institution = $GLOBALS['Institution'];

    $arr = array();
    
    if (isset($language_codes[$lang])) {
        //add common.inc.php to prevent warnings for variables that are undefined in messages.inc.php
        include "$webDir/lang/$lang/common.inc.php";
        
        $extra_messages = "config/{$language_codes[$lang]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }

        include "$webDir/lang/$lang/messages.inc.php";

        if (file_exists('config/config.php')) {
            if(get_config('show_always_collaboration') and get_config('show_collaboration')){
              include "$webDir/lang/$lang/messages_collaboration.inc.php";
            }
        }
        if ($extra_messages) {
            include $extra_messages;
        }
            
        foreach ($strings as $str) {
            if (isset($$str)) {
                $arr[$str] = $$str;
            }
        }
        
    }

    return $arr;
}
