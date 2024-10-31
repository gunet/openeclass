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

require_once '../../include/baseTheme.php';
require_once 'include/lib/cronutil.class.php';

echo "<p>Processing ...</p>";

session_write_close();
ignore_user_abort(true);
CronUtil::flush();

if ($uid > 0) { // restrict anonymous access
    set_time_limit(0);
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();
    $idx->queueAsyncProcess();
}
