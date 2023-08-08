
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">
                    
                    <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto mb-4 ps-3 pe-3'><p class='TextMedium text-justify text-center'>{{ trans('langInfoHomeText')}}</p></div>

                    <div class="card-group mb-md-5 mb-2">
                        <div class="card me-md-4">
                            <img src="{{ $urlAppend }}template/modern/images/statistics.jpg" class="card-img-top cardImgHome insideHomeImage" alt="...">
                            <div class="card-body">
                                <h5 class="card-title TextSemiBold">{{ trans('langMentoringPrograms') }}</h5>
                                <p class="card-text TextRegular">{{ trans('langInfoPrograms') }}</p>
                                <p class="card-text"><a class="btn bgEclass small-text TextSemiBold showProgramsBtnHome mt-3" href='{{ $urlAppend }}modules/mentoring/programs/show_programs.php'>{{ trans('langGoToPrograms')}}</a></p>
                            </div>
                        </div>
                        <div class="card mt-md-0 mt-4 ms-md-4 solidPanel">
                            <img src="{{ $urlAppend }}template/modern/images/img1.jpg" class="card-img-top cardImgHome insideHomeImage" alt="...">
                            <div class="card-body">
                                <h5 class="card-title TextSemiBold">{{ trans('langTotalMentors') }}</h5>
                                <p class="card-text TextRegular">{{ trans('langInfoMentors') }}</p>
                                <p class="card-text"><a class="btn bgEclass TextSemiBold small-text showMentorsBtnHome mt-3" href='{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php'>{{ trans('langGoToMentors')}}</a></p>
                            </div>
                        </div>
                    </div>

                    @if($texts)
                        <div class='col-12'>
                            <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                <ul class="list-group list-group-flush mt-4">
                                    @foreach ($texts as $text)
                                        @if(!empty($text->title))
                                            <li class="list-group-item px-0 mb-4 bg-transparent">
                                                <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#faq-{{ $text->id }}' aria-expanded='false' aria-controls='#{{ $text->id }}'>
                                                    <span class='fa-solid fa-chevron-down'></span>
                                                    {!! $text->title !!}
                                                    
                                                </a>

                                                <div id='faq-{{ $text->id }}' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' aria-labelledby='heading{{ $text->id }}' data-bs-parent='#accordion'>
                                                    <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                        {!! $text->body !!}
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                            
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                       
                

        </div>
      
    </div>
</div>

<script>

    localStorage.setItem("MenuMentoring","home");

    $('.showProgramsBtnHome').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
    $('.showMentorsBtnHome').on('click',function(){
        localStorage.setItem("MenuMentoring","mentors");
    });
</script>

@endsection