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

class H5PFramework implements H5PFrameworkInterface {

    private $messages = array('error' => array(), 'info' => array());
    private $handle_errormsg;

    public function __construct() {
        $this->handle_errormsg = function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        };
    }

    public function setErrorMessage($message, $code = NULL) {
        $this->messages['error'][] = (object) array(
            'code' => $code,
            'message' => $message
        );
    }

    public function getPlatformInfo() {
        $info = array();
        $info['name'] = "GUNET Openeclass";
        $info['version'] = "4.0";
        $info['h5pVersion'] = "1.0";
        return $info;
    }

    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL, $fullData = FALSE, $headers = array(), $files = array(), $method = 'POST') {
    }

    public function setLibraryTutorialUrl($machineName, $tutorialUrl) {
    }

    /**
     * Show the user an information message
     *
     * @param string $message
     *  The error message
     */
    public function setInfoMessage($message) {
        if ($message !== null) {
            $this->messages['info'][] = $message;
        }
    }

    /**
     * Return messages
     *
     * @param string $type 'info' or 'error'
     * @return string[]
     */
    public function getMessages($type): array {
        if (!isset($this->messages[$type])) {
            return array();
        }
        $messages = $this->messages[$type];
        $this->messages[$type] = array();
        return $messages;
    }

    /**
     * Translation function
     *
     * @param string $message
     *  The english string to be translated.
     * @param array $replacements
     *   An associative array of replacements to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value. Based
     *   on the first character of the key, the value is escaped and/or themed:
     *    - !variable: inserted as is
     *    - @variable: escape plain text to HTML
     *    - %variable: escape text and theme as a placeholder for user-submitted
     *      content
     * @return string Translated string
     * Translated string
     */
    public function t($message, $replacements = array()): string {
        if ($replacements !== NULL && is_array($replacements) && count($replacements) > 0) {
            foreach ($replacements as $key => $value) {
                $message = str_replace($key, $value, $message);
            }
        }
        return $message;
    }

    public function getLibraryFileUrl($libraryFolderName, $fileName) {
        return "libraries" . $libraryFolderName . "/" . $fileName;
    }

    public function getUploadedH5pFolderPath() {
        global $webDir, $course_code;
        return $webDir . "/courses/temp/h5p/" . $course_code;
    }

    public function getUploadedH5pPath() {
        global $webDir, $course_code;
        $path = $webDir . '/courses/temp/h5p/' . $course_code . "/*.h5p";
        return implode("", glob($path));
    }

    /**
     * Load addon libraries
     *
     * @return array
     */
    public function loadAddons(): array {
        $addons = array();

        $sql = "SELECT l1.id as library_id, l1.machine_name, l1.major_version, l1.minor_version, l1.patch_version, l1.add_to, l1.preloaded_js, l1.preloaded_css
                  FROM h5p_library l1
             LEFT JOIN h5p_library l2
                    ON (l1.machine_name = l2.machine_name
                        AND (l1.major_version < l2.major_version
                             OR (l1.major_version = l2.major_version AND l1.minor_version < l2.minor_version)
                            )
                       )
                 WHERE l1.add_to IS NOT NULL
                   AND l2.machine_name IS NULL";

        $records = Database::get()->queryArray($sql);

        // NOTE: These are treated as library objects but are missing the following properties:
        // title, droplibrarycss, fullscreen, runnable, semantics.

        // Extract num from records
        foreach ($records as $addon) {
            $addons[] = H5PCore::snakeToCamel($addon);
        }

        return $addons;
    }

    public function getLibraryConfig($libraries = NULL) {
        return defined('H5P_LIBRARY_CONFIG') ? H5P_LIBRARY_CONFIG : NULL;
    }

    public function loadLibraries() {
        $libraries = array();
        $sql = Database::get()->queryArray("SELECT * FROM h5p_library ORDER BY machine_name, major_version ASC, minor_version ASC");
        foreach ($sql as $lib) {
            $libraries[$lib->machine_name][] = $lib->machine_name;
        }
        return $libraries;
    }

    public function getAdminUrl() {
    }

    /**
     * Get id to an existing library.
     * If version number is not specified, the newest version will be returned.
     *
     * @param string $machineName
     *   The librarys machine name
     * @param ?int $majorVersion
     *   Optional major version number for library
     * @param ?int $minorVersion
     *   Optional minor version number for library
     * @return int
     *   The id of the specified library or FALSE
     */
    public function getLibraryId($machineName, $majorVersion = NULL, $minorVersion = NULL) {
        if ($majorVersion !== NULL) {
            if ($minorVersion !== NULL) {
                $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $machineName, $majorVersion, $minorVersion, 1);
            } else {
                $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $machineName, $majorVersion, 1);
            }
        } else {
            $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $machineName, 1);
        }
        if ($sql) {
            return $sql->id;
        }
        return false;
    }

    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist) {
        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' ' . $defaultLibraryWhitelist;
        }
        return $whitelist;
    }

    /**
     * Is the library a patched version of an existing library?
     *
     * @param object $library
     *   An associative array containing:
     *   - machineName: The library machineName
     *   - majorVersion: The librarys majorVersion
     *   - minorVersion: The librarys minorVersion
     *   - patchVersion: The librarys patchVersion
     * @return boolean
     *   TRUE if the library is a patched version of an existing library
     *   FALSE otherwise
     */
    public function isPatchedLibrary($library): bool {
        $sql = "SELECT id
                  FROM h5p_library
                 WHERE machine_name = ?s
                   AND major_version = ?d
                   AND minor_version = ?d
                   AND patch_version < ?d";

        $library = Database::get()->querySingle($sql, $library['machineName'], $library['majorVersion'], $library['minorVersion'], $library['patchVersion']);

        return !empty($library);
    }

    public function isInDevMode() {
        return true;
    }

    public function mayUpdateLibraries() {
        return true;
    }

    /**
     * Store data about a library
     *
     * Also fills in the libraryId in the libraryData object if the object is new
     *
     * @param object $libraryData
     *   Associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - metadataSettings: Associative array containing:
     *      - disable: 1 if the library should not support setting metadata (copyright etc)
     *      - disableExtraTitleField: 1 if the library don't need the extra title field
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): list of associative arrays containing:
     *     - path: path to a js file relative to the library root folder
     *   - preloadedCss(optional): list of associative arrays containing:
     *     - path: path to css file relative to the library root folder
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - language(optional): associative array containing:
     *     - languageCode: Translation in json format
     * @param bool $new
     * @return
     */
    public function saveLibraryData(&$libraryData, $new = TRUE) {
        $machine_name = $libraryData['machineName'];
        $title = $libraryData['title'];
        $major_version = $libraryData['majorVersion'];
        $minor_version = $libraryData['minorVersion'];
        $patch_version = $libraryData['patchVersion'];
        $runnable = $libraryData['runnable'];
        $fullscreen = $libraryData['fullscreen'] ?? 0;
        $embed_types = '';
        if (isset($libraryData['embedTypes'])) {
            $embed_types = implode(', ', $libraryData['embedTypes']);
        }
        $preloaded_js = $this->libraryParameterValuesToCsv($libraryData, 'preloadedJs', 'path');
        $preloaded_css = $this->libraryParameterValuesToCsv($libraryData, 'preloadedCss', 'path');
        $droplibrary_css = $this->libraryParameterValuesToCsv($libraryData, 'dropLibraryCss', 'machineName');
        $semantics = $libraryData['semantics'] ?? null;
        $add_to = isset($libraryData['addTo']) ? json_encode($libraryData['addTo']) : null;
        $core_major = $libraryData['coreApi']['majorVersion'] ?? null;
        $core_minor = $libraryData['coreApi']['minorVersion'] ?? null;
        $metadata_settings = $libraryData['metadataSettings'] ?? null;

        if ($new) {
            // Create new library and keep track of id.
            $sql = "INSERT INTO h5p_library 
                                (machine_name, title, major_version, minor_version, patch_version, runnable, fullscreen, embed_types, preloaded_js, preloaded_css, droplibrary_css,
                                 semantics, add_to, core_major, core_minor, metadata_settings) 
                         VALUES (?s, ?s, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d, ?s)";
            $libraryId = Database::get()->query($sql, $this->handle_errormsg,
                $machine_name, $title, $major_version, $minor_version, $patch_version, $runnable, $fullscreen, $embed_types, $preloaded_js, $preloaded_css, $droplibrary_css,
                $semantics, $add_to, $core_major, $core_minor, $metadata_settings)->lastInsertID;

            $libraryData['libraryId'] = $libraryId;
        } else {
            $libraryId = $libraryData['libraryId'];
            $sql = "UPDATE h5p_library 
                       SET machine_name = ?s, title = ?s, major_version = ?d, minor_version = ?d, patch_version = ?d, 
                           runnable = ?d, fullscreen = ?d, embed_types = ?s, preloaded_js = ?s, preloaded_css = ?s, 
                           droplibrary_css = ?s, semantics = ?s, add_to = ?s, core_major = ?d, core_minor = ?d, metadata_settings = ?s
                     WHERE id = ?d";
            Database::get()->query($sql, $this->handle_errormsg,
                $machine_name, $title, $major_version, $minor_version, $patch_version, $runnable, $fullscreen, $embed_types, $preloaded_js, $preloaded_css, $droplibrary_css,
                $semantics, $add_to, $core_major, $core_minor, $metadata_settings, $libraryId);
            // Remove old dependencies.
            $this->deleteLibraryDependencies($libraryData['libraryId']);
        }
    }

    /**
     * Insert new content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     */
    public function insertContent($content, $contentMainId = NULL) {
        return $this->updateContent($content);
    }

    /**
     * Update old content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     */
    public function updateContent($content, $contentMainId = NULL) {

        global $course_id, $uid, $is_editor;

        // Add title to 'params' to use in the editor.
        if (!empty($content['title'])) {
            $params = json_decode($content['params']);
            $params->title = $content['title'];
            $content['params'] = json_encode($params);
        }
        $enabled = ($is_editor) ? 1 : 0;
        if (!isset($content['id'])) {
            $id = Database::get()->query("INSERT INTO h5p_content (main_library_id, params, course_id, creator_id, enabled) VALUES (?d, ?s, ?d, ?d, ?d)",
                $content['library']['libraryId'], $content['params'], $course_id, $uid, $enabled)->lastInsertID;
        } else {
            Database::get()->query("UPDATE h5p_content SET main_library_id = ?d, params = ?s WHERE id = ?d",
                $content['library']['libraryId'], $content['params'], $content['id']);
            $id = $content['id'];
        }
        return $id;
    }

    /**
     * Resets marked user data for the given content.
     *
     * @param int $contentId
     */
    public function resetContentUserData($contentId) {
        // Currently, we do not store user data for a content.
    }

    /**
     * Save what libraries a library is depending on
     *
     * @param int $libraryId
     *   Library Id for the library we're saving dependencies for
     * @param array $dependencies
     *   List of dependencies as associative arrays containing:
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     * @param string $dependency_type
     *   What type of dependency this is, the following values are allowed:
     *   - editor
     *   - preloaded
     *   - dynamic
     */
    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type) {
        foreach ($dependencies as $dependency) {
            $sql = "SELECT *
                      FROM h5p_library
                     WHERE machine_name = ?s
                           AND major_version = ?d
                           AND minor_version = ?d
                     LIMIT 1";
            $dependencylibrary = Database::get()->querySingle($sql, $dependency['machineName'], $dependency['majorVersion'], $dependency['minorVersion']);

            $sql = "INSERT INTO h5p_library_dependency (library_id, required_library_id, dependency_type) VALUES (?d, ?d, ?s)";
            Database::get()->query($sql, $this->handle_errormsg, $libraryId, $dependencylibrary->id, $dependency_type);
        }
    }

    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL) {
        Database::get()->query("INSERT INTO h5p_content_dependency (content_id,library_id,dependency_type) SELECT ?d ,library_id, dependency_type FROM h5p_content_dependency WHERE content_id = ?d",
            $this->handle_errormsg, $contentId, $copyFromId);
    }

    public function deleteContentData($contentId) {
        Database::get()->query("DELETE FROM h5p_content WHERE id = ?d", $this->handle_errormsg, $contentId);
    }

    public function deleteLibraryUsage($contentId) {
        Database::get()->query("DELETE FROM h5p_content_dependency WHERE content_id = ?d", $this->handle_errormsg, $contentId);
    }

    public function saveLibraryUsage($contentId, $librariesInUse) {
        foreach ($librariesInUse as $library) {
            $libraryId = $library['library']['libraryId'];
            $dependencyType = $library['type'];
            Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id, dependency_type) VALUES (?d, ?d, ?s)",
                $this->handle_errormsg, $contentId, $libraryId, $dependencyType);
        }
    }

    public function getLibraryUsage($libraryId, $skipContent = FALSE) {
    }

    /**
     * Loads a library
     *
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return array|FALSE
     *   FALSE if the library does not exist.
     *   Otherwise an associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - preloadedDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - dynamicDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - editorDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion): bool|array {
        global $webDir;

        $sql = "SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?s AND minor_version = ?s";
        $libRow = Database::get()->querySingle($sql, $machineName, $majorVersion, $minorVersion);

        if (!$libRow) {
            return false;
        }

        $libraryId = $libRow->id;
        $library = array();
        $library['libraryId'] = $libraryId;

        $path = $webDir . '/courses/h5p/libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion;
        $json = $path . '/' . "library.json";

        $string = file_get_contents($json); // getting the library setings from the json file
        $json = json_decode($string, true);
        $library['title'] = $json['title'];
        $library['machineName'] = $json['machineName'];
        $library['majorVersion'] = $json['majorVersion'];
        $library['minorVersion'] = $json['minorVersion'];
        $library['patchVersion'] = $json['patchVersion'];
        $library['runnable'] = $json['runnable'];

        if (isset($json['fullscreen'])) {
            $library['fullscreen'] = $json['fullscreen'];
        }

        if (isset($json['embedTypes'])) {
            $library['embedTypes'] = $json['embedTypes'];
        }

        $library['preloadedJs'] = '';
        if (isset($json['preloadedJs'])) {
            $preloadedJs = '';
            $count = count($json['preloadedJs']);
            for ($i = 0; $i < $count; $i++) {
                if ($i != 0) {
                    $preloadedJs .= ", ";
                }
                $preloadedJs .= $json['preloadedJs'][$i]['path'];
            }
            $library['preloadedJs'] = $preloadedJs;
        }

        $library['preloadedCss'] = '';
        if (isset($json['preloadedCss'])) {
            $preloadedCss = '';
            $count = count($json['preloadedCss']);
            for ($i = 0; $i < $count; $i++) {
                if ($i != 0) {
                    $preloadedCss .= ", ";
                }
                $preloadedCss .= $json['preloadedCss'][$i]['path'];
            }
            $library['preloadedCss'] = $preloadedCss;
        }

        if (isset($json['dropLibraryCss'])) {
            $library['dropLibraryCss'] = $json['dropLibraryCss'];
        }

        $semantics = $path . "/" . "semantics.json";
        if (file_exists($semantics)) {
            $decode = file_get_contents($semantics);
            $library['semantics'] = json_decode($decode, true);
        }

        if (isset($json['preloadedDependencies'])) {
            $library['preloadedDependencies'] = $json['preloadedDependencies'];
        }

        if (isset($json['dynamicDependencies'])) {
            $library['dynamicDependencies'] = $json['dynamicDependencies'];
        }

        if (isset($json['editorDependencies'])) {
            $library['editorDependencies'] = $json['editorDependencies'];
        }

        return $library;
    }

    /**
     * Loads library semantics.
     *
     * @param string $machineName
     *   Machine name for the library
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return string
     *   The library's semantics as json
     */
    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion): ?string {
        global $webDir;
        $semantics = $webDir . '/courses/h5p/libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion . '/semantics.json';
        return (file_exists($semantics)) ? file_get_contents($semantics): null;
    }

    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion) {
    }

    /**
     * Delete all dependencies belonging to given library
     *
     * @param int $libraryId
     *   Library identifier
     */
    public function deleteLibraryDependencies($libraryId) {
        Database::get()->query("DELETE FROM h5p_library_dependency WHERE library_id = ?d", $this->handle_errormsg, $libraryId);
    }

    public function lockDependencyStorage() {
    }

    public function unlockDependencyStorage() {
    }

    public function deleteLibrary($library) {
        Database::get()->query("DELETE FROM h5p_library WHERE id = ?d", $this->handle_errormsg, $library->id);
        $dir = "libraries/" . $library->name . "-" . $library->major_version . "." . $library->minor_version;
        H5PCore::deleteFileTree($dir);
    }

    /**
     * Load content.
     *
     * @param int $id
     *   Content identifier
     * @return array
     *   Associative array containing:
     *   - contentId: Identifier for the content
     *   - params: json content as string
     *   - embedType: csv of embed types
     *   - title: The contents title
     *   - language: Language code for the content
     *   - libraryId: Id for the main library
     *   - libraryName: The library machine name
     *   - libraryMajorVersion: The library's majorVersion
     *   - libraryMinorVersion: The library's minorVersion
     *   - libraryEmbedTypes: CSV of the main library's embed types
     *   - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
     */
    public function loadContent($id): ?array {
        $sql = "SELECT hc.id, hc.main_library_id, hc.params, hc.course_id, hc.title, 
                       hl.id as libraryid, hl.machine_name, hl.title as librarytitle, 
                       hl.major_version, hl.minor_version, hl.fullscreen, hl.embed_types, 
                       hl.semantics
                  FROM h5p_content hc
                  JOIN h5p_library hl ON hl.id = hc.main_library_id
                 WHERE hc.id = ?d";

        $data = Database::get()->querySingle($sql, $id);

        if (!$data) {
            return null;
        }

        $content = array(
            'id' => $data->id,
            'params' => $data->params,
            'embedType' => 'iframe',
            'title' => $data->title,
            'slug' => H5PCore::slugify($data->librarytitle) . '-' . $data->id,
            'libraryId' => $data->libraryid,
            'libraryName' => $data->machine_name,
            'libraryMajorVersion' => $data->major_version,
            'libraryMinorVersion' => $data->minor_version,
            'libraryEmbedTypes' => $data->embed_types,
            'libraryFullscreen' => $data->fullscreen,
            'metadata' => ''
        );

        $params = json_decode($data->params);
        if (empty($params->metadata)) {
            $params->metadata = new stdClass();
        }
        // Add title to metadata.
        if (!empty($params->title) && empty($params->metadata->title)) {
            $params->metadata->title = $params->title;
        }
        $content['metadata'] = $params->metadata;
        $content['params'] = json_encode($params->params ?? $params);

        return $content;
    }

    /**
     * Custom : saves the dependencies of a content into the database
     * gets the data from the h5p.json file and the database(lirbrary table)
     *
     * @param int $id
     *   Content identifier
     *
     * Author : Dimitris Delis
     */
    public function saveContentDependencies($id) {
        $path = 'courses/h5p/content/' . $id . "/h5p.json";
        $file = file_get_contents($path);
        $json = json_decode($file, true);
        foreach ($json['preloadedDependencies'] as $json) {
            $machinename = $json['machineName'];
            $majorVersion = $json['majorVersion'];
            $minorVersion = $json['minorVersion'];
            $sqllib = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d ORDER BY major_version DESC, minor_version DESC, patch_version DESC LIMIT ?d", $machinename, $majorVersion, $minorVersion, 1);
            $libraryid = $sqllib->id;
            Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id) VALUES(?d, ?d)", $this->handle_errormsg, $id, $libraryid);
        }
    }

    public function loadContentDependencies($id, $type = NULL) {
        $content = array();
        if ($type) {
            $sql = Database::get()->queryArray("SELECT * FROM h5p_content_dependency WHERE content_id = ?d AND dependency_type = ?s", $id, $type);
        } else {
            $sql = Database::get()->queryArray("SELECT * FROM h5p_content_dependency WHERE content_id = ?d", $id);
        }
        $library[]['libraryId'] = $sql->library_id;

        foreach ($library as $lib) {
            $libraryId = $lib['libraryId'];
            $sql = Database::get()->querySingle("SELECT * FROM h5p_library WHERE id = ?d", $libraryId);
            $lib['libraryId'] = $sql->id;
            $lib['machineName'] = $sql->machine_name;
            $lib['majorVersion'] = $sql->major_version;
            $lib['minorVersion'] = $sql->minor_version;
            $lib['patchVersion'] = $sql->patch_version;
            $lib['preloadedJs'] = $sql->preloaded_js;
            $lib['preloadedCss'] = $sql->preloaded_css;
            $lib['dropCss'] = $sql->dropLibraryCss;
            $content[] = $lib;
        }

        return $content;
    }

    /**
     * Get stored setting.
     *
     * @param string $name
     *   Identifier for the setting
     * @param string $default
     *   Optional default value if settings is not set
     * @return mixed
     *   Whatever has been stored as the setting
     */
    public function getOption($name, $default = NULL) {
        if ($name == 'hub_is_enabled') {
            return true;
        }

        // avoid updating the libraries cache when using the Hub selector
        if ($name == 'content_type_cache_updated_at') {
            return time();
        }

        return $default;
    }

    public function setOption($name, $value) {
    }

    public function updateContentFields($id, $fields) {
    }

    /**
     * Will clear filtered params for all the content that uses the specified
     * libraries. This means that the content dependencies will have to be rebuilt,
     * and the parameters re-filtered.
     *
     * @param array $library_ids
     */
    public function clearFilteredParameters($library_ids) {
        // currently no need for implementing
    }

    public function getNumNotFiltered() {
    }

    public function getNumContent($libraryId, $skip = NULL) {
    }

    public function isContentSlugAvailable($slug) {
    }

    public function getLibraryStats($type) {
    }

    public function getNumAuthors() {
    }

    public function saveCachedAssets($key, $libraries) {
    }

    /**
     * Locate hash keys for given library and delete them.
     * Used when cache file are deleted.
     *
     * @param int $library_id
     *  Library identifier
     * @return array
     *  List of hash keys removed
     */
    public function deleteCachedAssets($library_id): array {
        // currently no need for implementing
        return [];
    }

    public function getLibraryContentCount() {
    }

    public function afterExportCreated($content, $filename) {
    }

    /**
     * Check if user has permissions to an action
     *
     * @method hasPermission
     * @param  [H5PPermission] $permission Permission type, ref H5PPermission
     * @param  [int]           $id         Id need by platform to determine permission
     * @return boolean
     */
    public function hasPermission($permission, $id = NULL): bool {
        // H5P capabilities have not been introduced.
        return true;
    }

    public function replaceContentTypeCache($contentTypeCache) {
    }

    public function libraryHasUpgrade($library) {
        return false;
    }

    public function replaceContentHubMetadataCache($metadata, $lang) {
        return null;
    }

    public function getContentHubMetadataCache($lang = 'en') {
        return null;
    }

    public function getContentHubMetadataChecked($lang = 'en') {
        return null;
    }

    public function setContentHubMetadataChecked($time, $lang = 'en') {
        return false;
    }

    /**
     * Convert list of library parameter values to csv.
     *
     * @param array $librarydata Library data as found in library.json files
     * @param string $key Key that should be found in $librarydata
     * @param string $searchparam The library parameter (Default: 'path')
     * @return string Library parameter values separated by ', '
     */
    private function libraryParameterValuesToCsv(array $librarydata, string $key, string $searchparam = 'path'): string {
        if (isset($librarydata[$key])) {
            $parametervalues = array();
            foreach ($librarydata[$key] as $file) {
                foreach ($file as $index => $value) {
                    if ($index === $searchparam) {
                        $parametervalues[] = $value;
                    }
                }
            }
            return implode(', ', $parametervalues);
        }
        return '';
    }

}
