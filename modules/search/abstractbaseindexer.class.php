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

require_once 'Zend/Search/Lucene/Exception.php';

abstract class AbstractBaseIndexer {
    
    protected $__indexer = null;
    protected $__index = null;
    
    /**
     * Constructor. You can optionally use an already instantiated Indexer object if there is one.
     * 
     * @param Indexer $idxer - optional indexer object
     */
    public function __construct($idxer = null) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
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
     * Store a Resource in the Index.
     * 
     * @param  int     $id       - the resource id
     * @param  boolean $optimize - whether to optimize after storing
     */
    public function store($id, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
        $resource = $this->fetch($id);
        if (!$resource) {
            return;
        }
        
        try {
            $this->remove($id, false, false);                       // delete existing resource from index
            $this->__index->addDocument($this->makeDoc($resource)); // add the resource back to the index
            $this->optimizeOrCommit($optimize);
        } catch (Zend_Search_Lucene_Exception $e) {
            $this->handleWriteErrors($e);
        }
    }
    
    /**
     * Remove a Resource from the Index.
     * 
     * @param int     $id         - the resource id
     * @param boolean $existCheck - whether to checking existance before removing
     * @param boolean $optimize   - whether to optimize after removing
     */
    public function remove($id, $existCheck = false, $optimize = false) {
        if (!get_config('enable_indexing')) {
            return;
        }
        
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

        try {
            $this->optimizeOrCommit($optimize);
        } catch (Zend_Search_Lucene_Exception $e) {
            $this->handleWriteErrors($e);
        }
    }
    
    /**
     * Reindex all resources.
     * 
     * @param boolean $optimize - whether to optimize after reindexing
     * @deprecated
     */
//    public function reindex($optimize = false) {
//        if (!get_config('enable_indexing')) {
//            return;
//        }
//        
//        // remove all resources from index
//        $docIds = $this->__index->termDocs($this->getTermForAllResources());
//        foreach ($docIds as $id) {
//            $this->__index->delete($id);
//        }
//
//        // get/index all resources from db
//        $res = $this->getAllResourcesFromDB();
//        foreach ($res as $row) {
//            $this->__index->addDocument($this->makeDoc($row));
//        }
//
//        $this->optimizeOrCommit($optimize);
//    }
    
    /**
     * Handle Write Exceptions.
     * 
     * @global string                       $urlAppend
     * @param  Zend_Search_Lucene_Exception $e
     */
    protected function handleWriteErrors($e) {
        global $tool_content, $pageName, $errorMessage;
        if (preg_match("/too many open files/i", $e->getMessage())) {
            $pageName = 'Open eClass Asynchronous eLearning Platform';
            $tool_content .= "
              <p>The Open eClass asynchronous eLearning platform is not operational.</p>
              <p>This is caused by a possible maximum open files (ulimit) problem for the search engine indexing directory (courses/idx/).</p>
              <p>Please inform the platform administrator.</p>";
            draw_popup();
            exit();
        } else {
            $errorMessage = $e->getMessage();
            require_once 'fatal_error.php';
        }
    }
    
}
