<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2020  Greek Universities Network - GUnet
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
 * ======================================================================== */

/* ===========================================================================
  fileManageLib.inc.php
  @last update: 30-06-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.3 licensed under GPL
  and Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: fileManageLib.inc.php Revision: 1.3
  extra porting from: fileManage.lib.php Revision 1.49.2.3

  Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
  Hugues Peeters    <peeters@ipm.ucl.ac.be>
  Christophe Gesche <gesche@ipm.ucl.ac.be>
  ==============================================================================
 */

/*
 * Update the file or directory path in the document db document table
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - action (string) - action type require : 'delete' or 'update'
 * @param  - oldPath (string) - old path info stored to change
 * @param  - newPath (string) - new path info to substitute
 * @desc Update the file or directory path in the document db document table
 *
 */

function update_db_info($dbTable, $action, $oldPath, $filename, $newPath = "") {
    global $course_id, $group_sql, $subsystem;

    if ($action == "delete") {
        Database::get()->query("DELETE FROM `$dbTable`
                                 WHERE $group_sql AND
                                       path LIKE ?s", ($oldPath . '%'));
        if ($subsystem == COMMON) {
            // For common documents, delete all references
            Database::get()->query("DELETE FROM `$dbTable`
                                         WHERE extra_path LIKE ?s", ('common:' . $oldPath . '%'));
        }
        Log::record($course_id, MODULE_ID_DOCS, LOG_DELETE, array('path' => $oldPath,
                                                                  'filename' => $filename));
    } elseif ($action == "update") {
        Database::get()->query("UPDATE `$dbTable`
                                 SET path = CONCAT('$newPath', SUBSTRING(path, LENGTH('$oldPath')+1))
                                 WHERE $group_sql AND path LIKE ?s", ($oldPath . '%'));
        if ($subsystem == COMMON) {
            // For common documents, update all references
            Database::get()->query("UPDATE `$dbTable`
                                         SET extra_path = CONCAT('common:$newPath',
                                                                 SUBSTRING(extra_path, LENGTH('common:$oldPath')+1))
                                         WHERE extra_path LIKE ?s", ('common:' . $oldPath . '%'));
        }
        $newencodepath = Database::get()->querySingle("SELECT SUBSTRING(path, 1, LENGTH(path) - LENGTH('$oldPath')) as value
                                FROM $dbTable WHERE path=?s", $newPath)->value;
        $newpath = Database::get()->querySingle("SELECT filename FROM $dbTable
                                        WHERE path = ?s", $newencodepath);
        if ($newpath) {
            $newpath = $newpath->filename;
        }
        Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array('oldencpath' => $oldPath,
                                                                  'newencpath' => $newPath,
                                                                  'newpath' => $newpath,
                                                                  'filename' => $filename));
    }
}

/*
 * @brief Delete a file or a directory
 * @author - Hugues Peeters
 * @param  - $file (String) - the path of file or directory to delete
 * @return - boolean - true if the delete succeed, false otherwise.
 */

function my_delete($file) {
    if (!file_exists($file)) {
        return true; // no need to delete nonexistent file
    }
    if (is_file($file) or is_link($file)) {
        return @unlink($file);
    } elseif (is_dir($file)) {
        return removeDir($file);
    }
}


/*
 * @brief Move a file or a directory to another area
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $source (String) - the path of file or directory to move
 * @param  - $target (String) - the path of the new area
 * @return - boolean - true if the move succeed false otherwise.
 * @see    - move() uses check_name_exist() and copyDirTo() functions
 */

function move($source, $target) {
    if (file_exists($source)) {
        $fileName = my_basename($source);
        if (file_exists($target . "/" . $fileName)) {
            return false;
        } else {
            if (is_file($source)) {  /*** File case ***/
                copy($source, $target . "/" . $fileName);
                unlink($source);
                return true;
            }
            elseif (is_dir($source)) { /*** Directory case ** */
                // check to not copy the directory inside itself
                if (strpos($target, $source) === 0) {
                    return false;
                } else {
                    copyDirTo($source, $target);
                    removeDir($source);
                    return true;
                }
            }
        }
    } else {
        return false;
    }
}

/*
 * Move a directory and its content to another area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new directory
 */

function move_dir($src, $dest) {
    if (file_exists($dest)) {
        if (!is_dir($dest)) {
            die("<br>Error! a file named $dest already exists\n");
        }
    } else {
        make_dir($dest);
    }

    $handle = opendir($src);
    if (!$handle) {
        die("Unable to read $src!");
    }
    while ($element = readdir($handle)) {
        $file = "$src/$element";
        if ($element == "." || $element == "..") {
            continue; // skip the current and parent directories
        } elseif (is_file($file)) {
            if (is_file("$dest/$element")) {
                unlink("$dest/$element");
            }
            copy($file, "$dest/$element") or
                    die("Error copying $src/$element to $dest");
            unlink($file);
        } elseif (is_dir($file)) {
            move_dir($file, "$dest/$element");
        }
    }
    closedir($handle);
    removeDir($src);
}

/*
 * Copy a directory and its content to another area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @return - no return !!
 */

function copyDirTo($origDirPath, $destination) {
    // extract directory name - create it at destination - update destination trail
    $dirName = my_basename($origDirPath);
    make_dir($destination . '/' . $dirName);
    $destinationTrail = $destination . "/" . $dirName;

    $cwd = getcwd();
    chdir($origDirPath);
    $handle = opendir($origDirPath);

    while ($element = readdir($handle)) {
        if ($element == "." || $element == "..") {
            continue; // skip the current and parent directories
        } elseif (is_file($element)) {
            copy($element, $destinationTrail . "/" . $element);
        } elseif (is_dir($element)) {
            $dirToCopy[] = $origDirPath . "/" . $element;
        }
    }

    closedir($handle);

    if (isset($dirToCopy) and sizeof($dirToCopy) > 0) {
        foreach ($dirToCopy as $thisDir) {
            copyDirTo($thisDir, $destinationTrail); // recursivity
        }
    }

    chdir($cwd);
}

// Return a list of all directories
function directory_list() {
    global $group_sql;

    $sortedDirs = $dirArray = array();

    $r = Database::get()->queryArray("SELECT filename, path FROM document WHERE $group_sql AND format = '.dir'");
    foreach ($r as $row) {
        $dirArray[] = array($row->path, $row->filename,
            public_file_path($row->path, $row->filename));
    }
    setlocale(LC_COLLATE, $GLOBALS['langLocale']);
    usort($dirArray, function ($a, $b) {
        return strcoll($a[2], $b[2]);
    });
    foreach ($dirArray as $dir) {
        $sortedDirs[$dir[0]] = $dir[1];
    }
    return $sortedDirs;
}

/*
 * Returns HTML form select element listing all directories in current course documents
 * excluding the one with path $entryToExclude and all under $directoryToExclude
 */
function directory_selection($source_value, $command, $entryToExclude, $directoryToExclude) {
    $dirList = directory_list();
    $items = array();
    foreach ($dirList as $path => $filename) {
        $disabled = false;
        $depth = substr_count($path, '/');
        if ($directoryToExclude !== '/' and $directoryToExclude !== '') {
            $disabled = (strpos($path, $directoryToExclude) === 0);
        }
        if (!$disabled and $entryToExclude !== '/' and $entryToExclude !== '') {
            $disabled = ($path === $entryToExclude);
        }
        $items[] = (object) compact('disabled', 'path', 'filename', 'depth');
    }
    return $items;
}


/**
 * @brief Create a zip file with the contents of documents path $downloadDir
 * @global type $basedir
 * @global type $group_sql
 * @param type $zip_filename
 * @param type $downloadDir
 * @param type $include_invisible
 */
function zip_documents_directory($zip_filename, $downloadDir, $include_invisible = false) {
    global $basedir, $group_sql, $map_filenames, $path_visibility;

    create_map_to_real_filename($downloadDir, $include_invisible);
    $topdir = ($downloadDir == '/') ? $basedir : ($basedir . $downloadDir);
    $zipFile = new ZipArchive();
    $zipFile->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    // Create recursive directory iterator
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($topdir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        // Get real and filename to be added for current file
        $filePath = fix_directory_separator($file->getRealPath());
        $relativePath = substr($filePath, strlen($basedir));

        // Skip directories (they will be added automatically)
        if (!$file->isDir()) {
            if (!isset($path_visibility[$relativePath]) or !$path_visibility[$relativePath] or !isset($map_filenames[$relativePath])) {
                continue; // skip invisible files for student
            } else {
                // Add current file to archive
                $zipFile->addFile($filePath, substr($map_filenames[$relativePath], 1));
            }
        } else { // empty directory
            if (!isset($path_visibility[$relativePath]) or !$path_visibility[$relativePath] or !isset($map_filenames[$relativePath])) {
                continue; // skip invisible files for student
            } else {
                $zipFile->addEmptyDir(substr($map_filenames[$relativePath], 1));
            }
        }
    }

    // support for common documents
    if (isset($GLOBALS['common_docs'])) {
        foreach ($GLOBALS['common_docs'] as $path => $real_path) {
            $common_filename = $GLOBALS['map_filenames'][$path];
            $zipFile->addFile($real_path, substr($common_filename, 1));
        }
    }
    if (!$zipFile->close()) {
        die("Error while creating ZIP file!");
    }
}


/**
 * @brief Creates mapping between encoded filenames and real filenames
 * @global type $group_sql
 * @param type $downloadDir
 * @param type $include_invisible
 */
function create_map_to_real_filename($downloadDir, $include_invisible) {

    global $group_sql;

    $prefix = strlen(preg_replace('|[^/]*$|', '', $downloadDir)) - 1;
    $encoded_filenames = $decoded_filenames = $filename = array();

    $hidden_dirs = array();
    $sql = Database::get()->queryArray("SELECT path, filename, visible, format, extra_path, public FROM document
                                WHERE $group_sql AND
                                      path LIKE '$downloadDir%'");
    foreach ($sql as $files) {
        if ($cpath = common_doc_path($files->extra_path, true)) {
            if ($GLOBALS['common_doc_visible'] and ($include_invisible or $files->visible == 1)) {
                $GLOBALS['common_docs'][$files->path] = $cpath;
            }
        }
        $GLOBALS['path_visibility'][$files->path] = ($include_invisible or resource_access($files->visible, $files->public));
        array_push($encoded_filenames, $files->path);
        array_push($filename, str_replace(['/', '\\'], '_', $files->filename));
        if (!$include_invisible and $files->format == '.dir' and !resource_access($files->visible, $files->public)) {
            $parentdir = preg_replace('|/[^/]+$|', '', $files->path);
            // Don't need to check lower-level hidden dir if parent is there
            if (array_search($parentdir, $hidden_dirs) === false) {
                array_push($hidden_dirs, $files->path);
            }
        }
    }
    if (!$include_invisible) {
        if (count($hidden_dirs)) {
            $hidden_regexp = '#^(' . implode('|', $hidden_dirs) . ')#';
        } else {
            $hidden_regexp = false;
        }
    }
    $decoded_filenames = $encoded_filenames;
    foreach ($encoded_filenames as $position => $name) {
        if (!$include_invisible and $hidden_regexp and
                preg_match($hidden_regexp, $name)) {
            $GLOBALS['path_visibility'][$name] = false;
        }
        $last_name_component = substr(strrchr($name, "/"), 1);
        foreach ($decoded_filenames as &$newname) {
            $newname = str_replace($last_name_component, $filename[$position], $newname);
        }
        unset($newname);
    }
    foreach ($decoded_filenames as &$s) {
        $s = substr($s, $prefix);
    }

    // create global array with mappings
    $GLOBALS['map_filenames'] = array_combine($encoded_filenames, $decoded_filenames);
}

/**
 * Check if a path (from document table extra_path field) points to a common
 * document and if so return the full path on disk, else return false.
 * Sets global $common_doc_visible = false if file pointed to is invisible
 *
 * @global string $webDir
 * @global bool $common_doc_visible
 * @param string $extra_path
 * @param bool $full Return full on-disk path
 * @return string|boolean
 */
function common_doc_path($extra_path, $full = false) {
    global $webDir, $common_doc_visible;
    if (!is_null($extra_path) and preg_match('#^common:(/.*)$#', $extra_path, $matches)) {
        $cpath = $matches[1];
        $q = Database::get()->querySingle("SELECT visible FROM document
                                      WHERE path = ?s AND
                                            subsystem = " . COMMON, $cpath);
        if ($q and $q->visible) {
            $common_doc_visible = true;
        } else {
            $common_doc_visible = false;
        }
        return ($full ? $webDir : '') . '/courses/commondocs' . $cpath;
    } else {
        $common_doc_visible = true;
        return false;
    }
}
//------------------------------------------------------------------------------
/* --------------- backported functions from Claroline 1.7.x --------------- */

/*
 * Delete a file or a directory (and its whole content)
 *
 * @param  - $filePath (String) - the path of file or directory to delete
 * @return - boolean - true if the delete succeed
 *           boolean - false otherwise.
 */

function claro_delete_file($filePath) {
    if (is_file($filePath)) {
        return unlink($filePath);
    } elseif (is_dir($filePath)) {
        $dirHandle = opendir($filePath);

        if (!$dirHandle)
            return false;

        $removableFileList = array();
        while ($file = readdir($dirHandle)) {
            if ($file == '.' || $file == '..')
                continue;

            $removableFileList[] = $filePath . '/' . $file;
        }

        closedir($dirHandle); // impossible to test, closedir return void ...

        if (sizeof($removableFileList) > 0) {
            foreach ($removableFileList as $thisFile) {
                if (!claro_delete_file($thisFile))
                    return false;
            }
        }
        return rmdir($filePath);
    } // end elseif is_dir()
}

/*
 * Copy a file or a directory and its content to another area
 *
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @param  - $delete (bool) - move or copy the file
 * @return - void no return !!
 */

function claro_copy_file($sourcePath, $targetPath) {
    $fileName = my_basename($sourcePath);

    if (is_file($sourcePath)) {
        return copy($sourcePath, $targetPath . '/' . $fileName);
    } elseif (is_dir($sourcePath)) {
        // check to not copy the directory inside itself
        if (preg_match('|^' . $sourcePath . '/|', $targetPath . '/'))
            return false;

        if (!make_dir($targetPath . '/' . $fileName))
            return false;

        $dirHandle = opendir($sourcePath);

        if (!$dirHandle)
            return false;

        $copiableFileList = array();

        while ($element = readdir($dirHandle)) {
            if ($element == '.' || $element == '..')
                continue;
            $copiableFileList[] = $sourcePath . '/' . $element;
        }

        closedir($dirHandle);

        if (count($copiableFileList) > 0) {
            foreach ($copiableFileList as $thisFile) {
                if (!claro_copy_file($thisFile, $targetPath . '/' . $fileName))
                    return false;
            }
        }

        return true;
    } // end elseif is_dir()
}

/* ----------- end of backported functions from Claroline 1.7.x ----------- */


/**
 * helper function to get a file path from get variable
 * @param string $name
 * @global array $_GET
 * @return string
 */
function get_dir_path($name) {
    if (isset($_GET[$name])) {
        $path = q($_GET[$name]);
        if ($path == '/' or $path == '\\') {
            $path = '';
        }
    } else {
        $path = '';
    }
    return $path;
}
