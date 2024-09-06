<?php

/**
 * @brief make directories
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3
 * @param type $dirname
 */
function mkdir_try($dirname) {
    global $errorContent, $configErrorExists, $langWarningInstall3, $autoinstall;

    if (!is_dir($dirname)) {
        if (!make_dir($dirname)) {
            if ($autoinstall) {
                echo sprintf($langWarningInstall3, $dirname), "\n";
            } else {
                $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $dirname);
            }
            $configErrorExists = true;
        }
    }
}

/**
 * @brief create files
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3
 * @param type $filename
 */
function touch_try($filename) {
    global $errorContent, $configErrorExists, $langWarningInstall3, $autoinstall;

    if (!@touch($filename)) {
        if ($autoinstall) {
            echo sprintf($langWarningInstall3, $dirname), "\n";
        } else {
            $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $filename);
        }
        $configErrorExists = true;
    }
}

// Create config, courses directories etc.
function create_directories() {
    mkdir_try('config');
    touch_try('config/index.php');
    mkdir_try('storage');
    mkdir_try('storage/views');
    mkdir_try('courses');
    touch_try('courses/index.php');
    mkdir_try('courses/temp');
    touch_try('courses/temp/index.php');
    mkdir_try('courses/temp/pdf');
    mkdir_try('courses/userimg');
    touch_try('courses/userimg/index.php');
    mkdir_try('courses/faculytimg');
    mkdir_try('courses/commondocs');
    touch_try('courses/commondocs/index.php');
    mkdir_try('video');
    touch_try('video/index.php');
    mkdir_try('courses/user_progress_data');
    mkdir_try('courses/user_progress_data/cert_templates');
    touch_try('courses/user_progress_data/cert_templates/index.php');
    mkdir_try('courses/user_progress_data/badge_templates');
    touch_try('courses/user_progress_data/badge_templates/index.php');
    mkdir_try('courses/eportfolio');
    touch_try('courses/eportfolio/index.php');
    mkdir_try('courses/eportfolio/userbios');
    touch_try('courses/eportfolio/userbios/index.php');
    mkdir_try('courses/eportfolio/work_submissions');
    touch_try('courses/eportfolio/work_submissions/index.php');
    mkdir_try('courses/eportfolio/mydocs');
    touch_try('courses/eportfolio/mydocs/index.php');
}

function getenv_default($name, $default) {
    $value = getenv($name);
    if ($value === false) {
        return $default;
    } else {
        return $value;
    }
}
