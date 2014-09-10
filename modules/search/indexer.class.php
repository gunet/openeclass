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

set_include_path(implode(PATH_SEPARATOR, array(
    $webDir . '/include',
    get_include_path(),
)));
require_once 'Zend/Search/Exception.php';
require_once 'Zend/Search/Lucene.php';
require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';
require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8Num/CaseInsensitive.php';
require_once 'Zend/Search/Lucene/Storage/Directory/Filesystem.php';
require_once 'agendaindexer.class.php';
require_once 'announcementindexer.class.php';
require_once 'courseindexer.class.php';
require_once 'documentindexer.class.php';
require_once 'exerciseindexer.class.php';
require_once 'forumindexer.class.php';
require_once 'forumpostindexer.class.php';
require_once 'forumtopicindexer.class.php';
require_once 'linkindexer.class.php';
require_once 'noteindexer.class.php';
require_once 'unitindexer.class.php';
require_once 'unitresourceindexer.class.php';
require_once 'videoindexer.class.php';
require_once 'videolinkindexer.class.php';

class Indexer {

    private $__index = null;
    private static $_resultSetLimit = 100;
    private static $lookup = array(
        // Greek doubles
        'αι' => 'e', 'Αι' => 'E', 'ΑΙ' => 'E', 'αί' => 'e', 'Αί' => 'E', 'ει' => 'i', 'Ει' => 'i', 'ΕΙ' => 'i', 'εί' => 'i', 'Εί' => 'i',
        'ου' => 'u', 'Ου' => 'U', 'ΟΥ' => 'U', 'ού' => 'u', 'Ού' => 'U', 'οι' => 'i', 'Οι' => 'i', 'ΟΙ' => 'i', 'Οί' => 'i', 'Οί' => 'i',
        'υι' => 'i', 'Υι' => 'i', 'ΥΙ' => 'i', 'υί' => 'i', 'Υί' => 'i',
        'ββ' => 'b', 'ΒΒ' => 'B', 'γγ' => 'gk', 'ΓΓ' => 'gk', 'κκ' => 'k', 'ΚΚ' => 'K', 'λλ' => 'l', 'ΛΛ' => 'L',
        'μμ' => 'm', 'MM' => 'M', 'νν' => 'n', 'ΝΝ' => 'N', 'ππ' => 'p', 'ΠΠ' => 'P', 'ρρ' => 'r', 'ΡΡ' => 'r',
        'σσ' => 's', 'ΣΣ' => 'S', 'ττ' => 't', 'ΤΤ' => 'T',
        // Greek letters
        'α' => 'a', 'ά' => 'a', 'Α' => 'A', 'Ά' => 'A', 'β' => 'b', 'Β' => 'B', 'γ' => 'g', 'Γ' => 'G', 'δ' => 'd', 'Δ' => 'D',
        'ε' => 'e', 'έ' => 'e', 'Ε' => 'E', 'Έ' => 'E', 'ζ' => 'z', 'Ζ' => 'Z', 'η' => 'i', 'ή' => 'i', 'Η' => 'I', 'Ή' => 'I',
        'θ' => 'q', 'Θ' => 'Q', 'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I',
        'κ' => 'k', 'Κ' => 'K', 'λ' => 'l', 'Λ' => 'L', 'μ' => 'm', 'Μ' => 'M', 'ν' => 'n', 'Ν' => 'N', 'ξ' => 'j', 'Ξ' => 'J',
        'ο' => 'o', 'ό' => 'o', 'Ο' => 'O', 'Ό' => 'O', 'π' => 'p', 'Π' => 'P', 'ρ' => 'r', 'Ρ' => 'R', 'σ' => 's', 'ς' => 's', 'Σ' => 'S',
        'τ' => 't', 'Τ' => 'T', 'υ' => 'i', 'ύ' => 'i', 'ϋ' => 'i', 'ΰ' => 'i', 'Υ' => 'I', 'Ύ' => 'I', 'Ϋ' => 'I',
        'φ' => 'f', 'Φ' => 'F', 'χ' => 'x', 'Χ' => 'X', 'ψ' => 'c', 'Ψ' => 'C', 'ω' => 'o', 'ώ' => 'o', 'Ω' => 'O', 'Ώ' => 'O',
        // Special characters
        'µ' => 'm', 'µµ' => 'm',
        // Latin
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f'
    );
    private static $specials = array(
        '?', '*', '[', ']', '{', '}', '~', '"', '\'', '+', '-',
        '&&', '||', '!', '(', ')', '^', ':', '\\'
    );
    private static $specialkeywords = array(
        'not', 'and', 'or'
    );

