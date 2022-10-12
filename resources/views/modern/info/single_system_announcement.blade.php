@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    {!! $action_bar !!}

                    
                    <div class="col-12">
                        <div class="panel panel-admin rounded-0 border-0 shadow-lg">
                            <div class='panel-heading bg-light rounded-0 border-0'>
                                <div class='panel-title text-dark fw-bold'>{!! $title !!}</div>
                            </div>
                        
                            <div class="panel-body rounded-0">
                                <div class="single_announcement">
                                    <div class='announcement-main'>
                                        {!! $body !!}
                                    </div>
                                </div>
                            </div>
                            <div class='panel-footer d-flex justify-content-end rounded-0'>
                                <div class="text-end info-date">
                                     {!! $date !!} 
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


