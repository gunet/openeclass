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

require_once 'indexer.class.php';
require_once 'abstractbaseindexer.class.php';
require_once 'courseindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/FetcherUtil.php';

class CourseIndexer extends AbstractBaseIndexer implements CourseIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a course db row.
     *
     * @param object $course
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($course) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_COURSE . '_' . $course->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $course->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_COURSE, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_CODE, Indexer::phonetics($course->code), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($course->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_KEYWORDS, Indexer::phonetics($course->keywords), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_VISIBLE, $course->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_PROF_NAMES, Indexer::phonetics($course->prof_names), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_PUBLIC_CODE, Indexer::phonetics($course->public_code), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_UNITS, Indexer::phonetics(strip_tags($course->units)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_CREATED, $course->created, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $urlServer . 'courses/' . $course->code, $encoding));

        return $doc;
    }

    /**
     * Fetch a Course from DB.
     *
     * @param int $courseId
     * @return ?object - the mysql fetched row
     */
    protected function fetch($courseId): ?object {
        return FetcherUtil::fetchCourse($courseId);
    }

    /**
     * Get Term object for locating a unique single course.
     *
     * @param int $courseId - the course id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($courseId) {
        return new Zend_Search_Lucene_Index_Term('course_' . $courseId, 'pk');
    }

    /**
     * Get Term object for locating all possible courses.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('course', 'doctype');
    }

    /**
     * Get all possible courses from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM course");
    }

    /**
     * Build one or more Lucene Queries.
     *
     * @param array $data - The data (normally $_POST), needs specific array keys, @see getDetailedSearchForm()
     * @return string             - the returned query string
     */
    public static function buildQueries($data) {
        $queryStrings = array();
        $andCourse = ' AND doctype:course';

        if (isset($data['search_terms']) && !empty($data['search_terms'])) {
            $clearTerms = Indexer::filterQuery($data['search_terms']);

            if (strpos($clearTerms, ' ') !== false) {
                $queryFull = 'title:"' . $clearTerms . '"' . $andCourse;
                $queryStrings[] = $queryFull;
                $querySemi = 'title:"' . $clearTerms . '*"' . $andCourse;
                $queryStrings[] = $querySemi;
            }

            $queryStr = '(';
            $terms = explode(' ', $clearTerms);
            foreach ($terms as $term) {
                // ignore short terms
                if (strlen($term) <= 2) {
                    continue;
                }
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'keywords:' . $term . '* ';
                $queryStr .= 'prof_names:' . $term . '* ';
                $queryStr .= 'code:' . $term . '* ';
                $queryStr .= 'public_code:' . $term . '* ';
                $queryStr .= 'units:' . $term . '* ';
            }
            $queryStr .= ')' . $andCourse;
            $queryStrings[] = $queryStr;
        } else {
            $queryStr = '(';
            $needsOR = false;
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_title', 'title', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_keywords', 'keywords', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_instructor', 'prof_names', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_coursecode', 'code', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_coursecode', 'public_code', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_description', 'units', $queryStr, $needsOR);
            $queryStr .= ')' . $andCourse;
            $queryStrings[] = $queryStr;
        }
        return $queryStrings;
    }

    /**
     * Append to the Lucene Query according to data input.
     *
     * @param array $data - The data (normally coming from $_POST)
     * @param string $key - $data[key]
     * @param string $queryKey - Lucene Document field key
     * @param string $queryStr - The Lucene Query string
     * @param boolean $needsOR - special flag for appending OR
     * @return array             - returns the Lucene Query string and the flag for appending OR in an array
     */
    private static function appendQuery($data, $key, $queryKey, $queryStr, $needsOR) {
        if (isset($data[$key]) && !empty($data[$key])) {
            $terms = explode(' ', Indexer::filterQuery($data[$key]));
            foreach ($terms as $term) {
                $queryStr .= ($needsOR) ? 'OR ' : '';
                $queryStr .= $queryKey . ':' . $term . '* ';
                $needsOR = true;
            }
        }
        return array($queryStr, $needsOR);
    }

}
