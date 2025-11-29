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

class SolrForumTopicIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $ftopic): array {
        global $urlServer;
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_FORUMTOPIC . '_' . $ftopic->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_FORUMTOPIC . '_' . $ftopic->id,
            ConstantsUtil::FIELD_PKID => $ftopic->id,
            ConstantsUtil::FIELD_COURSEID => $ftopic->course_id,
            ConstantsUtil::FIELD_FORUMID => $ftopic->forum_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_FORUMTOPIC,
            ConstantsUtil::FIELD_TITLE => $ftopic->title,
            ConstantsUtil::FIELD_URL => $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($ftopic->course_id) . '&amp;forum=' . intval($ftopic->forum_id)
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $ftopics = FetcherUtil::fetchForumTopics($courseId);
        foreach ($ftopics as $ftopic) {
            $docs[] = $this->makeDoc($ftopic);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $ftopic = FetcherUtil::fetchForumTopic($id);
        if (!empty($ftopic)) {
            $docs[] = $this->makeDoc($ftopic);
            error_log('indexing forumtopic: ' . print_r($ftopic, true));
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_FORUMTOPIC . '_' . $id
            ]
        ];
    }

    public function removeByForum(int $forumId): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_DOCTYPE . ":" . ConstantsUtil::DOCTYPE_FORUMTOPIC . ' AND ' . ConstantsUtil::FIELD_FORUMID . ':' . $forumId
            ]
        ];
    }

}
