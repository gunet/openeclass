<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$require_admin = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";

load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function() {
        sdate = new Date(new Date().setFullYear(new Date().getFullYear() - 1)) /* one year back */
        startdate = sdate.getFullYear()+'-'+(sdate.getMonth()+1)+'-'+sdate.getDate();
        edate = new Date(); /* current date */
        enddate = edate.getFullYear()+'-'+(edate.getMonth()+1)+'-'+edate.getDate();
        refresh_oldstats_plot(startdate, enddate);
    });

function refresh_oldstats_plot(startdate, enddate) {
        
    $.getJSON('../usage/results.php',{ t:'ols', s:startdate, e:enddate },function(data) {
        var options = {
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                axes: {
                    hits: 'y'
                },
                types:{
                    hits: 'bar'
                },
                names:{
                    hits: '" . js_escape($langNbLogin) . "'
                }
            },
            axis:{
                x: {
                    type:'timeseries',
                    tick: {
                        format: '%m-%Y',
                        rotate: 60
                    }
                },
                y: {
                    min: 0,
                    padding:{
                        top:0, bottom:0
                    }
                }
            },
            bar:{
                width:{
                    ratio: 0.6
                }
            },
            bindto: '#old_stats'
        };
        c3.generate(options);
    });
}
</script>";

$toolName = $langUsageVisits;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "../usage/index.php?t=a",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ),false);

/****   C3 plot   ****/
$tool_content .= "<div class='row plotscontainer'>";
$tool_content .= "<div id='userlogins_container' class='col-lg-12'>";
$tool_content .= plot_placeholder("old_stats", $langLoginUser);
$tool_content .= "</div></div>";

$tool_content .= "<div class='table-responsive'>
                <table class='table-default'>
                <tr>
                    <th class='list-header' colspan='2'><strong>$langLoginUser $langUsersOf</strong></th>                    
                </tr>";
// recent logins
$interval = [ $langToday => 1, $langLast7Days => 7, $langLast30Days => 30 ];
foreach ($interval as $legend => $data) {
    $loginUsers = Database::get()->querySingle("SELECT COUNT(*) AS cnt 
                        FROM loginout
                        WHERE action='LOGIN'
                        AND `when` >= DATE_SUB(DATE(NOW()), INTERVAL $data DAY)");
    $tool_content .= "<tr>
                        <td>$legend</td>
                        <td class='text-right col-sm-1'>" . $loginUsers->cnt . "</td>
                    </tr>";
}

// old logins
$user_logins_data = get_user_login_archives();

foreach ($user_logins_data as $data) {
    $formatted_data = date_format(date_create($data[0]), "n / Y");
    $tool_content .= "<tr>";
    $tool_content .= "<td>$formatted_data</td>";
    $tool_content .= "<td class='text-right'>$data[1]</td>";
    $tool_content .= "</tr>";
}

$tool_content .= "</table></div>";

draw($tool_content, 3, null, $head_content);
