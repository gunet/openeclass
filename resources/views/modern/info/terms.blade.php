@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    <div class='col-12'>
                       <div class='border-card bg-white Borders p-lg-5 p-3'>{!! $terms !!}</div> 
                    </div>
                    

               
        </div>
  
</div>
</div>

@endsection
