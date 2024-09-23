<?php

/* ========================================================================
 * Open eClass 3.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in
 */

$require_login = true;
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'my_courses';
include '../include/baseTheme.php';
require_once 'portfolio_functions.php';

$toolName = $langMyCourses;

load_js('datatables');

$myCourses = array();
$html = "";
//  Get user's course list
if(isset($_GET['term'])){

  $q = $_GET['term'];

  $typeCourse = 0;
  if(get_config('show_collaboration') && get_config('show_always_collaboration')){
    $typeCourse = 1;
  }
  if(isset($_GET['typeCourse'])){
    $typeCourse = intval($_GET['typeCourse']);
  }

  //Get all courses which user has registered
  if(empty($q)){
    $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                      course.code code,
                      course.public_code,
                      course.title title,
                      course.prof_names professor,
                      course.lang,
                      course.visible visible,
                      course.course_image course_image,
                      course_user.status status,
                      course_user.favorite favorite
                FROM course JOIN course_user
                      ON course.id = course_user.course_id 
                      AND course_user.user_id = ?d 
                      AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                      AND is_collaborative = ?d
                  ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid, $typeCourse);
  }else{//Get all courses from search-component which user has registered
    $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                   course.code code,
                   course.public_code,
                   course.title title,
                   course.prof_names professor,
                   course.lang,
                   course.visible visible,
                   course.course_image course_image,
                   course_user.status status,
                   course_user.favorite favorite
             FROM course JOIN course_user
                  ON course.id = course_user.course_id 
                  WHERE title LIKE ?s
                  AND course_user.user_id = ?d 
                  AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                  AND is_collaborative = ?d
              ORDER BY favorite DESC, status ASC, visible ASC, title ASC","%$q%",  $uid, $typeCourse);
  }

  if($myCourses){
      $html .="<div class='col-12'>
            <div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>";

                    $pagesPag = 0;
                    $allCourses = 0;
                    $temp_pages = 0;
                    $countCards = 1;
                    if($countCards == 1){
                        $pagesPag++;
                    }

                foreach($myCourses as $course){
                    $temp_pages++;

                  $html .= "<div class='col cardCourse$pagesPag'>
                        <div class='card h-100 card$pagesPag Borders border-card card-default px-2 py-3'>";

                                $courseImage = '';
                                if(!empty($course->course_image)){
                                    $courseImage = "{$urlServer}courses/{$course->code}/image/{$course->course_image}";
                                }else{
                                    $courseImage = "{$urlServer}resources/img/ph1.jpg";
                                }

                      $html .= "<div class='card-header border-0'>
                                <div class='card-title d-flex justify-content-start align-items-start gap-2 mb-0'>";
                                    if($course->visible == 1){
                                        $html .= "<button type='button' class='btn btn-transparent p-0' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langRegCourse' aria-label='$langRegCourse'>
                                            <i class='fa-solid fa-square-pen title-default fa-lg'></i>
                                        </button>";
                                    }
                                    if($course->visible == 2){
                                        $html .= "<button type='button' class='btn btn-transparent p-0' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langOpenCourse' aria-label='$langOpenCourse'>
                                            <i class='fa-solid fa-lock-open title-default fa-lg'></i>
                                        </button>";
                                    }
                                    if($course->visible == 0){
                                        $html .= "<button type='button' class='btn btn-transparent p-0' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langClosedCourse' aria-label='$langClosedCourse'>
                                            <i class='fa-solid fa-lock title-default fa-lg'></i>
                                        </button>";
                                    }
                                    if($course->visible == 3){
                                        $html .= "<button type='button' class='btn btn-transparent p-0' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$langInactiveCourse' aria-label='$langInactiveCourse'>
                                            <i class='fa-solid fa-triangle-exclamation title-default fa-lg'></i>
                                        </button>";
                                    }
                                    $invisibleCourse = '';
                                    if($course->visible == 3){
                                      $invisibleCourse = 'InvisibleCourse';
                                    }
                                    $html .= "<a class='$invisibleCourse TextBold mt-2' href='{$urlServer}courses/{$course->code}/index.php'>".q($course->title)."</a>
                                </div>
                            </div>


                            <div class='card-body pt-0'>
                                <img src='{$courseImage}' class='card-img-top cardImgCourse rounded-0 $invisibleCourse' alt='$langCourseImage&nbsp;(".q($course->title).")'>
                                <div class='card-text mt-3'>
                                    <p class='d-inline $invisibleCourse mb-0 TextBold'>$langCode:</p>
                                    &nbsp;<p class='d-inline $invisibleCourse'>".q($course->public_code)."</p>
                                </div>
                                <div class='card-text'>
                                    <p class='d-inline $invisibleCourse mb-0 TextBold'>$langTeacher:</p>
                                    &nbsp;<p class='d-inline $invisibleCourse'>".q($course->professor)."</p>
                                </div>

                            </div>
                            <div class='card-footer d-flex justify-content-center align-items-center border-0 mb-2'>";
                                // check if uid is editor of course or student
                                $is_course_teacher = check_editor($uid,$course->course_id);

                                if($_SESSION['status'] == USER_TEACHER and $is_course_teacher and $course->status == 1){
                                    $html .= "<a class='btn submitAdminBtn w-100 gap-1' href='{$urlServer}modules/course_info/index.php?from_home=true&amp;course={$course->code}'>
                                        <i class='fa-solid fa-gear settings-icons'></i>
                                        $langAdm
                                    </a>";
                                } else {
                                    if (get_config('disable_student_unregister_cours') == 0){
                                        $html .= "<button class='btn deleteAdminBtn w-100 gap-1' data-bs-toggle='modal' data-bs-target='#exampleModal{$course->course_id}'>
                                            <i class='fa-solid fa-circle-xmark settings-icons'></i>
                                            $langUnregCourse
                                        </button>";
                                    }
                                }
                            $html .= "</div>
                        </div>
                    </div>

                    <div class='modal fade' id='exampleModal{$course->course_id}' tabindex='-1' aria-labelledby='exampleModalLabel{$course->course_id}' aria-hidden='true'>
                        <form method='post' action='{$urlAppend}main/unregcours.php?u={$_SESSION['uid']}&amp;cid={$course->course_id}'>
                            <div class='modal-dialog modal-md'>
                                <div class='modal-content'>
                                    <div class='modal-header'>

                                        <div class='modal-title' id='exampleModalLabel{$course->course_id}'>
                                            <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                                            <div class='modal-title-default text-center text-center mb-0'>$langUnCourse</div>
                                        </div>
                                    </div>
                                    <div class='modal-body text-center'>
                                        $langConfirmUnregCours<strong class='text-capitalize'>&nbsp;" . q($course->title) . "</strong>;
                                        <input type='hidden' name='fromMyCoursesPage' value='1'>
                                    </div>
                                    <div class='modal-footer d-flex justify-content-center align-items-center'>
                                        <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                        <button type='submit' class='btn deleteAdminBtn' name='doit'>$langUnCourse</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>";


                    if($countCards == 6 and $temp_pages < count($myCourses)){
                        $pagesPag++;
                        $countCards = 0;
                    }
                    $countCards++;
                    $allCourses++;

                  }

            $html .= "</div>";

            if($pagesPag > 0){
                $html .= GroupCardsPagination($allCourses,$pagesPag);
            }

            $html .= "
        </div>";
  }else{
      $html .= "<div class='col-12'>
          <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCourses</span></div>
      </div>";
  }


  echo($html);

  exit();

}

