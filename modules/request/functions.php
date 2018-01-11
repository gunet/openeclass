<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

define('REQUEST_STATE_NEW', 1);
define('REQUEST_STATE_ASSIGNED', 2);
define('REQUEST_STATE_LOCKED', 3);
define('REQUEST_STATE_CLOSED', 4);
define('REQUEST_ASSIGNED', 1);
define('REQUEST_WATCHER', 2);

$stateLabels = [
    REQUEST_STATE_NEW => $langRequestStateNew,
    REQUEST_STATE_ASSIGNED => $langRequestStateAssigned,
    REQUEST_STATE_LOCKED => $langRequestStateLocked,
    REQUEST_STATE_CLOSED => $langRequestStateClosed
];

function getWatchers($rid, $type) {
    return Database::get()->queryArray("SELECT user_id
        FROM request_watcher
        WHERE request_id = ?d AND type = ?d
        ORDER BY id", $rid, $type);
}

