@extends('layouts.default')

@section('content')

<div class='col-12 main-section-mobile Primary-100-bg'>
    <div class='{{ $container }}'>
        <div class='row rowMargin'>
            <div class='col-12 d-lg-flex justify-content-lg-between align-items-lg-center'>

                <div class='d-lg-flex'>
                    <div class='text-lg-start text-center'>
                        <img class="user-detals-photo m-auto d-block" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                        <div class='col-lg-12 col-md-3 col-6 ms-auto me-auto'>
                            <a class='btn submitAdminBtn mt-2' href='{{ $urlAppend }}main/profile/display_profile.php'>
                                <i class='fa-solid fa-user'></i>&nbsp&nbsp{{ trans('langMyProfile') }}
                            </a>
                        </div>
                    </div>
                    <div class='px-3 text-lg-start text-center'>
                        <h4 class='mb-0 mt-4'> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h4>
                        <p class='Neutral-900-cl mb-0'>
                            @if(($session->status == USER_TEACHER))
                                {{ trans('langMetaTeacher') }}
                            @elseif(($session->status == USER_STUDENT))
                                {{ trans('langStudent') }}
                            @else
                                {{ trans('langAdministrator')}}
                            @endif
                        </p>
                        <p class='vsmall-text Neutral-800-cl mb-0'>
                            {{ trans('langProfileLastVisit') }}&nbsp:&nbsp{{ format_locale_date(strtotime($lastVisit->when)) }}
                        </p>
                    </div>
                </div>

                <div class='mt-lg-0 mt-4'>
                    <div class='col-12'>
                        <div class='row rowMargin row-cols-1 row-cols-lg-2 g-4'>
                            <div class='col'>
                                <div class='card user-info-card border-0 h-100'>
                                    <div class='card-body d-flex justify-content-center align-items-center'>
                                        <div>
                                            <h1 class='d-flex justify-content-center align-items-center'>
                                                <i class='fa-solid fa-book-open pe-2'></i>{{ $student_courses_count }}
                                            </h1>
                                            <div class='form-label text-center'>{!! trans('langSumCoursesEnrolled') !!}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col'>
                                <div class='card user-info-card border-0 h-100'>
                                    <div class='card-body d-flex justify-content-center align-items-center'>
                                        <div>
                                            <h1 class='d-flex justify-content-center align-items-center'>
                                                <i class='fa-solid fa-book-reader pe-2'></i>{{ $teacher_courses_count }}
                                            </h1>
                                            <div class='form-label text-center'>{!! trans('langSumCoursesSupport') !!}</div>
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
</div>





