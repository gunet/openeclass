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

class DocumentIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a document db row.
     * 
     * @global string $urlServer
     * @param  object  $docu
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($docu) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_DOCUMENT . '_' . $docu->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $docu->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_DOCUMENT, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $docu->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($docu->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($docu->description), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('filename', Indexer::phonetics($docu->filename), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('comment', Indexer::phonetics($docu->comment), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('creator', Indexer::phonetics($docu->creator), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('subject', Indexer::phonetics($docu->subject), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('author', Indexer::phonetics($docu->author), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $docu->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('public', $docu->public, $encoding));

        $urlAction = ($docu->format == '.dir') ? 'openDir' : 'download';
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer
                        . 'modules/document/index.php?course=' . course_id_to_code($docu->course_id)
                        . '&amp;' . $urlAction . '=' . $docu->path, $encoding));

        return $doc;
    }

    /**
     * Fetch a Document from DB.
     * 
     * @param  int $docId
     * @return object - the mysql fetched row
     */
    protected function fetch($docId) {
        // exclude non-main subsystems and metadata
        $doc = Database::get()->querySingle("SELECT * FROM document WHERE id = ?d AND course_id >= 1 AND subsystem = 0 AND format <> \".meta\"", $docId);
        if (!$doc) {
            return null;
        }

        return $doc;
    }
    
    /**
     * Get Term object for locating a unique single document.
     * 
     * @param  int $docId - the document id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($docId) {
        return new Zend_Search_Lucene_Index_Term('doc_' . $docId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible documents.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('doc', 'doctype');
    }
    
    /**
     * Get all possible documents from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM document WHERE course_id >= 1 AND subsystem = 0 AND format <> \".meta\"");
    }
    
    /**
     * Get Lucene query input string for locating all documents belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:doc AND courseid:' . $courseId;
    }
    
    /**
     * Get all documents belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * 
            FROM document 
            WHERE course_id >= 1 
            AND subsystem = 0 
            AND format <> \".meta\" 
            AND course_id = ?d", $courseId);
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
                $queryStr .= 'filename:' . $term . '* ';
                $queryStr .= 'comment:' . $term . '* ';
                $queryStr .= 'creator:' . $term . '* ';
                $queryStr .= 'subject:' . $term . '* ';
                $queryStr .= 'author:' . $term . '* ';
            }
            $queryStr .= ') AND courseid:' . $data['course_id'] . ' AND doctype:doc AND visible:1';
            if ($anonymous) {
                $queryStr .= ' AND public:1';
            }
            return $queryStr;
        }

        return null;
    }

}
