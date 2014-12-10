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

require_once '../../include/baseTheme.php';
require_once 'include/lib/cronutil.class.php';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $logo; ?></title>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <link href='<?php echo $urlAppend; ?>/install/install.css' rel='stylesheet' type='text/css' />
  </head>
  <body style='background-color: #ffffff;'>
    <div class='container'>
      <p align='center'><img src='<?php echo $urlAppend; ?>/template/classic/img/logo_openeclass.png' alt='logo' /></p>
      <div class='alert' align='center'>
        <p>Processing ...</p>
      </div>
    </div>
  </body>
</html>

<?php

session_write_close();
ignore_user_abort(true);
CronUtil::flush();

if ($uid > 0) { // restrict anonymous access
    set_time_limit(0);
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();
    $idx->queueAsyncProcess();
}