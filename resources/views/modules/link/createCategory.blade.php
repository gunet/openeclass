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
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @include('layouts.partials.show_alert') 
                    
                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                        <div class='form-wrapper form-edit rounded'>
                            
                            <form class = 'form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}&urlview={{ $urlview }}'>
                                @if ($action == 'editcategory')
                                    <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
                                @endif
                                <fieldset>
                                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                    <div class="form-group{{ $categoryNameError ? ' has-error' : ''}}">
                                    <label for='CatName' class='col-sm-6 control-label-notes'>{{ trans('langCategoryName') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                    <div class='col-sm-12'>
                                        <input id="CatName" class='form-control' type='text' name='categoryname' size='53' placeholder='{{ trans('langCategoryName') }}' value='{{ isset($category) ? $category->name : "" }}'>
                                        {!! Session::getError('categoryname', "<span class='help-block Accent-200-cl'>:message</span>") !!}
                                    </div>
                                    </div>

                                  

                                    <div class='form-group mt-4'>
                                        <label for='CatDesc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                        <div class='col-sm-12'>
                                            <textarea id="CatDesc" class='form-control' rows='5' name='description'>{{ isset($category) ? $category->description : "" }}</textarea>
                                        </div>
                                    </div>

                                  
                                    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <input type='submit' class='btn submitAdminBtn' name='submitCategory' value="{{ $form_legend }}">
                                            <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </fieldset>
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