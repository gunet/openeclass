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


$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
require_once 'include/lib/csv.class.php';
require_once 'process_functions.php';

$element = 'certificate';
$element_id = "$_GET[certificate_id]";

$csv = new CSV();
//$csv->setDebug(false);

if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . "_users_certificate_results.csv";

$cert_title = get_title($element, $_GET['certificate_id']);

if ($element == 'certificate') {
    $csv->outputRecord($cert_title)
            ->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langProgress);

    $sql = Database::get()->queryArray("SELECT user, completed, completed_criteria, total_criteria FROM user_certificate 
                                            WHERE certificate = ?d", $element_id);
    
    foreach ($sql as $user_data) {
        $csv->outputRecord(uid_to_name($user_data->user, 'surname'), 
                           uid_to_name($user_data->user, 'givenname'), 
                           uid_to_am($user_data->user),
                           uid_to_name($user_data->user, 'username'),                
                           uid_to_email($user_data->user),
                           round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . '%');
    $csv->outputRecord();
    }    
}
