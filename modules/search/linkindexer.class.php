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

class LinkIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a link db row.
     * 
     * @global string $urlServer
     * @param  array  $link
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($link) {
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'link_' . $link['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $link['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'link', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $link['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($link['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($link['description'])), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $link['url'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Link from DB.
     * 
     * @param  int $linkId
     * @return array - the mysql fetched row
     */
    private function fetch($linkId) {
        $res = db_query("SELECT * FROM link WHERE id = " . intval($linkId));
        $link = mysql_fetch_assoc($res);
        if (!$link)
            return null;
        
        return $link;
    }

    /**
     * Store a Link in the Index.
     * 
     * @param  int     $linkId
     * @param  boolean $optimize
     */
    public function store($linkId, $optimize = false) {
        $link = $this->fetch($linkId);
        if (!$link)
            return;
        
        // delete existing link from index
        $this->remove($linkId, false, false);

        // add the link back to the index
        $this->__index->addDocument(self::makeDoc($link));
        
        // commit/optimize unless not wanted
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Link from the Index.
     * 
     * @param int     $linkId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($linkId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $link = $this->fetch($linkId);
            if (!$link)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('link_' . $linkId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Store all Links belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function storeByCourse($courseId, $optimize = false) {
        // delete existing links from index
        $this->removeByCourse($courseId);

        // add the links back to the index
        $res = db_query("SELECT * FROM link WHERE course_id = ". intval($courseId));
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Links belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:link AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all links.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all links from index
        $term = new Zend_Search_Lucene_Index_Term('link', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all links from db
        $res = db_query("SELECT * FROM link");
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
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:link';
            return $queryStr;
        } 
        
        return null;
    }
    
}
