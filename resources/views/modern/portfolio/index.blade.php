<?php
    /** User */

    $fullname = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
    $user = Database::get()->querySingle("SELECT * FROM `user` WHERE `user`.`id`={$uid} ");
    $registered_at = substr($user->registered_at,0,10);

    /** Cources */
    $cources = Database::get()->queryArray(
       "SELECT `course`.*,`course_user`.`tutor` FROM `course` JOIN  `course_user` ON `course`.`id`=`course_user`.`course_id` WHERE `course_user`.`user_id`={$uid} ");
    
    $course_tutor_N = 0; $course_std_N = 0;
    foreach($cources as $cource) { 
        if( $cource->tutor == 1 ) { $course_tutor_N++; } else { $course_std_N++; } 

        /** Fake Image */
        // if( $cource->course_image == null ) { 
        //     $n=rand(1,4);
        //     $cource->course_image = "template/modern/images/img{$n}b.jpg";
        // }
    }

    $items_per_page = 8;
    $cource_pages = ceil(count($cources)/$items_per_page);




    
?>

@extends('layouts.default')

@section('content')

<script>  
    
    var user_cources = <?php echo json_encode($cources); ?>;
    var user_cource_pages = <?php echo $cource_pages; ?>;
    
</script>



<div class="pb-3 pt-3">


