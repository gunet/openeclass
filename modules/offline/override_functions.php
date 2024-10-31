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
 * Function lessonToolsMenu
 *
 * Creates a multidimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 *
 * @param bool $rich Whether to include rich text notifications in title
 *
 * @return array
 */
function lessonToolsMenu_offline($rich=true, $urlAppend) {
    global $langExternalLinks, $offline_course_modules, $langCourseTools;

    $sideMenuGroup = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'none';
    $mids = array();

    $tools_sections =
        array(array('type' => 'Public',
                    'title' => $langCourseTools,
                    'class' => 'active'));

    foreach ($tools_sections as $section) {

        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $sideMenuID = array();
        $arrMenuType = array('type' => 'text',
                            'text' => $section['title'],
                            'class' => $section['class']);
        $sideMenuSubGroup[] = $arrMenuType;

        foreach ($offline_course_modules as $key => $value) {
            $mids[$key] = $offline_course_modules[$key]['link'];
        }
        // sort array according to title (respect locale)
        setlocale(LC_COLLATE, $GLOBALS['langLocale']);
        $offline_modules = $offline_course_modules;
        usort($offline_modules, function ($a, $b) {
            return strcoll($a['title'], $b['title']);
        });
        foreach ($offline_modules as $m) {
            $mid = array_search($m['link'], $mids);
            if (!visible_module($mid)) {
                continue;
            }
            array_push($sideMenuText, q($m['title']));
            array_push($sideMenuLink, q($urlAppend . 'modules/' . $m['link'] . '.html'));
            array_push($sideMenuImg, $m['image']);
            array_push($sideMenuID, $m);

        }
        $sideMenuSubGroup[] = $sideMenuText;
        $sideMenuSubGroup[] = $sideMenuLink;
        $sideMenuSubGroup[] = $sideMenuImg;
        $sideMenuSubGroup[] = $sideMenuID;
        $sideMenuGroup[] = $sideMenuSubGroup;
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
        $sideMenuSubGroup[] = $arrMenuType;

        foreach ($result2 as $ex_link) {
            $sideMenuText[] = q($ex_link->title);
            $sideMenuLink[] = q($ex_link->url);
            $sideMenuImg[] = 'fa-external-link';
        }

        $sideMenuSubGroup[] = $sideMenuText;
        $sideMenuSubGroup[] = $sideMenuLink;
        $sideMenuSubGroup[] = $sideMenuImg;
        $sideMenuGroup[] = $sideMenuSubGroup;
    }
    return $sideMenuGroup;
}

/**
 * Used in documents path navigation bar
 * @param type $path
 * @return type
 */
function make_clickable_path($path) {
    global $langRoot, $group_sql;

    $out = '';
    $depth = count(explode('/', $path));
    $i = 1;
    foreach (explode('/', $path) as $component) {
        $dotsprefix = "";
        for ($j = 1; $j <= $depth-$i; $j++) {
            $dotsprefix .= "../";
        }

        if (empty($component)) {
            $out = "<a href='" . $dotsprefix . "document.html'>$langRoot</a>";
        } else {
            $row = Database::get()->querySingle("SELECT filename FROM document WHERE path LIKE '%/$component' AND $group_sql");
            $dirname = $row->filename;
            $out .= " &raquo; <a href='" . $dotsprefix . $dirname . ".html'>" . q($dirname) . "</a>";
        }
        $i++;
    }
    return $out;
}

function getLinksOfCategory($cat_id, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin) {

    $uncatresults = array();

    $vis_q = ($is_editor) ? '' : "AND visible = 1";
    if ($cat_id > 0) {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv AND course_id = ?d AND category = ?d $vis_q $order", $course_id, $cat_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl AND course_id = ?d AND category = ?d $vis_q $order", $course_id, $cat_id);
    } else {
        $results['video'] = Database::get()->queryArray("SELECT * FROM video $filterv AND course_id = ?d AND (category IS NULL OR category = 0) $vis_q $order", $course_id);
        $results['videolink'] = Database::get()->queryArray("SELECT * FROM videolink $filterl AND course_id = ?d AND (category IS NULL OR category = 0) $vis_q $order", $course_id);
    }

    foreach ($results as $table => $result) {
        foreach ($result as $myrow) {
            $myrow->course_id = $course_id;
            $resultObj = new stdClass();
            $resultObj->myrow = $myrow;
            $resultObj->table = $table;

            if (resource_access($myrow->visible, $myrow->public) || $is_editor) {
                switch ($table) {
                    case 'video':
                        $vObj = MediaResourceFactory::initFromVideo($myrow);
                        if ($is_in_tinymce && !$compatiblePlugin) { // use Access/DL URL for non-modable tinymce plugins
                            $vObj->setPlayURL($vObj->getAccessURL());
                        }
                        $resultObj->vObj = $vObj;
                        $resultObj->link_href = "<a href='video/" . $vObj->getUrl() . "'>" . $vObj->getTitle() . "</a>";
                        break;
                    case "videolink":
                        $resultObj->vObj = $vObj = MediaResourceFactory::initFromVideoLink($myrow);
                        $resultObj->link_href = "<a href='" . $vObj->getUrl() . "'>" . $vObj->getTitle() . "</a>";
                        break;
                    default:
                        exit;
                }

                $resultObj->row_class = (!$myrow->visible) ? 'not_visible' : 'visible' ;
                $resultObj->extradescription = '';

                if (!$is_in_tinymce and ( !empty($myrow->creator) or ! empty($myrow->publisher))) {
                    $resultObj->extradescription .= '<br><small>';
                    if ($myrow->creator == $myrow->publisher) {
                        $resultObj->extradescription .= $GLOBALS['langCreator'] . ": " . q($myrow->creator);
                    } else {
                        $emit = false;
                        if (!empty($myrow->creator)) {
                            $resultObj->extradescription .= $GLOBALS['langCreator'] . ": " . q($myrow->creator);
                            $emit = true;
                        }
                        if (!empty($myrow->publisher)) {
                            $resultObj->extradescription .= ($emit ? ', ' : '') . $GLOBALS['langpublisher'] . ": " . q($myrow->publisher);
                        }
                    }
                    $resultObj->extradescription .= "</small>";
                }
                $uncatresults[] = $resultObj;
            }
        }
    }

    return ($uncatresults);
}
