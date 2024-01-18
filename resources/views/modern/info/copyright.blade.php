@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12'>
                            <h1>Copyright</h1>
                    </div>
                    <div class="col-12 mt-4">
                        
                    
                        <div class='panel-copyright border-card Borders bg-default p-lg-5 p-3'>{!! trans('langCopyrightNotice') !!}</div>
                    </div>
               
        </div>
</div>
</div>

@endsection
