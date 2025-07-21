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

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $vlinks = FetcherUtil::fetchVideoLinks($courseId);
        foreach ($vlinks as $vlink) {
            $docs[] = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $video->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $video->id,
                ConstantsUtil::FIELD_PKID => $video->id,
                ConstantsUtil::FIELD_COURSEID => $video->course_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_VIDEOLINK,
                ConstantsUtil::FIELD_TITLE => $video->title,
                ConstantsUtil::FIELD_CONTENT => strip_tags($video->description),
                ConstantsUtil::FIELD_URL => $vlink->url
            ];
        }
        return $docs;
    }

}
