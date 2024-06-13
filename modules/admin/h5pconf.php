<?php

set_time_limit(0);

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/h5p/classes/H5PHubUpdater.php';

$toolName = $langH5pInteractiveContent;

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

if (isset($_GET['update']) and $_GET['update']) {
    $hubUpdater = new H5PHubUpdater();
    $hubUpdater->fetchLatestContentTypes();
    set_config('h5p_update_content_ts', date('Y-m-d H:i', time()));
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langH5pUpdateComplete</span></div></div>";
} else {
    $ts = get_config('h5p_update_content_ts');
    $tool_content .= "
        <div class='col-sm-12'>
        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langH5pInfoUpdate</span></div>
            <div class='d-flex justify-content-center'>            
                <a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?update=true' data-bs-placement='bottom' data-bs-toggle='tooltip' title='$langMaj'>
                    <span class='fa fa-refresh space-after-icon settings-icons'></span>
                    <span class='hidden-xs TextBold ps-1'>$langMaj</span>                    
                </a>";
                if ($ts) {
                    $tool_content .= "<span class='help-block ps-2 text-success fw-bold'><em>$langlastUpdated: " . format_locale_date(strtotime($ts), 'short', false) . "</em></span>";
                }
            $tool_content .= "</div>
        </div>";
}

draw($tool_content, 3, null, $head_content);
