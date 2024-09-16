@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='mt-3'>@include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])</div>

            @include('layouts.partials.legend_view')
            
            <div class='col-12'>
                <div class='alert alert-danger panel-phpinfo'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                    {!! phpinfo() !!}</span>
                </div>
            </div>
                
        </div>
</div>
</div>
@endsection