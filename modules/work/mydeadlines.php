<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

define('INDEX_START', 1);
require_once '../../include/baseTheme.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'main/perso.php';

$nameTools = $langMyPersoDeadlines;

$tool_content = "<div class='panel panel-default mt-4'><div class='panel-body'>{%ASSIGN_CONTENT%}</div></div>";

draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
