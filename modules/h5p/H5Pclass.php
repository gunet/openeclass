<?php
// $require_current_course = true;

// require_once '../../include/baseTheme.php';


class H5PClass implements H5PFrameworkInterface {

    private $messages = array('error' => array(), 'info' => array());

    public function setErrorMessage($message, $code = NULL) {
        if (true) {
            $this->messages['error'][] = (object)array(
                'code' => $code,
                'message' => $message
            );
        }
    }


    public function getPlatformInfo() {
        $info = array();
        $info['name'] = "GUNET Openeclass";
        $info['version'] = "4.0";
        $info['h5pVersion'] = "1.0";
        return $info;
    }


    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL) {
    }

    public function setLibraryTutorialUrl($machineName, $tutorialUrl) {
    }

    public function setInfoMessage($message) {
        if (true) {
            $this->messages['info'][] = $message;
        }
    }

    public function getMessages($type) {
        if (empty($this->messages[$type])) {
            return NULL;
        }
        $messages = $this->messages[$type];
        $this->messages[$type] = array();
        return $messages;
    }

    public function t($message, $replacements = array()) {
        return ($message);
    }

    public function getLibraryFileUrl($libraryFolderName, $fileName) {
        return "libraries" . $libraryFolderName . "/" . $fileName;
    }

    public function getUploadedH5pFolderPath() {
        global $webDir, $course_code;
        return $webDir . "/courses/temp/h5p/" . $course_code;
    }

    public function getUploadedH5pPath() {
        global $webDir;
        global $course_code;
        $path = $webDir . '/courses/temp/h5p/' . $course_code . "/*.h5p";
        return implode("", glob($path));
    }

    public function loadAddons() {
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
            $id = $sql->id;
        } else {
            return false;
        }
        return $id;
    }

    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist) {

        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' ' . $defaultLibraryWhitelist;
        }
        return $whitelist;
    }

    public function isPatchedLibrary($library) {

        return TRUE;
    }

    public function isInDevMode() {
        return true;
    }

    public function mayUpdateLibraries() {
        return true;
    }


    public function saveLibraryData(&$libraryData, $new = TRUE) {

        if ($new) {
            if (isset($libraryData['title'])) {
                $title = $libraryData['title'];
            } else {
                $title = NULL;
            }
            if (isset($libraryData['machineName'])) {
                $machine_name = $libraryData['machineName'];
            } else {
                $machine_name = NULL;
            }
            if (isset($libraryData['majorVersion'])) {
                $major_version = $libraryData['majorVersion'];
            } else {
                $major_version = NULL;
            }
            if (isset($libraryData['minorVersion'])) {
                $minor_version = $libraryData['minorVersion'];
            } else {
                $minor_version = NULL;
            }
            if (isset($libraryData['patchVersion'])) {
                $patch_version = $libraryData['patchVersion'];
            } else {
                $patch_version = NULL;
            }
            if (isset($libraryData['runnable'])) {
                $runnable = $libraryData['runnable'];
            } else {
                $runnable = 0;
            }
            if (isset($libraryData['fullscreen'])) {
                $fullscreen = $libraryData['fullscreen'];
            } else {
                $fullscreen = 0;
            }
            if (isset($libraryData['embedTypes'])) {
                $embed_types = implode(",", $libraryData['embedTypes']);
            } else {
                $embed_types = "";
            }
            if (isset($libraryData['preloadedJs'])) {
                $array = array();
                foreach ($libraryData['preloadedJs'] as $libjs) {
                    $array[] = $libjs['path'];
                }
                $array = implode(",", $array);
                $preloaded_js = $array;
            } else {
                $preloaded_js = "";
            }
            if (isset($libraryData['preloadedCss'])) {
                $array = array();
                foreach ($libraryData['preloadedCss'] as $libcss) {
                    $array[] = $libcss['path'];
                }
                $array = implode(",", $array);
                $preloaded_css = $array;
            } else {
                $preloaded_css = "";
            }

            $libraryId = Database::get()->query("INSERT INTO h5p_library(machine_name, title, major_version, minor_version, patch_version, runnable, fullscreen, embed_types, preloaded_js, preloaded_css) VALUES (?s, ?s, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?s)", function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $machine_name, $title, $major_version, $minor_version, $patch_version, $runnable, $fullscreen, $embed_types, $preloaded_js, $preloaded_css)->lastInsertID;

            $libraryData['libraryId'] = $libraryId;
        } else {
            echo "<br>Library already exists in database. Update function not ready";
        }

    }


    public function insertContent($content, $contentMainId = NULL) {
        return $this->updateContent($content);
    }

    public function updateContent($content, $contentMainId = NULL) {
        global $course_id;
        if (isset($content['id'])) {
            $id = $content['id'];
        } else {
            $sql = Database::get()->querySingle("SELECT * FROM h5p_content ORDER BY id DESC LIMIT ?d", 1);
            if (isset($sql->id)) {
                $id = $sql->id + 1;
            } else {
                $id = 1;
            }
        }
        $contentdata = $content['params'];
        $libraryId = $content['library']['libraryId'];

        $sql = Database::get()->query("INSERT INTO h5p_content(id, main_library_id, params, course_id) VALUES (?d, ?d, ?s, ?d)", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $id, $libraryId, $contentdata, $course_id);
        return $id;
    }


    public function resetContentUserData($contentId) {
    }

    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type) {
        foreach ($dependencies as $dependency) {
            $machine_name = $dependency['machineName'];
            $major_version = $dependency['majorVersion'];
            $minor_version = $dependency['minorVersion'];
            $sqlselect = Database::get()->querySingle("SELECT * FROM h5p_library WHERE machine_name = ?s AND major_version = ?d AND minor_version = ?d LIMIT ?d", $machine_name, $major_version, $minor_version, 1);
            $required_library_id = $sqlselect->id;


            Database::get()->query("INSERT INTO h5p_library_dependency (library_id, required_library_id, dependency_type) VALUES (?d, ?d, ?s)", function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $libraryId, $required_library_id, $dependency_type);
        }
    }


    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL) {

        $sql = Database::get()->query("INSERT INTO h5p_content_dependency (content_id,library_id,dependency_type) SELECT ?d,library_id,dependency_type FROM h5p_content_dependency WHERE content_id = ?d", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $contentId, $copyFromId);
    }

    public function deleteContentData($contentId) {
        Database::get()->query("DELETE FROM h5p_content WHERE id = ?d", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $contentId);
    }

    public function deleteLibraryUsage($contentId) {

        $sql = Database::get()->query("DELETE FROM h5p_content_dependency WHERE content_id = ?d", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $contentId);

    }

    public function saveLibraryUsage($contentId, $librariesInUse) {

        foreach ($librariesInUse as $library) {
            $libraryId = $library['library']['libraryId'];
            $dependencyType = $library['type'];
            $sql = Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id, dependency_type) VALUES (?d, ?d, ?s)", function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $contentId, $libraryId, $dependencyType);

        }
    }

    public function getLibraryUsage($libraryId, $skipContent = FALSE) {

    }

    public function loadLibrary($machineName, $majorVersion, $minorVersion) {
        $sql = Database::get()->querySinge("SELECT * FROM h5p_library WHERE machine_name = ?s && major_version = ?s && minor_version = ?s", $machineName, $majorVersion, $minorVersion);

        $libraryId = $sql->id;
        $library = array();
        $library['libraryId'] = $libraryId;

        $path = 'libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion;
        $json = $path . '/' . "library.json";

        $string = file_get_contents($json); // getting the library setings from the json file
        $json = json_decode($string, true);
        //    - title: The library's name
        $library['title'] = $json['title'];
        //    - machineName: The library machineName
        $library['machineName'] = $json['machineName'];
        //    - majorVersion: The library's majorVersion
        $library['majorVersion'] = $json['majorVersion'];
        //    - minorVersion: The library's minorVersion
        $library['minorVersion'] = $json['minorVersion'];
        //    - patchVersion: The library's patchVersion
        $library['patchVersion'] = $json['patchVersion'];
        //    - runnable: 1 if the library is a content type, 0 otherwise
        $library['runnable'] = $json['runnable'];
        //    - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
        if (isset($json['fullscreen'])) {
            $library['fullscreen'] = $json['fullscreen'];
        }
        //    - embedTypes(optional): list of supported embed types
        if (isset($json['embedTypes'])) {
            $library['embedTypes'] = $json['embedTypes'];
        }
        //    - preloadedJs(optional): comma separated string with js file paths

        //    - preloadedCss(optional): comma separated sting with css file paths
        if (isset($json['preloadedCss'])) {
            $preloadedCss = '';
            $count = count($json['preloadedCss']);
            if ($count == 1) {
                $preloadedCss = $preloadedCss . $path . $json['preloadedCss'][0]['path'];
            } else {
                for ($i = 0; $i < $count; $i++) {
                    if (isset($json['preloadedCss'][$i + 1])) {
                        $preloadedCss = $preloadedCss . $path . $json['preloadedCss'][$i]['path'] . ",";
                    } else {
                        $preloadedCss = $preloadedCss . $path . $json['preloadedCss'][$i]['path'];
                    }
                }
            }
            $library['preloadedCss'] = $preloadedCss;
        }
        //    - dropLibraryCss(optional): list of associative arrays containing:
        //      - machineName: machine name for the librarys that are to drop their css
        if (isset($json['dropLibraryCss'])) {
            $$library['dropLibraryCss'] = $json['dropLibraryCss'];
        }


        //    - semantics(optional): Json describing the content structure for the library
        $semantics = $path . "/" . "semantics.json";
        if (file_exists($semantics)) {
            $decode = file_get_contents($semantics);
            $library['semantics'] = json_decode($decode, true);
        }
        //    - preloadedDependencies(optional): list of associative arrays containing:
        //      - machineName: Machine name for a library this library is depending on
        //      - majorVersion: Major version for a library this library is depending on
        //      - minorVersion: Minor for a library this library is depending on
        if (isset($json['preloadedDependencies'])) {
            $library['preloadedDependencies'] = $json['preloadedDependencies'];
        }
        //    - dynamicDependencies(optional): list of associative arrays containing:
        //      - machineName: Machine name for a library this library is depending on
        //      - majorVersion: Major version for a library this library is depending on
        //      - minorVersion: Minor for a library this library is depending on
        if (isset($json['dynamicDependencies'])) {
            $library['dynamicDependencies'] = $json['dynamicDependencies'];
        }
        //    - editorDependencies(optional): list of associative arrays containing:
        //      - machineName: Machine name for a library this library is depending on
        //      - majorVersion: Major version for a library this library is depending on
        //      - minorVersion: Minor for a library this library is depending on
        if (isset($json['editorDependencies'])) {
            $library['editorDependencies'] = $json['editorDependencies'];
        }
        return $library;
    }

    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion) {

        $path = 'libraries/' . $machineName . '-' . $majorVersion . '.' . $minorVersion;
        $semantics = $path . '/semantics.json';
        $string = file_get_contents($semantics);
        return $string;
    }

    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion) {
        global $link;
    }

    public function deleteLibraryDependencies($libraryId) {
        Database::get()->query("DELETE FROM h5p_library_dependency WHERE library_id = ?d", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $libraryId);
    }


    public function lockDependencyStorage() {
    }


    public function unlockDependencyStorage() {
    }

    /**
     * Custom : deletes a directory whether it's empty or not
     *
     * @param string $dir
     *   path to directory
     *
     * Author : Dimitris Delis
     */

    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }


    public function deleteLibrary($library) {
        Database::get()->query("DELETE FROM h5p_library WHERE id = ?d", function ($errormsg) {
            echo "An error has occured: " . $errormsg;
        }, $library->id);
        $dir = "libraries/" . $library->name . "-" . $library->major_version . "." . $library->minor_version;
        deleteDirectory($dir);
    }

    public function loadContent($id) {
        $content = array();
        $sql = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d", $id);
        $content['id'] = $sql->id;
        $content['params'] = $sql->params;
        $content['libraryId'] = $sql->main_library_id;

        $path = 'h5p/content/' . $content['id'] . '/h5p.json';
        $json = file_get_contents($path);
        $json = json_decode($json, true);

        $embedTypes = '';
        $count = count($json['embedTypes']);
        if ($count == 1) {
            $embedTypes = $json['embedTypes'][0];
        } else {
            for ($i = 0; $i < $count; $i++) {
                if (isset($json['embedTypes'][$i + 1])) {
                    $embedTypes = $embedTypes . $json['embedTypes'][$i] . ",";
                } else {
                    $embedTypes = $embedTypes . $json['embedTypes'][$i];
                }
            }
        }
        $content['embedTypes'] = $embedTypes;

        $content['title'] = $json['title'];

        $content['language'] = $json['language'];

        $content['libraryName'] = $json['mainLibrary'];

        foreach ($json['preloadedDependencies'] as $jsondep) {
            if (strcmp($content['libraryName'], $jsondep['machineName']) == 0) {
                $content['libraryName'] = $jsondep['machineName'];
                $content['libraryMajorVersion'] = $jsondep['majorVersion'];
                $content['libraryMinorVersion'] = $jsondep['minorVersion'];
            }
        }

        $librarypath = 'h5p/libraries/' . $content['libraryName'] . '-' . $content['libraryMajorVersion'] . '.' . $content['libraryMinorVersion'] . '/library.json';
        $libjson = file_get_contents($librarypath);
        $libjson = json_decode($libjson, true);

        $libraryembedTypes = '';
        $libcount = count($libjson['embedTypes']);
        if ($libcount == 1) {
            $libraryembedTypes = $libjson['embedTypes'][0];
        } else {
            for ($i = 0; $i < $libcount; $i++) {
                if (isset($libjson['embedTypes'][$i + 1])) {
                    $libraryembedTypes = $libraryembedTypes . $libjson['embedTypes'][$i] . ",";
                } else {
                    $libraryembedTypes = $libraryembedTypes . $libjson['embedTypes'][$i];
                }
            }
        }
        $content['libraryEmbedTypes'] = $libraryembedTypes;

        if (isset($libjson['fullscreen'])) {
            $content['libraryFullscreen'] = $libjson['fullscreen'];
        } else {
            $content['libraryFullscreen'] = 0;
        }
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
            $sql = Database::get()->query("INSERT INTO h5p_content_dependency(content_id, library_id) VALUES(?d, ?d)", function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $id, $libraryid);
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

    public function getOption($name, $default = NULL) {
    }

    public function setOption($name, $value) {
    }

    public function updateContentFields($id, $fields) {
    }

    public function clearFilteredParameters($library_id) {
    }

    public function getNumNotFiltered() {
    }

    public function getNumContent($library_id, $skip = NULL) {
    }

    public function isContentSlugAvailable($slug) {
    }

    public function getLibraryStats($type) {
    }

    public function getNumAuthors() {
    }

    public function saveCachedAssets($key, $libraries) {
    }

    public function deleteCachedAssets($library_id) {
    }

    public function getLibraryContentCount() {
    }

    public function afterExportCreated($content, $filename) {
    }

    public function hasPermission($permission, $id = NULL) {
        return TRUE;
    }

    public function replaceContentTypeCache($contentTypeCache) {
    }

    public function libraryHasUpgrade($library) {
        return false;
    }

}
