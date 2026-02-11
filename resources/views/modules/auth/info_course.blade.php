@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @if(isset($_SESSION['uid']))
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @endif

            <div class='col-12 my-4'>
                <h1>{{ $pageName }}</h1>
            </div>

            <div class="col-12">

                    <div class="card card-course-info px-lg-4 py-lg-4 p-3 mb-3">
                        <div class="row row-cols-1 row-cols-md-2 g-3">
                            <div class="col-md-4 col d-flex justify-content-center justify-content-md-start">
                                @if($c->course_image == NULL)
                                    @if($c->is_collaborative)
                                        <img class='img-fluid rounded-start course_info_img' src="{{ $urlAppend }}template/modern/images/default-collaboration.jpg" alt="{{ trans('langImageSelected') }}" />
                                    @else
                                        <img class='img-fluid rounded-start course_info_img' src="{{ $urlAppend }}resources/img/ph1.jpg" alt="{{ trans('langImageSelected') }}" />
                                    @endif
                                @else
                                    <img class='img-fluid rounded-start course_info_img' src="{{ $urlAppend }}courses/{{ $c->code }}/image/{{ $c->course_image }}" alt="{{ trans('langImageSelected') }}" />
                                @endif
                            </div>
                            <div class="col-md-8 col">
                                <div class="card-body py-0">

                                    <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                        <h2 class="mb-0">{{ $c->title }}</h2>
                                        {!! course_access_icon($c->visible) !!}
                                        @if($c->course_license > 0)
                                            {!! copyright_info($c->id) !!}
                                        @endif
                                    </div>

                                    <p class="card-text mt-2 mb-4">({{ $c->public_code }})&nbsp;- &nbsp;{{ $c->prof_names }}</p>

                                    @if(empty($c->description))
                                        @if(!$c->is_collaborative)
                                        <h3 class='form-label mb-1'>{{ trans('langCourseProgram')}}</h3>
                                        @else
                                        <h3 class='form-label mb-1'>{{ trans('langCollabDes')}}</h3>
                                        @endif
                                        <p>{{ trans('langThisCourseDescriptionIsEmpty') }}</p>
                                    @else
                                        @if(!$c->is_collaborative)
                                        <h3 class='form-label mb-1'>{{ trans('langCourseProgram')}}</h3>
                                        @else
                                        <h3 class='form-label mb-1'>{{ trans('langCollabDes')}}</h3>
                                        @endif
                                        <p>{!! $c->description !!}</p>
                                    @endif

                                    <h3 class='form-label mb-1 mt-4'>{{ trans('langCreationDate')}}</h3>
                                    <p>{{ format_locale_date(strtotime($c->created), null, false) }}</p>

                                    <div class='col-12 mt-4 d-flex justify-content-md-start justify-content-center'>
                                        <a class='btn submitAdminBtnDefault d-flex jystify-content-start align-items-center gap-2' href='{{ $urlServer }}courses/{{ $c->code }}/'>

                                            @if($c->is_collaborative)
                                                {{ trans('langPageCollaboration')}}
                                            @else
                                                {{ trans('langCoursePage')}}
                                            @endif
                                            <i class="fa-solid fa-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            @if (!$c->is_collaborative)
                <div class='col-12 mt-4'>
                    <div class='row'>
                        <div class='panel'>
                            <div class='panel-group group-section mt-2 px-0' id='accordionDesC'>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item px-0 mb-4 bg-transparent">

                                        <h3 class='d-flex justify-content-between border-bottom-default'>
                                            <a class='accordion-btn d-flex justify-content-start align-items-start gap-2 py-2' role='button' id='btn-syllabus' data-bs-toggle='collapse' href='#collapseDescriptionc' aria-expanded='true' aria-controls='collapseDescriptionc'>
                                                <i class='fa-solid fa-chevron-down settings-icon'></i>
                                                {{ trans('langSyllabus') }}
                                            </a>
                                        </h3>
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
            @endif

        </div>

    </div>
</div>

@endsection
