
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">
                    
                    <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto mb-4 ps-3 pe-3'><p class='TextMedium text-justify text-center'>{{ trans('langInfoHomeText')}}</p></div>

                    <div class="card-group ps-4 pe-4 mb-md-5 mb-2">
                        <div class="card me-md-4">
                            <img src="{{ $urlAppend }}template/modern/images/statistics.jpg" class="card-img-top cardImgHome insideHomeImage" alt="...">
                            <div class="card-body">
                                <h5 class="card-title TextSemiBold">{{ trans('langMentoringPrograms') }}</h5>
                                <p class="card-text TextRegular">{{ trans('langInfoPrograms') }}</p>
                                <p class="card-text"><a class="btn bgEclass small-text TextSemiBold showProgramsBtnHome" href='{{ $urlAppend }}modules/mentoring/programs/show_programs.php'>{{ trans('langGoToPrograms')}}</a></p>
                            </div>
                        </div>
                        <div class="card mt-md-0 mt-4 ms-md-4 solidPanel">
                            <img src="{{ $urlAppend }}template/modern/images/img1.jpg" class="card-img-top cardImgHome insideHomeImage" alt="...">
                            <div class="card-body">
                                <h5 class="card-title TextSemiBold">{{ trans('langTotalMentors') }}</h5>
                                <p class="card-text TextRegular">{{ trans('langInfoMentors') }}</p>
                                <p class="card-text"><a class="btn bgEclass TextSemiBold small-text showMentorsBtnHome" href='{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php'>{{ trans('langGoToMentors')}}</a></p>
                            </div>
                        </div>
                    </div>

                    @if($texts)
                        @php $counter = 0; @endphp
                        <div class="accordion accordionHome ps-4 pe-4" id="accordionFaqs">
                            @foreach($texts as $text)
                                @if(!empty($text->title))
                                    <div class="accordion-item mb-3 solidPanel">
                                        <h2 class="accordion-header" id="heading{{ $counter }}">
                                            <button class="accordion-button bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $counter }}">
                                                <span class='TextBold fs-6 blackBlueText'>{!! $text->title !!}</span>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $counter }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $counter }}" data-bs-parent="#accordionFaqs">
                                            <div class="accordion-body">
                                                {!! $text->body !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @php $counter++; @endphp
                            @endforeach
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