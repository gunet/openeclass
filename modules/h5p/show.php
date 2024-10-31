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

$require_current_course = true;
$guest_allowed = true;
require_once '../../include/baseTheme.php';

$unit = isset($_GET['unit'])? intval($_GET['unit']): null;
$res_type = isset($_GET['res_type']);

// validate
$content_id = intval($_GET['id']);
$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$content = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d AND course_id = ?d $onlyEnabledWhere", $content_id, $course_id);
if (!$content) {
    redirect_to_home_page("modules/h5p/index.php?course=$course_code");
}

if (!$res_type) {
    $backUrl = $urlAppend . 'modules/h5p/index.php?course=' . $course_code;
} else {
    $backUrl = $urlAppend . 'modules/units/index.php?course=' . $course_code . '&id=' . $unit;
}

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5p];

$tool_content .= action_bar([
    [ 'title' => $langBack,
      'url' => $backUrl,
      'icon' => 'fa-reply',
      'level' => 'primary'
    ],
    [ 'title' => $langDownload,
      'url' => $urlServer . "modules/h5p/reuse.php?course=" . $course_code . "&id=" . $content->id,
      'icon' => 'fa-download',
      'level' => 'primary-label',
      'button-class' => 'btn-success',
      'show' => ($content->reuse_enabled && $is_editor)
    ]
], false);

$workspaceUrl = $urlAppend . 'courses/' . $course_code . '/h5p/content/' . $content_id . '/workspace';
$workspaceLibs = $urlAppend . 'courses/h5p/libraries';

$head_content .= "
    <script type='text/javascript' src='" . $urlAppend . "js/h5p-standalone/dist/main.bundle.js'></script>";

$tool_content .= "
        <div class='col-12'>
            <div id='h5p-container'></div>
        </div>";

$head_content .= "
    <script type='text/javascript'>
        $(document).ready(function() {
            const el = document.getElementById('h5p-container');
            const options = {
              h5pJsonPath:  '$workspaceUrl',
              librariesPath: '$workspaceLibs',
              frameJs: '" . $urlAppend . "js/h5p-standalone/dist/frame.bundle.js',
              frameCss: '" . $urlAppend . "js/h5p-standalone/dist/styles/h5p.css',
              frame: true,
              copyright: true,
              icon: true,
              fullScreen: true,
              export: " . (($content->reuse_enabled && $is_editor) ? "true" : "false") . ",
              downloadUrl: '" . $urlServer . "modules/h5p/reuse.php?course=" . $course_code . "&id=" . $content->id . "'
            };

            new H5PStandalone.H5P(el, options).then(() => {
                setTimeout(function() {
                    let ifcont = $('#h5p-container').find('iframe').first().contents()[0];
                    let jaxscr = ifcont.createElement('script');
                    jaxscr.type = 'text/javascript';
                    jaxscr.src = '" . $urlAppend . "node_modules/mathjax/es5/tex-chtml.js';
                    jaxscr.id = 'MathJax-script';
                    ifcont.head.appendChild(jaxscr);
                }, 40);
            });
        });
    </script>";

draw($tool_content, 2, null, $head_content);