$data['action_bar']  = action_bar([
    [ 'title' => $langRegCourses,
      'url' => $urlAppend . 'modules/auth/courses.php',
      'icon' => 'fa-pen-to-square',
      'level' => 'primary-label',
      'button-class' => 'submitAdminBtn' ],
    [ 'title' => $langCourseCreate,
      'url' => $urlAppend . 'modules/create_course/create_course.php',
      'show' => $_SESSION['status'] == USER_TEACHER,
      'icon' => 'fa-plus-circle',
      'level' => 'primary-label',
      'button-class' => 'submitAdminBtnDefault' ]

], false);

$data['menuTypeID'] = 1;

view('main.my_courses.index', $data);


function GroupCardsPagination($allCourses,$pagesPag){

    global $langPreviousPage, $langNextPage;

  $pagination = "";


  $pagination .= "<input type='hidden' id='KeyallCourse' value='$allCourses'>
            <input type='hidden' id='KeypagesCourse' value='$pagesPag'>

            <div class='col-12 d-flex justify-content-center Borders p-0 overflow-auto bg-transparent mt-4'>
                <nav role='navigation' aria-label='Pagination Navigation'>
                    <ul class='pagination mycourses-pagination w-100 mb-0'>
                        <li class='page-item page-item-previous'>
                            <a class='page-link' aria-label='$langPreviousPage'><span class='fa-solid fa-chevron-left'></span></a>
                        </li>";
                        if($pagesPag >=12 ){
                            for($i=1; $i<=$pagesPag; $i++){

                                if($i>=1 && $i<=5){
                                    if($i==1){
                                        $pagination .= "<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                            <a id='Keypage{$i}' class='page-link'>{$i}</a>
                                        </li>

                                        <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                            <a>...</a>
                                        </li>";
                                    }else{
                                        if($i<$pagesPag){
                                            $pagination .= "<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                                <a id='Keypage{$i}' class='page-link'>{$i}</a>
                                            </li>";
                                        }
                                    }
                                }

                                if($i>=6 && $i<=$pagesPag-1){
                                    $pagination .= "<li id='KeypageCenter{$i}' class='page-item page-item-pages d-none'>
                                        <a id='Keypage{$i}' class='page-link'>{$i}</a>
                                    </li>";

                                    if($i==$pagesPag-1){
                                        $pagination .= "<li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                            <a>...</a>
                                        </li>";
                                    }
                                }

                                if($i==$pagesPag){
                                    $pagination .= "<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                        <a id='Keypage{$i}' class='page-link'>{$i}</a>
                                    </li>";
                                }
                            }

                        }else{
                            for($i=1; $i<=$pagesPag; $i++){
                                $pagination .= "<li id='KeypageCenter{$i}' class='page-item page-item-pages'>
                                    <a id='Keypage{$i}' class='page-link'>{$i}</a>
                                </li>";
                            }
                        }

                        $pagination .=" <li class='page-item page-item-next'>
                            <a class='page-link' aria-label='$langNextPage'><span class='fa-solid fa-chevron-right'></span></a>
                        </li>
                    </ul>
                </nav>
            </div>


            <script type='text/javascript'>

                var arrayLeftRight = [];

                // init page1
                if(arrayLeftRight.length == 0){
                    var totalCourses = $('#KeyallCourse').val();
                    for(j=1; j<=totalCourses; j++){
                        if(j!=1){
                            $('.cardCourse'+j).removeClass('d-block');
                            $('.cardCourse'+j).addClass('d-none');
                        }else{
                            $('.page-item-previous').addClass('disabled');
                            $('.cardCourse'+j).removeClass('d-none');
                            $('.cardCourse'+j).addClass('d-block');
                            $('#Keypage1').addClass('active');
                        }
                    }
                    var totalPages = $('#KeypagesCourse').val();
                    if(totalPages == 1){
                        $('.page-item-previous').addClass('disabled');
                        $('.page-item-next').addClass('disabled');
                    }
                }


                // prev-button
                $('.page-item-previous .page-link').on('click',function(){

                    var prevPage;

                    $('.page-item-pages .page-link.active').each(function(index, value){
                        var IDCARD = this.id;
                        var number = parseInt(IDCARD.match(/\d+/g));
                        prevPage = number-1;

                        arrayLeftRight.push(number);

                        var totalCourses = $('#KeyallCourse').val();
                        var totalPages = $('#KeypagesCourse').val();
                        for(i=1; i<=totalCourses; i++){
                            if(i == prevPage){
                                $('.cardCourse'+i).removeClass('d-none');
                                $('.cardCourse'+i).addClass('d-block');
                                $('#Keypage'+prevPage).addClass('active');
                            }else{
                                $('.cardCourse'+i).removeClass('d-block');
                                $('.cardCourse'+i).addClass('d-none');
                                $('#Keypage'+i).removeClass('active');
                            }
                        }

                        if(prevPage == 1){
                            $('.page-item-previous').addClass('disabled');
                        }else{
                            if(prevPage < totalPages){
                                $('.page-item-next').removeClass('disabled');
                            }
                            $('.page-item-previous').removeClass('disabled');
                        }


                        //create page-link in center
                        if(number <= totalPages-3 && number >= 6 && totalPages>=12){

                            $('#KeystartLi').removeClass('d-none');
                            $('#KeystartLi').addClass('d-block');

                            for(i=2; i<=totalPages-1; i++){
                                $('#KeypageCenter'+i).removeClass('d-block');
                                $('#KeypageCenter'+i).addClass('d-none');
                            }

                            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                            var currentPage = number-1;
                            $('#KeypageCenter'+currentPage).removeClass('d-none');
                            $('#KeypageCenter'+currentPage).addClass('d-block');

                            var prevPage = number-2;
                            $('#KeypageCenter'+prevPage).removeClass('d-none');
                            $('#KeypageCenter'+prevPage).addClass('d-block');

                            $('#KeycloseLi').removeClass('d-none');
                            $('#KeycloseLi').addClass('d-block');

                        }else if(number <= 5 && totalPages>=12){

                            $('#KeystartLi').removeClass('d-block');
                            $('#KeystartLi').addClass('d-none');

                            for(i=6; i<=totalPages-1; i++){
                                $('#KeypageCenter'+i).removeClass('d-block');
                                $('#KeypageCenter'+i).addClass('d-none');
                            }

                            $('#KeycloseLi').removeClass('d-none');
                            $('#KeycloseLi').addClass('d-block');


                            for(i=1; i<=number; i++){
                                $('#KeypageCenter'+i).removeClass('d-none');
                                $('#KeypageCenter'+i).addClass('d-block');
                            }

                        }

                    });

                });




                // next-button
                $('.page-item-next .page-link').on('click',function(){

                    $('.page-item-pages .page-link.active').each(function(index, value){
                        var IDCARD = this.id;
                        var number = parseInt(IDCARD.match(/\d+/g));
                        arrayLeftRight.push(number);
                        var nextPage = number+1;

                        var delPageActive = nextPage-1;
                        $('#Keypage'+delPageActive).removeClass('active');
                        $('#Keypage'+nextPage).addClass('active');

                        var totalCourses = $('#KeyallCourse').val();
                        var totalPages = $('#KeypagesCourse').val();

                        for(i=1; i<=totalCourses; i++){
                            if(i == nextPage){
                                $('.cardCourse'+i).removeClass('d-none');
                                $('.cardCourse'+i).addClass('d-block');
                                // $('#Keypage'+nextPage).addClass('active');
                            }else{
                                $('.cardCourse'+i).removeClass('d-block');
                                $('.cardCourse'+i).addClass('d-none');
                                //$('#Keypage'+i).removeClass('active');
                            }
                        }

                        if(totalPages > 1){
                            $('.page-item-previous').removeClass('disabled');
                        }
                        if(nextPage == totalPages){
                            $('.page-item-next').addClass('disabled');
                        }else{
                            $('.page-item-next').removeClass('disabled');
                        }


                        //create page-link in center
                        if(number >= 4 && number < totalPages-5 && totalPages>=12){//5-7

                            $('#KeystartLi').removeClass('d-none');
                            $('#KeystartLi').addClass('d-block');

                            for(i=2; i<=totalPages-1; i++){
                                $('#KeypageCenter'+i).removeClass('d-block');
                                $('#KeypageCenter'+i).addClass('d-none');
                            }

                            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                            $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                            var currentPage = number+1;
                            $('#KeypageCenter'+currentPage).removeClass('d-none');
                            $('#KeypageCenter'+currentPage).addClass('d-block');

                            var nextPage = number+2;
                            $('#KeypageCenter'+nextPage).removeClass('d-none');
                            $('#KeypageCenter'+nextPage).addClass('d-block');

                            $('#KeycloseLi').removeClass('d-none');
                            $('#KeycloseLi').addClass('d-block');

                        }else if(arrayLeftRight[arrayLeftRight.length-1] >= totalPages-5 && totalPages>=12){//>=8

                            $('#KeystartLi').removeClass('d-none');
                            $('#KeystartLi').addClass('d-block');

                            for(i=2; i<=totalPages-5; i++){
                                $('#KeypageCenter'+i).removeClass('d-block');
                                $('#KeypageCenter'+i).addClass('d-none');
                            }

                            $('#KeycloseLi').removeClass('d-block');
                            $('#KeycloseLi').addClass('d-none');

                            var nextPage = arrayLeftRight[arrayLeftRight.length-1] + 1;
                            console.log('nextPage:'+nextPage);
                            for(i=nextPage; i<=totalPages; i++){
                                $('#KeypageCenter'+i).removeClass('d-none');
                                $('#KeypageCenter'+i).addClass('d-block');
                            }

                        }else if(number>=1 && number<=4 && totalPages>=12){
                            $('#KeystartLi').removeClass('d-block');
                            $('#KeystartLi').addClass('d-none');

                            for(i=1; i<=4; i++){
                                $('#KeypageCenter'+i).removeClass('d-none');
                                $('#KeypageCenter'+i).addClass('d-block');
                            }
                        }


                    });
                });




                // page-link except prev-next button
                $('.page-item-pages .page-link').on('click',function(){

                    var IDCARD = this.id;
                    var number = parseInt(IDCARD.match(/\d+/g));

                    arrayLeftRight.push(number);

                    var totalPages = $('#KeypagesCourse').val();
                    var totalCourses = $('#KeyallCourse').val();
                    for(i=1; i<=totalCourses; i++){
                        if(i!=number){
                            $('.cardCourse'+i).removeClass('d-block');
                            $('.cardCourse'+i).addClass('d-none');
                        }else{
                            $('.cardCourse'+i).removeClass('d-none');
                            $('.cardCourse'+i).addClass('d-block');
                        }

                        // about prev-next button
                        if(number>1){
                            $('.page-item-previous').removeClass('disabled');
                            $('.page-item-next').removeClass('disabled');
                        }if(number == 1){
                            if(totalPages == 1){
                                $('.page-item-previous').addClass('disabled');
                                $('.page-item-next').addClass('disabled');
                            }
                            if(totalPages > 1){
                                $('.page-item-previous').addClass('disabled');
                                $('.page-item-next').removeClass('disabled');
                            }
                        }if(number == totalPages){
                            $('.page-item-next').addClass('disabled');
                        }if(number < totalPages-1){
                            $('.page-item-next').removeClass('disabled');
                        }
                    }


                    if(number>=1 && number<=4 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-block');
                        $('#KeystartLi').addClass('d-none');

                        for(i=1; i<=5; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }
                        for(i=6; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');
                    }
                    if(number>=5 && number<=totalPages-5 && totalPages>=12){

                        for(i=5; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        var prevPage = number-1;
                        var nextPage = number+1;
                        var currentPage = number;

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');

                        for(i=2; i<=4; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeypageCenter'+prevPage).removeClass('d-none');
                        $('#KeypageCenter'+prevPage).addClass('d-block');

                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        $('#KeypageCenter'+nextPage).removeClass('d-none');
                        $('#KeypageCenter'+nextPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                    }
                    if(number>=totalPages-4 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');

                        for(i=2; i<=totalPages-5; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        for(i=totalPages-4; i<=totalPages; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }


                        $('#KeycloseLi').removeClass('d-block');
                        $('#KeycloseLi').addClass('d-none');
                    }
                    if(number==totalPages-4 && arrayLeftRight[arrayLeftRight.length-2]>number && totalPages>=12){

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');

                        for(i=2; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        var prevPage = number+1;
                        var nextPage = number-1;
                        var currentPage = number;

                        $('#KeypageCenter'+prevPage).removeClass('d-none');
                        $('#KeypageCenter'+prevPage).addClass('d-block');

                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        $('#KeypageCenter'+nextPage).removeClass('d-none');
                        $('#KeypageCenter'+nextPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');
                    }


                    // about active page-item
                    $('.page-item-pages .page-link').each(function(index, value){
                        $('.page-item-pages .page-link').removeClass('active');
                    });
                    $(this).addClass('active');

                });

        </script>";

        return $pagination;
}
