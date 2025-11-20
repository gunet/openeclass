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

// form select about visibility
function visibility_select($current) {
    global $langOpenCourse, $langRegCourse, $langClosedCourse, $langInactiveCourse, $langCourseVis;

    $ret = "<select class='form-select' name='course_vis' aria-label='$langCourseVis'>\n";
    foreach (array($langOpenCourse => COURSE_OPEN,
            $langRegCourse => COURSE_REGISTRATION,
            $langClosedCourse => COURSE_CLOSED,
            $langInactiveCourse => COURSE_INACTIVE) as $text => $type) {
        $selected = ($type == $current) ? ' selected' : '';
        $ret .= "<option value='$type'$selected>" . q($text) . "</option>\n";
    }
    $ret .= "</select>";
    return $ret;
}


// Unzip backup file
function unpack_zip_inner($zipfile, $clone) {
    global $webDir, $uid, $langGeneralError;
    require_once 'include/lib/fileUploadLib.inc.php';

    $destdir = $webDir . '/courses/tmpUnzipping/' . $uid;
    if (!is_dir($destdir)) {
        make_dir($destdir);
    }

    $zip = new ZipArchive;
    if (!$zip->open($zipfile) or !$zip->extractTo($destdir)) {
        Session::flash('message',$langGeneralError);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/course_info/restore_course.php');
    }
    // see if any files use backslash as directory separator
    $filesToMove = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (strpos($filename, '\\') !== false) {
            $filesToMove[] = $filename;
        }
    }
    $zip->close();

    foreach ($filesToMove as $filename) {
        $filename = $destdir . '/' . $filename;
        $newname = str_replace('\\', '/', $filename);
        $parentdir = my_dirname($newname);
        if (!is_dir($parentdir)) {
            mkdir($parentdir, 0700, true);
        }
        if (!is_dir($filename)) {
            rename($filename, $newname);
        } else {
            rmdir($filename);
        }
    }

    $retArr = array();
    foreach (find_backup_folders($destdir) as $folder) {
        $retArr[] = array(
            'path' => $folder['path'] . '/' . $folder['dir'],
            'file' => $folder['dir'],
            'course' => preg_replace('|^.*/|', '', $folder['path'])
        );
    }

    chdir($webDir);
    return $retArr;
}

function unpack_zip_show_files($zipfile) {
    global $langEndFileUnzip, $langLesFound, $langRestore, $langLesFiles;

    $retArr = unpack_zip_inner($zipfile, FALSE);
    $retString = '';

    if (count($retArr) > 0) {
        $retString .= "<p>$langEndFileUnzip</p><br />$langLesFound
                           <form action='$_SERVER[SCRIPT_NAME]' method='post'>
                             <ol>";
        $checked = ' checked';

        foreach ($retArr as $entry) {
            $path = $entry['path'];
            $file = q($entry['file']);
            $course = q($entry['course']);

            $retString .= "<li>$langLesFiles <div class='radio'><label><input type='radio' name='restoreThis' value='" . q(getIndirectReference($path)) . "'$checked>
                            <b>$course</b> ($file)</label></div></li>\n";
            $checked = '';
        }

        $retString .= "</ol><br /><input class='btn submitAdminBtn' type='submit' name='do_restore' value='$langRestore' />
                      ".generate_csrf_token_form_field()."
                      </form>";
    }

    return $retString;
}

// Find folders under $basedir containing a "backup.php" or a "config_vars" file
function find_backup_folders($basedir) {
    $dirlist = array();
    if (is_dir($basedir) and $handle = opendir($basedir)) {
        while (($file = readdir($handle)) !== false) {
            $entry = "$basedir/$file";
            if (is_dir($entry) and $file != '.' and $file != '..') {
                if (file_exists("$entry/backup.php") or
                        file_exists("$entry/config_vars")) {
                    $dirlist[] = array('path' => $basedir,
                        'dir' => $file);
                } else {
                    $dirlist = array_merge($dirlist, find_backup_folders($entry));
                }
            }
        }
        closedir($handle);
    }
    return $dirlist;
}

function restore_table($basedir, $table, $options, $url_prefix_map, $backupData, $restoreHelper) {
    $set = get_option($options, 'set');
    if (!file_exists($basedir . "/" . $restoreHelper->getFile($table))
            && $restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X
            && isset($backupData) && is_array($backupData)
            && isset($backupData['query']) && is_array($backupData['query'])) {
        // look into backupData for our data
        $backup = get_tabledata_from_parsed($table, $backupData, $restoreHelper, $set);
    } else if (file_exists($basedir . "/" . $restoreHelper->getFile($table))) {
        $backup = unserialize(file_get_contents($basedir . "/" . $restoreHelper->getFile($table)));
    } else {
        $backup = array();
    }
    $mapping = array();
    if (isset($options['return_mapping'])) {
        $return_mapping = true;
        $id_var = $restoreHelper->getField($table, $options['return_mapping']); // map needs reverse resolution
    } else {
        $return_mapping = false;
    }

    if (isset($options['insert_field'])) {
        $insert_field = $options['insert_field'];
        $insert_data = $options['insert_field_data'];
        $insert_key = $options['insert_field_key'];
    } else {
        $insert_field = null;
    }

    // extract old value to global var $GLOBALS[$extract_target] and delete field
    if (isset($options['extract_field'])) {
        $extract_field = true;
        $extract_var = $restoreHelper->getField($table, $options['extract_field']); // map needs reverse resolution
        $extract_target = $table . '_' . $options['extract_field'];
        $GLOBALS[$extract_target] = [];
    } else {
        $extract_field = false;
    }

    // move data to new table
    if (isset($options['target_table'])) {
        $target_table = $options['target_table'];
    } else {
        $target_table = $table;
    }

    foreach ($backup as $data) {
        if ($return_mapping) {
            $old_id = $data[$id_var];
            unset($data[$id_var]);
        }
        if ($extract_field and isset($data[$extract_var])) {
            $extract_value = $data[$extract_var];
            unset($data[$extract_var]);
        }
        if (isset($options['delete'])) {
            foreach ($options['delete'] as $field) {
                unset($data[$field]);
            }
        }
        if (isset($options['init'])) {
            foreach ($options['init'] as $field => $value) {
                if (!isset($data[$field])) {
                    $data[$field] = $value;
                }
            }
        }
        if (isset($options['map'])) {
            foreach ($options['map'] as $field => &$map) {
                // Should we keep records where a mapped field is missing from the map?
                $map_missing_keep = in_array($field, $options['map_missing_keep'] ?? []);
                $newField = $restoreHelper->getField($table, $field);
                // Don't pass null data through mapping
                if (!is_null($data[$newField])) {
                    if (isset($data[$newField]) && isset($map[$data[$newField]])) { // map needs reverse resolution
                        $data[$newField] = $map[$data[$newField]];
                    } elseif (!$map_missing_keep) {
                        continue 2;
                    }
                }
            }
        }
        $do_insert = true;
        if (isset($options['map_function'])) {
            if (isset($options['map_function_data'])) {
                $do_insert = $options['map_function']($data, $options['map_function_data']);
            } else {
                $do_insert = $options['map_function']($data);
            }
        }
        if ($insert_field and !isset($data[$insert_field])) {
            $insert_value = $insert_data[$data[$insert_key]];
            $data[$insert_field] = $insert_value;
        }
        if (!isset($sql_intro)) {
            $sql_intro = "INSERT INTO `$target_table` " . field_names($data, $table, $restoreHelper) . ' VALUES ';
        }
        if ($do_insert) {
            $field_args = field_args($data, $table, $set, $url_prefix_map, $restoreHelper);
            $lastid = Database::get()->query($sql_intro . field_placeholders($data, $table, $set, $restoreHelper), $field_args)->lastInsertID;
            if ($return_mapping) {
                $mapping[$old_id] = $lastid;
            }
            if ($extract_field and isset($extract_value)) {
                $GLOBALS[$extract_target][$lastid] = $extract_value;
            }
        }
    }
    if ($return_mapping) {
        return $mapping;
    }
}

function field_names($data, $table, $restoreHelper) {
    foreach ($data as $name => $value) {
        $keys[] = '`' . $restoreHelper->getField($table, $name) . '`';
    }
    return '(' . implode(', ', $keys) . ')';
}

function field_placeholders($data, $table, $set, $restoreHelper) {
    foreach ($data as $name => $value) {
        if (isset($set[$restoreHelper->getField($table, $name)])) {
            $value = $set[$restoreHelper->getField($table, $name)];
        }
        if (is_int($value)) {
            $values[] = '?d';
        } else {
            // consult restoreHelper in case we cannot determine the type
            // just by looking at the value. For example, if the value
            // is null we cannot be sure if the type is string or numeric
            // and this is not helpful for SQL strict mode.
            $values[] = $restoreHelper->getType($table, $name, $value);
        }
    }
    return '(' . implode(', ', $values) . ')';
}

function field_args($data, $table, $set, $url_prefix_map, $restoreHelper) {
    $values = array();
    foreach ($data as $name => $value) {
        if (isset($set[$restoreHelper->getField($table, $name)])) {
            $value = $set[$restoreHelper->getField($table, $name)];
        }
        $rhvalue = $restoreHelper->getValue($table, $name, $value);
        if (isset($url_prefix_map)) {
            // preserve null values because strtr() below turns NULL
            // into empty string ('') which is bad for STRICT SQL mode
            if (is_null($rhvalue)) {
                $values[] = $rhvalue;
            } else {
                $values[] = strtr($rhvalue, $url_prefix_map);
            }
        } else {
            $values[] = $rhvalue;
        }
    }
    return $values;
}

function get_option($options, $name) {
    if (isset($options[$name])) {
        return $options[$name];
    } else {
        return array();
    }
}

/**
 * @param string $code
 * @param string $title
 * @param string $prof
 * @param string $lang
 * @param string $type - can be null
 * @param int $vis
 * @param string $desc
 * @param mixed $faculty - can be null
 */
