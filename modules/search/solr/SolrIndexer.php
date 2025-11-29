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

require_once 'SolrCourseIndexer.php';
require_once 'SolrAnnouncementIndexer.php';
require_once 'SolrAgendaIndexer.php';
require_once 'SolrLinkIndexer.php';
require_once 'SolrVideoIndexer.php';
require_once 'SolrVideolinkIndexer.php';
require_once 'SolrExerciseIndexer.php';
require_once 'SolrForumIndexer.php';
require_once 'SolrForumTopicIndexer.php';
require_once 'SolrForumPostIndexer.php';
require_once 'SolrDocumentIndexer.php';
require_once 'SolrUnitIndexer.php';
require_once 'SolrUnitResourceIndexer.php';
require_once 'SolrNoteIndexer.php';

class SolrIndexer {

    /**
     * Solr Indexer Constructor.
     */
    public function __construct() {
        if (!get_config('ext_solr_enabled')) {
            return;
        }
    }

    /**
     * Construct the payload to store all index contents related to a Course.
     */
    public function storeAllByCourse(int $courseId): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        $docs = [];

        $cidx = new SolrCourseIndexer();
        $docs = array_merge($docs, $cidx->storeByCourse($courseId));

        $aidx = new SolrAnnouncementIndexer();
        $docs = array_merge($docs, $aidx->storeByCourse($courseId));

        $agdx = new SolrAgendaIndexer();
        $docs = array_merge($docs, $agdx->storeByCourse($courseId));

        $lidx = new SolrLinkIndexer();
        $docs = array_merge($docs, $lidx->storeByCourse($courseId));

        $vdx = new SolrVideoIndexer();
        $docs = array_merge($docs, $vdx->storeByCourse($courseId));

        $vldx = new SolrVideolinkIndexer();
        $docs = array_merge($docs, $vldx->storeByCourse($courseId));

        $eidx = new SolrExerciseIndexer();
        $docs = array_merge($docs, $eidx->storeByCourse($courseId));

        $fidx = new SolrForumIndexer();
        $docs = array_merge($docs, $fidx->storeByCourse($courseId));

        $ftdx = new SolrForumTopicIndexer();
        $docs = array_merge($docs, $ftdx->storeByCourse($courseId));

        $fpdx = new SolrForumPostIndexer();
        $docs = array_merge($docs, $fpdx->storeByCourse($courseId));

        $didx = new SolrDocumentIndexer();
        $docs = array_merge($docs, $didx->storeByCourse($courseId));

        $uidx = new SolrUnitIndexer();
        $docs = array_merge($docs, $uidx->storeByCourse($courseId));

        $urdx = new SolrUnitResourceIndexer();
        $docs = array_merge($docs, $urdx->storeByCourse($courseId));

        $ndx = new SolrNoteIndexer();
        $docs = array_merge($docs, $ndx->storeByCourse($courseId));

        return $docs;
    }

    /**
     * Construct the delete payload for all index contents related to a Course.
     */
    public function removeAllByCourse(int $courseId): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        return [
            "delete" => [
                "query" => ConstantsUtil::FIELD_COURSEID . ":" . $courseId
            ]
        ];
    }

    public function removeAll(): array {
        if (!get_config('ext_solr_enabled')) {
            return [];
        }

        return [
            "delete" => [
                "query" => "*:*"
            ]
        ];
    }

    public function indexResource(string $requestType, string $resourceType, int $resourceId): ?array {
        $varidx = null;
        switch ($resourceType) {
            case ConstantsUtil::RESOURCE_AGENDA:
                $varidx = new SolrAgendaIndexer();
                break;
            case ConstantsUtil::RESOURCE_ANNOUNCEMENT:
                $varidx = new SolrAnnouncementIndexer();
                break;
            case ConstantsUtil::RESOURCE_COURSE:
                $varidx = new SolrCourseIndexer();
                break;
            case ConstantsUtil::RESOURCE_DOCUMENT:
                $varidx = new SolrDocumentIndexer();
                break;
            case ConstantsUtil::RESOURCE_EXERCISE:
                $varidx = new SolrExerciseIndexer();
                break;
            case ConstantsUtil::RESOURCE_FORUM:
                $varidx = new SolrForumIndexer();
                break;
            case ConstantsUtil::RESOURCE_FORUMPOST:
                $varidx = new SolrForumPostIndexer();
                break;
            case ConstantsUtil::RESOURCE_FORUMTOPIC:
                $varidx = new SolrForumTopicIndexer();
                break;
            case ConstantsUtil::RESOURCE_IDX:
                $varidx = $this;
                break;
            case ConstantsUtil::RESOURCE_LINK:
                $varidx = new SolrLinkIndexer();
                break;
            case ConstantsUtil::RESOURCE_NOTE:
                $varidx = new SolrNoteIndexer();
                break;
            case ConstantsUtil::RESOURCE_UNIT:
                $varidx = new SolrUnitIndexer();
                break;
            case ConstantsUtil::RESOURCE_UNITRESOURCE:
                $varidx = new SolrUnitResourceIndexer();
                break;
            case ConstantsUtil::RESOURCE_VIDEO:
                $varidx = new SolrVideoIndexer();
                break;
            case ConstantsUtil::RESOURCE_VIDEOLINK:
                $varidx = new SolrVideolinkIndexer();
                break;
            default:
                break;
        }
        return $this->callVariableIndexer($varidx, $requestType, $resourceId);
    }

    private function callVariableIndexer(AbstractSolrIndexer|SolrIndexer|null $idxObj, string $method, int $arg): ?array {
        $queryPostData = null;
        if ($idxObj !== null && is_callable(array($idxObj, $method))) {
            $queryPostData = $idxObj->$method($arg);
        }
        return $queryPostData;
    }

    public static function buildSolrQuery(array $params): array {
        $q = [];
        if (!empty($params['search_terms']) && !empty($params['course_id'])) {
            $terms = trim($params['search_terms']);
            if ($terms === '') {
                // empty behavior
                $terms = '*:*';
            }

            // fields & boosts for the simple search
            $qf = [
                'title^4',
                'content^3',
                'filename^2',
                'comment^1',
                'creator^1',
                'subject^1',
                'author^1',
            ];

            // edismax Solr search (parse the text)
            $q = [
                'defType' => 'edismax',
                'q'       => $terms,
                'qf'      => implode(' ', $qf),
                'q.op'    => 'OR',                               // ← the default operator, we want anything that matches, at least one term
                'fq'      => 'courseid:' . $params['course_id'], // ← filter to only specific course id
                //'mm'      => '0%',                             // ← for at least any match, this defaults to 0%
                //'rows'    => 10,                               // ← row limiter
                'wt'      => 'json',
            ];
        }
        return $q;
    }

}