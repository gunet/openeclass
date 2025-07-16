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
require_once 'modules/search/lucene/indexer.class.php';
require_once 'modules/search/lucene/courseindexer.class.php';

class LuceneSearchEngine implements SearchEngineInterface {

    public function search(array $params): array {
        $idx = new Indexer();
        if (!$idx->getIndex()) {
            return [];
        }

        $hits = $idx->multiSearchRaw(CourseIndexer::buildQueries($params));
        // return $hits;

        return array_map(function ($hit) {
            return new SearchResult($hit->pk, $hit->pkid, $hit->doctype, $hit->visible, $hit);
        }, $hits);
    }

    public function index(int $courseId): void {
        $idx = new Indexer();
        if (!$idx->getIndex()) {
            return;
        }
        // re-index
        $idx->removeAllByCourse($courseId);
        $idx->storeAllByCourse($courseId);
        $idx->getIndex()->optimize();
    }

}
