@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
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

                    <div class="overflow-auto">
                        <table id='search_results_table' class='table-default display'>
                            <thead class='list-header'>
                                <tr>
                                <th class='text-white' width='150'>{{ trans('langSurname') }}</th>
                                <th class='text-white' width='100'>{{ trans('langName') }}</th>
                                <th class='text-white' width='170'>{{ trans('langUsername') }}</th>
                                <th class='text-white'>{{ trans('langEmail') }}</th>
                                <th class='text-white'>{{ trans('langProperty') }}</th>
                                <th width='130' class='text-white centertext-center'>{!! icon('fa-gears') !!}</th>
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
                </div>
            </div>
        </div>
    </div>
</div>         
@endsection