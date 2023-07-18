@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

            <div class='mt-3'>@include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])</div>

            @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
            
            <div class='col-12'>
                <div class='alert alert-danger panel-phpinfo'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                    {!! phpinfo() !!}</span>
                </div>
            </div>
                
        </div>
</div>
</div>
@endsection