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

class UnitResourceIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a unit resource db row.
     * 
     * @global string $urlServer
     * @param  array  $ures
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($ures) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'unitresource_' . $ures['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $ures['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'unitresource', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $ures['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('unitid', $ures['unit_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($ures['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($ures['comments'])), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $ures['visible'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer 
                .'modules/units/index.php?course='. course_id_to_code($ures['course_id']) 
                .'&amp;id='. $ures['unit_id'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Unit Resource from DB.
     * 
     * @param  int $uresId
     * @return array - the mysql fetched row
     */
    private function fetch($uresId) {
        $res = db_query("SELECT ur.*, cu.course_id
            FROM unit_resources ur 
            JOIN course_units cu ON cu.id = ur.unit_id 
            WHERE id = " . intval($uresId));
        $ures = mysql_fetch_assoc($res);
        if (!$ures)
            return null;
        
        return $ures;
    }

    /**
     * Store a Unit Resource in the Index.
     * 
     * @param  int     $uresId
     * @param  boolean $optimize
     */
    public function store($uresId, $optimize = false) {
        $ures = $this->fetch($uresId);
        if (!$ures)
            return;
        
        // delete existing unit resource from index
        $this->remove($uresId, false, false);

        // add the unit resource back to the index
        $this->__index->addDocument(self::makeDoc($ures));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Unit Resource from the Index.
     * 
     * @param int     $uresId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($uresId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $ures = $this->fetch($uresId);
            if (!$ures)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('unitresource_' . $uresId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Unit Resources belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:unitresource AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Unit Resources belonging to a Unit.
     * 
     * @param int     $unitId
     * @param boolean $optimize
     */
    public function removeByUnit($unitId, $optimize = false) {
        $hits = $this->__index->find('doctype:unitresource AND unitid:' . $unitId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all unit resources.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all unit resources from index
        $term = new Zend_Search_Lucene_Index_Term('unitresource', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all unit resources from db
        $res = db_query("SELECT ur.*, cu.course_id
            FROM unit_resources ur 
            JOIN course_units cu ON cu.id = ur.unit_id");
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
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:unitresource AND visible:1';
            return $queryStr;
        } 
        
        return null;
    }
    
}
