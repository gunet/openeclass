
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(!get_config('mentoring_always_active') and !get_config('mentoring_platform'))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    <div class='col-12'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                            {{ trans('langMonthlyReportInfo') }}</span>
                        </div>
                    </div>

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <tbody>
                                    <th class='list-header text-white'>{{ trans('langMonth') }}</th>
                                    <th class='list-header text-center text-white'>{{ trans('langTeachers') }}</th>
                                    <th class='list-header text-center text-white'>{{ trans('langStudents') }}</th>
                                    <th class='list-header text-center text-white'>{{ trans('langGuests') }}</th>
                                    <th class='list-header text-center text-white'>{{ trans('langCourses') }}</th>

                                    @foreach ($monthly_data as $data)
                                        @php
                                            $formatted_data = date_format(date_create($data[0]), "n / Y")
                                        @endphp
                                        <tr>
                                            <td>{{ $formatted_data }}</td>
                                            <td class='text-center'>{{ $data[1] }}</td>
                                            <td class='text-center'>{{ $data[2] }}</td>
                                            <td class='text-center'>{{ $data[3] }}</td>
                                            <td class='text-center'>{{ $data[4] }}</td>
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
