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

$require_admin = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.7.20/c3.css' />";

load_js('d3/d3.min.js');
load_js('c3-0.7.20/c3.min.js');

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

$toolName = $langAdmin;
$pageName = "$langLoginUser $langUsersOf";

$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

// recent logins
/*$interval = [ $langToday => 1, $langLast7Days => 7];
foreach ($interval as $legend => $value) {
    $loginUsers = Database::get()->querySingle("SELECT COUNT(*) AS cnt
                        FROM loginout
                        WHERE action='LOGIN'
                        AND `when` >= DATE_SUB(DATE(NOW()), INTERVAL $value DAY)");
    $data['recent_logins'][] = [ $legend, $loginUsers->cnt ];
}
*/
// monthly logins
$data['user_logins_data'] = $user_logins_data = get_user_login_archives();

view('admin.other.stats.login_stats', $data);
