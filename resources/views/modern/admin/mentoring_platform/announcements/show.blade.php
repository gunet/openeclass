@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @include('modules.mentoring.common.common_current_title')

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($announcementsID)
                       
                        <div class='col-sm-12'>
                            <div class="panel panel-default">
                                <div class="panel-heading bg-white">                   
                                    <div class='blackBlueText TextBold'>{{ $announcementsID->title }}</div>
                                </div>
                                <div class="panel-body">
                                    <span class="text-secondary">
                                        {!! $announcementsID->body !!}
                                    </span>
                                </div>
                                <div class='panel-footer'>
                                    <div class='blackBlueText TextSemiBold'>{!! format_locale_date(strtotime($announcementsID->date)) !!}</div>
                                </div>
                            </div>
                        </div>
                       
                    @endif
                
        </div>
    </div>
</div>
@endsection