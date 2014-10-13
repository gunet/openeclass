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

/*
 * Groups Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id: group_properties.php,v 1.24 2011-05-16 12:36:35 adia Exp $
 *
 * @abstract This module is responsible for the user groups of each lesson
 *
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Group';
$require_editor = true;

require_once '../../include/baseTheme.php';
$nameTools = $langGroupProperties;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroupManagement);

require_once 'group_functions.php';
initialize_group_info();

$checked['self_reg'] = $self_reg ? ' checked="1"' : '';
$checked['multi_reg'] = $multi_reg ? ' checked="1"' : '';
$checked['has_forum'] = $has_forum ? ' checked="1"' : '';
$checked['documents'] = $documents ? ' checked="1"' : '';
$checked['private_forum_yes'] = $private_forum ? ' checked="1"' : '';
$checked['private_forum_no'] = $private_forum ? '' : ' checked="1"';
$checked['wiki'] = ($wiki==0) ? '' : ' checked="1"';

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
            <label class='col-sm-2 control-label'>$langGroupStudentRegistrationType:</label>
                <div class='col-xs-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='self_reg' value='1'$checked[self_reg]>
                        $langGroupAllowStudentRegistration
                      </label>
                    </div>
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='multi_reg' value='1'$checked[multi_reg]>
                        $langGroupAllowMultipleRegistration
                      </label>
                    </div>                    
                </div>
            </div>
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langGroupForum:</label>
                <div class='col-xs-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='has_forum' value='1'$checked[has_forum]>
                        $langGroupAllowMultipleRegistration
                      </label>
                    </div>                    
                </div>
            </div>           
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langPrivate_1:</label>
                <div class='col-sm-10'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='1' checked=''$checked[private_forum_yes]>
                        $langPrivate_2
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='private_forum' value='0'$checked[private_forum_no]>
                        $langPrivate_3
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langDoc:</label>
                <div class='col-xs-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='documents' value='1'$checked[documents]>
                      </label>
                    </div>                    
                </div>
            </div>  
            <div class='form-group'>
            <label class='col-sm-2 control-label'>$langWiki:</label>
                <div class='col-xs-10'>             
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='wiki' value='1'$checked[wiki]>
                      </label>
                    </div>                    
                </div>
            </div>
            <div class='col-sm-10 col-sm-offset-2'>
                <input type='submit' class='btn btn-primary' name='properties' value='$langModify'>
                <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
            </div>
        </fieldset>
        </form>
    </div>";
draw($tool_content, 2);

