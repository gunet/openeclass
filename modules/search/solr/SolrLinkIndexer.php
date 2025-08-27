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

require_once 'AbstractSolrIndexer.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/FetcherUtil.php';

class SolrLinkIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $link): array {
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_LINK . '_' . $link->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_LINK . '_' . $link->id,
            ConstantsUtil::FIELD_PKID => $link->id,
            ConstantsUtil::FIELD_COURSEID => $link->course_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_LINK,
            ConstantsUtil::FIELD_TITLE => $link->title,
            ConstantsUtil::FIELD_CONTENT => strip_tags($link->description),
            ConstantsUtil::FIELD_URL => $link->url
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $links = FetcherUtil::fetchLinks($courseId);
        foreach ($links as $link) {
            $docs[] = $this->makeDoc($link);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $link = FetcherUtil::fetchLink($id);
        if (!empty($link)) {
            $docs[] = $this->makeDoc($link);
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_LINK . '_' . $id
            ]
        ];
    }

}
