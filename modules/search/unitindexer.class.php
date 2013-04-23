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

class UnitIndexer implements ResourceIndexerInterface {
    
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
     * Construct a Zend_Search_Lucene_Document object out of a unit db row.
     * 
     * @global string $urlServer
     * @param  array  $unit
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($unit) {
        global $urlServer;
        $encoding = 'utf-8';
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'unit_' . $unit['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $unit['id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'unit', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $unit['course_id'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($unit['title']), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($unit['comments'])), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $unit['visible'], $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer 
                .'modules/units/index.php?course='. course_id_to_code($unit['course_id']) . '&amp;id=' . $unit['id'], $encoding));
        
        return $doc;
    }
    
    /**
     * Fetch a Unit from DB.
     * 
     * @param  int $unitId
     * @return array - the mysql fetched row
     */
    private function fetch($unitId) {
        $res = db_query("SELECT * FROM course_units WHERE id = " . intval($unitId));
        $unit = mysql_fetch_assoc($res);
        if (!$unit)
            return null;
        
        return $unit;
    }

    /**
     * Store a Unit in the Index.
     * 
     * @param  int     $unitId
     * @param  boolean $optimize
     */
    public function store($unitId, $optimize = false) {
        $unit = $this->fetch($unitId);
        if (!$unit)
            return;
        
        // delete existing unit from index
        $this->remove($unitId, false, false);

        // add the unit back to the index
        $this->__index->addDocument(self::makeDoc($unit));
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove a Unit from the Index.
     * 
     * @param int     $unitId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($unitId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $unit = $this->fetch($unitId);
            if (!$unit)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('unit_' . $unitId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Remove all Units belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:unit AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);
        
        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all units.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all units from index
        $term = new Zend_Search_Lucene_Index_Term('unit', 'doctype');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        // get/index all units from db
        $res = db_query("SELECT * FROM course_units");
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
            $queryStr .= ') AND courseid:'. $data['course_id'] .' AND doctype:unit AND visible:1';
            return $queryStr;
        } 
        
        return null;
    }
    
}
