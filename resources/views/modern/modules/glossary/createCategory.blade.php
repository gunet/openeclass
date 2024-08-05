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

                                        <div class='d-lg-flex gap-4 mt-4'>
                                        <div class='flex-grow-1'>
                                            <div class='form-wrapper form-edit rounded'>

                                                <form class='form-horizontal' role='form' action='{{ $cat_url }}' method='post'>

                                                    @if(isset($glossary_cat))
                                                        <input type='hidden' name='category_id' value='{{ getIndirectReference($glossary_cat->id) }}'>
                                                    @endif

                                                    <div class='form-group{{ Session::getError('name') ? " has-error" : "" }}'>
                                                        <label for='term' class='col-sm-4 control-label-notes'>{{ trans('langCategoryName') }}: </label>
                                                        <div class='col-sm-12'>
                                                            <input type='text' class='form-control' id='term' name='name' placeholder='{{ trans('langCategoryName') }}' value='{{ $name }}'>
                                                            <span class='help-block Accent-200-cl'>{{ Session::getError('name') }}</span>    
                                                        </div>
                                                    </div>

                                                    

                                                    <div class='form-group mt-4'>
                                                        <label for='description' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                                        <div class='col-sm-12'>
                                                            {!! $description_rich !!}
                                                        </div>
                                                    </div>

                                                    

                                                    <div class='form-group mt-5'>    
                                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                                            {!! $form_buttons !!}
                                                            <a class='btn cancelAdminBtn ms-1' href="{{$cat_url}}">{{trans('langCancel')}}</a>
                                                        </div>
                                                    </div>
                                                    {!! generate_csrf_token_form_field() !!}   
                                                                
                                                </form>
                                            </div>
                                        </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                        </div>
                                            
                                        
                                
                            </div>
                       
                    </div>
                </div>
            
        </div>   
        </div>               
@endsection

