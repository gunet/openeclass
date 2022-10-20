@extends('layouts.default')

@section('content')



<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class='mt-3'>@include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])</div>

            @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
            
            <div class='col-12'>
                <div class='alert alert-danger panel-phpinfo'>
                    {!! phpinfo() !!}
                </div>
            </div>
                
        </div>
    </div>
</div>
@endsection