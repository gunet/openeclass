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

define('ANALYTICS_BLOGPOSTS', 10);
define('ANALYTICS_BLOGCOMMENTS', 20);
define('ANALYTICS_COURSECOMMENTS', 21);
define('ANALYTICS_WALLCOMMENTS', 22);
define('ANALYTICS_EXERCISEGRADE',30);
define('ANALYTICS_ASSIGNMENTGRADE',40);
define('ANALYTICS_ASSIGNMENTDL',41);
define('ANALYTICS_FORUMACTIVITY',50);
define('ANALYTICS_WIKIACTIVITY',60);
define('ANALYTICS_FILEVIEW',70);
define('ANALYTICS_DAILYLOGINS',80);
define('ANALYTICS_HITS',81);
define('ANALYTICS_DURATION',82);
define('ANALYTICS_LPPERCENTAGE', 90);

class ElementTypes {
    const elements = array(
        ANALYTICS_BLOGPOSTS => array('title' => 'Δημοσιεύσεις στο Blog', 'link' => 'blogposts', 'icon' =>'fa fa-columns fa-fw'),
        ANALYTICS_BLOGCOMMENTS => array('title' => 'Σχόλια στο Blog', 'link' => 'blog-comments', 'icon' => 'fa fa-comment fa-fw'),
        ANALYTICS_COURSECOMMENTS => array('title' => 'Σχόλια στο μάθημα', 'link' => 'course-comments', 'icon' => 'fa fa-edit space-after-icon'),
        ANALYTICS_WALLCOMMENTS => array('title' => 'Σχόλια στον τοίχο', 'link' => 'wall-comments', 'icon' => 'fa fa-comment fa-fw'),
        ANALYTICS_EXERCISEGRADE => array('title' => 'Βαθμός σε άσκηση', 'link' => 'exercise-grade', 'icon' => 'fa fa-square-pen'),
        ANALYTICS_ASSIGNMENTGRADE => array('title' => 'Βαθμός σε εργασία', 'link' => 'assignment-grade', 'icon' => 'fa fa-flask space-after-icon'),
        ANALYTICS_ASSIGNMENTDL => array('title' => 'Υποβολή εργασίας πριν την προθεσμία', 'link' => 'assignment-dl', 'icon' => 'fa fa-flask space-after-icon'),
        ANALYTICS_FORUMACTIVITY => array('title' => 'Δημοσιεύσεις στο Forum', 'link' => 'forum-posts', 'icon' => 'fa fa-comments fa-fw'),
        ANALYTICS_WIKIACTIVITY => array('title' => 'Σελίδες στο Wiki', 'link' => 'wiki-pages', 'icon' => 'fa fa-won-sign fa-fw'),
        //ANALYTICS_FILEVIEW => array('title' => 'Προβολή περιεχομένου', 'link' => 'viewing-event', 'icon' => 'fa fa-columns fa-fw'),
        ANALYTICS_DAILYLOGINS => array('title' => 'Συνδέσεις στο μάθημα', 'link' => 'dailylogins', 'icon' =>'fa fa-area-chart fa-fw'),
        ANALYTICS_HITS => array('title' => 'Χτυπήματα', 'link' => 'hits', 'icon' =>'fa fa-mouse-pointer fa-fw'),
        ANALYTICS_DURATION => array('title' => 'Διάρκεια στο μάθημα', 'link' => 'duration', 'icon' =>'fa fa-clock fa-fw'),
        ANALYTICS_LPPERCENTAGE => array('title' => 'Γραμμή μάθησης', 'link' => 'lp-percentage', 'icon' => 'fa fa-ellipsis-h fa-fw')
    );
}
