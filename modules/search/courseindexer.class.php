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
require_once 'Zend/Search/Lucene/Document.php';
require_once 'Zend/Search/Lucene/Field.php';
require_once 'Zend/Search/Lucene/Index/Term.php';

class CourseIndexer {
    
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
     * Fetch a Course from DB.
     * 
     * @param  int $courseId
     * @return array - the mysql fetched row
     */
    private function fetchCourse($courseId) {
        $res = db_query("SELECT * FROM course WHERE id = " . intval($courseId));
        return mysql_fetch_assoc($res);
    }

    /**
     * Store a Course in the Index.
     * 
     * @global string  $urlServer
     * @param  int     $courseId
     * @param  boolean $finalize
     */
    public function storeCourse($courseId, $finalize = true) {
        global $urlServer;
        
        $course = $this->fetchCourse($courseId);
        if (!$course)
            return;
        
        // delete existing course from index
        $this->removeCourse($courseId, false, false);
        
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'course_' . $course['id']));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $course['id']));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'course'));
        $doc->addField(Zend_Search_Lucene_Field::Text('code', Indexer::phonetics($course['code'])));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($course['title'])));
        $doc->addField(Zend_Search_Lucene_Field::Text('keywords', Indexer::phonetics($course['keywords'])));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $course['visible']));
        $doc->addField(Zend_Search_Lucene_Field::Text('prof_names', Indexer::phonetics($course['prof_names'])));
        $doc->addField(Zend_Search_Lucene_Field::Text('public_code', Indexer::phonetics($course['public_code'])));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('created', $course['created']));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'courses/'. $course['code']));

        $this->__index->addDocument($doc);
        if ($finalize)
            $this->__indexer->finalize();
    }
    
    /**
     * Remove a Course from the Index.
     * 
     * @param int     $courseId
     * @param boolean $existCheck
     * @param boolean $finalize
     */
    public function removeCourse($courseId, $existCheck = false, $finalize = true) {
        if ($existCheck) {
            $course = $this->fetchCourse($courseId);
            if (!$course)
                return;
        }
        
        $term = new Zend_Search_Lucene_Index_Term('course_' . $courseId, 'pk');
        $docIds  = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);
        
        if ($finalize)
            $this->__indexer->finalize();
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

        return "
        <form method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
         <legend>$langSearchCriteria:</legend>
            <table class='tbl'>
                <tr>
                    <th width='120'>$langTitle:</th>
                    <td><input name='search_terms_title' type='text' size='50' /></td>
                    <td class='smaller'>$langTitle_Descr</td>
                </tr>
                <tr>
                    <th>$langDescription:</th>
                    <td><input name='search_terms_description' type='text' size='50' /></td>
                    <td class='smaller'>$langDescription_Descr</small>
                </tr>
                <tr>
                    <th>$langKeywords:</th>
                    <td><input name='search_terms_keywords' type='text' size='50' /></td>
                    <td class='smaller'>$langKeywords_Descr</td>
                </tr>
                <tr>
                    <th>$langTeacher:</th>
                    <td><input name='search_terms_instructor' type='text' size='50' /></td>
                    <td class='smaller'>$langInstructor_Descr</td>
                </tr>
                <tr>
                    <th>$langCourseCode:</th>
                    <td><input name='search_terms_coursecode' type='text' size='50' /></td>
                    <td class='smaller'>$langCourseCode_Descr</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td colspan=2 class='right'><input type='submit' name='submit' value='$langDoSearch' />&nbsp;&nbsp;<input type='reset' name='reset' value='$langNewSearch' /></td>
                </tr>
            </table>
        </fieldset>
        </form>";
    }
    
    /**
     * Build a Lucene Query.
     * 
     * @param  array   $data      - The data (usually $_POST), needs specific array keys, @see getDetailedSearchForm()
     * @param  boolean $anonymous - whether we build query for anonymous user access or not
     * @return string             - the returned query string
     */
    public static function buildQuery($data, $anonymous = true) {
        if (isset($data['search_terms']) && !empty($data['search_terms'])) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'keywords:' . $term . '* ';
                $queryStr .= 'prof_names:' . $term . '* ';
                $queryStr .= 'code:' . $term . '* ';
                $queryStr .= 'public_code:' . $term . '* ';
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
            //list($queryStr, $needsOR) = self::appendQuery($data, 'search_terms_description', '', $queryStr, $needsOR); // TODO: implement searching in course units
            $queryStr .= ')';
        }
        $queryStr .= ' AND doctype:course';
        if ($anonymous)
            $queryStr .= ' AND (visible:1 OR visible:2)';
        else
            $queryStr .= ' AND (visible:0 OR visible:3)';
        return $queryStr;
    }

    /**
     * Append to the Lucene Query according to data input.
     * 
     * @param  array   $data     - The data (usually coming from $_POST)
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
