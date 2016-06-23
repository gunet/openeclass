<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file chat.php
 * @brief Main script for chat module
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'Mindmap';

require_once '../../include/baseTheme.php';


$coursePath = $webDir . '/courses/';
    $fileChatName = $coursePath . $course_code . '/chat.txt';
    $tmpArchiveFile = $coursePath . $course_code . '/tmpChatArchive.txt';

    $nick = uid_to_name($uid);

// How many lines to show on screen
    define('MESSAGE_LINE_NB', 40);
// How many lines to keep in temporary archive
// (the rest are in the current chat file)
    define('MAX_LINE_IN_FILE', 80);

    if ($GLOBALS['language'] == 'el') {
        $timeNow = date("d-m-Y / H:i", time());
    } else {
        $timeNow = date("Y-m-d / H:i", time());
    }

    if (!file_exists($fileChatName)) {
        $fp = fopen($fileChatName, 'w') or die('<center>$langChatError</center>');
        fclose($fp);
    }

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_MINDMAP);
/* * *********************************** */

$toolName = $langMindmap;
// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='alert alert-danger'>$langNoGuest</div>";
    draw($tool_content, 2, 'mindmap');
}

$head_content .= '

<link type="text/css" rel="stylesheet" href="jsmind.css" />

    <style type="text/css">
        li{margin-top:2px; margin-bottom:2px;}
        button.jsmind{width:140px;}
        select{width:140px;}
        #layout{width:1230px;}
        #jsmind_nav{width:240px;height:600px;border:solid 1px #ccc;overflow:auto;float:left; margin-left:10px;}
        .file_input{width:100px;}
        button.sub{width:100px;}

        #jsmind_container{
            float:left;
            width:700px;
            height:600px;
            border:solid 1px #ccc;
            background:#f4f4f4;
        }
    </style>';




$tool_content .= "


<div id='layout'>
    <div id='jsmind_nav'>
        <div>1. $langOpenMind</div>
        <ol type='A'>
            <li><button class='jsmind' onclick='open_json();'>$langOpenEx</button></li>
            
        </ol>
        </ol>
        <div>2. $langSelect &amp; $langToggle</div>
        <ol type='A'>
            <li><button class='jsmind' onclick='select_node();'>$langSelectNode</button></li>
            <li><button class='jsmind' onclick='one_click();'>$langClickNode</button></li>
            <li><button class='jsmind' onclick='show_selected();'>$langSelectedNode</button></li>
            <li><button class='jsmind' onclick='toggle_node();'>$langToggleNode</button></li>
        </ol>
        <div>3. $langEditMind</div>
        <ol type='A'>
            <li><button class='jsmind' onclick='toggle_editable(this);'>$langEditDis</button></li>
            <li><button class='jsmind' onclick='add_node();'>$langAddNode</button></li>
            <li><button class='jsmind' onclick='dbl_click();'>$langDblClickNode</button></li>
            <li><button class='jsmind' onclick='move_node();'>$langMoveNode</button></li>
            <li><button class='jsmind' onclick='move_to_first();'>$langMoveTFirstNode</button></li>
            <li><button class='jsmind' onclick='move_to_last();'>$langMoveTLastNode</button></li>
            <li><button class='jsmind' onclick='remove_node();'>$langRemoveNode</button></li>
        </ol>
        <div>4. $langThemes</div>
        <ol type='A'>
        <li>
        <select onchange='set_theme(this.value);'>
            <option value=''>default</option>
            <option value='primary'>primary</option>
            <option value='warning'>warning</option>
            <option value='danger'>danger</option>
            <option value='success'>success</option>
            <option value='info'>info</option>
            <option value='greensea' selected='selected'>greensea</option>
            <option value='nephrite'>nephrite</option>
            <option value='belizehole'>belizehole</option>
            <option value='wisteria'>wisteria</option>
            <option value='asphalt'>asphalt</option>
            <option value='orange'>orange</option>
            <option value='pumpkin'>pumpkin</option>
            <option value='pomegranate'>pomegranate</option>
            <option value='clouds'>clouds</option>
            <option value='asbestos'>asbestos</option>
        </select>
        </li>
        </ol>
        <div>5. $langSave</div>
        <ol type='A'>
		        <li><button class='sub' onclick='screen_shot();'>$langScreenshot</button></li>
                <li><button class='sub' onclick='save_file();'>$langSaveFile</button></li>";
				
				
if($is_editor)	{			
$tool_content .="<li><button class='sub' onclick='open_file();'>$langSaveInDoc</button></li>";				
				
}				
				
				
$tool_content .="<li><button class='sub' onclick='open_file();'>$langOpenFile</button></li>
                <li><input id='file_input' class='sub' type='file'/></li>                
        </ol>
    </div>
    <div id='jsmind_container'></div>
</div>";
   

