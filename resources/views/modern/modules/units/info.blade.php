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

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>


            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>


                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>



                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])



                    <?php $url = $urlServer.'courses/'.$course_code.'/index.php';?>
                    {!! action_bar(array(
                            array('title' => trans('langBack'),
                                'button-class' => 'btn-secondary',
                                'url' => $url,
                                'icon' => 'fa-reply',
                                'level' => 'primary-label')), false)
                    !!}

                    @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                    @endif

                    <div class="row p-2"></div>


                        <div class='col-md-12'>
                            <div class='form-wrapper'>
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

                                    <div class="row p-2"></div>

                                    <div class='form-group'>
                                        <label for='unitdescr' class='col-sm-6 control-label-notes'>{{ trans('langUnitDescr') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $descriptionEditor !!}
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>

                                    <div class='form-group'>
                                        <label for='unitduration' class='col-sm-6 control-label-notes'>{{ trans('langDuration') }}
                                            <span class='help-block'>{{ trans('langOptional') }}</span>
                                        </label>
                                        <div class="row">

                                            <div class="col-xl-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">{{ trans('langFrom2') }}</span>
                                                    <input type="text" class="form-control" id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}' aria-label="{{ $start_week }}" aria-describedby="basic-addon1">
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon2">{{ trans('langUntil') }}</span>
                                                    <input type="text" class="form-control" id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}' aria-label="{{ $finish_week }}" aria-describedby="basic-addon2">
                                                </div>
                                            </div>

                                            <!-- <label for='unitduration' class='control-label-notes'>{{ trans('langFrom2') }}</label>
                                            <div class='col-sm-5'>
                                                <input type='text' class='form-control' id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}'>
                                            </div>
                                            <label for='unitduration' class='control-label-notes'>{{ trans('langUntil') }}</label>
                                            <div class='col-sm-5'>
                                                <input type='text' class='form-control' id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}'>
                                            </div>    -->
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>

                                    {!! $tagInput !!}

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <div class='col-xs-offset-2 col-xs-10'>
                                            <button class='btn btn-primary' type='submit' name='edit_submit'>{{ trans('langSubmit') }}</button>
                                            <a class='btn btn-secondary' href='{{ $postUrl }}'>{{ trans('langCancel') }}</a>
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