    /**
     * Convert an input string to its phonetic representation.
     * 
     * @param  string $text
     * @param  int    $ignoreCase
     * @return string
     */
    public static function phonetics($text, $ignoreCase = 1) {
        $result = strtr($text, self::$lookup);
        if ($ignoreCase) {
            $result = strtolower($result);
        }
        return $result;
    }

    /**
     * Clear/filter Lucene operators.
     * 
     * @param  string $inputStr
     * @return string
     */
    public static function filterQuery($inputStr) {
        $terms = explode(' ', str_replace(self::$specials, '', self::phonetics($inputStr)));
        $clearTerms = array();
        foreach ($terms as $term) {
            if (!in_array($term, self::$specialkeywords)) {
                $clearTerms[] = $term;
            }
        }
        return implode(' ', $clearTerms);
    }

    /**
     * Indexer Constructor.
     * 
     * @global type $webDir
     */
    public function __construct() {
        global $webDir;
        
        if (!get_config('enable_indexing')) {
            return;
        }

        $index_path = $webDir . '/courses/idx';
        // Give read-writing permissions only for current user and group
        Zend_Search_Lucene_Storage_Directory_Filesystem::setDefaultFilePermissions(0664);
        // Utilize UTF-8 compatible text analyzer
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

        try {
            if (file_exists($index_path)) {
                $this->__index = Zend_Search_Lucene::open($index_path); // Open index
            } else {
                $this->__index = Zend_Search_Lucene::create($index_path); // Create index
            }
        } catch (Zend_Search_Lucene_Exception $e) {
            require_once 'fatal_error.php';
        }

        $this->__index->setFormatVersion(Zend_Search_Lucene::FORMAT_2_3); // Set Index Format Version
        Zend_Search_Lucene::setResultSetLimit(self::$_resultSetLimit);    // Set Result Set Limit
        // write an .htaccess to prevent raw access to index files
        $htaccess = $index_path . '/.htaccess';
        if (!file_exists($htaccess)) {
            $fd = fopen($htaccess, "w");
            fwrite($fd, "deny from all\n");
            fclose($fd);
        }
    }

    /**
     * Return the index object.
     * 
     * @return Zend_Search_Lucene_Interface
     */
    public function getIndex() {
        return $this->__index;
    }

