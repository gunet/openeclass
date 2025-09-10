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

class SolrUnitIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $unit): array {
        global $urlServer;
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_UNIT . '_' . $unit->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_UNIT . '_' . $unit->id,
            ConstantsUtil::FIELD_PKID => $unit->id,
            ConstantsUtil::FIELD_COURSEID => $unit->course_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_UNIT,
            ConstantsUtil::FIELD_TITLE => $unit->title,
            ConstantsUtil::FIELD_CONTENT => strip_tags($unit->comments),
            ConstantsUtil::FIELD_VISIBLE => $unit->visible,
            ConstantsUtil::FIELD_URL => $urlServer . 'modules/units/index.php?course=' . course_id_to_code($unit->course_id) . '&amp;id=' . $unit->id
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $units = FetcherUtil::fetchUnits($courseId);
        foreach ($units as $unit) {
            $docs[] = $this->makeDoc($unit);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $unit = FetcherUtil::fetchUnit($id);
        if (!empty($unit)) {
            $docs[] = $this->makeDoc($unit);
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_UNIT . '_' . $id
            ]
        ];
    }

}
