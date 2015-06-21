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
load_js('jquery-' . JQUERY_VERSION . '.min.js');

if (isset($_GET['reindex'])) {
    require_once 'modules/search/indexer.class.php';
    Indexer::deleteAll();
    Database::get()->query("DELETE FROM idx_queue");
    Database::get()->queryFunc("SELECT id FROM course", function($r) {
        Database::get()->query("INSERT INTO idx_queue (course_id) VALUES (?d)", $r->id);
    });
}

$head_content .= "
  <script>
    /* <![CDATA[ */

    var langIndexingDone = '" . js_escape($langIndexingDone) . "';
    
    // confirm window closing
    // from https://developer.mozilla.org/en-US/docs/Web/Events/beforeunload
    var confirmClose = function (e) {
        var confirmationMessage = '\o/';
        (e || window.event).returnValue = confirmationMessage;   // Gecko + IE
        return confirmationMessage;                              // Webkit, Safari, Chrome etc.
    };
        
    var doProc = function() {
        jQuery.getJSON('idxproc.php')
        .done(function(data) {
            //console.debug(data);
            $('#idxremaining').html(data.remaining);
            if (data.remaining > 0) {
                setTimeout(doProc, 0);
            } else {
                $('#idxinfo').attr('class', 'success');
                $('#idxresul').html(langIndexingDone);
                window.removeEventListener('beforeunload', confirmClose);
            }
        })
        .fail(function(jqxhr, textStatus, error) {
            //console.debug('jqxhr Request Failed: ' + textStatus + ', ' + error);
        });
    };
    
    $(document).ready(function() {
        window.addEventListener('beforeunload', confirmClose);
        doProc();
    });
    
    /* ]]> */
  </script>";

$tool_content .= "
    <p>$langIndexingAlert1</p>
    <p id='idxresul'>$langIndexingAlert2</p>
    <p>$langIndexingRemain: <span id='idxremaining'></span></p>";

$toolName = $logo;

draw_popup();

