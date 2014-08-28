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

$tool_content = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης Open eClass</title>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <link href='../../install/install.css' rel='stylesheet' type='text/css' />
  </head>
  <body>
  <div class='install_container'>
  <p align='center'><img src='../../template/classic/img/logo_openeclass.png' alt='logo' /></p>
  <div class='alert' align='center'>
  <p>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης Open eClass δεν λειτουργεί.</p>
  <p>Πιθανό πρόβλημα με τα δικαιώματα του καταλόγου της μηχανής αναζήτησης (courses/idx/).</p>
  <p>Παρακαλούμε ενημερώστε το διαχειριστή της πλατφόρμας.</p>
  </div>
  </div>
  </body>
</html>";

echo $tool_content;

exit();
