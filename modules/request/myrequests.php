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

$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/request/functions.php';

$toolName = $langMyRequests;
$backUrl = $urlAppend . 'main/portfolio.php';

load_js('datatables');
load_js('datatables_bootstrap');
$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
      'url' => $backUrl,
      'icon' => 'fa-reply',
      'level' => 'primary' ]], false);

$data['listUrl'] = $urlAppend . 'modules/request/mylist.php';

view('modules.request.my_requests', $data);

