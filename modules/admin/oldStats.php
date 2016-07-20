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

/**
 * @file oldStats.php
 * @description Shows statistics older than twelve months that concern the number of visits
  on the platform for a time period. Information for old statistics is taken from table 'loginout_summary' where
  cummulative monthly data are stored.
 * 
 */
$require_admin = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');
$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');
load_js('bootstrap-datepicker');

$head_content .= "<script type='text/javascript'>
        var xMinVal = null;
        var xMaxVal = null;
        var xTicks = null;
        var interval = 30; //per month
        oldStatsChart = null;

        $(document).ready(function(){
            $('#user_date_start').datepicker({
            format: 'dd-mm-yyyy',
            pickerPosition: 'bottom-left',
            language: '$language',
            autoclose: true
        });

        $('#user_date_end').datepicker({
            format: 'dd-mm-yyyy',
            pickerPosition: 'bottom-left',
            language: '$language',
            autoclose: true
        });


        sdate = $('#user_date_start').datepicker('getDate');
        startdate = sdate.getFullYear()+'-'+(sdate.getMonth()+1)+'-'+sdate.getDate();        
        edate = $('#user_date_end').datepicker('getDate');
        enddate = edate.getFullYear()+'-'+(edate.getMonth()+1)+'-'+edate.getDate();                
        refresh_oldstats_plot(startdate, enddate);
    });

function refresh_oldstats_plot(startdate, enddate){
    xAxisTicksAdjust();
    $.getJSON('../usage/results.php',{t:'ols', s:startdate, e:enddate},function(data){
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
                    hits: '$langVisits'
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: '%m-%Y', values:xTicks, rotate:60}, label: '$langMonth', min: xMinVal}, y:{label:'$langVisits', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.9}},
            bindto: '#old_stats'
        };
        c3.generate(options);
    });
}

function xAxisTicksAdjust()
{
	var xmin = sdate;
	var xmax = edate;
	
        dayMilliseconds = 24*60*60*1000;
        diffInDays = (edate-sdate)/dayMilliseconds;
        xTicks = new Array();
	var tick = new Date(xmin);
        cur = xmin.getMonth();
        if(interval == 1){
            xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-'+tick.getDate();
            xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-'+xmax.getDate();
            if(tick.getDate() == 1){
                xTicks.push(xMinVal);
            }
            while(tick <= xmax)
            {
                    tick.setDate(tick.getDate() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(cur != tick.getMonth()){
                        xTicks.push(tickval);
                        cur = tick.getMonth();
                    }
            }    
        }
        else if(interval == 7){
            xminMonday = new Date(xmin.getTime() - xmin.getUTCDay()*dayMilliseconds);
            xMinVal = xminMonday.getFullYear()+'-'+(xminMonday.getMonth()+1)+'-'+xminMonday.getDate();
            xmaxMonday = new Date(xmax.getTime() + (7-xmax.getUTCDay())*dayMilliseconds);
            xMaxVal = xmaxMonday.getFullYear()+'-'+(xmaxMonday.getMonth()+1)+'-'+xmaxMonday.getDate();
            xTicks.push(xMinVal);
            tick = new Date(xminMonday);
            i = 1;
            while(tick <= xmaxMonday)
            {
                    tick.setDate(tick.getDate() + 7);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(i % 2 == 0){
                        xTicks.push(tickval);
                    }
                    i++;
                    
            } 
        }
        else if(interval == 30){
            xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-15';
            xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-15';
            xTicks.push(xMinVal);
            while(tick <= xmax)
            {
                    tick.setMonth(tick.getMonth() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-15';
                    xTicks.push(tickval);
            } 
        }
        else if(interval == 365){
            xMinVal = xmin.getFullYear()+'-06-30';
            xMaxVal = xmax.getFullYear()+'-06-30';
            xTicks.push(xMinVal);
            while(tick <= xmax)
            {
                    tick.setFullYear(tick.getFullYear() + 1);
                    tickval = tick.getFullYear()+'-06-30';
                    xTicks.push(tickval);
            }     
        }
}
"
        . ""
. " </script>";

$toolName = $langOldStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "../usage/index.php?t=a",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ),false);

//$min_w is the min date in 'loginout'. Statistics older than $min_w will be shown.
$query = "SELECT MIN(`when`) AS min_when FROM loginout";
foreach (Database::get()->queryArray($query) as $row) {
    $min_when = strtotime($row->min_when);
}
$min_w = date("d-m-Y", $min_when);

$endDate_obj = new DateTime();
$data['user_date_start'] = $endDate_obj->format('d-m-Y');
$startDate_obj = $endDate_obj->sub(new DateInterval('P2Y'));
$data['user_date_start'] = $startDate_obj->format('d-m-Y');

if (isset($_POST['user_date_start']) && isset($_POST['user_date_end'])) {
    $uds = DateTime::createFromFormat('d-m-Y', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d');
    $data['user_date_start'] = $uds->format('d-m-Y');

    $ude = DateTime::createFromFormat('d-m-Y', $_POST['user_date_end']);
    $u_date_end = $ude->format('Y-m-d');
    $data['user_date_end'] =  $ude->format('d-m-Y');
} else {
    $last_month = "P" . get_config('actions_expire_interval') . "M";
    $date_end = new DateTime();
    $date_end->sub(new DateInterval($last_month));
    $u_date_end = $date_end->format('Y-m-d');
    $data['user_date_end'] = $date_end->format('d-m-Y');
}

$data['menuTypeID'] = 3;
view('admin.other.stats.oldStats', $data);

