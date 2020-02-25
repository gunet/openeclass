<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
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

/**
 * @brief fetch course announcements
 * @global type $course_id
 * @global type $course_code
 * @global type $langNoAnnounce
 * @global type $urlAppend
 * @global type $dateFormatLong
 * @return string
 */
function course_announcements() {
    global $course_id, $course_code, $langNoAnnounce, $urlAppend, $dateFormatLong;

    if (visible_module(MODULE_ID_ANNOUNCE)) {
        $q = Database::get()->queryArray("SELECT title, `date`, id
                            FROM announcement
                            WHERE course_id = ?d AND
                                  visible = 1
                            ORDER BY `date` DESC LIMIT 5", $course_id);
        if ($q) { // if announcements exist
            $ann_content = '';
            foreach ($q as $ann) {
                $ann_url = $urlAppend . "modules/announcements/?course=$course_code&amp;an_id=" . $ann->id;
                $ann_date = claro_format_locale_date($dateFormatLong, strtotime($ann->date));
                $ann_content .= "<li class='list-item'>
                                    <span class='item-wholeline'><div class='text-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) ."</a></div>$ann_date</span>
                                </li>";
            }
            return $ann_content;
        }
    }
    return "<li class='list-item'><span class='item-wholeline'><div class='text-title not_visible'> - $langNoAnnounce - </div></span></li>";
}

/**
 * @brief display link for given unit resource
 * @param type $type
 * @param type $res_id
 * @return string
 */
function get_unit_resource_link($type, $res_id) {

    global $group_sql;

    $link = '';
    $group_sql = "true";
    switch ($type) {
        case 'doc':
            $doc = Database::get()->querySingle("SELECT path, filename FROM document WHERE id = ?d", $res_id);
            $link = "../document/" . public_file_path($doc->path, $doc->filename);
            break;
        case 'video':
            $link = Database::get()->querySingle("SELECT url FROM video WHERE id = ?d", $res_id)->url;
            $link = "../video/" . $link;
            break;
        case 'link':
            $link = Database::get()->querySingle("SELECT url FROM link WHERE id = ?d", $res_id)->url;
            break;
        case 'videolink':
            $link = Database::get()->querySingle("SELECT url FROM videolink WHERE id = ?d", $res_id)->url;
            break;
        case 'exercise':
            $link = "../exercise/index.html";
            break;
        case 'wiki':
            $link = "../wiki/index.html";
            break;
    }
    return $link;

}

/**
 * @brief display icon for given unit resource
 * @param type $type
 * @param type $res_id
 * @return string
 */
function get_unit_resource_icon($type, $res_id) {

    $icon = '';
    switch ($type) {
        case 'doc':
            $icon = 'fa-file';
            break;
        case 'video':
            $icon = 'fa-film';
            break;
        case 'link':
        case 'videolink':
            $icon = 'fa-link';
            break;
        case 'exercise':
            $icon = 'fa-pencil-square-o';
            break;
        case 'wiki':
            $icon = 'fa-wikipedia-w';
            break;
        case 'glossary':
            $icon = 'fa-list';
            break;
        case 'blog':
            $icon = 'fa-columns';
            break;
        case 'calendar':
            $icon = 'fa-calendar-o';
            break;
        case 'forum':
            $icon = 'fa-comments';
            break;
    }
    return "fa $icon";
}


/**
 * @brief get theme options
 * @return type
 */
function get_theme_options() {

    $data = [];

    $theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');
    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = 'theme_data/' . $theme_id;

        $styles_str = '';
        if (!empty($theme_options_styles['bgColor']) || !empty($theme_options_styles['bgImage'])) {
            $background_type = "";
            if (isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'stretch') {
                $background_type .= "background-size: 100% 100%;";
            } elseif(isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'fix') {
                $background_type .= "background-size: 100% 100%;background-attachment: fixed;";
            }
            $bg_image = isset($theme_options_styles['bgImage']) ? " url('$urlThemeData/$theme_options_styles[bgImage]')" : "";
            $bg_color = isset($theme_options_styles['bgColor']) ? $theme_options_styles['bgColor'] : "";
            $styles_str .= "body{background: $bg_color$bg_image;$background_type}";
        }
        $gradient_str = 'radial-gradient(closest-corner at 30% 60%, #009BCF, #025694)';
        if (!empty($theme_options_styles['loginJumbotronBgColor']) && !empty($theme_options_styles['loginJumbotronRadialBgColor'])) {
            $gradient_str = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[loginJumbotronRadialBgColor], $theme_options_styles[loginJumbotronBgColor])";
        }
        if (isset($theme_options_styles['loginImg'])) {
            $styles_str .= ".jumbotron.jumbotron-login { background-image: url('$urlThemeData/$theme_options_styles[loginImg]'), $gradient_str }";
        }
        if (isset($theme_options_styles['loginImgPlacement']) && $theme_options_styles['loginImgPlacement']=='full-width') {
            $styles_str .= ".jumbotron.jumbotron-login {  background-size: cover, cover; background-position: 0% 0%;}";
        }
        if (isset($theme_options_styles['fluidContainerWidth'])) {
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }
        if (isset($theme_options_styles['openeclassBanner'])){
             $styles_str .= "#openeclass-banner {display: none;}";
        }
        if (!empty($theme_options_styles['leftNavBgColor'])) {
            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';
            $styles_str .= "#background-cheat-leftnav, #bgr-cheat-header, #bgr-cheat-footer{background:$theme_options_styles[leftNavBgColor];} @media(max-width: 992px){#leftnav{background:$rgba_no_alpha;}}";
        }
        if (!empty($theme_options_styles['linkColor'])) {
            $styles_str .= "a {color: $theme_options_styles[linkColor];}";
        }
        if (!empty($theme_options_styles['linkHoverColor'])) {
            $styles_str .= "a:hover, a:focus {color: $theme_options_styles[linkHoverColor];}";
        }
        if (!empty($theme_options_styles['leftSubMenuFontColor'])) {
            $styles_str .= "#leftnav .panel a {color: $theme_options_styles[leftSubMenuFontColor];}";
        }
        if (!empty($theme_options_styles['leftSubMenuHoverBgColor'])) {
            $styles_str .= "#leftnav .panel a.list-group-item:hover{background: $theme_options_styles[leftSubMenuHoverBgColor];}";
        }
        if (!empty($theme_options_styles['leftSubMenuHoverFontColor'])) {
            $styles_str .= "#leftnav .panel a.list-group-item:hover{color: $theme_options_styles[leftSubMenuHoverFontColor];}";
        }
        if (!empty($theme_options_styles['leftMenuFontColor'])) {
            $styles_str .= "#leftnav .panel a.parent-menu{color: $theme_options_styles[leftMenuFontColor];}";
        }
        if (!empty($theme_options_styles['leftMenuBgColor'])) {
            $styles_str .= "#leftnav .panel a.parent-menu{background: $theme_options_styles[leftMenuBgColor];}";
        }
        if (!empty($theme_options_styles['leftMenuHoverFontColor'])) {
            $styles_str .= "#leftnav .panel .panel-heading:hover {color: $theme_options_styles[leftMenuHoverFontColor];}";
        }
        if (!empty($theme_options_styles['leftMenuSelectedFontColor'])) {
            $styles_str .= "#leftnav .panel a.parent-menu:not(.collapsed){color: $theme_options_styles[leftMenuSelectedFontColor];}";
        }
        if (isset($theme_options_styles['imageUpload'])) {
            $data['logo_img'] = "$urlThemeData/$theme_options_styles[imageUpload]";
        }
        if (isset($theme_options_styles['imageUploadSmall'])) {
            $data['logo_img_small'] = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
        }
        $data['styles'] = $styles_str;

        return $data;
    }
}
