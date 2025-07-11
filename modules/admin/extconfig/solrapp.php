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

require_once 'genericrequiredparam.php';

class SolrApp extends ExtApp {

    const SOLRURL = "url";
    const SOLRDEFAULTURL = "http://127.0.0.1:8983/solr/eclass_index/";
    const NAME = "Solr";

    public function __construct() {
        parent::__construct();
        $this->registerParam(new GenericRequiredParam($this->getName(), "Solr URL", self::SOLRURL, self::SOLRDEFAULTURL));
    }

    public function getDisplayName() {
        return self::NAME;
    }

    public function getShortDescription() {
        return $GLOBALS['langSolrShortDescription'];
    }

    public function getLongDescription() {
        return $GLOBALS['langSolrLongDescription'];
    }

    public function getConfigUrl() {
        return 'modules/admin/solrconf.php';
    }

}
