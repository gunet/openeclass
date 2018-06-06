<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ========================================================================
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
 * @global type $blade
 * @global type $webDir
 * @global type $course_id
 * @global type $course_code
 * @global type $downloadDir
 * @global type $langDownloadDir
 * @global type $langSave
 * @global type $copyright_titles
 * @global type $copyright_links
 * @param type $curDirPath
 * @param type $curDirName
 * @param type $curDirPrefix
 * @param type $bladeData
 */
function offline_documents($curDirPath, $curDirName, $curDirPrefix, $bladeData) {
    global $blade, $webDir, $course_id, $course_code, $downloadDir,
           $langDownloadDir, $langSave, $copyright_titles, $copyright_links;

    // doc init
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    if (!file_exists($downloadDir . '/modules/' . $curDirName)) {
        mkdir($downloadDir . '/modules/' . $curDirName);
    }

    $files = $dirs = array();
    $result = Database::get()->queryArray("SELECT id, path, filename, format,
                                        title, extra_path, course_id,
                                        date_modified, public, visible,
                                        editable, copyrighted, comment,
                                        IF((title = '' OR title IS NULL), filename, title) AS sort_key
                                FROM document
                                WHERE
                                      course_id = ?d AND
                                      path LIKE ?s AND
                                      path NOT LIKE ?s ORDER BY sort_key COLLATE utf8_unicode_ci", $course_id, $curDirPath . "/%", $curDirPath . "/%/%");
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
            'filename' => $row->filename,
            'format' => $row->format,
            'path' => $row->path,
            'extra_path' => $row->extra_path,
            'visible' => ($row->visible == 1),
            'public' => $row->public,
            'comment' => $row->comment,
            'date' => nice_format($row->date_modified, true, true),
            'date_time' => nice_format($row->date_modified, true),
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
            $newData['template_base'] = $newData['urlAppend'] . 'template/default';
            $newData['themeimg'] = $newData['urlAppend'] . 'template/default/img';
            $newData['logo_img'] = $newData['themeimg'] . '/eclass-new-logo.png';
            $newData['logo_img_small'] = $newData['themeimg'] . '/logo_eclass_small.png';
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
                $info['copyright_title'] = $copyright_titles[$copyid];
                $info['copyright_link'] = $copyright_links[$copyid];
            }

            $files[] = (object) $info;
        }
    }
    $bladeData['fileInfo'] = array_merge($dirs, $files);
    $bladeData['curDirPath'] = $curDirPath;
    $docout = $blade->view()->make('modules.document.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/' . $curDirName . '.html', 'w');
    fwrite($fp, $docout);
    fclose($fp);
}


/**
 * @brief get / render announcements
 * @global type $blade
 * @global type $course_id
 * @global type $downloadDir
 * @param type $bladeData
 */
