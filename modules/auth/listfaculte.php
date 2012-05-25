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

include '../../include/baseTheme.php';

$TBL_HIERARCHY = 'hierarchy';
require_once '../../include/lib/hierarchy.class.php';
$tree = new hierarchy();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$nameTools = $langSelectFac;


$tool_content .= "<table class='tbl_border' width=\"100%\">";

$initopen = $tree->buildJSTreeInitOpen();
                
                $head_content .= <<<hContent
<script type="text/javascript">

$(function() {
        
    $( "#js-tree" ).jstree({
        "plugins" : ["html_data", "themes", "ui", "cookies", "types", "sort"],
        "core" : {
            "animation": 300,
            "initially_open" : [$initopen]
        },
        "themes" : {
            "theme" : "eclass",
            "dots" : true,
            "icons" : false
        },
        "ui" : {
            "select_limit" : 1
        },
        "cookies" : {
            "save_selected": false
        },
        "types" : {
            "types" : {
                "nosel" : {
                    "hover_node" : false,
                    "select_node" : false
                }
            }
        },
        "sort" : function (a, b) { 
            priorityA = this._get_node(a).attr("tabindex");
            priorityB = this._get_node(b).attr("tabindex");
            
            if (priorityA == priorityB)
                return this.get_text(a) > this.get_text(b) ? 1 : -1;
            else
                return priorityA < priorityB ? 1 : -1;
        }
    })
    .bind("select_node.jstree", function (event, data) { document.location.href='opencourses.php?fc=' + data.rslt.obj.attr("id"); });
    
});

</script>
hContent;
                
$tool_content .= "<tr><td><div id='js-tree'>". $tree->buildHtmlUl(array(), 'id', null, 'AND node.allow_course = true', false, true) ."</div></td></tr>";

$tool_content .= "</table>";

draw($tool_content, (isset($uid) and $uid)? 1: 0, null, $head_content);
