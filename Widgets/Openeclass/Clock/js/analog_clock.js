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
 */                /*
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

$(document).ready(function() {
                    setInterval( function() {
                      var seconds = new Date().getSeconds();
                      var sdegree = seconds * 6;
                      var srotate = 'rotate(' + sdegree + 'deg)';
                      $('.sec').css({'-moz-transform' : srotate, '-webkit-transform' : srotate});
                    }, 1000 );
                    setInterval( function() {
                        var hours = new Date().getHours();
                        var mins = new Date().getMinutes();
                        var hdegree = hours * 30 + (mins / 2);
                        var hrotate = 'rotate(' + hdegree + 'deg)';
                        $('.hour').css({'-moz-transform' : hrotate, '-webkit-transform' : hrotate});
                    }, 1000 );
                    setInterval( function() {
                        var mins = new Date().getMinutes();
                        var mdegree = mins * 6;
                        var mrotate = 'rotate(' + mdegree + 'deg)';
                        $('.min').css({'-moz-transform' : mrotate, '-webkit-transform' : mrotate});
                    }, 1000 );
                });


