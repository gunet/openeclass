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
require_once 'resourceindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class ExerciseIndexer implements ResourceIndexerInterface {

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
     * Construct a Zend_Search_Lucene_Document object out of an exercise db row.
     * 
     * @global string $urlServer
     * @param  object  $exercise
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($exercise) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'exercise_' . $exercise->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $exercise->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'exercise', $encoding));
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
    private function fetch($exerciseId) {
        $exercise = Database::get()->querySingle("SELECT * FROM exercise WHERE id = ?d", $exerciseId);        
        if (!$exercise)
            return null;

        return $exercise;
    }

    /**
     * Store an Exercise in the Index.
     * 
     * @param  int     $exerciseId
     * @param  boolean $optimize
     */
    public function store($exerciseId, $optimize = false) {
        $exercise = $this->fetch($exerciseId);
        if (!$exercise)
            return;

        // delete existing exercise from index
        $this->remove($exerciseId, false, false);

        // add the exercise back to the index
        $this->__index->addDocument(self::makeDoc($exercise));

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Remove an Exercise from the Index.
     * 
     * @param int     $exerciseId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($exerciseId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $exercise = $this->fetch($exerciseId);
            if (!$exercise)
                return;
        }

        $term = new Zend_Search_Lucene_Index_Term('exercise_' . $exerciseId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Store all Exercises belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function storeByCourse($courseId, $optimize = false) {
        // delete existing exercises from index
        $this->removeByCourse($courseId);

        // add the exercises back to the index
        $res = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $courseId);
        foreach ($res as $row) {
            $this->__index->addDocument(self::makeDoc($row));
        }

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Remove all Exercises belonging to a Course.
     * 
     * @param int     $courseId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:exercise AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Reindex all exercises.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all exercises from index
        $term = new Zend_Search_Lucene_Index_Term('exercise', 'doctype');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);

        // get/index all exercises from db
        $res = Database::get()->queryArray("SELECT * FROM exercise");
        foreach ($res as $row) {
            $this->__index->addDocument(self::makeDoc($row));
        }

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
                isset($data['course_id']) && !empty($data['course_id'])) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'content:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:' . $data['course_id'] . ' AND doctype:exercise AND visible:1';
            return $queryStr;
        }

        return null;
    }

}
