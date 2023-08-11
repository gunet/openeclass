@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

                    <div class='col-12'>
                            <h1>Copyright</h1>
                    </div>
                    <div class="col-12 mt-4">
                        
                    
                        <div class='border-card Borders bg-white p-lg-5 p-3'>{!! trans('langCopyrightNotice') !!}</div>
                    </div>
               
        </div>
</div>
</div>

@endsection
