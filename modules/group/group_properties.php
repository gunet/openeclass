<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * @file group_properties.php
 * @brief display group properties
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Group';
$require_editor = true;

require_once '../../include/baseTheme.php';
$toolName = $langGroups;
$pageName = $langGroupProperties;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroups);

require_once 'group_functions.php';
initialize_group_id();
$group_id = $_GET['group_id'];

initialize_group_info($group_id);

$group = Database::get()->querySingle("SELECT * FROM group_properties WHERE group_id = ?d AND course_id = ?d", $group_id, $course_id);

$checked['self_reg'] = ($group->self_registration?'checked':'');
$checked['multi_reg'] = ($group->multiple_registration?'checked':'');
$checked['private_forum_yes'] =($group->private_forum?' checked="1"' : '');
$checked['private_forum_no'] = ($group->private_forum? '' : ' checked="1"');
$checked['has_forum'] = ($group->forum?'checked':'');
$checked['documents'] = ($group->documents?'checked':'');
$checked['wiki'] = ($group->wiki?'checked':'');


$tool_content .= action_bar(array(
    array(
        'title' => $langBack,
        'level' => 'primary-label',
        'icon' => 'fa-reply',
        'url' => "index.php?course=$course_code"
    )
));

$tool_content .= "
<div class='form-wrapper'>    
    <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>
        <fieldset>
            <div class='form-group'>
            <label class='col-sm-3 control-label'>$langGroupStudentRegistrationType:</label>
                <div class='col-xs-9'>             
                    <div class='checkbox'>
                    <label>
                     <input type='checkbox' name='self_reg' $checked[self_reg]>
                        $langGroupAllowStudentRegistration
                        </label>
                        </div>
                    <div class='checkbox'>
                        <label>
                        <input type='checkbox' name='multi_reg' $checked[multi_reg]>
                        $langGroupAllowMultipleRegistration
                      </label>
                    </div>                    
                </div>
            </div>        
		    <div class='form-group'>
                <label class='col-sm-3 control-label'>$langPrivate_1:</label>
                <div class='col-sm-9'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='1' checked=''  $checked[private_forum_yes]>
                        $langPrivate_2
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='0' $checked[private_forum_no]>
                        $langPrivate_3
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
            <label class='col-sm-3 control-label'>$langGroupForum:</label>
                <div class='col-xs-9'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='forum' $checked[has_forum]>
                      </label>
                    </div>                    
                </div>
            </div>   
            <div class='form-group'>
            <label class='col-sm-3 control-label'>$langDoc:</label>
                <div class='col-xs-9'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='documents' $checked[documents]>
                      </label>
                    </div>                    
                </div>
            </div>  
            <div class='form-group'>
            <label class='col-sm-3 control-label'>$langWiki:</label>
                <div class='col-xs-9'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='wiki' $checked[wiki]>
                      </label>
                    </div>                    
                </div>
            </div>			
            <input type='hidden' name='group_id' value=$group_id></input>			
            <div class='form-group'>
            <div class='col-sm-9 col-sm-offset-3'>
                <input type='submit' class='btn btn-primary' name='properties' value='$langModify'>
                <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
            </div>
            </div>
        </fieldset>
        </form>
    </div>";
draw($tool_content, 2);

