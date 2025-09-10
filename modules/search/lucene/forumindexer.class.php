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

class ForumIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum db row.
     *
     * @param object $forum
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($forum) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_FORUM . '_' . $forum->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $forum->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_FORUM, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_COURSEID, $forum->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($forum->name), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_CONTENT, Indexer::phonetics($forum->desc), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($forum->course_id)
            . '&amp;forum=' . $forum->id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Forum from DB.
     *
     * @param int $forumId
     * @return object - the mysql fetched row
     */
    protected function fetch($forumId) {
        return FetcherUtil::fetchForum($forumId);
    }

    /**
     * Get Term object for locating a unique single forum.
     *
     * @param int $forumId - the forum id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($forumId) {
        return new Zend_Search_Lucene_Index_Term('forum_' . $forumId, 'pk');
    }

    /**
     * Get Term object for locating all possible forums.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('forum', 'doctype');
    }

    /**
     * Get all possible forums from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT f.* 
            FROM forum f 
            JOIN forum_category fc ON f.cat_id = fc.id 
            WHERE fc.cat_order >= 0");
    }

    /**
     * Get Lucene query input string for locating all forums belonging to a given course.
     *
     * @param int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:forum AND courseid:' . $courseId;
    }

    /**
     * Get all forums belonging to a given course from DB.
     *
     * @param int $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        FetcherUtil::fetchForums($courseId);
    }

}
