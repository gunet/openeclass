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
 * @file group_creation.php
 * @brief create users group
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';
$require_editor = true;

require_once '../../include/baseTheme.php';
$toolName = $langGroups;
$pageName = $addcategory;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);

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
				<form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code&amp;addcategory=1'>
					<fieldset>
                        <div class='form-group'>
                            <label for='CatName' class='col-sm-2 control-label'>$langCategoryName:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='CatDesc' class='col-sm-2 control-label'>$langDescription:</label>
                            <div class='col-sm-10'>
                                <textarea class='form-control' rows='5' name='description'>$form_description</textarea>
                            </div>
                        </div>
						<div class='form-group'>
							<div class='col-sm-10 col-sm-offset-2'>
								<input type='submit' class='btn btn-primary' name='submitCategory' value='$langCategoryAdd' />
                                <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
							</div>
						</div>
                        </fieldset>
                   ". generate_csrf_token_form_field() ." 
                </form>
            </div>";


draw($tool_content, 2);