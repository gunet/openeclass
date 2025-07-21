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

class SolrUnitResourceIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $docs = [];
        $ureses = FetcherUtil::fetchUnitResources($courseId);
        foreach ($ureses as $ures) {
            $docs[] = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_UNITRESOURCE . '_' . $ures->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_UNITRESOURCE . '_' . $ures->id,
                ConstantsUtil::FIELD_PKID => $ures->id,
                ConstantsUtil::FIELD_COURSEID => $ures->course_id,
                ConstantsUtil::FIELD_UNITID => $ures->unit_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_UNITRESOURCE,
                ConstantsUtil::FIELD_TITLE => $ures->title,
                ConstantsUtil::FIELD_CONTENT => strip_tags($ures->comments),
                ConstantsUtil::FIELD_VISIBLE => $ures->visible,
                ConstantsUtil::FIELD_URL => $urlServer
                    . 'modules/units/index.php?course=' . course_id_to_code($ures->course_id)
                    . '&amp;id=' . $ures->unit_id
            ];
        }
        return $docs;
    }

}
