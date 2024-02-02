@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @if(isset($_SESSION['uid']))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view')


                    {!! $action_bar !!}

                    
                    <div class="col-12">
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                                <h3>{!! $title !!}</h3>
                            </div>
                        
                            <div class="card-body">
                                <div class="single_announcement">
                                    <div class='announcement-main'>
                                        {!! $body !!}
                                    </div>
                                </div>
                            </div>
                            <div class='card-footer bg-default border-0 d-flex justify-content-start align-items-center'>
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


