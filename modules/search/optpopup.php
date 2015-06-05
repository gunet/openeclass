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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/cronutil.class.php';

$pageName = $logo;
$tool_content .= "
  <p>$langIndexingOptAlert1</p>
  <p>$langIndexingOptAlert2</p>";
draw_popup();

session_write_close();
ignore_user_abort(true);
CronUtil::flush();

require_once 'modules/search/indexer.class.php';
$idx = new Indexer();
set_time_limit(0);
$idx->getIndex()->optimize();
