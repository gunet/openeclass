@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @if(isset($_SESSION['uid']))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    <div class='col-12 my-4'>
                        <h1>{{ $pageName }}</h1>
                    </div>
                    
                    <div class="col-12">
                        <div class="card panelCard card-default px-lg-4 py-lg-3">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <div class='text-heading-h3'>{!! $title !!}</div>
                            </div>
                        
                            <div class="card-body">
                                <div class="single_announcement">
                                    <div class='announcement-main'>
                                        {!! $body !!}
                                    </div>
                                </div>
                            </div>
                            <div class='card-footer border-0 d-flex justify-content-start align-items-center'>
                                <div class="announcement-date small-text">
                                     {!! $date !!} 
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                
        </div>

</div>
</div>

@endsection


