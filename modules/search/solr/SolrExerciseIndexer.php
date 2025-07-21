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

class SolrExerciseIndexer extends AbstractSolrIndexer {

    public function storeByCourse(int $courseId): array {
        global $urlServer;
        $docs = [];
        $exercises = FetcherUtil::fetchExercises($courseId);
        foreach ($exercises as $exercise) {
            $docs[] = [
                ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_EXERCISE . '_' . $exercise->id,
                ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_EXERCISE . '_' . $exercise->id,
                ConstantsUtil::FIELD_PKID => $exercise->id,
                ConstantsUtil::FIELD_COURSEID => $exercise->course_id,
                ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_EXERCISE,
                ConstantsUtil::FIELD_TITLE => $exercise->title,
                ConstantsUtil::FIELD_CONTENT => strip_tags($exercise->description),
                ConstantsUtil::FIELD_VISIBLE => $exercise->active,
                ConstantsUtil::FIELD_URL => $urlServer . 'modules/exercise/exercise_submit.php?course=' . course_id_to_code($exercise->course_id) . '&amp;exerciseId=' . $exercise->id
            ];
        }
        return $docs;
    }

}
