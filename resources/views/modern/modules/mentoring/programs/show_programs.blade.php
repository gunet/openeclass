
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(isset($_SESSION['uid']))
                        <div class='col-12 ps-4 pe-4'>
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class="@if(!isset($_SESSION['uid'])) no_uid_menu @endif TextSemiBold" href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                                </ol>
                            </nav>
                        </div>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoProgramsText')!!}</p>
                        </div>
                    </div>
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
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

                    @if (isset($_SESSION['uid']))
                        <div class='row ms-0'>
                            <div class='col-md-8 col-12 ps-3'>
                                <div class='col-12 d-flex justify-content-md-start justify-content-center align-items-start mb-md-0 mb-3'>
                                    <a class='btn bgEclass btnMyPrograms small-text rounded-2 TextSemiBold text-uppercase d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}modules/mentoring/programs/myprograms.php'>
                                        <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'><span class='hidden-xs-mentoring hidden-md-mentoring TextBold'>&nbsp{{ trans('langMyPrograms') }}</span>
                                    </a>
                                    @if($is_admin)
                                        <a class='btn bgEclass btnDisablePrograms ms-2 small-text rounded-2 TextSemiBold text-uppercase d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}modules/mentoring/programs/disable_programs.php'>
                                            <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'><span class='hidden-xs-mentoring hidden-md-mentoring TextBold'>&nbsp{{ trans('langShowDisableMentoringProgramms') }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <!-- can create program only USER_TEACHER users-->
                            @php 
                                $is_only_tutor = Database::get()->querySingle("SELECT is_mentor FROM user WHERE id = ?d",$uid)->is_mentor;
                                $userStudent_is_mentor = Database::get()->querySingle("SELECT COUNT(id) as ui FROM user WHERE id = ?d AND status = ?d AND is_mentor = ?d",$uid,USER_STUDENT,1)->ui;
                            @endphp
                            @if(($is_editor_mentoring and get_config('mentoring_mentor_as_tutorProgram')) 
                                or ($is_editor_mentoring and !get_config('mentoring_mentor_as_tutorProgram') and $is_only_tutor == 0)
                                or $is_admin
                                or ($userStudent_is_mentor > 0 and get_config('mentoring_mentor_as_tutorProgram')))
                                <div class='col-md-4 col-12 pe-3'>
                                    <div class='col-12 d-flex justify-content-md-end justify-content-center align-items-start'>
                                        <a class='btn btn-primary btnCreateProgram' href='{{ $urlAppend }}modules/mentoring/programs/create_program.php'>
                                            <span class='fa fa-plus'></span><span class='hidden-md-mentoring TextBold'>&nbsp{{ trans('langAddMentoring') }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- all programs -->
                    @if(count($all_programs) > 0)
                        @php
                            $pagesPag = 0;
                            $allPrograms = 0;
                            $temp_pages = 0;
                        @endphp
                        <div class="card-group">
                            @php
                                $countCards = 1;
                                if($countCards == 1){
                                    $pagesPag++;
                                }
                            @endphp
                            @foreach($all_programs as $mentoring_program)
                               @php $temp_pages++; @endphp
                                <div class='col-xl-4 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3 cardProgram{{ $pagesPag }}'>
                                    <div class="card w-100 card{{ $pagesPag }}">
                                        @if(!empty($mentoring_program->program_image))
                                            <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/image/{{ $mentoring_program->program_image }}">
                                        @else
                                            <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                        @endif
                                        <div class="card-body">
                                            <p class="card-title TextBold fs-5 blackBlueText text-center">{{ $mentoring_program->title }}</p>
                                            <p class="card-text text-center">
                                                @php
                                                    $tutor = show_mentoring_program_tutor($mentoring_program->id);
                                                @endphp
                                                @foreach($tutor as $t)
                                                    &nbsp<span class='TextMedium blackBlueText'>{{ $t->givenname }}&nbsp{{ $t->surname }}</span>
                                                @endforeach
                                            </p>
                                        </div>
                                        <div class="card-footer text-center">
                                            <small class="text-muted">
                                                <a class='btn viewProgram bgEclass TextBold text-uppercase small-text rounded-2 d-flex justify-content-center aling-items-center' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/index.php">
                                                    <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                                </a>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    if($countCards == 8 and $temp_pages < count($all_programs)){
                                        $pagesPag++;
                                        $countCards = 0;
                                    }
                                    $countCards++;
                                    $allPrograms++;

                                @endphp
                            @endforeach

                            @if($pagesPag > 0)

                                <input type='hidden' id='KeyallPrograms' value='{{ $allPrograms }}'>
                                <input type='hidden' id='KeypagesProgram' value='{{ $pagesPag }}'>
                                <div class='col-12 ps-lg-3 pe-lg-3 d-flex justify-content-center align-items-center mt-3'>
                                    <div class='col-12 d-flex justify-content-center p-0 overflow-auto bg-white border-card'>
                                        <nav aria-label='Page navigation example w-100'>
                                            <ul class='pagination mentors-pagination w-100 mb-0'>
                                                <li class='page-item page-item-previous'>
                                                    <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-left'></span></a>
                                                </li>
                                                @if($pagesPag >=12 )
                                                    @for($i=1; $i<=$pagesPag; $i++)
                                                    
                                                        @if($i>=1 && $i<=5)
                                                            @if($i==1)
                                                                <li id='KeypageCenter{{ $i }}' class='page-item page-item-pages'>
                                                                    <a id='Keypage{{ $i }}' class='page-link' href='#'>{{ $i }}</a>
                                                                </li>

                                                                <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                                    <a>...</a>
                                                                </li>
                                                            @else
                                                                @if($i<$pagesPag)
                                                                    <li id='KeypageCenter{{ $i }}' class='page-item page-item-pages'>
                                                                        <a id='Keypage{{ $i }}' class='page-link' href='#'>{{ $i }}</a>
                                                                    </li>
                                                                @endif
                                                            @endif
                                                        @endif

                                                        @if($i>=6 && $i<=$pagesPag-1)
                                                            <li id='KeypageCenter{{ $i }}' class='page-item page-item-pages d-none'>
                                                                <a id='Keypage{{ $i }}' class='page-link' href='#'>{{ $i }}</a>
                                                            </li>

                                                            @if($i==$pagesPag-1)
                                                                <li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                                    <a>...</a>
                                                                </li>
                                                            @endif
                                                        @endif

                                                        @if($i==$pagesPag)
                                                            <li id='KeypageCenter{{ $i }}' class='page-item page-item-pages'>
                                                                <a id='Keypage{{ $i }}' class='page-link' href='#'>{{ $i }}</a>
                                                            </li>
                                                        @endif
                                                    @endfor
                                                
                                                @else
                                                    @for($i=1; $i<=$pagesPag; $i++)
                                                        <li id='KeypageCenter{{ $i }}' class='page-item page-item-pages'>
                                                            <a id='Keypage{{ $i }}' class='page-link' href='#'>{{ $i }}</a>
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
                            @endif
                        </div>
                    @else
                        <div class='col-12 mt-3 ps-4 pe-4'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{trans('langNoMentoringPrograms')}}</div></div>
                        </div>
                    @endif
                    
               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
      
        $('#table_all_available_programs').DataTable();

        $('.viewProgram').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });

        $('.btnMyPrograms, .btnDisablePrograms, .btnCreateProgram').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });




        //PAGINATION
        var arrayLeftRight = [];
            
        // init page1
        if(arrayLeftRight.length == 0){
            var totalPrograms = $('#KeyallPrograms').val();
            for(j=1; j<=totalPrograms; j++){
                if(j!=1){
                    $('.cardProgram'+j).removeClass('d-block');
                    $('.cardProgram'+j).addClass('d-none');
                }else{
                    $('.page-item-previous').addClass('disabled');
                    $('.cardProgram'+j).removeClass('d-none');
                    $('.cardProgram'+j).addClass('d-block');
                    $('#Keypage1').addClass('active');
                }
            }
            var totalPages = $('#KeypagesProgram').val();
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

                var totalPrograms = $('#KeyallPrograms').val();
                var totalPages = $('#KeypagesProgram').val();
                for(i=1; i<=totalPrograms; i++){
                    if(i == prevPage){
                        $('.cardProgram'+i).removeClass('d-none');
                        $('.cardProgram'+i).addClass('d-block');
                        $('#Keypage'+prevPage).addClass('active');
                    }else{
                        $('.cardProgram'+i).removeClass('d-block');
                        $('.cardProgram'+i).addClass('d-none');
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
            
                var totalPrograms = $('#KeyallPrograms').val();
                var totalPages = $('#KeypagesProgram').val();
                
                for(i=1; i<=totalPrograms; i++){
                    if(i == nextPage){
                        $('.cardProgram'+i).removeClass('d-none');
                        $('.cardProgram'+i).addClass('d-block');
                        // $('#Keypage'+nextPage).addClass('active');
                    }else{
                        $('.cardProgram'+i).removeClass('d-block');
                        $('.cardProgram'+i).addClass('d-none');
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

            var totalPrograms = $('#KeyallPrograms').val();
            var totalPages = $('#KeypagesProgram').val();
            for(i=1; i<=totalPrograms; i++){
                if(i!=number){
                    $('.cardProgram'+i).removeClass('d-block');
                    $('.cardProgram'+i).addClass('d-none');
                }else{
                    $('.cardProgram'+i).removeClass('d-none');
                    $('.cardProgram'+i).addClass('d-block');
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

    } );
</script>

@endsection