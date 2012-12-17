<?php
/* ========================================================================
 * Open eClass 2.6
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
                        $tool_content .=  "<td width='45' valign='top' align='right'>";
                        $editlink = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=editlink&amp;id=$myrow[0]&amp;urlview=$urlview";
                        if (isset($category)) {
                                $editlink .= "&amp;category=$category";
                        }

                        $tool_content .= icon('edit', $langModify, $editlink) .
                                "&nbsp;&nbsp;" .
                                icon('delete', $langDelete,
                                        "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=deletelink&amp;id=$myrow[0]&amp;urlview=$urlview",
                                        "onclick=\"javascript:if(!confirm('".$langLinkDelconfirm."')) return false;\"") .
                                        "</td><td width='35' valign='top' align='right'>";
                        // Display move up command only if it is not the top link
                        if ($i != 1) {
                                $tool_content .= icon('up', $langUp,
                                        "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;up=$myrow[id]");
                        }
                        // Display move down command only if it is not the bottom link
                        if ($i < $numberoflinks) {
                                $tool_content .= icon('down', $langDown,
                                        "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;down=$myrow[id]");
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

        $basecaturl = "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$categoryid&amp;urlview=$urlview&amp;";
        $tool_content .=  "<th width='45' valign='top' class='right'>" .
                          icon('edit', $langModify, $basecaturl . 'action=editcategory') .
                          '&nbsp;&nbsp;' .
                          icon('delete', $langDelete, $basecaturl . 'action=deletecategory',
                               "onclick=\"javascript:if(!confirm('$langCatDel')) return false;\"") .
                          "</th>";

	$tool_content .= "<th width='35' valign='top' class='right'>";
	// Display move up command only if it is not the top link
	if ($catcounter != 1) {
                $tool_content .= icon('up', $langUp,
                        "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;cup=$categoryid");
        } else {
        }
	// Display move down command only if it is not the bottom link
	if ($catcounter < $aantalcategories) {
                $tool_content .= icon('down', $langDown,
                        "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview&amp;cdown=$categoryid");
	}
        $tool_content .= "</th>";
	$catcounter++;
}

