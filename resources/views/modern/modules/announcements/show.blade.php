
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
                            <div class="card panelCard px-lg-4 py-lg-3">
                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>{!! $title !!}</div>
                                </div>
                                <div class="card-body">
                                   
                                    {!! $content !!}
                                    @if ($tags_list)
                                        <p class='card-text'>{{ trans('langTags') }}: {!! $tags_list !!}</p>
                                    @endif
                                </div>
                                <div class='card-footer small-text bg-white border-0'>
                                    {!! $date !!}
                                </div>
                            </div>
                        </div>
                        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
