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

function modalConfirmation($id, $labelId, $title, $body, $cancelId, $okId) {
    global $langCancel, $langOk;
    return <<<htmlEOF
<div class='modal fade' id='$id' tabindex='-1' role='dialog' aria-labelledby='$labelId' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h4 class='modal-title' id='$labelId'>$title</h4>
            </div>
            <div class='modal-body'><p>$body</p></div>
            <div class='modal-footer'>
                <button id='$cancelId' type='button' class='btn btn-default'>$langCancel</button>
                <button id='$okId' type='button' class='btn btn-primary'>$langOk</button>
            </div>
        </div>
    </div>
</div>
htmlEOF;
}