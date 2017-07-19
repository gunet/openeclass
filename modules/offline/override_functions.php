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

/**
 * Function lessonToolsMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 *
 * @param bool $rich Whether to include rich text notifications in title
 *
 * @return array
 */
function lessonToolsMenu_offline($rich=true, $urlAppend) {
    global $uid, $is_editor, $is_course_admin, $courses,
           $course_code, $langAdministrationTools, $langExternalLinks,
           $modules, $admin_modules, $status, $course_id;

    $sideMenuGroup = array();
    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();
    $sideMenuID = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'none';

    $tools_sections =
        array(array('type' => 'Public',
            'title' => $GLOBALS['langCourseOptions'],
            'iconext' => '_on.png',
            'class' => 'active'));

    foreach ($tools_sections as $section) {
        $result = getToolsArray($section['type']);

        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $sideMenuID = array();
        $mail_status = '';
        $arrMenuType = array('type' => 'text',
            'text' => $section['title'],
            'class' => $section['class']);
        array_push($sideMenuSubGroup, $arrMenuType);

        setlocale(LC_COLLATE, $GLOBALS['langLocale']);
        usort($result, function ($a, $b) {
            global $modules;
            return strcoll($modules[$a->module_id]['title'], $modules[$b->module_id]['title']);
        });

        // check if we have define mail address and want to receive messages
        if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
            $mail_status = '&nbsp;' . icon('fa-exclamation-triangle');
        }

        foreach ($result as $toolsRow) {
            $mid = $toolsRow->module_id;

            // hide groups for unregistered users
            if ($mid == MODULE_ID_GROUPS and !$courses[$course_code]) {
                continue;
            }

            // hide teleconference when no BBB or OM servers are enabled
            if ($mid == MODULE_ID_TC and !is_configured_tc_server()) {
                continue;
            }

            // if we are in dropbox or announcements add (if needed) mail address status
            if ($rich and ($mid == MODULE_ID_MESSAGE or $mid == MODULE_ID_ANNOUNCE)) {
                if ($mid == MODULE_ID_MESSAGE) {
                    $mbox = new Mailbox($uid, course_code_to_id($course_code));
                    $new_msgs = $mbox->unreadMsgsNumber();
                    if ($new_msgs != 0) {
                        array_push($sideMenuText, '<b>' . q($modules[$mid]['title']) .
                            " $mail_status<span class='badge pull-right'>$new_msgs</span></b>");
                    } else {
                        array_push($sideMenuText, q($modules[$mid]['title']).' '.$mail_status);
                    }
                } else {
                    array_push($sideMenuText, q($modules[$mid]['title']).' '.$mail_status);
                }
            } elseif ($rich and $mid == MODULE_ID_DOCS and ($new_docs = get_new_document_count($course_id))) {
                array_push($sideMenuText, '<b>' . q($modules[$mid]['title']) .
                    "<span class='badge pull-right'>$new_docs</span></b>");
            } else {
                array_push($sideMenuText, q($modules[$mid]['title']));
            }

            array_push($sideMenuLink, q($urlAppend . 'modules/' . $modules[$mid]['link'] .
                '/index.html'));
            array_push($sideMenuImg, $modules[$mid]['image'] . $section['iconext']);
            array_push($sideMenuID, $mid);
        }
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuSubGroup, $sideMenuID);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }
    $result2 = getExternalLinks();
    if ($result2) { // display external link (if any)
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $arrMenuType = array('type' => 'text',
            'text' => $langExternalLinks,
            'class' => 'external');
        array_push($sideMenuSubGroup, $arrMenuType);

        foreach ($result2 as $ex_link) {
            array_push($sideMenuText, q($ex_link->title));
            array_push($sideMenuLink, q($ex_link->url));
            array_push($sideMenuImg, 'fa-external-link');
        }

        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }
    return $sideMenuGroup;
}

/**
 * Used in documents path navigation bar
 * @global type $langRoot
 * @global type $base_url
 * @global type $group_sql
 * @param type $path
 * @return type
 */
function make_clickable_path($path) {
    global $langRoot, $base_url, $group_sql;

    $cur = $out = '';
    foreach (explode('/', $path) as $component) {
        if (empty($component)) {
            $out = "<a href='{$base_url}openDir=/'>$langRoot</a>";
        } else {
            $cur .= rawurlencode("/$component");
            $row = Database::get()->querySingle("SELECT filename FROM document
                                        WHERE path LIKE '%/$component' AND $group_sql");
            $dirname = $row->filename;
            $out .= " &raquo; <a href='{$base_url}openDir=$cur'>".q($dirname)."</a>";
        }
    }
    return $out;
}

/**
 * @brief Link for sortable table headings
 * @global type $sort
 * @global type $reverse
 * @global type $curDirPath
 * @global type $base_url
 * @global type $themeimg
 * @global type $langUp
 * @global type $langDown
 * @param type $label
 * @param type $this_sort
 * @return type
 */
function headlink($label, $this_sort) {
    global $sort, $reverse, $curDirPath, $base_url, $themeimg, $langUp, $langDown;

    if (empty($curDirPath)) {
        $path = '/';
    } else {
        $path = $curDirPath;
    }
    if ($sort == $this_sort) {
        $this_reverse = !$reverse;
        $indicator = " <img src='$themeimg/arrow_" .
            ($reverse ? 'up' : 'down') . ".png' alt='" .
            ($reverse ? $langUp : $langDown) . "'>";
    } else {
        $this_reverse = $reverse;
        $indicator = '';
    }
    return '<a href="' . $base_url . 'openDir=' . $path .
        '&amp;sort=' . $this_sort . ($this_reverse ? '&amp;rev=1' : '') .
        '">' . $label . $indicator . '</a>';
}