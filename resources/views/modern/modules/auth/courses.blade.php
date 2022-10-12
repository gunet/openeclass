<?php
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////// WARNING!!! //////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      $all_registered_courses = array(); 
      $all_prerequisites_courses = array();
      $cid = array();
      $password = array();
      $myCourses = array();
      $vis_class= array();

      $all_registered_courses[] = $data_all[0];
      $all_prerequisites_courses[] = $data_all[4];
      $cid[] = $data_all[1];
      $password = $data_all[3];
      $myCourses = $data_all[5];
      $vis_class = $data_all[2];

      $prerequisite_course_title = ' ';
      $prerequisite_course_public_code = ' ';
      $i=0;
      foreach($all_prerequisites_courses[0] as $key){
            foreach($key[0] as $value){
                if($i==3){
                    $prerequisite_course_title = $value;
                }
                if($i==8){
                    $prerequisite_course_public_code = $value;
                }
                $i++;
            }
      }

?>


@extends('layouts.default')

@section('content')


    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                        @if($course_code)
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        @else
                            @include('layouts.partials.sidebarAdmin')
                        @endif 
                    </div>
                </div>

                <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                   
                   
                        <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                            @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @if($course_code)
                                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                    @else
                                        @include('layouts.partials.sidebarAdmin')
                                    @endif
                                </div>
                            </div>

                            @if(Session::has('message'))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                                <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    @if(is_array(Session::get('message')))
                                        @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                        @foreach($messageArray as $message)
                                            {!! $message !!}
                                        @endforeach
                                    @else
                                        {!! Session::get('message') !!}
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </p>
                            </div>
                            @endif

                            @if(!$fac)
                                {!! $tool_content2 !!}
                            @else
                            
                            
                                <form action='{{$_SERVER[SCRIPT_NAME]}}' method='post' style="padding-top:20px;">
                                    <ul class='list-group list_grouping' style="padding-top:25px; padding-bottom:25px;">
                                        <li class='list-group-item list-header'>
                                            <a name='top'></a>
                                            {!! $langFaculty !!}:{!! $getFullPath !!}
                                            {!! $childHtml !!}
                                        </li>
                                    </ul>
                                </form>

                                @if($numofcourses > 0 )
                                <form action="{{ $urlAppend }}modules/auth/courses.php" method="post">
                                
                                    <div class='table-responsive'>
                                        <table id="mycoursesregister_table" class='table-default table_register_to_course'>
                                            <thead class="notes_thead">
                                                <tr>
                                                    <th class="text-white" scope="col"><span class="types-cols" >{!! $langRegistration !!}</span></th>
                                                    <th class="text-white" scope="col"><span class="types-cols" >{!! $langCourseCode !!}</span></th>
                                                    <th class="text-white" scope="col"><span class="types-cols" >{!! $langTeacher !!}</span></th>
                                                    <th class="text-white" scope="col"><span class="types-cols" >{!! $langType !!}</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="ps-3">
                                                <?php $user = Database::get()->querySingle("SELECT * FROM `user` WHERE `user`.`id`='{$uid}' "); ?>
                                                <?php for($i=0; $i<count($all_registered_courses[0]); $i++){ ?>
                                                    <?php if($user->username != "admin"){ ?>
                                                        <tr>
                                                            <?php $myVar = strip_tags($all_registered_courses[0][$i]);?>
                                                            <?php $course_visible = Database::get()->querySingle("SELECT * FROM `course` WHERE `course`.`title`='{$myVar}' "); ?>
                                                            <?php $myTeacher = strip_tags($all_registered_courses[0][$i]);?>
                                                            <?php $course_teacher = Database::get()->querySingle("SELECT * FROM `course` WHERE `course`.`title`='{$myTeacher}' "); ?>
                                                            <?php if(isset($myCourses[$cid[0][$i]])) {?>
                                                                <?php if ($myCourses[$cid[0][$i]]->status != 1) { ?>
                                                                        <th scope="row">
                                                                            <label class="checkbox_container2">
                                                                                <input type='checkbox' name='selectCourse[]' value='{{$cid[0][$i]}}' checked='checked' $vis_class[$i] >
                                                                                <input type='hidden' name='changeCourse[]' value='{{$cid[0][$i]}}' >
                                                                                <span class="checkmark2"></span>
                                                                                
                                                                            </label>
                                                                        </th>
                                                                        <?php if (!empty($password[$i])) { ?>
                                                                            <td class="ps-3">
                                                                                <a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a><br>
                                                                                <small>{{trans('langPassCode')}}:</small> <input type='password' name='pass{{$cid[0][$i]}}' value='{{$password[$i]}}' autocomplete='off' />
                                                                            
                                                                            </td>
                                                                            <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                            <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                        <?php }else{ ?>
                                                                            <td class="ps-3"><a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a></td>
                                                                            <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                            <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                        <?php } ?>
                                                                        
                                                                <?php } ?>
                                                                    
                                                            <?php }else{ ?>
                                                                <?php if($course_visible->visible == 1){?>
                                                                    <th scope="row">
                                                                        <label class="checkbox_container2">
                                                                            <input type='checkbox' name='selectCourse[]' value='{{$cid[0][$i]}}' $vis_class[$i] />
                                                                            <input type='hidden' name='changeCourse[]' value='{{$cid[0][$i]}}' >
                                                                            <span class="checkmark2"></span>
                                                                            
                                                                        </label>
                                                                    </th>
                                                                    <td class="ps-3">
                                                                        <a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a><br>
                                                                        <small style='color:red'>{{trans('langPassCode')}}:</small> <input type='password' name='pass{{$cid[0][$i]}}' autocomplete='off' />
                                                                    </td>
                                                                    <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                    <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                <?php }else if($course_visible->visible == 2){?>
                                                                    <th scope="row">
                                                                        <label class="checkbox_container2">
                                                                                <input type='checkbox' name='selectCourse[]' value='{{$cid[0][$i]}}' $vis_class[$i] />
                                                                                <input type='hidden' name='changeCourse[]' value='{{$cid[0][$i]}}' >
                                                                                <span class="checkmark2"></span>
                                                                                
                                                                        </label>
                                                                        </th>
                                                                    <td class="ps-3"><a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a></td>
                                                                    <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                    <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                <?php }else if($course_visible->visible == 3){ ?>
                                                                    <th scope="row">
                                                                        <label class="checkbox_container2">
                                                                                <input type='checkbox' name='selectCourse[]' value='{{$cid[0][$i]}}' disabled $vis_class[$i] />
                                                                                <input type='hidden' name='changeCourse[]' value='{{$cid[0][$i]}}' >
                                                                                <span class="checkmark2"></span>
                                                                                
                                                                            </label>
                                                                        </th>
                                                                    <td class="ps-3"><a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a></td>
                                                                    <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                    <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                <?php }else{ ?>
                                                                    <th scope="row">
                                                                        <label class="checkbox_container2">
                                                                                <input type="checkbox" name="selectCourse[]" value='{{$cid[0][$i]}}' disabled />
                                                                                <input type='hidden' name='changeCourse[]' value='{{$cid[0][$i]}}' >
                                                                                <span class="checkmark2"></span>
                                                                                
                                                                            </label>
                                                                    </th>
                                                                    <td class="ps-3"><a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a></td>
                                                                    <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                                    <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                                <?php } ?>
                                                                
                                                            <?php } ?>   
                                                        </tr>
                                                    <?php }else{ ?>
                                                        <?php $myVar = strip_tags($all_registered_courses[0][$i]);?>
                                                        <?php $course_visible = Database::get()->querySingle("SELECT * FROM `course` WHERE `course`.`title`='{$myVar}' "); ?>
                                                        <?php $myTeacher = strip_tags($all_registered_courses[0][$i]);?>
                                                        <?php $course_teacher = Database::get()->querySingle("SELECT * FROM `course` WHERE `course`.`title`='{$myTeacher}' "); ?>
                                                        <tr>
                                                            
                                                            <th class="ps-3" scope="row"><i class='fas fa-user'></i></th>
                                                            <td class="ps-3"><a class="info_reg_course" href="{{ $urlAppend }}main/info_mycourse.php?title=<?php echo $course_visible->title ?>">{!! $all_registered_courses[0][$i] !!}</a></td>
                                                            <td class="ps-3">{!! $course_teacher->prof_names !!}</td>
                                                            <?php if($course_visible->visible == 1){?>
                                                                <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?><i class="fas fa-pen-fancy" style="color:orange; margin-top:-20px; margin-left:10px;"></td>
                                                            <?php }else{ ?>
                                                                <td class="ps-3"><?php echo course_access_icon($course_visible->visible) ?></td>
                                                            <?php } ?>

                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row p-2"></div>
                                    <input class="btn btn-success" type="submit" name="submit_register_course" value="{{trans('langSubmitChanges')}}">

                                    
                                </form>
                            @endif
                                        
                        </div>
                    @endif
                        
                   
                </div>

            </div>
        </div>
    </div>      

@endsection
    
    

