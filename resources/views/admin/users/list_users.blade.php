@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
        <table id='search_results_table' class='display'>
        <thead>
            <tr>
              <th width='150'>{{ trans('langSurname') }}</th>
              <th width='100'>{{ trans('langName') }}</th>
              <th width='170'>{{ trans('langUsername') }}</th>
              <th>{{ trans('langEmail') }}</th>
              <th>{{ trans('langProperty') }}</th>
              <th width='130' class='centertext-center'>{!! icon('fa-gears') !!}</th>
            </tr>
        </thead>
        <!-- DO NOT DELETE THESE EMPTY COLUMNS -->
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
        <tbody>
        </tbody>
    </table>
    <div align='right' style='margin-top: 60px; margin-bottom:10px;'>
        <!--Edit all function-->
        <form action='multiedituser.php' method='post'> 
        <!--redirect all request vars towards delete all action-->
        @foreach ($_REQUEST as $key => $value)
            <input type='hidden' name='{{ $key }}' value='{{ $value }}'>
        @endforeach
        @if (isset($_GET['department']) && $_GET['department'] && is_numeric($_GET['department'])) {
            <input class='btn btn-primary' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
        @endif
        <input class='btn btn-primary' type='submit' name='dellall_submit' value='{{ trans('langDelList') }}'>
        <input class='btn btn-primary' type='submit' name='activate_submit' value='{{ trans('langAddSixMonths') }}'>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>            
@endsection