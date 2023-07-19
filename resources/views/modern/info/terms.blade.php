@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                <div class='col-12'>
                        <h1>{{ trans('langUsageTerms') }}</h1>
                </div>

                <div class='col-12 mt-4'>
                        <div class='border-card bg-white Borders p-lg-5 p-3'>{!! $terms !!}</div> 
                </div>
                    

               
        </div>
  
</div>
</div>

@endsection
