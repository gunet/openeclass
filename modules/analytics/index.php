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

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'learning_analytics';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

//Name of the page
$toolName = $langLearningAnalytics;

// Validate data and insert a new record to the DB
if (isset($_POST['insert_analytics'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    if($_POST['start_date'] and $_POST['end_date']) {
        $start_date = date_format(date_create_from_format('d-m-Y', $_POST['start_date']), 'Y-m-d');
        $end_date = date_format(date_create_from_format('d-m-Y', $_POST['end_date']), 'Y-m-d');

        $v->rule('dateAfter', 'end_date', $start_date);
    } else if ($_POST['start_date'] or $_POST['end_date']){
        $v->rule('required', array('start_date', 'end_date'));
    } else {
        $start_date = null;
        $end_date = null;
    }

    $v->labels(array(
        'title' => $langRequiredTitle
    ));

    if($v->validate()) {
        $created = date('Y-m-d H:i:s');

        $analytics_id = insert_analytics($_POST['title'], $_POST['description'], $_POST['active'], $_POST['periodType'], $start_date, $end_date, $created);

        Session::flash('message',$langAnalyticsInsertSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=courseStatistics");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&amp;new=1");
    }
// Validate data and update to DB
} else if (isset($_POST['update_analytics'])) {
    $analytics_id = $_POST['analytics_id'];
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    if($_POST['start_date'] and $_POST['end_date']) {
        $start_date = date_format(date_create_from_format('d-m-Y', $_POST['start_date']), 'Y-m-d');
        $end_date = date_format(date_create_from_format('d-m-Y', $_POST['end_date']), 'Y-m-d');

        $v->rule('dateAfter', 'end_date', $start_date);
    } else if ($_POST['start_date'] or $_POST['end_date']){
        $v->rule('required', array('start_date', 'end_date'));
    } else {
        $start_date = null;
        $end_date = null;
    }

    $v->labels(array(
        'title' => $langRequiredTitle
    ));

    if($v->validate()) {
        update_analytics($analytics_id, $_POST['title'], $_POST['description'], $_POST['active'], $_POST['periodType'], $start_date, $end_date);

        Session::flash('message',$langAnalyticsUpdateSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=courseStatistics");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&amp;analytics_id=$analytics_id&amp;edit_analytics=1");
    }
//Go to edit analytics form
} else if (isset($_REQUEST['edit_analytics'])) {
    if (isset($_REQUEST['analytics_id'])) {
        $analytics_id = $_REQUEST['analytics_id'];
        $action_bar = action_bar(array(
            array('title' => $langBack,
                    'url' =>"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=courseStatistics",
                    'icon' => 'fa fa-reply',
                    'level' => 'primary')
            ));
        $tool_content .= $action_bar;
        edit_analytics_settings ($analytics_id);
    } else {
        $action_bar = action_bar(array(
            array('title' => $langBack,
                    'url' =>"$_SERVER[SCRIPT_NAME]?course=$course_code",
                    'icon' => 'fa fa-reply',
                    'level' => 'primary')
            ));
        $tool_content .= $action_bar;
        edit_analytics_settings ();
    }
// Delete analytics
}  else if (isset($_REQUEST['delete_analytics'])) {
    $analytics_id = $_REQUEST['analytics_id'];
    delete_analytics($analytics_id);
    Session::flash('message', $langAnalyticsDeleteSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/analytics/index.php?course=$course_code");
} else if  (isset($_REQUEST['activate'])) {
    if ($_REQUEST['activate'] == 0) { // Deactivate analytics
        $analytics_id = $_REQUEST['analytics_id'];
        switch_activation($analytics_id, 0);
        Session::flash('message', $langAnalyticsDeactivated);
        Session::flash('alert-class', 'alert-success');
    } else if ($_REQUEST['activate'] == 1) { // Activate analytics
        $analytics_id = $_REQUEST['analytics_id'];
        switch_activation($analytics_id, 1);
        Session::flash('message', $langAnalyticsActivated);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page("modules/analytics/index.php?course=$course_code");
// Go to edit analytics elememt forms,
}  else if (isset($_REQUEST['delete_analytics_element'])) {
    $analytics_id = $_REQUEST['analytics_id'];
    $analytics_element_id = $_REQUEST['analytics_element_id'];
    delete_analytics_element($analytics_id, $analytics_element_id);
    Session::flash('message',$langAnalyticsElementDeleteSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/analytics/index.php?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=showElements");
} else if (isset($_REQUEST['edit_analytics_element'])){
    $analytics_id = $_REQUEST['analytics_id'];
    $action_bar = action_bar(array(
        array('title' => $langBack,
                'url' =>"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=showElements",
                'icon' => 'fa fa-reply',
                'level' => 'primary')
        ));
    $tool_content .= $action_bar;
    if (isset($_REQUEST['analytics_element_id'])) {
        // Update analytics element
        $analytics_element_id = $_REQUEST['analytics_element_id'];
        analytics_element_form($analytics_id, null, $analytics_element_id);
    } else if (isset($_REQUEST['elementType'])) {
        $type = $_REQUEST['elementType'];

        // Insert new analytics element
        analytics_element_form($analytics_id, $type);
    }
} else if (isset($_REQUEST['update_analytics_element'])) {
    // Validate data and update to DB
    $analytics_element_id = $_POST['analytics_element_id'];
    $analytics_id = $_POST['analytics_id'];
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('min_value','max_value','lower_threshold', 'upper_threshold','weight'));

    $v->labels(array(
        'min_value' => $langAnalyticsMinValueRequired,
        'max_value' => $langAnalyticsMaxValueRequired,
        'lower_threshold' => $langAnalyticsLowThresholdRequired,
        'upper_threshold' => $langAnalyticsHighThresholdRequired,
        'weight' => $langAnalyticsWeightRequired
    ));

    if($v->validate()) {
        update_analytics_element($_POST['analytics_id'], $_POST['analytics_element_id'], $_POST['resource'], $_POST['module_id'], $_POST['min_value'], $_POST['max_value'], $_POST['lower_threshold'], $_POST['upper_threshold'], $_POST['weight']);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&analytics_id=$analytics_id&mode=showElements");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/analytics/index.php?course=$course_code&analytics_id=$analytics_id&mode=showElements");
    }
} else if (isset($_REQUEST['insert_analytics_element'])) {
    $analytics_element_id = $_POST['analytics_element_id'];
    $analytics_id = $_POST['analytics_id'];
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('min_value','max_value','lower_threshold', 'upper_threshold','weight'));

    $v->labels(array(
        'min_value' => $langAnalyticsMinValueRequired,
        'max_value' => $langAnalyticsMaxValueRequired,
        'lower_threshold' => $langAnalyticsLowThresholdRequired,
        'upper_threshold' => $langAnalyticsHighThresholdRequired,
        'weight' => $langAnalyticsWeightRequired
    ));

    if($v->validate()) {
        insert_analytics_element($_POST['analytics_id'], $_POST['resource'], $_POST['module_id'], $_POST['min_value'], $_POST['max_value'], $_POST['lower_threshold'], $_POST['upper_threshold'], $_POST['weight'], $_POST['analytics_element_id']);

        Session::flash('message',$langAnalyticsElementInsertSuccess);
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/analytics/index.php?course=$course_code&analytics_id=$analytics_id&mode=showElements");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());

        redirect_to_home_page("modules/analytics/index.php?course=$course_code&analytics_id=$analytics_id&mode=showElements");
    }
} else if (isset($_REQUEST['analytics_id']) and isset($_REQUEST['mode'])) {
    $analytics_id = $_REQUEST['analytics_id'];
    $mode = $_REQUEST['mode'];

    $analyticsPeriod = get_analytics_period($analytics_id);
    $period = $analyticsPeriod->periodType;
    $counter = 0;
    $dates = array();

    $dates[0]['start'] = $analyticsPeriod->start_date;
    $counterEnd = $analyticsPeriod->end_date;

    while (++$counter) {
        switch ($period) {
            case 0:
                $dates[$counter]['start'] = date('Y-m-d', strtotime($dates[$counter-1]['start']. ' next day'));
                $dates[$counter-1]['end'] = date('Y-m-d', strtotime($dates[$counter]['start']. ' previous day'));
                break;
            case 1:
                $dates[$counter]['start'] = date('Y-m-d', strtotime($dates[$counter-1]['start']. ' next monday'));
                $dates[$counter-1]['end'] = date('Y-m-d', strtotime($dates[$counter]['start']. ' previous day'));
                break;
            case 2:
                $dates[$counter]['start'] = date('Y-m-d', strtotime(date('Y-m-t', strtotime($dates[$counter-1]['start'])). ' next day'));
                $dates[$counter-1]['end'] = date('Y-m-d', strtotime($dates[$counter]['start']. ' previous day'));
                break;
            case 3:
                $dates[$counter]['start'] = '9999-01-01';
                $dates[$counter-1]['end'] = $counterEnd;
        }

        if($dates[$counter]['start'] > $counterEnd) {
            unset($dates[$counter]);
            $dates[$counter-1]['end'] = $counterEnd;
            break;
        }
    }

    if (isset($_REQUEST['period'])) {
        $period = $_REQUEST['period'];
    } else {
        $period = 0;
    }

    if(array_key_exists ($period-1, $dates)) {
        $previous = $period-1;
    } else {
        $previous = null;
    }

    if(array_key_exists ($period+1, $dates)) {
        $next = $period+1;
    } else {
        $next = null;
    }



    if ($mode == 'perUser') {
        if(isset($_REQUEST['user_id'])) {
            $user_id = $_REQUEST['user_id'];
            $action_bar = action_bar(
                array(
                    array('title' => $langBack,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                )
            );
            $tool_content .= $action_bar;
            display_user_info($user_id);
            display_analytics_user($user_id, $analytics_id, $dates[$period]['start'], $dates[$period]['end'], $previous, $next);
        } else {
            $orderby = '';
            if (isset($_REQUEST['orderby'])) {
                $orderby = $_REQUEST['orderby'];
            }

            $reverse = '';
            if(isset($_REQUEST['reverse'])) {
                $reverse = $_REQUEST['reverse'];
            }

            $download = false;
            if(isset($_REQUEST['download'])) {
                $download = true;
            }

            $reverse_op = 'true';
            if ($reverse=='true') {
                $reverse_op = 'false';
            }

            $action_bar = action_bar(
                array(
                    array('title' => $langAnalyticsTotalAnalytics,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=courseStatistics",
                        'icon' => 'fa-bar-chart',
                        'level' => 'primary-label'),
                    array('title' => $langExport,
                        'url' => '?course='.$course_code.'&amp;analytics_id='.$analytics_id.'&amp;mode=perUser&amp;period='.$period.'&amp;orderby='.$orderby.'&amp;reverse='.$reverse_op.'&amp;download=true',
                        'icon' => 'fa-envelope',
                        'level' => 'primary-label')

                )
            );
            $tool_content .= $action_bar;
            display_analytics_peruser($analytics_id, $dates[$period]['start'], $dates[$period]['end'], $previous, $next, $orderby, $reverse, $period, $download);
        }
    } else if ( $mode == 'courseStatistics') {
        $action_bar = action_bar(
            array(
                array('title' => $langAnalyticsViewPerUserGeneral,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser",
                    'icon' => 'fa-users',
                    'level' => 'primary-label')

            )
        );
        $tool_content .= $action_bar;
        display_analytics_information($analytics_id);
        display_general_lists($analytics_id);
    } else if ($mode == 'showElements'){
        display_analytics_elements($analytics_id);
    } else {
        //Should never get here
    }
} else { // Display all learning analytics
    display_learning_analytics();
}

draw($tool_content, 2, null, $head_content);
