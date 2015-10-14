<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

require_once 'indexer.class.php';
require_once 'abstractindexer.class.php';
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class ForumTopicIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum topic db row.
     * 
     * @global string $urlServer
     * @param  object  $ftopic
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($ftopic) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_FORUMTOPIC . '_' . $ftopic->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $ftopic->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_FORUMTOPIC, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $ftopic->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('forumid', $ftopic->forum_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($ftopic->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($ftopic->course_id)
                        . '&amp;forum=' . intval($ftopic->forum_id), $encoding));

        return $doc;
    }

    /**
     * Fetch a Forum Topic from DB.
     * 
     * @param  int $ftopicId
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
     * @param  int $ftopicId - the forum topic id
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
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:ftopic AND courseid:' . $courseId;
    }
    
    /**
     * Get all forum topics belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT ft.*, f.course_id FROM forum_topic ft 
                                                JOIN forum f ON ft.forum_id = f.id 
                                                JOIN forum_category fc ON fc.id = f.cat_id 
                                            WHERE fc.cat_order >= 0 AND f.course_id = ?d", $courseId);
    }

    /**
     * Remove all Forum Topics belonging to a Forum.
     * 
     * @param int     $forumId
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

    /**
     * Build a Lucene Query.
     * 
     * @param  array   $data      - The data (normally $_POST), needs specific array keys
     * @param  boolean $anonymous - whether we build query for anonymous user access or not
     * @return string             - the returned query string
     */
    public static function buildQuery($data, $anonymous = true) {
        if (isset($data['search_terms']) && !empty($data['search_terms']) &&
                isset($data['course_id']) && !empty($data['course_id'])) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:' . $data['course_id'] . ' AND doctype:ftopic';
            return $queryStr;
        }

        return null;
    }

}
