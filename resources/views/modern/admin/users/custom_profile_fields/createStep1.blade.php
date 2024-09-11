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

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            
                            <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <input type='hidden' name='catid' value='{{ getIndirectReference($catid) }}'>
                                <div class='form-group'>
                                    <label for='datatype' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldDatatype') }} <span class='Accent-200-cl'>(*)</span></label>
                                    <div class='col-sm-12'>
                                        {!! selection($field_types, 'datatype', 1, 'class="form-control" id="datatype"') !!}
                                    </div>
                                </div>
                             
                                <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='add_field_proceed_step2' value='{{ trans('langNext') }}'>
                                </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                
        </div>
</div>
</div>
@endsection