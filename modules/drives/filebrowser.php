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

require_once 'clouddrive.php';
$drive = CloudDriveManager::getSessionDrive();

echo '<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>jQuery File Tree Demo</title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <script src="jquery_filetree/jquery.js" type="text/javascript"></script>
        <script src="jquery_filetree/jqueryFileTree.js" type="text/javascript"></script>
        <link href="jquery_filetree/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
        <script type="text/javascript">
            $(document).ready(function () {
              $(\'#fileTreeDemo\').fileTree({root: \'/\', script: \'fileprovider.php?' . $drive->getDriveDefaultParameter() . '\', loadMessage: \'Please wait...\'}, function (file) {
                parent.$.colorbox.close();
                parent.callback(file);
              });
            });
        </script>
    </head>
    <body>
        <div id="fileTreeDemo" class="browsearea"></div>
    </body>
</html>';
