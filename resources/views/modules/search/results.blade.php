@extends('layouts.default')

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div class="col_maincontent_active @if(!isset($course_code)) search-content @endif">

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        {!! generate_csrf_token_form_field() !!}

                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='alert alert-info'>
                                    <i class='fa-solid fa-circle-info fa-lg'></i>
                                    <span>
                                        @if (isset($_POST['search_terms']))
                                            <label>{{ $_POST['search_terms'] }}</label>
                                        @endif
                                        <strong>{{ $count_courses }}</strong> {{ trans('langResults2') }}
                                    </span>
                                </div>
                            </div>

                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <thead>
                                        <tr class='list-header'>
                                            @if ($uid > 0)
                                                <th width='50'>{{ trans('langRegistration') }}</th>
                                            @endif
                                            <th class='text-start ps-1'>{{ trans('langCourse') }} ({{ trans('langCode') }})</th>
                                            <th class='text-start'>{{ trans('langTeacher') }}</th>
                                            <th class='text-start'>{{ trans('langKeywords') }}</th>
                                            <th class='text-start'>{{ trans('langType') }}</th>
                                        </tr>
                                        </thead>

                                        {!! $search_result_content !!}

                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript'>$(course_list_init);
        var themeimg = '{{ $themeimg }}'
        var urlAppend = '{{ $urlAppend }}'
        var lang = {
            unCourse: '{{ js_escape(trans('langUnCourse')) }}',
            cancel: '{{ js_escape(trans('langCancel')) }}',
            close: '{{ js_escape(trans('langClose')) }}',
            unregCourse: '{{ js_escape(trans('langUnregCourse')) }}',
            reregisterImpossible: '{{ js_escape(trans('langConfirmUnregCours')) }} {{ js_escape(trans('m[unsub]')) }}',
            invalidCode: '{{ js_escape(trans('langInvalidCode')) }}',
            prereqsNotComplete: '{{ js_escape(trans('langPrerequisitesNotComplete')) }}',
        };
        var courses = {!! $courses_list !!};
    </script>

@endsection
