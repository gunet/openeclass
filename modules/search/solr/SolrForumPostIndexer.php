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

class SolrForumPostIndexer extends AbstractSolrIndexer {

    private function makeDoc(object $fpost): array {
        global $urlServer;
        return [
            ConstantsUtil::FIELD_ID => 'doc_' . ConstantsUtil::DOCTYPE_FORUMPOST . '_' . $fpost->id,
            ConstantsUtil::FIELD_PK => ConstantsUtil::DOCTYPE_FORUMPOST . '_' . $fpost->id,
            ConstantsUtil::FIELD_PKID => $fpost->id,
            ConstantsUtil::FIELD_COURSEID => $fpost->course_id,
            ConstantsUtil::FIELD_TOPICID => $fpost->topic_id,
            ConstantsUtil::FIELD_DOCTYPE => ConstantsUtil::DOCTYPE_FORUMPOST,
            ConstantsUtil::FIELD_TITLE => $fpost->title,
            ConstantsUtil::FIELD_CONTENT => strip_tags($fpost->post_text),
            ConstantsUtil::FIELD_URL => $urlServer . 'modules/forum/viewtopic.php?course=' . course_id_to_code($fpost->course_id)
                . '&amp;topic=' . intval($fpost->topic_id)
                . '&amp;forum=' . intval($fpost->forum_id)
        ];
    }

    public function storeByCourse(int $courseId): array {
        $docs = [];
        $fposts = FetcherUtil::fetchForumPosts($courseId);
        foreach ($fposts as $fpost) {
            $docs[] = $this->makeDoc($fpost);
        }
        return $docs;
    }

    public function store(int $id): array {
        $docs = [];
        $fpost = FetcherUtil::fetchForumPost($id);
        if (!empty($fpost)) {
            $docs[] = $this->makeDoc($fpost);
        }
        return $docs;
    }

    public function remove(int $id): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_ID . ":" . 'doc_' . ConstantsUtil::DOCTYPE_FORUMPOST . '_' . $id
            ]
        ];
    }

    public function removeByTopic(int $topicId): array {
        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_DOCTYPE . ":" . ConstantsUtil::DOCTYPE_FORUMPOST . ' AND ' . ConstantsUtil::FIELD_TOPICID . ':' . $topicId
            ]
        ];
    }

}
