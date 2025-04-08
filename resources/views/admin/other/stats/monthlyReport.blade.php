
@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                <div class='col-12 mt-4'>
                    @if (isset($buildRoots))
                        {!! $buildRoots !!}
                    @endif
                </div>

                <div class='col-12'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i>
                        <span>
                            @if (isset($_GET['d']))
                                {{ trans('langMonth') }} <strong>{{ date_format(date_create($_GET['m']), "n / Y") }}</strong>
                            @else
                                {{ trans('langMonthlyReportInfo') }}
                           @endif
                        </span>
                    </div>
                </div>

                <div class='col-12'>
                    <div class='table-responsive'>
                        <table class='table-default table-logs'>
                            <thead>
                                <tr>
                                    @if (isset($_GET['d']))
                                        <th>{{ trans('langFaculty') }}</th>
                                    @else
                                        <th>{{ trans('langMonth') }}</th>
                                    @endif
                                    <th>{{ trans('langTeachers') }}</th>
                                    <th>{{ trans('langStudents') }}</th>
                                    <th>{{ trans('langCourses') }}</th>
                                    <th>{{ trans('langDoc') }}</th>
                                    <th>{{ trans('langExercises') }}</th>
                                    <th>{{ trans('langWorks') }}</th>
                                    <th>{{ trans('langAnnouncements') }}</th>
                                    <th>{{ trans('langMessages') }}</th>
                                    <th>{{ trans('langForums') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthly_data as $data)
                                    <tr>
                                        @if (isset($_GET['d']))
                                            <td style="background-color: aliceblue">
                                                {{ $data['faculty'] }}
                                            </td>
                                        @else
                                            <td style="background-color: aliceblue">
                                                {{ date_format(date_create($data['month']), "n / Y") }}
                                                <h3><a href="{{ $_SERVER['SCRIPT_NAME'] }}?d=1&amp;m={{ $data['month'] }}&fc={{ $fc }}">{{ trans('langDetails') }}</a></h3>
                                            </td>
                                        @endif
                                        <td style="text-align: center;">{{ $data['teachers'] }}</td>
                                        <td style="text-align: center;">{{ $data['students'] }}
                                            @if ($data['guests'] > 0)
                                                <br><span class='help-block'>({{ trans('langGuests') }}: {{ $data['guests'] }})</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">{{ $data['courses'] }}
                                            @if ($data['inactive_courses'] > 0)
                                                <br><span class='help-block'>({{ trans('langTypesInactive') }}: {{ $data['inactive_courses'] }})</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">{{ $data['documents'] }}</td>
                                        <td style="text-align: center;">{{ $data['exercises'] }}</td>
                                        <td style="text-align: center;">{{ $data['assignments'] }}</td>
                                        <td style="text-align: center;">{{ $data['announcements'] }}</td>
                                        <td style="text-align: center;">{{ $data['messages'] }}</td>
                                        <td style="text-align: center;">{{ $data['forum_posts'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
