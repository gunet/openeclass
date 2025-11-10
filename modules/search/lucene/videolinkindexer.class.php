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
require_once 'abstractindexer.class.php';
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/FetcherUtil.php';

class VideolinkIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a VideoLink db row.
     *
     * @param object $vlink
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($vlink) {
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_VIDEOLINK . '_' . $vlink->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $vlink->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_VIDEOLINK, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_COURSEID, $vlink->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($vlink->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_CONTENT, Indexer::phonetics($vlink->description), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $vlink->url, $encoding));

        return $doc;
    }

    /**
     * Fetch a VideoLink from DB.
     *
     * @param int $vlinkId
     * @return object - the mysql fetched row
     */
    protected function fetch($vlinkId) {
        return FetcherUtil::fetchVideoLink($vlinkId);
    }

    /**
     * Get Term object for locating a unique single videolink.
     *
     * @param int $vlinkId - the videolink id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($vlinkId) {
        return new Zend_Search_Lucene_Index_Term('vlink_' . $vlinkId, 'pk');
    }

    /**
     * Get Term object for locating all possible videolinks.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('vlink', 'doctype');
    }

    /**
     * Get all possible videolinks from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM videolink");
    }

    /**
     * Get Lucene query input string for locating all videolinks belonging to a given course.
     *
     * @param int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:vlink AND courseid:' . $courseId;
    }

    /**
     * Get all videolinks belonging to a given course from DB.
     *
     * @param int $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return FetcherUtil::fetchVideoLinks($courseId);
    }

}
