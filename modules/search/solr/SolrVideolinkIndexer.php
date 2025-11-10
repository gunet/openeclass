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

class SolrVideolinkIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $vlink): array {
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $vlink->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $vlink->id,
            ConstantsUtil::FIELD_PKID => $vlink->id,
            ConstantsUtil::FIELD_COURSEID => $vlink->course_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_VIDEOLINK,
            ConstantsUtil::FIELD_TITLE => $vlink->title,
            ConstantsUtil::FIELD_CONTENT => strip_tags($vlink->description),
            ConstantsUtil::FIELD_URL => $vlink->url
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $vlinks = FetcherUtil::fetchVideoLinks($courseId);
        foreach ($vlinks as $vlink) {
            $docs[] = $this->makeDoc($vlink);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $vlink = FetcherUtil::fetchVideoLink($id);
        if (!empty($vlink)) {
            $docs[] = $this->makeDoc($vlink);
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $id
            ]
        ];
    }

}