    /**
     * Filtered Search in the index.
     * 
     * @param  string $inputStr - A Lucene Query, it is filtered for Lucene operators
     * @return array            - array of Zend_Search_Lucene_Search_QueryHit objects
     */
    public function search($inputStr) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $queryStr = self::filterQuery($inputStr);
        return $this->searchRaw($queryStr);
    }

    /**
     * Raw Search in the index.
     * 
     * @param  string $inputStr - A Lucene Query, it is NOT filtered for Lucene operators
     * @return array            - array of Zend_Search_Lucene_Search_QueryHit objects
     */
    public function searchRaw($inputStr) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        try {
            $query = Zend_Search_Lucene_Search_QueryParser::parse($inputStr, 'utf-8');
            return $this->__index->find($query);
        } catch (Zend_Search_Exception $e) {
            return array();
        }
        return array();
    }

    /**
     * Batch store all index contents related to a Course.
     * 
     * @param int $courseId
     */
    public function storeAllByCourse($courseId) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $cidx = new CourseIndexer($this);
        $cidx->store($courseId);

        $aidx = new AnnouncementIndexer($this);
        $aidx->storeByCourse($courseId);

        $agdx = new AgendaIndexer($this);
        $agdx->storeByCourse($courseId);

        $lidx = new LinkIndexer($this);
        $lidx->storeByCourse($courseId);

        $vdx = new VideoIndexer($this);
        $vdx->storeByCourse($courseId);

        $vldx = new VideolinkIndexer($this);
        $vldx->storeByCourse($courseId);

        $eidx = new ExerciseIndexer($this);
        $eidx->storeByCourse($courseId);

        $fidx = new ForumIndexer($this);
        $fidx->storeByCourse($courseId);

        $ftdx = new ForumTopicIndexer($this);
        $ftdx->storeByCourse($courseId);

        $fpdx = new ForumPostIndexer($this);
        $fpdx->storeByCourse($courseId);

        $didx = new DocumentIndexer($this);
        $didx->storeByCourse($courseId);

        $uidx = new UnitIndexer($this);
        $uidx->storeByCourse($courseId);

        $urdx = new UnitResourceIndexer($this);
        $urdx->storeByCourse($courseId);
        
        $ndx = new NoteIndexer($this);
        $ndx->storeByCourse($courseId);
    }

    /**
     * Batch remove all index contents related to a Course.
     * 
     * @param int $courseId
     */
    public function removeAllByCourse($courseId) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $cidx = new CourseIndexer($this);
        $cidx->remove($courseId);

        $aidx = new AnnouncementIndexer($this);
        $aidx->removeByCourse($courseId);

        $agdx = new AgendaIndexer($this);
        $agdx->removeByCourse($courseId);

        $lidx = new LinkIndexer($this);
        $lidx->removeByCourse($courseId);

        $vdx = new VideoIndexer($this);
        $vdx->removeByCourse($courseId);

        $vldx = new VideolinkIndexer($this);
        $vldx->removeByCourse($courseId);

        $eidx = new ExerciseIndexer($this);
        $eidx->removeByCourse($courseId);

        $fidx = new ForumIndexer($this);
        $fidx->removeByCourse($courseId);

        $ftdx = new ForumTopicIndexer($this);
        $ftdx->removeByCourse($courseId);

        $fpdx = new ForumPostIndexer($this);
        $fpdx->removeByCourse($courseId);

        $didx = new DocumentIndexer($this);
        $didx->removeByCourse($courseId);

        $uidx = new UnitIndexer($this);
        $uidx->removeByCourse($courseId);

        $urdx = new UnitResourceIndexer($this);
        $urdx->removeByCourse($courseId);
        
        $ndx = new NoteIndexer($this);
        $ndx->removeByCourse($courseId);
    }

    /**
     * Batch index all possible contents.
     */
    public function reindexAll() {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $cidx = new CourseIndexer($this);
        $cidx->reindex();

        $aidx = new AnnouncementIndexer($this);
        $aidx->reindex();

        $agdx = new AgendaIndexer($this);
        $agdx->reindex();

        $lidx = new LinkIndexer($this);
        $lidx->reindex();

        $vdx = new VideoIndexer($this);
        $vdx->reindex();

        $vldx = new VideolinkIndexer($this);
        $vldx->reindex();

        $eidx = new ExerciseIndexer($this);
        $eidx->reindex();

        $fidx = new ForumIndexer($this);
        $fidx->reindex();

        $ftdx = new ForumTopicIndexer($this);
        $ftdx->reindex();

        $fpdx = new ForumPostIndexer($this);
        $fpdx->reindex();

        $didx = new DocumentIndexer($this);
        $didx->reindex();

        $uidx = new UnitIndexer($this);
        $uidx->reindex();

        $urdx = new UnitResourceIndexer($this);
        $urdx->reindex();
        
        $ndx = new NoteIndexer($this);
        $ndx->reindex();
    }
    
    /**
     * Batch remove all index contents.
     */
    public function deleteAll() {
        if (!get_config('enable_indexing')) {
            return;
        }

        for ($count = 0; $count < $this->__index->maxDoc(); $count++) {
            if (!$this->__index->isDeleted($count)) {
                $this->__index->delete($count);
            }
        }

        $this->__index->commit();
    }

    /**
     * Unit test for phonetic conversion.
     * 
     * @return int - returns 0 if test failed or 1 if test succeeds, should always return 1
     */
    public function test() {
        $phtext = "αβγδεζηθικλμνξοπρσςτυφχψω άέίύήόώ ϊΐϋΰ ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ ΆΈΊΎΉΌΏ ΪΫ Αι έννοιαι των Αιρέσεων του Αββαείου";
        if (self::phonetics($phtext, 0) != "abgdeziqiklmnjoprsstifxco aeiiioo iiii ABGDEZIQIKLMNJOPRSTIFXCO AEIIIOO II E enie ton Ereseon tu Abaiu") {
            return 0;
        }

        return 1;
    }

}
