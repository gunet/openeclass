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

$head_content .= 
    "<script type='text/javascript'>
        console.log(stats+' '+interval);
        startdate = null;
        interval = 1;
        enddate = null;
        module = null;
        user = null;
        course = null;
        stats = 'a';
        console.log(stats+' '+interval);
    </script>";
$tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<ul class='list-group'><li class='list-group-item'><label id='depuser_title'>$langUsers</label></li><li class='list-group-item'><div id='depuser_stats'></div></li></ul>";
$tool_content .= "<ul class='list-group'><li class='list-group-item'><label id='depcourse_title'>$langCoursesHeader</label></li><li class='list-group-item'><div id='depcourse_stats'></div></li></ul>";
$tool_content .= "</div></div></div></div>";
require_once('form.php');
$tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<ul class='list-group'><li class='list-group-item'><label id='userlogins_title'>$langNbLogin</label></li><li class='list-group-item'><div id='userlogins_stats'></div></li></ul>";
$tool_content .= "</div></div></div></div>";