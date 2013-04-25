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

class DocumentIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a document db row.
     * 
     * @global string $urlServer
     * @param  array  $docu
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($docu) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'doc_' . $docu['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $docu['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'doc', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $docu['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($docu['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($docu['description']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('filename', Indexer::phonetics($docu['filename']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('comment', Indexer::phonetics($docu['comment']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('creator', Indexer::phonetics($docu['creator']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('subject', Indexer::phonetics($docu['subject']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('author', Indexer::phonetics($docu['author']), $encoding));        
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $docu['visible'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('public', $docu['public'], $encoding));

        $urlAction = ($docu['format'] == '.dir') ? 'openDir' : 'download';
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer 
                .'modules/document/index.php?course='. course_id_to_code($docu['course_id']) 
                .'&amp;'. $urlAction .'='. $docu['path'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Document from DB.
     * 
     * @param  int $docId
     * @return array - the mysql fetched row
     */
    private function fetch($docId) {
        // exclude non-main subsystems and metadata
        $res = db_query("SELECT * FROM document WHERE id = " . intval($docId) . " AND course_id >= 1 AND subsystem = 0 AND format <> \".meta\"");
        $doc = mysql_fetch_assoc($res);
        if (!$doc)
            return null;
        
        return $doc;
    }

    /**
     * Store a Document in the Index.
     * 
     * @param  int     $docId
     * @param  boolean $optimize
     */
    public function store($docId, $optimize = false) {
        $doc = $this->fetch($docId);
        if (!$doc)
            return;
        
        // delete existing document from index
        $this->remove($docId, false, false);

        // add the document back to the index
        $this->__index->addDocument(self::makeDoc($doc));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Document from the Index.
     * 
     * @param int     $docId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($docId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $doc = $this->fetch($docId);
            if (!$doc)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('doc_' . $docId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Store all Documents belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function storeByCourse($courseId, $optimize = false) {
        // delete existing documents from index
        $this->removeByCourse($courseId);

        // add the documents back to the index
        $res = db_query("SELECT * FROM document WHERE course_id >= 1 AND subsystem = 0 AND format <> \".meta\" AND course_id = ". intval($courseId));
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Documents belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:doc AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all documents.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all documents from index
        $term = new Zend_Search_Lucene_Index_Term('doc', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all documents from db - exclude non-main subsystems and metadata
        $res = db_query("SELECT * FROM document WHERE course_id >= 1 AND subsystem = 0 AND format <> \".meta\"");
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
                $queryStr .= 'filename:' . $term . '* ';
                $queryStr .= 'comment:' . $term . '* ';
                $queryStr .= 'creator:' . $term . '* ';
                $queryStr .= 'subject:' . $term . '* ';
                $queryStr .= 'author:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:doc AND visible:1';
            if ($anonymous)
                $queryStr .= ' AND public:1';
            return $queryStr;
        } 
        
        return null;
    }
    
}
