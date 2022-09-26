<?php

require_once 'PeriodType.php';
require_once 'ElementTypes.php';

/**
 * @brief learning analytics home page
 */
function display_learning_analytics() {
    global $course_id, $course_code, $tool_content, $langAnalyticsNoAnalytics, $langActive, $langInactive,
    $langAnalyticsTotalAnalytics, $langAnalyticsViewPerUserGeneral, $langModify, $langAnalyticsEditElements, $langDeactivate,
    $langActivate, $langDelete, $langAnalyticsConfirm, $langLearningAnalytics, $langAdd;

    $sql_data = Database::get()->queryArray("SELECT id, title, description, active, start_date, end_date, created, periodType FROM analytics WHERE courseID= ?d", $course_id);
    if (count($sql_data) == 0) {
        $results = "<div class='text-center text-muted'>$langAnalyticsNoAnalytics</div>";
    } else {
        $results = "";
        foreach ($sql_data as $data) {
            $id = $data->id;
            $active = $data->active;
            $active_vis = $data->active ? "text-success" : "text-danger";
            $active_msg = $data->active ? $langActive : $langInactive;
            $title = $data->title;
            $description = $data->description;
            
            $results .= "
            <div class='row res-table-row border-0'>
                <div class='col-sm-3'>
                    <strong>$title</strong> <span class='$active_vis'>($active_msg)</span><br/>
                    <small class='text-left text-muted'>$description</small>
                </div>
                <div class='col-sm-9 text-left'>".
                action_bar(array(
                    array('title' => $langAnalyticsTotalAnalytics,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;mode=courseStatistics",
                        'icon' => 'fa-bar-chart',
                        'level' => 'primary-label'),
                    array('title' => $langAnalyticsViewPerUserGeneral,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;mode=perUser",
                        'icon' => 'fa-users',
                        'level' => 'primary-label'),
                    array('title' => $langModify,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;edit_analytics=1",
                            'icon' => 'fa-edit'),
                    array('title' => $langAnalyticsEditElements,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;mode=showElements",
                            'icon' => 'fa-edit'),
                    array('title' => $active ? $langDeactivate : $langActivate,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;activate=" .
                                ($active ? '0' : '1'),
                            'icon' => $active ? 'fa-eye-slash' : 'fa-eye'),
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$id&amp;delete_analytics=1",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langAnalyticsConfirm)
                ))
                ."</div>
            </div>";
        }
    }

    $tool_content .= "

            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading'>
                            <div class='row'>
                                <div class='col-xl-7 col-lg-7 col-md-7 col-sm-6 col-6'>
                                    <strong>$langLearningAnalytics</strong>
                                </div>
                                <div class='col-xl-5 col-lg-5 col-md-5 col-sm-6 col-6 text-end'>
                                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;edit_analytics=1' class='btn btn-success btn-sm'><span class='fa fa-plus'></span> &nbsp;&nbsp;&nbsp;$langAdd</a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class='res-table-wrapper'>
                            $results
                        </div>  
                    </div>
                </div>
            </div>";         
}


/**
 * @brief display analytics result per module
 * @param $analytics_id
 */
