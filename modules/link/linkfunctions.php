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
        global $is_editor, $cours_id, $urlview, $tool_content, 
               $urlServer, $currentCourseID, $code_cours, $themeimg,
               $langLinkDelconfirm, $langDelete, $langUp, $langDown, 
               $langModify, $langLinks, $langCategoryDelconfirm,
               $is_in_tinymce;

        $result = db_query("SELECT * FROM `link`
                                   WHERE course_id = $cours_id AND category = $catid
                                   ORDER BY `order`");
	$numberoflinks = mysql_num_rows($result);

	$i=1;
	while ($myrow = mysql_fetch_array($result)) {
                if ($i % 2 == 0) {
                        $tool_content .= "
                <tr class='odd'>";
                } else {
                        $tool_content .= "
                <tr class='even'>";
                }
                $title = empty($myrow['title'])? $myrow['url']: $myrow['title'];
                $tool_content .= "
                  <td>&nbsp;</td>
                  <td width='1' valign='top'><img src='$themeimg/arrow.png' alt='' /></td>";
                if ($is_editor) {
                    $num_merge_cols = 1;
                } else {
                    $num_merge_cols = 1;
                }
                $aclass = ($is_in_tinymce) ? " class='fileURL' ": '';
                $tool_content .= "
                  <td valign='top' colspan='$num_merge_cols'><a href='".$urlServer ."modules/link/go.php?c=$currentCourseID&amp;id=$myrow[id]&amp;url=" .
                  urlencode($myrow['url']) . "' $aclass target='_blank'>" . q($title) . "</a>";
                if (!empty($myrow['description'])) {
                        $tool_content .= "<br />" . standard_text_escape($myrow['description']);
                }
                $tool_content .= "</td>";

                if ($is_editor && !$is_in_tinymce) {
                        $tool_content .=  "
                  <td width='45' valign='top' align='right'>";
                        if (isset($category)) {
                                $tool_content .=  "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=editlink&amp;category=$category&amp;id=$myrow[0]&amp;urlview=$urlview'>";
                        } else {
                                $tool_content .=  "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=editlink&amp;id=$myrow[0]&amp;urlview=$urlview'>";
                        }

                        $tool_content .= "<img src='$themeimg/edit.png' title='$langModify' alt='$langModify' /></a>&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=deletelink&amp;id=$myrow[0]&amp;urlview=$urlview' onclick=\"javascript:if(!confirm('".$langLinkDelconfirm."')) return false;\"><img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete' /></a></td>" .
                                         "<td width='35' valign='top' align='right'>";
                        // Display move up command only if it is not the top link
                        if ($i != 1) {
                                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;up=$myrow[id]'><img src='$themeimg/up.png' title='$langUp' alt='$langUp' /></a>";
                        }
                        // Display move down command only if it is not the bottom link
                        if ($i < $numberoflinks) {
                                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;down=$myrow[id]'><img src='$themeimg/down.png' title='$langDown' alt='$langDown' /></a>";
                        }
                        $tool_content .= "
                  </td>";
                }

                $tool_content .= "
                </tr>";
                $i++;
        }
}

function showcategoryadmintools($categoryid)
{
        global $urlview, $aantalcategories, $catcounter, $langDelete, 
               $langModify, $langUp, $langDown, $langCatDel, $tool_content,
               $code_cours, $themeimg;

	$tool_content .=  "
                <th width='45' valign='top'><div align='right'>
                    <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=editcategory&amp;id=$categoryid&amp;urlview=$urlview'>
                        <img src='$themeimg/edit.png' title='$langModify' /></a>&nbsp;&nbsp;<a
                            href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=deletecategory&amp;id=$categoryid&amp;urlview=".
                            $urlview."' onclick=\"javascript:if(!confirm('$langCatDel')) return false;\">".
                            "<img src='$themeimg/delete.png' title='$langDelete' /></a></div></th>";

	$tool_content .= "<th width='35' valign='top'><div align='right'>";
	// Display move up command only if it is not the top link
	if ($catcounter != 1) {
		$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;cup=$categoryid'><img src='$themeimg/up.png' title='$langUp' alt='$$langUp' /></a>";
	}
	// Display move down command only if it is not the bottom link
	if ($catcounter < $aantalcategories) {
		$tool_content .=  "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;cdown=$categoryid'><img src='$themeimg/down.png' title='$langDown' alt='$langDown' /></a>";
	}
        $tool_content .=  "</div>
                  </th>";
	$catcounter++;
}

