<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */


require_once 'template/template.inc.php';

$t = new Template('template/bootstrap');

$t->set_file('fh', 'theme.html');
$t->set_block('fh', 'mainBlock', 'main');
$t->set_block('mainBlock', 'leftNavBlock', 'delete');
$t->set_block('mainBlock', 'sideBarBlock', 'delete');
$t->set_block('mainBlock', 'LoggedInBlock', 'delete');
$t->set_block('mainBlock', 'LoggedOutBlock', 'delete');
$t->set_block('mainBlock', 'toolTitleBlock', 'delete');
$t->set_block('mainBlock', 'statusSwitchBlock', 'delete');
$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'delete');
$t->set_block('mainBlock', 'breadCrumbStartBlock', 'delete');
$t->set_block('mainBlock', 'breadCrumbEndBlock', 'delete');
$t->set_block('mainBlock', 'modalWindowBlock', 'delete');
$t->set_var('template_base', 'template/bootstrap');
$t->set_var('PAGE_TITLE', "$langEclass");
$t->set_var('TOOL_CONTENT', "
<div class='row'>
    <div class='col-md-12'>
        <div class='alert alert-warning'>
        $langIdxErrorPermissions
        </div>
    <div>
</div>");

$t->parse('main', 'mainBlock', false);
$t->pparse('Output', 'fh');
exit;