function course_details_form($code, $title, $prof, $lang, $type, $vis, $desc, $faculty, $allowables = null) {
    global $langInfo1, $langInfo2, $langCourseCode, $langLanguage, $langTitle,
    $langCourseDescription, $langFaculty, $langCourseVis, $langTeacher, $langUsersWillAdd,
    $langRestore, $langAll, $langsTeachers, $langMultiRegType,
    $langNone, $langOldValue, $treeObj, $langImgFormsDes, $langSelect, $langForm, $head_content;


    if (isset($allowables) and $allowables) {
        list($tree_js, $tree_html) = $treeObj->buildCourseNodePicker(['allowables' => $allowables]);
    } else {
        list($tree_js, $tree_html) = $treeObj->buildCourseNodePicker();
    }
    $head_content .= $tree_js;

    if ($type) {
        if (isset($GLOBALS['lang' . $type])) {
            $type_label = ' (' . $GLOBALS['lang' . $type] . ')';
        } else {
            $type_label = ' (' . $type . ')';
        }
    } else {
        $type_label = '';
    }
    if (is_array($faculty)) {
        foreach ($faculty as $entry) {
            $old_faculty_names[] = q(Hierarchy::unserializeLangField($entry['name']));
        }
        $old_faculty = implode('<br>', $old_faculty_names);
    } else {
        $old_faculty = q(Hierarchy::unserializeLangField($faculty) . $type_label);
    }
    $formAction = $_SERVER['SCRIPT_NAME'];
    if (isset($GLOBALS['course_code'])) {
        $formAction .= '?course=' . $GLOBALS['course_code'];
    }
    return "<div class='row m-auto'>
                <div class='col-lg-10 col-12'>
                    <div class='form-wrapper form-edit rounded'>
                        <form class='form-horizontal' role='form' action='$formAction' method='post' onsubmit='return validateNodePickerForm();' >
                            <fieldset>
                                <legend class='mb-0' aria-label='$langForm'></legend>
                                <div class='col-12'>
                                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langInfo1 <br> $langInfo2</span></div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='course_code' class='col-12 control-label-notes'>$langCourseCode</label>
                                    <div class='col-sm-12'>
                                        <input type='text' class='form-control' id='course_code' name='course_code' value='" . q($code) . "'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-12 control-label-notes'>$langLanguage</div>
                                    <div class='col-sm-12'>
                                        " . lang_select_options('course_lang') . "
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='course_title' class='col-12 control-label-notes'>$langTitle</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='course_title' name='course_title' value='" . q($title) . "' />
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='course_desc' class='col-12 control-label-notes'>$langCourseDescription</label>
                                    <div class='col-sm-12'>
                                        " . rich_text_editor('course_desc', 10, 40, purify($desc)) . "
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='dialog-set-value' class='col-12 control-label-notes'>$langFaculty</label>
                                    <div class='col-sm-12'>
                                        " . $tree_html . "<br>$langOldValue: <i>$old_faculty</i>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-12 control-label-notes'>$langCourseVis:</div>
                                    <div class='col-sm-12'>
                                        " . visibility_select($vis) . "
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='course_prof' class='col-12 control-label-notes'>$langTeacher</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='course_prof' name='course_prof' value='" . q($prof) . "' size='50' />
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='control-label-notes mb-2'>$langUsersWillAdd</div>

                                    <div class='col-sm-12'>
                                    <div class='radio mb-2'>
                                            <label>
                                                <input type='radio' name='add_users' value='all' id='add_users_all' checked='checked'>
                                                $langAll
                                            </label>
                                        </div>
                                        <div class='radio mb-2'>
                                            <label>
                                                <input type='radio' name='add_users' value='prof' id='add_users_prof'>
                                                $langsTeachers
                                            </label>
                                        </div>
                                        <div class='radio mb-2'>
                                            <label>
                                                <input type='radio' name='add_users' value='none' id='add_users_none'>
                                                $langNone
                                            </label>
                                        </div>
                                    </div>
                                </div>" .
                                // Hide "Create accounts" option if in course (i.e. clone mode)
                                (isset($GLOBALS['course_code'])? '': "
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                        <label class='label-container' aria-label='$langSelect'>
                                            <input type='checkbox' name='create_users' value='1' id='create_users' checked='checked'>
                                            <span class='checkmark'></span>
                                            $langMultiRegType
                                        </label>
                                    </div>
                                </div>") . "
                                <div class='form-group mt-5 d-flex justify-content-end align-items-center'>

                                    <input class='btn submitAdminBtn' type='submit' name='create_restored_course' value='$langRestore' />
                                <input type='hidden' name='restoreThis' value='" . q($_POST['restoreThis']) . "' />

                                </div>
                            " . generate_csrf_token_form_field() . "
                            </fieldset>
                        </form>
                    </div>
                </div>
                <div class='col-lg-2 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>
    ";
}

function create_restored_course(&$tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof, $clone_course = FALSE, $fetch_course = FALSE) {
    global $webDir, $urlServer, $urlAppend, $langEnter, $langBack, $currentCourseCode;

    require_once 'modules/create_course/functions.php';
    require_once 'modules/course_info/restorehelper.class.php';
    require_once 'include/lib/fileManageLib.inc.php';
    Database::get()->transaction(function() use (&$new_course_code, &$new_course_id, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof, $webDir, &$tool_content, $urlServer, $urlAppend, $clone_course, $fetch_course) {
        if (!$fetch_course) {
            $departments = array();
            if (isset($_POST['department'])) {
                foreach ($_POST['department'] as $did) {
                    $departments[] = intval($did);
                }
            } else {
                $minDep = Database::get()->querySingle("SELECT MIN(id) AS min FROM hierarchy");
                if ($minDep) {
                    $departments[0] = $minDep->min;
                }
            }

            list($new_course_code, $new_course_id) = create_course($course_code, $course_lang, $course_title, $course_desc, $departments, $course_vis, $course_prof);
            if (!$new_course_code) {
                Session::flash('message', $GLOBALS['langGeneralError']);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/course_info/restore_course.php');
            }

            if (!file_exists($restoreThis)) {
                redirect_to_home_page('modules/course_info/restore_course.php');
            }
            $config_data = unserialize(file_get_contents($restoreThis . '/config_vars'));
            // If old $urlAppend didn't end in /, add it
            if (substr($config_data['urlAppend'], -1) !== '/') {
                $config_data['urlAppend'] .= '/';
            }
            $eclass_version = (isset($config_data['version'])) ? $config_data['version'] : null;
            $backupData = null;
            if (file_exists($restoreThis . '/backup.php')) {
                $backupData = parse_backup_php($restoreThis . '/backup.php');
                $eclass_version = $backupData['eclass_version'];
            }
            $restoreHelper = new RestoreHelper($eclass_version);

            $course_file = $restoreThis . '/' . $restoreHelper->getFile('course');
            if (file_exists($course_file)) {
                $course_dataArr = unserialize(file_get_contents($course_file));
                $course_data = $course_dataArr[0];
                // update course query
                $upd_course_sql = "UPDATE course SET keywords = ?s, doc_quota = ?f, video_quota = ?f, "
                    . " group_quota = ?f, dropbox_quota = ?f, glossary_expand = ?d, course_license = ?d ";
                $upd_course_args = array(
                    $course_data[$restoreHelper->getField('course', 'keywords')],
                    floatval($course_data['doc_quota']),
                    floatval($course_data['video_quota']),
                    floatval($course_data['group_quota']),
                    floatval($course_data['dropbox_quota']),
                    intval($course_data[$restoreHelper->getField('course', 'glossary_expand')]),
                    intval($course_data[$restoreHelper->getField('course', 'course_license')])
                );
                if (!isset($course_data['course_image'])) {
                    $course_data['course_image'] = null;
                }
                if (isset($course_data['home_layout'])) {
                    $upd_course_sql .= ', home_layout = ?d, course_image = ?s ';
                    $upd_course_args[] = $course_data['home_layout'];
                    $upd_course_args[] = $course_data['course_image'];
                }
                // Set keywords to '' if NULL
                if (!isset($upd_course_args[0])) {
                    $upd_course_args[0] = '';
                }
                if ($course_data['view_type'] == 'weekly') {
                    $course_data['view_type'] = 'units';
                    $weekly_view = true;
                } else {
                    $weekly_view = false;
                }
                // handle course weekly if exists
                if (isset($course_data['view_type']) && isset($course_data['start_date']) && isset($course_data['end_date'])) {
                    if ($course_data['start_date'] == '0000-00-00') {
                        $course_data['start_date'] = null;
                    }
                    if ($course_data['end_date'] == '0000-00-00') {
                        $course_data['end_date'] = null;
                    }
                    $upd_course_sql .= " , view_type = ?s, start_date = ?t, end_date = ?t ";
                    array_push($upd_course_args,
                        $course_data['view_type'],
                        $course_data['start_date'],
                        $course_data['end_date']
                    );
                } else {
                    $upd_course_sql .= " , view_type = ?s ";
                    array_push($upd_course_args, $course_data['view_type']);
                }
                $upd_course_sql .= " WHERE id = ?d ";
                $upd_course_args[] = intval($new_course_id);
                Database::get()->query($upd_course_sql, $upd_course_args);
            }

            $userid_map = array();
            $user_file = $restoreThis . '/user';
            if (file_exists($user_file)) {
                $cours_user = unserialize(file_get_contents($restoreThis . '/' . $restoreHelper->getFile('course_user')));
                if ($clone_course) {
                    $userid_map = clone_users($cours_user, $restoreHelper);
                } else {
                    $userid_map = restore_users(unserialize(file_get_contents($user_file)), $cours_user, $departments, $restoreHelper);
                }
                register_users($new_course_id, $userid_map, $cours_user, $restoreHelper);
            }
            $userid_map[0] = 0;
            $userid_map[-1] = -1;
            if (!isset($userid_map[1])) {
                $userid_map[1] = 1;
            }

            $courseDir = "$webDir/courses/$new_course_code";
            $videoDir = "$webDir/video/$new_course_code";
            $oldCourseDir = $restoreThis . '/html';
            move_dir($oldCourseDir, $courseDir);

            if ($clone_course) {
                recurse_copy($webDir . '/video/' . $GLOBALS['currentCourseCode'], $webDir . '/video/' . $new_course_code);
            } else {
                if (is_dir($restoreThis . '/video_files')) {
                    move_dir($restoreThis . '/video_files', $videoDir);
                }
            }

            course_index($new_course_code);
            $tool_content .= "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . $GLOBALS['langCopyFiles'] . "</span></div></div>";

            require_once 'upgrade/functions.php';
            load_global_messages();

            $url_prefix_map = array(
                $config_data['urlServer'] . 'modules/ebook/show.php/' . $course_data['code'] =>
                    $urlServer . 'modules/ebook/show.php/' . $new_course_code,
                $config_data['urlAppend'] . 'modules/ebook/show.php/' . $course_data['code'] =>
                    $urlAppend . 'modules/ebook/show.php/' . $new_course_code,
                $config_data['urlServer'] . 'modules/document/file.php/' . $course_data['code'] =>
                    $urlServer . 'modules/document/file.php/' . $new_course_code,
                $config_data['urlAppend'] . 'modules/document/file.php/' . $course_data['code'] =>
                    $urlAppend . 'modules/document/file.php/' . $new_course_code,
                $config_data['urlServer'] . 'courses/' . $course_data['code'] =>
                    $urlServer . 'courses/' . $new_course_code,
                $config_data['urlAppend'] . 'courses/' . $course_data['code'] =>
                    $urlAppend . 'courses/' . $new_course_code,
                $course_data['code'] =>
                    $new_course_code);

            // Update course description URLs if needed
            $fixed_course_desc = strtr($course_desc, $url_prefix_map);
            if ($fixed_course_desc != $course_desc) {
                Database::get()->query('UPDATE course SET description = ?s WHERE id = ?d',
                    $fixed_course_desc, $new_course_id);
            }

            if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_3X) {
                restore_table($restoreThis, 'course_module', array('set' => array('course_id' => $new_course_id), 'delete' => array('id')), $url_prefix_map, $backupData, $restoreHelper);
                create_modules($new_course_id);
            } else if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X) {
                create_modules($new_course_id);
                foreach (get_tabledata_from_parsed('accueil', $backupData, $restoreHelper) as $accueil) {
                    Database::get()->query('UPDATE course_module SET visible = ?d WHERE course_id = ?d AND module_id = ?d',
                        $accueil['visible'], $new_course_id, $accueil['id']);
                }
            }
        } else {
            $new_course_id = $GLOBALS['course_id'];
            $new_course_code = $GLOBALS['course_code'];
            $backupData = null;
            $weekly_view = false;
            $courseDir = "$webDir/courses/$new_course_code";
            $videoDir = "$webDir/video/$new_course_code";
            $userid_map = array();
            $config_data = unserialize(file_get_contents($restoreThis . '/config_vars'));
            $restoreHelper = new RestoreHelper($config_data['version']);
            $course_file = $restoreThis . '/course';
            if (file_exists($course_file)) {
                $course_dataArr = unserialize(file_get_contents($course_file));
                $course_data = $course_dataArr[0];
            }

            if (file_exists($restoreThis . '/backup.php')) {
                $backupData = parse_backup_php($restoreThis . '/backup.php');
                $eclass_version = $backupData['eclass_version'];
            }

            // If old $urlAppend didn't end in /, add it
            if (!str_ends_with($config_data['urlAppend'], '/')) {
                $config_data['urlAppend'] .= '/';
            }
            $url_prefix_map = array(
                $config_data['urlServer'] . 'modules/ebook/show.php/' . $course_data['code'] =>
                    $urlServer . 'modules/ebook/show.php/' . $new_course_code,
                $config_data['urlAppend'] . 'modules/ebook/show.php/' . $course_data['code'] =>
                    $urlAppend . 'modules/ebook/show.php/' . $new_course_code,
                $config_data['urlServer'] . 'modules/document/file.php/' . $course_data['code'] =>
                    $urlServer . 'modules/document/file.php/' . $new_course_code,
                $config_data['urlAppend'] . 'modules/document/file.php/' . $course_data['code'] =>
                    $urlAppend . 'modules/document/file.php/' . $new_course_code,
                $config_data['urlServer'] . 'courses/' . $course_data['code'] =>
                    $urlServer . 'courses/' . $new_course_code,
                $config_data['urlAppend'] . 'courses/' . $course_data['code'] =>
                    $urlAppend . 'courses/' . $new_course_code,
                $course_data['code'] =>
                    $new_course_code);

            move_dir($restoreThis . '/html', $webDir . '/courses/' . $new_course_code);

            recurse_copy($webDir . '/video/' . $course_data['code'], $webDir . '/video/' . $new_course_code);
        }

        restore_table($restoreThis, 'announcement', array('set' => array('course_id' => $new_course_id), 'delete' => array('id', 'preview')), $url_prefix_map, $backupData, $restoreHelper);

        // Forums Restore
        $forum_category_map = restore_table($restoreThis, 'forum_category', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $forum_category_map[0] = 0;
        $forum_map = restore_table($restoreThis, 'forum', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id', 'map' => array('cat_id' => $forum_category_map)), $url_prefix_map, $backupData, $restoreHelper);
        $forum_map[0] = 0;
        $forum_topic_map = restore_table($restoreThis, 'forum_topic', array('return_mapping' => 'id',
            'map' => array('forum_id' => $forum_map, 'poster_id' => $userid_map)), $url_prefix_map, $backupData, $restoreHelper);
        $forum_topic_map[0] = 0;
        $forum_post_options = array('return_mapping' => 'id',
                                    'map' => array('topic_id' => $forum_topic_map,
                                                   'poster_id' => $userid_map));
        if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X) {
            $forum_post_options['set'] = array('post_text' => '');
        }
        $forum_post_map = restore_table($restoreThis, 'forum_post', $forum_post_options, $url_prefix_map, $backupData, $restoreHelper);
        $forum_post_map[0] = 0;
        restore_table($restoreThis, 'forum_notify', array('set' => array('course_id' => $new_course_id),
            'map' => array('user_id' => $userid_map, 'cat_id' => $forum_category_map, 'forum_id' => $forum_map, 'topic_id' => $forum_topic_map),
            'delete' => array('id')), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'forum_user_stats', array('set' => array('course_id' => $new_course_id),
        'map' => array('user_id' => $userid_map)), $url_prefix_map, $backupData, $restoreHelper);
        if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X
                && isset($backupData) && is_array($backupData)
                && isset($backupData['query']) && is_array($backupData['query'])) {
            $postsText = get_tabledata_from_parsed('posts_text', $backupData, $restoreHelper);
            foreach ($postsText as $ptData) {
                if (array_key_exists($ptData['post_id'], $forum_post_map)) {
                    Database::get()->query("UPDATE forum_post SET post_text = ?s WHERE id = ?d", $ptData['post_text'], intval($forum_post_map[$ptData['post_id']]));
                }
            }
        }
        $forum_ids = '(' . implode(', ', array_values($forum_map))  . ')';
        Database::get()->query("UPDATE forum
            SET num_topics = (SELECT COUNT(*) FROM forum_topic WHERE forum_id = forum.id),
                num_posts = (SELECT COUNT(*) FROM forum_topic, forum_post WHERE topic_id = forum_topic.id AND forum_id = forum.id),
                last_post_id = COALESCE((SELECT forum_post.id FROM forum_topic, forum_post WHERE topic_id = forum_topic.id AND forum_id = forum.id ORDER BY post_time LIMIT 1), 0)
            WHERE forum.id IN $forum_ids");
        Database::get()->query("UPDATE forum_topic
            SET last_post_id = (SELECT id FROM forum_post WHERE topic_id = forum_topic.id ORDER BY post_time LIMIT 1)
            WHERE forum_id IN $forum_ids");

        $forumLastPosts = Database::get()->queryArray("SELECT DISTINCT last_post_id FROM forum WHERE course_id = ?d ", intval($new_course_id));
        if (is_array($forumLastPosts) && count($forumLastPosts) > 0) {
            foreach ($forumLastPosts as $lastPost) {
                if (isset($forum_post_map[$lastPost->last_post_id])) {
                    Database::get()->query("UPDATE forum SET last_post_id = ?d WHERE course_id = ?d AND last_post_id = ?d", intval($forum_post_map[$lastPost->last_post_id]), intval($new_course_id), intval($lastPost->last_post_id));
                }
            }
        }

        $topicLastPosts = Database::get()->queryArray("SELECT DISTINCT last_post_id FROM forum_topic WHERE forum_id IN (SELECT id FROM forum WHERE course_id = ?d)", intval($new_course_id));
        if (is_array($topicLastPosts) && count($topicLastPosts) > 0) {
            foreach ($topicLastPosts as $lastPost) {
                if (isset($forum_post_map[$lastPost->last_post_id])) {
                    Database::get()->query("UPDATE forum_topic SET last_post_id = ?d WHERE last_post_id = ?d", intval($forum_post_map[$lastPost->last_post_id]), intval($lastPost->last_post_id));
                }
            }
        }

        $parentPosts = Database::get()->queryArray("SELECT DISTINCT parent_post_id FROM forum_post WHERE topic_id IN (SELECT id FROM forum_topic WHERE forum_id IN (SELECT id FROM forum WHERE course_id = ?d))", intval($new_course_id));
        if (is_array($parentPosts) && count($parentPosts) > 0) {
            foreach ($parentPosts as $parentPost) {
                if (isset($forum_post_map[$parentPost->parent_post_id])) {
                    Database::get()->query("UPDATE forum_post SET parent_post_id = ?d WHERE parent_post_id = ?d AND topic_id IN (SELECT id FROM forum_topic WHERE forum_id IN (SELECT id FROM forum WHERE course_id = ?d))", $forum_post_map[$parentPost->parent_post_id], $parentPost->parent_post_id, $new_course_id);
                }
            }
        }
        // Forums Restore End

        // groups restore
        $group_category_map  = restore_table($restoreThis, 'group_category',
            array('set' => array('course_id' => $new_course_id),
                                 'return_mapping' => 'id'),
            $url_prefix_map, $backupData, $restoreHelper);
        if (count($group_category_map) > 0) { // version >= 3.2
            $group_category_map[0] = 0;
            $group_map = restore_table($restoreThis, 'group',
                array('set' => array('course_id' => $new_course_id),
                      'map' => array(
                          'category_id' => $group_category_map,
                          'forum_id' => $forum_map
                        ),
                      'return_mapping' => 'id'),
                $url_prefix_map, $backupData, $restoreHelper);
        } else {
            $group_map = restore_table($restoreThis, 'group',
                array('set' => array('course_id' => $new_course_id),
                      'map' => array(
                          'forum_id' => $forum_map
                        ),
                      'init' => array('category_id' => 0),
                      'return_mapping' => 'id'),
                $url_prefix_map, $backupData, $restoreHelper);
        }

        restore_table($restoreThis, 'group_members',
            array('map' => array('group_id' => $group_map,
                  'user_id' => $userid_map)),
            $url_prefix_map, $backupData, $restoreHelper);

        $config_data = unserialize(file_get_contents($restoreThis . '/group_properties'));
        if (isset($config_data[0]['group_id'])) { // version >= 3.2
            restore_table($restoreThis, 'group_properties', [
                    'set' => ['course_id' => $new_course_id],
                    'map' => ['group_id' => $group_map],
                    'delete' => ['multiple_registration']],
                $url_prefix_map, $backupData, $restoreHelper);
        } else {
            $num = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d", $new_course_id);
            foreach ($num as $group_num) {
                $new_group_id = $group_num->id;
                restore_table($restoreThis, 'group_properties',
                    array('set' => array('course_id' => $new_course_id),
                          'init' => array('group_id' => $new_group_id),
                          'delete' => array('multiple_registration')),
                    $url_prefix_map, $backupData, $restoreHelper);
            }
        }

        // Glossary Restore
        $glossary_category_map = restore_table($restoreThis, 'glossary_category', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $glossary_category_map[0] = 0;
        restore_table($restoreThis, 'glossary', array('set' => array('course_id' => $new_course_id),
            'delete' => array('id'), 'map' => array('category_id' => $glossary_category_map)), $url_prefix_map, $backupData, $restoreHelper);
        // Glossary Restore End

        $link_category_map = restore_table($restoreThis, 'link_category', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $link_category_map[0] = 0;
        $link_category_map[-1] = -1;
        $link_category_map[-2] = -2;
        $link_map = restore_table($restoreThis, 'link',
            array('set' => array('course_id' => $new_course_id),
                  'delete' => array('hits'),
                  'map' => array('category' => $link_category_map, 'user_id' => $userid_map),
                  'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $ebook_map = restore_table($restoreThis, 'ebook', array('set' => array('course_id' => $new_course_id), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        foreach ($ebook_map as $old_id => $new_id) {
            // new and old id might overlap as the map contains multiple values!
            rename("$courseDir/ebook/$old_id", "$courseDir/ebook/__during_restore__$new_id");
        }
        foreach ($ebook_map as $old_id => $new_id) {
            // better to use an intermediary rename step
            rename("$courseDir/ebook/__during_restore__$new_id", "$courseDir/ebook/$new_id");
        }
        $document_map = restore_table($restoreThis, 'document', array('set' => array('course_id' => $new_course_id),
            'map_function' => 'document_map_function',
            'map_function_data' => array(1 => $group_map, 2 => $ebook_map),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $ebook_section_map = restore_table($restoreThis, 'ebook_section', array('map' => array('ebook_id' => $ebook_map),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $ebook_subsection_map = restore_table($restoreThis, 'ebook_subsection', array('map' => array('section_id' => $ebook_section_map,
            'file_id' => $document_map), 'delete' => array('file'), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);

        // Video
        $videocat_map = restore_table($restoreThis, 'video_category', array('set' => array('course_id' => $new_course_id), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $videocat_map[''] = '';
        $videocat_map[0] = 0;
        $video_map = restore_table($restoreThis, 'video', array(
            'map' => array('category' => $videocat_map),
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        $videolink_map = restore_table($restoreThis, 'videolink', array(
            'map' => array('category' => $videocat_map),
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);

        // Dropbox
        $dropbox_map = restore_table($restoreThis, 'dropbox_msg', array('set' => array('course_id' => $new_course_id),
                'map' => array('author_id' => $userid_map), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'dropbox_attachment', array('map' => array('msg_id' => $dropbox_map), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'dropbox_index', array('map' => array('msg_id' => $dropbox_map, 'recipient_id' => $userid_map)), $url_prefix_map, $backupData, $restoreHelper);

        // Learning Path
        $lp_learnPath_map = restore_table($restoreThis, 'lp_learnPath', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'learnPath_id'), $url_prefix_map, $backupData, $restoreHelper);
        $lp_module_map = restore_table($restoreThis, 'lp_module', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'module_id'), $url_prefix_map, $backupData, $restoreHelper);
        $lp_asset_map = restore_table($restoreThis, 'lp_asset', array('map' => array('module_id' => $lp_module_map),
            'return_mapping' => 'asset_id'), $url_prefix_map, $backupData, $restoreHelper);
        // update lp_module startAsset_id with new asset_id from map
        foreach ($lp_asset_map as $key => $value) {
            Database::get()->query("UPDATE lp_module SET `startAsset_id` = ?d "
                    . "WHERE `course_id` = ?d "
                    . "AND `startAsset_id` = ?d", intval($value), intval($new_course_id), intval($key));
        }
        $lp_rel_learnPath_module_map = restore_table($restoreThis, 'lp_rel_learnPath_module', array('map' => array('learnPath_id' => $lp_learnPath_map,
            'module_id' => $lp_module_map), 'return_mapping' => 'learnPath_module_id'), $url_prefix_map, $backupData, $restoreHelper);
        // update parent
        foreach ($lp_rel_learnPath_module_map as $key => $value) {
            Database::get()->query("UPDATE lp_rel_learnPath_module SET `parent` = ?d "
                    . "WHERE `learnPath_id` IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d) "
                    . "AND `parent` = ?d", intval($value), intval($new_course_id), intval($key));
        }
        restore_table($restoreThis, 'lp_user_module_progress', array('delete' => array('user_module_progress_id'),
            'map' => array('user_id' => $userid_map,
            'learnPath_module_id' => $lp_rel_learnPath_module_map,
            'learnPath_id' => $lp_learnPath_map)), $url_prefix_map, $backupData, $restoreHelper);
        foreach ($lp_learnPath_map as $old_id => $new_id) {
            // new and old id might overlap as the map contains multiple values!
            $old_dir = "$courseDir/scormPackages/path_$old_id";
            if (file_exists($old_dir) && is_dir($old_dir)) {
                rename($old_dir, "$courseDir/scormPackages/__during_restore__$new_id");
            }
        }
        foreach ($lp_learnPath_map as $old_id => $new_id) {
            // better to use an intermediary rename step
            $tempLPDir = "$courseDir/scormPackages/__during_restore__$new_id";
            if (file_exists($tempLPDir) && is_dir($tempLPDir)) {
                rename($tempLPDir, "$courseDir/scormPackages/path_$new_id");
            }
        }

        // Wiki
        $wiki_map = restore_table($restoreThis, 'wiki_properties', array('set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'wiki_acls', array('map' => array('wiki_id' => $wiki_map)), $url_prefix_map, $backupData, $restoreHelper);
        $wiki_pages_map = restore_table($restoreThis, 'wiki_pages', array('map' => array('wiki_id' => $wiki_map,
            'owner_id' => $userid_map), 'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'wiki_pages_content', array('delete' => array('id'),
            'map' => array('pid' => $wiki_pages_map, 'editor_id' => $userid_map)), $url_prefix_map, $backupData, $restoreHelper);

        // Blog
        if (file_exists("$restoreThis/blog_post")) {
            $blog_map = restore_table($restoreThis, 'blog_post', [
                'set' => ['course_id' => $new_course_id],
                'map' => ['user_id' => $userid_map],
                'return_mapping' => 'id',
            ], $url_prefix_map, $backupData, $restoreHelper);
        } else {
            $blog_map = array();
        }

        // Wall
        if (file_exists("$restoreThis/wall_post")) {
            $wall_map = restore_table($restoreThis, 'wall_post', array('set' => array('course_id' => $new_course_id),
                    'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);

            restore_table($restoreThis, 'wall_post_resources', array('delete' => array('id'),
            'map' => array('post_id' => $wall_map),
            'map_function' => 'wall_map_function',
            'map_function_data' => array($document_map, $video_map, $videolink_map)
            ), $url_prefix_map, $backupData, $restoreHelper);
        } else {
            $wall_map = array();
        }

        // Comments
        if (file_exists("$restoreThis/comments")) {
            $comment_map = restore_table($restoreThis, 'comments', array('delete' => array('id'),
            'map' => array('user_id' => $userid_map),
            'map_function' => 'comments_map_function',
            'map_function_data' => array($blog_map, $wall_map, $new_course_id),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        } else {
            $comment_map = array();
        }

        //Abuse Report
        if (file_exists("$restoreThis/abuse_report")) {
            restore_table($restoreThis, 'abuse_report', array('delete' => array('id'),
            'set' => array('course_id' => $new_course_id),
            'map' => array('user_id' => $userid_map),
            'map_function' => 'abuse_report_map_function',
            'map_function_data' => array($forum_post_map,
            $comment_map, $link_map, $wall_map)), $url_prefix_map, $backupData, $restoreHelper);
        }

        // Rating
        if (file_exists("$restoreThis/rating")) {
            restore_table($restoreThis, 'rating', array('delete' => array('rate_id'),
            'map' => array('user_id' => $userid_map),
            'map_function' => 'ratings_map_function',
            'map_function_data' => array($blog_map, $forum_post_map, $link_map, $wall_map,
            $new_course_id)), $url_prefix_map, $backupData, $restoreHelper);
        }
        if (file_exists("$restoreThis/rating_cache")) {
            restore_table($restoreThis, 'rating_cache', array('delete' => array('rate_cache_id'),
            'map_function' => 'ratings_map_function',
            'map_function_data' => array($blog_map, $forum_post_map, $link_map, $wall_map,
            $new_course_id)), $url_prefix_map, $backupData, $restoreHelper);
        }


        // Course_settings
        if (!$fetch_course) {
            if (file_exists("$restoreThis/course_settings")) {
                restore_table($restoreThis, 'course_settings', array('set' => array('course_id' => $new_course_id)), $url_prefix_map, $backupData, $restoreHelper);
            }
        }

        // Polls
        $poll_map = restore_table($restoreThis, 'poll', [
            'set' => ['course_id' => $new_course_id],
            'map' => ['creator_id' => $userid_map],
            'map_missing_keep' => ['creator_id'],
            'return_mapping' => 'pid',
            'delete' => ['type']],
             $url_prefix_map, $backupData, $restoreHelper);
        $poll_to_specific_map = restore_table($restoreThis, 'poll_to_specific', array('map' => array('poll_id' => $poll_map),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $poll_question_map = restore_table($restoreThis, 'poll_question', array('map' => array('pid' => $poll_map),
            'return_mapping' => 'pqid'), $url_prefix_map, $backupData, $restoreHelper);
        $poll_answer_map = restore_table($restoreThis, 'poll_question_answer', array('map' => array('pqid' => $poll_question_map),
            'return_mapping' => 'pqaid'), $url_prefix_map, $backupData, $restoreHelper);
        $poll_answer_map[0] = 0; // aid = 0 means "scale answer" - doesn't need mapping
        if (file_exists("$restoreThis/poll_user_record")) {
            // 3.2-style poll answer tables
            $poll_user_record_map = restore_table($restoreThis, 'poll_user_record',
                array('return_mapping' => 'id',
                      'map' => array('pid' => $poll_map, 'uid' => $userid_map)),
                $url_prefix_map, $backupData, $restoreHelper);
            restore_table($restoreThis, 'poll_answer_record',
                array('delete' => array('arid'),
                      'map' => array('qid' => $poll_question_map,
                                     'aid' => $poll_answer_map,
                                     'poll_user_record_id' => $poll_user_record_map)),
                $url_prefix_map, $backupData, $restoreHelper);
        } else {
            // 3.[0-1]-style tables
            restore_table($restoreThis, 'poll_answer_record',
                array('delete' => array('arid'),
                      'map' => array('qid' => $poll_question_map,
                                     'aid' => $poll_answer_map),
                      'map_function' => 'poll_map_function',
                      'map_function_data' => array($userid_map, $poll_map)),
                $url_prefix_map, $backupData, $restoreHelper);
        }


        // Rubrics - Scales
        $rubric_map = restore_table($restoreThis, 'rubric', array(
                        'return_mapping' => 'id',
                        'set' => array('course_id' => $new_course_id)
        ), $url_prefix_map, $backupData, $restoreHelper);
        $rubric_map[0] = 0;

        $grading_scale_map = restore_table($restoreThis, 'grading_scale', array(
                        'return_mapping' => 'id',
                        'set' => array('course_id' => $new_course_id)
        ), $url_prefix_map, $backupData, $restoreHelper);
        $grading_scale_map[0] = 0;

        // Assignments
        if (!isset($group_map[0])) {
            $group_map[0] = 0;
        }
        $assignments_map = restore_table($restoreThis, 'assignment',
            array('set' => array('course_id' => $new_course_id),
                                  'map_function' => 'rubric_scaling_map_function',
                                  'map_function_data' => array($rubric_map, $grading_scale_map),
                                  'return_mapping' => 'id',
                                  'init' => array('max_grade' => 10)),
            $url_prefix_map, $backupData, $restoreHelper);
        $assignments_map[0] = 0;
        $assignment_to_specific_map = restore_table($restoreThis, 'assignment_to_specific', array('map' => array('assignment_id' => $assignments_map)),
                $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'assignment_submit', array('delete' => array('id'),
            'map' => array('uid' => $userid_map, 'assignment_id' => $assignments_map, 'group_id' => $group_map)), $url_prefix_map, $backupData, $restoreHelper);

        // delete assignment submission files which haven't been restored
        $files = [];
        $baseDir = "$webDir/courses/$new_course_code/work";
        foreach ($assignments_map as $old_id => $new_id) {
            Database::get()->queryFunc('SELECT file_path FROM assignment
                WHERE id = ?d',
                function ($item) use (&$files, $baseDir) {
                    if ($item->file_path) {
                        $files[] = $baseDir . '/admin_files/' . $item->file_path;
                    }
                }, $new_id);
            Database::get()->queryFunc('SELECT file_path, grade_comments_filepath
                FROM assignment_submit WHERE assignment_id = ?d',
                function ($item) use (&$files, $baseDir) {
                    if ($item->file_path) {
                        $files[] = $baseDir . '/' . $item->file_path;
                    }
                    if ($item->grade_comments_filepath) {
                        $files[] = $baseDir . '/admin_files/' . $item->grade_comments_filepath;
                    }
                }, $new_id);
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator("courses/$new_course_code/work"),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $name => $file) {
            if (!$file->isDir()) {
                $filePath = "$webDir/$name";
                if (!in_array($filePath, $files)) {
                    unlink($filePath);
                }
            }
        }

        // Agenda
        $agenda_map = restore_table($restoreThis, 'agenda', array(
            'return_mapping' => 'id',
            'set' => array('course_id' => $new_course_id)
        ), $url_prefix_map, $backupData, $restoreHelper);
        $agenda_map[0] = 0;

        // Exercises
        $exercise_map = restore_table($restoreThis, 'exercise', array(
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'
            ), $url_prefix_map, $backupData, $restoreHelper);
        $exercise_map[0] = 0;
        $exercise_to_specific_map = restore_table($restoreThis, 'exercise_to_specific', array('map' => array('exercise_id' => $exercise_map),
            'return_mapping' => 'id'), $url_prefix_map, $backupData, $restoreHelper);
        $question_category_map = restore_table($restoreThis, 'exercise_question_cats', array(
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'question_cat_id'
            ), $url_prefix_map, $backupData, $restoreHelper);
        $question_category_map[0] = 0;
        // global $exercise_question_q_position set as side-effect by
        // 'extract_field' => 'q_position'
        $question_map = restore_table($restoreThis, 'exercise_question', array(
            'set' => array('course_id' => $new_course_id),
            'init' => array('category' => 0),
            'map' => array('category' => $question_category_map),
            'return_mapping' => 'id',
            'extract_field' => 'q_position'
            ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'exercise_answer', array(
            'delete' => array('id'),
            'map' => array('question_id' => $question_map),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'exercise_with_questions', array(
            'delete' => array('id'),
            'map' => array('question_id' => $question_map, 'exercise_id' => $exercise_map),
            'insert_field' => 'q_position',
            'insert_field_key' => 'question_id',
            'insert_field_data' => $GLOBALS['exercise_question_q_position']
            ), $url_prefix_map, $backupData, $restoreHelper);
        $eurid_map = restore_table($restoreThis, 'exercise_user_record', array(
            'return_mapping' => 'eurid',
            'map' => array('eid' => $exercise_map, 'uid' => $userid_map)
            ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'exercise_answer_record', array(
            'delete' => array('answer_record_id'),
            'insert_field' => 'q_position',
            'insert_field_key' => 'question_id',
            'insert_field_data' => $GLOBALS['exercise_question_q_position'],
            'map' => array('question_id' => $question_map,
                'eurid' => $eurid_map)
            ), $url_prefix_map, $backupData, $restoreHelper);
        // rename question images
        foreach (glob("$courseDir/image/quiz-*") as $imagefile) {
            if (preg_match('/quiz-(\d+)$/', $imagefile, $matches)) {
                $oldid = $matches[1];
                if (isset($question_map[$oldid])) {
                    $newid = $question_map[$oldid];
                    $newimagefile = str_replace("quiz-$oldid", "quiz-$newid", $imagefile);
                    rename($imagefile, $newimagefile);
                }
            }
        }

        $sql = "SELECT asset.asset_id, asset.path FROM `lp_module` AS module, `lp_asset` AS asset
                        WHERE module.startAsset_id = asset.asset_id
                        AND course_id = ?d AND contentType = 'EXERCISE' AND path <> '' AND path IS NOT NULL";
        $rows = Database::get()->queryArray($sql, intval($new_course_id));

        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                Database::get()->query("UPDATE `lp_asset` SET path = ?s WHERE asset_id = ?d", $exercise_map[$row->path], intval($row->asset_id));
            }
        }

        // Attendance
        $start_date = date('Y-m-d H:i:s', strtotime("-6 month"));
        $end_date = date('Y-m-d H:i:s', strtotime("6 month"));
        $attendance_map = restore_table($restoreThis, 'attendance', array(
            'set' => array('course_id' => $new_course_id),
            'init' => array('start_date' => $start_date, 'end_date' => $end_date),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        $attendance_activities_map = restore_table($restoreThis, 'attendance_activities', array(
            'map' => array('attendance_id' => $attendance_map),
            'map_function' => 'attendance_gradebook_activities_map_function',
            'map_function_data' => array($assignments_map, $exercise_map),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'attendance_book', array(
            'map' => array(
            'attendance_activity_id' => $attendance_activities_map,
            'uid' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'attendance_users', array(
            'map' => array(
                'attendance_id' => $attendance_map,
                'uid' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);

        // Gradebook
        $gradebook_map = restore_table($restoreThis, 'gradebook', array(
            'set' => array('course_id' => $new_course_id),
            'init' => array('start_date' => $start_date, 'end_date' => $end_date),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        $gradebook_activities_map = restore_table($restoreThis, 'gradebook_activities', array(
            'map' => array('gradebook_id' => $gradebook_map),
            'map_function' => 'attendance_gradebook_activities_map_function',
            'map_function_data' => array($assignments_map, $exercise_map),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'gradebook_book', array(
            'map' => array(
            'gradebook_activity_id' => $gradebook_activities_map,
            'uid' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'gradebook_users', array(
            'map' => array(
            'gradebook_id' => $gradebook_map,
            'uid' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);


        // Course Units
        if (!$weekly_view) {
            $unit_map = restore_table($restoreThis, 'course_units',
                array('set' =>
                    array('course_id' => $new_course_id),
                    'return_mapping' => 'id',
                ), $url_prefix_map, $backupData, $restoreHelper);
            restore_table($restoreThis, 'unit_resources', array('delete' => array('id'),
                'map' => array('unit_id' => $unit_map),
                'map_function' => 'unit_map_function',
                'map_function_data' => array($document_map,
                    $link_category_map,
                    $link_map,
                    $ebook_map,
                    $ebook_section_map,
                    $ebook_subsection_map,
                    $video_map,
                    $videolink_map,
                    $videocat_map,
                    $lp_learnPath_map,
                    $wiki_map,
                    $assignments_map,
                    $exercise_map,
                    $forum_map,
                    $forum_topic_map,
                    $poll_map)
            ), $url_prefix_map, $backupData, $restoreHelper);

            restore_table($restoreThis, 'unit_prerequisite',
                array('set' => array('course_id' => $new_course_id),
                    'map' => array('unit_id' => $unit_map,
                        'prerequisite_unit' => $unit_map),
                    'delete' => array('id'),
                ), $url_prefix_map, $backupData, $restoreHelper);
        }
        $unit_map[0] = 0;

        // Certificate
        $certificate_map = restore_table($restoreThis, 'certificate', array(
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        $certificate_criterion_map = restore_table($restoreThis, 'certificate_criterion', array(
            'map' => array('certificate' => $certificate_map),
            'map_function' => 'certificate_criterion_map_function',
            'map_function_data' => array($document_map, $video_map, $videolink_map,
                                         $blog_map, $forum_map, $forum_topic_map,
                                         $lp_learnPath_map, $ebook_map, $poll_map,
                                         $wiki_map, $assignments_map, $exercise_map),
            'delete' => array('id'),
            'return_mapping' => 'id',
            ), $url_prefix_map, $backupData, $restoreHelper);

        restore_table($restoreThis, 'user_certificate_criterion', array(
            'map' => array(
                'certificate_criterion' => $certificate_criterion_map,
                'user' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);

        // Badge
        $badge_map = restore_table($restoreThis, 'badge', array(
            'set' => array('course_id' => $new_course_id),
            'map' => array('unit_id' => $unit_map),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);

        $badge_criterion_map = restore_table($restoreThis, 'badge_criterion', array(
            'map' => array('badge' => $badge_map),
            'map_function' => 'badge_criterion_map_function',
            'map_function_data' => array($document_map, $video_map, $videolink_map,
                                         $blog_map, $forum_map, $forum_topic_map,
                                         $lp_learnPath_map, $ebook_map, $poll_map,
                                         $wiki_map, $assignments_map, $exercise_map),
            'delete' => array('id'),
            'return_mapping' => 'id'
            ), $url_prefix_map, $backupData, $restoreHelper);

        restore_table($restoreThis, 'user_badge', array(
            'map' => array(
                'badge' => $badge_map,
                'user' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);

        restore_table($restoreThis, 'user_badge_criterion', array(
            'map' => array(
                'badge_criterion' => $badge_criterion_map,
                'user' => $userid_map
            ),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);

        // Notes
        restore_table($restoreThis, 'note', array(
            'set' => array('reference_obj_course' => $new_course_id),
            'map' => array('user_id' => $userid_map),
            'map_function' => 'notes_map_function',
            'map_function_data' => array($new_course_id, $agenda_map, $document_map, $link_map,
                $video_map, $videolink_map, $assignments_map, $exercise_map, $ebook_map,
                $lp_learnPath_map),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);

        // H5P
        $h5p_content_map = restore_table($restoreThis, 'h5p_content', array(
            'set' => array('course_id' => $new_course_id),
            'return_mapping' => 'id'
        ), $url_prefix_map, $backupData, $restoreHelper);
        restore_table($restoreThis, 'h5p_content_dependency', array(
            'map' => array('content_id' => $h5p_content_map),
            'delete' => array('id')
        ), $url_prefix_map, $backupData, $restoreHelper);
        foreach ($h5p_content_map as $hp5_content_oldid => $h5p_content_newid) {
            $h5p_content_olddir = $courseDir . "/h5p/content/" . $hp5_content_oldid;
            if (file_exists($h5p_content_olddir) && is_dir($h5p_content_olddir)) {
                $h5p_content_newdir = $courseDir . "/h5p/content/" . $h5p_content_newid;
                move_dir($h5p_content_olddir, $h5p_content_newdir);
            }
        }

        // actions
        restore_table($restoreThis, 'actions_daily',
            array('set' => array('course_id' => $new_course_id),
                  'map' => array('user_id' => $userid_map),
                  'delete' => array('id')
            ),
            $url_prefix_map, $backupData, $restoreHelper);

        //log
        restore_table($restoreThis, 'log',
            array('set' => array('course_id' => $new_course_id),
                  'map' => array('user_id' => $userid_map),
                  'delete' => array('id')
            ),
            $url_prefix_map, $backupData, $restoreHelper);

        // Weekly - deprecated as of 3.7, but need to move to units
        if ($weekly_view) {
            $weekly_map = restore_table($restoreThis, 'course_weekly_view', array(
                'set' => array('course_id' => $new_course_id),
                'return_mapping' => 'id',
                'map_function' => 'comments_not_null',
                'target_table' => 'course_units'
            ), $url_prefix_map, $backupData, $restoreHelper);
            $week = 1;
            foreach ($weekly_map as $old_id => $new_id) {
                $weekTitle = Database::get()->querySingle('SELECT title FROM course_units WHERE id = ?d', $new_id)->title;
                if (!$weekTitle) {
                    $weekTitle = getTranslation('langWeek', $course_lang) . ' ' . $week;
                    Database::get()->querySingle('UPDATE course_units
                        SET title = ?s WHERE id = ?d', $weekTitle, $new_id);
                }
                $week++;
            }
            restore_table($restoreThis, 'course_weekly_view_activities', array(
                'delete' => array('id'),
                'target_table' => 'unit_resources',
                'map' => array('course_weekly_view_id' => $weekly_map),
                'map_function' => 'unit_map_function',
                'map_function_data' => array($document_map,
                    $link_category_map,
                    $link_map,
                    $ebook_map,
                    $ebook_section_map,
                    $ebook_subsection_map,
                    $video_map,
                    $videolink_map,
                    $videocat_map,
                    $lp_learnPath_map,
                    $wiki_map,
                    $assignments_map,
                    $exercise_map,
                    $forum_map,
                    $forum_topic_map,
                    $poll_map)
                ), $url_prefix_map, $backupData, $restoreHelper);
        }

        restore_table($restoreThis, 'course_description', array(
            'set' => array('course_id' => $new_course_id),
            'delete' => array('id')
            ), $url_prefix_map, $backupData, $restoreHelper);

        // Course category metadata - restore only if cloning
        // TODO: handle restore if categories are identical to source instance
        if ($clone_course) {
            restore_table($restoreThis, 'course_category', array(
                'set' => array('course_id' => $new_course_id),
                'delete' => array('id')
                ), $url_prefix_map, $backupData, $restoreHelper);
        }

        // Course activity-type course - restore only if cloning
        // TODO: handle restore if headings are identical to source instance
        if ($clone_course) {
            restore_table($restoreThis, 'activity_content', array(
                'set' => array('course_id' => $new_course_id),
                'delete' => array('id')
                ), $url_prefix_map, $backupData, $restoreHelper);
        }

        fix_media_links($course_data['code'], $new_course_code, $new_course_id, $video_map);

        removeDir($restoreThis);

        // index course after restoring
        require_once 'modules/search/classes/ConstantsUtil.php';
        require_once 'modules/search/classes/SearchEngineFactory.php';
        $searchEngine = SearchEngineFactory::create();
        $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVEALLBYCOURSE, ConstantsUtil::RESOURCE_IDX, $new_course_id);
        $searchEngine->indexResource(ConstantsUtil::REQUEST_STOREALLBYCOURSE, ConstantsUtil::RESOURCE_IDX, $new_course_id);
    });

    // check/cleanup video files after restore transaction
    if ($new_course_code != null && $new_course_id != null) {
        if (!$clone_course) {
            require_once 'include/lib/fileUploadLib.inc.php';
            $videodir = $webDir . "/video/" . $new_course_code;
            $videos = scandir($videodir);
            foreach ($videos as $videofile) {
                if (is_dir($videofile)) {
                    continue;
                }

                $vlike = '/' . $videofile;

                if (!isWhitelistAllowed($videofile)) {
                    unlink($videodir . "/" . $videofile);
                    Database::get()->query("DELETE FROM `video` WHERE course_id = ?d AND path LIKE ?s", $new_course_id, $vlike);
                    continue;
                }

                $vcnt = Database::get()->querySingle("SELECT count(id) AS count FROM `video` WHERE course_id = ?d AND path LIKE ?s", $new_course_id, $vlike)->count;
                if ($vcnt <= 0) {
                    unlink($videodir . "/" . $videofile);
                }
            }
        }


        $backUrl = $urlAppend . (isset($currentCourseCode)? "courses/$currentCourseCode/": 'modules/admin/');
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => $backUrl,
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
            array('title' => $langEnter,
                  'url' => $urlAppend . "courses/$new_course_code/",
                  'icon' => 'fa-arrow-right',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success')
            ), false);

    }
}


function getid($map, $old_id) {
    if (isset($map[$old_id])) {
        return $map[$old_id];
    } else {
        return null;
    }
}

function fix_media_links($oldcode, $newcode, $new_course_id, $video_map) {
    $fixes = [
      [ 'table' => 'course',
        'field' => 'description',
        'query' => 'SELECT id, description FROM course
                       WHERE id = ?d' ],
      [ 'table' => 'course_units',
        'field' => 'comments',
        'query' => 'SELECT id, comments FROM course_units
                       WHERE course_id = ?d' ],
      [ 'table' => 'unit_resources',
        'field' => 'comments',
        'query' => 'SELECT id, comments FROM unit_resources
                       WHERE unit_id IN (SELECT id FROM course_units WHERE course_id = ?d)' ],
      [ 'table' => 'assignment',
        'field' => 'description',
        'query' => 'SELECT id, description FROM assignment
                       WHERE course_id = ?d' ],
      [ 'table' => 'exercise_question',
        'field' => 'description',
        'query' => 'SELECT id, description FROM exercise_question
                       WHERE course_id = ?d' ],
      [ 'table' => 'exercise_answer',
        'field' => 'answer',
        'query' => 'SELECT id, answer FROM exercise_answer
                       WHERE question_id IN (SELECT id FROM exercise_question
                           WHERE course_id = ?d)' ],
    ];

    foreach ($fixes as $fix) {
        $all = Database::get()->queryArray($fix['query'], $new_course_id);
        foreach ($all as $entry) {
            $field = $fix['field'];
            $table = $fix['table'];
            if (!is_null($entry->$field)) {
                $newcontents = preg_replace_callback('<(/video/(?:file|play).php\?course=\w+&amp;id=)(\d+)>',
                    function ($match) use ($video_map, $oldcode, $newcode) {
                        $new_id = getid($video_map, $match[2]);
                        if ($new_id) {
                            // $fix[table]: $match[2] => $new_id
                            $base = str_replace("course=$oldcode", "course=$newcode", $match[1]);
                            return $base . $new_id;
                        } else {
                            // $fix[table]: $match[2] not found
                            return $match[1] . $match[2];
                        }
                    }, $entry->$field);

                if ($entry->$field != $newcontents) {
                    Database::get()->query("UPDATE `$table`
                SET `$field` = ?s WHERE id = ?d",
                        $newcontents, $entry->id);
                }
            }
        }
    }
}

function clone_users($cours_user, $restoreHelper) {
    global $uid;

    $userid_map = array();
    foreach ($cours_user as $item) {
        $user_id = $item['user_id'];
        $is_teacher = $item['status'] == USER_TEACHER;
        if ($_POST['add_users'] == 'all' or
            ($_POST['add_users'] == 'prof' and $is_teacher)) {
                $userid_map[$user_id] = $user_id;
        } elseif ($_POST['add_users'] == 'none' and $is_teacher) {
            // when adding no users, just map the first prof to the current
            // user and return the single-element mapping array
            $userid_map[$user_id] = $uid;
            return $userid_map;
        }
    }
    return $userid_map;
}

function restore_users($users, $cours_user, $departments, $restoreHelper) {
    global $tool_content, $langRestoreUserExists, $langRestoreUserNew, $uid;

    $userid_map = array();
    if ($_POST['add_users'] == 'none') {
        // find the 1st teacher (oldid)
        foreach ($cours_user as $cudata) {
            if (intval($cudata[$restoreHelper->getField('course_user', 'status')]) === USER_TEACHER) {
                $old_id = $cudata['user_id'];
                $userid_map[$old_id] = $uid;
                break;
            }
        }
        return $userid_map;
    }

    if ($_POST['add_users'] == 'prof') {
        $add_only_profs = true;
        foreach ($cours_user as $cu_info) {
            $is_prof[$cu_info['user_id']] = ($cu_info[$restoreHelper->getField('course_user', 'status')] == 1);
        }
    } else {
        $add_only_profs = false;
    }

    require_once 'include/lib/user.class.php';
    foreach ($users as $data) {
        if ($add_only_profs and !$is_prof[$data[$restoreHelper->getField('user', 'id')]]) {
            continue;
        }
        $u = Database::get()->querySingle("SELECT * FROM user WHERE BINARY username = ?s", $data['username']);
        if ($u) {
            $userid_map[$data[$restoreHelper->getField('user', 'id')]] = $u->id;
            $tool_content .= "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" .
                sprintf($langRestoreUserExists,
                    '<b>' . q($data['username']) . '</b>',
                    '<i>' . q(trim($u->givenname . ' ' . $u->surname)) . '</i>',
                    '<i>' . q(trim($data[$restoreHelper->getField('user', 'givenname')] .
                        ' ' . $data[$restoreHelper->getField('user', 'surname')])) . '</i>') .
                "</span></div></div>\n";
        } elseif (isset($_POST['create_users'])) {
            $now = date('Y-m-d H:i:s', time());
            $user_id = Database::get()->query("INSERT INTO user SET surname = ?s, "
                . "givenname = ?s, username = ?s, password = ?s, email = ?s, status = ?d, phone = ?s, "
                . "registered_at = ?t, expires_at = ?t",
                (isset($data[$restoreHelper->getField('user', 'surname')])) ? $data[$restoreHelper->getField('user', 'surname')] : '',
                (isset($data[$restoreHelper->getField('user', 'givenname')])) ? $data[$restoreHelper->getField('user', 'givenname')] : '',
                $data['username'],
                isset($data['password'])? $data['password']: 'empty',
                isset($data['email'])? $data['email']: '',
                intval($data[$restoreHelper->getField('course_user', 'status')]),
                isset($data['phone'])? $data['phone']: '',
                $now,
                date('Y-m-d H:i:s', time() + get_config('account_duration')))->lastInsertID;
            $userid_map[$data[$restoreHelper->getField('user', 'id')]] = $user_id;
            // update personal calendar info table
            // we don't check if trigger exists since it requires `super` privilege
            Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $user_id);
            $user = new User();
            $user->refresh($user_id, $departments);
            user_hook($user_id);
            $tool_content .= "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" .
                sprintf($langRestoreUserNew,
                    '<b>' . q($data['username']) . '</b>',
                    '<i>' . q($data[$restoreHelper->getField('user', 'givenname')] .
                        ' ' . $data[$restoreHelper->getField('user', 'surname')]) . '</i>') .
                "</span></div></div>\n";
        }
    }
    return $userid_map;
}

function register_users($course_id, $userid_map, $cours_user, $restoreHelper) {
    global $langPrevId, $langNewId, $tool_content;

    foreach ($cours_user as $cudata) {
        $old_id = $cudata['user_id'];
        if (isset($userid_map[$old_id])) {
            $status[$old_id] = $cudata[$restoreHelper->getField('course_user', 'status')];
            $tutor[$old_id] = $cudata['tutor'];
            if (isset($cudata['editor'])) {
                $editor[$old_id] = $cudata['editor'];
            } else {
                $editor[$old_id] = ($status[$old_id] == USER_TEACHER);
            }
            $reviewer[$old_id] = (isset($cudata['reviewer'])) ? $cudata['reviewer'] : 0;
            $reg_date[$old_id] = $cudata['reg_date'];
            $receive_mail[$old_id] = $cudata['receive_mail'];
            $document_timestamp[$old_id] = isset($cudata['document_timestamp'])?
                $cudata['document_timestamp']: date('Y-m-d H:i:s', time());
        }
    }

    foreach ($userid_map as $old_id => $new_id) {
        if ($document_timestamp[$old_id] == '0000-00-00 00:00:00') { // invalide date time value for mysql >= 5.7
            Database::get()->query("INSERT INTO course_user SET course_id = ?d, user_id = ?d, status = ?d, tutor = ?d, editor = ?d, "
                . "reviewer = ?d, reg_date = ?t, receive_mail = ?d, document_timestamp = " . DBHelper::timeAfter() . "",
                intval($course_id),
                intval($new_id),
                intval($status[$old_id]),
                intval($tutor[$old_id]),
                intval($editor[$old_id]),
                intval($reviewer[$old_id]),
                $reg_date[$old_id],
                intval($receive_mail[$old_id]));
        } else {
            Database::get()->query("INSERT INTO course_user SET course_id = ?d, user_id = ?d, status = ?d, tutor = ?d, editor = ?d, "
                    . "reviewer = ?d, reg_date = ?t, receive_mail = ?d, document_timestamp = ?t",
                    intval($course_id),
                    intval($new_id),
                    intval($status[$old_id]),
                    intval($tutor[$old_id]),
                    intval($editor[$old_id]),
                    intval($reviewer[$old_id]),
                    $reg_date[$old_id],
                    intval($receive_mail[$old_id]),
                    $document_timestamp[$old_id]);
        }
    }
}

function inner_unquote($s) {
    return str_replace(array('\"', "\\\0"), array('"', "\0"), $s);
}

function parse_backup_php($file) {
    global $durationAccount;

    $source = preg_replace('/^<\?\n/m', "<?php\n", file_get_contents($file));
    if (!preg_match('/encoding = .UTF-8./', $source)) {
        $source = iconv('ISO-8859-7', 'UTF-8//IGNORE', $source);
    }
    $tokens = token_get_all($source);
    $info = array();
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];
        if (!is_string($token)) {
            list($id, $text) = $token;
            if ($id == T_VARIABLE) {
                $varname = substr($text, 1);
                do {
                    $i++;
                } while ($tokens[$i] == '=' or
                $tokens[$i][0] == T_WHITESPACE);
                list($id, $text) = $tokens[$i];
                if ($id == T_CONSTANT_ENCAPSED_STRING or
                        $id == T_LNUMBER) {
                    $value = eval("return($text);");
                    $info[$varname] = $value;
                }
            } elseif ($id == T_STRING) {
                list($i, $args) = get_args($tokens, ++$i);
                if ($text == 'query') {
                    $sql = $args[0];
                    if (preg_match('/^INSERT INTO `(\w+)` \(([^)]+)\) VALUES\s+(.*)$/si', $sql, $matches)) {
                        $table = $matches[1];
                        // Skip 'stat_accueil' and 'users' (not used any longer) and
                        // 'actions' and 'logins' which can grow very large
                        if (!in_array($table, array('stat_accueil', 'users', 'actions', 'logins'))) {
                            $info['query'][] = array(
                                'table' => $table,
                                'fields' => parse_fields($matches[2]),
                                'values' => parse_values($matches[3]));
                        }
                    }
                } elseif ($text == 'course_details') {
                    $info['code'] = $args[0];
                    $info['lang'] = $args[1];
                    $info['title'] = $args[2];
                    $info['description'] = $args[3];
                    $info['faculty'] = $args[4];
                    $info['visible'] = $args[5];
                    $info['prof_names'] = $args[6];
                    $info['type'] = $args[7];
                } elseif ($text == 'announcement') {
                    $info['announcement'][] = make_assoc($args, array('contenu', 'temps', 'ordre', 'title'));
                } elseif ($text == 'user') {
                    if (!isset($args[9])) {
                        $args[9] = time();
                        $args[10] = time() + $durationAccount;
                    }
                    $info['user'][] = make_assoc($args, array('id', 'name', 'surname', 'username', 'password',
                        'email', 'status', 'phone', 'department',
                        'registered_at', 'expires_at'));
                } elseif ($text == 'assignment_submit') {
                    $info['assignment_submit'][] = make_assoc($args, array('uid', 'assignment_id', 'submission_date',
                        'submission_ip', 'file_path', 'file_name',
                        'comments', 'grade', 'grade_comments',
                        'grade_submission_date', 'grade_submission_ip'));
                } elseif ($text == 'dropbox_file') {
                    $info['dropbox_file'][] = make_assoc($args, array('uploader_id', 'filename', 'filesize', 'title',
                        'description', 'author', 'upload_date', 'last_upload_date'));
                } elseif ($text == 'dropbox_person') {
                    $info['dropbox_person'][] = array(
                        'file_id' => $args[0],
                        'person_id' => $args[1]);
                } elseif ($text == 'dropbox_post') {
                    $info['dropbox_post'][] = array(
                        'file_id' => $args[0],
                        'recipient_id' => $args[1]);
                } elseif ($text == 'group') {
                    $info['group'][] = make_assoc($args, array('user', 'team', 'status', 'role'));
                } elseif ($text == 'course_units') {
                    $info['course_units'][] = make_assoc($args, array('title', 'comments', 'visibility', 'order',
                        'resource_units'));
                } else {
                    $info[$text] = $args;
                }
            } /* else {
              if ($id != T_WHITESPACE) {
              echo token_name($id), ": ", q($text), '<br>';
              }
            } */
        }
    }
    return $info;
}

function make_assoc($args, $names) {
    foreach ($args as $i => $value) {
        $assoc[$names[$i]] = $value;
    }
    return $assoc;
}

function get_args($tokens, $i) {
    $args = array();
    do {
        if (!is_string($tokens[$i])) {
            if ($tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING or
                    $tokens[$i][0] == T_LNUMBER) {
                $args[] = eval("return({$tokens[$i][1]});");
            } elseif ($tokens[$i][0] == T_ARRAY) {
                list($i, $args1) = get_args($tokens, ++$i);
                $args[] = $args1;
            }
        }
        $i++;
    } while ($tokens[$i] != ')');
    return array($i, $args);
}

function parse_fields($s) {
    return preg_split('/[`, ]/', $s, null, PREG_SPLIT_NO_EMPTY);
}

function parse_values($s) {
    $values = array();
    $tokens = token_get_all('<?php ' . $s . ';');
    foreach ($tokens as $token) {
        if ($token == '(') {
            $vtmp = array();
        } elseif ($token == ')') {
            $values[] = $vtmp;
        } elseif (isset($token[0]) and
                ($token[0] == T_CONSTANT_ENCAPSED_STRING or
                $token[0] == T_LNUMBER)) {
            $vtmp[] = eval("return({$token[1]});");
        }
    }
    return $values;
}

function get_serialized_file($file) {
    global $base;

    $file = $base . '/' . $file;
    if (file_exists($file)) {
        return unserialize(file_get_contents($file));
    } else {
        return false;
    }
}

function search_table_dump($table, $field, $value) {
    foreach ($table as $id => $data) {
        if (isset($data[$field]) and
                $data[$field] == $value) {
            return $data;
        }
    }
    return null;
}

// Translate 3.1-style poll_answer_record data to 3.2-style poll_answer_record
// and poll_user_record
function poll_map_function(&$data, $maps) {
    static $poll_user_record_map = array();

    $uid_map = $maps[0];
    $poll_map = $maps[1];

    $uid = $data['user_id'];
    $pid = $data['pid'];
    unset($data['user_id']);
    unset($data['pid']);

    // If user doesnt exist in target, skip this record
    if (!isset($uid_map[$uid]) or !isset($poll_map[$pid])) {
        return false;
    }

    $uid = $uid_map[$uid];
    $pid = $poll_map[$pid];
    if (isset($poll_user_record_map[$uid][$pid])) {
        $data['poll_user_record_id'] = $poll_user_record_map[$uid][$pid];
    } else {
        $pum_id = Database::get()->query('INSERT INTO poll_user_record
            SET pid = ?d, uid = ?d', $pid, $uid)->lastInsertID;
        $poll_user_record_map[$uid][$pid] = $pum_id;
    }
    return true;
}

function document_map_function(&$data, $maps) {
    // $maps[1]: group map, $maps[2]: ebook map
    $stype = $data['subsystem'];
    $sid = $data['subsystem_id'];
    if ($stype > 0) {
        if (isset($maps[$stype][$sid])) {
            $data['subsystem_id'] = $maps[$stype][$sid];
        } else {
            return false;
        }
    }
    if (!isset($data['extra_path'])) {
        $data['extra_path'] = '';
    }
    if (!$data['author']) {
        $data['author'] = '';
    }
    return true;
}

function unit_map_function(&$data, $maps) {
    // opoy yparxei if isset, isxyei h:
    // idia symbash/paradoxh me to attendance_gradebook_activities_map_function()
    // des to ekei comment gia ta spasmena FKs
    list($document_map, $link_category_map, $link_map, $ebook_map, $section_map, $subsection_map, $video_map, $videolink_map, $video_category_map, $lp_learnPath_map, $wiki_map, $assignments_map, $exercise_map, $forum_map, $forum_topic_map, $poll_map) = $maps;
    if ($data['type'] == 'videolinks') {
        $data['type'] == 'videolink';
    }
    if (isset($data['course_weekly_view_id'])) {
        $data['unit_id'] = $data['course_weekly_view_id'];
        unset($data['unit_id']);
    }
    $type = $data['type'];
    if ($type == 'doc') {
        $data['res_id'] = @$document_map[$data['res_id']];
    } elseif ($type == 'linkcategory') {
        $data['res_id'] = @$link_category_map[$data['res_id']];
    } elseif ($type == 'link') {
        $data['res_id'] = @$link_map[$data['res_id']];
    } elseif ($type == 'ebook') {
        $data['res_id'] = @$ebook_map[$data['res_id']];
    } elseif ($type == 'section') {
        $data['res_id'] = @$section_map[$data['res_id']];
    } elseif ($type == 'subsection') {
        $data['res_id'] = @$subsection_map[$data['res_id']];
    } elseif ($type == 'description') {
        $data['res_id'] = intval($data['res_id']);
    } elseif ($type == 'video') {
        $data['res_id'] = @$video_map[$data['res_id']];
    } elseif ($type == 'videolink') {
        $data['res_id'] = @$videolink_map[$data['res_id']];
    } elseif ($type == 'videolinkcategory') {
        $data['res_id'] = @$video_category_map[$data['res_id']];
    } elseif ($type == 'lp') {
        $data['res_id'] = @$lp_learnPath_map[$data['res_id']];
    } elseif ($type == 'wiki') {
        $data['res_id'] = @$wiki_map[$data['res_id']];
    } elseif ($type == 'work') {
        if (isset($assignments_map[$data['res_id']])) {
            $data['res_id'] = @$assignments_map[$data['res_id']];
        } else {
            $data['res_id'] = $assignments_map[0];
        }
    } elseif ($type == 'exercise') {
        if (isset($exercise_map[$data['res_id']])) {
            $data['res_id'] = @$exercise_map[$data['res_id']];
        } else {
            $data['res_id'] = $exercise_map[0];
        }
    } elseif ($type == 'forum') {
        $data['res_id'] = @$forum_map[$data['res_id']];
    } elseif ($type == 'topic') {
        $data['res_id'] = @$forum_topic_map[$data['res_id']];
    } elseif ($type == 'poll') {
        $data['res_id'] = @$poll_map[$data['res_id']];
    }
    return true;
}


function rubric_scaling_map_function(&$data, $maps) {
    list($rubric_map, $grading_scale_map) = $maps;
    if ($data['grading_type'] == ASSIGNMENT_SCALING_GRADE) {
        $data['grading_scale_id'] = $grading_scale_map[$data['grading_scale_id']];
    } elseif ($data['grading_type'] == ASSIGNMENT_RUBRIC_GRADE) {
        $data['grading_scale_id'] = $rubric_map[$data['grading_scale_id']];
    }
    return true;
}

function comments_not_null(&$data) {
    if (is_null($data['comments'])) {
        $data['comments'] = '';
    }
    return true;
}

function ratings_map_function(&$data, $maps) {
    list($blog_post_map, $forum_post_map, $link_map, $wall_map, $course_id) = $maps;
    $rtype = $data['rtype'];
    if ($rtype == 'blogpost') {
        $data['rid'] = @$blog_post_map[$data['rid']];
    } elseif ($rtype == 'course') {
        $data['rid'] = $course_id;
    } elseif ($rtype == 'forum_post') {
        $data['rid'] = @$forum_post_map[$data['rid']];
    } elseif ($rtype == 'link') {
        $data['rid'] = @$link_map[$data['rid']];
    } elseif ($rtype == 'wallpost') {
        $data['rid'] = @$wall_map[$data['rid']];
    }
    return $data['rid'];
}

function comments_map_function(&$data, $maps) {
    list($blog_post_map, $wall_map, $course_id) = $maps;
    $rtype = $data['rtype'];
    if ($rtype == 'blogpost') {
        $data['rid'] = $blog_post_map[$data['rid']];
    } elseif ($rtype == 'course') {
        $data['rid'] = $course_id;
    } elseif ($rtype == 'wallpost') {
        $data['rid'] = $wall_map[$data['rid']];
    }
    return $data['rid'];
}

function abuse_report_map_function(&$data, $maps) {
    list($forum_post_map, $comment_map, $link_map, $wall_map) = $maps;
    $rtype = $data['rtype'];
    if ($rtype == 'comment') {
        $data['rid'] = $comment_map[$data['rid']];
    } elseif ($rtype == 'forum_post') {
        $data['rid'] = $forum_post_map[$data['rid']];
    } elseif ($rtype == 'link') {
        $data['rid'] = $link_map[$data['rid']];
    } elseif ($rtype == 'wallpost') {
        $data['rid'] = $wall_map[$data['rid']];
    }
    return true;
}

function wall_map_function(&$data, $maps) {
    list($document_map, $video_map, $videolink_map) = $maps;
    $type = $data['type'];
    if ($type == 'document') {
        if (isset($document_map[$data['res_id']])) {
            $data['res_id'] = @$document_map[$data['res_id']];
        }
    } elseif ($type == 'video') {
        $data['res_id'] = @$video_map[$data['res_id']];
    } elseif ($type == 'videolink') {
        $data['res_id'] = @$videolink_map[$data['res_id']];
    }
    return true;
}

function attendance_gradebook_activities_map_function(&$data, $maps) {
    list($assignments_map, $exercise_map) = $maps;
    $type = intval($data['module_auto_type']);

    // PROSOXH! edw kanoyme thn exhs symvash/paradoxh:
    // Yparxei pi8anothta ta attendance/gradebook activities na kanoun
    // reference se mh-yparkto record, logw ths apoysias pragmatikou FK klp.
    // H restore gia na mporesei na leitoyrghsei me th logikh: "prospa8w na anakthsw
    // thn arxikh bash xwris data loss, akoma kai asyndeta/spasmena data", telikws 8a
    // kanei assign se ayta to id mhden (0).

    if ($type === 1) {
        if (isset($assignments_map[$data['module_auto_id']])) {
            $data['module_auto_id'] = $assignments_map[$data['module_auto_id']];
        } else {
            $data['module_auto_id'] = $assignments_map[0];
        }
    } else if ($type === 2) {
        if (isset($exercise_map[$data['module_auto_id']])) {
            $data['module_auto_id'] = $exercise_map[$data['module_auto_id']];
        } else {
            $data['module_auto_id'] = $exercise_map[0];
        }
    }
    if (is_null($data['module_auto_id'])) {
        $data['module_auto_id'] = 0;
    }
    return true;
}

function certificate_criterion_map_function(&$data, $maps) {
    list($document_map, $video_map, $videolink_map,
         $blog_map, $forum_map, $forum_topic_map,
         $lp_learnPath_map, $ebook_map, $poll_map, $wiki_map,
         $assignments_map, $exercise_map) = $maps;

    $type = $data['activity_type'];
    switch ($type) {
        case 'document': $data['resource'] = $document_map[$data['resource']];
                    $data['module'] = MODULE_ID_DOCS;
                    break;
        case 'video': $data['resource'] = $video_map[$data['resource']];
                    $data['module'] = MODULE_ID_VIDEO;
                    break;
        case 'videolink': $data['resource'] = $videolink_map[$data['resource']];
                    $data['module'] = MODULE_ID_VIDEO;
                    break;
        case 'blog': $data['module'] = MODULE_ID_BLOG;
                    break;
        case 'blogpost': $data['resource'] = $blog_map[$data['resource']];
                    $data['module'] = MODULE_ID_COMMENTS;
                    break;
        case 'forum': $data['resource'] = $forum_map[$data['resource']];
                    $data['module'] = MODULE_ID_FORUM;
                    break;
        case 'forumtopic': $data['resource'] = $forum_topic_map[$data['resource']];
                    $data['module'] = MODULE_ID_FORUM;
                    break;
        case 'learning path':
        case 'learning path duration':
                    $data['resource'] = $lp_learnPath_map[$data['resource']];
                    $data['module'] = MODULE_ID_LP;
                    break;
        case 'ebook': $data['resource'] = $ebook_map[$data['resource']];
                    $data['module'] = MODULE_ID_EBOOK;
                    break;
        case 'questionnaire': $data['resource'] = $poll_map[$data['resource']];
                    $data['module'] = MODULE_ID_QUESTIONNAIRE;
                    break;
        case 'wiki': $data['resource'] = $wiki_map[$data['resource']];
                    $data['module'] = MODULE_ID_WIKI;
                    break;
        case 'assignment-submit':
        case 'assignment': $data['resource'] = $assignments_map[$data['resource']];
                    $data['module'] = MODULE_ID_ASSIGN;
                    break;
        case 'exercise': $data['resource'] = $exercise_map[$data['resource']];
                    $data['module'] = MODULE_ID_EXERCISE;
                    break;
        case 'courseparticipation': $data['resource'] = null;
                    $data['module'] = MODULE_ID_USAGE;
                    break;
        default:
                break;
    }
    return true;
}

function badge_criterion_map_function(&$data, $maps) {
    list($document_map, $video_map, $videolink_map,
         $blog_map, $forum_map, $forum_topic_map,
         $lp_learnPath_map, $ebook_map, $poll_map, $wiki_map,
         $assignments_map, $exercise_map) = $maps;

    $type = $data['activity_type'];
    switch ($type) {
        case 'document': $data['resource'] = $document_map[$data['resource']];
                    $data['module'] = MODULE_ID_DOCS;
                    break;
        case 'video': $data['resource'] = $video_map[$data['resource']];
                    $data['module'] = MODULE_ID_VIDEO;
                    break;
        case 'videolink': $data['resource'] = $videolink_map[$data['resource']];
                    $data['module'] = MODULE_ID_VIDEO;
                    break;
        case 'blog': $data['module'] = MODULE_ID_BLOG;
                    break;
        case 'blogpost': $data['resource'] = $blog_map[$data['resource']];
                    $data['module'] = MODULE_ID_COMMENTS;
                    break;
        case 'forum': $data['resource'] = $forum_map[$data['resource']];
                    $data['module'] = MODULE_ID_FORUM;
                    break;
        case 'forumtopic': $data['resource'] = $forum_topic_map[$data['resource']];
                    $data['module'] = MODULE_ID_FORUM;
                    break;
        case 'learning path':
        case 'learning path duration':
                    $data['resource'] = $lp_learnPath_map[$data['resource']];
                    $data['module'] = MODULE_ID_LP;
                    break;
        case 'ebook': $data['resource'] = $ebook_map[$data['resource']];
                    $data['module'] = MODULE_ID_EBOOK;
                    break;
        case 'questionnaire': $data['resource'] = $poll_map[$data['resource']];
                    $data['module'] = MODULE_ID_QUESTIONNAIRE;
                    break;
        case 'wiki': $data['resource'] = $wiki_map[$data['resource']];
                    $data['module'] = MODULE_ID_WIKI;
                    break;
        case 'assignment-submit':
        case 'assignment': $data['resource'] = $assignments_map[$data['resource']];
                    $data['module'] = MODULE_ID_ASSIGN;
                    break;
        case 'exercise': $data['resource'] = $exercise_map[$data['resource']];
                    $data['module'] = MODULE_ID_EXERCISE;
                    break;
        case 'courseparticipation': $data['resource'] = null;
                    $data['module'] = MODULE_ID_USAGE;
                    break;
        default:
                break;
    }
    return true;
}


function notes_map_function(&$data, $maps) {
    // opoy yparxei if isset, isxyei h:
    // idia symbash/paradoxh me to attendance_gradebook_activities_map_function()
    // des to ekei comment gia ta spasmena FKs
    list($course_id, $agenda_map, $document_map, $link_map, $video_map,
            $videolink_map, $assignments_map, $exercise_map, $ebook_map,
            $lp_learnPath_map) = $maps;
    $type = $data['reference_obj_type'];
    switch ($type) {
        case 'course':
            $data['reference_obj_id'] = $course_id;
            break;
        case 'course_event':
            if (isset($agenda_map[$data['reference_obj_id']])) {
                $data['reference_obj_id'] = $agenda_map[$data['reference_obj_id']];
            } else {
                $data['reference_obj_id'] = $agenda_map[0];
            }
            break;
        case 'course_document':
            $data['reference_obj_id'] = $document_map[$data['reference_obj_id']];
            break;
        case 'course_link':
            $data['reference_obj_id'] = $link_map[$data['reference_obj_id']];
            break;
        case 'course_video':
            $data['reference_obj_id'] = $video_map[$data['reference_obj_id']];
            break;
        case 'course_videolink':
            $data['reference_obj_id'] = $videolink_map[$data['reference_obj_id']];
            break;
        case 'course_assignment':
            if (isset($assignments_map[$data['reference_obj_id']])) {
                $data['reference_obj_id'] = $assignments_map[$data['reference_obj_id']];
            } else {
                $data['reference_obj_id'] = $assignments_map[0];
            }
            break;
        case 'course_exercise':
            if (isset($exercise_map[$data['reference_obj_id']])) {
                $data['reference_obj_id'] = $exercise_map[$data['reference_obj_id']];
            } else {
                $data['reference_obj_id'] = $exercise_map[0];
            }
            break;
        case 'course_ebook':
            $data['reference_obj_id'] = $ebook_map[$data['reference_obj_id']];
            break;
        case 'course_learningpath':
            $data['reference_obj_id'] = $lp_learnPath_map[$data['reference_obj_id']];
            break;
        case 'course_learningpath':
            $data['reference_obj_id'] = $lp_learnPath_map[$data['reference_obj_id']];
            break;
        default:
            break;
    }
    return true;
}

function exercise_with_questions_map_function(&$data, $q_position) {
    if (!isset($data['q_position'])) {
        $data['q_position'] = $q_position[$data['id']];
    }
    return true;
}

function get_tabledata_from_parsed($table, $backupData, $restoreHelper, $set = array()) {
    $backup = array();
    foreach ($backupData['query'] as $tableData) {
        if (is_array($tableData) && isset($tableData['table'])
                && $tableData['table'] === $restoreHelper->getFile($table)
                && is_array($tableData['fields']) && is_array($tableData['values'])) {
            $row = array();
            foreach ($tableData['values'] as $tableValue) {
                for ($i = 0; $i < count($tableData['fields']); $i++) {
                    if ($restoreHelper->getField($table, $tableData['fields'][$i]) !== RestoreHelper::FIELD_DROP) {
                        $row[$tableData['fields'][$i]] = $tableValue[$i];
                    }
                }
                foreach ($set as $setKey => $setValue) {
                    if (!isset($row[$setKey])) {
                        $row[$setKey] = $setValue;
                    }
                }
                $backup[] = $row;
            }
        }
    }
    return $backup;
}

function getTranslation($name, $lang) {
    global $webDir, $siteName, $InstitutionUrl, $Institution;
    static $trans;

    if (!isset($trans[$name])) {
        include "$webDir/lang/$lang/common.inc.php";
        $extra_messages = "config/$lang.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "$webDir/lang/$lang/messages.inc.php";
        if (file_exists('config/config.php')) {
            if(get_config('show_always_collaboration') and get_config('show_collaboration')){
              include "$webDir/lang/$lang/messages_collaboration.inc.php";
            }
        }
        if ($extra_messages) {
            include $extra_messages;
        }
        $trans[$name] = $$name;
    }
    return $trans[$name];
}
