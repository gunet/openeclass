<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
  @file lib.diff.php
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */

define("DIFF_EQUAL", "=");
define("DIFF_ADDED", "+");
define("DIFF_DELETED", "-");
define("DIFF_MOVED", "M");

/**
 * Get difference between two strings
 * @param string old first string
 * @param string new second string
 * @param boolean show_equals set to true to see line that are equal between
 *      the two strings (default true)
 * @param string format_line_function callback function to format line
 *      (default 'format_line')
 * @return string formated diff output
 */
function diff($old, $new, $show_equals = false, $format_line_function = 'format_line') {
    $oldArr = str_split_on_new_line($old);
    $newArr = str_split_on_new_line($new);

    $oldCount = count($oldArr);
    $newCount = count($newArr);

    $max = max($oldCount, $newCount);

    //get added and deleted lines

    $deleted = array_diff_assoc($oldArr, $newArr);
    $added = array_diff_assoc($newArr, $oldArr);

    $moved = array();

    foreach ($added as $key => $candidate) {
        foreach ($deleted as $index => $content) {
            if ($candidate == $content) {
                $moved[$key] = $candidate;
                unset($added[$key]);
                unset($deleted[$index]);
                break;
            }
        }
    }

    $output = '';

    for ($i = 0; $i < $max; $i++) {
        // line changed
        if (isset($deleted[$i]) && isset($added[$i])) {
            $output .= $format_line_function($i, DIFF_DELETED, $deleted[$i]);
            $output .= $format_line_function($i, DIFF_ADDED, $added[$i]);
        }
        // line deleted
        elseif (isset($deleted[$i]) && !isset($added[$i])) {
            $output .= $format_line_function($i, DIFF_DELETED, $deleted[$i]);
        }
        // line added
        elseif (isset($added[$i]) && !isset($deleted[$i])) {
            $output .= $format_line_function($i, DIFF_ADDED, $added[$i]);
        }
        // line moved
        elseif (isset($moved[$i])) {
            $output .= $format_line_function($i, DIFF_MOVED, $newArr[$i]);
        }
        // line unchanged
        elseif ($show_equals == true) {
            $output .= $format_line_function($i, DIFF_EQUAL, $newArr[$i]);
        } else {
            // skip
        }
    }

    return $output;
}

/**
 * Split strings on new line
 */
function str_split_on_new_line($str) {
    $content = array();

    if (strpos($str, "\r\n") != false) {
        $content = explode("\r\n", $str);
    } elseif (strpos($str, "\n") != false) {
        $content = explode("\n", $str);
    } elseif (strpos($str, "\r") != false) {
        $content = explode("\r", $str);
    } else {
        $content[] = $str;
    }

    return $content;
}

/**
 * Default and prototype format line function
 * @param int line line number
 * @param mixed type line type, must be one of the following :
 *      DIFF_EQUAL, DIFF_MOVED, DIFF_ADDED, DIFF_DELETED
 * @param string value line content
 * @param boolean skip_empty skip empty lines (default false)
 * @return string formated diff line
 */
function format_line($line, $type, $value, $skip_empty = false) {
    if (trim($value) == "" && $skip_empty) {
        return "";
    } elseif (trim($value) == "") {
        $value = '&nbsp;';
    }

    switch ($type) {
        case DIFF_EQUAL: {
                return $line . ' : ' . ' = <span class="diffEqual" >' . q($value) . '</span><br />' . "\n";
            }
        case DIFF_MOVED: {
                return $line . ' : ' . ' M <span class="diffMoved" >' . q($value) . '</span><br />' . "\n";
            }
        case DIFF_ADDED: {
                return $line . ' : ' . ' + <span class="diffAdded" >' . q($value) . '</span><br />' . "\n";
            }
        case DIFF_DELETED: {
                return $line . ' : ' . ' - <span class="diffDeleted" >' . q($value) . '</span><br />' . "\n";
            }
    }
}

/**
 * Table format line function
 * @see format_line
 */
function format_table_line($line, $type, $value, $skip_empty = false) {
    if (trim($value) == "" && $skip_empty) {
        return "";
    } elseif (trim($value) == "") {
        $value = '&nbsp;';
    }

    switch ($type) {
        case DIFF_EQUAL: {
                return '<tr><td>' . $line . '&nbsp;:&nbsp;' . '&nbsp;=</td><td><span class="diffEqual" >'
                        . q($value) . '</span></td></tr>' . "\n"
                ;
            }
        case DIFF_MOVED: {
                return '<tr><td>' . $line . '&nbsp;:&nbsp;' . '&nbsp;M</td><td><span class="diffMoved" >'
                        . q($value) . '</span></td></tr>' . "\n"
                ;
            }
        case DIFF_ADDED: {
                return '<tr><td>' . $line . '&nbsp;:&nbsp;' . '&nbsp;+</td><td><span class="diffAdded" >'
                        . q($value) . '</span></td></tr>' . "\n"
                ;
            }
        case DIFF_DELETED: {
                return '<tr><td>' . $line . '&nbsp;:&nbsp;' . '&nbsp;-</td><td><span class="diffDeleted" >'
                        . q($value) . '</span></td></tr>' . "\n"
                ;
            }
    }
}