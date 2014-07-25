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

abstract class AbstractIndexer {
    
    protected $__indexer = null;
    protected $__index = null;
    
    /**
     * Constructor. You can optionally use an already instantiated Indexer object if there is one.
     * 
     * @param Indexer $idxer - optional indexer object
     */
    public function __construct($idxer = null) {
        if ($idxer == null) {
            $this->__indexer = new Indexer();
        } else {
            $this->__indexer = $idxer;
        }

        $this->__index = $this->__indexer->getIndex();
    }
    
    /**
     * 
     * @param boolean $optimize - whether to optimize after store/remove action or just commit
     */
    protected function optimizeOrCommit($optimize) {
        if ($optimize) {
            $this->__index->optimize();
        } else {
            $this->__index->commit();
        }
    }
    
    /**
     * Construct a Zend_Search_Lucene_Document object out of a resource anonymous object with property names that correspond to the column names (DB fetched).
     * 
     * @param  object $resource
     * @return Zend_Search_Lucene_Document
     */
    abstract protected function makeDoc($resource);
    
    /**
     * Fetch a Resource from DB.
     * 
     * @param  int    $id - the resource item id
     * @return object     - the DB fetched anonymous object with property names that correspond to the column names
     */
    abstract protected function fetch($id);
    
    /**
     * Get Term object for locating a unique single resource.
     * 
     * @param  int $id - the resource id
     * @return Zend_Search_Lucene_Index_Term
     */
    abstract protected function getTermForSingleResource($id);
    
    /**
     * Get Term object for locating all possible resources.
     * 
     * @return Zend_Search_Lucene_Index_Term
     */
    abstract protected function getTermForAllResources();
    
    /**
     * Get all possible resources from DB.
     * 
     * @return array - array of DB fetched anonymous objects with property names that correspond to the column names
     */
    abstract protected function getAllResourcesFromDB();
    
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
     * Store a Resource in the Index.
     * 
     * @param  int     $id       - the resource id
     * @param  boolean $optimize - whether to optimize after storing
     */
    public function store($id, $optimize = false) {
        $resource = $this->fetch($id);
        if (!$resource) {
            return;
        }

        // delete existing resource from index
        $this->remove($id, false, false);

        // add the forum post back to the index
        $this->__index->addDocument($this->makeDoc($resource));

        $this->optimizeOrCommit($optimize);
    }
    
    /**
     * Remove a Resource from the Index.
     * 
     * @param int     $id         - the resource id
     * @param boolean $existCheck - whether to checking existance before removing
     * @param boolean $optimize   - whether to optimize after removing
     */
    public function remove($id, $existCheck = false, $optimize = false) {
        if ($existCheck) {
            $resource = $this->fetch($id);
            if (!$resource) {
                return;
            }
        }

        $docIds = $this->__index->termDocs($this->getTermForSingleResource($id));
        foreach ($docIds as $id) {
            $this->__index->delete($id);
        }

        $this->optimizeOrCommit($optimize);
    }
    
    /**
     * Reindex all resources.
     * 
     * @param boolean $optimize - whether to optimize after reindexing
     */
    public function reindex($optimize = false) {
        // remove all forum posts from index
        $docIds = $this->__index->termDocs($this->getTermForAllResources());
        foreach ($docIds as $id) {
            $this->__index->delete($id);
        }

        // get/index all resources from db
        $res = $this->getAllResourcesFromDB();
        foreach ($res as $row) {
            $this->__index->addDocument($this->makeDoc($row));
        }

        $this->optimizeOrCommit($optimize);
    }
    
    /**
     * Remove all Resources belonging to a Course.
     * 
     * @param int     $courseId - the given course id
     * @param boolean $optimize - whether to optimize after removing
     */
    public function removeByCourse($courseId, $optimize = false) {
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