function display_general_lists($analytics_id) {
    global $course_id, $course_code, $tool_content, $langAnalyticsDetails,
           $langMessage, $langAnalyticsAdvancedLevel, $langAnalyticsCriticalLevel;

    $analytics_elements = Database::get()->queryArray("SELECT * FROM analytics_element WHERE analytics_id= ?d", $analytics_id);

    foreach ($analytics_elements as $analytics_element) {
        $message_advanced = $message_critical = '';
        $good_results = $bad_results = '';
        $module_id = $analytics_element->module_id;
        $resource = $analytics_element->resource;
        $analytics_element_id = $analytics_element->id;
        $upper_threshold = $analytics_element->upper_threshold;
        $lower_threshold = $analytics_element->lower_threshold;

        $users = Database::get()->queryArray("SELECT u.id, u.givenname as givenname, u.surname as surname, cu.user_id as userid 
                FROM course_user AS cu INNER JOIN user as u on cu.user_id=u.id 
                WHERE course_id = ?d 
                AND u.status = " . USER_STUDENT ."", $course_id);

        $critical = array();
        $advanced = array();
        foreach ($users as $user) {
            $user_result = Database::get()->querySingle("SELECT SUM(value) AS value FROM user_analytics WHERE user_id=?d and analytics_element_id = ?d", $user->id, $analytics_element_id);
            if($user_result->value >= $upper_threshold) {
                array_push($advanced, array('id' => $user->id, 'givenname' => $user->givenname, 'surname' => $user->surname));
            } else if($user_result->value <= $lower_threshold) {
                array_push($critical, array('id' => $user->id, 'givenname' => $user->givenname, 'surname' => $user->surname));
            }
        }

        if (count($critical) > 0) {
            $message_critical = $langAnalyticsCriticalLevel;
            foreach ($critical as $crit) {
                $userid = $crit['id'];
                $bad_results .="<div class='res-table-row row'>
                    <div class='col-sm-5'>". display_user($userid) ."</div>
                    <div class='col-sm-7'>".
                action_bar(
                    array(
                        array('title' => $langAnalyticsDetails,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;user_id=$userid",
                                'icon' => 'fa-user-o',
                                'level' => 'primary-label'
                            ),
                        array('title' => $langMessage,
                            'url' => "../message/index.php?course=$course_code&upload=1&type=cm&id=$userid",
                            'icon' => 'fa-envelope',
                            'level' => 'primary-label')
                    )
                ).
                "</div></div>";
            }
        }

        if (count($advanced) > 0) {
            $message_advanced = $langAnalyticsAdvancedLevel;
            foreach ($advanced as $adv) {
                $userid = $adv['id'];
                $good_results .= "<div class='res-table-row row'>
                    <div class='col-sm-5'>". display_user($userid) ."</div>
                        <div class='col-sm-7'>".
                        action_bar(
                            array(
                                array('title' => $langAnalyticsDetails,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;user_id=$userid",
                                        'icon' => 'fa-user-o',
                                        'level' => 'primary-label'
                                    ),
                                array('title' => $langMessage,
                                    'url' => "../message/index.php?course=$course_code&upload=1&type=cm&user_id=$userid",
                                    'icon' => 'fa-envelope',
                                    'level' => 'primary-label')
                            )
                        ).
                    "</div>
                </div>";
            }
        }

        $tool_content .= "
                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-12'>
                                        <strong>
                                            <a class='ps-3' data-bs-toggle='collapse' href='#LearnAnalyticsResource$module_id' aria-expanded='false' aria-controls='LearnAnalyticsResource$module_id'>
                                            <i class='fa fa-arrow-down'>" . get_resource_info($resource, $module_id) . "</a></i>
                                        </strong>
                                    </div>
                                </div>
                            </div>       
                            <hr>                 
                            <div class='col-sm-12 collapse ps-4 pe-4 pt-3 pb-3 mt-4' id='LearnAnalyticsResource$module_id'>
                                <h6 class='text-success'>$message_advanced</h6>
                                <div class='res-table-wrapper'>
                                    $good_results
                                </div>                                                        
                                <h6 class='text-danger mt-4'>$message_critical</h6>
                                <div class='res-table-wrapper'>
                                    $bad_results
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>";
    }
}


/**
 * @brief display learning analytics elements
 * @param $analytics_id
 */
function display_analytics_elements($analytics_id) {

    global $tool_content, $course_code, $langAdd, $langAnalyticsNoElements,
           $langAnalyticsType, $langAnalyticsGradeLimits, $langAnalyticsThresholds,
           $langAnalyticsWeight, $langAnalyticsCriticalLevel, $langAnalyticsAdvancedLevel,
           $langModify, $langDelete, $langAnalyticsConfirmDeletion,
           $langAnalyticsParameters;

    $buttons = array();
    foreach (ElementTypes::elements as $elementType) {
        $type = $elementType['link'];
        array_push( $buttons,   
                array('title' => $elementType['title'],
                    'url' => "$_SERVER[SCRIPT_NAME]?analytics_id=$analytics_id&amp;edit_analytics_element=true&amp;elementType=$type",
                    'icon' => $elementType['icon'],
                    'class' => ''));
    }

    $addParametersBtn = action_button($buttons,
        array(
            'secondary_title' => $langAdd,
            'secondary_icon' => '',
            'secondary_btn_class' => 'btn-success btn-sm'
        
    ));

    $sql_data = Database::get()->queryArray("SELECT id, upper_threshold, lower_threshold, min_value, max_value, weight, resource, module_id FROM analytics_element WHERE analytics_id=?d", $analytics_id);
    if (count($sql_data) == 0) {
        $results = "<p class='text-center text-muted'>$langAnalyticsNoElements</p>";
    } else {
        $results ="<div class='row p-2 res-table-header'>
                        <div class='col-sm-2'>
                            <span class='control-label-notes'>$langAnalyticsType</span>
                        </div>
                        <div class='col-sm-3'>
                        <span class='control-label-notes'>$langAnalyticsGradeLimits</span>
                        </div>
                        <div class='col-sm-4'>
                        <span class='control-label-notes'>$langAnalyticsThresholds</span>
                        </div>
                        <div class='col-sm-2'>
                        <span class='control-label-notes'>$langAnalyticsWeight</span>
                        </div>
                        <div class='col-sm-1 text-center'>
                            <span class='control-label-notes'><i class='fa fa-cogs'></i></span>
                        </div>
                    </div>";
        foreach ($sql_data as $result) {
            $id = $result->id;
            $lower_threshold = $result->lower_threshold;
            $upper_threshold = $result->upper_threshold;
            $min_value = $result->min_value;
            $max_value = $result->max_value;
            $weight = $result->weight;
            $resource = $result->resource;
            $module_id = $result->module_id;
            $results .= "
                <div class='row p-2 res-table-row border-0'>
                    <div class='col-sm-3'><em>
                        " . get_resource_info($resource, $module_id) . "</em>
                        </div>
                    <div class='col-sm-2 col-sm-offset-2'><span class='text-danger'>$min_value</span> - <span class='text-success'>$max_value</span></div>
                    <div class='col-sm-4'>
                        <span class='text-danger'>$langAnalyticsCriticalLevel: $min_value - $lower_threshold</span><br/>
                        <span class='text-success'>$langAnalyticsAdvancedLevel: $upper_threshold - $max_value</span>
                    </div>
                    <div class='col-sm-2'>$weight</div>
                    <div class='col-sm-1 text-center'>".
                        action_button(array(
                            array('title' => $langModify,
                                'icon' => 'fa-edit',
                                'url' => "$_SERVER[SCRIPT_NAME]?analytics_element_id=$id&amp;edit_analytics_element=true&amp;analytics_id=$analytics_id" ,
                                'class' => ''
                            ),
                            array('title' => $langDelete,
                                'icon' => 'fa-times',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;analytics_element_id=$id&amp;delete_analytics_element=1",
                                'confirm' => $langAnalyticsConfirmDeletion,
                                'class' => 'delete'))).
                    "</div>
                </div>";
        }
    }

    $tool_content .= "
        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div class='inner-heading'>
                        <div class='row'>
                            <div class='col-xl-7 col-lg-7 col-md-7 col-sm-6 col-6'>
                                <strong>$langAnalyticsParameters</strong>
                            </div>
                            <div class='col-xl-5 col-lg-5 col-md-5 col-sm-6 col-6 text-end'>
                                <div style='margin-top:-3px;'>$addParametersBtn</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='res-table-wrapper'>
                        $results
                    </div>  
                </div>
            </div>
        </div>";
}


/**
 * Learning Analytics information
 * @param $analytics_id
 */
function display_analytics_information($analytics_id) {
    global $course_id, $course_code, $tool_content, $langActive, $langInactive, $langModify, $langDescription,
    $langAnalyticsTimeFrame, $langFrom, $langTill, $langAnalyticsCalculation;

    $sql_data = Database::get()->querySingle("SELECT a.id as id, a.title as title, a.description as description, a.active as active, a.start_date as start_date, a.end_date as end_date, a.created as created, a.periodtype as periodType FROM analytics as a WHERE a.courseID= ?d AND a.id = ?d", $course_id, $analytics_id);
    
    $title = $sql_data->title;
    $description = $sql_data->description;
    $active_vis = $sql_data->active ? "text-success" : "text-danger";
    $active_msg = $sql_data->active ? $langActive : $langInactive;
    $start_date = format_locale_date(strtotime($sql_data->start_date), 'short', false);
    $end_date = format_locale_date(strtotime($sql_data->end_date), 'short', false);
    $periodType = periodType::periodType[$sql_data->periodType]['title'];

    $tool_content .= "
        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div class='inner-heading'>
                        <div class='row'>
                            <div class='col-xl-7 col-lg-7 col-md-7 col-sm-6 col-6'>
                                <strong>$title</strong> <span class='$active_vis'>($active_msg)</span>
                            </div>
                            <div class='col-xl-5 col-lg-5 col-md-5 col-sm-6 col-6 text-end'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;edit_analytics=1' class='btn btn-primary btn-sm'>"
                                        . "<span class='fa fa-pencil'></span> &nbsp;&nbsp;$langModify
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-sm-7'>
                                <div class='control-label-notes pn-info-title-sct'>$langDescription</div>
                                <div class='pn-info-text-sct'>$description</div>
                            </div>
                            <div class='col-sm-5'>
                                <div class='control-label-notes pn-info-title-sct'>$langAnalyticsTimeFrame</div>
                                <div class='pn-info-text-sct'>$langFrom $start_date $langTill $end_date</div>
                                <div class='mt-4 control-label-notes pn-info-title-sct'>$langAnalyticsCalculation</div>
                                <div class='pn-info-text-sct'>$periodType</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
}

/**
 * @brief display learning analytics per user
 * @param $analytics_id
 * @param $startdate
 * @param $enddate
 * @param $previous
 * @param $next
 * @param $orderby
 * @param $reverse
 * @param $period
 * @param $download
 */
function display_analytics_peruser($analytics_id, $startdate, $enddate, $previous, $next, $orderby, $reverse, $period, $download) {
    global $tool_content, $course_id, $course_code, $langAnalyticsNoUsersToDisplay, $langSurnameName, $langPercentage, $langAnalyticsStatus,
    $langAnalyticsAdvancedLevel, $langAnalyticsMiddleLevel, $langAnalyticsCriticalLevel, $langAnalyticsDetails, $langMessage;

    $sql_data = Database::get()->queryArray("SELECT u.givenname AS givenname, 
                                u.surname AS surname, cu.user_id AS userid 
                                FROM course_user AS cu INNER JOIN user AS u on cu.user_id=u.id 
                                WHERE course_id = ?d 
                                AND cu.status = " . USER_STUDENT . "", $course_id);

    if(count($sql_data) == 0) {
        $results = "<div><p class='text-center text-muted'>$langAnalyticsNoUsersToDisplay</p>";
    } else {
        $backclass = '';
        if (is_null($previous)) {
            $backclass = 'style="display:none"';
        }
        $nextclass = '';
        if (is_null($next)) {
            $nextclass = 'style="display:none"';
        }
        //translation until here
        $arrowdirection = 'down';
        $arrowdirectionName = 'down';
        $arrowdirectionPercentage = 'down';
        $reverse_op = 'true';

        if($reverse=='true') {
            $arrowdirection = 'up';
            $reverse_op = 'false'; 
        }

        if($orderby == 'surname') {
            $arrowdirectionName = $arrowdirection;
        } else if($orderby == 'percentage') {
            $arrowdirectionPercentage = $arrowdirection;
        }

        $results = "
                <div class='inner-heading'>
                    <div class='row p-2 res-table-header'>
                        <div class='col-sm-3'>
                            <h6>$langSurnameName <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;period=$period&amp;orderby=surname&amp;reverse=$reverse_op'><i class='fa fa-arrow-$arrowdirectionName fa-fw' aria-hidden='true'></i></a></h6>
                        </div>
                        <div class='col-sm-3'>
                            <h6>$langPercentage <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;period=$period&amp;orderby=percentage&amp;reverse=$reverse_op'><i class='fa fa-arrow-$arrowdirectionPercentage fa-fw' aria-hidden='true'></i></a></h6>
                        </div>
                        <div class='col-sm-2'>
                            <h6>$langAnalyticsStatus</h6> 
                        </div>
                        <div class='col-sm-4' >
                            <h6>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;period=$previous&amp;orderby=$orderby&amp;reverse=$reverse'><i class='fa fa-arrow-circle-left fa-fw' $backclass aria-hidden='true'></i></a>
                                " . format_locale_date(strtotime($startdate), 'short', false) . " &mdash; " . format_locale_date(strtotime($enddate), 'short', false)  . "
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;period=$next&amp;orderby=$orderby&amp;reverse=$reverse'><i class='fa fa-arrow-circle-right fa-fw' $nextclass aria-hidden='true'></i></a>
                            </h6>
                        </div>
                    </div>
                </div>
                <hr>
                <div class='res-table-wrapper'>";

        $peruserarray = array();

        foreach ($sql_data as $data) {
            $userid = $data->userid;
            $values = compute_general_analytics_foruser($userid, $analytics_id, $startdate, $enddate);
            $percentage = $values['percentage'];

            /*if ($orderby == 'percentage'){
                $peruserarray[(string)$percentage + (string)$userid] = array('givenname' => $givenname, 'surname' => $surname, 'userid' => $userid, 'percentage'=>$percentage, 'values' =>$values);
            }  else {
                $peruserarray[$surname] = array('givenname' => $givenname, 'surname' => $surname, 'userid' => $userid, 'percentage'=>$percentage, 'values' =>$values);
            } */
        //}
        /*
        if($reverse == 'false') {
            ksort($peruserarray);
        } else {
            krsort($peruserarray);
        }
        */
        //foreach ($peruserarray as $peruser) {
            $results .="<div class='row p-2 res-table-row border-0'>
            <div class='col-sm-3'>
                <div >". display_user($userid). "</div>
            </div>
            <div class='col-sm-3'>
                <div class='progress mt-md-0 mt-2' style='display: inline-block; width: 120px; margin-bottom:0px;'>
                    <div class='progress-bar' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: " .$percentage ."%; min-width: 2em;'>
                    " .$percentage ."%
                    </div>
                </div>
            </div>
            <div class='col-sm-2'>
                <div>
                <span class='text-success'>$langAnalyticsAdvancedLevel: " . $values['text-success'] . "</span><br/>
                <span class='text-warning'>$langAnalyticsMiddleLevel: " . $values['text-warning'] . "</span><br/>
                <span class='text-danger'>$langAnalyticsCriticalLevel: " . $values['text-danger'] . "</span>
                </div>
            </div>
            <div class='col-sm-4'>" . action_bar(
                array(                  
                    array('title' => $langAnalyticsDetails,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;user_id=$userid&amp;period=$period",
                            'icon' => 'fa-user-o',
                            'level' => 'primary-label'
                        ),
                    array('title' => $langMessage,
                        'url' => "../message/index.php?course=$course_code&upload=1&type=cm&user_id=$userid",
                        'icon' => 'fa-envelope',
                        'level' => 'primary-label')
                )
            ) . "</div>
            </div>";
        }
    }
    $analytics_title = Database::get()->querySingle("SELECT title FROM analytics WHERE id=?d", $analytics_id);

    if ($download) {
        generate_analytics_csv($peruserarray, $analytics_title->title);
    }

    $tool_content .= "
        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>                 
            <div class='panel panel-default'>
                <div class='panel-heading'>             
                    <div class='control-label-notes text-center'>$analytics_title->title</div>
                </div>
                <div class='panel-body'>
                     $results
                </div>
            </div>
        </div>";
    $tool_content .= "</div>";
}


/**
 * @brief export learning analytics
 * @param $peruserarray
 * @param $title
 */
function generate_analytics_csv($peruserarray, $title) {

    require_once 'include/lib/csv.class.php';

    global $langSurnameName, $langPercentage, $langAnalyticsAdvancedLevel, $langAnalyticsMiddleLevel, $langAnalyticsCriticalLevel;

    $csv = new CSV();
    $csv->setEncoding('UTF-8');

    $csv->filename =   $title . '_learning_analytics.csv';

    $csv->outputRecord($langSurnameName, $langPercentage, $langAnalyticsAdvancedLevel, $langAnalyticsMiddleLevel, $langAnalyticsCriticalLevel);

    foreach($peruserarray as $array) {
        $csv->outputRecord($array['givenname'] . ' ' . $array['surname'], $array['percentage'], $array['values']['text-success'], $array['values']['text-warning'], $array['values']['text-danger']);
    }
    exit;
}


/**
 * @brief display user learning analytics
 * @param $userid
 * @param $analytics_id
 * @param $start
 * @param $end
 * @param $previous
 * @param $next
 */
function display_analytics_user($userid, $analytics_id, $start, $end, $previous, $next) {

    global $tool_content, $course_code, $langType, $langPercentage;

    $backclass = '';
    if (is_null($previous)) {
        $backclass = 'style="display:none"';
    }

    $nextclass = '';
    if (is_null($next)) {
        $nextclass = 'style="display:none"';
    }
    $results = "
        <div class='inner-heading notes_thead mt-3'>
            <h5>
                <div class='row p-2 res-table-header'>
                    <div class='col-sm-5 text-white'>
                        $langType 
                    </div>
                    <div class='col-sm-3 text-white'>
                        $langPercentage
                    </div>
                    <div class='col-sm-4 text-white'>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;user_id=$userid&amp;period=$previous'><i class='fa fa-arrow-circle-left fa-fw' $backclass aria-hidden='true'></i></a>"
                            . format_locale_date(strtotime($start), 'short', false) . " &mdash; " . format_locale_date(strtotime($end), 'short', false) .
                            "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=perUser&amp;user_id=$userid&amp;period=$next'><i class='fa fa-arrow-circle-right fa-fw' $nextclass aria-hidden='true'></i></a>
                    </div>
                </div>
            </h5>
        </div>
    <div class='res-table-wrapper'>";

    $elements_data = Database::get()->queryArray("SELECT id, module_id, resource, upper_threshold, lower_threshold, max_value, min_value 
                                            FROM analytics_element
                                            WHERE analytics_id = ?d", $analytics_id);

    foreach($elements_data as $element_data) {
        $element_id = $element_data->id;
        $module_id = $element_data->module_id;
        $resource = $element_data->resource;
        $upper_threshold = $element_data->upper_threshold;
        $lower_threshold = $element_data->lower_threshold;
        $max_value = $element_data->max_value;
        $min_value = $element_data->min_value;

        $elements_data = Database::get()->queryArray("SELECT value, updated 
                                                        FROM user_analytics
                                                        WHERE user_id = ?d
                                                        AND analytics_element_id = ?d
                                                        AND updated >= ?t
                                                        AND updated <= ?t", $userid, $element_id, $start, $end);

        $total_value = 0;

        if(count($elements_data) > 0) {
            foreach ($elements_data as $element_data) {
                $total_value = $total_value + $element_data->value;
            }
        }

        if($max_value < $total_value) {
            $total_value = $max_value;
        } else if ($min_value > $total_value) {
            $total_value = $min_value;
        }

        if($upper_threshold <= $total_value) {
            $class = "text-success";
        } else if ($lower_threshold >= $total_value) {
            $class = "text-danger";
        } else {
            $class = "text-warning";
        }

        $percentage_value = (($total_value - $min_value) * 100) / ($max_value - $min_value);
        $percentage_value = number_format($percentage_value , 2, '.', '') + 0;

        $results .="<div class='m-auto row p-2 res-table-row'>
                        <div class='col-sm-5'>
                            <div >". ElementTypes::elements[$module_id]['title'] . "</div>
                        </div>
                        <div class='col-sm-3'>
                            <div class='$class'>$percentage_value%</div>
                        </div>
                    </div>";

    }
    $analytics_title = Database::get()->querySingle("SELECT title FROM analytics WHERE id=?d", $analytics_id);

    $tool_content .= "
            <div class='col-12 mt-3'>
                    <div class='panel panel-default'>
                        <h4 class='control-label-notes text-center pt-3'>$analytics_title->title</h4>
                        <hr>
                        <div class='panel-body'>
                        $results
                        </div>
                    </div>
                </div>
            </div>";
}



function display_user_info($user_id) {
    global $tool_content, $langAnalyticsNotAvailable, $langEmail, $langAm, $langPhone;
    $user_data = Database::get()->querySingle("SELECT givenname, surname, email, am, phone FROM user WHERE id=?d", $user_id);

    $givenname = $user_data->givenname;
    $surname = $user_data->surname;

    $email = $user_data->email;
    if($email == '')
        $email = '<span class="tag-value not_visible"> - ' . $langAnalyticsNotAvailable . ' - </span>'; 

    $am = $user_data->am;
    if($am == '')
        $am = '<span class="tag-value not_visible"> - ' . $langAnalyticsNotAvailable . ' - </span>'; 
    $phone = $user_data->phone;
    if($phone == '')
        $phone = '<span class="tag-value not_visible"> - ' . $langAnalyticsNotAvailable . ' - </span>'; 
    
    $tool_content .= "
        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div class='inner-heading'>
                        <div class='row'>
                            <div class='col-sm-12'>
                                <strong>$givenname $surname</strong>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='panel-body'>
                        <div class='row p-2'>
                            <div class='col-sm-7'>
                                <div class='pn-info-title-sct control-label-notes'>$langEmail</div>
                                $email
                            </div>
                            <div class='col-sm-5'>
                                <div class='pn-info-title-sct control-label-notes'>$langAm</div>
                                $am
                                <div class='mt-4 pn-info-title-sct control-label-notes'>$langPhone</div>
                                $phone

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
}

/**
 * @brief Compute user learning analytics
 * @param $userid
 * @param $analytics_id
 * @param $start
 * @param $end
 * @return array
 */
function compute_general_analytics_foruser($userid, $analytics_id, $start, $end) {
    $toreturn = array('text-success' => 0, 'text-warning' => 0, 'text-danger' => 0,);
    $start = $start ." 00:00";
    $end = $end ." 23:59";
    $results = array();

    //Get all available elements
    $sql_elements = Database::get()->queryArray("SELECT id, upper_threshold, lower_threshold, weight, min_value, max_value 
                                        FROM analytics_element 
                                        WHERE analytics_id = ?d", $analytics_id);
    
    foreach ($sql_elements as $sql_element) {
        $element_id = $sql_element->id;
        $element_upper_threshold = $sql_element->upper_threshold;
        $element_lower_threshold = $sql_element->lower_threshold;
        $element_max_value = $sql_element->max_value;
        $element_min_value = $sql_element->min_value;
        $element_weight = $sql_element->weight;

        $element_result = Database::get()->querySingle("SELECT sum(value) as total 
                                                            FROM user_analytics
                                                            WHERE updated >= ?t
                                                            AND updated <= ?t
                                                            and analytics_element_id = ?d
                                                            AND user_id = ?d", $start, $end, $element_id, $userid);
        
        if($element_upper_threshold <= $element_result->total) {
            $status = "text-success";
        } else if ($element_lower_threshold >= $element_result->total) {
            $status =  "text-danger";
        } else {
            $status = "text-warning";
        }

        $value = 0;
        if (!is_null($element_result)) {
            $value = (($element_result->total - $element_min_value) * 100) / ($element_max_value - $element_min_value);
            if ($value>100)
                $value = 100;
            else if($value < 0)
                $value = 0;
        }
        $results[$element_id] = array('percentage' => $value, 'status' => $status, 'weight' => $element_weight);
        $toreturn[$status] = $toreturn[$status] + 1;
    }

    $sum_percentage = 0;
    $sum_weight = 0;

    foreach ($results as $result){
        $sum_percentage = $sum_percentage + ($result['weight'] * $result['percentage']);
        $sum_weight = $sum_weight + $result['weight'];
    }

    if ($sum_weight != 0)
        $toreturn['percentage'] = number_format($sum_percentage/$sum_weight, 2, '.', '') + 0;
    else
        $toreturn['percentage'] = 0;
    
    return $toreturn;
}

//Function to add or edit analytics
function edit_analytics_settings ($analytics_id = 0)
{
    global $tool_content, $course_code, $course_id, $language, $langCertDeadlineHelp, $head_content, $langTitle, $langDescription,
    $langActivate, $langAnalyticsCalculation, $langStart, $langAnalyticsStartDescription, $langFinish, $langAnalyticsEndDescription,
    $langSave, $langAdd;

    load_js('bootstrap-datepicker');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#start_date').datepicker({
                    format: 'dd-mm-yyyy',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true
            });
        });

        $(function() {
            $('#end_date').datepicker({
                    format: 'dd-mm-yyyy',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true
            });
        });
        </script>";

    if ($analytics_id > 0) {
        $result = Database::get()->querySingle("SELECT a.id as id, a.title as title, a.description as description, a.active as active, a.start_date as start_date, a.end_date as end_date, a.created as created, a.periodType as periodType FROM analytics as a WHERE a.courseID= ?d AND a.id = ?d", $course_id, $analytics_id);

        $title = $result->title;
        $description = $result->description;
        $active = $result->active;
        $start_date = date_format(date_create_from_format('Y-m-d', $result->start_date), 'd-m-Y');
        $end_date = date_format(date_create_from_format('Y-m-d', $result->end_date), 'd-m-Y');
        $periodType = $result->periodType;
        $action = 'update_analytics';
        $id_input = "<input type='hidden' name='analytics_id' value='$analytics_id'>";
    } else {
        $title = '';
        $description = '';
        $active = '';
        $start_date = '';
        $end_date = '';
        $periodType = '';
        $action = 'insert_analytics';
        $id_input = '';
    }
    //<form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
    
    $tool_content .= "
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
    <div class='form-wrapper shadow-sm p-3 rounded'>
    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <div class='form-group mt-3'>
            <label for='title' class='col-sm-6 control-label-notes'>$langTitle</label>
            <div class='col-sm-12'>
                <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$title'>
            </div>
        </div>
        <div class='form-group mt-3'>
            <label for='description' class='col-sm-6 control-label-notes'>$langDescription</label>
            <div class='col-sm-12'>
                <textarea class='form-control' placeholder='$langDescription' name='description' rows='6'>$description</textarea>
            </div>
        </div>
        <div class='form-group mt-3'>
            <label for='title' class='col-sm-6 control-label-notes'>$langActivate</label>
                <div class='col-sm-12'>";
                    $tool_content .= selection(get_activation_status(), 'active', $active);
                $tool_content .= "</div>
        </div>
        <div class='form-group mt-3'>
        <label for='title' class='col-sm-6 control-label-notes'>$langAnalyticsCalculation</label>
            <div class='col-sm-12'>";
                $tool_content .= selection(get_period_types_array () , 'periodType', $periodType);
            $tool_content .= "</div>
        </div>
        <div class='form-group mt-3'>
            <label class='col-sm-6 control-label-notes'>$langStart:</label>
            <div class='col-sm-12'>
                <div class='input'>
                    <input class='form-control' name='start_date' id='start_date' type='text' value='$start_date'>
                </div>
                <span class='help-block'>&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i>$langAnalyticsStartDescription</span>
            </div>
        </div>
        <div class='form-group mt-3'>
            <label class='col-sm-6 control-label-notes'>$langFinish:</label>
            <div class='col-sm-12'>
                <div class='input'>
                    <input class='form-control' name='end_date' id='end_date' type='text' value='$end_date'>
                </div>
                <span class='help-block'>&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i>$langAnalyticsEndDescription</span>
            </div>
        </div> $id_input
        <div class='form-group mt-5'>
            <div class='col-12'>
              <div class='row'>
                <div class='col-6'>
                  ".form_buttons(array(
                    array(
                            'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                            'text' => $langSave,
                            'name' => $action,
                            'value'=> $langAdd
                        )
                    ))."
                </div>
                <div class='col-6'>
                  ".form_buttons(array(
                    array(
                        'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                        )
                    ))."
                </div>
              </div>
            </div>
        </div>
    </form>
