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
 * @file admin.inc.php	
 * @authors :Karatzidis Stratos <kstratos@uom.gr>
 *           Vagelis Pitsioygas <vagpits@uom.gr>
 * @brief: library of functions for admin purposes
 */
/* * ************************************************************
  Purpose: display paging navigation
  Parameters: limit - the current limit
  listsize - the size of the list
  fulllistsize - the size of the full list
  page - the page to send links from pages
  extra_page - extra arguments to page link
  displayAll - display 'all' button (defaults to FALSE)

  return String (the constructed table)
 * ************************************************************* */
function show_paging($limit, $listsize, $fulllistsize, $page, $extra_page = '', $displayAll = FALSE) {
    global $langNextPage, $langBeforePage, $langAllUsers;
    $limit = intval($limit);
    $retString = $link_all = "";
    if ($displayAll == TRUE) {
        if (isset($GLOBALS['course_code'])) {
            $link_all = "<a href='$_SERVER[SCRIPT_NAME]?all=TRUE&amp;course=$GLOBALS[course_code]'>$langAllUsers</a>";
        } else {
            $link_all = "<a href='$_SERVER[SCRIPT_NAME]?all=TRUE'>$langAllUsers</a>";
        }
    }

    // Page numbers of navigation
    $pn = 15;
    $retString .= "
        <table width=\"99%\" class='tbl'>
        <tr>
          <td>&nbsp;</td>
          <td align='center'>$link_all &nbsp;&nbsp;&nbsp;";
    // Deal with previous page
    if ($limit != 0) {
        $newlimit = $limit - $listsize;
        $retString .= "<a href='$page?limit=$newlimit$extra_page'><b>$langBeforePage</b></a>&nbsp;|&nbsp;";
    } else {
        $retString .= "<b>$langBeforePage</b>&nbsp;|&nbsp;";
    }
    // Deal with pages
    if (ceil($fulllistsize / $listsize) <= $pn / 3) {
        // Show all
        $counter = 0;
        while ($counter * $listsize < $fulllistsize) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>$aa</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
    } elseif ($limit / $listsize < ($pn / 3) + 3) {
        // Show first 10
        $counter = 0;
        while ($counter * $listsize < $fulllistsize && $counter < $pn / 3 * 2) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>$aa</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
        $retString .= "<b>...</b>&nbsp;";
        // Show last 5
        $counter = ceil($fulllistsize / $listsize) - ($pn / 3);
        while ($counter * $listsize < $fulllistsize) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>" . $aa . "</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
    } elseif ($limit / $listsize >= ceil($fulllistsize / $listsize) - ($pn / 3) - 3) {
        // Show first 5
        $counter = 0;
        while ($counter * $listsize < $fulllistsize && $counter < ($pn / 3)) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>" . $aa . "</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href=\"" . $page . "?limit=" . $newlimit . "" . $extra_page . "\">" . $aa . "</a></b>&nbsp;";
            }
            $counter++;
        }
        $retString .= "<b>...</b>&nbsp;";
        // Show last 10
        $counter = ceil($fulllistsize / $listsize) - ($pn / 3 * 2);
        while ($counter * $listsize < $fulllistsize) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>" . $aa . "</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
    } else {
        // Show first 5
        $counter = 0;
        while ($counter * $listsize < $fulllistsize && $counter < ($pn / 3)) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>$aa</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
        $retString .= "<b>...</b>&nbsp;";
        // Show middle 5
        $counter = ($limit / $listsize) - 2;
        $top = $counter + 5;
        while ($counter * $listsize < $fulllistsize && $counter < $top) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>" . $aa . "</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
        $retString .= "<b>...</b>&nbsp;";
        // Show last 5
        $counter = ceil($fulllistsize / $listsize) - ($pn / 3);
        while ($counter * $listsize < $fulllistsize) {
            $aa = $counter + 1;
            if ($counter * $listsize == $limit) {
                $retString .= "<b>$aa</b>&nbsp;";
            } else {
                $newlimit = $counter * $listsize;
                $retString .= "<b><a href='$page?limit=$newlimit$extra_page'>$aa</a></b>&nbsp;";
            }
            $counter++;
        }
    }
    // Deal with next page
    if ($limit + $listsize >= $fulllistsize) {
        $retString .= "|&nbsp;<b>$langNextPage</b>";
    } else {
        $newlimit = $limit + $listsize;
        $retString .= "|&nbsp;<a href='$page?limit=$newlimit$extra_page'><b>$langNextPage</b></a>";
    }
    $retString .= "
          </td>
        </tr>
        </table>";

    return $retString;
}

