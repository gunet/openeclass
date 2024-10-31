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

class ExerciseIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of an exercise db row.
     *
     * @global string $urlServer
     * @param  object  $exercise
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($exercise) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_EXERCISE . '_' . $exercise->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $exercise->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_EXERCISE, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $exercise->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($exercise->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($exercise->description)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $exercise->active, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/exercise/exercise_submit.php?course=' . course_id_to_code($exercise->course_id) . '&amp;exerciseId=' . $exercise->id, $encoding));

        return $doc;
    }

    /**
     * Fetch an Exercise from DB.
     *
     * @param  int $exerciseId
     * @return object - the mysql fetched row
     */
    protected function fetch($exerciseId) {
        $exercise = Database::get()->querySingle("SELECT * FROM exercise WHERE id = ?d", $exerciseId);
        if (!$exercise) {
            return null;
        }

        return $exercise;
    }

    /**
     * Get Term object for locating a unique single exercise.
     *
     * @param  int $exerciseId - the exercise id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($exerciseId) {
        return new Zend_Search_Lucene_Index_Term('exercise_' . $exerciseId, 'pk');
    }

    /**
     * Get Term object for locating all possible exercises.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('exercise', 'doctype');
    }

    /**
     * Get all possible exercises from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM exercise");
    }

    /**
     * Get Lucene query input string for locating all exercises belonging to a given course.
     *
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:exercise AND courseid:' . $courseId;
    }

    /**
     * Get all exercises belonging to a given course from DB.
     *
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $courseId);
    }

}
