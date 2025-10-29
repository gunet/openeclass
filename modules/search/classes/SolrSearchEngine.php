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

require_once 'SearchEngineInterface.php';
require_once 'SearchResult.php';
require_once 'modules/admin/extconfig/solrapp.php';
require_once 'modules/search/solr/SolrIndexer.php';
require_once 'modules/search/solr/SolrCourseIndexer.php';

class SolrSearchEngine implements SearchEngineInterface {

    /** @var array<string,string> */
    private array $facetConfig = [
        'doctype' => 'Document Type',
        'courseid' => 'Course',
    ];
    private int $rows = 10;

    public function search(array $params): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        // construct Solr Url for searching
        $solrUrl = $this->constructSolrUrl("select", SolrCourseIndexer::buildSolrQuery($params));

        // Perform Solr query
        list($response, $code) = CurlUtil::httpGetRequest($solrUrl);
        if ($code !== 200) {
            return [];
        }

        // Handle Solr response
        $resp = json_decode($response, false);
        $hits = $resp->response->docs;

        return array_map(function ($hit) {
            return new SearchResult($hit->pk, $hit->pkid, $hit->doctype, $hit->visible, $hit);
        }, $hits);
    }

    public function searchInCourse(array $params): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        // construct Solr Url for searching
        $solrUrl = $this->constructSolrUrl("select", SolrIndexer::buildSolrQuery($params));

        // Perform Solr query
        list($response, $code) = CurlUtil::httpGetRequest($solrUrl);
        if ($code !== 200) {
            return [];
        }

        // Handle Solr response
        $resp = json_decode($response, false);
        $hits = $resp->response->docs;

        return array_map(function ($hit) {
            return new SearchResult($hit->pk, $hit->pkid, $hit->doctype, $hit->visible, $hit);
        }, $hits);
    }

    public function searchInCourse2(string $query, int $page, int $course_id): array {
        $page = max(1, $page);
        $rows = $this->rows;
        $start = ($page - 1) * $rows;
//        $selectedFacets = $this->sanitizeFacetSelection($selectedFacets);

        $params = [
            'q' => $query !== '' ? $query : '*:*',
            'start' => $start,
            'rows' => $rows,
//            'fq' => 'courseid:' . $course_id, // ← filter to only specific course id
            'wt' => 'json',
            'facet' => 'true',
            'facet.limit' => 20,
            'facet.mincount' => 1,
            'facet.field' => array_keys($this->facetConfig),
        ];

        if ($query !== '') {
            $params['defType'] = 'edismax';
            $params['qf'] = 'title^3 content^2 units code prof_names url';
        }

//        $filters = $this->buildFacetFilters($selectedFacets);
//        if (!empty($filters)) {
//            $params['fq'] = $filters;
//        }

        // construct Solr Url for searching
        $solrUrl = $this->constructSolrUrl("select", $params);

        // Perform Solr query
        list($response, $code) = CurlUtil::httpGetRequest($solrUrl);
        if ($code !== 200) {
            return [];
        }

        // Handle Solr response
        $payload = json_decode($response, true);
        error_log(print_r($payload, true));
        $numFound = (int)($payload['response']['numFound'] ?? 0);
        $docs = $payload['response']['docs'] ?? [];
        if (!is_array($docs)) {
            $docs = [];
        }

//        $facets = $this->formatFacets($payload['facet_counts']['facet_fields'] ?? []);

        return [
            'docs' => $docs,
            'numFound' => $numFound,
            'start' => (int)($payload['response']['start'] ?? 0),
            'rows' => $rows,
            'totalPages' => max(1, (int)ceil($numFound / $rows)),
//            'facets' => $facets,
            'facets' => [],
            'facetLabels' => $this->facetConfig,
//            'appliedFacets' => $selectedFacets,
            'appliedFacets' => [],
        ];
    }

    public function index(int $courseId): void {
        if (!get_config('ext_solr_enabled')) {
            return;
        }

        // construct Solr Url for indexing
        $solrUrl = $this->constructSolrUrl("update", []);

        // delete previous course contents and (re)index
        $idx = new SolrIndexer();
        CurlUtil::httpPostJsonRequest($solrUrl, $idx->removeAllByCourse($courseId));
        CurlUtil::httpPostJsonRequest($solrUrl, $idx->storeAllByCourse($courseId));
        // CurlUtil::httpPostJsonRequest($solrUrl, $this->generateSampleDocuments(2, $courseId));
    }

    public function commit(): void {
        if (!get_config('ext_solr_enabled')) {
            return;
        }

        $solrUrl = $this->constructSolrUrl("update", ['commit' => 'true']);
        CurlUtil::httpPostJsonRequest($solrUrl, []);
    }

    public function deleteAll(): void {
        if (!get_config('ext_solr_enabled')) {
            return;
        }

        // construct Solr Url for indexing
        $solrUrl = $this->constructSolrUrl("update", ['commit' => 'true']);

        // delete all contents
        $idx = new SolrIndexer();
        CurlUtil::httpPostJsonRequest($solrUrl, $idx->removeAll());
    }

    public function indexResource(string $requestType, string $resourceType, int $resourceId): void {
        if (!get_config('ext_solr_enabled')) {
            return;
        }

        // construct Solr Url for indexing
        $query = [
            'commit' => 'true'
        ];
        $solrUrl = $this->constructSolrUrl("update", $query);

        // process index request
        $idx = new SolrIndexer();
        $queryPostData = $idx->indexResource($requestType, $resourceType, $resourceId);
        if (!empty($queryPostData)) {
            CurlUtil::httpPostJsonRequest($solrUrl, $queryPostData);
        }
    }

    public function coreStatus(): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        list($core, $solrUrl) = $this->constructSolrCoreStatusUrl();
        list($response, $code) = CurlUtil::httpGetRequest($solrUrl);
        if ($code !== 200) {
            return [];
        }

        $resp = json_decode($response, true);
        return $resp['status'][$core]['index'] ?? [];
    }

    private function constructSolrUrl(string $action, array $params): string {
        $solrUrl = get_config('ext_solr_url', SolrApp::SOLRDEFAULTURL);
        if ($solrUrl[strlen($solrUrl) - 1] != '/') {
            $solrUrl .= '/';
        }
        $solrUrl .= $action;
        return $solrUrl . '?' . http_build_query($params);
    }

    private function constructSolrCoreStatusUrl(): array {
        $solrUrl = get_config('ext_solr_url', SolrApp::SOLRDEFAULTURL);
        if ($solrUrl[strlen($solrUrl) - 1] != '/') {
            $solrUrl .= '/';
        }
        $parts = explode('/', trim($solrUrl, '/'));
        $core = array_pop($parts);
        $baseSolrUrl = implode('/', $parts) . '/';
        $params = [
            'action' => 'STATUS',
            'core' => $core
        ];
        return [$core, $baseSolrUrl . 'admin/cores' . '?' . http_build_query($params)];
    }

//    private function generateSampleDocuments(int $count = 10, int $courseId): array {
//        global $urlServer;
//        $docs = [];
//
//        for ($i = 0; $i < $count; $i++) {
//            $docs[] = [
//                "id" => uniqid("doc_", true),
//                "pk" => "course_$i",
//                "pkid" => $i,
//                "courseid" => $courseId,
//                "doctype" => "course",
//                "code" => "TMA$i",
//                "title" => "course title $i",
//                "keywords" => "course keywords $i",
//                "visible" => 1,
//                "prof_names" => "course prof_names $i",
//                "public_code" => "TMA$i",
//                "units" => "course units $i",
//                "created" => gmdate("Y-m-d\TH:i:s\Z"),
//                "url" => $urlServer . 'courses/' . "TMA$i"
//            ];
//        }
//
//        return $docs;
//    }

}