<div class='col-12 main-section-courses'>
    <div class='{{ $container }}'>
        <div class='row rowMargin row-cols-1 row-cols-lg-2 g-5'>
            <div class='col-lg-8 col-12'>
                <div class='card border-0'>
                    <div class='card-header d-md-flex justify-content-md-between align-items-md-center px-0 bg-white border-0'>
                        @php $totalCourses = $student_courses_count + $teacher_courses_count; @endphp
                        <h2>{{ trans('langMyCoursesSide') }}&nbsp({{ $totalCourses }})</h2>
                        <div class='d-flex'>
                            <a class="btn submitAdminBtn @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user) me-2 @endif" href="{{ $urlAppend }}modules/auth/courses.php">
                                <i class="fa-regular fa-pen-to-square"></i>&nbsp
                                {{ trans('langRegister') }}
                            </a>
                            @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                <a id="btn_create_course" class="btn submitAdminBtn submitAdminBtnDefault" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                    <i class="fa-solid fa-plus"></i>&nbsp{{ trans('langCreate') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class='card-body px-0'>

                        @if(Session::has('message'))
                            <div class='col-12 mt-3 px-0'>
                                <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    @php 
                                        $alert_type = '';
                                        if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                            $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                            $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                            $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                        }else{
                                            $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                        }
                                    @endphp
                                    
                                    @if(is_array(Session::get('message')))
                                        @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                        {!! $alert_type !!}<span>
                                        @foreach($messageArray as $message)
                                            {!! $message !!}
                                        @endforeach</span>
                                    @else
                                        {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                                    @endif
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif


                        <div id="cources-bars">
                            {!! $perso_tool_content['lessons_content'] !!}
                        </div>

                        <div id="cources-pics" class="col-12">

                            <div class="d-flex justify-content-end flex-wrap mb-4">
                                <a class="btn showCoursesBars">
                                    <i class="fa-solid fa-table-list"></i>
                                </a>
                                <a class="btn showCoursesPics">
                                    <i class="fa-solid fa-table-cells-large"></i>
                                </a>
                            </div>

                            <div class="row rowMargin row-cols-1 row-cols-md-2 g-4">
                                @php
                                    $pagesPag = 0;
                                    $allCourses = 0;
                                    $temp_pages = 0;
                                    $countCards = 1;
                                    if($countCards == 1){
                                        $pagesPag++;
                                    }
                                @endphp
                                @foreach($cources as $cource)
                                    @php $temp_pages++; @endphp
                                    <div class="col cardCourse{{ $pagesPag }}">
                                        <div class="card h-100 card{{ $pagesPag }} Borders border-card">
                                            @php 
                                                $courseImage = ''; 
                                                if(!empty($course->course_image)){
                                                    $courseImage = "{$urlServer}courses/$course->code/image/$course->course_image";
                                                }else{
                                                    $courseImage = "{$urlServer}template/modern/img/ph1.jpg";
                                                }
                                            @endphp 

                                            @if($cource->course_image == NULL)
                                                <img class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" src="{{ $urlAppend }}template/modern/img/ph1.jpg" alt="{{ $cource->course_image }}" />
                                            @else
                                                <img class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" src="{{$urlAppend}}courses/{{$cource->code}}/image/{{$cource->course_image}}" alt="{{ $cource->course_image }}" />
                                            @endif
                                                    
                                            <div class='card-body'>
                                                <div class="lesson-title">
                                                    <a class='TextRegular text-decoration-underline' href="{{$urlServer}}courses/{{$cource->code}}/index.php">
                                                        {{ $cource->title }}&nbsp({{ $cource->public_code }})
                                                    </a>
                                                </div>

                                                <div class="vsmall-text Neutral-900-cl TextRegular">{{ $cource->professor }}</div>
                                            </div>
                                        
                                        </div>
                                    </div>

                                    @php
                                        if($countCards == 6 and $temp_pages < count($cources)){
                                            $pagesPag++;
                                            $countCards = 0;
                                        }
                                        $countCards++;
                                        $allCourses++;
                                    @endphp
                                @endforeach

                            </div>

                            <input type='hidden' id='KeyallCourse' value='{{ $allCourses }}'>
                            <input type='hidden' id='KeypagesCourse' value='{{ $pagesPag }}'>
                            
                            <div class='col-12 d-flex justify-content-center Borders p-0 overflow-auto bg-white solidPanel mt-4'>
                                <nav aria-label='Page navigation example w-100'>
                                    <ul class='pagination mycourses-pagination w-100 mb-0'>
                                        <li class='page-item page-item-previous'>
                                            <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-left'></span></a>
                                        </li>
                                        @if($pagesPag >=12 )
                                            @for($i=1; $i<=$pagesPag; $i++)
                                            
                                                @if($i>=1 && $i<=5)
                                                    @if($i==1)
                                                        <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                            <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                        </li>

                                                        <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                            <a>...</a>
                                                        </li>
                                                    @else
                                                        @if($i<$pagesPag)
                                                            <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                                <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                @endif

                                                @if($i>=6 && $i<=$pagesPag-1)
                                                    <li id='KeypageCenter{{$i}}' class='page-item page-item-pages d-none'>
                                                        <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                    </li>

                                                    @if($i==$pagesPag-1)
                                                        <li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                            <a>...</a>
                                                        </li>
                                                    @endif
                                                @endif

                                                @if($i==$pagesPag)
                                                    <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                        <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                    </li>
                                                @endif
                                            @endfor
                                        
                                        @else
                                            @for($i=1; $i<=$pagesPag; $i++)
                                                <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                    <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                </li>
                                            @endfor
                                        @endif

                                        <li class='page-item page-item-next'>
                                            <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-right'></span></a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>

                        </div>


                        @if($portfolio_page_main_widgets)
                            <div class='panel panel-admin border-0 bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                {!! $portfolio_page_main_widgets !!}
                            </div>
                        @endif
                        
                    </div>
                </div>
            </div>
            <div class='col-lg-4 col-12'>

                <div class='col-12 mb-4 mt-1'><h3>{{ trans('langAgenda') }}</h3></div>
                @include('portfolio.portfolio-calendar')

                <div class='card bg-transparent border-0 mt-5'>
                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                        <h3 class='mb-0'>{{ trans('langAnnouncements') }}</h3>
                        <a class='text-decoration-underline vsmall-text' href="{{$urlAppend}}modules/announcements/myannouncements.php">
                            {{ trans('langAllAnnouncements') }}
                        </a>
                    </div>
                    <div class='card-body px-0'>
                        @if(empty($user_announcements))
                            <div class='text-start mb-3'><span class='text-title not_visible'>{{ trans('langNoRecentAnnounce') }}</span></div>
                        @else
                            {!! $user_announcements !!}
                        @endif
                    </div>
                </div>

                <div class='card bg-transparent border-0 mt-5'>
                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                    
                        <h3 class='mb-0'>{{ trans('langMessages') }}</h3>
                        <a class='text-decoration-underline vsmall-text' href="{{$urlAppend}}modules/message/index.php">
                            {{ trans('langAllMessages') }}
                        </a>
                        
                    </div>
                    <div class='card-body px-0'>
                        @if(empty($user_messages))
                            <div class='text-start mb-3'><span class='text-title not_visible'>{{ trans('langDropboxNoMessage') }}</span></div>
                        @else
                            {!! $user_messages !!}
                        @endif
                    </div>
                </div>

                @if($portfolio_page_sidebar_widgets)
                    <div class='card panelCard border-0 BorderSolid bg-white mt-lg-3 mt-4 py-lg-3 px-lg-4 py-0 px-0 shadow-none'>
                        <div class='card-header bg-white border-0 text-start'>
                        
                                <h3>{{ trans('langMyWidgets') }}</h3>
                            
                        </div>
                        <div class='card-body'>
                            {!! $portfolio_page_sidebar_widgets !!}
                        </div>
                    </div>
                @endif                   
            </div>
        </div>
    </div>
</div>





<script>
    var user_cources = <?php echo json_encode($cources); ?>;
    var user_cource_pages = <?php echo $cource_pages; ?>;
</script>

<script type="text/javascript">
    var idCoursePortfolio = '';
    var btnPortfolio = '';
    var modal_portfolio = '';
    $(".ClickCoursePortfolio").click(function() {
        // Get the btn id
        idCoursePortfolio = this.id;

        // Get the modal
        modal_portfolio = document.getElementById("PortfolioModal"+idCoursePortfolio);

        // Get the button that opens the modal
        btnPortfolio = document.getElementById(idCoursePortfolio);

        // When the user clicks the button, open the modal 
        modal_portfolio.style.display = "block";

        $('[data-bs-toggle="tooltip"]').tooltip("hide");
    });

    $(".close").click(function() {
        modal_portfolio.style.display = "none";
    });

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal_portfolio) {
            modal_portfolio.style.display = "none";
        }
        $('[data-bs-toggle="tooltip"]').tooltip("hide");
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#cources-pics').css('display','none');
        $('.showCoursesBars').on('click',function(){
            $('#cources-bars').css('display','block');
            $('#cources-pics').css('display','none');
            $('.showCoursesBars').addClass('active');
            $('.showCoursesPics').removeClass('active');
        });
        $('.showCoursesPics').on('click',function(){
            $('#cources-bars').css('display','none');
            $('#cources-pics').css('display','block');
            $('.showCoursesBars').removeClass('active');
            $('.showCoursesPics').addClass('active');
        });
    });
</script>



<script>
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
</script>


@endsection
