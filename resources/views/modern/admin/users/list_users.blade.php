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
                    
                    @include('layouts.partials.show_alert') 

                    <div class="overflow-auto">
                        <table id='search_results_table' class='table-default display'>
                            <thead class='list-header'>
                                <tr>
                                <th>{{ trans('langSurname') }}</th>
                                <th>{{ trans('langName') }}</th>
                                <th>{{ trans('langUsername') }}</th>
                                <th>{{ trans('langEmail') }}</th>
                                <th style='width:5%;'>{{ trans('langProperty') }}</th>
                                <th style='width:5%;' aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
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
                    </div>
                    <div class='col-12 mt-4'>
                        <!--Edit all function-->
                        <form action='multiedituser.php' method='post' class='d-flex'>
                        <!--redirect all request vars towards delete all action-->
                        @foreach ($_REQUEST as $key => $value)
                            <input type='hidden' name='{{ $key }}' value='{{ $value }}'>
                        @endforeach
                        @if (isset($_GET['department']) && $_GET['department'] && is_numeric($_GET['department'])) {
                            <input class='btn submitAdminBtn me-1' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
                        @endif
                        <input class='btn deleteAdminBtn me-1' type='submit' name='dellall_submit' value='{{ trans('langDelList') }}'>
                        <input class='btn submitAdminBtn' type='submit' name='activate_submit' value='{{ trans('langAddSixMonths') }}'>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>

        </div>
</div>
</div>
@endsection
