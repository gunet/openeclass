<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

// form select about visibility
function visibility_select($current) {
    global $langOpenCourse, $langRegCourse, $langClosedCourse, $langInactiveCourse;

    $ret = "<select name='course_vis'>\n";
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
function unpack_zip_show_files($zipfile) {
    global $webDir, $uid, $langEndFileUnzip, $langLesFound, $langRestore, $langLesFiles;

    $retString = '';
    $zip = new pclZip($zipfile);
    validateUploadedZipFile($zip->listContent(), 3);

    $destdir = $webDir . '/courses/tmpUnzipping/' . $uid;
    mkpath($destdir);
    chdir($destdir);
    $state = $zip->extract();
    $retString .= "<br />$langEndFileUnzip<br /><br />$langLesFound
                       <form action='$_SERVER[SCRIPT_NAME]' method='post'>
                         <ol>";
    $checked = ' checked';
    foreach (find_backup_folders($destdir) as $folder) {
        $path = q($folder['path'] . '/' . $folder['dir']);
        $file = q($folder['dir']);
        $course = q(preg_replace('|^.*/|', '', $folder['path']));
        $retString .= "<li>$langLesFiles <input type='radio' name='restoreThis' value='$path'$checked>
                        <b>$course</b> ($file)</li>\n";
        $checked = '';
    }
    $retString .= "</ol><br /><input class='btn btn-primary' type='submit' name='do_restore' value='$langRestore' /></form>";
    chdir($webDir);
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

    foreach ($backup as $data) {
        if ($return_mapping) {
            $old_id = $data[$id_var];
            unset($data[$id_var]);
        }
        if (isset($options['delete'])) {
            foreach ($options['delete'] as $field) {
                unset($data[$field]);
            }
        }
        if (!isset($sql_intro)) {
            $sql_intro = "INSERT INTO `$table` " . field_names($data, $table, $restoreHelper) . ' VALUES ';
        }
        if (isset($options['map'])) {
            foreach ($options['map'] as $field => &$map) {
                if (isset ($data[$restoreHelper->getField($table, $field)]) && isset($map[$data[$restoreHelper->getField($table, $field)]])) { // map needs reverse resolution
                    $data[$restoreHelper->getField($table, $field)] = $map[$data[$restoreHelper->getField($table, $field)]];
                } else {
                    continue 2;
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
        if ($do_insert) {
            $field_args = field_args($data, $table, $set, $url_prefix_map, $restoreHelper);
            $lastid = Database::get()->query($sql_intro . field_placeholders($data, $table, $set, $restoreHelper), $field_args)->lastInsertID;
        }
        if ($return_mapping) {
            $mapping[$old_id] = $lastid;
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
            $values[] = '?s';
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
        if (isset($url_prefix_map)) {
            $values[] = strtr($restoreHelper->getValue($table, $name, $value), $url_prefix_map);
        } else {
            $values[] = $restoreHelper->getValue($table, $name, $value);
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
function course_details_form($code, $title, $prof, $lang, $type, $vis, $desc, $faculty) {
    global $langInfo1, $langInfo2, $langCourseCode, $langLanguage, $langTitle,
    $langCourseDescription, $langFaculty, $langCourseVis,
    $langTeacher, $langUsersWillAdd,
    $langOk, $langAll, $langsTeachers, $langMultiRegType,
    $langNone, $langOldValue, $treeObj;

    list($tree_js, $tree_html) = $treeObj->buildCourseNodePicker();
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
            $old_faculty_names[] = q($entry['name']);
        }
        $old_faculty = implode('<br>', $old_faculty_names);
    } else {
        $old_faculty = q($faculty . $type_label);
    }
    return "<p>$langInfo1</p>
                <p>$langInfo2</p>
                <form action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();' >
                <table width='99%' class='tbl'><tbody>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langCourseCode:</th>
                       <td><input type='text' name='course_code' value='" . q($code) . "' /></td></tr>
                   <tr><th>$langLanguage:</th>
                       <td>" . lang_select_options('course_lang') . "</td>
                   <tr><th>$langTitle:</th>
                       <td><input type='text' name='course_title' value='" . q($title) . "' size='50' /></td></tr>
                   <tr><th>$langCourseDescription:</th>
                       <td>" . rich_text_editor('desc', 10, 40, purify($desc)) . "</td></tr>
                       <tr><th>$langFaculty:</th>
                       <td>" . $tree_html . "<br>$langOldValue: <i> " . hierarchy::unserializeLangField($old_faculty) . "</i></td></tr>
                   <tr><th>$langCourseVis:</th><td>" . visibility_select($vis) . "</td></tr>
                   <tr><th>$langTeacher:</th>
                       <td><input type='text' name='course_prof' value='" . q($prof) . "' size='50' /></td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langUsersWillAdd:</th>
                       <td><input type='radio' name='add_users' value='all' id='add_users_all'>
                           <label for='add_users_all'>$langAll</label><br>
                           <input type='radio' name='add_users' value='prof' id='add_users_prof' checked>
                           <label for='add_users_prof'>$langsTeachers</label><br>
                           <input type='radio' name='add_users' value='none' id='add_users_none'>
                           <label for='add_users_none'>$langNone</label></td></tr>
                   <tr><th><label for='create_users'>$langMultiRegType:</label></th>
                       <td><input type='checkbox' name='create_users' value='1' id='create_users'></td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td colspan='2'>
                      <input class='btn btn-primary' type='submit' name='create_restored_course' value='$langOk' />
                      <input type='hidden' name='restoreThis' value='" . q($_POST['restoreThis']) . "' /></td></tr>
                </tbody></table>
                </form>";
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

    foreach ($users as $data) {
        if ($add_only_profs and !$is_prof[$data[$restoreHelper->getField('user', 'id')]]) {
            continue;
        }
        $u = Database::get()->querySingle("SELECT * FROM user WHERE BINARY username = ?s", $data['username']);
        if ($u) {
            $userid_map[$data[$restoreHelper->getField('user', 'id')]] = $u->id;
            $tool_content .= "<p>" .
                    sprintf($langRestoreUserExists, 
                            '<b>' . q($data['username']) . '</b>', 
                            '<i>' . q($u->givenname . " " . $u->surname) . '</i>', 
                            '<i>' . q($data[$restoreHelper->getField('user', 'givenname')] . " " . $data[$restoreHelper->getField('user', 'surname')]) . '</i>') .
                    "</p>\n";
        } elseif (isset($_POST['create_users'])) {
            $user_id = Database::get()->query("INSERT INTO user SET surname = ?s, "
                    . "givenname = ?s, username = ?s, password = ?s, email = ?s, status = ?d, phone = ?s, "
                    . "registered_at = ?t, expires_at = ?t", 
                    (isset($data[$restoreHelper->getField('user', 'surname')])) ? $data[$restoreHelper->getField('user', 'surname')] : '', 
                    (isset($data[$restoreHelper->getField('user', 'givenname')])) ? $data[$restoreHelper->getField('user', 'givenname')] : '', 
                    $data['username'], 
                    (isset($data['password'])) ? $data['password'] : 'empty', 
                    (isset($data['email'])) ? $data['email'] : '', 
                    intval($data[$restoreHelper->getField('course_user', 'status')]), 
                    (isset($data['phone'])) ? $data['phone'] : '', 
                    date('Y-m-d H:i:s', time()), 
                    date('Y-m-d H:i:s', time() + get_config('account_duration')))->lastInsertID;
            $userid_map[$data[$restoreHelper->getField('user', 'id')]] = $user_id;
            require_once 'include/lib/user.class.php';
            $user = new User();
            $user->refresh($user_id, $departments);
            $tool_content .= "<p>" .
                    sprintf($langRestoreUserNew, '<b>' . q($data['username']) . '</b>', '<i>' . q($data[$restoreHelper->getField('user', 'givenname')] . " " . $data[$restoreHelper->getField('user', 'surname')]) . '</i>') .
                    "</p>\n";
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
            $editor[$old_id] = $cudata['editor'];
            $reviewer[$old_id] = (isset($cudata['reviewer'])) ? $cudata['reviewer'] : 0;
            $reg_date[$old_id] = $cudata['reg_date'];
            $receive_mail[$old_id] = $cudata['receive_mail'];
        }
    }

    foreach ($userid_map as $old_id => $new_id) {
        Database::get()->query("INSERT INTO course_user SET course_id = ?d, user_id = ?d, status = ?d, tutor = ?d, editor = ?d, "
                . "reviewer = ?d, reg_date = ?t, receive_mail = ?d",
                intval($course_id),
                intval($new_id),
                intval($status[$old_id]), 
                intval($tutor[$old_id]), 
                intval($editor[$old_id]), 
                intval($reviewer[$old_id]), 
                $reg_date[$old_id], 
                intval($receive_mail[$old_id]));
        $tool_content .= "<p>$langPrevId=$old_id, $langNewId=$new_id</p>\n";
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
                        // Skip tables not used any longer
                        if ($table != 'stat_accueil' and $table != 'users') {
                            $fields = parse_fields($matches[2]);
                            $values = parse_values($matches[3]);
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
    return true;
}

function unit_map_function(&$data, $maps) {
    list($document_map, $link_category_map, $link_map, $ebook_map, $section_map, $subsection_map, $video_map, $videolink_map, $lp_learnPath_map, $wiki_map, $assignments_map, $exercise_map) = $maps;
    $type = $data['type'];
    if ($type == 'doc') {
        $data['res_id'] = $document_map[$data['res_id']];
    } elseif ($type == 'linkcategory') {
        $data['res_id'] = $link_category_map[$data['res_id']];
    } elseif ($type == 'link') {
        $data['res_id'] = $link_map[$data['res_id']];
    } elseif ($type == 'ebook') {
        $data['res_id'] = $ebook_map[$data['res_id']];
    } elseif ($type == 'section') {
        $data['res_id'] = $section_map[$data['res_id']];
    } elseif ($type == 'subsection') {
        $data['res_id'] = $subsection_map[$data['res_id']];
    } elseif ($type == 'description') {
        $data['res_id'] = intval($data['res_id']);
    } elseif ($type == 'video') {
        $data['res_id'] = $video_map[$data['res_id']];
    } elseif ($type == 'videolink') {
        $data['res_id'] = $videolink_map[$data['res_id']];
    } elseif ($type == 'lp') {
        $data['res_id'] = $lp_learnPath_map[$data['res_id']];
    } elseif ($type == 'wiki') {
        $data['res_id'] = $wiki_map[$data['res_id']];
    } elseif ($type == 'work') {
        $data['res_id'] = $assignments_map[$data['res_id']];
    } elseif ($type == 'exercise') {
        $data['res_id'] = $exercise_map[$data['res_id']];
    }
    return true;
}

function ratings_map_function(&$data, $maps) {
    list($blog_post_map, $course_id) = $maps;
    $rtype = $data['rtype'];
    if ($rtype == 'blogpost') {
        $data['rid'] = $blog_post_map[$data['rid']];
    } elseif ($rtype == 'course') {
        $data['rid'] = $course_id;
    }
    return true;
}

function comments_map_function(&$data, $maps) {
    list($blog_post_map, $course_id) = $maps;
    $rtype = $data['rtype'];
    if ($rtype == 'blogpost') {
        $data['rid'] = $blog_post_map[$data['rid']];
    } elseif ($rtype == 'course') {
        $data['rid'] = $course_id;
    }
    return true;
}

function attendance_gradebook_activities_map_function(&$data, $maps) {
    list($assignments_map, $exercise_map) = $maps;
    $type = intval($data['module_auto_type']);
    if ($type === 1) {
        $data['module_auto_id'] = $assignments_map[$data['module_auto_id']];
    } else if ($type === 2) {
        $data['module_auto_id'] = $exercise_map[$data['module_auto_id']];
    }
    return true;
}

function notes_map_function(&$data, $maps) {
    list($course_id, $agenda_map, $document_map, $link_map, $video_map, 
            $videolink_map, $assignments_map, $exercise_map, $ebook_map, 
            $lp_learnPath_map) = $maps;
    $type = $data['reference_obj_type'];
    switch ($type) {
        case 'course':
            $data['reference_obj_id'] = $course_id;
            break;
        case 'course_event':
            $data['reference_obj_id'] = $agenda_map[$data['reference_obj_id']];
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
            $data['reference_obj_id'] = $assignments_map[$data['reference_obj_id']];
            break;
        case 'course_exercise':
            $data['reference_obj_id'] = $exercise_map[$data['reference_obj_id']];
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
