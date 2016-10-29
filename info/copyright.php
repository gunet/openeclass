<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

require_once '../include/baseTheme.php';

$nameTools = $langCopyright;

$data['menuTypeID'] = isset($_SESSION['uid'])? 1: 0;
$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
          'url' => $urlServer,
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'button-class' => 'btn-default')), false);


view('info.copyright', $data);
