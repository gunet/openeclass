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

class SolrAgendaIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $docs = [];
        $agendas = FetcherUtil::fetchAgendas($courseId);
        foreach ($agendas as $agenda) {
            $docs[] = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_AGENDA . '_' . $agenda->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_AGENDA . '_' . $agenda->id,
                ConstantsUtil::FIELD_PKID => $agenda->id,
                ConstantsUtil::FIELD_COURSEID => $agenda->course_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_AGENDA,
                ConstantsUtil::FIELD_TITLE => $agenda->title,
                ConstantsUtil::FIELD_CONTENT => strip_tags($agenda->content),
                ConstantsUtil::FIELD_VISIBLE => $agenda->visible,
                ConstantsUtil::FIELD_URL => $urlServer . 'modules/agenda/index.php?course=' . course_id_to_code($agenda->course_id)
            ];
        }
        return $docs;
    }

}
