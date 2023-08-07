<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * @file commondocs.php
 * @brief Admin form for uploading common documents (e.g. shared docs among all courses)
 */



if(isset($_GET['common_docs']) or isset($_GET['common_program'])){
    define('MENTORING_COMMON_DOCUMENTS', TRUE);
}elseif(isset($_GET['mydocs']) or isset($_GET['program'])){
    define('MENTORING_MYDOCS',TRUE);
}else{
     define('MENTORING_GROUP_DOCUMENTS',TRUE);
}

if(isset($_GET['upload'])){
    require_once '../../../../mentoring/programs/group/document/upload.php';
}elseif(isset($_GET['new'])){
    require_once '../../../../mentoring/programs/group/document/new.php';
}else{
    require_once '../../../../mentoring/programs/group/document/index.php';
}
