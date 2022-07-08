
@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
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

                        
                        
                        {!! $action_bar !!}

                       
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                            <div class='panel shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                                <div class='panel-body'>
                                    <div class='single_announcement'>
                                        <label class="control-label-notes">{{trans('langHomePageIntroTitle')}}</label>
                                        <div class='announcement-title'>
                                            {!! $title !!}
                                        </div>

                                        <div class="row p-2"></div>


                                        <label class="control-label-notes">{{trans('langDate')}}</label>
                                        <div class='announcement-date'>
                                            {!! $date !!}
                                        </div>

                                        <div class="row p-2"></div>


                                        <label class="control-label-notes">{{trans('langContent')}}</label>
                                        <div class='announcement-main'>
                                            {!! $content !!}
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    @if ($tags_list)
                                        <hr>
                                        <div>{{ trans('langTags') }}: {!! $tags_list !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
