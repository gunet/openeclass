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
require_once 'abstractbaseindexer.class.php';
require_once 'courseindexer.interface.php';
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class CourseIndexer extends AbstractBaseIndexer implements CourseIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a course db row.
     * 
     * @global string $urlServer
     * @param  object  $course
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($course) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'course_' . $course->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $course->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'course', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('code', Indexer::phonetics($course->code), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($course->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('keywords', Indexer::phonetics($course->keywords), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $course->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('prof_names', Indexer::phonetics($course->prof_names), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('public_code', Indexer::phonetics($course->public_code), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('units', Indexer::phonetics(strip_tags($course->units)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('created', $course->created, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'courses/' . $course->code, $encoding));

        return $doc;
    }

    /**
     * Fetch a Course from DB.
     * 
     * @param  int $courseId
     * @return object - the mysql fetched row
     */
    protected function fetch($courseId) {
        $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $courseId);        
        if (!$course) {
            return null;
        }

        // visible units
        $course->units = '';
        $res = Database::get()->queryArray("SELECT id, title, comments
                                            FROM course_units
                                           WHERE visible > 0
                                             AND course_id = ?d", $courseId);
        $unitIds = array();
        foreach ($res as $row) {
            $course->units .= $row->title . ' ' . $row->comments . ' ';
            $unitIds[] = $row->id;
        }

        // visible unit resources
        foreach ($unitIds as $unitId) {
            $res = Database::get()->queryArray("SELECT title, comments
                                                FROM unit_resources
                                               WHERE visible > 0
                                                 AND unit_id = ?d", $unitId);
            foreach ($res as $row) {
                $course->units .= $row->title . ' ' . $row->comments . ' ';
            }
        }

        // invisible but useful units and resources
        $res = Database::get()->queryArray("SELECT id
                                            FROM course_units
                                           WHERE visible = 0
                                             AND `order` = -1
                                             AND course_id  = ?d", $courseId);
        $unitIds = array();
        foreach ($res as $row) {
            $unitIds[] = $row->id;
        }
        foreach ($unitIds as $unitId) {
            $res = Database::get()->queryArray("SELECT comments
                                                FROM unit_resources
                                               WHERE visible >= 0
                                                 AND unit_id = ?d", $unitId);
            foreach ($res as $row) {
                $course->units .= $row->comments . ' ';
            }
        }
        return $course;
    }
    
    /**
     * Get Term object for locating a unique single course.
     * 
     * @param  int $courseId - the course id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($courseId) {
        return new Zend_Search_Lucene_Index_Term('course_' . $courseId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible courses.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('course', 'doctype');
    }
    
    /**
     * Get all possible courses from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM course");
    }

    /**
     * Return the detailed search form for courses.
     * 
     * @global string $langSearchCriteria
     * @global string $langTitle
     * @global string $langTitle_Descr
     * @global string $langDescription
     * @global string $langDescription_Descr
     * @global string $langKeywords
     * @global string $langKeywords_Descr
     * @global string $langTeacher
     * @global string $langInstructor_Descr
     * @global string $langCourseCode
     * @global string $langCourseCode_Descr
     * @global string $langDoSearch
     * @global string $langNewSearch
     * @return string - The form's HTML representation
     */
    public static function getDetailedSearchForm() {
        global $langSearchCriteria, $langTitle, $langTitle_Descr, $langDescription,
        $langDescription_Descr, $langKeywords, $langKeywords_Descr, $langTeacher,
        $langInstructor_Descr, $langCourseCode, $langCourseCode_Descr, $langDoSearch,
        $langNewSearch;

        return "<div class='form-wrapper'>            
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>            
        <fieldset>         
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                <div class='col-sm-10'><input id='title' class='form-control' name='search_terms_title' type='text' placeholder='$langTitle_Descr'></div>
            </div>
            <div class='form-group'>
                <label for='description' class='col-sm-2 control-label'>$langDescription:</label>
                <div class='col-sm-10'><input id='description' class='form-control' name='search_terms_description' type='text' placeholder='$langDescription_Descr'></div>                    
            </div>
            <div class='form-group'>
                <label for='keywords' class='col-sm-2 control-label'>$langKeywords:</label>
                <div class='col-sm-10'><input id='keywords' class='form-control' name='search_terms_keywords' type='text' placeholder='$langKeywords_Descr'></div>                
            </div>
            <div class='form-group'>
                <label for='teacher' class='col-sm-2 control-label'>$langTeacher:</label>
                <div class='col-sm-10'><input id='teacher' class='form-control' name='search_terms_instructor' type='text' placeholder='$langInstructor_Descr'></div>                
            </div>
            <div class='form-group'>
                <label for='code' class='col-sm-2 control-label'>$langCourseCode:</label>
                <div class='col-sm-10'><input id='code' class='form-control' name='search_terms_coursecode' type='text' placeholder='$langCourseCode_Descr'></div>                
            </div>
            <div class='col-sm-offset-2 col-sm-10'>              
                <input class='btn btn-primary' type='submit' name='submit' value='$langDoSearch'>
                <input class='btn btn-default' type='reset' name='reset' value='$langNewSearch'>
            </div>            
            </fieldset>
            </form>
        </div>";
    }

    /**
     * Build a Lucene Query.
     * 
     * @param  array   $data      - The data (normally $_POST), needs specific array keys, @see getDetailedSearchForm()
     * @return string             - the returned query string
     */
    public static function buildQuery($data) {
        if (isset($data['search_terms']) && !empty($data['search_terms'])) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'keywords:' . $term . '* ';
                $queryStr .= 'prof_names:' . $term . '* ';
                $queryStr .= 'code:' . $term . '* ';
                $queryStr .= 'public_code:' . $term . '* ';
                $queryStr .= 'units:' . $term . '* ';
            }
            $queryStr .= ')';
        } else {
            $queryStr = '(';
            $needsOR = false;
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_title', 'title', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_keywords', 'keywords', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_instructor', 'prof_names', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_coursecode', 'code', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_coursecode', 'public_code', $queryStr, $needsOR);
            list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_description', 'units', $queryStr, $needsOR);
            $queryStr .= ')';
        }
        $queryStr .= ' AND doctype:course';
        return $queryStr;
    }

    /**
     * Append to the Lucene Query according to data input.
     * 
     * @param  array   $data     - The data (normally coming from $_POST)
     * @param  string  $key      - $data[key]
     * @param  string  $queryKey - Lucene Document field key
     * @param  string  $queryStr - The Lucene Query string
     * @param  boolean $needsOR  - special flag for appending OR
     * @return array             - returns the Lucene Query string and the flag for appending OR in an array
     */
    private static function appendQuery($data, $key, $queryKey, $queryStr, $needsOR) {
        if (isset($data[$key]) && !empty($data[$key])) {
            $terms = explode(' ', Indexer::filterQuery($data[$key]));
            foreach ($terms as $term) {
                $queryStr .= ($needsOR) ? 'OR ' : '';
                $queryStr .= $queryKey . ':' . $term . '* ';
                $needsOR = true;
            }
        }
        return array($queryStr, $needsOR);
    }

}
