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

class VideoIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of a video db row.
     * 
     * @global string $urlServer
     * @param  object  $video
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($video) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_VIDEO . '_' . $video->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $video->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_VIDEO, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $video->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($video->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics($video->description), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/video/file.php?course=' . course_id_to_code($video->course_id) . '&amp;id=' . $video->id, $encoding));

        return $doc;
    }

    /**
     * Fetch a Video from DB.
     * 
     * @param  int $videoId
     * @return object - the mysql fetched row
     */
    protected function fetch($videoId) {
        $video = Database::get()->querySingle("SELECT * FROM video WHERE id = ?d", $videoId);        
        if (!$video) {
            return null;
        }

        return $video;
    }
    
    /**
     * Get Term object for locating a unique single video.
     * 
     * @param  int $videoId - the video id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($videoId) {
        return new Zend_Search_Lucene_Index_Term('video_' . $videoId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible videos.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('video', 'doctype');
    }
    
    /**
     * Get all possible videos from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM video");
    }
    
    /**
     * Get Lucene query input string for locating all videos belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:video AND courseid:' . $courseId;
    }
    
    /**
     * Get all videos belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM video WHERE course_id = ?d", $courseId);
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
            $queryStr .= ') AND courseid:' . $data['course_id'] . ' AND doctype:video';
            return $queryStr;
        }

        return null;
    }

}
