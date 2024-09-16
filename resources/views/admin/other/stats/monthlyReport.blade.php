
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

                    <div class='col-12'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                            {{ trans('langMonthlyReportInfo') }}</span>
                        </div>
                    </div>

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table class='table-default table-logs'>
                                <thead><tr>
                                    <th>{{ trans('langMonth') }}</th>
                                    <th>{{ trans('langTeachers') }}</th>
                                    <th>{{ trans('langStudents') }}</th>
                                    <th>{{ trans('langGuests') }}</th>
                                    <th>{{ trans('langCourses') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($monthly_data as $data)
                                        @php
                                            $formatted_data = date_format(date_create($data[0]), "n / Y")
                                        @endphp
                                        <tr>
                                            <td>{{ $formatted_data }}</td>
                                            <td>{{ $data[1] }}</td>
                                            <td>{{ $data[2] }}</td>
                                            <td>{{ $data[3] }}</td>
                                            <td>{{ $data[4] }}</td>
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
