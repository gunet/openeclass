<?php

$theme_settings = array(
    'js_loaded' => array('jquery', 'jquery-ui'),
    'classes' => array('tool_active' => 'active',
                      'group_active' => 'in'),
    'icon_map' => array(
        'arrow' => 'fa-caret-right',
        'announcements' => 'fa-bullhorn',
        'calendar' => 'fa-calendar-o',
        'dropbox' => 'fa-envelope-o',
        'docs' => 'fa-folder-open-o',
        'links' => 'fa-link',
        'description' => 'fa-info-circle',
        'forum' => 'fa-comments',
        'assignments' => 'fa-flask',
        'exercise' => 'fa-pencil-square-o',
        'questionnaire' => 'fa-question-circle',
        'ebook' => 'fa-book',
        'videos' => 'fa-film',
        'groups' => 'fa-users',
        'lp' => 'fa-ellipsis-h',
        'conference' => 'fa-exchange',
        'glossary' => 'fa-list',
        'wiki' => 'fa-globe',
        'course_info' => 'fa-cogs',
        'users' => 'fa-cogs',
        'tooladmin' => 'fa-cogs',
        'usage' => 'fa-cogs',
    ),
);

function template_callback($template, $menuTypeID)
{
    global $uid, $session, $native_language_names_init;

    if ($uid) {
        $template->set_block('mainBlock', 'LoggedOutBlock', 'delete');
    } else {
        $template->set_block('mainBlock', 'LoggedInBlock', 'delete');
    }

    if ($menuTypeID != 2) {
        $lang_select = "<li class='dropdown'>
          <a href='#' class='btn btn-default dropdown-toggle' type='button' id='dropdownMenuLang' data-toggle='dropdown'>
              <i class='fa fa-globe'></i>
            <span class='caret'></span>
          </a>
          <ul class='dropdown-menu' role='menu' aria-labelledby='dropdownMenuLang'>";
        foreach ($session->active_ui_languages as $code) {
            $lang_select .=
                "<li role='presentation'>
                    <a role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>" .
                        q($native_language_names_init[$code]) . "</a></li>";
        }
        $lang_select .= "</ul></li>";
        $template->set_var('LANG_SELECT', $lang_select);
    }
}
