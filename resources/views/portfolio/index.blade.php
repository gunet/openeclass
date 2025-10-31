@push('head_scripts')
    <script type='text/javascript'>
        var msg = {
            langSearch: '{{ js_escape(trans('langSearch')) }}',
            langDisplay: '{{ js_escape(trans('langDisplay')) }}',
            langResults2: '{{ js_escape(trans('langResults2')) }}',
            langNoResult: '{{ js_escape(trans('langNoResult')) }}',
            langDisplayed: '{{ js_escape(trans('langDisplayed')) }}',
            langTill: '{{ js_escape(trans('langTill')) }}',
            langFrom2: '{{ js_escape(trans('langFrom2')) }}',
            langTotalResults: '{{ js_escape(trans('langTotalResults')) }}',
            dataTablesDomParam: @if(get_config('show_always_collaboration')) '<"all_courses float-end px-0">frtip' @else '' @endif };

        $(function() {

            $('#consentModal').modal('show');

            var pages = parseInt("{{ $pages }}");

            initialize_lesson_display(pages);

            $('.all_courses').html("<div class='d-flex justify-content-end align-items-center flex-wrap gap-2'>" +
                "<a href='{{ $urlAppend }}main/portfolio.php?countPages=-1' class='btn submitAdminBtn submitAdminBtnAllCourses'>{{ js_escape(trans('langListAll')) }}</a>" +
                "<a class='btn showCoursesBars active' role='button' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='bottom' title='{{ js_escape(trans('langShowCoursesInTable')) }}' aria-label='{{ js_escape(trans('langShowCoursesInTable')) }}'>" +
                "<i class='fa-solid fa-table-list'></i>" +
                "</a>" +
                "<a class='btn showCoursesPics' role='button' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='bottom' title='{{ js_escape(trans('langShowCoursesInPics')) }}' aria-label='{{ js_escape(trans('langShowCoursesInPics')) }}'><i class='fa-solid fa-table-cells-large'></i></a>" +
                "</div>");

            jQuery('.panel_title').click(function()
            {
                var mypanel = $(this).next();
                mypanel.slideToggle(100);
                if($(this).hasClass('active')) {
                    $(this).removeClass('active');
                } else {
                    $(this).addClass('active');
                }
            });

        var idCoursePortfolio = '';
        var btnPortfolio = '';
        var modal_portfolio = '';
        $("#portfolio_lessons, #cources-pics, #portfolio_collaborations").on('click','.ClickCoursePortfolio',function() {
            // Get the btn id
            idCourse = this.id;
            if(idCourse.includes('CourseTable_')){
                idCoursePortfolio = idCourse.replace('CourseTable_', '');
            }else if(idCourse.includes('CoursePic_')){
                idCoursePortfolio = idCourse.replace('CoursePic_', '');
            }

            // Get the modal
            modal_portfolio = document.getElementById("PortfolioModal"+idCoursePortfolio);

            // Get the button that opens the modal
            btnPortfolio = document.getElementById(idCoursePortfolio);

            // When the user clicks the button, open the modal
            modal_portfolio.style.display = "block";

            $('[data-bs-toggle="tooltip"]').tooltip("hide");


            var $div = $('<div />').appendTo('body');
            $div.attr('class', 'modal-backdrop fade show');
        });

        $(".close").click(function() {
            modal_portfolio.style.display = "none";
            $(".modal-backdrop").remove();
        });

        // When the user clicks anywhere outside the modal, close it
        window.onclick = function(event) {
            if (event.target === modal_portfolio) {
                modal_portfolio.style.display = "none";
                $(".modal-backdrop").remove();
            }
            $('[data-bs-toggle="tooltip"]').tooltip("hide");
        }

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
    });
    </script>

@endpush


@extends('layouts.default')

@section('content')

@if ($_SESSION['status'] == USER_STUDENT and get_config('activate_privacy_policy_consent') and !isset($_SESSION['accept_policy_later']) and !user_has_accepted_policy($uid))
    @include('portfolio.privacy_policy_modal')
@endif

