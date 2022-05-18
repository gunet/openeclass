<?php

$require_current_course = true;
require_once '../../include/baseTheme.php';

// validate
$content_id = intval($_GET['id']);
$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$content = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d AND course_id = ?d $onlyEnabledWhere", $content_id, $course_id);
if (!$content) {
    redirect_to_home_page("modules/h5p/index.php?course=$course_code");
}

$data = [];
$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$data['action_bar'] = action_bar([
    [ 'title' => $langDownload,
      'url' => $urlServer . "modules/h5p/reuse.php?course=" . $course_code . "&id=" . $content->id,
      'icon' => 'fa-download',
      'level' => 'primary-label',
      'button-class' => 'btn-success',
      'show' => $content->reuse_enabled
    ],
    [ 'title' => $langBack,
      'url' => $backUrl,
      'icon' => 'fa-reply',
      'level' => 'primary-label'
    ]
], false);

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5P];
$data['workspaceUrl'] = $urlAppend . 'courses/' . $course_code . '/h5p/content/' . $content_id . '/workspace';
$data['workspaceLibs'] = $urlAppend . 'courses/h5p/libraries';
$data['content'] = $content;
view('modules.h5p.show', $data);