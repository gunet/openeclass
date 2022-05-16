<?php

$require_admin = true;
require_once '../include/baseTheme.php';
load_js('jquery-' . JQUERY_VERSION . '.min.js');

//set_config('upgrade_begin', time());

$head_content .= "
  <script>    
    var success_message =  '" . js_escape($langUpgradeSuccess) . "' + ' ' + '" . js_escape($langUpgReady) . "';
    // confirm window closing
    // from https://developer.mozilla.org/en-US/docs/Web/Events/beforeunload
    var confirmClose = function (e) {
        var confirmationMessage = '\o/';
        (e || window.event).returnValue = confirmationMessage;   // Gecko + IE
        return confirmationMessage;                              // Webkit, Safari, Chrome etc.
    };
            
    var doProc = function() {        
        jQuery.getJSON('upgrade_db.php')
        .done(function(data) {
            console.debug(data);
            status_string = data.status;
            error_string = data.error;
            message_string = data.message;
            if (status_string !== 0) {                
                $('#info_message').html(message_string);
                if (error_string) {
                    $('div').removeClass('alert-info');                    
                    $('#error_message').addClass('alert').addClass('alert-danger');                    
                    $('#error_message').html(error_string);                    
                }                
                setTimeout(doProc, 0);
            } else {
                $('#header_message').remove();
                $('#info_message').html(message_string);
                $('#success_message').html(success_message);
                if (error_string) {
                    $('div').removeClass('alert-info');                                        
                    $('#error_message').addClass('alert').addClass('alert-danger');
                    $('#error_message').html(error_string);
                    
                }
                window.removeEventListener('beforeunload', confirmClose);
            }
        })
        .fail(function(jqxhr, textStatus, error) {
            console.debug('jqxhr Request Failed: ' + textStatus + ', ' + error);
        });
    };
    
    $(document).ready(function() {
        window.addEventListener('beforeunload', confirmClose);
        doProc();
    });
    
  </script>";

$toolName = $logo;

$tool_content .= "<div id='header_message'>";
$tool_content .= "<p>$langUpgradeDBInfoMessage</p>";
$tool_content .= "<p style='margin-top: 10px;'>$langUpgradePopUpCloseWarning</p>";
$tool_content .= "</div>";
$tool_content .= "<p style='margin-top: 20px;'><span id='info_message'></span></p>";
$tool_content .= "<div id='success_message' style='margin-top: 20px;'></div>";
$tool_content .= "<div id='error_message'></div>";

draw_popup();
