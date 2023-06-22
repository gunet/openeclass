@extends('layouts.default')

@section('content')
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-xl-5 px-lg-0 py-lg-3 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}
                    
                    <div class='col-12'>
                        <div class='border-cols-default bg-white Borders p-lg-5 p-3'>{!! $policy !!}</div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
