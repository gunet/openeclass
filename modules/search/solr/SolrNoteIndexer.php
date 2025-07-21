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

class SolrNoteIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $docs = [];
        $notes = FetcherUtil::fetchNotes($courseId);
        foreach ($notes as $note) {
            $doc = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_NOTE . '_' . $note->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_NOTE . '_' . $note->id,
                ConstantsUtil::FIELD_PKID => $note->id,
                ConstantsUtil::FIELD_USERID => $note->user_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_NOTE,
                ConstantsUtil::FIELD_TITLE => $note->title,
                ConstantsUtil::FIELD_CONTENT => strip_tags($note->content),
                ConstantsUtil::FIELD_URL => $urlServer . 'modules/notes/index.php?an_id=' . $note->id
            ];
            if (isset($note->course_id)) {
                $doc[ConstantsUtil::FIELD_COURSEID] = $note->course_id;
            }
            $docs[] = $doc;
        }
        return $docs;
    }

}
