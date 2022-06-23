@extends('layouts.default')

@section('content')
    <style>
        .prev_month { color: red; }
    </style>

    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class="form-wrapper">
        <form class="form-horizontal" role="form" method="post">
        <div class="form-group">
            <div class="col-sm-10">
                <select name="selectedMonth" class="form-control">
                    {!! $months !!}
                </select>
            </div>
            <input class="btn btn-primary" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
        </div>
    </form>
    </div>

    @if (isset($_POST['selectedMonth']))
        @if ($coursNum)
            <div class='alert alert-info text-center'>{{ trans('langReport') }}: <strong>{{ $msg_of_month }} {{ $y }}</strong>
                <div><small>({{ trans('langInfoMonthlyStatistics') }})</small></div>
            </div>
            <table class="table-default">
                <tbody>
                    <tr>
                        <td>{{ trans('langNbProf') }}: {{ $profesNum }} (<span class='prev_month'>{{ $diff_profesNum }}</span>)</td>
                    </tr>
                    <tr>
                        <td>{{ trans('langNbStudents') }}: {{ $studNum }} (<span class='prev_month'>{{ $diff_studNum }}</span>)</td>
                    </tr>
                    <tr>
                        <td>{{ trans('langNbVisitors') }}: {{ $visitorsNum }} (<span class='prev_month'>{{ $diff_visitorsNum }}</span>)</td>
                    </tr>
                    <tr>
                        <td>{{ trans('langNbCourses') }}: {{ $coursNum }} (<span class='prev_month'>{{ $diff_coursNum }}</span>)</td>
                    </tr>
                    <tr>
                        <td>{{ trans('langNbLogin') }}: {{ $logins }} (<span class='prev_month'>{{ $diff_logins }}</span>)</td>
                    </tr>
                    <tr>
                        <td>{!! $details !!}</td>
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
