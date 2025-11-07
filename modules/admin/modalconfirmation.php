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

function modalConfirmation($id, $labelId, $title, $body, $cancelId, $okId) {
    global $langCancel, $langAnalyticsConfirm;
    return <<<htmlEOF
<div class='modal fade' id='$id' tabindex='-1' role='dialog' aria-labelledby='$labelId' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='$labelId'>$title</div>
            </div>
            <div class='modal-body'><p>$body</p></div>
            <div class='modal-footer'>
                <button id='$cancelId' type='button' class='btn cancelAdminBtn'>$langCancel</button>
                <button id='$okId' type='button' class='btn submitAdminBtn ms-1'>$langAnalyticsConfirm</button>
            </div>
        </div>
    </div>
</div>
htmlEOF;
}
