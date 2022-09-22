<?php

set_time_limit(0);

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/h5p/classes/H5PHubUpdater.php';

$toolName = $langH5pInteractiveContent;

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => 'extapp.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label')
), false);

if (isset($_GET['update']) and $_GET['update']) {
    $hubUpdater = new H5PHubUpdater();
    $hubUpdater->fetchLatestContentTypes();
    set_config('h5p_update_content_ts', date('Y-m-d H:i', time()));
    $tool_content .= "<div class='alert alert-info text text-center'>$langH5pUpdateComplete</div>";
} else {
    $ts = get_config('h5p_update_content_ts');
    $tool_content .= "
        <div class='col-sm-12'>
        <div class='alert alert-info'>$langH5pInfoUpdate</div>
            <div class='text-center'>            
                <a class='btn btn-success' href='$_SERVER[SCRIPT_NAME]?update=true' data-placement='bottom' data-toggle='tooltip' title='$langMaj'>
                    <span class='fa fa-refresh space-after-icon'></span>
                    <span class='hidden-xs'>$langMaj</span>                    
                </a>";
                if ($ts) {
                    $tool_content .= "<span class='help-block'><em>$langlastUpdated: " . format_locale_date(strtotime($ts), 'short', false) . "</em></span>";
                }
            $tool_content .= "</div>
        </div>";
}

draw($tool_content, 3, null, $head_content);
