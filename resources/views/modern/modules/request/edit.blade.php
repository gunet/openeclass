@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')
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
                    
                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>
                                    <div class='form-group'>
                                        <label for='requestTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' id='requestTitle' name='requestTitle' value='{{ $request->title }}' required>
                                        </div>
                                    </div>

                            

                                    <div class='form-group mt-4'>
                                        <label for='requestDescription' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! $descriptionEditor !!}
                                        </div>
                                    </div>

                                    @if ($request->type_id)
                                    <div class='mt-4'></div>
                                        @include('modules.request.extra_fields',
                                            ['type_name' => $type->name,
                                            'type_id' => $type->id,
                                            'fields_info' => $field_data])
                                    @endif

                                

                                    <div class='form-group mt-4'>
                                        <div class='col-12'>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input type='checkbox' name='send_mail' value='on' checked> <span class='checkmark'></span>{{ trans('langSendInfoMail') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                            

                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>

                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    
</div>
</div>
       
@endsection
