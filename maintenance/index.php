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

define('MAINTENANCE_PAGE', true);
include '../include/baseTheme.php';

$maintenance_theme = get_config('maintenance_theme');
$langcode = $session->language;
$maintenance_text = get_config('maintenance_text_' . $langcode);

if (get_config('maintenance')==1) {
    include_once('theme_'.$maintenance_theme.'/index.php');
} else {
    redirect_to_home_page();
}
