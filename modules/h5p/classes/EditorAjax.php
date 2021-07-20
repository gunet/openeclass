<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

class EditorAjax implements H5PEditorAjaxInterface {

    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions(): array {
        $sql = "SELECT hl.id, hl.machine_name, hl.title, hl.major_version, hl.minor_version, hl.patch_version, '' as has_icon, 0 as restricted, 1 as enabled
                  FROM h5p_library hl
                 WHERE hl.runnable = 1
              ORDER BY hl.title";
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
        // TODO: Implement getContentTypeCache() method.
        error_log("Unhandled EditorAjax->getContentTypeCache()");
        return null;
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
        // TODO: Implement validateEditorToken() method.
        error_log("Unhandled EditorAjax->validateEditorToken()");
        return true;
    }

    /**
     * Get translations for a language for a list of libraries
     *
     * @param array $libraries An array of libraries, in the form "<machineName> <majorVersion>.<minorVersion>
     * @param string $language_code
     * @return array
     */
    public function getTranslations($libraries, $language_code): array {
        // TODO: Implement getTranslations() method.
        error_log("Unhandled EditorAjax->getTranslations()");
        return [];
    }
}
