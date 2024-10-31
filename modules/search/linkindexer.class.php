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

class LinkIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a link db row.
     *
     * @global string $urlServer
     * @param  object  $link
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($link) {
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_LINK . '_' . $link->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $link->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_LINK, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $link->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($link->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($link->description)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $link->url, $encoding));

        return $doc;
    }

    /**
     * Fetch a Link from DB.
     *
     * @param  int $linkId
     * @return object - the mysql fetched row
     */
    protected function fetch($linkId) {
        $link = Database::get()->querySingle("SELECT * FROM link WHERE id = ?d", $linkId);
        if (!$link) {
            return null;
        }

        return $link;
    }

    /**
     * Get Term object for locating a unique single link.
     *
     * @param  int $linkId - the link id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($linkId) {
        return new Zend_Search_Lucene_Index_Term('link_' . $linkId, 'pk');
    }

    /**
     * Get Term object for locating all possible links.
     *
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('link', 'doctype');
    }

    /**
     * Get all possible links from DB.
     *
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM link");
    }

    /**
     * Get Lucene query input string for locating all links belonging to a given course.
     *
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:link AND courseid:' . $courseId;
    }

    /**
     * Get all links belonging to a given course from DB.
     *
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d", $courseId);
    }

}
