<?php

/* ========================================================================
 * Open eClass 3.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @file not_installed.php
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @abstract Output a message to the user's browser to inform them that Open eClass
 * is not installed.
 */

require_once 'template/template.inc.php';

function installation_error($eng_msg, $el_msg) {
    $t = new Template('template/modern');

    $t->set_file('fh', 'theme.html');
    $t->set_block('fh', 'mainBlock', 'main');
    $t->set_block('mainBlock', 'leftNavBlock', 'delete');
    $t->set_block('mainBlock', 'sideBarBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedInBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedOutBlock', 'delete');
    $t->set_block('mainBlock', 'toolTitleBlock', 'delete');
    $t->set_block('mainBlock', 'pageTitleBlock', 'delete');
    $t->set_block('mainBlock', 'statusSwitchBlock', 'delete');
    $t->set_block('mainBlock', 'breadCrumbs', 'delete');
    $t->set_block('mainBlock', 'normalViewOpenDiv', 'delete');
    $t->set_var('template_base', 'template/modern');
    $t->set_var('PAGE_TITLE', 'Open eClass eLearning Platform / Πλατφόρμα Τηλεκπαίδευσης Open eClass');
    $t->set_var('TOOL_CONTENT', "
        <div class='row'>
            <div class='col-md-12'>
                <div class='alert alert-warning'>
                <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                    <p>The <strong>Open eClass</strong> asynchronous elearning platform is not functional.</p>$eng_msg</span></div>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-12'>
                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span><p>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης <strong>Open eClass</strong> δεν λειτουργεί.</p>                                
                $el_msg</span></div>
            </div>
        </div>
    ");

    $t->parse('main', 'mainBlock', false);
    $t->pparse('Output', 'fh');
    exit;
}
