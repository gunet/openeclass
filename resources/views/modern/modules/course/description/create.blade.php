@extends('layouts.default')

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

                    {!! $action_bar !!}
                    
                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                        <div class='form-wrapper form-edit rounded'>
                            
                            <form class='form-horizontal' role='form' action='{{$urlServer}}modules/course_description/index.php?course={{ $course_code }}' method='post'>
                                <input type='hidden' name='course' value='{{ $course_code }}'>
                                @if ($editId)
                                    <input type='hidden' name='editId' value='{{ getIndirectReference($editId) }}'>
                                @endif            

                            

                                <div class='form-group'>
                                    <label for='editType' class='col-sm-6 control-label-notes'>{{ trans('langType') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($types, 'editType', $defaultType, 'class="form-control" id="typSel"') !!}
                                    </div>
                                </div>

                                


                                <div class='form-group{{ $titleError ? " form-error" : ""}} mt-4'>
                                    <label for='titleSel' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                    <div class='col-sm-12'>
                                        <input type='text' name='editTitle' class='form-control' value='{{ $cdtitle }}' size='40' id='titleSel'>
                                        {!! Session::getError('editTitle', "<span class='help-block Accent-200-cl'>:message</span>") !!}                                    
                                    </div>
                                </div>      
                                
                            


                                <div class='form-group mt-4'>
                                    <label for='editComments' class='col-sm-6 control-label-notes'>{{ trans('langContent') }}</label>
                                    <div class='col-sm-12'>
                                    {!! $text_area_comments !!}
                                    </div>
                                </div>

                                

                                <div class='form-group mt-5'>    
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                        {!! $form_buttons !!}
                                    </div>
                                </div>
                            {!! generate_csrf_token_form_field() !!}                              
                            </form>
                        </div>  
                    </div>
                    <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                            </div>
                        </div>
                </div>
            </div>

        </div>
    
</div>
</div>
        
        
@endsection

