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

/**
 *  @file record_action.php
 *  @description: Provides an endpoint called periodically from the learning
 *      path viewer to record user actions and update course usage statistics
 */

$require_current_course = true;
include "../../include/baseTheme.php";
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_LP);
