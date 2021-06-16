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

$head_content .= "
    <link type='text/css' rel='stylesheet' media='all' href='$urlServer/js/h5p-standalone/styles/h5p.css' />
    <script type='text/javascript' src='$urlServer/js/h5p-standalone/js/h5p-standalone-main.min.js'></script>";

$tool_content .= "<div class='row'>
        <div class='col-xs-12'>
			<div class='h5p-container'></div>
        </div>
    </div>";

$head_content .= "
  <script type='text/javascript'>
    (function($) {
        $(function() {
            $('.h5p-container').h5p({
          frameJs: '$urlServer/js/h5p-standalone/js/h5p-standalone-frame.min.js',
          frameCss: '$urlServer/js/h5p-standalone/styles/h5p.css',
          h5pContent: '$workspaceUrl',
          displayOptions: {
                frame: true,
                copyright : true,
                embed: false,
                download: false,
                icon: true,
                export: false
          }
        });
      });
    })(H5P.jQuery);
  </script>";

draw($tool_content, 2, null, $head_content);
