@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
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
                    


                    {!! $action_bar !!}

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


                    <div class='col-sm-12'>
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
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='send_mail' value='on' checked> {{ trans('langSendInfoMail') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                        

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        
                                           
                                                 <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                           
                                           
                                                 <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                           
                                       
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
