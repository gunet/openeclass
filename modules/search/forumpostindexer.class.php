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

class ForumPostIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a forum post row.
     * 
     * @global string $urlServer
     * @param  array  $fpost
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($fpost) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'fpost_' . $fpost['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $fpost['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'fpost', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $fpost['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('topicid', $fpost['topic_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($fpost['post_text'])), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', 
                $urlServer .'modules/forum/viewtopic.php?course='. course_id_to_code($fpost['course_id']) 
                           .'&amp;topic=' . intval($fpost['topic_id']) 
                           .'&amp;forum=' . intval($fpost['forum_id']), $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Forum Post from DB.
     * 
     * @param  int $fpostId
     * @return array - the mysql fetched row
     */
    private function fetch($fpostId) {
        $res = db_query("SELECT fp.*, f.course_id, ft.forum_id FROM forum_post fp 
            JOIN forum_topic ft ON fp.topic_id = ft.id 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0 AND fp.id = " . intval($fpostId));
        $fpost = mysql_fetch_assoc($res);
        if (!$fpost)
            return null;
        
        return $fpost;
    }

    /**
     * Store a Forum Post in the Index.
     * 
     * @param  int     $fpostId
     * @param  boolean $optimize
     */
    public function store($fpostId, $optimize = false) {
        $fpost = $this->fetch($fpostId);
        if (!$fpost)
            return;
        
        // delete existing forum post from index
        $this->remove($fpostId, false, false);

        // add the forum post back to the index
        $this->__index->addDocument(self::makeDoc($fpost));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Forum Post from the Index.
     * 
     * @param int     $fpostId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($fpostId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $fpost = $this->fetch($fpostId);
            if (!$fpost)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('fpost_' . $fpostId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Forum Posts belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:fpost AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Forum Posts belonging to a Forum Topic.
     * 
     * @param int     $topicId
     * @param boolean $optimize
     */
    public function removeByTopic($topicId, $optimize = false) {
        $hits = $this->__index->find('doctype:fpost AND topicid:' . $topicId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all forum posts.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all forum posts from index
        $term = new Zend_Search_Lucene_Index_Term('fpost', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all forum posts from db
        $res = db_query("SELECT fp.*, f.course_id, ft.forum_id FROM forum_post fp 
            JOIN forum_topic ft ON fp.topic_id = ft.id 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0");
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
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
                $queryStr .= 'content:' . $term . '* ';
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:fpost';
            return $queryStr;
        } 
        
        return null;
    }
    
}
