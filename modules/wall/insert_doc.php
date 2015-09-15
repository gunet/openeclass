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

function list_docs($id = NULL) {
    global $course_code;
    
    load_js('jstree3');
    
    $ret_str = '<div id="jstree_doc"></div>';
    
    $ret_str .= "<script>
                   $(function () {
                     $('#jstree_doc').jstree({
                       'core': {
                         'themes': {
                           'name': 'proton',
                           'responsive': true
                         },
	                     'data' : {
                           'url' : 'load_doc.php?course=$course_code',
                           'data' : function (node) {
                             return { 'id' : node.id };
                           }
                         }
                       },
                       'plugins' : [ 'checkbox' ]
                     });
                   });
                 </script>";
    
    return $ret_str;
}
