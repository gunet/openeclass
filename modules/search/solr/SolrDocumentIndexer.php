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

class SolrDocumentIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $docu): array {
        global $urlServer;
        $urlAction = ($docu->format == '.dir') ? 'openDir' : 'download';
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_DOCUMENT . '_' . $docu->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_DOCUMENT . '_' . $docu->id,
            ConstantsUtil::FIELD_PKID => $docu->id,
            ConstantsUtil::FIELD_COURSEID => $docu->course_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_DOCUMENT,
            ConstantsUtil::FIELD_TITLE => (empty($docu->title)) ? $docu->filename : $docu->title,
            ConstantsUtil::FIELD_CONTENT => strip_tags($docu->description),
            ConstantsUtil::FIELD_FILENAME => $docu->filename,
            ConstantsUtil::FIELD_COMMENT => $docu->comment,
            ConstantsUtil::FIELD_CREATOR => $docu->creator,
            ConstantsUtil::FIELD_SUBJECT => $docu->subject,
            ConstantsUtil::FIELD_AUTHOR => $docu->author,
            ConstantsUtil::FIELD_VISIBLE => $docu->visible,
            ConstantsUtil::FIELD_PUBLIC => $docu->public,
            ConstantsUtil::FIELD_URL => $urlServer
                . 'modules/document/index.php?course=' . course_id_to_code($docu->course_id)
                . '&amp;' . $urlAction . '=' . $docu->path
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $docus = FetcherUtil::fetchDocuments($courseId);
        foreach ($docus as $docu) {
            $docs[] = $this->makeDoc($docu);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $docu = FetcherUtil::fetchDocument($id);
        if (!empty($docu)) {
            $docs[] = $this->makeDoc($docu);
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_DOCUMENT . '_' . $id
            ]
        ];
    }

}
