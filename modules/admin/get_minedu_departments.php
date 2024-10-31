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

require_once '../../include/baseTheme.php';

$qtype = isset($_GET['qtype']) ? $_GET['qtype'] : null;

switch ($qtype) {

    case 'Institution':
        $result = Database::get()->queryArray('SELECT * FROM minedu_departments ORDER BY Institution ASC');

        $uniqueInstitutions = array();

        if ($result) {
            foreach ($result as $r) {
                $institutionName = $r->Institution;
                if (!isset($uniqueInstitutions[$institutionName])) {
                    $uniqueInstitutions[$institutionName] = 0;
                }
                $uniqueInstitutions[$institutionName]++;
            }
        }

        $ajax_results = array();
        foreach ($uniqueInstitutions as $institutionName => $count) {
            $ajax_results[] = array(
                'Institution' => $institutionName,
                'Count' => $count
            );
        }
        break;

    case 'School':
        $Institution = isset($_GET['Institution']) ? $_GET['Institution'] : null;
        $result = Database::get()->queryArray("SELECT MineduID, Department, School
            FROM minedu_departments
            WHERE Institution = ?s AND MineduID REGEXP '^[0-9]+$'
            ORDER BY School ASC", $Institution);

        $ajax_results = array();
        if ($result) {
            foreach ($result as $r) {
                $department = !empty($r->School) ? $r->School . ' - ' . $r->Department : $r->Department;
                $ajax_results[] = array(
                    'MineduID' => $r->MineduID,
                    'Department' => $department
                );
            }
        }
        break;

    default:
        echo json_encode('Invalid query type');
        die;
}

echo json_encode($ajax_results);
