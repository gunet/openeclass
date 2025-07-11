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
        $solrUrl = get_config('ext_solr_url', SolrApp::SOLRDEFAULTURL);
        if ($solrUrl[strlen($solrUrl) - 1] != '/') {
            $solrUrl .= '/';
        }
        $solrUrl .= "select";

        // search parameters
        $query = [
            'q' => 'title:example',
            'wt' => 'json'
        ];
        $fullUrl = $solrUrl . '?' . http_build_query($query);

        // Perform Solr query
        list($response, $code) = CurlUtil::httpGetRequest($fullUrl);
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

}