</div></div>";
}

/**
 * @brief display form for adding analytics element
 * @param $analytics_id
 * @param null $type
 * @param int $analytics_element_id
 */
function analytics_element_form($analytics_id, $type=null, $analytics_element_id=0) {

    global $tool_content, $course_code, $langAnalyticsCriticalLevel, $langAnalyticsMinValue,
           $langAnalyticsWeight, $langAnalyticsMaxValue, $langAnalyticsAdvancedLevel, $langSave, $langAdd;

    if ($analytics_element_id==0) {
        $resource = '';
        $upper_threshold = 0;
        $lower_threshold = 0;
        $min_value = 0;
        $max_value = 0;
        $weight = 0;
        $action = 'insert_analytics_element';
    } else {
        $result = Database::get()->querySingle("SELECT * FROM analytics_element WHERE id= ?d AND analytics_id = ?d", $analytics_element_id, $analytics_id);
        $resource = $result->resource;
        $upper_threshold = $result->upper_threshold;
        $lower_threshold = $result->lower_threshold;
        $min_value = $result->min_value;
        $max_value = $result->max_value;
        $weight = $result->weight;
        $action = 'update_analytics_element';
        $module_id = $result->module_id;
    }
    
    if ($type == null) {
        $elementTypeTitle = ElementTypes::elements[$module_id]['title'];
    } else {
            foreach(ElementTypes::elements as $element) {
                if($element['link'] == $type) {
                    $elementTypeTitle = $element['title'];
           }
        }
    }

    $tool_content .="
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
    <div class='form-wrapper shadow-sm p-3 rounded'><form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
    <h4 class='fs-4 control-label-notes text-center'>$elementTypeTitle</h4>
        <input type='hidden' name='analytics_id' value='$analytics_id'>
        <input type='hidden' name='analytics_element_id' value='$analytics_element_id'>
        <div class='form-group mt-4'>
            <div class='row'>
                <label for='title' class='col-sm-4 control-label-notes' style='text-align:left'>$langAnalyticsCriticalLevel:</label>
                <label for='title' class='col-sm-2 control-label'>$langAnalyticsMinValue</label>
                <div class='col-sm-2'>
                    <input class='form-control' type='number' name='min_value' value='$min_value'>
                </div>
                <label for='title' class='col-sm-2 control-label'>$langAnalyticsMaxValue</label>
                <div class='col-sm-2'>
                    <input class='form-control' type='number' name='lower_threshold' value='$lower_threshold'>
                </div>
            </div>
        </div>
        <div class='form-group mt-3'>
            <div class='row'>
                <label for='title' class='col-sm-4 control-label-notes' style='text-align:left'>$langAnalyticsAdvancedLevel:</label>
                <label for='title' class='col-sm-2 control-label'>$langAnalyticsMinValue</label>
                <div class='col-sm-2'>
                    <input class='form-control' type='number' name='upper_threshold' value='$upper_threshold'>
                </div>
                <label for='title' class='col-sm-2 control-label'>$langAnalyticsMaxValue</label>
                <div class='col-sm-2'>
                    <input class='form-control' type='number' name='max_value' value='$max_value'>
                </div>
            </div>
        </div>
        <div class='form-group mt-3'>   
            <label for='title' class='col-sm-6 control-label-notes'  style='text-align:left'>$langAnalyticsWeight:</label>
            <div class='col-sm-12'>
                <input class='form-control' type='number' placeholder='' name='weight' value='$weight'>
            </div>
        </div>";

        $tool_content .= get_available_resources($type, $analytics_element_id);
        $tool_content .= "<div class='form-group mt-5'>
            <div class='col-12'>
              <div class='row'>
                <div class='col-6'>
                 ".form_buttons(array(
                    array(
                            'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                            'text' => $langSave,
                            'name' => $action,
                            'value'=> $langAdd
                        )
                    ))."
                </div>
                <div class='col-6'>
                 ".form_buttons(array(
                    array(
                        'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;analytics_id=$analytics_id&amp;mode=showElements"
                        )
                    ))."
                </div>
              </div>
            </div>
        </div>
    </form></div></div>";
}


