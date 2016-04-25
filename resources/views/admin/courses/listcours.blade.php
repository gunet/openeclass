@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <table id='course_results_table' class='display'>
        <thead>
            <tr>
            <th>{{ trans('langCourseCode') }}</th>
            <th>{{ trans('langGroupAccess') }}</th>
            <th>{{ trans('langFaculty') }}</th>
            <th>{!! icon('fa-cogs') !!}</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    @if (isset($_GET['formsearchfaculte']) and $_GET['formsearchfaculte'] and is_numeric(getDirectReference($_GET['formsearchfaculte'])))
        <div align='right' style='margin-top: 60px; margin-bottom:10px;'>
        <form action='multieditcourse.php' method='post'>
        <!--redirect all request vars towards action-->
        @foreach ($_REQUEST as $key => $value)
            <input type='hidden' name='{{ $key }}' value='{{ $value }}'>
        @endforeach

        <input class='btn btn-primary' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
        {!! generate_csrf_token_form_field() !!}
        </form>
        </div>
    @endif    
@endsection