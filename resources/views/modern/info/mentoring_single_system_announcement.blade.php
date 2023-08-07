@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('modules.mentoring.common.common_current_title')


                    {!! $action_bar !!}

                    
                    <div class="col-12">
                        <div class="panel panel-default">
                            <div class='panel-heading bg-white border-0'>
                                <div class='blackBlueText TextBold'>{!! $title !!}</div>
                            </div>
                        
                            <div class="panel-body">
                                <div class="single_announcement">
                                    <div class='announcement-main'>
                                        {!! $body !!}
                                    </div>
                                    <div class='announcement-main mt-4 text-start small-text textgreyColor TextSemiBold'>
                                        {!! $date !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
               
        </div>
    </div>
</div>
@endsection


