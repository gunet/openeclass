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


/* ===========================================================================
  viewModule.php
  @last update: 05-08-2009 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: Auto to script praktika oloklhrwnei ton kyklo
  zwhs ths html selidas otan perihgoumaste sta scorm paketa.
  Dhladh: o xrhsths zhtaei na paei se allo scorm. Opote
  prwta fortwnoume auto to dummy html kai meta h javascript
  autou tou script leei sto mainFrame na paei ekei pou o
  xrhsths zhthse na paei. Etsi, glitwnoume apo to bug opou
  o javascript kwdikas sthn onunloadpage twn scorm den
  etrexe epeidh den prolabaine, epeidh ananewname kateu8eian
  to mainFrame. Apla pros8esame auth th "stash" edw kai ola
  kylane pleon mia xara.


  @Comments:

  @todo:
  ==============================================================================
 */


$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';

$unitParam = isset($_GET['unit']) ? ('&unit=' . intval($_GET['unit'])) : '';

if (isset($_GET['go']) and strlen($_GET['go']) > 0) {
    $redirect = "../" . js_escape($_GET['go']) . ".php?course=$course_code" . $unitParam;
} else {
    $redirect = "startModule.php?course=$course_code&viewModule_id=" . urlencode($_GET['viewModule_id']) . $unitParam;
}

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<script type=\"text/javascript\">
    <!--//" . "\n";
if (isset($_GET['go']) && strlen($_GET['go']) > 0) {
    echo "parent.parent.window.location.href=\"" . $redirect . "\";" . "\n";
} else {
    echo "parent.parent.mainFrame.location.href=\"" . $redirect . "\";" . "\n";
}
echo "    //-->
    </script>
</head>
<body>
loading ...
</body>
</html>";
