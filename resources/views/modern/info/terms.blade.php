@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langUsageTerms')}}</div>
                        {!! $action_bar !!}
                    </div>
    
                    
                    <div class='col-12'>
                        <div class='panel shadow-sm p-3 mb-5 bg-body rounded'>
                            <div class='panel-body pane-body-terms'>
                                {!! $terms !!} 
                            </div>
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
