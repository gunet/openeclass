@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class="form-wrapper">
        <form class="form-horizontal" role="form" method="post">
        <div class="form-group">
            <div class="col-sm-10">
                <select name="selectedMonth" class="form-control">
                    @for ($i = 0; $i < 12; $i++)
                    <option value='{{ $option_date->modify( '-1 month' )->format('m Y') }}'>{{ trans("langMonths['".$option_date->format('m')."']").' '.$option_date->format('Y') }}</option>
                    @endfor
                </select>
            </div>
            <input class="btn btn-primary" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
        </div>
    </form>
    </div>
    @if (isset($_POST['selectedMonth']))
        @if (isset($monthly_data))
            <table class="table-default">
                <tbody>		
                    <tr>
                        <th colspan="2" class="text-center">{{ trans('langReport') }}: {{ $msg_of_month }} {{ $y }}</th>
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
                        <td colspan="2">{!! $monthly_data->details !!}</td>
                    </tr>
                    </tbody>
                    </table>
        @else
            <div class="alert alert-warning">
                {{ trans('langNoReport') }}: {{ $msg_of_month }} {{ $y }}
            </div>
        @endif
    @endif
@endsection