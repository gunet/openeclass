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

function list_docs($id = NULL, $subsystem = NULL) {
    global $course_code;
    
    load_js('jstree3');
    
    if (is_null($subsystem)) { //main documents
        $div_id = 'jstree_doc';
        $sys = '';
        $input_id = 'docs';
    } elseif ($subsystem == 'mydocs') { //my documents
        $div_id = 'jstree_mydoc';
        $sys = '&subsystem=mydocs';
        $input_id = 'mydocs';
    }
    
    $ret_str = '<div id="'.$div_id.'"></div>';
    
    $ret_str .= "<script>
                   $(function () {
                     $('#$div_id').jstree({
                       'types' : {
                         'folder' : {
                           'icon' : 'fa fa-folder'
                         },
                         'file' : {
                           'icon' : 'fa fa-file'
                         }
                       },
                       'core': {
                         'themes': {
                           'name': 'proton',
                           'responsive': true
                         },
	                     'data' : {
                           'url' : 'load_doc.php?course=$course_code$sys',
                           'data' : function (node) {
                             return { 'id' : node.id };
                           }
                         }
                       },
                       'plugins' : [ 'checkbox', 'types' ]
                     });
                     
                     $('#wall_form').on('submit', function(e) {
                        var selectedElms = $('#$div_id').jstree('get_selected', true);
                        var concat_ids = '';
                        $.each(selectedElms, function() {
                          if (this.type == 'file') {
                            concat_ids += this.id + ',';
                          }
                        });
                        $('#$input_id').val(concat_ids.slice(0,-1));
                     });
                   });
                 </script>";
    
    
    if (!is_null($id)) {
        $doc_res = Database::get()->queryArray("SELECT res_id FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'document');
        if (count($doc_res)) {
            $ret_str .= "<script>
                           $('#$div_id').on('ready.jstree', function (e, data) {";
            foreach ($doc_res as $doc) {
                $ret_str .= "$('#$div_id').jstree('select_node', '".$doc->res_id."');";
            }
            $ret_str .= "  });
                         </script>";
        }
    }
    
    return $ret_str;
}
