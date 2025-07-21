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

class UnitIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a unit db row.
     *
     * @param object $unit
     * @return Zend_Search_Lucene_Document
     * @global string $urlServer
     */
    protected function makeDoc($unit) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PK, ConstantsUtil::DOCTYPE_UNIT . '_' . $unit->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_PKID, $unit->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_DOCTYPE, ConstantsUtil::DOCTYPE_UNIT, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword(ConstantsUtil::FIELD_COURSEID, $unit->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_TITLE, Indexer::phonetics($unit->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_CONTENT, Indexer::phonetics(strip_tags($unit->comments)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text(ConstantsUtil::FIELD_VISIBLE, $unit->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(ConstantsUtil::FIELD_URL, $urlServer
            . 'modules/units/index.php?course=' . course_id_to_code($unit->course_id) . '&amp;id=' . $unit->id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Unit from DB.
     *
     * @param int $unitId
     * @return object - the mysql fetched row
     */
    protected function fetch($unitId) {
        $unit = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d", $unitId);
        if (!$unit) {
            return null;
        }

        return $unit;
    }

    /**
     * Get Term object for locating a unique single unit.
     *
     * @param int $unitId - the unit id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($unitId) {
        return new Zend_Search_Lucene_Index_Term('unit_' . $unitId, 'pk');
    }

    /**
     * Get Term object for locating all possible units.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('unit', 'doctype');
    }

    /**
     * Get all possible units from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM course_units");
    }

    /**
     * Get Lucene query input string for locating all units belonging to a given course.
     *
     * @param int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:unit AND courseid:' . $courseId;
    }

    /**
     * Get all units belonging to a given course from DB.
     *
     * @param int $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        FetcherUtil::fetchUnits($courseId);
    }

}
