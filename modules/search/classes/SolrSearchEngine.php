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

        CurlUtil::httpPostJsonRequest($solrUrl, $this->generateSampleDocuments(2));
    }

    private function constructSolrUrl(string $action, array $params): string {
        $solrUrl = get_config('ext_solr_url', SolrApp::SOLRDEFAULTURL);
        if ($solrUrl[strlen($solrUrl) - 1] != '/') {
            $solrUrl .= '/';
        }
        $solrUrl .= $action;
        return $solrUrl . '?' . http_build_query($params);
    }

    private function generateSampleDocuments($count = 10) {
        $docs = [];

        for ($i = 0; $i < $count; $i++) {
            $docs[] = [
                "id" => uniqid("doc_", true),
                "vid" => $i,
                "object_type" => "book",
                "opac1" => "OPAC_$i",
                "label" => "Sample Label $i",
                "record_type" => "typeA",
                "form_type" => "printed",
                "label_ol" => "Label OL $i",
                "title" => "Example Title $i",
                "sort_label" => "Label $i",
                "is_subject" => false,
                "pubdate" => gmdate("Y-m-d\TH:i:s\Z"),
                "create_dt" => gmdate("Y-m-d\TH:i:s\Z"),
                "num_of_books" => rand(0, 10),
                "tree" => ["tree_" . uniqid()],
                "collection" => ["col_" . uniqid()],
                "item_type" => ["monograph"],
                "author" => ["Author $i"],
                "subjects" => ["Subject $i"],
                "title_lang" => ["en"],
                "manif_type" => ["paper"],
                "organization_category" => ["archive"],
                "contributors" => ["Contributor $i"],
                "w_languages" => ["en"],
                "keywords" => ["keyword$i"],
                "digital_object" => ["http://example.org/object/$i"],
                "ocr_text" => "OCR content for sample $i"
            ];
        }

        return $docs;
    }

}
