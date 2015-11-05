<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message

$require_admin = true;
require_once '../../include/baseTheme.php';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if ($_POST['action'] == 'add') {
        $widget_area_id = $_POST['widget_area_id'];
        $widget_id = $_POST['widget_id'];
        $position = $_POST['position'];
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` + 1 WHERE `position` >= ?d AND `widget_area_id` = ?d", $position, $widget_area_id);
        $widget_widget_area_id = Database::get()->query("INSERT INTO `widget_widget_area` (`widget_id`, `widget_area_id`, `position`) VALUES (?d, ?d, ?d)", $widget_id, $widget_area_id, $position)->lastInsertID;
        $data['widget_widget_area_id'] = $widget_widget_area_id;
    } elseif ($_POST['action'] == 'move') {
        $widget_widget_area_id = $_POST['widget_widget_area_id'];
        $widget_area_id = $_POST['widget_area_id'];
        $oldPos = $_POST['oldPos'];
        $newPos = $_POST['newPos'];
        $prev_widget_area_id = Database::get()->querySingle("SELECT `widget_area_id` FROM `widget_widget_area` WHERE id = ?d", $widget_widget_area_id)->widget_area_id;
        // updating old list widget positions
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` - 1 WHERE `position` > ?d AND `widget_area_id` = ?d", $oldPos, $prev_widget_area_id);
        // updating new list widget positions
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` + 1 WHERE `position` >= ?d AND `widget_area_id` = ?d", $newPos, $widget_area_id);
        // moving widget to new widget list
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = ?d, widget_area_id = ?d WHERE id = ?d", $newPos, $widget_area_id, $widget_widget_area_id);
    } elseif ($_POST['action'] == 'remove') {
        $widget_area_id = $_POST['widget_area_id'];
        $widget_widget_area_id = $_POST['widget_widget_area_id'];
        $position = $_POST['position'];
        $data = Database::get()->query("DELETE FROM widget_widget_area WHERE id = ?d", $widget_widget_area_id);
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` - 1 WHERE `position` > ?d AND `widget_area_id` = ?d", $position, $widget_area_id);
    } elseif ($_POST['action'] == 'changePos') {
        $widget_area_id = $_POST['widget_area_id'];
        $widget_widget_area_id = $_POST['widget_widget_area_id'];
        $newPos = $_POST['newPos'];
        $oldPos = $_POST['oldPos'];
        if ($newPos < $oldPos) {
            Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` + 1 WHERE `position` >= ?d  AND `position` < ?d AND `widget_area_id` = ?d", $newPos, $oldPos, $widget_area_id);
        } else {
            Database::get()->query("UPDATE `widget_widget_area` SET `position` = `position` - 1 WHERE `position` <= ?d AND `position` > ?d AND `widget_area_id` = ?d", $newPos, $oldPos, $widget_area_id);
        }
        Database::get()->query("UPDATE `widget_widget_area` SET `position` = ?d WHERE id = ?d", $newPos, $widget_widget_area_id);
        
    } elseif ($_POST['action'] == 'getForm') {
        $widget_id = $_POST['widget_id'];
        $widget_widget_area_id = $_POST['widget_widget_area_id'];
        $widget = Database::get()->querySingle('SELECT * FROM widget WHERE id = ?d', $widget_id);
        $widget_obj = new $widget->class;
        $data['form_view'] = $widget_obj->getOptionsForm($widget_widget_area_id);
    } elseif ($_POST['action'] == 'saveOptions') {
        $widget_widget_area_id = $_POST['widget_widget_area_id'];
        $options = $_POST['options'];
        $option_data = array();
        foreach ($options as $option) {
            $option_data[$option['name']] = $option['value'];
        }
        \Database::get()->query("UPDATE `widget_widget_area` SET `options` = ?s WHERE id = ?d", serialize($option_data), $widget_widget_area_id);     
    }
    echo json_encode($data);
    exit;    
}

load_js('sortable');
if (isset($_POST['widgetAction'])) {
    $namespaced_class = $_POST['widgetClassName'];
    if ($_POST['widgetAction'] == 'install') {
        $namespaced_class::install();
    } elseif ($_POST['widgetAction'] == 'uninstall') {
        $namespaced_class::uninstall();
    }
    redirect_to_home_page('modules/admin/widgets.php');
}
$head_content .= 
        "
        <script type='text/javascript'>
            $(function() {
                var byId = function (id) { return document.getElementById(id); }
                Sortable.create(byId('widgets'), {
                    draggable: '.widget',
                    sort: false,
                    group: { name: 'widgets', pull: 'clone', put: false },
                    animation: 150
                });
                [
                'home_widget_main',
                'home_widget_sidebar'
                ].forEach(function (id, i) { 
                    Sortable.create(byId(id), {
                        draggable: '.widget',
                        sort: true,                        
                        group: { name: 'widgets', pull: true, put: true },
                        animation: 150,
                        filter: '.remove',
                        onFilter: function (e) {
                            removeWidget(e);                        
                        },
                        // Changed sorting within list
                        onUpdate: function (e) {
                            changePos(e);                            
                        },                       
                        onRemove: function (e) {
                            // When a widget is moved between widgets areas
                            moveWidget(e);
                        },                     
                        onAdd: function (e) {
                            // When a widget is added to a widget area
                            // from the widgets list
                            addWidget(e);       
                        }                    
                    });                
                });           
            });
            function changePos(e) {
                var item = $(e.item);
                var widget_widget_area_id = item.data('widget-widget-area-id');
                var widget_area_id = item.closest('div.panel-body').data('widget-area-id');
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                     widget_widget_area_id : widget_widget_area_id,
                     widget_area_id: widget_area_id,
                     oldPos: e.oldIndex,
                     newPos: e.newIndex,
                     action: 'changePos'
                  },
                  success: function(data){

                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });                 
            }
            function removeWidget(e) {
                var item = $(e.item);  // dragged HTMLElement
                var widget_area_id = item.closest('div.panel-body').data('widget-area-id');
                var widget_widget_area_id = item.data('widget-widget-area-id');
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                     widget_widget_area_id: widget_widget_area_id,
                     widget_area_id: widget_area_id,
                     position: e.oldIndex,
                     action: 'remove'
                  },
                  success: function(data){
                    item.hide('slow', function(){ item.remove(); });
                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });                 
            }
            function addWidget(e) {
                if (e.from['id'] == 'widgets') {
                    var item = $(e.item);  // dragged HTMLElement
                    item.find('div.panel-heading a').append('<span class=\'fa fa-spinner fa-spin\'></span>');
                    var widget_area_id = item.closest('div.panel-body').data('widget-area-id');
                    var widget_id = item.data('widget-id');   
                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                        widget_id: widget_id,
                        widget_area_id: widget_area_id,
                        position: e.newIndex,
                        action: 'add'
                      },
                      success: function(data){ 
                            initializeWidget(e, data);
                      },
                      error: function(xhr, textStatus, error){
                          console.log(xhr.statusText);
                          console.log(textStatus);
                          console.log(error);
                      }
                    });                                  
                }              
            }
            function moveWidget(e) {
                var item = $(e.item);  // dragged HTMLElement
                var widget_widget_area_id = item.data('widget-widget-area-id');
                var widget_area_id = item.closest('div.panel-body').data('widget-area-id');
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                     widget_widget_area_id : widget_widget_area_id,
                     widget_area_id: widget_area_id,
                     oldPos: e.oldIndex,
                     newPos: e.newIndex,
                     action: 'move'
                  },
                  success: function(data){

                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });             
            }            
            function initializeWidget(e, data) {
                    var item = $(e.item);  // dragged HTMLElement
                    var widget_id = item.data('widget-id');            
                    var obj = jQuery.parseJSON(data);                                  
                    item
                        .attr('data-widget-widget-area-id', obj.widget_widget_area_id)
                        .find('.widget_title')
                        .attr('data-target', '#widget_form_'+obj.widget_widget_area_id)
                        .attr('href', '#widget_form_'+obj.widget_widget_area_id)
                        .end()
                        .find('#widget_form')
                        .attr('id', 'widget_form_'+obj.widget_widget_area_id)
                        .removeClass('hidden')
                        .prev()
                        .remove();

                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                         widget_id: widget_id,
                         widget_widget_area_id: obj.widget_widget_area_id,
                         action: 'getForm'
                      },
                      success: function(data){
                            var form_obj = jQuery.parseJSON(data);
                            item
                            .find('#widget_form_'+obj.widget_widget_area_id)
                            .find('.panel-body')
                            .append(form_obj.form_view);
                            item.find('div.panel-heading a span').removeClass().addClass('fa fa-check');
                      },
                      error: function(xhr, textStatus, error){
                          console.log(xhr.statusText);
                          console.log(textStatus);
                          console.log(error);
                      }
                    });
            }

                $(document).on('click', '.submitOptions', function(e) {
                    e.preventDefault();
                    var widget_widget_area_id = $(this).closest('.panel').data('widget-widget-area-id');
                    var options = $(this).closest('.panel-body').find('form#optionsForm'+widget_widget_area_id).serializeArray();
                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                         widget_widget_area_id : widget_widget_area_id,
                         options: options,
                         action: 'saveOptions'
                        },
                        success: function(data){

                        },
                        error: function(xhr, textStatus, error){
                            console.log(xhr.statusText);
                            console.log(textStatus);
                            console.log(error);
                        } 
                      });
                });            

        </script>
        ";
        
$installed_widgets = Database::get()->queryArray("SELECT id, class FROM widget");
$installed_widgets_arr = [];
foreach ($installed_widgets as $installed_widget) {
    $installed_widgets_arr[$installed_widget->id] = $installed_widget->class;
}    
$view_data['installed_widgets'] = [];
$view_data['uninstalled_widgets'] = [];

$home_main_area = new Widgets\WidgetArea(1);
$view_data['home_main_area_widgets'] = $home_main_area->getWidgets();
$home_sidebar_area = new Widgets\WidgetArea(2);
$view_data['home_sidebar_widgets'] = $home_sidebar_area->getWidgets();
  
$view_data = recursiveWidgetIterator('widgets', $view_data);

$view_data['menuTypeID'] = 3;
echo view('admin.widgets', $view_data);

function recursiveWidgetIterator ($directory = null, $view_data = array()) {
    global $installed_widgets_arr;
    $files = new \DirectoryIterator ( $directory );
    foreach ($files as $file) {
        if ($file->isFile ()) {
            $widget_class = $file->getBasename('.php');
            if (!in_array($widget_class, ['Widget', 'WidgetInterface', 'WidgetArea', 'WidgetWidgetArea'])) {
                $namespace = by_token(file_get_contents($directory.'/'.$file));
                $namespaced_class = $namespace."\\".$widget_class;
                $widget = new $namespaced_class;
                $widget_id = array_search($namespaced_class, $installed_widgets_arr);
                if ($widget_id) {
                    $widget->id = $widget_id;
                    array_push($view_data['installed_widgets'], $widget);
                } else {
                    array_push($view_data['uninstalled_widgets'], $widget);
                }
            }
        } elseif (!$file->isDot ()) {
            $view_data = recursiveWidgetIterator($directory.DIRECTORY_SEPARATOR.$file->__toString(), $view_data);
        }
    }
    return $view_data;
}
// Get Namespace from file
function by_token ($src) {
	$tokens = token_get_all($src);
	$count = count($tokens);
	$i = 0;
	$namespace = '';
	$namespace_ok = false;
	while ($i < $count) {
		$token = $tokens[$i];
		if (is_array($token) && $token[0] === T_NAMESPACE) {
			// Found namespace declaration
			while (++$i < $count) {
				if ($tokens[$i] === ';') {
					$namespace_ok = true;
					$namespace = trim($namespace);
					break;
				}
				$namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
			}
			break;
		}
		$i++;
	}

	if (!$namespace_ok) {
		return null;
	} else {
		return $namespace;
	}
}