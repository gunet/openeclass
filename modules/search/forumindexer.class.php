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

class ForumIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a forum db row.
     * 
     * @global string $urlServer
     * @param  array  $forum
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($forum) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'forum_' . $forum['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $forum['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'forum', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $forum['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($forum['name']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($forum['desc']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', 
                $urlServer .'modules/forum/viewforum.php?course='. course_id_to_code($forum['course_id']) 
                           .'&amp;forum=' . $forum['id'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Forum from DB.
     * 
     * @param  int $forumId
     * @return array - the mysql fetched row
     */
    private function fetch($forumId) {
        $res = db_query("SELECT f.* FROM forum f 
            JOIN forum_category fc ON f.cat_id = fc.id 
            WHERE fc.cat_order >= 0 AND f.id = " . intval($forumId));
        $forum = mysql_fetch_assoc($res);
        if (!$forum)
            return null;
        
        return $forum;
    }

    /**
     * Store a Forum in the Index.
     * 
     * @param  int     $forumId
     * @param  boolean $optimize
     */
    public function store($forumId, $optimize = false) {
        $forum = $this->fetch($forumId);
        if (!$forum)
            return;
        
        // delete existing forum from index
        $this->remove($forumId, false, false);

        // add the forum back to the index
        $this->__index->addDocument(self::makeDoc($forum));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Forum from the Index.
     * 
     * @param int     $forumId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($forumId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $forum = $this->fetch($forumId);
            if (!$forum)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('forum_' . $forumId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Store all Forums belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function storeByCourse($courseId, $optimize = false) {
        // delete existing forums from index
        $this->removeByCourse($courseId);

        // add the forums back to the index
        $res = db_query("SELECT f.* FROM forum f JOIN forum_category fc ON f.cat_id = fc.id WHERE fc.cat_order >= 0 AND f.course_id = ". intval($courseId));
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Forums belonging to a Course.
     * 
     * @param int     $courseId
     * @oaram boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:forum AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all forums.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all forums from index
        $term = new Zend_Search_Lucene_Index_Term('forum', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all forums from db
        $res = db_query("SELECT f.* FROM forum f JOIN forum_category fc ON f.cat_id = fc.id WHERE fc.cat_order >= 0");
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
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'content:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:forum';
            return $queryStr;
        } 
        
        return null;
    }
    
}
