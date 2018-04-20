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

/**
  @file lib.url.php
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */

/**
 * add a GET request variable to the given URL
 * @param string url url
 * @param string name name of the variable
 * @param string value value of the variable
 * @return string url
 */
function add_request_variable_to_url(&$url, $name, $value) {
    if (strstr($url, "?") != false) {
        $url .= "&amp;$name=$value";
    } else {
        $url .= "?$name=$value";
    }

    return $url;
}

/**
 * add a GET request variable list to the given URL
 * @param string url url
 * @param array variableList list of the request variables to add
 * @return string url
 */
function add_request_variable_list_to_url(&$url, $variableList) {
    foreach ($variableList as $name => $value) {
        $url = add_request_variable_to_url($url, $name, $value);
    }

    return $url;
}