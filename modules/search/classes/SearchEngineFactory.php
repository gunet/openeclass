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

require_once 'SearchEngineInterface.php';
require_once 'SolrSearchEngine.php';
require_once 'LuceneSearchEngine.php';

class SearchEngineFactory {

    public static function create(): SearchEngineInterface {
        return get_config('ext_solr_enabled')
            ? new SolrSearchEngine()
            : new LuceneSearchEngine();
    }

}
