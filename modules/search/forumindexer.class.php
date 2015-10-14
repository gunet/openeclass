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

class ForumIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a forum db row.
     * 
     * @global string $urlServer
     * @param  object  $forum
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($forum) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_FORUM . '_' . $forum->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $forum->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_FORUM, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $forum->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($forum->name), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($forum->desc), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/forum/viewforum.php?course=' . course_id_to_code($forum->course_id)
                        . '&amp;forum=' . $forum->id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Forum from DB.
     * 
     * @param  int $forumId
     * @return object - the mysql fetched row
     */
    protected function fetch($forumId) {
        $forum = Database::get()->querySingle("SELECT f.* FROM forum f 
                    JOIN forum_category fc ON f.cat_id = fc.id 
                    WHERE fc.cat_order >= 0 AND f.id = ?d", $forumId);
        if (!$forum) {
            return null;
        }

        return $forum;
    }
    
    /**
     * Get Term object for locating a unique single forum.
     * 
     * @param  int $forumId - the forum id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($forumId) {
        return new Zend_Search_Lucene_Index_Term('forum_' . $forumId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible forums.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('forum', 'doctype');
    }
    
    /**
     * Get all possible forums from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT f.* 
            FROM forum f 
            JOIN forum_category fc ON f.cat_id = fc.id 
            WHERE fc.cat_order >= 0");
    }
    
    /**
     * Get Lucene query input string for locating all forums belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:forum AND courseid:' . $courseId;
    }
    
    /**
     * Get all forums belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT f.* 
            FROM forum f 
            JOIN forum_category fc ON f.cat_id = fc.id 
            WHERE fc.cat_order >= 0
            AND f.course_id = ?d", $courseId);
    }

}
