<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$tool_content .= "
            <div class='col-12 d-flex justify-content-md-start justify-content-center align-items-start gap-3 flex-wrap pb-4'>
                <a class='quickLink' href='../usage/displaylog.php?from_other=TRUE'><i class='fa-solid fa-glasses'></i>$langSystemActions</a>
                <a class='quickLink' href='../admin/monthlyReport.php'><i class='fa-solid fa-chalkboard'></i>$langMonthlyReport</a>                                                                            
                <a class='quickLink' href='../admin/login_stats.php'><i class='fa-solid fa-users-line'></i>$langReports $langUsersOf</a>
                <a class='quickLink' href='faculty_stats.php'><i class='fa-solid fa-bar-chart'></i>$langStatOfFaculty</a>            
                <a class='quickLink' href='index.php?t=a&g_stats'><i class='fa-solid fa-chart-simple'></i>$langCharts</a>                
            </div>
";

$tool_content .= "
        <div class='col-12'>
                <div class='row row-cols-1 row-cols-md-2 g-3 g-md-4'>                
                    <div class='col'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langUsageCoursesHeader</div>
                                <div>
                                    ".count_courses()."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langOpenCoursesShort</div>
                                <div>
                                    ".count_courses(COURSE_OPEN)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langOpenCourseWithRegistration</div>
                                <div>
                                    ".count_courses(COURSE_REGISTRATION)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langClosedCourses</div>
                                <div>
                                    ".count_courses(COURSE_CLOSED)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langCourseInactiveShort</div>
                                <div>
                                    ".count_courses(COURSE_INACTIVE)."
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class='col'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langUsageUsers</div>
                                <div>
                                    ".count_users()."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langTeachers</div>
                                <div>
                                    ".count_users(USER_TEACHER)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langStudents</div>
                                <div>
                                    ".count_users(USER_STUDENT)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langGuest</div>
                                <div>
                                    ".count_users(USER_GUEST)."
                                </div>
                            </li>              
                        </ul>
                    </div>
                </div>
                <div class='row row-cols-1 mt-md-4 mt-3'>
                    <h3>
                        $langChecks / $langReports
                    </h3>
                    <div class='col'>                        
                        <ul class='list-group list-group-flush'>                                                        
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=memail'>$langMultipleAddr e-mail</a>
                            </li>                            
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=vmusers'>$langMailVerification</a>
                            </li>                            
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=popularcourses'>$langPopularCourses</a>
                                <small> ($langLast30Entries)</small>
                            </li>                            
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=cusers'>$langMultipleCourseUsers</a>
                                <small> ($langLast30Entries)</small>
                            </li>
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=failurelogin'>$langLoginFailures</a>
                                <small> ($langLastMonth)</small>
                            </li>                            
                            <li class='list-group-item element'>
                                <a href='../admin/otheractions.php?stats=unregusers'>$langUnregUsers</a>
                                <small> ($langLastMonth)</small>
                            </li>
                        </ul>
                    </div>   
                </div>         
        </div>";