function offline_announcements($bladeData) {
    global $blade, $course_id, $downloadDir;

    $bladeData['urlAppend'] = '../';
    $bladeData['template_base'] = '../template/default';
    $bladeData['themeimg'] = '../template/default/img';
    $bladeData['logo_img'] = '../template/default/img/eclass-new-logo.png';
    $bladeData['logo_img_small'] = '../template/default/img/logo_eclass_small.png';
    $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

    $bladeData['announcements'] = $announcements = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d
                                                AND visible = 1
                                                AND (start_display <= NOW() OR start_display IS NULL)
                                                AND (stop_display >= NOW() OR stop_display IS NULL)
                                            ORDER BY `order` DESC , `date` DESC", $course_id);


    $out = $blade->view()->make('modules.announcements.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/announcements.html', 'w');
    fwrite($fp, $out);

    if (is_array($announcements) && !empty($announcements) && count($announcements) > 0) {
        if (!file_exists($downloadDir . '/modules/announcement/')) {
            mkdir($downloadDir . '/modules/announcement/');
        }

        foreach ($announcements as $a) {
            $bladeData['urlAppend'] = '../../';
            $bladeData['template_base'] = '../../template/default';
            $bladeData['themeimg'] = '../../template/default/img';
            $bladeData['logo_img'] = '../../template/default/img/eclass-new-logo.png';
            $bladeData['logo_img_small'] = '../../template/default/img/logo_eclass_small.png';
            $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);
            $bladeData['ann_title'] = $a->title;
            $bladeData['ann_body'] = $a->content;
            $bladeData['ann_date'] = $a->date;
            $out = $blade->view()->make('modules.announcements.ann', $bladeData)->render();
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

    $out = $blade->view()->make('modules.video.index', $bladeData)->render();
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
 * @global type $course_id
 * @global type $blade
 */
function offline_unit_resources($bladeData, $downloadDir) {

    global $course_id, $blade;

    $bladeData['urlAppend'] = '../../';
    $bladeData['template_base'] = '../../template/default';
    $bladeData['themeimg'] = '../../template/default/img';
    $bladeData['logo_img'] = '../../template/default/img/eclass-new-logo.png';
    $bladeData['logo_img_small'] = '../../template/default/img/logo_eclass_small.png';
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
            $out = $blade->view()->make('modules.unit', $bladeData)->render();
            $fp = fopen($downloadDir . '/modules/unit/' . $cu->id . '.html', 'w');
            fwrite($fp, $out);
        }
    }
}



function offline_exercises($bladeData) {
    global $blade, $downloadDir, $course_id;

    $bladeData['exercises'] = $exercises = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d AND active = 1 ORDER BY start_date DESC", $course_id);

    $out = $blade->view()->make('modules.exercise.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/exercise.html', 'w');
    fwrite($fp, $out);

    if (is_array($exercises) && !empty($exercises) && count($exercises) > 0) {
        if (!file_exists($downloadDir . '/modules/exercise/')) {
            mkdir($downloadDir . '/modules/exercise/');
        }

        foreach ($exercises as $e) {
            $bladeData['urlAppend'] = '../../';
            $bladeData['template_base'] = '../../template/default';
            $bladeData['themeimg'] = '../../template/default/img';
            $bladeData['logo_img'] = '../../template/default/img/eclass-new-logo.png';
            $bladeData['logo_img_small'] = '../../template/default/img/logo_eclass_small.png';
            $bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

            // TODO
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

            $out = $blade->view()->make('modules.exercise.exer', $bladeData)->render();
            $fp = fopen($downloadDir . '/modules/exercise/' . $e->id . '.html', 'w');
            fwrite($fp, $out);
        }
    }
}

function offline_ebook($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->view()->make('modules.ebook.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/ebook.html', 'w');
    fwrite($fp, $out);

}

function offline_agenda($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->view()->make('modules.agenda.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/agenda.html', 'w');
    fwrite($fp, $out);
}

function offline_blog($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->view()->make('modules.blog.index', $bladeData)->render();
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

    $out = $blade->view()->make('modules.course_description.index', $bladeData)->render();
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

    $out = $blade->view()->make('modules.link.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/link.html', 'w');
    fwrite($fp, $out);
}



function offline_wiki($bladeData) {
    global $blade, $downloadDir;

    $out = $blade->view()->make('modules.wiki.index', $bladeData)->render();
    $fp = fopen($downloadDir . '/modules/wiki.html', 'w');
    fwrite($fp, $out);
}

/**
 * @brief get glossary terms
 * @global type $blade
 * @global type $course_id
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

    if (count($prefixes) > 1) {
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
            $bladeData['glossary'] = Database::get()->queryArray("SELECT id, term, definition, url, notes, category_id
                                FROM glossary WHERE course_id = ?d AND term LIKE '$letter%'
                                GROUP BY term, definition, url, notes, category_id, id
                                ORDER BY term", $course_id);
            $out = $blade->view()->make('modules.glossary.index', $bladeData)->render();
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