@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    <div class="overflow-auto">
                        <table id='search_results_table' class='table-default display'>
                            <thead class='list-header'>
                                <tr>
                                <th width='150'>{{ trans('langSurname') }}</th>
                                <th width='100'>{{ trans('langName') }}</th>
                                <th width='170'>{{ trans('langUsername') }}</th>
                                <th>{{ trans('langEmail') }}</th>
                                <th>{{ trans('langProperty') }}</th>
                                <th width='130'>{!! icon('fa-gears') !!}</th>
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