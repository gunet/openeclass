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

$require_admin = true;
$require_help = true;
$helpTopic = 'external_tools';
$helpSubTopic = 'solr';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/solrapp.php';

$app = ExtAppManager::getApp('solr');

$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($_POST['submit'] == 'clear') {
        foreach ($app->getParams() as $param) {
            $param->setValue('');
            $param->persistValue();
        }
        Session::flash('message', $langFileUpdatedSuccess);
        Session::flash('alert-class', 'alert-info');
    } else {
        $result = $app->storeParams();
        if ($result) {
            Session::flash('message', $result);
            Session::flash('alert-class', 'alert-danger');
        } else {
            Session::flash('message', $langFileUpdatedSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }
    redirect_to_home_page($app->getConfigUrl());
}

$data = [];
$data['appParams'] = $app->getParams();
view('admin.other.extapps.solrmoduleconf', $data);