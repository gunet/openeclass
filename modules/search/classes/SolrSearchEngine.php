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

class SolrSearchEngine implements SearchEngineInterface {

    public function search(array $params): array {
        // Perform Solr query
        // $solrResults = [];

        $solrUrl = 'http://localhost:8983/solr/lalakoko_index/select';
        $query = [
            'q' => 'title:example',
            'wt' => 'json'
        ];
        $fullUrl = $solrUrl . '?' . http_build_query($query);
        list($response, $code) = CurlUtil::httpGetRequest($fullUrl);
        $resp = json_decode($response, false);
        $hits = $resp->response->docs;

        return array_map(function ($hit) {
            return new SearchResult("hit->pk", $hit->vid, "hit->doctype", "hit->visible", $hit);
        }, $hits);
    }

}
