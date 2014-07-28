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

class NoteIndexer implements ResourceIndexerInterface {

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
     * Construct a Zend_Search_Lucene_Document object out of a note db row.
     * 
     * @global string $urlServer
     * @param  object  $note
     * @return Zend_Search_Lucene_Document
     */
    private static function makeDoc($note) {
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
    private function fetch($noteId) {
        $note = Database::get()->querySingle("SELECT * FROM note WHERE id = ?d", $noteId);        
        if (!$note)
            return null;
        if(!is_null($note->reference_obj_course)){
            $note->course_id = intval($note->reference_obj_course);
        }
        return $note;
    }

    /**
     * Store a Note in the Index.
     * 
     * @param  int     $noteId
     * @param  boolean $optimize
     */
    public function store($noteId, $optimize = false) {
        $note = $this->fetch($noteId);
        if (!$note)
            return;

        // delete existing note from index
        $this->remove($noteId, false, false);

        // add the note back to the index
        $this->__index->addDocument(self::makeDoc($note));

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Remove a Note from the Index.
     * 
     * @param int     $noteId
     * @param boolean $existCheck
     * @param boolean $optimize
     */
    public function remove($noteId, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $note = $this->fetch($noteId);
            if (!$note)
                return;
        }

        $term = new Zend_Search_Lucene_Index_Term('note_' . $noteId, 'pk');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Store all Notes written by a user.
     * 
     * @param int     $userId
     * @param boolean $optimize
     */
    public function storeByUser($userId, $optimize = false) {
        // delete existing notes from index
        $this->removeByUser($userId);

        // add the notes back to the index
        $res = Database::get()->queryArray("SELECT id FROM note WHERE user_id = ?d", $userId);
        foreach ($res as $row) {
            $this->__index->addDocument(self::makeDoc(self::$this->fetch($row->id)));
        }

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Remove all Notes written by a user.
     * 
     * @param int     $userId
     * @param boolean $optimize
     */
    public function removeByUser($userId, $optimize = false) {
        $hits = $this->__index->find('doctype:note AND userid:' . $userId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }

    /**
     * Remove all Notes written by a user.
     * 
     * @param int     $userId
     * @param boolean $optimize
     */
    public function removeByCourse($courseId, $optimize = false) {
        $hits = $this->__index->find('doctype:note AND courseid:' . $courseId);
        foreach ($hits as $hit)
            $this->__index->delete($hit->getDocument()->id);

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
    }
    
    /**
     * Reindex all notes.
     * 
     * @param boolean $optimize
     */
    public function reindex($optimize = false) {
        // remove all notes from index
        $term = new Zend_Search_Lucene_Index_Term('note', 'doctype');
        $docIds = $this->__index->termDocs($term);
        foreach ($docIds as $id)
            $this->__index->delete($id);

        // get/index all notes from db
        $res = Database::get()->queryArray("SELECT id FROM note");
        foreach ($res as $row) {
            $this->__index->addDocument(self::makeDoc($this->fetch($row->id)));
        }

        if ($optimize)
            $this->__index->optimize();
        else
            $this->__index->commit();
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
