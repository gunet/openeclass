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

    public static function buildSolrQuery(array $params): array {
        if (!empty($params['search_terms'])) {
            // ------- SIMPLE SEARCH (edismax over multiple fields) -------
            $terms = trim($params['search_terms']);
            if ($terms === '') {
                // empty behavior
                $terms = '*:*';
            }

            // fields & boosts for the simple search
            $qf = [
                'code^4',
                'public_code^4',
                'title^3',
                'prof_names^2',
                'units^1',
            ];

            // edismax Solr search (parse the text)
            $q = [
                'defType' => 'edismax',
                'q'       => $terms,
                'qf'      => implode(' ', $qf),
                'q.op'    => 'OR',             // ← the default operator, we want anything that matches, at least one term
                'fq'      => 'doctype:course', // ← filter to only "course" type docs
                //'mm'      => '0%',           // ← for at least any match, this defaults to 0%
                //'rows'    => 10,             // ← row limiter
                'wt'      => 'json',
            ];
        } else {
            // ------- ADVANCED SEARCH (fielded boolean query) -------
            $FIELD_MAP = [
                // map form inputs -> solr schema fields
                'search_terms_title'       => 'title',
                'search_terms_keywords'    => 'keywords',
                'search_terms_instructor'  => 'prof_names',
                'search_terms_coursecode'  => 'public_code',
                'search_terms_description' => 'units',
            ];

            $clauses = [];

            foreach ($FIELD_MAP as $formKey => $solrField) {
                if (!empty($params[$formKey])) {
                    $clause = self::buildFieldClause($solrField, (string)$params[$formKey]);
                    if ($clause) $clauses[] = $clause;
                }
            }

            if (!$clauses) {
                // If all optional fields empty, default behavior
                $terms = '*:*';
            } else {
                // OR between different fields to enforce each filled field as a constraint
                $terms = implode(' OR ', $clauses);
            }

            $q = [
                // Use the classic parser since we’ve already built a Lucene query string
                'defType' => 'lucene',
                'q'       => '(' . $terms . ') AND doctype:course',
                // 'rows'    => 10,
                'wt'      => 'json',
            ];
        }

        return $q;
    }

    /**
     * Escape Lucene special characters in a single token (NOT a full query)
     * Reference specials: + - && || ! ( ) { } [ ] ^ " ~ * ? : \ /
     */
    private static function solrEscapeTerm(string $term): string {
        // Remove control chars
        $term = preg_replace('/[\x00-\x1F\x7F]/u', '', $term);

        // Escape all Lucene special characters
        $specials = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '/',];
        // Sort longer tokens first so && and || are handled before single &
        usort($specials, fn($a, $b) => strlen($b) <=> strlen($a));
        foreach ($specials as $s) {
            $term = str_replace($s, '\\' . $s, $term);
        }

        // Also collapse multiple spaces
        return preg_replace('/\s+/u', ' ', trim($term));
    }


    /**
     * Build a clause like: field:(term1 OR term2) given a raw input string.
     * If the input looks like a single token containing no spaces, just field:term
     * If multiple tokens, OR them.
     */
    private static function buildFieldClause(string $field, string $raw): ?string {
        $raw = trim($raw);
        if ($raw === '') return null;

        // Split on whitespace, keep words of length > 0
        $parts = preg_split('/\s+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) return null;

        // Escape each token
        $escaped = array_map('self::solrEscapeTerm', $parts);

        if (count($escaped) === 1) {
            return sprintf('%s:%s', $field, $escaped[0]);
        }
        // OR within the same field to broaden results; could use ' AND ' to narrow matching
        return sprintf('%s:(%s)', $field, implode(' OR ', $escaped));
    }

}
