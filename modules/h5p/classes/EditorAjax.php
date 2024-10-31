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

class EditorAjax implements H5PEditorAjaxInterface {

    /** The component for H5P. */
    public const EDITOR_AJAX_TOKEN = 'editorajax';

    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions(): array {
        $sql = "SELECT hl2.id, hl2.machine_name, hl2.title, hl2.major_version, hl2.minor_version, hl2.patch_version, '' as has_icon, 0 as restricted, 1 as enabled
                  FROM h5p_library hl2
             LEFT JOIN h5p_library hl1
                    ON (hl1.machine_name = hl2.machine_name 
                        AND (hl2.major_version < hl1.major_version 
                             OR (hl2.major_version = hl1.major_version AND hl2.minor_version < hl1.minor_version)
                            )
                        )
                 WHERE hl2.runnable = 1
                   AND hl1.major_version is null
                   AND hl2.machine_name NOT IN ('H5P.Questionnaire')
              ORDER BY hl2.title";
        return Database::get()->queryArray($sql);
    }

    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @param $machineName
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = NULL): ?array  {
        // Added some extra fields to the result because they are expected by functions calling this. They have been
        // taken from method getCachedLibsMap() in h5peditor.class.php.
        $sql = "SELECT hl.id, hl.machine_name, hl.major_version, 
                       hl.minor_version, hl.patch_version, hl.core_major AS h5p_major_version, 
                       hl.core_minor AS h5p_minor_version, hl.title, hl.tutorial, hl.example,
                       '' AS summary, '' AS description, '' AS icon, 0 AS created_at, 0 AS updated_at, 0 AS is_recommended,
                       0 AS popularity, '' AS screenshots, '' as license, '' as owner
                  FROM h5p_library hl";
        if (!empty($machineName)) {
            $sql .= " WHERE hl.machine_name = ?s";
            return Database::get()->queryArray($sql, $machineName);
        }
        return Database::get()->queryArray($sql);
    }

    /**
     * Gets recently used libraries for the current author
     *
     * @return array machine names. The first element in the array is the
     * most recently used.
     */
    public function getAuthorsRecentlyUsedLibraries(): array {
        // This is to be implemented when the Hub client is used.
        return [];
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token): bool {
        return H5PCore::validToken(self::EDITOR_AJAX_TOKEN, $token);
    }

    /**
     * Get translations for a language for a list of libraries
     *
     * @param array $libraries An array of libraries, in the form "<machineName> <majorVersion>.<minorVersion>
     * @param string $language_code
     * @return array
     */
    public function getTranslations($libraries, $language_code): array {
        global $webDir;

        if (empty($language_code)) {
            return [];
        }

        $libsDir = $webDir . '/courses/h5p/libraries/';
        $translations = [];

        foreach ($libraries as $libstring) {
            $librarydata = H5PCore::libraryFromString($libstring);
            if (false === $librarydata) {
                continue;
            }

            $libDir = $librarydata['machineName'] . '-' . $librarydata['majorVersion'] . '.' . $librarydata['minorVersion'];
            $langFile = $libsDir . $libDir . '/language/' . $language_code . '.json';

            if (file_exists($langFile)) {
                $translations[$libstring] = file_get_contents($langFile);
            }
        }

        return $translations;
    }
}
