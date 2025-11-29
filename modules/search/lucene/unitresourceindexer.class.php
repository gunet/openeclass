<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once 'indexer.class.php';
require_once 'abstractindexer.class.php';
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/FetcherUtil.php';

class UnitResourceIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a unit resource db row.
     *
     * @param object $ures
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($ures) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_UNITRESOURCE . '_' . $ures->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $ures->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_UNITRESOURCE, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_COURSEID, $ures->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_UNITID, $ures->unit_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($ures->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_CONTENT, Indexer::phonetics(strip_tags($ures->comments)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_VISIBLE, $ures->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $urlServer
            . 'modules/units/index.php?course=' . course_id_to_code($ures->course_id)
            . '&amp;id=' . $ures->unit_id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Unit Resource from DB.
     *
     * @param int $uresId
     * @return object - the mysql fetched row
     */
    protected function fetch($uresId) {
        return FetcherUtil::fetchUnitResource($uresId);
    }

    /**
     * Get Term object for locating a unique single unit resource.
     *
     * @param int $uresId - the unit resource id
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
     * @param int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:unitresource AND courseid:' . $courseId;
    }

    /**
     * Get all unit resources belonging to a given course from DB.
     *
     * @param int $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return FetcherUtil::fetchUnitResources($courseId);
    }

    /**
     * Remove all Unit Resources belonging to a Unit.
     *
     * @param int $unitId
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
