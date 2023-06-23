@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class='mt-3'>@include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])</div>

            @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
            
            <div class='col-12'>
                <div class='alert alert-danger panel-phpinfo'>
                    {!! phpinfo() !!}
                </div>
            </div>
                
        </div>
</div>
@endsection