@push('head_scripts')
    <script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });
        });
</script>
@endpush

@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @unless(isset($_GET['c']))
                            <div class='d-lg-flex gap-4_content mt-4'>
                                <div class='flex-grow-1'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='get'>
                                            <fieldset><legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                                <div class='row form-group mt-4'>
                                                    <label for='dialog-set-value' class='col-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                                    <div class='col-12'>
                                                        {!! $html !!}
                                                    </div>
                                                </div>

                                                <div class='row input-append date form-group mt-4' data-date = '{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                                                    <label class='col-12 control-label-notes' for='user_date_start'>{{ trans('langFrom') }}</label>
                                                    <div class='col-12'>
                                                        <div class='input-group'>
                                                            <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                            <input class='form-control mt-0 border-start-0' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='row input-append date form-group mt-4' data-date= '{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                                                    <label class='col-12 control-label-notes' for='user_date_end'>{{ trans('langTill') }}</label>
                                                    <div class='col-12'>
                                                        <div class='input-group'>
                                                            <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                            <input class='form-control mt-0 border-start-0' id='user_date_end' name='user_date_end' type='text' value= '{{ $user_date_end }}'>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class='row form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                                        <input class='btn submitAdminBtn' type='submit' name='stats_submit' value='{{ trans('langSubmit') }}'>
                                                        <a href='index.php?t=a' class='btn cancelAdminBtn ms-2'>{{ trans('langCancel') }}</a>
                                                    </div>
                                                </div>
                                                </fieldset>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endunless

                        @if (isset($_GET['stats_submit']))
                            @if (isset($_GET['c']))
                                <div class='col-12'>
                                    <div class='col-sm-12'>
                                        <div class='panel panel-default'>
                                            <div class='panel-body'>
                                                <div class='inner-heading'>
                                                    <em>{!! $name !!}</em>
                                                </div>
                                                <div class='row col-12'>
                                                    <div class="d-flex justify-content-start align-items-center gap-2 mt-2 flex-wrap">
                                                        <strong>{{ $course->title }}</strong> <small>({{ $course->code }})</small><br>
                                                        &mdash;<i>{{ $course->prof_names }}</i>
                                                        {!! $visibility_icon !!}
                                                    </div>
                                                    <div class="d-flex justify-content-start align-items-center gap-1 mt-2 flex-wrap">
                                                        <strong>{{ trans('langUsers') }}</strong>: {{ $users }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- user registrations per month --}}
                                        <div class='table-responsive mt-4'>
                                            <table class='table-default'>
                                                <thead>
                                                    <tr class='list-header'>
                                                        <th class='col-1 text-center'>{{ trans('langMonth') }}</th>
                                                        <th class='text-center'>{{ trans('langTeachers') }}</th>
                                                        <th class='text-center'>{{ trans('langStudents') }}</th>
                                                        <th class='text-center'>{{ trans('langGuests') }}</th>
                                                        <th class='text-center'>{{ trans('langDoc') }}</th>
                                                        <th class='text-center'>{{ trans('langExercises') }}</th>
                                                        <th class='text-center'>{{ trans('langWorks') }}</th>
                                                        <th class='text-center'>{{ trans('langAnnouncements') }}</th>
                                                        <th class='text-center'>{{ trans('langMessages') }}</th>
                                                        <th class='text-center'>{{ trans('langForums') }}</th>
                                                    </tr>
                                                </thead>
                                                @foreach ($month_stats as $month_stats_data)
                                                    <tr>
                                                        <td class='text-center'>{{ $month_stats_data['start'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['prof'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['students'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['guests'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['documents'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['announcements'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['messages'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['exercises'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['assignments'] }}</td>
                                                        <td class='text-center'>{{ $month_stats_data['forum_posts'] }}</td>
                                                    <tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{--  courses list --}}
                                <div class='table-responsive'>

                                    <div class='alert alert-info'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i><span>{{ $s }} {{ trans('langCourses') }}({{ trans('langFrom2') }} {{ $all }} {{ trans('langSumFrom') }})</span>
                                    </div>

                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <thead>
                                                <tr class='list-header'><th class='col-3'>{{ trans('langCourse') }} - {{ trans('langCode') }}</th>
                                                    <th class='col-4'>{{ trans('langTeacher') }}</th>
                                                    <th class='col-3'>{{ trans('langCreationDate') }}</th>
                                                    <th class='col-1 text-end'>{{ trans('langActions') }}</th>
                                                </tr>
                                            </thead>

                                            @foreach ($sql as $data)
                                                <tr>
                                                    <td><a href='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $data->id }}&amp;user_date_start={{ $user_date_start }}&user_date_end={{ $user_date_end }}&stats_submit=true'>{{ $data->title }}</a><br/><small>({{ $data->code }})</small></td>
                                                    <td>{{ $data->prof_names }}</td>
                                                    <td>{!! format_locale_date(strtotime($data->creation_time), 'short') !!}</td>
                                                    <td class='text-end'>
                                                        {!! action_button(array(
                                                                array('title' => trans('langDumpUser'),
                                                                      'url' => "dump_faculty_stats.php?c=$data->id&amp;user_date_start=$u_date_start&amp;user_date_end=$u_date_end",
                                                                      'icon' => 'fa-file-excel')
                                                              ))
                                                        !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
