<?php

function getInfoAreas() {
    return '<div id="progressbar-outer"><div id="progressbar-inner"></div></div><div id="progressbar-info"></div>';
}

function updateInfo($percent, $infoText, $debug = true) {
    if ($debug)
        Debug::message($infoText, Debug::WARNING);
    echo '<script language="javascript">';
    if ($percent >= 0) {
        echo 'document.getElementById("progressbar-inner").style="width:' . ($percent * 100) . '%;";';
    }
    echo 'document.getElementById("progressbar-info").innerHTML="' . addslashes($infoText) . '";</script>
';
    // This is for the buffer achieve the minimum size in order to flush data
//    echo str_repeat(' ', 1024 * 64);
    // Send output to browser immediately
    flush();
}
