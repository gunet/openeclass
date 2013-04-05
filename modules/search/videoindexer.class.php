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

class VideoIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a video db row.
     * 
     * @global string $urlServer
     * @param  array  $video
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($video) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'video_' . $video['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $video['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'video', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $video['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($video['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($video['description']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer .'modules/video/file.php?course='. course_id_to_code($video['course_id']) . '&amp;id=' . $video['id'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Video from DB.
     * 
     * @param  int $videoId
     * @return array - the mysql fetched row
     */
    private function fetch($videoId) {
        $res = db_query("SELECT * FROM video WHERE id = " . intval($videoId));
        $video = mysql_fetch_assoc($res);
        if (!$video)
            return null;
        
        return $video;
    }

    /**
     * Store a Video in the Index.
     * 
     * @param  int     $videoId
     * @param  boolean $finalize
     */
    public function store($videoId, $finalize = true) {
        $video = $this->fetch($videoId);
        if (!$video)
            return;
        
        // delete existing video from index
        $this->remove($videoId, false, false);

        // add the video back to the index
        $this->__index->addDocument(self::makeDoc($video));
        
        // commit/optimize unless not wanted
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove a Video from the Index.
     * 
     * @param int     $videoId
     * @param boolean $existCheck
     * @param boolean $finalize
     */
    public function remove($videoId, $existCheck = false, $finalize = true) {
        if ($existCheck) {
            $video = $this->fetch($videoId);
            if (!$video)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('video_' . $videoId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove all Videos belonging to a Course.
     * 
     * @param int $courseId
     */
    public function removeByCourse($courseId) {
        $hits = $this->__index->find('doctype:video AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        $this->__indexer->finalize();
    }
    
    /**
     * Reindex all videos.
     */
    public function reindex() {
        // remove all videos from index
        $term = new Zend_Search_Lucene_Index_Term('video', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all videos from db
        $res = db_query("SELECT * FROM video");
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
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:video';
            return $queryStr;
        } 
        
        return null;
    }
    
}
