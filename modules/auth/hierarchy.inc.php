<?php
/* ========================================================================
 * Open eClass 3.0
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


function departmentChildren($depid, $url = '') {
    global $langAvCours, $langAvCourses;
    
    $ret = '';
    $res = db_query("SELECT node.id, node.code, node.name FROM hierarchy AS node
            LEFT OUTER JOIN hierarchy AS parent ON parent.lft = 
                            (SELECT MAX(S.lft) 
                            FROM hierarchy AS S WHERE node.lft > S.lft
                                AND node.lft < S.rgt)
                      WHERE parent.id = ". intval($depid) ."
                        AND node.allow_course = true");
    
    
    if (mysql_num_rows($res) > 0)
    {
        $ret .= "<table width='100%' class='tbl_border'>";
        $nodenames = array();
        $nodecodes = array();
        
        while ($node = mysql_fetch_array($res))
        {
            $nodenames[$node['id']] = hierarchy::unserializeLangField($node['name']);
            $nodecodes[$node['id']] = $node['code'];
        }
        asort($nodenames);
        
        foreach ($nodenames as $key => $value)
        {
            $ret .= "<tr><td><a href='$url.php?fc=". intval($key) ."'>". 
                    q($value) .
                    "</a>&nbsp;&nbsp;<small>(". q($nodecodes[$key]) .")";
            
            $n = db_query("SELECT COUNT(*) 
                             FROM course, course_department 
                            WHERE course.id = course_department.course 
                              AND course_department.department = ". intval($key));
            $r = mysql_fetch_array($n);

            $ret .= "&nbsp;&nbsp;-&nbsp;&nbsp;". intval($r[0]) ."&nbsp;". ($r[0] == 1 ? $langAvCours : $langAvCourses) . "</small></td></tr>";
        }
        
        $ret .= "</table><br />";
    }

    return $ret;
}