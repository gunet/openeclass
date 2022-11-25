@extends('layouts.default')


@push('head_styles')
    <link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
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
                    language: '".$language."',
                    autoclose: true    
                });
            });
    </script>            
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>


            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])




                    <?php $url = $urlServer.'courses/'.$course_code.'/index.php';?>
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
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
                                            'level' => 'primary-label')), false) 
                                !!}

                        <div class='col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                
                                <form class='form-horizontal' action='{{ $postUrl }}' method='post' onsubmit="return checkrequired(this, 'unittitle')">
                                    @if ($unitId)
                                        <input type='hidden' name='unit_id' value='{{ $unitId }}'>
                                    @endif

                                    <div class='form-group'>
                                        <label for='unitTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' id='unitTitle' name='unittitle' value='{{ $unitTitle }}'>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='unitdescr' class='col-sm-6 control-label-notes'>{{ trans('langUnitDescr') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $descriptionEditor !!}
                                        </div>
                                    </div>
                                    
                                    <div class='form-group mt-4'>
                                        <label for='unitduration' class='col-sm-6 control-label-notes mb-1'>{{ trans('langDuration') }}
                                            <span class='help-block'>{{ trans('langOptional') }}</span>
                                        </label>
                                        <div class="row">

                                            <div class="col-lg-6 col-12">
                                                <div class="input-group mb-4">
                                                    <span class="input-group-text h-30px border-0 BordersLeftInput bgEclass" id="basic-addon1">{{ trans('langFrom2') }}</span>
                                                    <input type="text" class="form-control mt-0" id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}' aria-label="{{ $start_week }}" aria-describedby="basic-addon1">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-12 mt-lg-0 mt-4">
                                                <div class="input-group mb-4">
                                                    <span class="input-group-text h-30px border-0 BordersLeftInput bgEclass" id="basic-addon2">{{ trans('langUntil') }}</span>
                                                    <input type="text" class="form-control mt-0" id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}' aria-label="{{ $finish_week }}" aria-describedby="basic-addon2">
                                                </div>
                                            </div>
                                        </div>                     
                                    </div>

                                    <div class="mt-4"></div>
                                    
                                    {!! $tagInput !!}

                                    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                           
                                                
                                                 <button class='btn submitAdminBtn' type='submit' name='edit_submit'>{{ trans('langSubmit') }}</button>
                                           
                                            
                                                <a class='btn btn-outline-secondary cancelAdminBtn ms-1' href='{{ $postUrl }}'>{{ trans('langCancel') }}</a>
                                              
                                           
                                            
                                            
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

