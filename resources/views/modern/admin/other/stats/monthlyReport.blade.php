
@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-12'>
                        <div class='alert alert-info'>
                            {{ trans('langMonthlyReportInfo') }}
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
</div>
@endsection
