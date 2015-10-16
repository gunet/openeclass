<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
require_once 'abstractindexer.class.php';
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class UnitResourceIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a unit resource db row.
     * 
     * @global string $urlServer
     * @param  object  $ures
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($ures) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_UNITRESOURCE . '_' . $ures->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $ures->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_UNITRESOURCE, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $ures->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('unitid', $ures->unit_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($ures->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($ures->comments)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $ures->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer
                        . 'modules/units/index.php?course=' . course_id_to_code($ures->course_id)
                        . '&amp;id=' . $ures->unit_id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Unit Resource from DB.
     * 
     * @param  int $uresId
     * @return object - the mysql fetched row
     */
    protected function fetch($uresId) {
        $ures = Database::get()->querySingle("SELECT ur.*, cu.course_id
                                                FROM unit_resources ur 
                                            JOIN course_units cu ON cu.id = ur.unit_id 
                                                WHERE ur.id = ?d",  $uresId);        
        if (!$ures) {
            return null;
        }

        return $ures;
    }
    
    /**
     * Get Term object for locating a unique single unit resource.
     * 
     * @param  int $uresId - the unit resource id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($uresId) {
        return new Zend_Search_Lucene_Index_Term('unitresource_' . $uresId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible unit resources.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('unitresource', 'doctype');
    }
    
    /**
     * Get all possible unit resources from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT ur.*, cu.course_id
                                                FROM unit_resources ur 
                                            JOIN course_units cu ON cu.id = ur.unit_id");
    }
    
    /**
     * Get Lucene query input string for locating all unit resources belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:unitresource AND courseid:' . $courseId;
    }
    
    /**
     * Get all unit resources belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT ur.*, cu.course_id
                                            FROM unit_resources ur 
                                           JOIN course_units cu ON cu.id = ur.unit_id AND cu.course_id = ?d", $courseId);
    }

    /**
     * Remove all Unit Resources belonging to a Unit.
     * 
     * @param int     $unitId
     * @param boolean $optimize
     */
    public function removeByUnit($unitId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $hits = $this->__index->find('doctype:unitresource AND unitid:' . $unitId);
        foreach ($hits as $hit) {
            $this->__index->delete($hit->id);
        }

        $this->optimizeOrCommit($optimize);
    }

}
