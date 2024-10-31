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

class VideolinkIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a VideoLink db row.
     *
     * @global string $urlServer
     * @param  object  $vlink
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($vlink) {
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_VIDEOLINK . '_' . $vlink->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $vlink->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_VIDEOLINK, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $vlink->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($vlink->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($vlink->description), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $vlink->url, $encoding));

        return $doc;
    }

    /**
     * Fetch a VideoLink from DB.
     *
     * @param  int $vlinkId
     * @return object - the mysql fetched row
     */
    protected function fetch($vlinkId) {

        $vlink = Database::get()->querySingle("SELECT * FROM videolink WHERE id = ?d", $vlinkId);
        if (!$vlink) {
            return null;
        }

        return $vlink;
    }

    /**
     * Get Term object for locating a unique single videolink.
     *
     * @param  int $vlinkId - the videolink id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($vlinkId) {
        return new Zend_Search_Lucene_Index_Term('vlink_' . $vlinkId, 'pk');
    }

    /**
     * Get Term object for locating all possible videolinks.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('vlink', 'doctype');
    }

    /**
     * Get all possible videolinks from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM videolink");
    }

    /**
     * Get Lucene query input string for locating all videolinks belonging to a given course.
     *
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:vlink AND courseid:' . $courseId;
    }

    /**
     * Get all videolinks belonging to a given course from DB.
     *
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM videolink WHERE course_id = ?d", $courseId);
    }

}
