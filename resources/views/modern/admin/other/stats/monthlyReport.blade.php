
@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class="form-wrapper shadow-sm p-3 rounded">
                            
                            <form class="form-horizontal" role="form" method="post">
                                <div class="form-group mt-3">
                                    <div class="col-sm-12">
                                       {{-- <select name="selectedMonth" class="form-control">
                                            @for ($i = 0; $i < 12; $i++)
                                            <option value="{{ $option_date->modify( '-1 month' )->format('m Y') }}">{{ trans("langMonths['".$option_date->format('m')."']").' '.$option_date->format('Y') }}</option>
                                            @endfor
                                        </select> --}}
                                        <select name="selectedMonth" class="form-select">{!! $months !!}</select>
                                    </div>
                                    <input class="btn btn-sm btn-primary mt-2 submitAdminBtn w-100" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                    @if (isset($_POST['selectedMonth']))
                        @if (isset($monthly_data))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='table-responsive mt-3'>
                                    <table class="table-default">
                                        <tbody>		
                                            <tr class='list-header'>
                                                <th colspan="2" class="text-center text-white">{{ trans('langReport') }}: {{ $msg_of_month }} {{ $y }}</th>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbProf') }}: </th>
                                                <td>{{ $monthly_data->profesNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbStudents') }}: </th>
                                                <td>{{ $monthly_data->studNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbVisitors') }}: </th>
                                                <td>{{ $monthly_data->visitorsNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbCourses') }}:  </th>
                                                <td>{{ $monthly_data->coursNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbLogin') }}: </th>
                                                <td>{{ $monthly_data->logins }}</td>
                                            </tr>
                                            <tr>
                                                <td>{!! $monthly_data->details !!}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class="alert alert-warning">
                                    {{ trans('langNoReport') }}: {{ $msg_of_month }} {{ $y }}
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