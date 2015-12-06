<?php

/* ========================================================================
 * Open eClass 3.3
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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

$tool_content .= "
        <div class='form-group'>
            <label for='pop3host' class='col-sm-2 control-label'>$langpop3host:</label>
            <div class='col-sm-10'>
                <input class='form-control' name='pop3host' id='pop3host' type='text' value='" . q($auth_data['pop3host']) . "'>
            </div>
        </div>
        <div class='form-group'>
            <label for='pop3port' class='col-sm-2 control-label'>$langpop3port:</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' value='110' name='pop3port' id='pop3port' disabled>
            </div>
        </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);

