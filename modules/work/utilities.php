<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
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


/**
 * @brief Returns an array of the details of assignment $id
 * @param type $id
 * @return type
 */
function get_assignment_details($id) {
    global $course_id;
    return Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
}

/**
 * @brief Check if a file has been submitted by user uid or by the user's group, and has been graded.
 * Returns the submission id or the whole submission details row (depending on ret_val),
 * or FALSE if no graded assignments were found.
 * @param $uid
 * @param $id
 * @param $ret_val
 * @return false|mixed|void
 */
function was_graded($uid, $id, $ret_val = FALSE) {
    global $course_id;
    $res =Database::get()->queryArray("SELECT * FROM assignment_submit
                                  WHERE assignment_id = ?d AND (uid = ?d OR
                                    group_id IN (SELECT group_id FROM `group` AS grp,
                                        group_members AS members
                                        WHERE grp.id = members.group_id AND
                                        user_id = ?d AND course_id = ?d))", $id, $uid, $uid, $course_id);
    if ($res) {
        foreach ($res as $row) {
            if ($row->grade) {
                if ($ret_val) {
                    return $row;
                } else {
                    return $row->id;
                }
            }
        }
    } else {
        return FALSE;
    }
}


/**
 * @brief  Check if a file has been submitted by user uid or group gid
 *  for assignment id. Returns 'user' if by user, 'group' if by group
 * @param $uid
 * @param $gid
 * @param $id
 * @return false|string
 */
function was_submitted($uid, $gid, $id) {

    $q = Database::get()->querySingle("SELECT uid, group_id
          FROM assignment_submit
          WHERE assignment_id = ?d AND
                (uid = ?d or group_id = ?d)", $id, $uid, $gid);
    if ($q) {
        if ($q->uid == $uid) {
            return 'user';
        } else {
            return 'group';
        }
    } else {
        return false;
    }
}


/**
 * @brief check if rubrics exist in course
 * @return bool
 */
function rubrics_exist(): bool
{

    global $course_id;

    $q = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d", $course_id);

    if ($q) {
        return true;
    } else {
        return false;
    }
}


/**
 * @brief check if grading scales exist in course
 * @return bool
 */
function grading_scales_exist(): bool
{

    global $course_id;

    $q = Database::get()->querySingle("SELECT * FROM grading_scale WHERE course_id = ?d", $course_id);

    if ($q) {
        return true;
    } else {
        return false;
    }
}

// Returns an array of numbers like [ 2 => 2, 3 => 3, ... ] to use as file count options
function fileCountOptions(): array
{
    return array_slice(range(0, get_config('max_work_file_count', 10)), 2, null, true);
}

/**
 * @brief Valitron rule to match IPv4/6 and IPv4/6 CIDR ranges
 * @param string $field field name (ignored)
 * @param array $value array of IPs
 * @param array $params ignored
 */
function ipORcidr($field, $value, array $params) {
    foreach ($value as $ip){
        $valid = isIPv4($ip) || isIPv4cidr($ip) || isIPv6($ip) || isIPv6cidr($ip);
        if (!$valid) {
            return false;
        }
    }
    return true;
}

/**
 * @brief Show a table header which is a link with the appropriate sorting
parameters - $attrib should contain any extra attributes requered in
the <th> tags
 * @param type $title
 * @param type $opt
 * @param type $attrib
 */
function sort_link($title, $opt, $attrib = '') {

    global $course_code;

    $i = '';
    if (isset($_REQUEST['id'])) {
        $i = "&id=$_REQUEST[id]";
    }
    if (@($_REQUEST['sort'] == $opt)) {
        if (@($_REQUEST['rev'] == 1)) {
            $r = 0;
        } else {
            $r = 1;
        }
        $html = "<th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt&rev=$r$i'>" . "$title</a></th>";
    } else {
        $html = "<th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt$i'>$title</a></th>";
    }
    return $html;
}

function max_grade_from_scale($scale_id) {
    global $course_id;
    $scale_data = Database::get()->querySingle("SELECT * FROM grading_scale WHERE id = ?d AND course_id = ?d", $scale_id, $course_id);
    $unserialized_scale_items = unserialize($scale_data->scales);
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $item) {
        if ($item['scale_item_value'] > $max_scale_item_value) {
            $max_scale_item_value = $item['scale_item_value'];
        }
    }
    return $max_scale_item_value;
}

function max_grade_from_rubric($rubric_id) {
    global $course_id;
    $rubric_data = Database::get()->querySingle("SELECT * FROM rubric WHERE id = ?d AND course_id = ?d", $rubric_id, $course_id);
    $unserialized_scale_items = unserialize($rubric_data->scales);
    $max_grade = 0;
    $max_scale_item_value = 0;
    foreach ($unserialized_scale_items as $CritArrItems) {
        $max_scale_item_value = 0;
        if(is_array($CritArrItems['crit_scales'] ))
            foreach($CritArrItems['crit_scales'] as $scalesArr){
                $max_scale_item_value = $max_scale_item_value<$scalesArr['scale_item_value']?$scalesArr['scale_item_value']:$max_scale_item_value;
            }
        $max_grade = $max_grade + $CritArrItems['crit_weight'] * $max_scale_item_value;
    }
    return $max_grade/100;
}

// Remove extension and directory from filename
function basename_noext($f) {
    return preg_replace('{\.[^\.]*$}', '', basename($f));
}

// Disallow '..' and initial '/' in filenames
function cleanup_filename($f) {
    if (preg_match('{/\.\./}', $f) or
        preg_match('{^\.\./}', $f)) {
        die("Error: up-dir detected in filename: $f");
    }
    $f = preg_replace('{^/+}', '', $f);
    return preg_replace('{//}', '/', $f);
}


/**
 * @brief Find secret subdir of this assignment - if a secret subdir isn't set,
 * use the assignment's id instead. Also insures that secret subdir exists
 * @param $id
 * @return mixed|void
 */
function work_secret($id) {
    global $course_id, $workPath, $coursePath;

    $res =  Database::get()->querySingle("SELECT secret_directory FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($res) {
        if (!empty($res->secret_directory)) {
            $s = $res->secret_directory;
        } else {
            $s = $id;
        }
        if (!is_dir("$workPath/$s")) {
            make_dir("$workPath/$s");
        }
        return $s;
    } else {
        die("Error: group $gid doesn't exist");
    }
}



/**
 * @brief Count number of submissions to an assignment
 * @param int $assignment_id
 * @return int
 */
function countSubmissions($assignment_id) {
    $num_submitted = Database::get()->querySingle('SELECT COUNT(*) AS count FROM (
            SELECT uid, group_id FROM assignment_submit
            WHERE assignment_id = ?d GROUP BY uid, group_id
        ) AS distinct_submissions', $assignment_id);
    if ($num_submitted) {
        return $num_submitted->count;
    } else {
        return 0;
    }
}

/**
 * @brief Count ungraded assignment submissions
 * @param $assignment_id
 * @return string
 */
function countUngradedSubmissions($assignment_id) {

    $num_ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count 
                    FROM assignment_submit 
                    WHERE assignment_id = ?d 
                    AND grade IS NULL", $assignment_id);
    if ($num_ungraded) {
        return $num_ungraded->count;
    } else {
        return '-';
    }

    return $num_ungraded;
}


/**
 * @brief Count number of submitted files for a submission where submission_type = multiple files
 * @param integer $sub_id - a database id of a submission
 * @return integer $count
 */
function submission_count($sub_id): int
{
    $sub = Database::get()->querySingle('SELECT assignment_id, uid, group_id
        FROM assignment_submit WHERE id = ?d', $sub_id);
    return Database::get()->querySingle('SELECT COUNT(*) AS cnt
        FROM assignment_submit
        WHERE assignment_id = ?d AND
              (uid = ?d OR group_id = ?d)',
        $sub->assignment_id, $sub->uid, $sub->group_id)->cnt;
}

/**
 * @brief get assignment scale options with user grade (if exist)
 * @param $row
 * @param $grade
 * @return string
 */
function get_scale_options($grading_scale_id, $grade) {
    global $course_id;

    $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $grading_scale_id, $course_id)->scales;
    $scales = unserialize($serialized_scale_data);
    $scale_options = "<option value> - </option>";
    $scale_values = array_value_recursive('scale_item_value', $scales);
    if (!in_array($grade, $scale_values) && !is_null($grade)) {
        $grade = closest($grade, $scale_values)['value'];
    }
    foreach ($scales as $scale) {
        $scale_options .= "<option value='$scale[scale_item_value]'".($scale['scale_item_value'] == $grade ? " selected" : "").">$scale[scale_item_name]</option>";
    }
    return $scale_options;
}


/**
 * @brief Auto Judge function
 * @param $scenarionAssertion
 * @param $scenarioInputResult
 * @param $scenarioOutputExpectation
 * @return bool
 */
function doScenarioAssertion($scenarionAssertion, $scenarioInputResult, $scenarioOutputExpectation) {
    switch($scenarionAssertion) {
        case 'eq':
            $assertionResult = ($scenarioInputResult == $scenarioOutputExpectation);
            break;
        case 'same':
            $assertionResult = ($scenarioInputResult === $scenarioOutputExpectation);
            break;
        case 'notEq':
            $assertionResult = ($scenarioInputResult != $scenarioOutputExpectation);
            break;
        case 'notSame':
            $assertionResult = ($scenarioInputResult !== $scenarioOutputExpectation);
            break;
        case 'integer':
            $assertionResult = (is_int($scenarioInputResult));
            break;
        case 'float':
            $assertionResult = (is_float($scenarioInputResult));
            break;
        case 'digit':
            $assertionResult = (ctype_digit($scenarioInputResult));
            break;
        case 'boolean':
            $assertionResult = (is_bool($scenarioInputResult));
            break;
        case 'notEmpty':
            $assertionResult = (empty($scenarioInputResult) === false);
            break;
        case 'notNull':
            $assertionResult = ($scenarioInputResult !== null);
            break;
        case 'string':
            $assertionResult = (is_string($scenarioInputResult));
            break;
        case 'startsWith':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === 0);
            break;
        case 'endsWith':
            $stringPosition  = mb_strlen($scenarioInputResult, 'utf8') - mb_strlen($scenarioOutputExpectation, 'utf8');
            $assertionResult = (mb_strripos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === $stringPosition);
            break;
        case 'contains':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8'));
            break;
        case 'numeric':
            $assertionResult = (is_numeric($scenarioInputResult));
            break;
        case 'isArray':
            $assertionResult = (is_array($scenarioInputResult));
            break;
        case 'true':
            $assertionResult = ($scenarioInputResult === true);
            break;
        case 'false':
            $assertionResult = ($scenarioInputResult === false);
            break;
        case 'isJsonString':
            $assertionResult = (json_decode($value) !== null && JSON_ERROR_NONE === json_last_error());
            break;
        case 'isObject':
            $assertionResult = (is_object($scenarioInputResult));
            break;
    }

    return $assertionResult;
}

