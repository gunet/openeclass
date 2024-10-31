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

function getInfoAreas() {
    return "<div class='progress'>
      <div id='progress-bar' class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='45' aria-valuemin='0' aria-valuemax='100' style='width: 100%'>
        0%
      </div>
    </div><div id='progressbar-info'></div>";
}

function updateInfo($percent, $infoText, $debug = true) {
    global $command_line;
    static $last = null;

    if ($debug) {
        Debug::message($infoText, Debug::WARNING);
    }
    $percentageText = ($percent == 1)? '100': sprintf('%2.0f', $percent * 100);
    if (isset($command_line) and $command_line) {
        if ($percent < 0) {
            $percentageText = ' * ';
        } else {
            $percentageText .= '%';
        }
        $output = $percentageText . ' ' . $infoText;
        if ($last != $output) {
            echo $output, "\n";
            $last = $output;
        }
    } else {
        echo '<script>';
        if ($percent >= 0) {
            echo 'document.getElementById("progress-bar").style="width:' . ($percent * 100) . '%;";', "\n",
                 'document.getElementById("progress-bar").innerHTML ="' . $percentageText . '%";', "\n";
        }
        if ($percent == 1) {
            echo 'document.getElementById("progress-bar").className = "progress-bar progress-bar-striped";';
        }
        echo 'document.getElementById("progressbar-info").innerHTML="' . addslashes($infoText) . '";</script>';
    }

    // This is for the buffer achieve the minimum size in order to flush data
    //    echo str_repeat(' ', 1024 * 64);

    // Send output to browser immediately
    flush();
}
