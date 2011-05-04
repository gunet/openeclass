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

/*
 * Logged In Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content of the start page when the
 * user is logged in
 *
 */
if (!defined('INDEX_START')) {
	die('Action not allowed!');
}

$tool_content = "";
include "perso.php";

$tool_content = "
<div style='margin: 20px 0 20px 0;'>
  <table width='100%' class='tbl_border'>
  <tr>
    <th>{LANG_MY_PERSO_LESSONS}</th>
  </tr>
  <tr>
    <td>{LESSON_CONTENT}</td>
  </tr>
  </table>
</div>


<div id='leftnav_perso'>
  <table width='100%'>
  <thead>
  <tr>
    <th class='persoBoxTitle'>{LANG_MY_PERSO_ANNOUNCEMENTS}</th>
  </tr>
  </thead>
  <tbody>
  <tr class='odd'>
    <td>{ANNOUNCE_CONTENT}</td>
  </tr>
  </tbody>
  </table>

  <br />

  <table width='99%'>
  <thead>
  <tr>
    <th class='persoBoxTitle'>{LANG_MY_PERSO_AGENDA}</th>
  </tr>
  </thead>
  <tbody>
  <tr class='odd'>
    <td>{AGENDA_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

</div>


<div id='content_main_perso'>
  <table width='99%'>
  <thead>
  <tr>
    <th class='persoBoxTitle'>{LANG_MY_PERSO_DEADLINES}</th>
  </tr>
  </thead>
  <tbody>
  <tr class='odd'>
    <td>{ASSIGN_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

  <br />

  <table width='99%'>
  <thead>
  <tr>
    <th class='persoBoxTitle'>{LANG_MY_PERSO_DOCS}</th>
  </tr>
  </thead>
  <tbody>
  <tr class='odd'>
    <td>{DOCS_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

  <br />

  <table width='99%'>
  <thead>
  <tr>
    <th class='persoBoxTitle'>{LANG_PERSO_FORUM}</th>
  </tr>
  </thead>
  <tbody>
  <tr class='odd'>
    <td>{FORUM_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

</div>";
