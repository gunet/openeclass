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

interface ResourceIndexerInterface {

    /**
     * Store a Resource in the Index.
     *
     * @param  int     $id       - the resource id
     * @param  boolean $optimize - whether to optimize after storing
     */
    public function store($id, $optimize);

    /**
     * Remove a Resource from the Index.
     *
     * @param int     $id         - the resource id
     * @param boolean $existCheck - whether to checking existance before removing
     * @param boolean $optimize   - whether to optimize after removing
     */
    public function remove($id, $existCheck, $optimize);

    /**
     * Reindex all resources.
     *
     * @param boolean $optimize - whether to optimize after reindexing
     * @deprecated
     */
    //public function reindex();
}
