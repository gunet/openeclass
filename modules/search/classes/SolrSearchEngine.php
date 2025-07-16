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

class SolrSearchEngine implements SearchEngineInterface {

    public function search(array $params): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        // construct Solr Url for searching
        $query = [
            'q' => 'title:example',
            'wt' => 'json'
        ];
        $solrUrl = $this->constructSolrUrl("select", $query);

        // Perform Solr query
        list($response, $code) = CurlUtil::httpGetRequest($solrUrl);
        if ($code !== 200) {
            return [];
        }

        // Handle Solr response
        $resp = json_decode($response, false);
        $hits = $resp->response->docs;

        return array_map(function ($hit) {
            return new SearchResult("hit->pk", $hit->vid, "hit->doctype", "hit->visible", $hit);
        }, $hits);
    }

    public function index(int $courseId): void {
        if (!get_config('ext_solr_enabled')) {
            return;
        }

        // construct Solr Url for indexing
        $query = [
            'commit' => 'true'
        ];
        $solrUrl = $this->constructSolrUrl("update", $query);

        /*
         *
         * #!/bin/bash
            curl http://localhost:8983/solr/eclass_index/update?commit=true \
              -H 'Content-Type: application/json' \
              --data-binary @sample_docs.json
         *
         */
    }

    private function constructSolrUrl(string $action, array $params): string {
        $solrUrl = get_config('ext_solr_url', SolrApp::SOLRDEFAULTURL);
        if ($solrUrl[strlen($solrUrl) - 1] != '/') {
            $solrUrl .= '/';
        }
        $solrUrl .= $action;
        return $solrUrl . '?' . http_build_query($params);
    }

}
