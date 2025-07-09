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

class SearchResult {

    public string $pk;
    public string $pkid;
    public string $doctype;
    public string $visible;
    public object $raw;

    public function __construct(string $pk, string $pkid, string $doctype, string $visible, object $raw) {
        $this->pk = $pk;
        $this->pkid = $pkid;
        $this->doctype = $doctype;
        $this->visible = $visible;
        $this->raw = $raw;
    }

}
