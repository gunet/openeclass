<?php

define('UPGRADE', true);

require_once '../include/baseTheme.php';
require_once 'functions.php';

set_config('upgrade_begin', time());

if (php_sapi_name() == 'cli' and ! isset($_SERVER['REMOTE_ADDR'])) {
    $command_line = true;
} else {
    $command_line = false;
}

if ($command_line and isset($argv[1])) {
    $logfile_path = $argv[1];
} else {
    $logfile_path = "$webDir/courses";
}

header('Content-Type: application/json; charset=utf-8');

$oldversion = get_config('version');
if (version_compare($oldversion , '3.13', '<=')) {
    $tbl_options = 'DEFAULT CHARACTER SET=utf8 ENGINE=InnoDB';
} else {
    $tbl_options = 'DEFAULT CHARACTER SET=utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
}

$info_message = $error_message = '';
$logfile_path = "$webDir/courses";

/* error handling */
$logdate = date("Y-m-d_G.i.s");
$logfile = "log-$logdate.html";
if (!($logfile_handle = @fopen("$logfile_path/$logfile", 'w'))) {
    $error = error_get_last();
}

fwrite($logfile_handle, "<!DOCTYPE html><html><head><meta charset='UTF-8'>
      <title>Open eClass upgrade log of $logdate</title></head><body>\n");

Debug::setOutput(function ($message, $level) use ($logfile_handle, &$debug_error) {
    fwrite($logfile_handle, $message);
    if ($level > Debug::WARNING) {
        $debug_error = true;
    }
});

// Close HTML body
fwrite($logfile_handle, "\n</body>\n</html>\n");
fclose($logfile_handle);

if ($debug_error) {
    $error_message .= "$langUpgSucNotice <a href='../courses/$logfile' target='_blank'>$langLogOutput</a>";
}

if (version_compare($oldversion, '3.1', '<')) {
    upgrade_to_3_1($tbl_options);
    // unlink files that were used with the old theme import mechanism
    @unlink("$webDir/template/default/img/bcgr_lines_petrol_les saturation.png");
    @unlink("$webDir/template/default/img/eclass-new-logo_atoms.png");
    @unlink("$webDir/template/default/img/OpenCourses_banner_Color_theme1-1.png");
    @unlink("$webDir/template/default/img/banner_Sketch_empty-1-2.png");
    @unlink("$webDir/template/default/img/eclass-new-logo_sketchy.png");
    @unlink("$webDir/template/default/img/Light_sketch_bcgr2-1.png");
    @unlink("$webDir/template/default/img/Open-eClass-4-1-1.jpg");
    @unlink("$webDir/template/default/img/eclass_ice.png");
    @unlink("$webDir/template/default/img/eclass-new-logo_ice.png");
    @unlink("$webDir/template/default/img/ice.png");
    @unlink("$webDir/template/default/img/eclass_classic2-1-1.png");
    @unlink("$webDir/template/default/img/eclass-new-logo_classic.png");
    $version = "3.1";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.2', '<')) {
    upgrade_to_3_2($tbl_options);
    $version = "3.2";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.3', '<')) {
    upgrade_to_3_3($tbl_options);
    $version = "3.3";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.4', '<')) {
    upgrade_to_3_4($tbl_options);
    $version = "3.4";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.5', '<')) {
    upgrade_to_3_5($tbl_options);
    $version = "3.5";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.6', '<')) {
    upgrade_to_3_6($tbl_options);
    $version = "3.6";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}


if (version_compare($oldversion, '3.7', '<')) {
    upgrade_to_3_7($tbl_options);
    $version = "3.7";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.8', '<')) {
    upgrade_to_3_8($tbl_options);
    $version = "3.8";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.9', '<')) {
    upgrade_to_3_9($tbl_options);
    $version = "3.9";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.10', '<')) {
    upgrade_to_3_10($tbl_options);
    $version = "3.10";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.11', '<')) {
    upgrade_to_3_11($tbl_options);
    $version = "3.11";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.12', '<')) {
    upgrade_to_3_12($tbl_options);

    // install h5p content
    $hubUpdater = new H5PHubUpdater();
    $hubUpdater->fetchLatestContentTypes();
    set_config('h5p_update_content_ts', date('Y-m-d H:i', time()));
    // create directory indexes to hinder directory traversal in misconfigured servers
    addDirectoryIndexFiles();

    $version = "3.12";
    set_config('version', $version);
    if ($command_line) {
        echo "$langUpgForVersion $version -- $langH5pInstall $error_message\n\n";
    } else {
        $data = array(
            "status" => 1,
            "message" => "$langUpgForVersion $version -- $langH5pInstall",
            "error" => $error_message
        );
        echo json_encode($data);
    }
    exit();
}

if (version_compare($oldversion, '3.13', '<')) {
    upgrade_to_3_13($tbl_options);
    // change database encoding to utf8mb4
    convert_db_encoding_to_utf8mb4();
    // Ensure that all stored procedures about hierarchy are up and running!
    refreshHierarchyProcedures();
    // create appropriate indices
    create_indexes();
    // Import new themes
    importThemes();
    if (!get_config('theme_options_id')) {
        set_config('theme_options_id', Database::get()->querySingle('SELECT id FROM theme_options WHERE name = ?s', 'Open eClass 2022 - Default')->id);
    }
    finalize_upgrade();

    set_config('version', ECLASS_VERSION);
    set_config('upgrade_begin', '');
    if ($command_line) {
        echo "$langUpgForVersion " . ECLASS_VERSION . " -- $langChangeDBEncoding $error_message\n\n";
    } else {
        $data = array(
            "status" => 0,
            "message" => "$langUpgForVersion " . ECLASS_VERSION . " -- $langChangeDBEncoding",
            "error" => $error_message
        );
        echo json_encode($data);
    }

    exit();
}
