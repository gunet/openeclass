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
            return "Δεν είναι δυνατόν να είναι κενή η παράμετρος: " . $this->display();
        return null;
    }

    function isRequired() {
        return true;
    }

}
