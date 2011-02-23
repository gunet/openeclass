<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/


function makedefaultviewcode($locatie)
{
	global $aantalcategories;

        $view = str_repeat('0', $aantalcategories);
	$view[$locatie] = '1';
        return $view;
}


/**
 * Function getNumberOfLinks
 * @param unknown_type $catid
 * @return int number of links
 */
function getNumberOfLinks($catid){
        global $cours_id;

        list($count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM `link`
                                                        WHERE course_id = $cours_id AND category = $catid
                                                        ORDER BY `order`"));
	return $count;
}


function showlinksofcategory($catid)
{
        global $is_adminOfCourse, $cours_id, $urlview, $tool_content, $urlServer, $currentCourseID,
               $langLinkDelconfirm, $langDelete, $langUp, $langDown, $langModify, $langLinks, $langCategoryDelconfirm;

	$result = db_query("SELECT * FROM `link` WHERE course_id = $cours_id AND category = $catid ORDER BY `order`");
	$numberoflinks = mysql_num_rows($result);

	$i=1;
	while ($myrow = mysql_fetch_array($result)) {
                if ($i % 2 == 0) {
                        $tool_content .= "<tr class='even'>";
                } else {
                        $tool_content .= "<tr class='odd'>";
                }
                $title = empty($myrow['title'])? $myrow['url']: $myrow['title'];
                $tool_content .= "
                  <td>&nbsp;</td>
                  <td width='1' valign='top'><img src='$urlServer/template/classic/img/arrow_grey.gif' alt='' /></td>
                  <td valign='top'><a href='go.php?c=$currentCourseID&amp;id=$myrow[id]&amp;url=" .
                  urlencode($myrow['url']) . "' target='_blank'>" . q($title) . "</a>";
                if (!empty($myrow['description'])) {
                        $tool_content .= "<br />" . standard_text_escape($myrow['description']);
                }
                $tool_content .= "</td>\n";

                if ($is_adminOfCourse) {
                        $tool_content .=  "<td width='45' valign='top' align='right'>";
                        if (isset($category)) {
                                $tool_content .=  "<a href='$_SERVER[PHP_SELF]?action=editlink&amp;category=$category&amp;id=$myrow[0]&amp;urlview=$urlview'>";
                        } else {
                                $tool_content .=  "<a href='$_SERVER[PHP_SELF]?action=editlink&amp;id=$myrow[0]&amp;urlview=$urlview'>";
                        }

                        $tool_content .= "<img src='../../template/classic/img/edit.png' title='$langModify' alt='$langModify' /></a>&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?action=deletelink&amp;id=$myrow[0]&amp;urlview=$urlview' onclick=\"javascript:if(!confirm('".$langLinkDelconfirm."')) return false;\"><img src='../../template/classic/img/delete.png' title='$langDelete' alt='$langDelete' /></a></td>" .
                                         "<td width='45' valign='top' align='right'>";
                        // Display move up command only if it is not the top link
                        if ($i != 1) {
                                $tool_content .= "<a href='$_SERVER[PHP_SELF]?urlview=$urlview&amp;up=$myrow[id]'><img src='../../template/classic/img/up.png' title='$langUp' alt='$langUp' /></a>";
                        }
                        // Display move down command only if it is not the bottom link
                        if ($i < $numberoflinks) {
                                $tool_content .= "<a href='$_SERVER[PHP_SELF]?urlview=$urlview&amp;down=$myrow[id]'><img src='../../template/classic/img/down.png' title='$langDown' alt='$langDown' /></a>";
                        }
                        $tool_content .= "</td>";
                } else {
                        $tool_content .= "<td width='1' align='right' colspan='4'>&nbsp;</td>";
                }

                $tool_content .= "</tr>\n";
                $i++;
        }
}

function showcategoryadmintools($categoryid)
{
	global $urlview;
	global $aantalcategories;
	global $catcounter;
	global $langDelete, $langModify, $langUp, $langDown, $langCatDel;
	global $tool_content;

	$tool_content .=  "
		  <th width='45' valign='top'><div align='right'><a href=\"$_SERVER[PHP_SELF]?action=editcategory&amp;id=$categoryid&amp;urlview=$urlview\"><img src=\"../../template/classic/img/edit.png\" title=\"".$langModify."\" /></a>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=deletecategory&amp;id=".$categoryid."&amp;urlview=".$urlview."\" onclick=\"javascript:if(!confirm('".$langCatDel."')) return false;\">". "<img src=\"../../template/classic/img/delete.png\" title=\"".$langDelete."\" /></a></div></th>";


	$tool_content .= "<th width='45' valign='top'><div align='right'>";
	// Display move up command only if it is not the top link
	if ($catcounter != 1) {
		$tool_content .= "<a href='$_SERVER[PHP_SELF]?urlview=$urlview&amp;cup=$categoryid'><img src='../../template/classic/img/up.png' title='$langUp' alt='$$langUp' /></a>";
	}
	// Display move down command only if it is not the bottom link
	if ($catcounter < $aantalcategories) {
		$tool_content .=  "<a href='$_SERVER[PHP_SELF]?urlview=$urlview&amp;cdown=$categoryid'><img src='../../template/classic/img/down.png' title='$langDown' alt='$langDown' /></a>";
	}
        $tool_content .=  "</div></th></tr>";
	$catcounter++;
}