/**
 * @brief get available resources
 * @param $type
 * @param $analytics_element_id
 * @return string
 */
function get_available_resources($type, $analytics_element_id) {

    global $course_id, $analytics_id, $langAnalyticsResourceNotAvailable, $langAnalyticsResource;
    
    $resource_id = 0;
    $resource_type_id = 0;
    $resource = array();
    $resource_field = "";

    if ($analytics_element_id) {
        
        $result = Database::get()->querySingle("SELECT resource, module_id FROM analytics_element WHERE id = ?d", $analytics_element_id);
    
        $resource_id = $result->resource;
        $resource_type_id = $result->module_id;
        
        switch ($resource_type_id) {
            case ANALYTICS_EXERCISEGRADE:
                $result = Database::get()->queryArray("SELECT id, title FROM exercise WHERE course_id = ?d
                                            AND active = 1
                                            AND id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND id != ?d
                                                AND module_id = " . ANALYTICS_EXERCISEGRADE . ")
                                            ORDER BY title", $course_id, $analytics_id, $analytics_element_id);
                break;
            case ANALYTICS_ASSIGNMENTGRADE:
                $result = Database::get()->queryArray("SELECT id, title FROM assignment WHERE course_id = ?d
                                            AND active = 1
                                               AND id NOT IN 
                                               (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                   AND id != ?d
                                                   AND module_id = " . ANALYTICS_ASSIGNMENTGRADE . ")
                                               ORDER BY title", $course_id, $analytics_id, $analytics_element_id);
                break;
            case ANALYTICS_ASSIGNMENTDL:
                $result = Database::get()->queryArray("SELECT id, title FROM assignment WHERE course_id = ?d
                                            AND active = 1
                                               AND id NOT IN 
                                               (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                   AND id != ?d
                                                   AND module_id = " . ANALYTICS_ASSIGNMENTDL . ")
                                               ORDER BY title", $course_id, $analytics_id, $analytics_element_id);
                break;
            case ANALYTICS_LPPERCENTAGE:
                $result = Database::get()->queryArray("SELECT learnPath_id as id, name as title FROM lp_learnPath WHERE course_id = ?d
                                            AND learnPath_id NOT IN 
                                               (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                   AND id != ?d
                                                   AND module_id = " . ANALYTICS_LPPERCENTAGE . ")
                                               ORDER BY name", $course_id, $analytics_id, $analytics_element_id);
                break;
            case ANALYTICS_FILEVIEW:
                $result = Database::get()->queryArray("SELECT id,  (CASE WHEN title IS NULL OR title=' ' THEN filename ELSE title END) as title FROM document WHERE course_id = ?d
                                            AND visible = 1
                                            AND id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND id != ?d
                                                AND module_id = " . MODULE_ID_DOCS . ")
                                            ORDER BY title", $course_id, $analytics_id, $analytics_element_id);
                break;
            default:
                $resource_field = "<input type='hidden' name='resource' value='null'>
                                   <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
                break;
        }
    } else {
        switch ($type) {
            case 'blogposts':
                $resource_type_id = ANALYTICS_BLOGPOSTS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;     
            case 'blog-comments':
                $resource_type_id = ANALYTICS_BLOGCOMMENTS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'course-comments':
                $resource_type_id = ANALYTICS_COURSECOMMENTS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'wall-comments':
                $resource_type_id = ANALYTICS_WALLCOMMENTS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'blog-comments':
                $resource_type_id = ANALYTICS_COMMENTS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'forum-posts':
                $resource_type_id = ANALYTICS_FORUMACTIVITY;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'wiki-pages':
                $resource_type_id = ANALYTICS_WIKIACTIVITY;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'dailylogins':
                $resource_type_id = ANALYTICS_DAILYLOGINS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;
            case 'hits':
                $resource_type_id = ANALYTICS_HITS;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;   
            case 'duration':
                $resource_type_id = ANALYTICS_DURATION;
                $resource_field = "<input type='hidden' name='resource' value='null'>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
                return $resource_field;   
            case 'exercise-grade':
                $resource_type_id = ANALYTICS_EXERCISEGRADE;
                $result = Database::get()->queryArray("SELECT id, title FROM exercise WHERE course_id = ?d
                                            AND active = 1
                                            AND id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND module_id = ?d)
                                            ORDER BY title", $course_id, $analytics_id, $resource_type_id);
                break;
            case 'assignment-grade':
                $resource_type_id = ANALYTICS_ASSIGNMENTGRADE;
                $result = Database::get()->queryArray("SELECT id, title FROM assignment WHERE course_id = ?d
                                            AND active = 1
                                            AND id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND module_id = ?d)
                                            ORDER BY title", $course_id, $analytics_id, $resource_type_id);
                break;
            case 'assignment-dl':
                $resource_type_id = ANALYTICS_ASSIGNMENTDL;
                $result = Database::get()->queryArray("SELECT id, title FROM assignment WHERE course_id = ?d
                                            AND active = 1
                                            AND id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND module_id = ?d)
                                            ORDER BY title", $course_id, $analytics_id, $resource_type_id);
                break;
            case 'lp-percentage':
                $resource_type_id = ANALYTICS_LPPERCENTAGE;
                $result = Database::get()->queryArray("SELECT learnPath_id as id, name as title FROM lp_learnPath WHERE course_id = ?d
                                        AND learnPath_id NOT IN 
                                            (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND module_id = ?d)
                                            ORDER BY name", $course_id, $analytics_id, $resource_type_id);
                break;
            case 'viewing-event':
                $resource_type_id = ANALYTICS_FILEVIEW;
                $result = Database::get()->queryArray("SELECT id,  (CASE WHEN title IS NULL OR title=' ' THEN filename ELSE title END) as title FROM document WHERE visible = 1 AND course_id = ?d
                AND id NOT IN (SELECT resource FROM analytics_element WHERE analytics_id=?d
                                                AND module_id = ?d)", $course_id, $analytics_id, $resource_type_id);
                break;
        }
    }


    foreach ($result as $row) {
        $resource[$row->id] = $row->title;
    }

    if (sizeof($resource)  == 0) {
        $resource_field = "<input type='hidden' name='resource' value='null'>
                <div class='alert alert-warning'>$langAnalyticsResourceNotAvailable</div>
                <input type='hidden' name='module_id' value='$resource_type_id'>";
        return $resource_field;
    }

    $resource_field =  "<div class='form-group mt-3'>
                            <label for='title' class='col-sm-6 control-label-notes'>$langAnalyticsResource</label>
                            <div class='col-sm-12'>"
                            . selection($resource, 'resource', $resource_id) . 
                            "</div>
                        </div>
                        <input type='hidden' name='module_id' value='$resource_type_id'>";

    return $resource_field;
}

/**
 * @return array
 */
function get_activation_status () {
    global $langInactive, $langActive;

    $status = array();
    $status[0] = $langInactive;
    $status[1] = $langActive;

    return $status;
}

/**
 * @return array
 */
function get_period_types_array () {
    $periodTypes = array();

    foreach ( PeriodType::periodType as $key => $periodType) {
        $periodTypes[$key] = $periodType['title'];
    }
    return $periodTypes;
}


function insert_analytics($title, $description, $active, $periodType, $start_date, $end_date, $created) {
    global $course_id;
    
    $new_id = Database::get()->query("INSERT INTO analytics SET 
                                        courseID = ?d,
                                        title = ?s,
                                        description = ?s,
                                        active = ?d,
                                        periodType = ?d,
                                        start_date = ?t,
                                        end_date = ?t,
                                        created = ?t", $course_id, $title, $description, $active, $periodType, $start_date, $end_date, $created)->lastInsertID;

    return $new_id;
}

function update_analytics($analytics_id, $title, $description, $active, $periodType, $start_date, $end_date) {
    global $course_id;
    
    Database::get()->query("UPDATE analytics SET 
                                        title = ?s,
                                        description = ?s,
                                        active = ?d,
                                        periodType = ?d,
                                        start_date = ?t,
                                        end_date = ?t
                                    WHERE id = ?d AND courseID = ?d", $title, $description, $active, $periodType, $start_date, $end_date, $analytics_id, $course_id);
}

function delete_analytics($analytics_id) {
    global $course_id;

    Database::get()->query("DELETE FROM user_analytics WHERE analytics_element_id IN (SELECT id FROM analytics_element WHERE analytics_id = ?d)", $analytics_id);
    Database::get()->query("DELETE FROM analytics_element WHERE analytics_id = ?d", $analytics_id);
    Database::get()->query("DELETE FROM analytics WHERE id = ?d AND courseID = ?d", $analytics_id, $course_id);
    
    return TRUE;
}

function delete_analytics_element($analytics_id, $analytics_element_id) {
    Database::get()->query("DELETE FROM user_analytics WHERE analytics_element_id = ?d", $analytics_element_id);
    Database::get()->query("DELETE FROM analytics_element WHERE id = ?d AND analytics_id = ?d", $analytics_element_id, $analytics_id);

    return TRUE;
}



function delete_user_analytics($analytics_element_id) {
    Database::get()->query("DELETE FROM user_analytics WHERE analytics_element_id = ?d", $analytics_element_id);

    return TRUE;
}

function switch_activation($analytics_id, $active) {
    global $course_id;

    Database::get()->query("UPDATE analytics SET 
                                active = ?d
                            WHERE id = ?d AND courseID = ?d", $active, $analytics_id, $course_id);

}


/**
 * @brief get info about resource to be displayed
 * @param $resource
 * @param $module_id
 * @return string
 */
function get_resource_info($resource, $module_id) {
    $module_title = ElementTypes::elements[$module_id]['title'];
    $resource_title = '';
    switch ($module_id) {
        case ANALYTICS_ASSIGNMENTGRADE:
            $result = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $resource);
            $resource_title = ' (' . $result->title. ') ';
            break;
        case ANALYTICS_ASSIGNMENTDL:
            $result = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $resource);
            $resource_title = ' (' . $result->title. ') ';
            break;
        case ANALYTICS_EXERCISEGRADE:
            $result = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d", $resource);
            $resource_title = ' (' . $result->title. ') ';
            break;
        case ANALYTICS_LPPERCENTAGE:
            $result = Database::get()->querySingle("SELECT name as title FROM lp_learnPath WHERE learnPath_id = ?d", $resource);
            $resource_title = ' (' . $result->title. ') ';
            break;
        case ANALYTICS_FILEVIEW:
            $result = Database::get()->querySingle("SELECT  (CASE WHEN title IS NULL OR title=' ' THEN filename ELSE title END) as title FROM document WHERE id = ?d", $resource);
            $resource_title = ' (' . $result->title. ') ';
            break;  
         
    }
    
    return '' . $module_title .  $resource_title ;
}

/**
 * @brief insert new analytics element
 * @param $analytics_id
 * @param $resource
 * @param $module_id
 * @param $min_value
 * @param $max_value
 * @param $lower_threshold
 * @param $upper_threshold
 * @param $weight
 * @return mixed
 */
function insert_analytics_element($analytics_id, $resource, $module_id, $min_value, $max_value, $lower_threshold, $upper_threshold, $weight) {
    global $course_id;
    
    $new_id = Database::get()->query("INSERT INTO analytics_element SET 
                                        analytics_id = ?d,
                                        resource = ?d,
                                        module_id = ?d,
                                        min_value = ?d,
                                        max_value = ?d,
                                        lower_threshold = ?d,
                                        upper_threshold = ?d,
                                        weight = ?d", $analytics_id, $resource, $module_id, $min_value, $max_value, $lower_threshold, $upper_threshold, $weight)->lastInsertID;

        triggerAnalytics($course_id, $module_id, $new_id, $resource, $analytics_id);
        return $new_id;
}

/**
 * @brief update existing analytics element
 * @param $analytics_id
 * @param $analytics_element_id
 * @param $resource
 * @param $module_id
 * @param $min_value
 * @param $max_value
 * @param $lower_threshold
 * @param $upper_threshold
 * @param $weight
 */
function update_analytics_element($analytics_id, $analytics_element_id, $resource, $module_id, $min_value, $max_value, $lower_threshold, $upper_threshold, $weight) {
    global $course_id;
    
    Database::get()->query("UPDATE analytics_element SET 
                                        analytics_id = ?d,
                                        resource = ?d,
                                        module_id = ?d,
                                        min_value = ?d,
                                        max_value = ?d,
                                        lower_threshold = ?d,
                                        upper_threshold = ?d,
                                        weight = ?d
                                    WHERE id = ?d", $analytics_id, $resource, $module_id, $min_value, $max_value, $lower_threshold, $upper_threshold, $weight, $analytics_element_id);

    delete_user_analytics($analytics_element_id);
    triggerAnalytics($course_id, $module_id, $analytics_element_id, $resource, $analytics_id);
}

/**
 * @param $course_id
 * @param $module_id
 * @param $analytics_element_id
 * @param $resource
 * @param $analytics_id
 */
function triggerAnalytics ($course_id, $module_id, $analytics_element_id, $resource, $analytics_id){
    require_once 'modules/analytics/ParticipationAnalyticsEvent.php';
    require_once 'modules/analytics/BlogAnalyticsEvent.php';
    require_once 'modules/analytics/ForumAnalyticsEvent.php';
    require_once 'modules/analytics/CommentsAnalyticsEvent.php';
    require_once 'modules/analytics/WikiAnalyticsEvent.php';
    require_once 'modules/analytics/ExerciseAnalyticsEvent.php';
    require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
    require_once 'modules/analytics/LpAnalyticsEvent.php';
    require_once 'modules/analytics/FileViewAnalyticsEvent.php';
    require_once 'modules/analytics/Event.php';
    $data = new stdClass();
    $data->course_id = $course_id;
    $data->module_id = $module_id;
    $data->analytics_element_id = $analytics_element_id;
    $data->resource = $resource;
    $data->element_type = $module_id;

    $record = Database::get()->querySingle("SELECT start_date, end_date FROM analytics WHERE id = ?d", $analytics_id);
    $data->start_date = $record->start_date;
    $data->end_date = $record->end_date;

    switch ($module_id) {
        case ANALYTICS_BLOGPOSTS:
            BlogAnalyticsEvent::trigger(BlogAnalyticsEvent::BLOGEVENT, $data, false);
            break;
        case ANALYTICS_BLOGCOMMENTS:
            CommentsAnalyticsEvent::trigger(CommentsAnalyticsEvent::BLOGPOSTCOMMENT, $data, false);
            break;
        case ANALYTICS_COURSECOMMENTS:
            CommentsAnalyticsEvent::trigger(CommentsAnalyticsEvent::COURSECOMMENT, $data, false);
            break;
        case ANALYTICS_WALLCOMMENTS:
            CommentsAnalyticsEvent::trigger(CommentsAnalyticsEvent::WALLPOSTCOMMENT, $data, false);
            break;
        case ANALYTICS_EXERCISEGRADE:
            ExerciseAnalyticsEvent::trigger(ExerciseAnalyticsEvent::EXERCISEGRADE, $data, false);
            break;
        case ANALYTICS_ASSIGNMENTGRADE:
            AssignmentAnalyticsEvent::trigger(AssignmentAnalyticsEvent::ASSIGNMENTGRADE, $data, false);
            break;
        case ANALYTICS_ASSIGNMENTDL:
            AssignmentAnalyticsEvent::trigger(AssignmentAnalyticsEvent::ASSIGNMENTDL, $data, false);
            break;
        case ANALYTICS_FORUMACTIVITY:
            ForumAnalyticsEvent::trigger(ForumAnalyticsEvent::FORUMEVENT, $data, false);
            break;
        case ANALYTICS_WIKIACTIVITY:
            WikiAnalyticsEvent::trigger(WikiAnalyticsEvent::WIKIEVENT, $data, false);
            break;
        case ANALYTICS_FILEVIEW:
            //FileViewAnalyticsEvent::trigger(FileViewAnalyticsEvent::LOGINRECORDED, $data, false);
            break;
        case ANALYTICS_DAILYLOGINS:
            ParticipationAnalyticsEvent::trigger(ParticipationAnalyticsEvent::LOGINRECORDED, $data, false);
            break;
        case ANALYTICS_HITS:
            ParticipationAnalyticsEvent::trigger(ParticipationAnalyticsEvent::HITRECORDED, $data, false);
            break;
        case ANALYTICS_DURATION:
            ParticipationAnalyticsEvent::trigger(ParticipationAnalyticsEvent::DURATIONRECORDED, $data, false);
            break;
        case ANALYTICS_LPPERCENTAGE:
            LpAnalyticsEvent::trigger(LpAnalyticsEvent::LPPERCENTAGE, $data, false);
            break;
    }
}

/**
 * @param $analytics_id
 * @return array
 */
function get_analytics_period($analytics_id) {
    $analyticsPeriod = Database::get()->querySingle("SELECT periodType, start_date, end_date FROM analytics WHERE id=?d", $analytics_id);

    return $analyticsPeriod;
}