/**
 * @brief check for valid plagiarism file type
 * @param type $file_id
 * @return boolean
 */
function valid_plagiarism_file_type($file_id) {

    $unplag_allowable_file_extensions = array('doc', 'docx', 'rtf', 'txt', 'odt', 'html', 'pdf');

    $file_details = Database::get()->querySingle("SELECT file_name FROM assignment_submit WHERE id = ?d", $file_id);
    if ($file_details) {
        $file_type = get_file_extension($file_details->file_name);
        if (in_array($file_type, $unplag_allowable_file_extensions)) {
            return TRUE;
        }
    }
    return FALSE;
}


/**
 * @brief check for plagiarism via unicheck (aka 'unplag') tool (http://www.unicheck.com)
 * @param $submission
 * @return string
 */
function get_unplag_plagiarism_results($submission) {

    global $langPlagiarismResult, $langDownloadToPDF;

    $plagiarismlink = '';

    if (get_config('ext_unicheck_enabled') and valid_plagiarism_file_type($submission)) {
        $results = Plagiarism::get()->getResults($submission);
        if ($results) {
            if ($results->ready) {
                $plagiarismlink = "<small><a href='$results->resultURL' target=_blank>$langPlagiarismResult</a><br>(<a href='$results->pdfURL' target=_blank>$langDownloadToPDF</a>)</small>";
            }
        }
    }
    return $plagiarismlink;
}
