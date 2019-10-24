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

function headlink($label, $this_sort) {
    global $sort, $reverse, $course_code, $themeimg, $langUp, $langDown;
    $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code;

    if ($sort == $this_sort) {
        $this_reverse = !$reverse;
        $indicator = " <img src='$themeimg/arrow_" .
                ($reverse ? 'up' : 'down') . ".png' alt='" .
                ($reverse ? $langUp : $langDown) . "'>";
    } else {
        $this_reverse = $reverse;
        $indicator = '';
    }
    return '<a href="' . $base_url .
            '&amp;sort=' . $this_sort . ($this_reverse ? '&amp;rev=1' : '') .
            '">' . $label . $indicator . '</a>';
}