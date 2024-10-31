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

function Rate(widget, rid, rtype, value, url) {
    if (window.XMLHttpRequest){
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            response = JSON.parse(xmlhttp.responseText);
            document.getElementById('rate_'+rid+'_up').innerHTML = response[0];
            document.getElementById('rate_'+rid+'_img').src = response[3];
            if (response[1] == 'ins') {
                document.getElementById('rate_msg_'+rid).innerHTML = response[2];
            } else if (response[1] == 'del' ) {
            	document.getElementById('rate_msg_'+rid).innerHTML = '';
            }
        }
    }

    xmlhttp.open("GET",url+"?widget="+widget+"&rid="+rid+"&rtype="+rtype+"&value="+value,true);

    xmlhttp.send();
}
