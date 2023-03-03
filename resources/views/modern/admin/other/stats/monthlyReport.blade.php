
@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='alert alert-info'>
                        {{ trans('langMonthlyReportInfo') }}
                    </div>
                    <table class='table-default'>
                        <tbody>
                            <th class='list-header'>{{ trans('langMonth') }}</th>
                            <th class='list-header text-center'>{{ trans('langTeachers') }}</th>
                            <th class='list-header text-center'>{{ trans('langStudents') }}</th>
                            <th class='list-header text-center'>{{ trans('langGuests') }}</th>
                            <th class='list-header text-center'>{{ trans('langCourses') }}</th>

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
