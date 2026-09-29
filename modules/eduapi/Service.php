<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

namespace modules\eduapi;

use Database;
use DBResult;
use Exception;
use ExtAppManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class Service {
    const BASE_PATH = '/ims/eduapi/base/v1p0/';
    const SCOPE = 'http://purl.1edtech.org/spec/eduapi/v1p0/scope/core.readonly http://purl.1edtech.org/spec/eduapi/v1p0/scope/core.readonly.privacy';
    const PAGE_SIZE = 200;

    private $client;
    private $base_uri;
    private $token_url;
    private $client_id;
    private $client_secret;
    private $accessToken = null;
    private $accessTokenExpires = 0;
    private $organizationCache = [];

    public function __construct() {
        $this->client = new Client();
        $this->base_uri = get_config('eduapi_base_url');
        $this->token_url = get_config('eduapi_token_url');
        $this->client_id = get_config('eduapi_client_id');
        $this->client_secret = get_config('eduapi_client_secret');
    }

    /**
     * Check if Edu-API app is enabled and properly configured
     * @throws Exception otherwise
     */
    public function checkAppEnabled(): void {
        global $langEduApiNotEnabled, $langEduApiNotConfigured;

        require_once 'modules/admin/extconfig/externals.php';
        $app = ExtAppManager::getApp('eduapi');

        if (!$app || !$app->isEnabled()) {
            throw new Exception($langEduApiNotEnabled);
        }

        if (empty($this->base_uri) || empty($this->token_url) || empty($this->client_id) || empty($this->client_secret)) {
            throw new Exception($langEduApiNotConfigured);
        }
    }

    /**
     * Select School node from hierarchy
     * If user is department admin, select their first admin node, else fallback to first top-level node
     */
    public function getSchoolNode() {
        global $is_admin, $uid;

        if (!$is_admin) {
            // If user is not global admin, assume department manager and get their managed department
            $topNode = Database::get()->querySingle("SELECT id, code, lft, rgt FROM hierarchy
                WHERE id = (SELECT department_id FROM admin
                WHERE user_id = ?d LIMIT 1)", $uid);
        } else {
            // Get the top hierarchy node
            $topNode = Database::get()->querySingle("SELECT id, code, lft, rgt FROM hierarchy WHERE lft = 1");
        }

        return $topNode;
    }

    /**
     * Get and cache an OAuth2 access token via client_credentials
     * @throws Exception
     */
    public function getAccessToken($forceRefresh = false) {
        if (!$forceRefresh && $this->accessToken !== null && time() < $this->accessTokenExpires) {
            return $this->accessToken;
        }

        try {
            $response = $this->client->request('POST', $this->token_url, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'scope' => self::SCOPE,
                ],
                'timeout' => 30,
                'connect_timeout' => 10
            ]);

            $data = json_decode($response->getBody()->getContents());

            if (!$data || empty($data->access_token)) {
                throw new Exception('No access_token in OAuth2 response');
            }

            $this->accessToken = $data->access_token;
            // Refresh one minute before actual expiry
            $expiresIn = isset($data->expires_in) ? (int)$data->expires_in : 300;
            $this->accessTokenExpires = time() + max($expiresIn - 60, 30);

            return $this->accessToken;
        } catch (RequestException $e) {
            error_log('Edu-API OAuth2 Error: ' . $e->getMessage());
            throw new Exception('Failed to obtain Edu-API access token: ' . $e->getMessage());
        }
    }

    /**
     * Authenticated GET against the Edu-API, retries exactly once with a fresh token on 401
     * @return object|array decoded JSON body
     * @throws Exception
     */
    public function get($path, $query = []): object|array {
        $endpoint = rtrim($this->base_uri, '/') . self::BASE_PATH . ltrim($path, '/');

        $attempt = 0;
        while (true) {
            $attempt++;
            $token = $this->getAccessToken($attempt > 1);

            try {
                $response = $this->client->request('GET', $endpoint, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                    ],
                    'query' => $query,
                    'timeout' => 30,
                    'connect_timeout' => 10
                ]);

                $data = json_decode($response->getBody()->getContents());

                if ($data === null) {
                    throw new Exception("Invalid JSON response from Edu-API ($path)");
                }

                return $data;
            } catch (RequestException $e) {
                $status = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
                if ($status === 401 && $attempt === 1) {
                    continue; // token may have expired mid-run - refresh and retry once
                }
                error_log('Edu-API Error: ' . $e->getMessage());
                throw new Exception('Failed to connect to Edu-API: ' . $e->getMessage());
            }
        }
    }

    /**
     * Fetch a full collection, following limit/offset pagination.
     *
     * @param string $path relative path under the base
     * @param string $key collection key expected in the response wrapper (e.g. 'courseOfferings')
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getCollection(string $path, string $key, array $query = []): array {
        $items = [];
        $offset = 0;
        $firstIdSeen = null;

        while (true) {
            $page = $this->get($path, array_merge($query, [
                'limit' => self::PAGE_SIZE,
                'offset' => $offset,
            ]));

            $pageItems = self::extractCollection($page, $key);

            if (empty($pageItems)) {
                break;
            }

            // Defensive break in case the server ignores paging parameters
            $firstId = $pageItems[0]->sourcedId ?? null;
            if ($offset > 0 && $firstId !== null && $firstId === $firstIdSeen) {
                break;
            }
            if ($offset === 0) {
                $firstIdSeen = $firstId;
            }

            $items = array_merge($items, $pageItems);

            if (count($pageItems) < self::PAGE_SIZE) {
                break;
            }
            $offset += self::PAGE_SIZE;
        }

        return $items;
    }

    /**
     * Unwrap a collection response: either a bare array, the expected wrapper
     * key, or the first array-valued property of the wrapper object.
     */
    private static function extractCollection($data, $key) {
        if (is_array($data)) {
            return $data;
        }
        if (is_object($data)) {
            if (isset($data->$key) && is_array($data->$key)) {
                return $data->$key;
            }
            foreach (get_object_vars($data) as $value) {
                if (is_array($value)) {
                    return $value;
                }
            }
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public function getAcademicSessions(): array {
        $this->checkAppEnabled();
        return $this->getCollection('academicSessions', 'academicSessions', ['sort' => 'endDate']);
    }

    /**
     * @throws Exception
     */
    public function getCourseOfferings($sessionId): array {
        $this->checkAppEnabled();
        return $this->getCollection('academicSessions/' . rawurlencode($sessionId) . '/courseOfferings', 'courseOfferings');
    }

    /**
     * @throws Exception
     */
    public function getEnrollments($offeringId): array {
        $this->checkAppEnabled();
        return $this->getCollection('courseOfferings/' . rawurlencode($offeringId) . '/enrollments', 'enrollments');
    }

    /**
     * @throws Exception
     */
    public function getStudents($offeringId): array {
        $this->checkAppEnabled();
        return $this->getCollection('courseOfferings/' . rawurlencode($offeringId) . '/students', 'people');
    }

    /**
     * @throws Exception
     */
    public function getStaff($offeringId): array {
        $this->checkAppEnabled();
        return $this->getCollection('courseOfferings/' . rawurlencode($offeringId) . '/staff', 'people');
    }

    /**
     * Fetch one organization by sourcedId (memorized per request)
     * @param $organizationId
     * @return object|null
     * @throws Exception
     */
    public function getOrganization($organizationId): ?object {
        if (empty($organizationId)) {
            return null;
        }
        if (array_key_exists($organizationId, $this->organizationCache)) {
            return $this->organizationCache[$organizationId];
        }

        $this->checkAppEnabled();
        $organizations = $this->getCollection('organizations', 'organizations', [
            'filter' => "sourcedId='" . $organizationId . "'",
        ]);

        return $this->organizationCache[$organizationId] = ($organizations[0] ?? null);
    }

    /**
     * Walk the organization parent chain upwards and return it in parent -> child -> direct order.
     * An ancestor whose fetch fails ends the walk there.
     *
     * @return object[] organization objects, root first
     */
    public function getOrganizationChain($organizationId): array {
        $chain = [];
        $seen = [];
        $currentId = trim((string)$organizationId);

        while ($currentId !== '' && !isset($seen[$currentId]) && count($chain) < 10) {
            $seen[$currentId] = true;
            try {
                $organization = $this->getOrganization($currentId);
            } catch (Exception $e) {
                error_log('Edu-API: organization lookup failed for ' . $currentId . ': ' . $e->getMessage());
                break;
            }
            if (!$organization) {
                break;
            }
            array_unshift($chain, $organization);
            $currentId = self::parentSourcedId($organization);
        }

        return $chain;
    }

    /**
     * Extract the parent sourcedId of a record (organization or academic
     * session), the parent reference may come as a plain string, an object,
     * an array of either, or null.
     */
    public static function parentSourcedId($record): string {
        $parent = $record->parent ?? null;
        if (is_array($parent)) {
            $parent = $parent[0] ?? null;
        }
        if (is_object($parent)) {
            $parent = $parent->sourcedId ?? ($parent->identifier ?? '');
        }

        return trim((string)$parent);
    }

    /**
     * Display name for an organization:
     * multilingual name list or primaryCode identifier or ''
     */
    public static function organizationName($organization, $lang) {
        if (!$organization) {
            return '';
        }
        if (!empty($organization->name) && is_array($organization->name)) {
            $name = self::pickTitle($organization->name, $lang);
            if ($name !== '') {
                return $name;
            }
        }
        return (string)($organization->primaryCode->identifier ?? '');
    }

    /**
     * Pick the best value from a multilingual title list:
     * current language or english or first entry or ''
     * @param array|null $titleList list of {recordLanguage, value} objects
     */
    public static function pickTitle(?array $titleList, $lang) {
        if (empty($titleList) || !is_array($titleList)) {
            return '';
        }

        foreach ([$lang, 'en'] as $wanted) {
            foreach ($titleList as $entry) {
                $entryLang = strtolower($entry->recordLanguage ?? '');
                if ($entryLang === $wanted || strpos($entryLang, $wanted . '-') === 0) {
                    return $entry->value ?? '';
                }
            }
        }

        return $titleList[0]->value ?? '';
    }

    /**
     * Derive the eClass username for an Edu-API person:
     * otherIdentifiers userName or primaryEmail or sourcedId or null
     */
    public static function personUserName($person) {
        if (!empty($person->otherIdentifiers) && is_array($person->otherIdentifiers)) {
            foreach ($person->otherIdentifiers as $identifier) {
                if (strcasecmp($identifier->identifierType ?? '', 'userName') === 0
                        && !empty($identifier->identifier)) {
                    return $identifier->identifier;
                }
            }
        }

        $email = self::personEmail($person);
        if (!empty($email)) {
            return $email;
        }

        if (!empty($person->sourcedId)) {
            return $person->sourcedId;
        }

        return null;
    }

    /**
     * Extract the primary email address; primaryEmail is an {emailType, email} object
     */
    public static function personEmail($person): string {
        if (empty($person->primaryEmail)) {
            return '';
        }
        if (is_object($person->primaryEmail)) {
            return $person->primaryEmail->email ?? '';
        }
        return (string)$person->primaryEmail;
    }

    /**
     * Treat any non-student enrollment role as a teacher role
     */
    public static function isTeacherRole($role): bool {
        return strcasecmp(trim((string)$role), 'student') !== 0;
    }
}
