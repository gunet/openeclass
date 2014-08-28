<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

require_once 'abstractbaseindexer.class.php';

abstract class AbstractIndexer extends AbstractBaseIndexer {
    
    /**
     * Get Lucene query input string for locating all resources belonging to a given course.
     * 
     * @param  int $courseId - the given course id
     * @return string        - the string that can be used as Lucene query input
     */
    abstract protected function getQueryInputByCourse($courseId);
    
    /**
     * Get all resources belonging to a given course from DB.
     * 
     * @param  int   $courseId - the given course id
     * @return array           - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    abstract protected function getCourseResourcesFromDB($courseId);
    
    /**
     * Remove all Resources belonging to a Course.
     * 
     * @param int     $courseId - the given course id
     * @param boolean $optimize - whether to optimize after removing
     */
    public function removeByCourse($courseId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $hits = $this->__index->find($this->getQueryInputByCourse($courseId));
        foreach ($hits as $hit) {
            $this->__index->delete($hit->id);
        }

        $this->optimizeOrCommit($optimize);
    }
    
    /**
     * Store all Resources belonging to a Course.
     * 
     * @param int     $courseId - the given course id
     * @param boolean $optimize - whether to optimize after removing
     */
    public function storeByCourse($courseId, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        // delete existing resources from index
        $this->removeByCourse($courseId);

        // add the resources back to the index
        $res = $this->getCourseResourcesFromDB($courseId);
        foreach ($res as $row) {
            $this->__index->addDocument($this->makeDoc($row));
        }
        
        $this->optimizeOrCommit($optimize);
    }
    
}