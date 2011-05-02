<?php
/*========================================================================
 *   Open eClass 2.3
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2010  Greek Universities Network - GUnet
 *  A full copyright notice can be read in "/info/copyright.txt".
 *
 *  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
 *			Yannis Exidaridis <jexi@noc.uoa.gr>
 *			Alexandros Diamantidis <adia@noc.uoa.gr>
 *			Tilemachos Raptis <traptis@noc.uoa.gr>
 *
 *  For a full list of contributors, see "credits.txt".
 *
 *  Open eClass is an open platform distributed in the hope that it will
 *  be useful (without any warranty), under the terms of the GNU (General
 *  Public License) as published by the Free Software Foundation.
 *  The full license can be read in "/info/license/license_gpl.txt".
 *
 *  Contact address: 	GUnet Asynchronous eLearning Group,
 *  			Network Operations Center, University of Athens,
 *  			Panepistimiopolis Ilissia, 15784, Athens, Greece
 *  			eMail: info@openeclass.org
 * =========================================================================*/


function display_text_form()
{
	global $tool_content, $id, $langContent, $langAdd, $code_cours;

	$tool_content .= "
        <form action='insert.php?course=$code_cours' method='post'><input type='hidden' name='id' value='$id'>";
	$tool_content .= "
        <fieldset>
        <legend>$langContent:</legend>".  rich_text_editor('comments', 4, 20, '') ."
	<br />
        <input type='submit' name='submit_text' value='$langAdd'>
	</fieldset>
	</form>";
}
