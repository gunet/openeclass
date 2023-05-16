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
                                
                                <div class='col-12'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form role='form' action='{{ $edit_url }}' method='post'>

                                                @if(isset($glossary_item))
                                                <input type='hidden' name='id' value='{{ getIndirectReference($glossary_item->id) }}'>                
                                                @endif

                                            
                                                <div class='form-group{{ Session::getError('term') ? " has-error" : "" }}'>
                                                    <label for='term' class='col-sm-6 control-label-notes'>{{ trans('langGlossaryTerm') }} </label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' class='form-control' placeholder="{{ trans('langGlossaryTerm') }}" id='term' name='term' placeholder='{{ trans('langGlossaryTerm') }}' value='{{ $term }}'>
                                                        <span class='help-block'>{{ Session::getError('term') }}</span>
                                                    </div>
                                                </div>

                                                <div class='form-group{{ Session::getError('definition') ? " has-error" : "" }} mt-4'>
                                                    <label for='term' class='col-sm-6 control-label-notes'>{{ trans('langGlossaryDefinition') }} </label>
                                                    <div class='col-sm-12'>
                                                        <textarea name="definition" placeholder="{{ trans('langGiveText') }}" rows="4" cols="60" class="form-control">{{ $definition }}</textarea>
                                                        <span class='help-block'>{{ Session::getError('definition') }}</span>    
                                                    </div>
                                                </div>

                                                <div class='form-group{{ Session::getError('url') ? " has-error" : "" }} mt-4'>
                                                    <label for='url' class='col-sm-6 control-label-notes'>{{ trans('langGlossaryUrl') }} </label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' placeholder="{{ trans('langGlossaryUrl') }}" class='form-control' id='url' name='url' value='{{ $url }}'>
                                                        <span class='help-block'>{{ Session::getError('url') }}</span>     
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='notes' class='col-sm-6 control-label-notes'>{{ trans('langCategoryNotes') }}</label>
                                                    <div class='col-sm-12'>
                                                        {!! $notes_rich !!}
                                                    </div>
                                                </div>

                                                <div class="mt-4">{!! isset($category_selection) ? $category_selection : "" !!}</div>

                                                                                    
                                                <div class='form-group mt-5'>    
                                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                                       
                                                            
                                                                {!! $form_buttons !!}
                                                           
                                                           
                                                                <a class='btn cancelAdminBtn ms-1' href="{{$base_url}}">
                                                                    {{trans('langCancel')}}
                                                                </a>
                                                         
                                                        
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