$tool_content .= '   
   
	<script type="text/javascript" src="jsmind.js"></script>
	<script type="text/javascript" src="jsmind.draggable.js"></script>
	<script type="text/javascript" src="jsmind.screenshot.js"></script>
	<script type="text/javascript">
    var _jm = null;
    function open_empty(){
        var options = {
            container:"jsmind_container",
            theme:"greensea",
            editable:true
        }
        _jm = jsMind.show(options);
        // _jm = jsMind.show(options,mind);
    }

    function open_json(){
        var mind = {
            "meta":{
                "name":"jsMind remote",
                "author":"hizzgdev@163.com",
                "version":"0.2"
            },
            "format":"node_tree",
            "data":{"id":"root","topic":"jsMind","children":[
                {"id":"easy","topic":"Easy","direction":"left","children":[
                    {"id":"easy1","topic":"Easy to show"},
                    {"id":"easy2","topic":"Easy to edit"},
                    {"id":"easy3","topic":"Easy to store"},
                    {"id":"easy4","topic":"Easy to embed"}
                ]},
                {"id":"open","topic":"Open Source","direction":"right","children":[
                    {"id":"open1","topic":"on GitHub"},
                    {"id":"open2","topic":"BSD License"}
                ]},
                {"id":"powerful","topic":"Powerful","direction":"right","children":[
                    {"id":"powerful1","topic":"Base on Javascript"},
                    {"id":"powerful2","topic":"Base on HTML5"},
                    {"id":"powerful3","topic":"Depends on you"}
                ]},
                {"id":"other","topic":"test node","direction":"left","children":[
                    {"id":"other1","topic":"I m from local variable"},
                    {"id":"other2","topic":"I can do everything"}
                ]}
            ]}
        }
        _jm.show(mind);
    }



    function screen_shot(){
        _jm.shoot();
    }

    function show_data(){
        var mind_data = _jm.get_data();
        var mind_string = jsMind.util.json.json2string(mind_data);
        prompt_info(mind_string);
    }

    function save_file(){
        var mind_data = _jm.get_data();
        var mind_name = mind_data.meta.name;
        var mind_str = jsMind.util.json.json2string(mind_data);
        jsMind.util.file.save(mind_str,"text/jsmind",mind_name+".jm");
    }
    
    function open_file(){
        var file_input = document.getElementById("file_input");
        var files = file_input.files;
        if(files.length > 0){
            var file_data = files[0];
            jsMind.util.file.read(file_data,function(jsmind_data, jsmind_name){
                var mind = jsMind.util.json.string2json(jsmind_data);
                if(!!mind){
                    _jm.show(mind);
                }else{
                    prompt_info("can not open this file as mindmap");
                }
            });
        }else{
            prompt_info("'.$langPlzChooseFile.'")
        }
    }

    function select_node(){
        var nodeid = "other";
        _jm.select_node(nodeid);
    }

    function show_selected(){
        var selected_node = _jm.get_selected_node();
        if(!!selected_node){
            prompt_info(selected_node.topic);
        }else{
            prompt_info("nothing");
        }
    }

    function toggle_node(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info("'.$langPleaseSelectNode.'");}

        _jm.toggle_node(selected_id);
    }

    function get_selected_nodeid(){
        var selected_node = _jm.get_selected_node();
        if(!!selected_node){
            return selected_node.id;
        }else{
            return null;
        }
    }

    function add_node(){
        var selected_node = _jm.get_selected_node(); // as parent of new node
        if(!selected_node){prompt_info("'.$langPleaseSelectNode.'");}

        var nodeid = jsMind.util.uuid.newid();
        var topic = "* Node_"+nodeid.substr(0,3)+" *";
        var node = _jm.add_node(selected_node, nodeid, topic);
    }



    function move_to_first(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info("'.$langPleaseSelectNode.'");}

        _jm.move_node(selected_id,"_first_");
    }

    function move_to_last(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info("'.$langPleaseSelectNode.'");}

        _jm.move_node(selected_id,"_last_");
    }

    function move_node(){
        // move a node before another
        _jm.move_node("other","open");
    }

    function remove_node(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info("'.$langPleaseSelectNode.'");}

        _jm.remove_node(selected_id);
    }

    function set_theme(theme_name){
        _jm.set_theme(theme_name);
    }

    function toggle_editable(btn){
        var editable = _jm.get_editable();
        if(editable){
            _jm.disable_edit();
            btn.innerHTML = "'.$langEditEn.'";
        }else{
            _jm.enable_edit();
            btn.innerHTML = "'.$langEditDis.'";
        }
    }

	
	function one_click(){
		prompt_info("'.$langPlzClickNode.'");
	}
	
	
	function dbl_click(){
		prompt_info("'.$langPlzDblClickNode.'");
	}


    function prompt_info(msg){
        alert(msg);
    }

    open_empty();
</script>';  
   
   
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
