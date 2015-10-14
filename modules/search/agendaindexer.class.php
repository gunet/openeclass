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

class AgendaIndexer extends AbstractIndexer implements ResourceIndexerInterface {

    /**
     * Construct a Zend_Search_Lucene_Document object out of an agenda db row.
     * 
     * @global string $urlServer
     * @param  object  $agenda
     * @return Zend_Search_Lucene_Document
     */
    protected function makeDoc($agenda) {
        global $urlServer;
        $encoding = 'utf-8';

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', Indexer::DOCTYPE_AGENDA . '_' . $agenda->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('pkid', $agenda->id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('doctype', Indexer::DOCTYPE_AGENDA, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Keyword('courseid', $agenda->course_id, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', Indexer::phonetics($agenda->title), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('content', Indexer::phonetics(strip_tags($agenda->content)), $encoding));
        $doc->addField(Zend_Search_Lucene_Field::Text('visible', $agenda->visible, $encoding));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $urlServer . 'modules/agenda/index.php?course=' . course_id_to_code($agenda->course_id), $encoding));

        return $doc;
    }

    /**
     * Fetch an Agenda from DB.
     * 
     * @param  int $agendaId
     * @return object - the mysql fetched row
     */
    protected function fetch($agendaId) {
        $agenda = Database::get()->querySingle("SELECT * FROM agenda WHERE id = ?d", $agendaId);        
        if (!$agenda) {
            return null;
        }

        return $agenda;
    }
    
    /**
     * Get Term object for locating a unique single agenda.
     * 
     * @param  int $agendaId - the agenda id
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForSingleResource($agendaId) {
        return new Zend_Search_Lucene_Index_Term('agenda_' . $agendaId, 'pk');
    }
    
    /**
     * Get Term object for locating all possible agendas.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    protected function getTermForAllResources() {
        return new Zend_Search_Lucene_Index_Term('agenda', 'doctype');
    }
    
    /**
     * Get all possible agendas from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getAllResourcesFromDB() {
        return Database::get()->queryArray("SELECT * FROM agenda");
    }
    
    /**
     * Get Lucene query input string for locating all agendas belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    protected function getQueryInputByCourse($courseId) {
        return 'doctype:agenda AND courseid:' . $courseId;
    }
    
    /**
     * Get all agendas belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    protected function getCourseResourcesFromDB($courseId) {
        return Database::get()->queryArray("SELECT * FROM agenda WHERE course_id = ?d", $courseId);
    }

}
