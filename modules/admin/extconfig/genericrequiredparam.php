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

/**
 * Description of genericrequiredparam
 *
 * @author teras
 */
require_once 'genericparam.php';

class GenericRequiredParam extends GenericParam {

    public function validateParam() {

        $value = $this->value();
        if (is_null($value) || trim($value) === '')
            return "$GLOBALS[langEmptyParameter]: " . $this->display();
        return null;
    }

    function isRequired() {
        return true;
    }
}