<div class="container-fluid main-container details-section">

    <div class="row">
        <div class="col-lg-12 user-details" >
            <div class="row p-5">

                <div class="container-fluid">
                    <div class="row block-title-2 justify-content-between">
                        <div class="col-3 col-md-6">
                            <h4 style="margin-left:-10px; font-size:20px;"class="">ΣΥΝΟΠΤΙΚΟ ΠΡΟΦΙΛ</h4>
                        </div>
                        <div class="col-4 col-xl-4 col-md-6">
                            <div class="collapse-details-button" data-bs-toggle="collapse" data-bs-target=".user-details-collapse" aria-expanded="false" onclick="switch_user_details_toggle()" >
                                <span style="float:right; margin-right:-10px;" class="user-details-collapse-more"> ΠΕΡΙΣΣΟΤΕΡΕΣ ΠΛΗΡΟΦΟΡΙΕΣ <i class="fas fa-chevron-down"></i> </span>
                                <span style="float:right; margin-right:-10px;"class="user-details-collapse-less"> ΣΥΝΟΠΤΙΚΟ ΠΡΟΦΙΛ <i class="fas fa-chevron-up"></i> </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="container-fluid collapse user-details-collapse show">

                    <div class="row">
    
                        <div class="col-xl-4 col-lg-6 col-md-8 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12" style="margin-left:-20px;">
                                        <img class="user-detals-photo" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $fullname }}">
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12" style="margin-left:20px;">
                                        <div class="user-detals-fullname">
                                            <h5> {{ $fullname }} </h5>
                                        </div>
                                        <div>Εκπαιδευτικός</div>
                                        <div class="text-secondary" style="margin-top: 40px;"> {{$user->username}} </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-xl-8 col-lg-6 col-md-4 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <p>Μαθήματα που παρακολουθώ: <strong>{{$course_std_N}}  </strong></p>
                                        <p>Μαθήματα που υποστηρίζω:  <strong>{{$course_tutor_N}}</strong></p>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <span>Τελευταία επίσκεψη:</span> Δευτέρα, 22 Μαρτίου 2021
                                    </div>
                                </div>

                            </div>
                        </div>
    

    
                    </div>
                </div>

                <div class="container-fluid collapse user-details-collapse">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row justify-content-center">
                                    <div class="user-detals-photo-2">
                                        <img src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $fullname }}">
                                    </div>
                                </div>
                                <div class="row justify-content-center text-center" >
                                    <h5> {{ $fullname }} </h5>
                                    <p> Εκπαιδευτικός </p>
                                </div>
                                <div class="row justify-content-center text-center">
                                    <div class="py-1" >
                                        <a href="{{ $urlAppend }}main/profile/profile.php" class="btn btn-outline-primary btn-rounded"><i class="fas fa-pen"></i> ΕΠΕΞΕΡΓΑΣΙΑ ΠΡΟΦΙΛ</a>
                                    </div>
                                    <div class="py-1">
                                        <a href="#" class="btn btn-outline-warning btn-rounded"><i class="fas fa-trash"></i> ΔΙΑΓΡΑΦΗ ΛΟΓΑΡΙΑΣΜΟΥ</a>
                                    </div>
                                    <div class="py-1">
                                        ! Για να διαγραφείτε από την πλατφόρμα, πρέπει πρώτα να απεγγραφείτε από τα μαθήματα που είστε εγγεγραμμένος.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-6 ">
                                        <p>Μαθήματα που παρακολουθώ: <strong>{{$course_std_N}}</strong></p>
                                        <p>Μαθήματα που υποστηρίζω: <strong>{{$course_tutor_N}}</strong></p>
                                    </div>
                                    <div class="col-6  ">
                                        <span>Τελευταία επίσκεψη:</span> Δευτέρα, 22 Μαρτίου 2021
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="container-fluid">

                                        <div class="row block-title-3">
                                            <p>ΠΡΟΣΩΠΙΚΑ ΣΤΟΙΧΕΙΑ</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >E-mail:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $_SESSION['email'] }}</h5>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{trans('langStatus')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{trans('langMetaTeacher')}}: </h5>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{trans('langFaculty')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > - </h5>
                                            </div>
                                            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary">{{trans('langPhone')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $user->phone }}</h5>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{trans('langAm')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $user->am }}</h5>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{trans('langProfileMemberSince')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $registered_at }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="row border-bottom border-primary block-title-3">
                                            <p>ΣΧΕΤΙΚΑ ΜΕ ΕΜΕΝΑ</p>
                                        </div>
                                        <p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet 
                                            dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit 
                                            lobortis nisl ut aliquip ex ea commodo. </p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row border-bottom border-primary block-title-3">
                                            <p>ΤΑ ΕΝΔΙΑΦΕΡΟΝΤΑ ΜΟΥ</p>
                                        </div>
                                        <p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet 
                                            dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit 
                                            lobortis nisl ut aliquip ex ea commodo.Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit 
                                            lobortis nisl ut aliquip ex ea commodo. </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid main-container cources-section mt-3">
    <div class="row">
        <div class="col-12 col-xl-8 user-details">
            <div class="row p-5">
                <div class="container-fluid mycourses_view_container" id="mycourses_view">
                    <div class="row border-bottom border-primary justify-content-between mb-4">

                        <div class="col-4">
                            <span class="text-primary" style="font-size:20px; margin-left:-10px;">ΤΑ ΜΑΘΗΜΑΤΑ ΜΟΥ</span>
                        </div>
                        
                        <div class="col-xl-1 col-lg-1 col-md-2 col-sm-2 col-xs-2">
                            <div id="bars-active" style="display:flex;">
                                <div id="cources-bars-button" 
                                    class="collapse-cources-button text-primary" >
                                    <span class="list-style active"><i class="fas fa-custom-size fa-bars"></i></span>
                                </div>
                                <div id="cources-pics-button" 
                                    class="collapse-cources-button text-secondary collapse-cources-button-deactivated"
                                    onclick="switch_cources_toggle()">
                                    <span class="grid-style"><i class="fas fa-custom-size fa-th-large"></i></span>
                                </div>
                            </div>
                            
                            <div id="pics-active" style="display:none">
                                <div id="cources-bars-button" 
                                    class="collapse-cources-button text-secondary collapse-cources-button-deactivated"
                                    onclick="switch_cources_toggle()">
                                    <span class="list-style active"><i class="fas fa-custom-size fa-bars"></i></span>
                                </div>
                                <div id="cources-pics-button" class="collapse-cources-button text-primary">
                                    <span class="grid-style"><i class="fas fa-custom-size fa-th-large"></i></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div id="cources-bars" class="container-fluid">
                    <div class="row cources-bars-page" id="cources-bars-page-1">
                        @foreach($cources as $i => $cource )
                        <div class="col-12">
                            <div class="lesson">
                                <h3 class="lesson-title">
                                    <a class="lesson_title_a" style="text-decoration:none; font-size:18px;" href="{{$urlAppend}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a> 
                                    <span class="lesson-id" style="font-size:17px;">({{ $cource->code }})</span>
                                </h3>
                                <div class="lesson-professor">{{ $cource->prof_names }}</div>
                            </div>
                            <hr>
                        </div>
                            @if( $i>0 && ($i+1)%$items_per_page==0 )
                    </div>
                    <div class="row cources-bars-page" style="display:none" id="cources-bars-page-{{ceil($i/$items_per_page)+1}}" >
                            @endif
                        @endforeach
                    </div>
                    @include('portfolio.portfolio-courcesnavbar', ['paging_type' => 'bars', 'cource_pages' => $cource_pages])
                </div>

                <div id="cources-pics" class="container-fluid cources-paging" style="display:none">
                    <div class="row cources-pics-page" id="cources-pics-page-1">
                        @foreach($cources as $i => $cource )
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="lesson">
                                <figure class="lesson-image" style="background-color:#f5f5f5">
                                    <a href="{{$urlServer}}courses/{{$cource->code}}/index.php">
                                    <picture>
                                        @if($cource->course_image == NULL)
                                            <img class="imageCourse" src="{{ $urlAppend }}template/modern/images/no-image-found-360x250.png" alt="{{ $cource->course_image }}" /></a> 
                                        @else
                                            <img class="imageCourse" src="{{$urlAppend}}courses/{{$cource->code}}/image/{{$cource->course_image}}" alt="{{ $cource->course_image }}" /></a> 
                                        @endif
                                    </picture>
                                    
                                </figure>
                                <h3 class="lesson-title">
                                    <a class="lesson_title_a" style="text-decoration:none; font-size:18px;" href="{{$urlServer}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a> 
                                    <span class="lesson-id" style="font-size:18px;">({{ $cource->code }})</span>
                                </h3>
                                <div class="lesson-professor">{{ $cource->prof_names }}</div>
                            </div>
                            <hr>
                        </div>
                            @if( $i>0 && ($i+1)%$items_per_page==0 )
                    </div>
                    <div class="row cources-pics-page" style="display:none" id="cources-pics-page-{{ceil($i/$items_per_page)+1}}" >
                            @endif
                        @endforeach
                    </div>
                    @include('portfolio.portfolio-courcesnavbar', ['paging_type' => 'pics', 'cource_pages' => $cource_pages])
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">

      
            <div class="container-fluid container_fluid_calendar">
                @include('portfolio.portfolio-calendar')
            </div>
 

            <div class="container-fluid user-announcements-portfolio">
                <div class="row p-5">
                    
                    <h5 class="text-primary">Οι τελευταίες μου ανακοινώσεις</h5>
                    <hr style="color:blue">  
                            

                    <div class="container-fluid">
                        {!! $user_announcements !!}
                    </div>

                    <div class="row p-2"></div>
                    <div class="row p-2"></div>

                    <div class="container-fluid">
                        <a href="{{$urlAppend}}modules/announcements/myannouncements.php">ΟΛΕΣ ΟΙ ΑΝΑΚΟΙΝΩΣΕΙΣ</a>
                    </div>

                </div>
            </div>

            
            <div class="container-fluid user-messages-portfolio">
                <div class="row p-5">

                    <h5 class="text-primary">Πρόσφατα μηνύματα</h5>
                    <hr style="color:blue">

                    <div class="container-fluid">
                        {!! $user_messages !!}
                    </div>

                    <div class="row p-2"></div>
                    <div class="row p-2"></div>


                    <div class="container-fluid">
                        <a href="{{$urlAppend}}modules/message/index.php">ΟΛΑ ΤΑ ΜΗΝΥΜΑΤΑ</a>
                    </div>

                    
                </div>
            </div>
            

        </div>
    </div>
</div>

</div>
@endsection
