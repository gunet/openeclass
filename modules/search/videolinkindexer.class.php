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

class VideolinkIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a VideoLink db row.
     * 
     * @global string $urlServer
     * @param  array  $vlink
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($vlink) {
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'vlink_' . $vlink['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $vlink['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'vlink', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $vlink['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($vlink['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($vlink['description']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $vlink['url'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a VideoLink from DB.
     * 
     * @param  int $vlinkId
     * @return array - the mysql fetched row
     */
    private function fetch($vlinkId) {
        $res = db_query("SELECT * FROM videolinks WHERE id = " . intval($vlinkId));
        $vlink = mysql_fetch_assoc($res);
        if (!$vlink)
            return null;
        
        return $vlink;
    }

    /**
     * Store a VideoLink in the Index.
     * 
     * @param  int     $vlinkId
     * @param  boolean $finalize
     */
    public function store($vlinkId, $finalize = true) {
        $vlink = $this->fetch($vlinkId);
        if (!$vlink)
            return;
        
        // delete existing videolink from index
        $this->remove($vlinkId, false, false);

        // add the videolink back to the index
        $this->__index->addDocument(self::makeDoc($vlink));
        
        // commit/optimize unless not wanted
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove a VideoLink from the Index.
     * 
     * @param int     $vlinkId
     * @param boolean $existCheck
     * @param boolean $finalize
     */
    public function remove($vlinkId, $existCheck = false, $finalize = true) {
        if ($existCheck) {
            $vlink = $this->fetch($vlinkId);
            if (!$vlink)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('vlink_' . $vlinkId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove all VideoLinks belonging to a Course.
     * 
     * @param int $courseId
     */
    public function removeByCourse($courseId) {
        $hits = $this->__index->find('doctype:vlink AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        $this->__indexer->finalize();
    }
    
    /**
     * Reindex all VideoLinks.
     */
    public function reindex() {
        // remove all videolinks from index
        $term = new Zend_Search_Lucene_Index_Term('vlink', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all videolinks from db
        $res = db_query("SELECT * FROM videolinks");
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
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'content:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:vlink';
            return $queryStr;
        } 
        
        return null;
    }
    
}
