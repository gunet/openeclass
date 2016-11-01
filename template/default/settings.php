<?php
$theme_settings = array(
    'js_loaded' => array('jquery'),
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
        'wiki' => 'fa-wikipedia',
        'blog' => 'fa-columns',
        'course_info' => 'fa-cogs',
        'users' => 'fa-user',
        'tooladmin' => 'fa-cogs',
        'usage' => 'fa-cogs',
        'attendance' => 'fa-check-square-o',
        'gradebook' => 'fa-sort-numeric-desc',
        'abuse' => 'fa-flag'
    ),
);

function template_callback($template, $menuTypeID, $embed)
{
    global $uid, $session, $native_language_names_init, $course_id, $professor,
           $modules, $admin_modules, $theme_settings, $langChooseLang;

    if ($uid and !defined('UPGRADE')) {
        if (!$embed) {
            $template->set_block('mainBlock', 'LoggedOutBlock', 'delete');
            $template->set_block('mainBlock', 'sideBarCourseBlock', 'sideBarCourse');
            $template->set_block('sideBarCourseBlock', 'sideBarCourseNotifyBlock', 'sideBarCourseNotify');

            // Save sideBarCourseNotifyBlock in session for use in AJAX callback
            $_SESSION['template']['sideBarCourseNotifyBlock'] = trim($template->get_var('sideBarCourseNotifyBlock'));

            // FIXME: smarter selection of courses for sidebar
            Database::get()->queryFunc("SELECT id, code, title, prof_names, public_code
                FROM course, course_user
                WHERE course.id = course_id AND course.visible != " . COURSE_INACTIVE . " AND user_id = ?d
                ORDER BY reg_date DESC", function ($c) use ($template, $modules, $admin_modules, $theme_settings) {
                    global $urlAppend;
                    static $counter = 1;

                    $template->set_var('sideBarCollapseId', $counter);
                    $template->set_var('sideBarCourseURL', $urlAppend . 'courses/' . $c->code . '/');
                    $template->set_var('sideBarCourseTitle', q($c->title));
                    $template->set_var('sideBarCourseCode', q($c->public_code));
                    $template->set_var('sideBarCourseID', q($c->id));
                    $template->set_var('sideBarCourseProf', q($c->prof_names));
                    $template->parse('sideBarCourse', 'sideBarCourseBlock', true);
                    $counter++;
                }, $uid);
        }
    } else {
        $template->set_block('mainBlock', 'LoggedInBlock', 'delete');
        $template->set_block('mainBlock', 'sideBarBlock', 'delete');
    }

    if (!$embed) {
        if (!$course_id or !isset($professor) or !$professor) {
            $template->set_block('mainBlock', 'professorBlock', 'delete');
        } else {
            $template->set_var('PROFESSOR', q($professor));
        }
    }

    if ($menuTypeID != 2) {
        $lang_select = "<li class='dropdown'>
          <a href='#' class='dropdown-toggle' role='button' id='dropdownMenuLang' data-toggle='dropdown'>
              <span class='fa fa-globe'></span><span class='sr-only'>$langChooseLang</span>
          </a>
          <ul class='dropdown-menu' role='menu' aria-labelledby='dropdownMenuLang'>";
        foreach ($session->active_ui_languages as $code) {
            $class = ($code == $session->language)? ' class="active"': '';
            $lang_select .=
                "<li role='presentation'$class>
                    <a role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>" .
                        q($native_language_names_init[$code]) . "</a></li>";
        }
        $lang_select .= "</ul></li>";
        $template->set_var('LANG_SELECT', $lang_select);
    }
}
