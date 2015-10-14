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

class ForumPostIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum post row.
     * 
     * @global string $urlServer
     * @param  object  $fpost
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($fpost) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_FORUMPOST . '_' . $fpost->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $fpost->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_FORUMPOST, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $fpost->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('topicid', $fpost->topic_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($fpost->post_text)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/forum/viewtopic.php?course=' . course_id_to_code($fpost->course_id)
                        . '&amp;topic=' . intval($fpost->topic_id)
                        . '&amp;forum=' . intval($fpost->forum_id), $encoding));

        return $doc;
    }

    /**
     * Fetch a Forum Post from DB.
     * 
     * @param  int    $fpostId - the forum post id
     * @return object          - the DB fetched anonymous object
     */
    protected function fetch($fpostId) {
        $fpost = Database::get()->querySingle("SELECT fp.*, f.course_id, ft.forum_id FROM forum_post fp 
                            JOIN forum_topic ft ON fp.topic_id = ft.id 
                            JOIN forum f ON ft.forum_id = f.id 
                            JOIN forum_category fc ON fc.id = f.cat_id 
                        WHERE fc.cat_order >= 0 AND fp.id = ?d", $fpostId);
        if (!$fpost) {
            return null;
        }

        return $fpost;
    }
    
    /**
     * Get Term object for locating a unique single forum post.
     * 
     * @param  int $fpostId - the forum post id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($fpostId) {
        return new Zend_Search_Lucene_Index_Term('fpost_' . $fpostId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible forum posts.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('fpost', 'doctype');
    }
    
    /**
     * Get all possible forum posts from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT fp.*, f.course_id, ft.forum_id FROM forum_post fp 
                                            JOIN forum_topic ft ON fp.topic_id = ft.id 
                                            JOIN forum f ON ft.forum_id = f.id 
                                            JOIN forum_category fc ON fc.id = f.cat_id 
                                        WHERE fc.cat_order >= 0");
    }
    
    /**
     * Get Lucene query input string for locating all forum posts belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:fpost AND courseid:' . $courseId;
    }
    
    /**
     * Get all forum posts belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT fp.*, f.course_id, ft.forum_id FROM forum_post fp 
                            JOIN forum_topic ft ON fp.topic_id = ft.id 
                            JOIN forum f ON ft.forum_id = f.id 
                            JOIN forum_category fc ON fc.id = f.cat_id 
                        WHERE fc.cat_order >= 0 AND f.course_id = ?d", $courseId);
    }

    /**
     * Remove all Forum Posts belonging to a Forum Topic.
     * 
     * @param int     $topicId
     * @param boolean $optimize
     */
    public function removeByTopic($topicId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $hits = $this->__index->find('doctype:fpost AND topicid:' . $topicId);
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
                $queryStr .= 'content:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:' . $data['course_id'] . ' AND doctype:fpost';
            return $queryStr;
        }

        return null;
    }

}
