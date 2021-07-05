<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';

$content_id = $_GET['id'];
$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;
$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => "H5P"];

$tool_content .= action_bar([
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);

$workspaceUrl = $urlAppend . 'courses/' . $course_code . '/h5p/content/' . $content_id . '/workspace';
$workspaceLibs = $urlAppend . 'courses/h5p/libraries';

$head_content .= "
    <link type='text/css' rel='stylesheet' media='all' href='$urlServer/js/h5p-standalone/styles/h5p.css' />
    <script type='text/javascript' src='$urlServer/js/h5p-standalone/main.bundle.js'></script>";

$tool_content .= "<div class='row'>
        <div class='col-xs-12'>
			<div id='h5p-container'></div>
        </div>
    </div>";

$head_content .= "
    <script type='text/javascript'>
        $(document).ready(function() {
            const el = document.getElementById('h5p-container');
            const options = {
              h5pJsonPath:  '$workspaceUrl',
              librariesPath: '$workspaceLibs',
              frameJs: '$urlServer/js/h5p-standalone/frame.bundle.js',
              frameCss: '$urlServer/js/h5p-standalone/styles/h5p.css',
              frame: true,
              copyright: true,
              icon: true,
              fullScreen: true
            };
            new H5PStandalone.H5P(el, options);
        });
    </script>";

draw($tool_content, 2, null, $head_content);
