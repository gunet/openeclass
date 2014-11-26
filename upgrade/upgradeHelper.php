<?php

function getInfoAreas() {
    return '<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<div id="information" style="width"></div>
';
}

function updateInfo($percent, $infoText, $debug = true) {
    if ($debug)
        Debug::message($infoText, Debug::WARNING);
    echo '<script language="javascript">';
    if ($percent >= 0) {
        echo 'document.getElementById("progress").innerHTML="<div style=\"width:' . ($percent * 100) . '%;background-color:#ddd;\">&nbsp;</div>";';
    }
    echo 'document.getElementById("information").innerHTML="' . addslashes($infoText) . '";</script>
';
    // This is for the buffer achieve the minimum size in order to flush data
//    echo str_repeat(' ', 1024 * 64);
    // Send output to browser immediately
    flush();
}
