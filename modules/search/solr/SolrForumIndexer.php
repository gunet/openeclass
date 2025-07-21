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

class SolrForumIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $docs = [];
        $forums = FetcherUtil::fetchForums($courseId);
        foreach ($forums as $forum) {
            $docs[] = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_FORUM . '_' . $forum->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_FORUM . '_' . $forum->id,
                ConstantsUtil::FIELD_PKID => $forum->id,
                ConstantsUtil::FIELD_COURSEID => $forum->course_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_FORUM,
                ConstantsUtil::FIELD_TITLE => $forum->name,
                ConstantsUtil::FIELD_CONTENT => strip_tags($forum->desc),
                ConstantsUtil::FIELD_URL => $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($forum->course_id) . '&amp;forum=' . $forum->id
            ];
        }
        return $docs;
    }

}
