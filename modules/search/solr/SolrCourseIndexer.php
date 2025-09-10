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

class SolrCourseIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $course = FetcherUtil::fetchCourse($courseId);
        $docs = [];
        $docs[] = [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_COURSE . '_' . $course->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_COURSE . '_' . $course->id,
            ConstantsUtil::FIELD_PKID => $course->id,
            ConstantsUtil::FIELD_COURSEID => $course->id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_COURSE,
            ConstantsUtil::FIELD_CODE => $course->code,
            ConstantsUtil::FIELD_TITLE => $course->title,
            ConstantsUtil::FIELD_KEYWORDS => $course->keywords,
            ConstantsUtil::FIELD_VISIBLE => $course->visible,
            ConstantsUtil::FIELD_PROF_NAMES => $course->prof_names,
            ConstantsUtil::FIELD_PUBLIC_CODE => $course->public_code,
            ConstantsUtil::FIELD_UNITS => strip_tags($course->units),
            ConstantsUtil::FIELD_CREATED => $course->created,
            ConstantsUtil::FIELD_URL => $urlServer . 'courses/' . $course->code
        ];
        return $docs;
    }

    public function store(int $id): array {
        return $this->storeByCourse($id);
    }

}
