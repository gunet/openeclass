@extends('layouts.default')


@push('head_styles')
    <link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
    <link href="{{ $urlAppend }}template/modern/css/new_calendar.css?v=4.0-dev" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/js/bootstrap-datepicker.min.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/locales/bootstrap-datepicker.{{ $language }}.min.js'></script>
    
    <script type='text/javascript'>
            $(function() {
                $('#unitdurationfrom, #unitdurationto').datepicker({
                    format: 'dd-mm-yyyy',
                    pickerPosition: 'bottom-right',
                    language: '{{ $language }}',
                    autoclose: true    
                });
            });
    </script>            
@endpush

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>


            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')




                    <?php $url = $urlServer.'courses/'.$course_code.'/index.php';?>
                    
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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



                    {!! action_bar(array(
                                        array('title' => trans('langBack'),
                                            'button-class' => 'btn-secondary',
                                            'url' => $url,
                                            'icon' => 'fa-reply',
                                            'level' => 'primary')), false) 
                                !!}

                        <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                
                                <form class='form-horizontal' action='{{ $postUrl }}' method='post' onsubmit="return checkrequired(this, 'unittitle')">
                                    @if ($unitId)
                                        <input type='hidden' name='unit_id' value='{{ $unitId }}'>
                                    @endif

                                    <div class='row form-group'>
                                        <label for='unitTitle' class='col-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                        <div class='col-12'>
                                            <input type='text' class='form-control' id='unitTitle' name='unittitle' value='{{ $unitTitle }}'>
                                        </div>
                                    </div>

                                    <div class='row form-group mt-4'>
                                        <label for='unitdescr' class='col-12 control-label-notes'>{{ trans('langUnitDescr') }}</label>
                                        <div class='col-12'>
                                            {!! $descriptionEditor !!}
                                        </div>
                                    </div>
                                    
                                    <div class='row form-group mt-4'>
                                        <label for='unitduration' class='col-12 control-label-notes mb-1'>{{ trans('langDuration') }}
                                            <span class='help-block'>{{ trans('langOptional') }}:</span>
                                        </label>
                                        <div class='col-12'>
                                            <div class="row">

                                                <div class="col-lg-6 col-12">
                                                    <div class="input-group mb-4">
                                                        <span class="input-group-text h-40px input-border-color bg-input-default border-end-0" id="basic-addon1"><i class='fa-regular fa-calendar'></i></span>
                                                        <input type="text" class="form-control mt-0 border-start-0" id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}' aria-label="{{ $start_week }}" aria-describedby="basic-addon1">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-12 mt-lg-0 mt-4">
                                                    <div class="input-group mb-4">
                                                        <span class="input-group-text h-40px input-border-color bg-input-default border-end-0" id="basic-addon2"><i class='fa-regular fa-calendar'></i></span>
                                                        <input type="text" class="form-control mt-0 border-start-0" id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}' aria-label="{{ $finish_week }}" aria-describedby="basic-addon2">
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>                 
                                    </div>

                                    <div class="mt-4"></div>
                                    
                                    {!! $tagInput !!}

                                    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                           <button class='btn submitAdminBtn' type='submit' name='edit_submit'>{{ trans('langSubmit') }}</button>
                                           <a class='btn cancelAdminBtn ms-1' href='{{ $postUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                        </div>
                    
                </div>
            </div>
        </div>
    
</div>
</div>
@endsection

