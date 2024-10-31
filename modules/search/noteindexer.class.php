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

class NoteIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a note db row.
     *
     * @global string $urlServer
     * @param  object  $note
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($note) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', 'note_' . $note->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $note->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', 'note', $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('userid', $note->user_id, $encoding));
        if(isset($note->course_id)){
            $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $note->course_id, $encoding));
        }
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($note->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($note->content)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/notes/index.php?an_id=' . $note->id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Note from DB.
     *
     * @param  int $noteId
     * @return object - the mysql fetched row
     */
    protected function fetch($noteId) {
        $note = Database::get()->querySingle("SELECT * FROM note WHERE id = ?d", $noteId);
        if (!$note) {
            return null;
        }

        if(!is_null($note->reference_obj_course)) {
            $note->course_id = intval($note->reference_obj_course);
        }

        return $note;
    }

    /**
     * Get Term object for locating a unique single note.
     *
     * @param  int $noteId - the note id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($noteId) {
        return new Zend_Search_Lucene_Index_Term('note_' . $noteId, 'pk');
    }

    /**
     * Get Term object for locating all possible notes.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('note', 'doctype');
    }

    /**
     * Get all possible notes from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM note");
    }

    /**
     * Get Lucene query input string for locating all notes belonging to a given course.
     *
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:note AND courseid:' . $courseId;
    }

    /**
     * Get all notes belonging to a given course from DB.
     *
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM note WHERE reference_obj_course = ?d", $courseId);
    }

    /**
     * Store all Notes written by a user.
     *
     * @param int     $userId
     * @param boolean $optimize
     */
    public function storeByUser($userId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }

        // delete existing notes from index
        $this->removeByUser($userId);

        // add the notes back to the index
        $res = Database::get()->queryArray("SELECT * FROM note WHERE user_id = ?d", $userId);
        foreach ($res as $row) {
            $this->__index->addDocument($this->makeDoc($row));
        }

        $this->optimizeOrCommit($optimize);
    }

    /**
     * Remove all Notes written by a user.
     *
     * @param int     $userId
     * @param boolean $optimize
     */
    public function removeByUser($userId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }

        $hits = $this->__index->find('doctype:note AND userid:' . $userId);
        foreach ($hits as $hit) {
            $this->__index->delete($hit->id);
        }

        $this->optimizeOrCommit($optimize);
    }

    /**
     * Build a Lucene Query.
     *
     * @param  array   $data      - The data (usually $_POST), needs specific array keys
     * @param  boolean $anonymous - whether we build query for anonymous user access or not
     * @return string             - the returned query string
     */
    public static function buildQuery($data, $anonymous = true) {
        if (isset($data['search_terms']) && !empty($data['search_terms']) &&
                isset($data['user_id']) && !empty($data['user_id'])) {
            $terms = explode(' ', Indexer::filterQuery($data['search_terms']));
            $queryStr = '(';
            foreach ($terms as $term) {
                $queryStr .= 'title:' . $term . '* ';
                $queryStr .= 'content:' . $term . '* ';
            }
            $queryStr .= ') AND userid:' . $data['user_id'] . ' AND doctype:note';
            return $queryStr;
        }

        return null;
    }

}
