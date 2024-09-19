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

require_once 'include/lib/curlutil.class.php';
require_once 'H5PFactory.php';

class H5PHubUpdater {

    /** @var H5PFactory */
    protected $factory;

    /** @var H5PFramework The Open eClass H5PFramework implementation */
    protected $framework;

    /** @var H5PCore The Open eClass H5PCore implementation */
    protected $core;

    /** @var H5PValidator */
    protected $h5pValidator;

    /** @var H5PStorage */
    protected $h5pStorage;

    protected $h5pPath;
    protected $uploadDir;

    /**
     * H5PHubUpdater constructor.
     */
    public function __construct() {
        global $webDir, $course_code;

        $this->h5pPath = $webDir . '/courses/h5p';
        $this->uploadDir = $webDir . '/courses/temp/h5p/' . $course_code;

        $this->factory = new H5PFactory();
        $this->framework = $this->factory->getFramework();
        $this->core = new H5PCore($this->framework, $this->h5pPath, $this->uploadDir, 'en', FALSE);
        $this->h5pValidator = new H5PValidator($this->framework, $this->core);
        $this->h5pStorage = new H5PStorage($this->framework, $this->core);
    }

    /**
     * Fetch and install the latest H5P content types libraries from the official H5P repository.
     * If the latest version of a content type library is present in the system, nothing is done for that content type.
     */
    public function fetchLatestContentTypes() {

        $contentTypes = $this->getLatestContentTypes();
        if (empty($contentTypes)) {
            return;
        }

        $framework = $this->factory->getFramework();

        foreach ($contentTypes->contentTypes as $type) {

            $library = [
                'machineName' => $type->id,
                'majorVersion' => $type->version->major,
                'minorVersion' => $type->version->minor,
                'patchVersion' => $type->version->patch,
            ];

            // Don't fetch content types that require a higher H5P core API version.
            if (!$this->isRequiredCoreApi($type->coreApiVersionNeeded)) {
                error_log("skip fetching: " .  H5PCore::libraryToString($library)); // log for backtracking purposes
                continue;
            }

            // Add example and tutorial to the library, to store this information too.
            if (isset($type->example)) {
                $library['example'] = $type->example;
            }
            if (isset($type->tutorial)) {
                $library['tutorial'] = $type->tutorial;
            }

            $shoulddownload = true;
            if ($framework->getLibraryId($type->id, $type->version->major, $type->version->minor)) {
                if (!$framework->isPatchedLibrary($library)) {
                    $shoulddownload = false;
                }
            }

            if ($shoulddownload) {
                $this->fetchContentType($library);
            }
        }
    }

    /**
     * Given an H5P content type machine name, fetch and install the required library from the official H5P repository.
     *
     * @param array $library Library machineName, majorVersion and minorVersion.
     * @return int|null Returns the id of the content type library installed, null otherwise.
     */
    public function fetchContentType(array $library): ?int {
        if (file_exists($this->uploadDir)) {
            H5PCore::deleteFileTree($this->uploadDir);
        }
        mkdir($this->uploadDir, 0775, true);

        $endpoint = $this->getApiEndpoint($library['machineName']);
        $targetFile = $this->uploadDir . "/" . $library['machineName'] . '-' . $library['majorVersion'] . '.' . $library['minorVersion'] . '.h5p';
        list($response, $code) = CurlUtil::httpGetRequest($endpoint, [], true, $targetFile);

        if ($code == 200 && file_exists($targetFile)) {
            if ($this->h5pValidator->isValidPackage(true, false)) {
                $this->h5pStorage->savePackage(null, null, true);
            }
        }

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        $libraryKey = H5PCore::libraryToString($library);
        $libraryId = $this->h5pStorage->h5pC->librariesJsonData[$libraryKey]["libraryId"] ?? null;
        if ($libraryId) {
            error_log("endpoint: " . $endpoint . ", librarykey: " . $libraryKey . ", libraryid: " . $libraryId); // log for backtracking purposes

            // Update example and tutorial (if any of them are defined in $library).
            $example = $library['example'] ?? null;
            $tutorial = $library['tutorial'] ?? null;
            $sql = "UPDATE h5p_library
                           SET example = ?s, tutorial = ?s
                         WHERE id = ?d";
            Database::get()->query($sql, $example, $tutorial, $libraryId);
        }

        return $libraryId;
    }

    /**
     * Get the latest version of the H5P content types available in the official repository.
     *
     * @return stdClass The H5P content types object
     */
    public function getLatestContentTypes(): ?stdClass {
        global $webDir;

        $siteUuid = $this->getSiteUuid() ?? md5($webDir);
        $postdata = ['uuid' => $siteUuid];

        // Get the latest content-types json.
        $endpoint = $this->getApiEndpoint();
        list($response, $code, $responseHeaders) = CurlUtil::httpPostRequest($endpoint, $postdata);

        if (!empty($code) && intval($code) == 200) {
            return json_decode($response);
        }

        return null;
    }

    /**
     * Get the site UUID. If site UUID is not defined, try to register the site.
     *
     * @return string The site UUID, null if it is not set.
     */
    public function getSiteUuid(): ?string {
        $siteUuid = get_config('core_h5p_site_uuid');

        if (empty($siteUuid)) {
            $siteUuid = $this->registerSite();
        }

        return $siteUuid;
    }

    /**
     * Get H5P generated site UUID.
     *
     * @return string Returns H5P generated site UUID, null if can't get it.
     */
    public function registerSite(): ?string {
        $endpoint = $this->getApiEndpoint(null, 'site');
        list($siteUuid, $code) = CurlUtil::httpGetRequest($endpoint);

        if ($code == 200) {
            $json = json_decode($siteUuid);
            if (isset($json->uuid)) {
                set_config('core_h5p_site_uuid', $json->uuid);
                return $json->uuid;
            }
        }

        return null;
    }

    /**
     * Get H5P endpoints.
     *
     * If $endpoint = 'content' and $library is null, the returned url is the endpoint of the latest version of the H5P content
     * types; however, if $library is the machine name of a content type, the returned url is the endpoint to download the content type.
     * The SITES endpoint ($endpoint = 'site') may be used to get a site UUID or send site data.
     *
     * @param string|null $library The machineName of the library whose endpoint is requested.
     * @param string $endpoint The endpoint required. Valid values: "site", "content".
     * @return string The endpoint url.
     */
    public function getApiEndpoint(?string $library = null, string $endpoint = 'content'): ?string {
        $h5purl = null;

        if ($endpoint == 'site') {
            $h5purl = H5PHubEndpoints::createURL(H5PHubEndpoints::SITES );
        } else if ($endpoint == 'content') {
            $h5purl = H5PHubEndpoints::createURL(H5PHubEndpoints::CONTENT_TYPES ) . $library;
        }

        return $h5purl;
    }

    /**
     * Checks that the required H5P core API version or higher is installed.
     *
     * @param stdClass $coreapi Object with properties major and minor for the core API version required.
     * @return bool True if the required H5P core API version is installed. False if not.
     */
    public function isRequiredCoreApi(stdClass $coreapi): bool {
        if (isset($coreapi) && !empty($coreapi)) {
            if (($coreapi->major > H5PCore::$coreApi['majorVersion']) ||
                (($coreapi->major == H5PCore::$coreApi['majorVersion']) && ($coreapi->minor > H5PCore::$coreApi['minorVersion']))) {
                return false;
            }
        }
        return true;
    }

}
