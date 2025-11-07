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

class EditorStorage implements H5peditorStorage {

    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @param string $language Language code
     * @return string Translation in JSON format
     */
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language): ?string {
        global $webDir;

        if (empty($language)) {
            return null;
        }

        $languagescript = $webDir . "/courses/h5p/libraries/" . $machineName . "-" . $majorVersion . "." . $minorVersion . "/language/" . $language . ".json";
        if (!file_exists($languagescript)) {
            return null;
        }

        return file_get_contents($languagescript);
    }

    /**
     * Load a list of available language codes from the database.
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @return array List of possible language codes
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion): array {
        global $webDir;

        $langDir = $webDir . '/courses/h5p/libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion . '/language';
        if (!(file_exists($langDir) && is_dir($langDir))) {
            return [];
        }

        $defaultcode = 'en';
        $defaultext = '.json';
        $languages = [];
        $ldir = scandir($langDir);

        foreach ($ldir as $idx => $lfile) {
            if (!in_array($lfile,array(".", ".."))) {
                if (substr($lfile, -strlen($defaultext)) === $defaultext) {
                    $lfile = substr($lfile, 0, -strlen($defaultext));
                }
                array_push($languages, $lfile);
            }
        }

        // Semantics is 'en' by default. It has to be added always.
        if (!in_array($defaultcode, $languages)) {
            array_unshift($languages, $defaultcode);
        }

        return $languages;
    }

    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $fileId
     */
    public function keepFile($fileId): void {
        // TODO: Implement keepFile() method.
        error_log("Unhandled EditorStorage->keepFile()");
    }

    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array $libraries List of library names + version to load info for
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = NULL): array {
        $librariesout = [];

        if ($libraries !== null) {
            // Get details for the specified libraries.
            foreach ($libraries as $library) {
                $sql = "SELECT title, runnable, metadata_settings, example, tutorial
                          FROM h5p_library
                         WHERE machine_name = ?s
                               AND major_version = ?s
                               AND minor_version = ?s";
                $details = Database::get()->querySingle($sql, $library->name, $library->majorVersion, $library->minorVersion);

                if ($details) {
                    $library->title = $details->title;
                    $library->runnable = $details->runnable;
                    $library->metadataSettings = $details->metadata_settings? json_decode($details->metadata_settings): '';
                    $library->example = $details->example;
                    $library->tutorial = $details->tutorial;
                    $librariesout[] = $library;
                }
            }
        } else {
            $sql = "SELECT machine_name AS name, title, major_version AS majorVersion, minor_version AS minorVersion, runnable, metadata_settings, example, tutorial
                      FROM h5p_library
                     WHERE runnable = 1
                  ORDER BY title, major_version DESC, minor_version DESC";
            $records = Database::get()->queryArray($sql);

            foreach ($records as $library) {
                if (!is_null($library->metadata_settings)) {
                    $library->metadataSettings = json_decode($library->metadata_settings);
                    unset($library->metadata_settings);
                    $librariesout[] = $library;
                }
            }
        }

        return $librariesout;
    }

    /**
     * Alter styles and scripts
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries) {
        // to be implemented
    }

    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string $data Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file): bool {
        // TODO: Implement saveFileTemporarily() method.
        error_log("Unhandled EditorStorage->saveFileTemporarily()");
        return false;
    }

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile
     * @param $content_id
     * @return int|null
     */
    public static function markFileForCleanup($file, $content_id): ?int {
        return null;
    }

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath): void {
        // TODO: Implement removeTemporarilySavedFiles() method.
        error_log("Unhandled EditorStorage->removeTemporarilySavedFiles()");
    }
}
