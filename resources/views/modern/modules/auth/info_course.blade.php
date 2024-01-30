@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @if(isset($_SESSION['uid']))
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @endif

            {!! $action_bar !!}

            <div class="col-12">
                @foreach($infoCourse as $c)
                    <div class="card border-0 px-0 mb-3">
                        <div class="row g-3">
                            <div class="col-lg-4 col-md-5 d-flex justify-content-center justify-content-md-start">
                                @if($c->course_image == NULL)
                                    <img class='img-fluid rounded-start course_info_img' src="{{ $urlAppend }}template/modern/img/ph1.jpg" alt="{{ $c->title }}" /></a>
                                @else
                                    <img class='img-fluid rounded-start course_info_img' src="{{ $urlAppend }}courses/{{ $c->code }}/image/{{ $c->course_image }}" alt="{{ $c->course_image }}" /></a>
                                @endif
                            </div>
                            <div class="col-lg-8 col-md-7 ps-md-3">
                                <div class="card-body px-0 pt-md-0">

                                    <h2 class="card-title d-flex justify-content-start align-items-center gap-2 flex-wrap">
                                        {!! $c->title !!}
                                        <span class='settings-icons Neutral-600-cl'>{!! course_access_icon($c->visible) !!}</span>
                                    </h2>

                                    <p class="card-text mt-2 mb-4">({!! $c->public_code !!})&nbsp;-&nbsp;{!! $c->prof_names !!}</p>


                                    @if(empty($c->description))
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item list-group-item-action'>{{ trans('langCourseProgram')}}</li>
                                            <li class='list-group-item element'>{{ trans('langThisCourseDescriptionIsEmpty') }}</li>
                                        </ul>
                                    @else
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item list-group-item-action'>{{ trans('langCourseProgram')}}</li>
                                            <li class='list-group-item element'>{!! $c->description !!}</li>
                                        </ul>
                                    @endif

                                    <ul class='list-group list-group-flush mt-3'>
                                        <li class='list-group-item list-group-item-action'>{{ trans('langCreationDate')}}</li>
                                        <li class='list-group-item element'>{!! format_locale_date(strtotime($c->created), null, false) !!}</li>
                                    </ul>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            <div class='col-12 mt-4'>
                <div class='row'>
                    <div class='panel'>
                        <div class='panel-group group-section mt-2 px-0' id='accordionDesC'>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 mb-4 bg-transparent">

                                    <div class='d-flex justify-content-between border-bottom-default'>
                                        <a class='accordion-btn d-flex justify-content-start align-items-start gap-2 py-2' role='button' id='btn-syllabus' data-bs-toggle='collapse' href='#collapseDescriptionc' aria-expanded='true' aria-controls='collapseDescriptionc'>
                                            <i class='fa-solid fa-chevron-down settings-icon'></i>
                                            {{ trans('langSyllabus') }}
                                        </a>
                                    </div>
                                    <div class='panel-collapse accordion-collapse collapse border-0 rounded-0 mt-3 show' id='collapseDescriptionc' data-bs-parent='#accordionDesC'>
                                        @if(count($course_descriptions) == 0)
                                            <div class='col-12 mb-4'>
                                                <p>{{ trans('langNoSyllabus')}}</p>
                                            </div>
                                        @else
                                            @foreach ($course_descriptions as $row)
                                                <div class='col-12 mb-4'>
                                                    <p class='form-label text-start'>{{ $row->title }}</p>
                                                    {!! standard_text_escape($row->comments) !!}
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection
