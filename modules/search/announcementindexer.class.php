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

class AnnouncementIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of an announcement db row.
     * 
     * @global string $urlServer
     * @param  array  $announce
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($announce) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'announce_' . $announce['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $announce['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'announce', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $announce['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($announce['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($announce['content'])), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $announce['visible'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer .'modules/announcements/index.php?course='. course_id_to_code($announce['course_id']) . '&amp;an_id=' . $announce['id'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch an Announcement from DB.
     * 
     * @param  int $announceId
     * @return array - the mysql fetched row
     */
    private function fetch($announceId) {
        $res = db_query("SELECT * FROM announcement WHERE id = " . intval($announceId));
        $announce = mysql_fetch_assoc($res);
        if (!$announce)
            return null;
        
        return $announce;
    }

    /**
     * Store an Announcement in the Index.
     * 
     * @param  int     $announceId
     * @param  boolean $finalize
     */
    public function store($announceId, $finalize = true) {
        $announce = $this->fetch($announceId);
        if (!$announce)
            return;
        
        // delete existing announcement from index
        $this->remove($announceId, false, false);

        // add the announcement back to the index
        $this->__index->addDocument(self::makeDoc($announce));
        
        // commit/optimize unless not wanted
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove an Announcement from the Index.
     * 
     * @param int     $announceId
     * @param boolean $existCheck
     * @param boolean $finalize
     */
    public function remove($announceId, $existCheck = false, $finalize = true) {
        if ($existCheck) {
            $announce = $this->fetch($announceId);
            if (!$announce)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('announce_' . $announceId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove all Announcements belonging to a Course.
     * 
     * @param int $courseId
     */
    public function removeByCourse($courseId) {
        $hits = $this->__index->find('doctype:announce AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        $this->__indexer->finalize();
    }
    
    /**
     * Reindex all announcements.
     */
    public function reindex() {
        // remove all announcements from index
        $term = new Zend_Search_Lucene_Index_Term('announce', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all announcements from db
        $res = db_query("SELECT * FROM announcement");
        while ($row = mysql_fetch_assoc($res))
            $this->__index->addDocument(self::makeDoc($row));
        
        $this->__indexer->finalize();
    }
    
    /**
     * Build a Lucene Query.
     * 
     * @param  array   $data      - The data (usually $_POST), needs specific array keys
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
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:announce AND visible:1';
            return $queryStr;
        } 
        
        return null;
    }
    
}
