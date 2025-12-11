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

function zip_offline_directory($zip_filename, $downloadDir) {
    global $public_code;
    $zipfile = new ZipArchive();
    if ($zipfile->open($zip_filename, ZipArchive::CREATE) !== true) {
        die("error: cannot open $zip_filename");
    }

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($downloadDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        // Skip directories (they will be added automatically)
        if (!$file->isDir()) {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = remove_filename_unsafe_chars($public_code . '-offline') . "/" . substr($filePath, strlen($downloadDir) + 1);

            // Add current file to archive
            $zipfile->addFile($filePath, $relativePath);
        }
    }

    $zipfile->close();
}

/**
 * @brief get / render documents
 * @param type $curDirPath
 * @param type $curDirName
 * @param type $curDirPrefix
 * @param type $bladeData
 */
function offline_documents($curDirPath, $curDirName, $curDirPrefix, $bladeData) {
    global $blade, $webDir, $course_id, $course_code, $downloadDir,
           $langDownloadDir, $langSave,
           $theme_data;

    copyright_info_init();
    // doc init
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    if (!file_exists($downloadDir . '/modules/' . $curDirName)) {
        mkdir($downloadDir . '/modules/' . $curDirName);
    }

    $files = $dirs = array();
    $result = Database::get()->queryArray("SELECT id, path, TRIM(filename) AS filename, format,
                                        title, extra_path, course_id,
                                        date_modified, public, visible,
                                        editable, copyrighted, comment,
                                        IF((title = '' OR title IS NULL), TRIM(filename), title) AS sort_key
                                FROM document
                                WHERE
                                      course_id = ?d AND
                                      path LIKE ?s AND
                                      path NOT LIKE ?s ORDER BY sort_key COLLATE utf8mb4_general_ci", $course_id, $curDirPath . "/%", $curDirPath . "/%/%");
    foreach ($result as $row) {
        $is_dir = $row->format == '.dir';
        if ($real_path = common_doc_path($row->extra_path, true)) {
            $path = $real_path;
        } else {
            $path = $basedir . $row->path;
        }
        if (!$real_path and $row->extra_path) {
            // external file
            $size = 0;
        } else {
            $size = file_exists($path) ? filesize($path): 0;
            if (file_exists($path) && !$is_dir) {
                copy($path, $downloadDir . '/modules/' . $curDirName . '/' . $row->filename);
            }
        }

        $info = array(
            'is_dir' => $is_dir,
            'size' => format_file_size($size),
            'title' => $row->sort_key,
            'filename' => trim($row->filename),
            'format' => $row->format,
            'path' => $row->path,
            'extra_path' => $row->extra_path,
            'visible' => ($row->visible == 1),
            'public' => $row->public,
            'comment' => $row->comment,
            'date' => format_locale_date(strtotime($row->date_modified), 'short', false),
            'date_time' => format_locale_date(strtotime($row->date_modified), 'short'),
            'editable' => $row->editable,
            'updated_message' => '');

        if ($row->extra_path) {
            $info['common_doc_path'] = common_doc_path($row->extra_path); // sets global $common_doc_visible
            $info['common_doc_visible'] = $GLOBALS['common_doc_visible'];
        }

        if (!$row->extra_path or $info['common_doc_path']) { // Normal or common document
            $download_url = $curDirPrefix . '/' . $row->filename;
        } else { // External document
            $download_url = $row->extra_path;
        }

        $downloadMessage = $row->format == '.dir' ? $langDownloadDir : $langSave;
        if ($row->format != '.dir') {
            $info['action_button'] = icon('fa-download', $downloadMessage, $download_url);
        }

        $info['copyrighted'] = false;
        if ($is_dir) {
            $info['icon'] = 'fa-folder';
            $info['url'] = $curDirPrefix . '/' . $row->filename . ".html";
            $newData = $bladeData;
            $newData['urlAppend'] .= '../';
            $newData['template_base'] = $newData['urlAppend'] . 'template/modern';
            $newData['themeimg'] = $newData['urlAppend'] . 'resources/img';
            if (!empty($theme_data['logo_img'])) {
                $newData['logo_img'] = $newData['urlAppend'] . $theme_data['logo_img'];
            } else {
                $newData['logo_img'] = $newData['themeimg'] . '/eclass-new-logo.svg';
            }
            if (!empty($theme_data['logo_img_small'])) {
                $newData['logo_img_small'] = $newData['urlAppend'] . $theme_data['logo_img_small'];
            } else {
                $newData['logo_img_small'] = $newData['themeimg'] . '/eclass-new-logo.svg';
            }
            $newData['toolArr'] = lessonToolsMenu_offline(true, $newData['urlAppend']);
            offline_documents($row->path, $curDirName . '/' . $row->filename, $row->filename, $newData);

            $dirs[] = (object) $info;
        } else {
            $info['icon'] = choose_image('.' . $row->format);
            $GLOBALS['group_sql'] = "course_id = $course_id AND subsystem = " . MAIN;
            $info['link'] = "<a href='$download_url' title='".q($row->filename)."'>" . $row->filename . "</a>";

            $copyid = $row->copyrighted;
            if ($copyid and $copyid != 2) {
                $info['copyrighted'] = true;
                $info['copyright_icon'] = ($copyid == 1) ? 'fa-copyright' : 'fa-cc';
                $info['copyright_title'] = $GLOBALS['copyright_titles'][$copyid];
                $info['copyright_link'] = $GLOBALS['copyright_links'][$copyid];
            }

            $files[] = (object) $info;
        }
    }
    $bladeData['fileInfo'] = array_merge($dirs, $files);
    $bladeData['curDirPath'] = $curDirPath;
    $docout = $blade->make('modules.document.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/' . $curDirName . '.html', 'w');
    fwrite($fp, $docout);
    fclose($fp);
}


/**
 * @brief get / render announcements
 * @global type $blade
 * @global type $course_id
 * @global type $downloadDir
 * @global type $theme_data
 * @param type $bladeData
 */
function offline_announcements($bladeData) {
    global $blade, $course_id, $downloadDir, $theme_data;

    $bladeData['urlAppend'] = '../';
    $bladeData['template_base'] = '../template/modern';
    $bladeData['themeimg'] = '../resources/img';
    if (!empty($theme_data['logo_img'])) {
        $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
    } else {
        $bladeData['logo_img'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
    }
    if (!empty($theme_data['logo_img_small'])) {
        $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
    } else {
        $bladeData['logo_img_small'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
    }

    $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

    $bladeData['announcements'] = $announcements = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d
                                                AND visible = 1
                                                AND (start_display <= NOW() OR start_display IS NULL)
                                                AND (stop_display >= NOW() OR stop_display IS NULL)
                                            ORDER BY `order` DESC , `date` DESC", $course_id);


    $out = $blade->make('modules.announcements.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/announcements.html', 'w');
    fwrite($fp, $out);

    if (is_array($announcements) && !empty($announcements) && count($announcements) > 0) {
        if (!file_exists($downloadDir . '/modules/announcement/')) {
            mkdir($downloadDir . '/modules/announcement/');
        }

        foreach ($announcements as $a) {
            $bladeData['urlAppend'] = '../../';
            $bladeData['template_base'] = '../../template/modern';
            $bladeData['themeimg'] = '../../resources/img';
            if (!empty($theme_data['logo_img'])) {
                $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
            } else {
                $bladeData['logo_img'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
            }
            if (!empty($theme_data['logo_img_small'])) {
                $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
            } else {
                $bladeData['logo_img_small'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
            }
            $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);
            $bladeData['ann_title'] = $a->title;
            $bladeData['ann_body'] = $a->content;
            $bladeData['ann_date'] = $a->date;
            $out = $blade->make('modules.announcements.ann', $bladeData)->render();
            $fp = fopen($downloadDir . '/modules/announcement/' . $a->id . '.html', 'w');
            fwrite($fp, $out);
        }
    }

}


/**
 * @brief get / render videos
 * @param array $bladeData
 */
function offline_videos($bladeData) {
    global $blade, $course_id, $downloadDir, $webDir, $course_code;

    // video file copy
    $basedir = $webDir . '/video/' . $course_code;
    mkdir($downloadDir . '/modules/video');

    $result = Database::get()->queryArray("select * from video WHERE course_id = ?d AND visible = 1", $course_id);
    foreach ($result as $row) {
        copy($basedir . $row->path, $downloadDir . '/modules/video/' . $row->url);
    }

    // module business logic
    $bladeData['is_editor'] = $is_editor = false;
    $bladeData['is_in_tinymce'] = $is_in_tinymce = false;
    $bladeData['filterv'] = $filterv = 'WHERE true';
    $bladeData['filterl'] = $filterl = 'WHERE true';
    $bladeData['order'] = $order = 'ORDER BY title';
    $bladeData['compatiblePlugin'] = $compatiblePlugin = true;
    $bladeData['count_video'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM video $filterv AND course_id = ?d", $course_id)->count;
    $bladeData['count_video_links'] = Database::get()->querySingle("SELECT count(*) AS count FROM videolink $filterl AND course_id = ?d", $course_id)->count;
    $bladeData['num_of_categories'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `video_category` WHERE course_id = ?d", $course_id)->count;
    $bladeData['items'] = getLinksOfCategory(0, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin); // uncategorized items
    $bladeData['categories'] = Database::get()->queryArray("SELECT * FROM `video_category` WHERE course_id = ?d ORDER BY name", $course_id);

    $out = $blade->make('modules.video.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/video.html', 'w');
    fwrite($fp, $out);
}


/**
 * @brief get course units
 * @global type $course_id
 */
function offline_course_units() {

    global $course_id;

    $data = Database::get()->queryArray("SELECT id, title, comments, visible, public, `order` FROM course_units
                                WHERE course_id = ?d
                                AND visible = 1
                                AND `order` >= 0
                                ORDER BY `order`", $course_id);

    return $data;
}

/**
 * @brief get / render unit resources from a given course unit
 * @param type $unit_id
 * @param type $downloadDir
 */
function offline_unit_resources($bladeData, $downloadDir) {

    global $course_id, $blade, $theme_data;

    $bladeData['urlAppend'] = '../../';
    $bladeData['template_base'] = '../../template/modern';
    $bladeData['themeimg'] = '../../resources/img';
    if (!empty($theme_data['logo_img'])) {
        $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
    } else {
        $bladeData['logo_img'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
    }
    if (!empty($theme_data['logo_img_small'])) {
        $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
    } else {
        $bladeData['logo_img_small'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
    }
    $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

    $data = Database::get()->queryArray("SELECT id, title, comments, visible, public, `order` FROM course_units
                                WHERE course_id = ?d
                                AND visible = 1
                                AND `order` >= 0
                                ORDER BY `order`", $course_id);

    if (count($data) > 0) {
        if (!file_exists($downloadDir . '/modules/unit/')) {
           mkdir($downloadDir . '/modules/unit/');
        }
        foreach ($data as $cu) {
            $bladeData['next_unit_title'] = $bladeData['next_unit_link'] = $bladeData['prev_unit_title'] = $bladeData['prev_unit_link'] = '';
            $cu_next = Database::get()->querySingle("SELECT id, title FROM course_units WHERE course_id = ?d "
                                       . "AND visible = 1 "
                                       . "AND `order` > ?d "
                                       . "ORDER BY `order` ASC "
                                       . "LIMIT 1"
                                       , $course_id, $cu->order);
            if ($cu_next) {
                $bladeData['next_unit_title'] = $cu_next->title;
                $bladeData['next_unit_link'] = $cu_next->id . ".html";
            }
            $cu_prev = Database::get()->querySingle("SELECT id, title FROM course_units WHERE course_id = ?d "
                                       . "AND visible = 1 "
                                       . "AND `order` < ?d "
                                       . "ORDER BY `order` DESC "
                                       . "LIMIT 1"
                                       , $course_id, $cu->order);
            if ($cu_prev) {
                $bladeData['prev_unit_title'] = $cu_prev->title;
                $bladeData['prev_unit_link'] = $cu_prev->id . ".html";
            }
            $bladeData['course_unit_title'] = $cu->title;
            $bladeData['course_unit_comments'] = $cu->comments;
            $bladeData['unit_resources'] = Database::get()->queryArray("SELECT title, comments, res_id, `type` FROM unit_resources "
                                . "WHERE unit_id = ?d AND visible = 1 "
                                . "AND `type` NOT IN ('poll', 'work', 'forum')"
                                . "ORDER BY `order`", $cu->id);
            $out = $blade->make('modules.unit', $bladeData)->render();
            $fp = fopen($downloadDir . '/modules/unit/' . $cu->id . '.html', 'w');
            fwrite($fp, $out);
        }
    }
}


/**
 * @brief get / render course exercises
 * @global type $blade
 * @global type $downloadDir
 * @global type $course_id
 * @global type $webDir
 * @global type $langScore
 * @global type $langExerciseDone
 * @global type $theme_data
 * @param type $bladeData
 */

function offline_exercises($bladeData) {
    global $blade, $downloadDir, $course_id, $webDir, $langScore, $langExerciseDone, $theme_data;

    $bladeData['exercises'] = $exercises = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d AND active = 1 ORDER BY start_date DESC", $course_id);

    $out = $blade->make('modules.exercise.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/exercise.html', 'w');
    fwrite($fp, $out);

    if (is_array($exercises) && !empty($exercises) && count($exercises) > 0) {
        if (!file_exists($downloadDir . '/modules/exercise/')) {
            mkdir($downloadDir . '/modules/exercise/');
        }

        $basedir = $webDir . '/modules/learnPath/export/';
        copy($basedir . 'scores.js', $downloadDir . '/modules/exercise/scores.js');
        copy($basedir . 'APIWrapper.js', $downloadDir . '/modules/exercise/APIWrapper.js');

        foreach ($exercises as $e) {
            $bladeData['urlAppend'] = '../../';
            $bladeData['template_base'] = '../../template/modern';
            $bladeData['themeimg'] = '../../resources/img';
            if (!empty($theme_data['logo_img'])) {
                $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
            } else {
                $bladeData['logo_img'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
            }
            if (!empty($theme_data['logo_img_small'])) {
                $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
            } else {
                $bladeData['logo_img_small'] = $bladeData['urlAppend'] . 'resources/img/eclass-new-logo.svg';
            }
            $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

            $quiz = new Exercise();
            if (!$quiz->read($e->id)) {
                continue;
            }
            $questionList = $quiz->selectQuestionList();
            $questions = array();
            foreach ($questionList as $questionId) {
                $question = new Question();
                if (!$question->read($questionId)) {
                    continue;
                }
                $questions[] = $question;
            }
            $bladeData['questions'] = $questions;
            $bladeData['langScore'] = $langScore;
            $bladeData['langExerciseDone'] = $langExerciseDone;

            $out = $blade->make('modules.exercise.exer', $bladeData)->render();
            $fp = fopen($downloadDir . '/modules/exercise/' . $e->id . '.html', 'w');
            fwrite($fp, $out);
        }
    }
}

function offline_ebook($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->make('modules.ebook.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/ebook.html', 'w');
    fwrite($fp, $out);

}

function offline_agenda($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->make('modules.agenda.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/agenda.html', 'w');
    fwrite($fp, $out);
}

function offline_blog($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->make('modules.blog.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/blog.html', 'w');
    fwrite($fp, $out);

}



/**
 * @brief get course description
 * @global type $blade
 * @global type $course_id
 * @param array $bladeData
 * @param type $downloadDir
 */
function offline_description($bladeData, $downloadDir) {
    global $blade, $course_id;

    $bladeData['course_description'] = Database::get()->queryArray("SELECT id, title, comments, type, visible FROM course_description "
                                . "WHERE course_id = ?d "
                                . "AND visible = 1 "
                                . "ORDER BY `order`", $course_id);

    $out = $blade->make('modules.course_description.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/course_description.html', 'w');
    fwrite($fp, $out);
}

/**
 * @brief get course links
 * @global type $blade
 * @global type $course_id
 * @param type $bladeData
 * @param type $downloadDir
 */
function offline_links($bladeData, $downloadDir) {
    global $blade, $course_id;

    $bladeData['numberofzerocategory'] = $numberofzerocategory = count(Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND (category = 0 OR category IS NULL)", $course_id));
    if ($numberofzerocategory !== 0) {
        $bladeData['result_zero_category'] = Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = 0 ORDER BY `order`", $course_id);
    }
    $bladeData['resultcategories'] = $resultcategories = Database::get()->queryArray("SELECT * FROM `link_category` WHERE course_id = ?d ORDER BY `order`", $course_id);
    $bladeData['aantalcategories'] = $aantalcategories = count($resultcategories);

    foreach ($resultcategories as $cat) {
        $cat_data = Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = ?d ORDER BY `order`", $course_id, $cat->id);
        if (count($cat_data) > 0) {
            foreach ($cat_data as $link_data) {
                $bladeData['result_link_category'][$cat->id] = $cat_data;
            }
        }
    }

    $bladeData['social_bookmarks_enabled'] = $social_bookmarks_enabled = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);
    if ($social_bookmarks_enabled == 1) {
        $bladeData['numberofsocialcategory'] = $numberofsocialcategory = count(Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = ?d", $course_id, -2));
        if ($numberofsocialcategory !== 0) {
            $bladeData['result_social_category'] = Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = -2 ORDER BY `order`", $course_id);
        }
    }

    $out = $blade->make('modules.link.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/link.html', 'w');
    fwrite($fp, $out);
}



function offline_wiki($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->make('modules.wiki.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/wiki.html', 'w');
    fwrite($fp, $out);
}

/**
 * @brief get glossary terms
 * @param array $bladeData
 * @param type $downloadDir
 */
function offline_glossary($bladeData, $downloadDir) {

    global $blade, $course_id;

    $categories = $prefixes = array();
    Database::get()->queryFunc("SELECT id, name, description, `order`
                          FROM glossary_category WHERE course_id = ?d
                          ORDER BY name", function ($cat) use (&$categories) {
                            $categories[intval($cat->id)] = $cat->name;
                        }, $course_id);
    $bladeData['categories'] = $categories;


    Database::get()->queryFunc("SELECT DISTINCT UPPER(LEFT(term, 1)) AS prefix
                          FROM glossary WHERE course_id = ?d
                          ORDER BY prefix", function ($prefix) use (&$prefixes) {
        $prefix = remove_accents($prefix->prefix);
        if (array_search($prefix, $prefixes) === false) {
            $prefixes[] = $prefix;
        }
    }, $course_id);

    if (count($prefixes) > 0) {
        $html_prefix = '';
        $begin = true;
        foreach ($prefixes as $letter) {
            $html_prefix .= ($begin ? '' : ' | ') .
                    ($begin ? "<a href='glossary.html'>" : "<a href='glossary_" . preg_replace('/%/', '_', urlencode($letter)) . ".html'>" ) .
                    q($letter) . "</a>";
            $bladeData['prefixes'] = $html_prefix;
            $begin = false;
        }
        $begin = true;
        foreach ($prefixes as $letter) {
            $bladeData['glossary'] = $q = Database::get()->queryArray("SELECT id, term, definition, url, notes, category_id
                                FROM glossary WHERE course_id = ?d AND term LIKE '$letter%'
                                GROUP BY term, definition, url, notes, category_id, id
                                ORDER BY term", $course_id);
            $out = $blade->make('modules.glossary.index', $bladeData)->render();
            if ($begin) {
                $fp = fopen($downloadDir . "/modules/glossary.html", 'w');
            } else {
                $fp = fopen($downloadDir . "/modules/glossary_" . preg_replace('/%/', '_', urlencode($letter)) . ".html", 'w');
            }
            fwrite($fp, $out);
            $begin = false;
        }
    }
}



/**
 *
 * @global string $group_sql
 * @param type $type
 * @param type $res_id
 * @return string
 */
function get_unit_resource_link($type, $res_id) {

    global $group_sql;

    $link = '';
    $group_sql = "true";
    switch ($type) {
        case 'doc':
            $doc = Database::get()->querySingle("SELECT path, filename FROM document WHERE id = ?d", $res_id);
            $link = "../document/" . public_file_path($doc->path, $doc->filename);
            break;
        case 'video':
            $link = Database::get()->querySingle("SELECT url FROM video WHERE id = ?d", $res_id)->url;
            $link = "../video/" . $link;
            break;
        case 'link':
            $link = Database::get()->querySingle("SELECT url FROM link WHERE id = ?d", $res_id)->url;
            break;
        case 'videolink':
            $link = Database::get()->querySingle("SELECT url FROM videolink WHERE id = ?d", $res_id)->url;
            break;
        case 'exercise':
            $link = "../exercise/index.html";
            break;
        case 'wiki':
            $link = "../wiki/index.html";
            break;
    }
    return $link;

}

/**
 * @brief display icon for given unit resource
 * @param type $type
 * @param type $res_id
 * @return string
 */
function get_unit_resource_icon($type, $res_id) {

    $icon = '';
    switch ($type) {
        case 'doc':
            $icon = 'fa-file';
            break;
        case 'video':
            $icon = 'fa-film';
            break;
        case 'link':
        case 'videolink':
            $icon = 'fa-link';
            break;
        case 'exercise':
            $icon = 'fa-square-pen';
            break;
        case 'wiki':
            $icon = 'fa-won-sign';
            break;
        case 'glossary':
            $icon = 'fa-list';
            break;
        case 'blog':
            $icon = 'fa-columns';
            break;
        case 'calendar':
            $icon = 'fa-calendar-o';
            break;
        case 'forum':
            $icon = 'fa-comments';
            break;
    }
    return "fa $icon";
}

/**
 * @brief get theme options
 * @return type
 */
function get_theme_options() {

    global $urlServer;

    $data = [];

    $theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
        $urlThemeData = 'theme_data/' . $theme_id;
        $urlThemeDataForModules = '../theme_data/' .$theme_id;
        $urlThemeDataForModulesContent = '../../theme_data/' .$theme_id;

        $styles_str = '';
        $styles_str .= "

            #bgr-cheat-header {
                z-index: 4;
            }

            .modal-backdrop.show{
                z-index: 0;
            }

            .modal,
            .modal-dialog {
                z-index: 1060;
            }
            
            .banner-image{
                display: none;
            }

            @media (min-width: 992px){
                .main-section {
                    min-height: calc(100vh - 80px - 80px);
                    padding-top: 0px;
                }
            }
        
        ";


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OR BACKGROUND IMAGE OF BODY ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColor']) || !empty($theme_options_styles['bgImage'])) {
            $background_type = "";
            if (isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'stretch') {
                $background_type .= "background-size: 100% 100%;";
            } elseif(isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'fix') {
                $background_type .= "background-size: 100% 100%;background-attachment: fixed;";
            }
            $bg_image = isset($theme_options_styles['bgImage']) ? " url('$urlThemeData/$theme_options_styles[bgImage]')" : "";
            $bg_image_module = isset($theme_options_styles['bgImage']) ? " url('$urlThemeDataForModules/$theme_options_styles[bgImage]')" : "";
            $bg_image_module_content = isset($theme_options_styles['bgImage']) ? " url('$urlThemeDataForModulesContent/$theme_options_styles[bgImage]')" : "";
            $bg_color = isset($theme_options_styles['bgColor']) ? $theme_options_styles['bgColor'] : "#ffffff";
            $styles_str .= "
                                body{
                                    background: $bg_color$bg_image;$background_type
                                }
                                body:has(.col_maincontent_active_module){
                                    background: $bg_color$bg_image_module;$background_type
                                }
                                body:has(.col_maincontent_active_module_content){
                                    background: $bg_color$bg_image_module_content;$background_type
                                }
                                .main-container,
                                .module-container{
                                    background-color: $bg_color;
                                }
                            ";
        }

        $gradient_str = 'radial-gradient(closest-corner at 30% 60%, #009BCF, #025694)';


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// FLUID OR BOXED SIZE OF PLATFORM ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['fluidContainerWidth'])){
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// SHOW - HIDE ECLASS_BANNER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['openeclassBanner'])){
            $styles_str .= "#openeclass-banner {display: none;}";
            $eclass_banner_value = 0;
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////// TYPOGRAPHY ///////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ColorHyperTexts'])){
            $styles_str .= "
                body,
                h1,h2,h3,h4,h5,h6,
                p,strong,.li-indented,li,small,
                .Neutral-900-cl,
                .agenda-comment,
                .form-label, 
                .default-value,
                label,
                th,
                td,
                .panel-body,
                .card-body,
                div,
                .visibleFile,
                .list-group-item,
                .help-block,
                .control-label-notes,
                .title-default,
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    color:$theme_options_styles[ColorHyperTexts];
                }


                .dataTables_wrapper .dataTables_length, 
                .dataTables_wrapper .dt-search, 
                .dataTables_wrapper .dataTables_info, 
                .dataTables_wrapper .dataTables_processing, 
                .dataTables_wrapper .dataTables_paginate {
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .circle-img-contant{
                    border: solid 1px $theme_options_styles[ColorHyperTexts];
                }

                .text-muted,
                .input-group-text{
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .c3-tooltip-container *{
                    background-color: #ffffff;
                    color: #2B3944;
                }

                .panel-default .panel-heading .panel-title, 
                .panel-action-btn-default .panel-heading .panel-title {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND-COLOR HEADER'S WRAPPER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['BgColorWrapperHeader'])) {
            $styles_str .= "

                #bgr-cheat-header{ 
                    background-color: $theme_options_styles[BgColorWrapperHeader];
                }

                .offCanvas-Tools{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

                .navbar-learningPath,
                .header-container-learningPath{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR FOOTER'S WRAPPER /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorWrapperFooter'])) {
            $styles_str .= "

                #bgr-cheat-footer,
                .div_social{
                    background-color: $theme_options_styles[bgColorWrapperFooter];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// LINKS COLOR OF HEADER ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorHeader'])){
            $styles_str .= "


                .link-selection-language,
                .link-bars-options,
                .user-menu-btn .user-name,
                .user-menu-btn .fa-chevron-down{
                    color: $theme_options_styles[linkColorHeader];
                }

                .container-items .menu-item{
                    color: $theme_options_styles[linkColorHeader];
                }

                #search_terms,
                #search_terms::placeholder{
                    color:$theme_options_styles[linkColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass{
                    color:$theme_options_styles[linkColorHeader];
                }

                .header-login-text{
                    color:$theme_options_styles[linkColorHeader];
                }

                .header-mobile-link{
                    color:$theme_options_styles[linkColorHeader];
                }

                .split-left,
                .split-content{
                    border-left: solid 1px $theme_options_styles[linkColorHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF ACTIVE LINK HEADER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveBgColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    background-color: $theme_options_styles[linkActiveBgColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR OF ACTIVE LINK HEADER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    color: $theme_options_styles[linkActiveColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// COLOR OF HOVER LINK IN HEADER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorHeader'])){
            $styles_str .= "
                .link-selection-language:hover,
                .link-selection-language:focus,
                .link-bars-options:hover,
                .link-bars-options:focus,
                .container-items .menu-item:hover,
                .container-items .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .user-name,
                .user-menu-btn:focus .user-name{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .fa-chevron-down,
                .user-menu-btn:focus .fa-chevron-down{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass:hover,
                #bgr-cheat-header .fa-magnifying-glass:focus {
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .header-login-text:hover,
                .header-login-text:focus{
                    color:$theme_options_styles[linkHoverColorHeader];
                }

                .header-mobile-link:hover,
                .header-mobile-link:focus{
                    color:$theme_options_styles[linkHoverColorHeader];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO ACTIVE LINK IN HEADER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['HoveredActiveLinkColorHeader'])){
            $styles_str .= "

                .container-items .menu-item.active:hover,
                .container-items .menu-item.active:focus,
                .container-items .menu-item.active2:hover,
                .container-items .menu-item.active2:focus{
                    color: $theme_options_styles[HoveredActiveLinkColorHeader];
                } 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// SHADOW TO THE BOTTOM SIDE INTO HEADER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['shadowHeader'])){
            $styles_str .= " 
                #bgr-cheat-header{ box-shadow: none; }
            ";
        }else{
            $styles_str .= " 
                #bgr-cheat-header{ box-shadow: 1px 2px 6px rgba(43,57,68,0.04); }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// LINKS COLOR OF FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item {
                    color: $theme_options_styles[linkColorFooter];
                }

                .copyright, 
                .social-icon-tool, 
                .a_tools_site_footer {
                    color:$theme_options_styles[linkColorFooter];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR OF HOVER LINK IN FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item:hover,
                .container-items-footer .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorFooter];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus {
                    color: $theme_options_styles[linkHoverColorFooter];
                }

                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////// TEXT COLOR OF TABS /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link{
                    color: $theme_options_styles[clTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// HOVERED TEXT COLOR OF TABS /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link:hover{
                    color: $theme_options_styles[clHoveredTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACTIVE TABS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link.active{
                    color: $theme_options_styles[clActiveTabs];
                    border-bottom: solid 2px $theme_options_styles[clActiveTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACCORDIONS  //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn{
                    color: $theme_options_styles[clAccordions];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TEXT OF ACCORDIONS  ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomAccordions'])){
            $styles_str .= "
                .group-section .list-group-item{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }

                .border-bottom-default{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TEXT OF ACCORDIONS  /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn:hover{
                    color: $theme_options_styles[clHoveredAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR TEXT OF ACTIVE ACCORDIONS  //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn[aria-expanded='true'], 
                .group-section .list-group-item .accordion-btn.showAll{
                    color: $theme_options_styles[clActiveAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF LIST GROUP //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgLists'])){
            $styles_str .= "
                .list-group-item.list-group-item-action{
                    background-color: $theme_options_styles[bgLists];
                }
                .list-group-item.list-group-item-action:hover{
                    background-color: $theme_options_styles[bgLists];
                }

                .list-group-item.element{
                    background-color: $theme_options_styles[bgLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }

                .profile-pers-info-row{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR LINK OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a,
                .list-group-item.element a{
                    color: $theme_options_styles[clLists];
                }

                .list-group-item.list-group-action a span,
                .list-group-item.element a span{
                    color: $theme_options_styles[clLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// HOVERED COLOR LINK OF LIST GROUP ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a:hover,
                .list-group-item.element a:hover{
                    color: $theme_options_styles[clHoveredLists];
                }

                .list-group-item.list-group-item-action a span:hover,
                .list-group-item.element a span:hover{
                    color: $theme_options_styles[clHoveredLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// ADD PADDING TO THE LIST GROUP /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['AddPaddingListGroup'])){
            $styles_str .= " 
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .homepage-annnouncements-container .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            ";
        }else{
            $styles_str .= " 
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF WHITE BUTTON ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgWhiteButtonColor'])) {
            $styles_str .= "
                .submitAdminBtn, 
                .cancelAdminBtn,
                .opencourses_btn {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn-outline-primary {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .quickLink{
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .menu-popover{
                    background: $theme_options_styles[bgWhiteButtonColor];
                }

                .bs-placeholder.submitAdminBtn{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .showSettings{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn.btn-default {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// TEXT COLOR OF WHITE BUTTON ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn, 
                .cancelAdminBtn,
                .opencourses_btn {
                    border-color: $theme_options_styles[whiteButtonTextColor];
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    border-color: $theme_options_styles[whiteButtonTextColor] !important;
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn-outline-primary {
                    border-color: $theme_options_styles[whiteButtonTextColor];
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .submitAdminBtn .fa-solid::before, 
                .submitAdminBtn .fa-regular::before,
                .submitAdminBtn .fa-brands::before,
                .submitAdminBtn span.fa::before{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .quickLink{
                    border: solid 1px $theme_options_styles[whiteButtonTextColor];
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .menu-popover{
                    border: solid 1px $theme_options_styles[whiteButtonTextColor];
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .bs-placeholder .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .showSettings{
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn.btn-default {
                    border-color: $theme_options_styles[whiteButtonTextColor];
                    color: $theme_options_styles[whiteButtonTextColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// HOVERED TEXT COLOR OF WHITE BUTTON //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover, 
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus, 
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .submitAdminBtn .fa-solid::before:hover, 
                .submitAdminBtn .fa-regular::before:hover,
                .submitAdminBtn .fa-brands::before:hover,
                .submitAdminBtn span.fa::before:hover{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    border: solid 1px $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .bs-placeholder:hover .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .showSettings:hover{
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// HOVERED BACKGROUND COLOR OF WHITE BUTTON ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover, 
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus, 
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .bs-placeholder.submitAdminBtn:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .showSettings:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF COLORFUL BUTTON ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonBgColor'])) {
            $styles_str .= "
                .submitAdminBtn.active{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnDefault{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .login-form-submit{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnDefault, 
                input[type=submit], 
                button[type=submit]{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnClassic.active {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }
               

                .carousel-indicators>button.active {
                    border-color: tranparent;
                    background-color: $theme_options_styles[buttonBgColor];
                }


                .pagination-glossary .page-item.active .page-link {
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }
               
                .bootbox.show .modal-footer .submitAdminBtn, 
                .modal.show .modal-footer .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .btn.btn-primary{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                
                .searchGroupBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .myProfileBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }
                  

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED BACKCKGROUND COLOR OF COLORFUL BUTTON ////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonHoverBgColor'])) {
            $styles_str .= "
                
                submitAdminBtn.active:hover{
                border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                
                .submitAdminBtnDefault:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                
                
                .login-form-submit:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                
                
                .submitAdminBtnDefault:hover,
                input[type=submit]:hover,
                button[type=submit]:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
    
                
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover, 
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor] ;
                    background-color: $theme_options_styles[buttonHoverBgColor] ;
                }
                
        
                
                .pagination-glossary .page-item.active .page-link:hover {
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }
            
                
                
                .bootbox.show .modal-footer .submitAdminBtn:hover, 
                .modal.show .modal-footer .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .btn.btn-primary:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .searchGroupBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }


                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF COLORFUL BUTTON //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn.active, 
                .submitAdminBtn.active:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnDefault, 
                .submitAdminBtnDefault:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .login-form-submit, 
                .login-form-submit:hover{
                    color: $theme_options_styles[buttonTextColor];
                }
                
                input[type=submit], 
                input[type=submit]:hover,
                button[type=submit],
                button[type=submit]:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnClassic.active {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover, 
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor];
                }
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .pagination-glossary .page-item.active .page-link,
                .pagination-glossary .page-item.active .page-link:hover {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .bootbox.show .modal-footer .submitAdminBtn, 
                .bootbox.show .modal-footer .submitAdminBtn:hover, 
                .modal.show .modal-footer .submitAdminBtn,
                .modal.show .modal-footer .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .btn.btn-primary,
                .btn.btn-primary:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .submitAdminBtnDefault span,
                .submitAdminBtnDefault span:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .submitAdminBtnDefault .fa-solid::before, 
                .submitAdminBtnDefault .fa-solid::before:hover,
                .submitAdminBtnDefault .fa-regular::before,
                .submitAdminBtnDefault .fa-regular::before:hover,
                .submitAdminBtnDefault .fa-brands::before,
                .submitAdminBtnDefault .fa-brands::before:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .searchGroupBtn span{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .myProfileBtn,
                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    color: $theme_options_styles[buttonTextColor] ;
                }
                
            ";
        }

        // Override button with white background if needed
        if (empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    border-color: #0073E6;
                    background-color: #ffffff;
                    color: #0073E6;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR CONTEXTUAL MENU ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    background-color: $theme_options_styles[bgContextualMenu];
                }

                .contextual-menu-user::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[bgContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR CONTEXTUAL MENU /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgBorderContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-menu-user{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-border{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BACKGROUND COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    background-color: $theme_options_styles[bgColorListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// COLOR TOOL CONTEXTUAL MENU //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    color: $theme_options_styles[clListMenu];
                }

                .contextual-menu .list-group-item .settings-icons::before{
                    color: $theme_options_styles[clListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    background-color: $theme_options_styles[bgHoveredListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    color: $theme_options_styles[clHoveredListMenu];
                }
                .contextual-menu .list-group-item:hover .settings-icons::before{
                    color: $theme_options_styles[clHoveredListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// USERNAME COLOR CONTEXTUAL MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuUsername'])) {
            $styles_str .= "
                .contextual-menu-user .username-text,
                .contextual-menu-user .username-paragraph{
                    color:$theme_options_styles[clListMenuUsername];
                } 
 
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgRadios'])){
            $styles_str .= "
                input[type='radio']{
                    background-color: $theme_options_styles[BgRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderRadios'])){
            $styles_str .= "
                input[type='radio']{
                    border: solid 1px $theme_options_styles[BgBorderRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// TEXT COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClRadios'])){
            $styles_str .= "
                .radio label{ 
                    color: $theme_options_styles[ClRadios];
                } 

                input[type='radio']{
                    color:  $theme_options_styles[ClRadios];
                }

                .radio:not(:has(input[type='radio']:checked)) .help-block{
                    color: $theme_options_styles[ClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND AND TEXT COLOR TO ACTIVE RADIO COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgClRadios'])){
            $styles_str .= "
                input[type='radio']:checked { 
                    border: solid 6px $theme_options_styles[BgClRadios];
                }
                .input-StatusCourse:checked{
                    box-shadow: inset 0 0 0 0px #e8e8e8;
                    border: 0px solid #e8e8e8;
                    background-color: $theme_options_styles[BgClRadios];
                }
                .form-wrapper.form-edit label:has(input[type='radio']:checked){
                    color: $theme_options_styles[BgClRadios];
                }
                
                .radio:has(input[type='radio']:checked) .help-block{
                    color: $theme_options_styles[BgClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// ICON COLOR TO ACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconRadios'])){
            $styles_str .= "
                .radio:has(.input-StatusCourse:checked) .fa{
                    color: $theme_options_styles[ClIconRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO INACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveRadios'])){
            $styles_str .= "
                label:has(input[type='radio']:disabled){
                    color: $theme_options_styles[ClInactiveRadios];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    background-color: $theme_options_styles[BgCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO CHECKBOX COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    border: 1px solid $theme_options_styles[BgBorderCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO CHECKBOX COMPOENENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClCheckboxes'])){
            $styles_str .= "
                .label-container {
                    color: $theme_options_styles[ClCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgActiveCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                    background-color: $theme_options_styles[BgActiveCheckboxes];
                }
                .label-container > input[type='checkbox']:active {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClActiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:checked){
                    color: $theme_options_styles[ClActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// ICON COLOR TO ACTIVE CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked + .checkmark::before {
                    color: $theme_options_styles[ClIconCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO INACTIVE CHECKBOX COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:disabled){
                    color: $theme_options_styles[ClInactiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO INPUT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    background-color: $theme_options_styles[BgInput];
                }
                
                textarea,
                textarea.form-control{
                    background-color: $theme_options_styles[BgInput];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    background-color: $theme_options_styles[BgInput];
                }

                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    background-color: $theme_options_styles[BgInput] !important;
                }
                
                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .add-on,
                .add-on1,
                .add-on2{
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .input-group-text.bg-input-default{
                    background-color: $theme_options_styles[BgInput];
                }

                .form-control:disabled {
                    background-color: $theme_options_styles[BgInput];
                }
                

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BORDER COLOR TO INPUT COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    border-color: $theme_options_styles[clBorderInput];
                }
                
                textarea,
                textarea.form-control{
                    border-color: $theme_options_styles[clBorderInput];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    border-color: $theme_options_styles[clBorderInput];
                }

                input:-webkit-autofill, 
                input:-webkit-autofill:hover, 
                input:-webkit-autofill:focus, 
                textarea:-webkit-autofill, 
                textarea:-webkit-autofill:hover, 
                textarea:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderInput];
                }


                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .input-border-color {
                    border-color: $theme_options_styles[clBorderInput] ;
                }

                .form-control:disabled {
                    border-color: $theme_options_styles[clBorderInput] ;
                }
                

                .wallWrapper textarea:focus{
                    border-color: $theme_options_styles[clBorderInput] ;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO INPUT COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clInputText'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .form-control::placeholder,
                .login-input::placeholder,
                .login-input,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    color: $theme_options_styles[clInputText];
                }
                
                textarea,
                textarea::placeholder,
                textarea.form-control{
                    color: $theme_options_styles[clInputText];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    color: $theme_options_styles[clInputText];
                }

                input:-webkit-autofill, 
                input:-webkit-autofill:hover, 
                input:-webkit-autofill:focus, 
                textarea:-webkit-autofill, 
                textarea:-webkit-autofill:hover, 
                textarea:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clInputText];
                }



                .dataTables_wrapper input::placeholder{
                    color: $theme_options_styles[clInputText] !important;
                }
                  
                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    color: $theme_options_styles[clInputText] !important;
                }
                
                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    color: $theme_options_styles[clInputText] !important;
                }

                .input-group-text .fa-calendar{
                    color: $theme_options_styles[clInputText];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO SELECT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgSelect'])){
            $styles_str .= "

                select.form-select {
                    background-color: $theme_options_styles[BgSelect];
                }
              
                select.form-select:focus {
                    background-color: $theme_options_styles[BgSelect];
                }

                .dataTables_wrapper select {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }


                .select2-selection.select2-selection--multiple{ 
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-dropdown--below {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    background-color: $theme_options_styles[BgSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO SELECT COMBONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderSelect'])){
            $styles_str .= "

                select.form-select {
                    border-color: $theme_options_styles[clBorderSelect];
                }
              
                select.form-select:focus {
                    border-color: $theme_options_styles[clBorderSelect];
                }

                .dataTables_wrapper select {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{ 
                    border-color: $theme_options_styles[clBorderSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;
                }

                select:-webkit-autofill:hover, 
                select:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderSelect];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelect'])){
            $colorChevronDown = "$theme_options_styles[clOptionSelect]";
            $mySVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='$colorChevronDown' d='M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z'/%3E%3C/svg";
            $mysvg2 = 'url("data:image/svg+xml,%3C' . $mySVG .'%3E")';
            $styles_str .= "

                select.form-select {
                    color: $theme_options_styles[clOptionSelect];
                    background-image: $mysvg2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
              
                select.form-select:focus {
                    color: $theme_options_styles[clOptionSelect];
                }

                select.form-select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect];
                }

                .dataTables_wrapper select {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{ 
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .select2-selection--multiple:before {
                    border-top: 5px solid $theme_options_styles[clOptionSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                select:-webkit-autofill:hover, 
                select:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clOptionSelect];
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// HOVERED BACKGROUND COLOR TO OPTION OF SELECT COMBONENT /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// HOVERED TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// BACKGROUND COLOR TO ACTIVE OPTION OF SELECT COMBONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }


            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR TO ACTIVE OPTION OF SELECT COMBONENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    color: $theme_options_styles[clOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    color: $theme_options_styles[clOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    color: $theme_options_styles[clOptionSelected] !important;
                }




            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO FORM COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgForms'])){
            $styles_str .= "
                .form-wrapper.form-edit { 
                    background-color: $theme_options_styles[BgForms];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BORDER COLOR TO FORM COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderForms'])){
            $styles_str .= "
                .form-wrapper.form-edit { 
                    border: solid 1px $theme_options_styles[BgBorderForms] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// ADD PADDING TO THE FORM COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AddPaddingFormWrapper'])){
            $styles_str .= "
                .form-wrapper.form-edit{
                    padding: 16px 24px 16px 24px !important;
                }  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// LABEL COLOR IN FORM COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLabelForms'])){
            $styles_str .= "
                form label,
                form .form-label{
                    color:$theme_options_styles[clLabelForms];
                }

                .form-wrapper.form-edit .control-label-notes,
                .form-group .control-label-notes{ 
                    color:$theme_options_styles[clLabelForms];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO MODAL COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgModal'])){
            $styles_str .= "
                .bootbox.show .bootbox-close-button{
                    background-color:$theme_options_styles[BgModal];
                }
                .modal.show .close{
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses{
                    background:$theme_options_styles[BgModal];
                }
                .course-content::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses .close{
                    background-color: $theme_options_styles[BgModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderModal'])){
            $styles_str .= "
                .modal-content {
                    border: 1px solid $theme_options_styles[clBorderModal];
                }
                .modal-content-opencourses{
                    border: solid 1px $theme_options_styles[clBorderModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clTextModal'])){
            $styles_str .= "
                .bootbox.show .modal-header .modal-title, 
                .modal.show .modal-header .modal-title {
                    color:  $theme_options_styles[clTextModal];
                }
                .modal-content h1,
                .modal-content h2,
                .modal-content h3,
                .modal-content h4,
                .modal-content h5,
                .modal-content h6,
                .modal-content div,
                .modal-content small,
                .modal-content span,
                .modal-content p,
                .modal-content b,
                .modal-content strong,
                .modal-content li,
                .modal-content label,
                .modal-content{
                    color:  $theme_options_styles[clTextModal];
                }

                .bootbox.show .bootbox-body, 
                .modal.show .modal-body{
                    color: $theme_options_styles[clTextModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO AGENDA COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    background-color: $theme_options_styles[bgAgenda];
                }
                .myPersonalCalendar {
                    background-color: $theme_options_styles[bgAgenda];
                }
                .myCalendarEvents table tr td{
                    background-color: $theme_options_styles[bgAgenda];
                    opacity: 0.8;
                }
                .myCalendarEvents .fc-widget-content table tbody td {
                    border: solid 1px rgba(79, 104, 147, 0.3)
                }
                
                .myCalendarEvents .fc-head-container.fc-widget-header,
                .myCalendarEvents .fc-head-container thead{
                    background: $theme_options_styles[bgAgenda];
                }
                .myPersonalCalendar .cal-row-fluid.cal-row-head {
                    background: $theme_options_styles[bgAgenda];
                }
                #cal-day-box .cal-day-hour:nth-child(odd) {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }


                .datepicker-centuries .table-condensed,
                .datepicker-centuries .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-decades .table-condensed,
                .datepicker-decades .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-years .table-condensed,
                .datepicker-years .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-months .table-condensed,
                .datepicker-months .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-days .table-condensed,
                .datepicker-days .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                




                .datetimepicker-years .table-condensed,
                .datetimepicker-years .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-months .table-condensed,
                .datetimepicker-months .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-days .table-condensed,
                .datetimepicker-days .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-hours .table-condensed,
                .datetimepicker-hours .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-minutes .table-condensed,
                .datetimepicker-minutes .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                

                
                .cal-day-today {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }

                .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top {
                    background-color: $theme_options_styles[bgAgenda];
                }

                .datetimepicker.datetimepicker-dropdown-bottom-right.dropdown-menu {
                    background-color: $theme_options_styles[bgAgenda];
                }
                 


                #cal-slide-content {
                    background: $theme_options_styles[bgAgenda] !important;
                }


                .datetimepicker.dropdown-menu,
                .datepicker.dropdown-menu{
                    background: $theme_options_styles[bgAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO OF AGENDA'S HEADER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading, 
                #cal-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }
                #calendar-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }
                .myCalendarEvents .fc-header-toolbar {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }



                .datepicker-centuries .table-condensed thead .prev,
                .datepicker-centuries .table-condensed thead .next,
                .datepicker-centuries .table-condensed thead .datepicker-switch,
                .datepicker-centuries .table-condensed thead .prev:hover,
                .datepicker-centuries .table-condensed thead .next:hover,
                .datepicker-centuries .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-decades .table-condensed thead .prev,
                .datepicker-decades .table-condensed thead .next,
                .datepicker-decades .table-condensed thead .datepicker-switch,
                .datepicker-decades .table-condensed thead .prev:hover,
                .datepicker-decades .table-condensed thead .next:hover,
                .datepicker-decades .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-years .table-condensed thead .prev,
                .datepicker-years .table-condensed thead .next,
                .datepicker-years .table-condensed thead .datepicker-switch,
                .datepicker-years .table-condensed thead .prev:hover,
                .datepicker-years .table-condensed thead .next:hover,
                .datepicker-years .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-months .table-condensed thead .prev,
                .datepicker-months .table-condensed thead .next,
                .datepicker-months .table-condensed thead .datepicker-switch,
                .datepicker-months .table-condensed thead .prev:hover,
                .datepicker-months .table-condensed thead .next:hover,
                .datepicker-months .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-days .table-condensed thead .prev,
                .datepicker-days .table-condensed thead .next,
                .datepicker-days .table-condensed thead .datepicker-switch,
                .datepicker-days .table-condensed thead .prev:hover,
                .datepicker-days .table-condensed thead .next:hover,
                .datepicker-days .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                



                .datetimepicker-years .table-condensed thead .prev,
                .datetimepicker-years .table-condensed thead .next,
                .datetimepicker-years .table-condensed thead .switch,
                .datetimepicker-years .table-condensed thead .prev:hover,
                .datetimepicker-years .table-condensed thead .next:hover,
                .datetimepicker-years .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev,
                .datetimepicker-months .table-condensed thead .next,
                .datetimepicker-months .table-condensed thead .switch,
                .datetimepicker-months .table-condensed thead .prev:hover,
                .datetimepicker-months .table-condensed thead .next:hover,
                .datetimepicker-months .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev,
                .datetimepicker-days .table-condensed thead .next,
                .datetimepicker-days .table-condensed thead .switch,
                .datetimepicker-days .table-condensed thead .prev:hover,
                .datetimepicker-days .table-condensed thead .next:hover,
                .datetimepicker-days .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev,
                .datetimepicker-hours .table-condensed thead .next,
                .datetimepicker-hours .table-condensed thead .switch,
                .datetimepicker-hours .table-condensed thead .prev:hover,
                .datetimepicker-hours .table-condensed thead .next:hover,
                .datetimepicker-hours .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev,
                .datetimepicker-minutes .table-condensed thead .next,
                .datetimepicker-minutes .table-condensed thead .switch,
                .datetimepicker-minutes .table-condensed thead .prev:hover,
                .datetimepicker-minutes .table-condensed thead .next:hover,
                .datetimepicker-minutes .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }

                .datepicker table tr td span.focused {
                    background: $theme_options_styles[BgColorHeaderAgenda] !important;
                }


                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF AGENDA'S HEADER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading, #cal-header {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                #current-month,
                #cal-header .fa-chevron-left,
                #cal-header .fa-chevron-right {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                .text-agenda-title,
                .text-agenda-title:hover{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                .myCalendarEvents .fc-today-button {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                .myCalendarEvents .fc-header-toolbar .fc-next-button .fc-icon-right-single-arrow,
                .myCalendarEvents .fc-header-toolbar .fc-prev-button .fc-icon-left-single-arrow,
                .myCalendarEvents .fc-header-toolbar .fc-center h2,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaDay-button,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button.fc-state-active,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th.next::after,
                .datepicker-decades .table-condensed thead tr th.next::after,
                .datepicker-years .table-condensed thead tr th.next::after,
                .datepicker-months .table-condensed thead tr th.next::after,
                .datepicker-days .table-condensed thead tr th.next::after{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.datepicker-switch,
                .datepicker-decades .table-condensed thead tr th.datepicker-switch,
                .datepicker-years .table-condensed thead tr th.datepicker-switch,
                .datepicker-months .table-condensed thead tr th.datepicker-switch,
                .datepicker-days .table-condensed thead tr th.datepicker-switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.prev::before,
                .datepicker-decades .table-condensed thead tr th.prev::before,
                .datepicker-years .table-condensed thead tr th.prev::before,
                .datepicker-months .table-condensed thead tr th.prev::before,
                .datepicker-days .table-condensed thead tr th.prev::before{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }




                .datetimepicker-years .table-condensed thead .prev::before,
                .datetimepicker-years .table-condensed thead .next::after,
                .datetimepicker-years .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev::before,
                .datetimepicker-months .table-condensed thead .next::after,
                .datetimepicker-months .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev::before,
                .datetimepicker-days .table-condensed thead .next::after,
                .datetimepicker-days .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev::before,
                .datetimepicker-hours .table-condensed thead .next::after,
                .datetimepicker-hours .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev::before,
                .datetimepicker-minutes .table-condensed thead .next::after,
                .datetimepicker-minutes .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

     
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR OF AGENDA'S BODY ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clColorBodyAgenda'])){
            $styles_str .= "
            
                .cal-row-fluid.cal-row-head .cal-cell1,
                .cal-month-day .pull-right,
                .cal-day-weekend span[data-cal-date] {
                    color: $theme_options_styles[clColorBodyAgenda];
                }

                .myPersonalCalendar .cal-row-fluid.cal-row-head .cal-cell1,
                .myPersonalCalendar .cal-month-day .pull-right,
                .myPersonalCalendar .cal-day-hour div,
                #cal-day-box div,
                .cal-year-box div,
                .cal-month-box div,
                .cal-week-box div {
                    color: $theme_options_styles[clColorBodyAgenda];
                }

                .myCalendarEvents .fc-widget-header table thead span {
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .myCalendarEvents .fc-widget-content table tbody span {
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }


                .datepicker-centuries .table-condensed thead tr th.dow,
                .datepicker-decades .table-condensed thead tr th.dow,
                .datepicker-years .table-condensed thead tr th.dow,
                .datepicker-months .table-condensed thead tr th.dow,
                .datepicker-days .table-condensed thead tr th.dow{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td,
                .datepicker-decades .table-condensed tbody tr td,
                .datepicker-years .table-condensed tbody tr td,
                .datepicker-months .table-condensed tbody tr td,
                .datepicker-days .table-condensed tbody tr td{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                




                .datetimepicker-years .table-condensed thead tr th.dow,
                .datetimepicker-months .table-condensed thead tr th.dow,
                .datetimepicker-days .table-condensed thead tr th.dow,
                .datetimepicker-hours .table-condensed thead tr th.dow,
                .datetimepicker-minutes .table-condensed thead tr th.dow{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datetimepicker-years .table-condensed tbody tr td,
                .datetimepicker-months .table-condensed tbody tr td,
                .datetimepicker-days .table-condensed tbody tr td,
                .datetimepicker-hours .table-condensed tbody tr td,
                .datetimepicker-minutes .table-condensed tbody tr td{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }


            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO AGENDA COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderColorAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    border: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                #calendar_wrapper{
                    border: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }

                .fc-unthemed .fc-content, 
                .fc-unthemed .fc-divider, 
                .fc-unthemed .fc-list-heading td, 
                .fc-unthemed .fc-list-view, 
                .fc-unthemed .fc-popover, 
                .fc-unthemed .fc-row, 
                .fc-unthemed tbody, 
                .fc-unthemed td, 
                .fc-unthemed th, 
                .fc-unthemed thead {
                    border-color: $theme_options_styles[BgBorderColorAgenda];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO AGENDA COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorHoveredBodyAgenda'])){
            $styles_str .= "
                .datetimepicker-years .table-condensed thead tr th:hover,
                .datetimepicker-years .table-condensed tbody tr td .year:hover,
                .datetimepicker-months .table-condensed thead tr th:hover,
                .datetimepicker-months .table-condensed tbody tr td .month:hover,
                .datetimepicker-days .table-condensed thead tr th:hover,
                .datetimepicker-days .table-condensed tbody tr td:hover,
                .datetimepicker-hours .table-condensed thead tr th:hover,
                .datetimepicker-hours .table-condensed tbody tr td .hour:hover,
                .datetimepicker-minutes .table-condensed thead tr th:hover,
                .datetimepicker-minutes .table-condensed tbody tr td .minute:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th:hover,
                .datepicker-decades .table-condensed thead tr th:hover,
                .datepicker-years .table-condensed thead tr th:hover,
                .datepicker-months .table-condensed thead tr th:hover,
                .datepicker-days .table-condensed thead tr th:hover{ 
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td .century:hover,
                .datepicker-decades .table-condensed tbody tr td .decade:hover,
                .datepicker-years .table-condensed tbody tr td .year:hover,
                .datepicker-months .table-condensed tbody tr td .month:hover,
                .datepicker-days .table-condensed tbody tr td:hover{ 
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-head:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-head .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-month-box .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-month-box .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-year-box .row-fluid:hover,
                .myPersonalCalendar .cal-week-box .row-fluid:hover,
                #cal-day-box .row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-year-box .row-fluid div:hover,
                .myPersonalCalendar .cal-week-box .row-fluid div:hover,
                #cal-day-box .row-fluid div:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO ACTIVE DATETIME SLOT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active, 
                .datetimepicker table tr td span.active:hover:active, 
                .datetimepicker table tr td span.active.disabled:active, 
                .datetimepicker table tr td span.active.disabled:hover:active, 
                .datetimepicker table tr td span.active.active, 
                .datetimepicker table tr td span.active:hover.active, 
                .datetimepicker table tr td span.active.disabled.active, 
                .datetimepicker table tr td span.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active, 
                .datepicker table tr td.active.highlighted:active, 
                .datepicker table tr td.active.active, 
                .datepicker table tr td.active.highlighted.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active, 
                .datetimepicker table tr td.active:hover:active, 
                .datetimepicker table tr td.active.disabled:active, 
                .datetimepicker table tr td.active.disabled:hover:active, 
                .datetimepicker table tr td.active.active, 
                .datetimepicker table tr td.active:hover.active, 
                .datetimepicker table tr td.active.disabled.active, 
                .datetimepicker table tr td.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active, 
                .datepicker table tr td span.active:hover:active, 
                .datepicker table tr td span.active.disabled:active, 
                .datepicker table tr td span.active.disabled:hover:active, 
                .datepicker table tr td span.active.active, 
                .datepicker table tr td span.active:hover.active, 
                .datepicker table tr td span.active.disabled.active, 
                .datepicker table tr td span.active.disabled:hover.active {
                    color: #fff;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                    border-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO ACTIVE DATETIME SLOT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active, 
                .datetimepicker table tr td span.active:hover:active, 
                .datetimepicker table tr td span.active.disabled:active, 
                .datetimepicker table tr td span.active.disabled:hover:active, 
                .datetimepicker table tr td span.active.active, 
                .datetimepicker table tr td span.active:hover.active, 
                .datetimepicker table tr td span.active.disabled.active, 
                .datetimepicker table tr td span.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active, 
                .datepicker table tr td.active.highlighted:active, 
                .datepicker table tr td.active.active, 
                .datepicker table tr td.active.highlighted.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active, 
                .datetimepicker table tr td.active:hover:active, 
                .datetimepicker table tr td.active.disabled:active, 
                .datetimepicker table tr td.active.disabled:hover:active, 
                .datetimepicker table tr td.active.active, 
                .datetimepicker table tr td.active:hover.active, 
                .datetimepicker table tr td.active.disabled.active, 
                .datetimepicker table tr td.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active, 
                .datepicker table tr td span.active:hover:active, 
                .datepicker table tr td span.active.disabled:active, 
                .datepicker table tr td span.active.disabled:hover:active, 
                .datepicker table tr td span.active.active, 
                .datepicker table tr td span.active:hover.active, 
                .datepicker table tr td span.active.disabled.active, 
                .datepicker table tr td span.active.disabled:hover.active {
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR OF COURSE LEFT MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftNavBgColor'])) {

            $aboutLeftForm = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $aboutLeftForm[3] = '0.1';
            $aboutLeftForm = 'rgba(' . implode(',', $aboutLeftForm) . ')';


            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';

            $styles_str .= " 

                .ContentLeftNav, #collapseTools{
                    background: $theme_options_styles[leftNavBgColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF COURSE LEFT MENU IN SMALL SCREEN /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftNavBgColorSmallScreen'])) {

            $styles_str .= " 

                @media(max-width:991px){
                    .ContentLeftNav, #collapseTools{
                        background: $theme_options_styles[leftNavBgColorSmallScreen];
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO TABLE COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTables'])){
            $styles_str .= "

                #portfolio_lessons tbody tr{
                    background-color: $theme_options_styles[BgTables];
                }
                #portfolio_collaborations tbody tr{
                    background-color: $theme_options_styles[BgTables];
                }

                .table-default tbody tr td, 
                .announcements_table tbody tr td, 
                table.dataTable tbody tr td, 
                .table-default tbody tr th, 
                .announcements_table tbody tr th, 
                table.dataTable tbody tr th {
                    background-color: $theme_options_styles[BgTables];
                }

                thead,
                .title1 {
                    background-color: $theme_options_styles[BgTables];
                }

                .row-course:hover td:first-child, .row-course:hover td:last-child{
                    background-color: $theme_options_styles[BgTables];
                }

                table.dataTable.display tbody tr.odd, 
                table.dataTable.display tbody tr.odd > .sorting_1, 
                table.dataTable.order-column.stripe tbody tr.odd > .sorting_1, 
                table.dataTable.display tbody tr.even > .sorting_1, 
                table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                table.dataTable tbody tr {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                .table-exercise-secondary {
                    background-color: $theme_options_styles[BgTables] ;
                }
                .table-exercise td, .table-exercise th {
                    background-color: transparent;
                }

                user-details-exec{
                    background-color: $theme_options_styles[BgTables];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TO TABLE'S THEAD /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomHeadTables'])){
            $styles_str .= "
                thead, 
                tbody .list-header,
                .border-bottom-table-head {
                    border-bottom: solid 2px $theme_options_styles[BgBorderBottomHeadTables];
                }
                table.dataTable thead th, 
                table.dataTable thead td {
                    border-bottom: 2px solid $theme_options_styles[BgBorderBottomHeadTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM COLOR TO TABLE'S ROWS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomRowTables'])){
            $styles_str .= "
                .table-default tbody tr{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tbody td{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable.no-footer {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }  
                .dataTables_wrapper.no-footer .dataTables_scrollBody {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tfoot th, table.dataTable tfoot td {
                    border-top: 1px solid  $theme_options_styles[BgBorderBottomRowTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{ 
                    background: $theme_options_styles[BgMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO MENU-POPOVER COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{ 
                    border: solid 1px $theme_options_styles[BgBorderMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER OPTIONS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    background-color: $theme_options_styles[BgMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO MENU-POPOVER OPTIONS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    color: $theme_options_styles[clMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO MENU-POPOVER OPTIONS /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{ 
                    background-color: $theme_options_styles[BgHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT HOVERED COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{ 
                    color: $theme_options_styles[clHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO THE TEXT EDITOR /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTextEditor'])){
            $styles_str .= "
                .mce-container, 
                .mce-widget, 
                .mce-widget *, 
                .mce-reset {
                    background: $theme_options_styles[BgTextEditor] !important;
                }
                .mce-window .mce-container-body {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab.mce-active {
                    background: $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-textbox {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  i.mce-i-checkbox {
                    background-image: -webkit-linear-gradient(top,#fff,$theme_options_styles[BgTextEditor]) !important;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR TO THE TEXT EDITOR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderTextEditor'])){
            $styles_str .= "
                .mce-panel {
                    border: solid 1px $theme_options_styles[BgBorderTextEditor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// TEXT COLOR TO THE TEXT EDITOR ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClTextEditor'])){
            $SVGtools = "svg xmlns='http://www.w3.org/2000/svg' height='20' width='15' viewBox='0 0 384 512' fill='%23000'%3e%3cpath fill='$theme_options_styles[ClTextEditor]' d='M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z'/%3e%3c/svg";
            $SVGtools2 = 'transparent url("data:image/svg+xml,%3C' . $SVGtools .'%3E") center / 1em auto no-repeat';
            $styles_str .= "

                .mce-toolbar .mce-btn i {
                    color: $theme_options_styles[ClTextEditor] !important;
                }
                
                .mce-menubtn span {
                    color: $theme_options_styles[ClTextEditor] !important;
                }
                .mce-btn i {
                    text-shadow: 0px 0px $theme_options_styles[ClTextEditor] !important;
                }

                .mce-container, .mce-container *, .mce-widget, .mce-widget *, .mce-reset {
                    color: $theme_options_styles[ClTextEditor] !important;
                }

                .mce-caret {
                    border-top: 4px solid $theme_options_styles[ClTextEditor] !important;
                }

                .mce-toolbar .mce-btn i.mce-i-none{
                    background: $SVGtools2 !important;
                }
                
            ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND CONTAINER OF SCROLLBAR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgScrollBar'])){
            $styles_str .= "
              .container-items::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .contextual-menu::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .course-content::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .panel-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
              
              .table-responsive::-webkit-scrollbar-track,
              .dataTables_wrapper::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .chat-iframe::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
              
              .bodyChat::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
              
              
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO THE SCROLLBAR COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }
            
              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
              
              .contextual-menu::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
 
              .panel-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
             
              .table-responsive::-webkit-scrollbar-thumb,
              .dataTables_wrapper::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
              
              .chat-iframe::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
            
              
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND HOVERED COLOR TO THE SCROLLBAR COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }
            
              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              
              .contextual-menu::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
 
              .panel-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
             
              .table-responsive::-webkit-scrollbar-thumb:hover,
              .dataTables_wrapper::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              
              .chat-iframe::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// PROGRESSBAR COMPONENT ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BackProgressBar']) && !empty($theme_options_styles['BgProgressBar']) &&
                            !empty($theme_options_styles['BgColorProgressBarAndText'])){

            $styles_str .= "
            .progress-circle-bar{
                --size: 9rem;
                --fg: $theme_options_styles[BgColorProgressBarAndText];
                --bg: $theme_options_styles[BgProgressBar];
                --pgPercentage: var(--value);
                animation: growProgressBar 3s 1 forwards;
                width: var(--size);
                height: var(--size);
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                background: 
                    radial-gradient(closest-side, $theme_options_styles[BackProgressBar] 80%, transparent 0 99.9%, $theme_options_styles[BackProgressBar] 0),
                    conic-gradient(var(--fg) calc(var(--pgPercentage) * 1%), var(--bg) 0)
                    ;
                font-weight: 700; font-style: normal;
                font-size: calc(var(--size) / 5);
                color: var(--fg);

            }

            .progress-bar {
                background-color: $theme_options_styles[BgColorProgressBarAndText];
            }

            .progress-line-bar{
                display: flex;
                flex-direction: column;
                justify-content: center;
                overflow: hidden;
                color: $theme_options_styles[BgProgressBar];
                text-align: center;
                white-space: nowrap;
                background-color: $theme_options_styles[BgColorProgressBarAndText];
            }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE TOOLTIP COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    background-color: $theme_options_styles[bgColorTooltip];

                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE TOOLTIP COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    color: $theme_options_styles[TextColorTooltip];

                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// LINKS COLOR OF PLATFORM ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColor'])){
            $styles_str .= "

                a, .toolAdminText{
                    color: $theme_options_styles[linkColor];
                }


                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button.fc-state-active,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaDay-button.fc-state-active{
                    background:$theme_options_styles[linkColor] !important;
                }

                .Primary-600-cl,
                .Primary-500-cl {
                    color: $theme_options_styles[linkColor];
                }

                .Primary-500-bg {
                    background-color:  $theme_options_styles[linkColor];
                }

                .menu-item.active,
                .menu-item.active2{
                    color:  $theme_options_styles[linkColor];
                }
                  
                .portfolio-tools a{
                    color: $theme_options_styles[linkColor];
                }

                .nav-link-adminTools{
                    color: $theme_options_styles[linkColor];
                }

                #cal-slide-content a.event-item{
                    color: $theme_options_styles[linkColor] !important;
                }
                
                .dataTables_paginate.paging_simple_numbers span .paginate_button, 
                .dataTables_paginate.paging_full_numbers span .paginate_button{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important; 
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:active {
                    background: transparent !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.next.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.next, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
                    color: $theme_options_styles[linkColor] !important;
                }

                .mycourses-pagination .page-item{
                    background-color: transparent !important;
                }
                
                .mycourses-pagination .page-item .page-link{
                    color: $theme_options_styles[linkColor] !important;
                    background-color: transparent !important;
                }
                
                .mycourses-pagination .page-item .page-link:hover{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                .mycourses-pagination .page-item .page-link.active:hover{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                .mycourses-pagination .page-item .page-link.active{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                
                .mycourses-pagination .page-item-pages{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-previous{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-next{
                    background-color: transparent !important;
                }

                .mycourses-pagination .page-item-next .page-link:hover,
                .mycourses-pagination .page-item-previous .page-link:hover{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-previous .page-link:hover,
                .mycourses-pagination .page-item-previous .page-link:hover{
                    background-color: transparent !important;
                }


                .commentPress:hover{
                    color: $theme_options_styles[linkColor];
                }


                #cal-slide-content a.event-item {
                    color: $theme_options_styles[linkColor] !important;
                }


                .tree-units summary::before {
                    background: $theme_options_styles[linkColor] url(../../resources/img/units-expand-collapse.svg) 0 0;
                }

                .more-enabled-login-methods div{
                    color: $theme_options_styles[linkColor];
                }

                .ClickCourse,
                .ClickCourse:hover{
                    color: $theme_options_styles[linkColor];
                }

                .carousel-prev-btn,
                .carousel-prev-btn:hover,
                .carousel-next-btn,
                .carousel-next-btn:hover{
                    color: $theme_options_styles[linkColor];
                }

                .link-color{
                    color: $theme_options_styles[linkColor];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO THE PLATFORM'S LINKS ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColor'])){
            $styles_str .= "
                a:hover, a:focus{
                    color: $theme_options_styles[linkHoverColor];
                } 

                #btn-search:hover, #btn-search:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                .portfolio-tools a:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .nav-link-adminTools:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .link-color:hover,
                .link-color:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// DELETE PLATFORM LINK COLOR ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkDeleteColor'])){
            $styles_str .= "
                .link-delete,
                .link-delete:hover,
                .link-delete:focus{
                    color: $theme_options_styles[linkDeleteColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// LINKS INSIDE ALERT COMPONENT  ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLinkAlertInfo'])){
            $styles_str .= "
                .alert-info a{
                    color: $theme_options_styles[clLinkAlertInfo];
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertInfo'])){
            $styles_str .= "
                .alert-info a:hover,
                .alert-info a:focus{
                    color: $theme_options_styles[clLinkHoveredAlertInfo];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertWarning'])){
            $styles_str .= "
                .alert-warning a{
                    color: $theme_options_styles[clLinkAlertWarning];
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertWarning'])){
            $styles_str .= "
                .alert-warning a:hover,
                .alert-warning a:focus{
                    color: $theme_options_styles[clLinkHoveredAlertWarning];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertSuccess'])){
            $styles_str .= "
                .alert-success a{
                    color: $theme_options_styles[clLinkAlertSuccess];
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertSuccess'])){
            $styles_str .= "
                .alert-success a:hover,
                .alert-success a:focus{
                        color: $theme_options_styles[clLinkHoveredAlertSuccess];
                }
            ";
        }

        if(!empty($theme_options_styles['clLinkAlertDanger'])){
            $styles_str .= "
                .alert-danger a{
                    color: $theme_options_styles[clLinkAlertDanger];
                }
            ";
        }
        if(!empty($theme_options_styles['clLinkHoveredAlertDanger'])){
            $styles_str .= "
                .alert-danger a:hover,
                .alert-danger a:focus{
                        color: $theme_options_styles[clLinkHoveredAlertDanger];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// SETTINGS TO THE LEFT MENU OF COURSE /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .toolSidebarTxt{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if (!empty($theme_options_styles['leftMenuFontColor'])){
            $styles_str .= "
                #leftnav .panel a.parent-menu{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu span{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuFontColor];
                }
                
                #collapse-left-menu-icon path{
                    fill: $theme_options_styles[leftMenuFontColor] !important;
                }
                
            ";
        }

        if (!empty($theme_options_styles['leftMenuHoverFontColor'])){
            $styles_str .= "
                #leftnav .panel .panel-sidebar-heading:hover{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover span{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item, .menu_btn_button .fa-bars{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    color:$theme_options_styles[leftSubMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    background-color:$theme_options_styles[leftSubMenuHoverBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    background-color: $theme_options_styles[leftMenuSelectedBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedLinkColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    color: $theme_options_styles[leftMenuSelectedLinkColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////// UPLOAD LOGO ////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['imageUpload'])) {
            $data['logo_img'] = "$urlThemeData/$theme_options_styles[imageUpload]";
        }

        if (isset($theme_options_styles['imageUploadSmall'])) {
            $data['logo_img_small'] = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OF HOMEPAGE ANNOUNCEMENTS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorAnnouncementHomepage'])){
            $styles_str .= "
                .homepage-annnouncements-container{
                    background-color: $theme_options_styles[BgColorAnnouncementHomepage];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF PORTFOLIO - COURSES CONTAINER ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorWrapperPortfolioCourses'])){
            $styles_str .= "
                .portfolio-courses-container {
                    background-color:$theme_options_styles[BgColorWrapperPortfolioCourses];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND COLOR OF COURSE CONTAINER (RIGHT COL) ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['RightColumnCourseBgColor'])){
            $styles_str .= "
                .col_maincontent_active {
                    background-color:$theme_options_styles[RightColumnCourseBgColor];
                }

                @media(max-width:991px){
                    .module-container:has(.course-wrapper){
                        background-color:$theme_options_styles[RightColumnCourseBgColor];
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND IMAGE TO THE COURSE CONTAINER (RIGHT COL) //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['RightColumnCourseBgImage'])){
            $styles_str .= "
                .col_maincontent_active {
                    background: url('$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]');
                    background-size: 100% 100%;
                    background-attachment: fixed;
                }
                .col_maincontent_active_module {
                    background: url('$urlThemeDataForModules/$theme_options_styles[RightColumnCourseBgImage]');
                    background-size: 100% 100%;
                    background-attachment: fixed;
                }
                .col_maincontent_active_module_content {
                    background: url('$urlThemeDataForModulesContent/$theme_options_styles[RightColumnCourseBgImage]');
                    background-size: 100% 100%;
                    background-attachment: fixed;
                }

                @media(max-width:991px){
                    .module-container:has(.course-wrapper){
                        background: url('$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]');
                        background-size: 100% 100%;
                        background-attachment: fixed;
                    }
                    .module-container:has(.col_maincontent_active_module){
                        background: url('$urlThemeDataForModules/$theme_options_styles[RightColumnCourseBgImage]');
                        background-size: 100% 100%;
                        background-attachment: fixed;
                    }
                    .module-container:has(.col_maincontent_active_module_content){
                        background: url('$urlThemeDataForModulesContent/$theme_options_styles[RightColumnCourseBgImage]');
                        background-size: 100% 100%;
                        background-attachment: fixed;
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BORDER COLOR TO THE LEFT SIDE OF COURSE CONTAINER  //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BorderLeftToRightColumnCourseBgColor'])){
            $styles_str .= "
                @media(min-width:992px){
                    .col_maincontent_active {
                        border-left: solid 1px $theme_options_styles[BorderLeftToRightColumnCourseBgColor];
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE PANEL'S BODY //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgPanels'])){
            $styles_str .= "

                .panel-action-btn-default,
                .panel-primary,
                .panel-success,
                .panel-default,
                .panel-info,
                .panel-danger,
                .panel-admin,
                .card,
                .user-info-card,
                .panelCard,
                .cardLogin,
                .statistics-card,
                .bodyChat{
                    background-color:$theme_options_styles[BgPanels] ;
                }

                .wallWrapper{
                    background-color:$theme_options_styles[BgPanels] !important;
                }

                .testimonials .testimonial {
                    background: $theme_options_styles[BgPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #lti_label{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #jsmind_container {
                    background: $theme_options_styles[BgPanels] !important;
                }

                .card-transparent,
                .card-transparent .card-header,
                .card-transparent .card-body,
                .card-transparent .card-footer,
                .card-transparent .panel-heading,
                .card-transparent .panel-body,
                .card-transparent .panel-footer{
                    background-color: transparent ;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    background: $theme_options_styles[BgPanels];
                }
               
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO THE PANELS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderPanels'])){
            $styles_str .= "

                .user-info-card,
                .panelCard,
                .cardLogin,
                .border-card,
                .statistics-card,
                .panel-success,
                .panel-admin,
                .panel-default,
                .panel-danger,
                .panel-primary,
                .panel-info,
                .panel-action-btn-default{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .panelCard.border-card-left-default {
                    border-left: solid 7px $theme_options_styles[clBorderPanels];
                }

                .border-top-default{
                    border-top: solid 1px $theme_options_styles[clBorderPanels];
                    border-left: none;
                    border-right: none;
                    border-bottom: none;
                }

                .BorderSolidDes{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .wallWrapper{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .testimonials .testimonial {
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                #lti_label{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                #jsmind_container {
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    border: none;
                }

                .panel-default:has(.panel-heading) {
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE COMMENTS PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    background-color: $theme_options_styles[BgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BORDER COLOR TO THE COMMENTS PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    border: solid 1px $theme_options_styles[clBorderBgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE QUESTIONNAIRE PANELS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire{
                    background-color: $theme_options_styles[BgQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE QUESTIONNAIRE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire{
                    border: solid 1px $theme_options_styles[clBorderQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE EXERCISE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    background-color: $theme_options_styles[BgExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER COLOR TO THE EXERCISES PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    border: solid 1px $theme_options_styles[clBorderExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE CHAT CONTAINER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutChatContainer'])){
            $styles_str .= "
                .bodyChat{
                    background: none;
                    background-color: $theme_options_styles[AboutChatContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO THE CHAT CONTAINER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderChatContainer'])){
            $styles_str .= "
                .embed-responsive-item{
                    border: solid 1px $theme_options_styles[AboutBorderChatContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE INFO CONTAINER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    background-color: $theme_options_styles[AboutCourseInfoContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE INFO CONTAINER ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    border: solid 1px $theme_options_styles[AboutBorderCourseInfoContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE UNITS CONTAINER /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutUnitsContainer'])){
            $styles_str .= "
                .card-units,
                .card-sessions{
                    background-color: $theme_options_styles[AboutUnitsContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE UNITS CONTAINER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderUnitsContainer'])){
            $styles_str .= "
                .card-units,
                .card-sessions{
                    border: solid 1px $theme_options_styles[AboutBorderUnitsContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////// BACKGROUND COLOR TO THE PLATFORM CONTENT WHEN PLATFORM IS BOXED TYPR ////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['view_platform']) && $theme_options_styles['view_platform'] == 'boxed'){

            $maxWidthPlatform = (isset($theme_options_styles['fluidContainerWidth']) ? "$theme_options_styles[fluidContainerWidth]px" : '1200px');
            $styles_str .= "

                @media (min-width: 992px) {
                    .ContentEclass{
                        margin-left: auto;
                        margin-right: auto;
                        min-width: 992px;
                        max-width: $maxWidthPlatform ;
                    }

                    #bgr-cheat-header,
                    #bgr-cheat-header-mentoring{
                        padding-left: 10px;
                        padding-right: 10px;
                        margin-left: auto;
                        margin-right: auto;
                        max-width: $maxWidthPlatform ;
                    }

                    #bgr-cheat-header.fixed, 
                    #bgr-cheat-header-mentoring.fixed {
                        margin-left: auto;
                        margin-right: auto;
                        max-width: $maxWidthPlatform ;
                    }

                    .notification-top-bar{
                        left: auto;
                        max-width: $maxWidthPlatform ;
                    }
                }

            ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND COLOR TO THE MAIN SECTION ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorContentPlatform'])){
           $styles_str .= "
                .ContentEclass,
                .main-container,
                .module-container{
                    background-color: $theme_options_styles[bgColorContentPlatform];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// LEARNPATH - SPECIAL CASES  ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorHeader'])){
            $styles_str .= "
                #leftTOCtoggler,
                .prev-next-learningPath{
                    color:$theme_options_styles[linkColorHeader];
                }

            ";
        }



        $data['styles'] = $styles_str;


    }

    return $data;
}
