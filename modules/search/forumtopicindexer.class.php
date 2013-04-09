<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class ForumTopicIndexer implements ResourceIndexerInterface {
    
    private $__indexer = null;
    private $__index = null;

    /**
     * Constructor. You can optionally use an already instantiated Indexer object if there is one.
     * 
     * @param Indexer $idxer - optional indexer object
     */
    public function __construct($idxer = null) {
        if ($idxer == null)
            $this->__indexer = new Indexer();
        else
            $this->__indexer = $idxer;
        
        $this->__index = $this->__indexer->getIndex();
    }
    
    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum topic db row.
     * 
     * @global string $urlServer
     * @param  array  $ftopic
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($ftopic) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'ftopic_' . $ftopic['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $ftopic['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'ftopic', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $ftopic['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('forumid', $ftopic['forum_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($ftopic['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', 
                $urlServer .'modules/forum/viewforum.php?course='. course_id_to_code($ftopic['course_id']) 
                           .'&amp;forum=' . intval($ftopic['forum_id']), $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Forum Topic from DB.
     * 
     * @param  int $ftopicId
     * @return array - the mysql fetched row
     */
    private function fetch($ftopicId) {
        $res = db_query("SELECT ft.*, f.course_id FROM forum_topic ft 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0 AND ft.id = " . intval($ftopicId));
        $ftopic = mysql_fetch_assoc($res);
        if (!$ftopic)
            return null;
        
        return $ftopic;
    }

    /**
     * Store a Forum Topic in the Index.
     * 
     * @param  int     $ftopicId
     * @param  boolean $finalize
     */
    public function store($ftopicId, $finalize = true) {
        $ftopic = $this->fetch($ftopicId);
        if (!$ftopic)
            return;
        
        // delete existing forum topic from index
        $this->remove($ftopicId, false, false);

        // add the forum topic back to the index
        $this->__index->addDocument(self::makeDoc($ftopic));
        
        // commit/optimize unless not wanted
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove a Forum Topic from the Index.
     * 
     * @param int     $ftopicId
     * @param boolean $existCheck
     * @param boolean $finalize
     */
    public function remove($ftopicId, $existCheck = false, $finalize = true) {
        if ($existCheck) {
            $ftopic = $this->fetch($ftopicId);
            if (!$ftopic)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('ftopic_' . $ftopicId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove all Forum Topics belonging to a Course.
     * 
     * @param int $courseId
     */
    public function removeByCourse($courseId) {
        $hits = $this->__index->find('doctype:ftopic AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        $this->__indexer->finalize();
    }
    
    /**
     * Remove all Forum Topics belonging to a Forum.
     * 
     * @param int $forumId
     */
    public function removeByForum($forumId) {
        $hits = $this->__index->find('doctype:ftopic AND forumid:' . $forumId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        $this->__indexer->finalize();
    }
    
    /**
     * Reindex all forum topics.
     */
    public function reindex() {
        // remove all forum topics from index
        $term = new Zend_Search_Lucene_Index_Term('ftopic', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all forum topics from db
        $res = db_query("SELECT ft.*, f.course_id FROM forum_topic ft 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0");
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        $this->__indexer->finalize();
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
            isset($data['course_id']   ) && !empty($data['course_id']   ) ) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term)
                $queryStr .= 'title:' . $term . '* ';
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:ftopic';
            return $queryStr;
        } 
        
        return null;
    }
    
}