<div class="col-12 main-section">
    <div class='row m-auto'>
        <div class='col-12 portfolio-profile-container'>
            <div class='{{ $container }} padding-default'>
            <h1 aria-label="{{ trans('langPortfolio') }}"></h1>
                <div class='row row-cols-xl-3 row-cols-md-2 row-cols-1'>
                    <div class='col-xl-4 col-md-6 d-flex col-12 justify-content-md-start justify-content-center align-items-center'>
                        <div class='d-flex justify-content-md-start justify-content-center align-items-center flex-wrap gap-3'>
                            <img class="user-detals-photo ms-auto me-auto" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ trans('langUser') }}: {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                            <div>
                                <div class='mb-0 portofolio-text-intro portfolio-username TextBold normal-text'> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </div>
                                <p class='small-text Neutral-900-cl mb-0 portofolio-text-intro'>
                                    {!! $_SESSION['uname'] !!}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class='col-xl-3 col-md-6 col-12 d-flex justify-content-xl-center justify-content-md-end justify-content-center align-items-center mt-md-0 mt-4'>
                        <div>
                            @if(!get_config('show_always_collaboration'))
                            <div class='d-flex justify-content-start align-items-center gap-2 portfolio-texts mb-0'>
                                <div>{!! trans('langSumCoursesEnrolled') !!}: {{ $num_of_courses }} </div>
                            </div>
                            @endif
                            @if(get_config('show_collaboration'))
                            <div class='d-flex justify-content-start align-items-center gap-2 portfolio-texts mb-0'>
                                <div>{!! trans('langSumCollaborationEnrolled') !!}: {{ $num_of_collaborations }} </div>
                            </div>
                            @endif
                            <p class='small-text Neutral-900-cl mb-0 portofolio-text-intro'>
                                {{ trans('langProfileLastVisit') }}&nbsp;:&nbsp;<span class='TextBold small-text'>{{ format_locale_date(strtotime($last_login)) }}</span>
                            </p>
                        </div>
                    </div>

                    <div class='col-xl-5 col-md-12 col-12 d-flex justify-content-xl-end justify-content-md-start justify-content-center align-items-center gap-2 flex-wrap mt-xl-0 mt-4'>
                        <a class='btn myProfileBtn' type='button' href='{{ $urlAppend }}main/profile/display_profile.php'>
                            {{ trans('langMyProfile') }}
                        </a>
                        <a class='btn myProfileBtn' type='button' href='{{ $urlAppend }}modules/usage/index.php?t=u'>
                            {{ trans('langMyStats') }}
                        </a>
                        @if ((isset($is_admin) and $is_admin) or
                            (isset($is_power_user) and $is_power_user) or
                            (isset($is_usermanage_user) and ($is_usermanage_user)) or
                            (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                <a class="btn myProfileBtn" type="button" href="{{ $urlAppend }}modules/admin/index.php">
                                    {{ trans('langAdminTool') }}
                                </a>
                        @elseif ($_SESSION['status'] == USER_STUDENT)
                            <a class="btn myProfileBtn" type="button" href="{{ $urlAppend }}modules/auth/formuser.php">
                                {{ trans('langMyRequests') }}
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class='col-12 main-section-courses'>
        <div class='row m-auto'>
            <div class='col-12 portfolio-courses-container'>
                <div class='{{ $container }} padding-default'>
                    <div class='row row-cols-1 g-4'>
                        <div class='col portfolio-content'>
                            <div class='d-xl-flex gap-5'>
                                <div class='flex-grow-1'>
                                    <div class='card card-transparent border-0 bg-transparent'>
                                        <div class='card-header d-md-flex justify-content-md-between align-items-md-center px-0 bg-transparent border-0'>
                                            <h2>{{ trans('langMyCoursesSide') }}&nbsp;
                                                @if (!get_config('show_always_collaboration'))
                                                    ({{ $num_of_courses }})
                                                @else
                                                    ({{ $num_of_collaborations }})
                                                @endif

                                            </h2>
                                            <div class='d-flex mt-md-0 mt-3'>
                                                {{--
                                                <a class="btn submitAdminBtn @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user) me-2 @endif" href="{{ $urlAppend }}modules/auth/courses.php">
                                                    <i class="fa-regular fa-pen-to-square"></i>&nbsp
                                                    {{ trans('langRegister') }}
                                                </a>
                                                --}}
                                                @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                                    <a id="btn_create_course" class="btn submitAdminBtnDefault" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                        <i class="fa-solid fa-plus"></i>&nbsp;{{ trans('langCreate') }}
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
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('langClose') }}"></button>
                                                    </div>
                                                </div>
                                            @endif


                                            <div id="cources-bars">
                                                {!! $perso_tool_content['lessons_content'] !!}
                                            </div>


                                            <div id="cources-pics">

                                                <div class="d-flex justify-content-end flex-wrap gap-2 mb-4">
                                                    <a class="btn showCoursesBars" role='button' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='bottom' title="{{ js_escape(trans('langShowCoursesInTable')) }}" aria-label="{{ js_escape(trans('langShowCoursesInTable')) }}">
                                                        <i class="fa-solid fa-table-list"></i>
                                                    </a>
                                                    <a class="btn showCoursesPics" role='button' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='bottom' title="{{ js_escape(trans('langShowCoursesInPics')) }}" aria-label="{{ js_escape(trans('langShowCoursesInPics')) }}">
                                                        <i class="fa-solid fa-table-cells-large"></i>
                                                    </a>
                                                </div>

                                                <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-4">
                                                    @php
                                                        $pagesPag = 0;
                                                        $allCourses = 0;
                                                        $temp_pages = 0;
                                                        $countCards = 1;
                                                        if($countCards == 1){
                                                            $pagesPag++;
                                                        }
                                                    @endphp

                                                    @foreach($courses as $course)

                                                            @php
                                                                $temp_pages++;
                                                                if (isset($course->favorite)) {
                                                                    $favorite_icon = 'fa-star Primary-500-cl';
                                                                    $fav_status = 0;
                                                                    $fav_message = '';
                                                                } else {
                                                                    $favorite_icon = 'fa-regular fa-star';
                                                                    $fav_status = 1;
                                                                    $fav_message = trans('langFavorite');
                                                                }
                                                            @endphp

                                                            <div class="col cardCourse{{ $pagesPag }}">
                                                                <div class="card h-100 card{{ $pagesPag }} Borders border-card card-default">
                                                                    @php
                                                                        $courseImage = '';
                                                                        if(!empty($course->course_image)){
                                                                            $courseImage = "{$urlServer}courses/$course->code/image/$course->course_image";
                                                                        }else{
                                                                            if($course->is_collaborative){
                                                                                $courseImage = "{$urlServer}template/modern/images/default-collaboration.jpg";
                                                                            }else{
                                                                                $courseImage = "{$urlServer}resources/img/ph1.jpg";
                                                                            }
                                                                        }
                                                                    @endphp

                                                                    @if($course->course_image == NULL)
                                                                        @if($course->is_collaborative)
                                                                            <img class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" src="{{ $urlAppend }}template/modern/images/default-collaboration.jpg" alt="{{ trans('langCourseImage') }} : {{ $course->title }}" />
                                                                        @else
                                                                            <img class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" src="{{ $urlAppend }}resources/img/ph1.jpg" alt="{{ trans('langCourseImage') }} : {{ $course->title }}" />
                                                                        @endif
                                                                    @else
                                                                        <img class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" src="{{$urlAppend}}courses/{{$course->code}}/image/{{$course->course_image}}" alt="{{ trans('langCourseImage') }} : {{ $course->title }}" />
                                                                    @endif

                                                                    <div class='card-body'>

                                                                        <div class="lesson-title line-height-default">
                                                                            <a class='TextBold' href="{{$urlServer}}courses/{{$course->code}}/">
                                                                                {{ $course->title }}&nbsp;({{ $course->public_code }})
                                                                            </a>
                                                                        </div>

                                                                        <div class="vsmall-text Neutral-900-cl TextRegular mt-1">{{ $course->professor }}</div>

                                                                    </div>

                                                                    <div class='card-footer d-flex justyfy-content-start align-items-center gap-3 flex-wrap border-0'>
                                                                        <a id='btnNotificationCards_{{ $course->course_id }}' class='d-none btn btn-notification-course-card text-decoration-none'
                                                                            data-bs-toggle='modal' href='#notificationCard{{ $course->course_id }}' aria-label="{{ trans('langNotificationsExist') }}">
                                                                            <i class='fa-solid fa-bell link-color' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title="{{ trans('langNotificationsExist') }}"></i>
                                                                        </a>

                                                                        <a class='ClickCoursePortfolio' href='javascript:void(0);' id='CoursePic_{{ $course->code }}' type="button" data-bs-toggle='tooltip' data-bs-placement='top'
                                                                            title="{{ trans('langPreview')}}&nbsp;{{ trans('langOfCourse') }}" aria-label="{{ trans('langPreview')}}&nbsp;{{ trans('langOfCourse') }}">
                                                                            <i class='fa-solid fa-display'></i>
                                                                        </a>

                                                                        {!! icon($favorite_icon, $fav_message, "course_favorite.php?course=" . $course->code . "&amp;fav=$fav_status") !!}

                                                                        @if ($course->status == USER_STUDENT)
                                                                            @if (get_config('disable_student_unregister_cours') == 0)
                                                                                {!! icon('fa-minus-circle', trans('langUnregCourse'), "{$urlServer}main/unregcours.php?cid=$course->course_id&amp;uid=$uid") !!}

                                                                            @endif
                                                                        @elseif ($course->status == USER_TEACHER)
                                                                            {!! icon('fa-wrench', trans('langAdm'), "{$urlServer}modules/course_info/index.php?from_home=true&amp;course=" . $course->code, '', true, true) !!}
                                                                        @endif


                                                                        <div class="modal fade" id="notificationCard{{ $course->course_id }}" tabindex="-1" aria-labelledby="notificationCardLabel{{ $course->course_id }}" aria-hidden="true">
                                                                            <div class="modal-dialog">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <div class='modal-title'>
                                                                                            <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                                                                                            <div class='modal-title-default text-center mb-0' id="notificationCardLabel{{ $course->course_id }}">{{ trans('langNotesNotifications') }}</div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <p class='text-center mb-3'>
                                                                                            <a class='TextBold' href="{{ $urlServer }}courses/{{ $course->code }}/index.php">
                                                                                                {{ $course->title }}
                                                                                            </a>
                                                                                        </p>
                                                                                        <div class='lesson-notifications' data-id='{{ $course->course_id }}'></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                            </div>

                                                            @php
                                                                if($countCards == 6 and $temp_pages < count($courses)){
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

                                                <div class='col-12 d-flex justify-content-center Borders p-0 bg-transparent solidPanel mt-4'>
                                                    <nav aria-label="{{ trans('langPagination') }}">
                                                        <ul class='pagination mycourses-pagination w-100 mb-0'>
                                                            <li class='page-item page-item-previous'>
                                                                <a class='page-link' aria-label="{{ trans('langPreviousPage') }}"><span class='fa-solid fa-chevron-left'></span></a>
                                                            </li>
                                                            @if($pagesPag >=12 )
                                                                @for($i=1; $i<=$pagesPag; $i++)

                                                                    @if($i>=1 && $i<=5)
                                                                        @if($i==1)
                                                                            <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                                                <a id='Keypage{{$i}}' class='page-link' aria-label="{{ trans('langWikiNumberOfPages')}}">{{$i}}</a>
                                                                            </li>

                                                                            <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                                                <a aria-label="{{ trans('langWikiNumberOfPages')}}">...</a>
                                                                            </li>
                                                                        @else
                                                                            @if($i<$pagesPag)
                                                                                <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                                                    <a id='Keypage{{$i}}' class='page-link' aria-label="{{ trans('langWikiNumberOfPages')}}">{{$i}}</a>
                                                                                </li>
                                                                            @endif
                                                                        @endif
                                                                    @endif

                                                                    @if($i>=6 && $i<=$pagesPag-1)
                                                                        <li id='KeypageCenter{{$i}}' class='page-item page-item-pages d-none'>
                                                                            <a id='Keypage{{$i}}' class='page-link' aria-label="{{ trans('langWikiNumberOfPages')}} {{$i}}">{{$i}}</a>
                                                                        </li>

                                                                        @if($i==$pagesPag-1)
                                                                            <li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                                                <a aria-label="{{ trans('langWikiNumberOfPages')}}">...</a>
                                                                            </li>
                                                                        @endif
                                                                    @endif

                                                                    @if($i==$pagesPag)
                                                                        <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                                            <a id='Keypage{{$i}}' class='page-link' aria-label="{{ trans('langWikiNumberOfPages')}} {{$i}}">{{$i}}</a>
                                                                        </li>
                                                                    @endif
                                                                @endfor

                                                            @else
                                                                @for($i=1; $i<=$pagesPag; $i++)
                                                                    <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                                        <a id='Keypage{{$i}}' class='page-link' aria-label="{{ trans('langWikiNumberOfPages')}} {{$i}}">{{$i}}</a>
                                                                    </li>
                                                                @endfor
                                                            @endif

                                                            <li class='page-item page-item-next'>
                                                                <a class='page-link' aria-label="{{ trans('langNextPage') }}"><span class='fa-solid fa-chevron-right'></span></a>
                                                            </li>
                                                        </ul>
                                                    </nav>
                                                </div>

                                            </div>


                                            @if($portfolio_page_main_widgets)
                                                <div class='col-12 mt-4'>
                                                    {!! html_entity_decode($portfolio_page_main_widgets) !!}
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <div>

                                    @include('portfolio.portfolio-calendar')

                                    {{--
                                    <div class='card bg-transparent card-transparent border-0 mt-5 sticky-column-course-home'>
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

                                    <div class='card bg-transparent card-transparent border-0 mt-5 sticky-column-course-home'>
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
                                    --}}

                                    @if($portfolio_page_sidebar_widgets)
                                        <div class='card bg-transparent card-transparent border-0 mt-5 sticky-column-course-home'>
                                            <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                                <h3>{{ trans('langMyWidgets') }}</h3>
                                            </div>
                                            <div class='card-body px-0'>
                                                {!! html_entity_decode($portfolio_page_sidebar_widgets) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
