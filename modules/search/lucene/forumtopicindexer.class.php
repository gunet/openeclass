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

class ForumTopicIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum topic db row.
     *
     * @param object $ftopic
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($ftopic) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_FORUMTOPIC . '_' . $ftopic->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $ftopic->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_FORUMTOPIC, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_COURSEID, $ftopic->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_FORUMID, $ftopic->forum_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($ftopic->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($ftopic->course_id)
            . '&amp;forum=' . intval($ftopic->forum_id), $encoding));

        return $doc;
    }

    /**
     * Fetch a Forum Topic from DB.
     *
     * @param int $ftopicId
     * @return object - the mysql fetched row
     */
    protected function fetch($ftopicId) {
        $ftopic = Database::get()->querySingle("SELECT ft.*, f.course_id FROM forum_topic ft 
                                                    JOIN forum f ON ft.forum_id = f.id 
                                                    JOIN forum_category fc ON fc.id = f.cat_id 
                                                WHERE fc.cat_order >= 0 AND ft.id = ?d", $ftopicId);

        if (!$ftopic) {
            return null;
        }

        return $ftopic;
    }

    /**
     * Get Term object for locating a unique single forum topic.
     *
     * @param int $ftopicId - the forum topic id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($ftopicId) {
        return new Zend_Search_Lucene_Index_Term('ftopic_' . $ftopicId, 'pk');
    }

    /**
     * Get Term object for locating all possible forum topics.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('ftopic', 'doctype');
    }

    /**
     * Get all possible forum topics from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT ft.*, f.course_id FROM forum_topic ft 
                                            JOIN forum f ON ft.forum_id = f.id 
                                            JOIN forum_category fc ON fc.id = f.cat_id 
                                          WHERE fc.cat_order >= 0");
    }

    /**
     * Get Lucene query input string for locating all forum topics belonging to a given course.
     *
     * @param int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:ftopic AND courseid:' . $courseId;
    }

    /**
     * Get all forum topics belonging to a given course from DB.
     *
     * @param int $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return FetcherUtil::fetchForumTopics($courseId);
    }

    /**
     * Remove all Forum Topics belonging to a Forum.
     *
     * @param int $forumId
     * @param boolean $optimize
     */
    public function removeByForum($forumId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }

        $hits = $this->__index->find('doctype:ftopic AND forumid:' . $forumId);
        foreach ($hits as $hit) {
            $this->__index->delete($hit->id);
        }

        $this->optimizeOrCommit($optimize);
    }

}
