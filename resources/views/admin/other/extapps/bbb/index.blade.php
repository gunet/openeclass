@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                @include('layouts.partials.show_alert') 

                @if (!$tc_cron_running)
                    @include('admin.other.extapps.bbb.bbb_cron_modal')
                @endif

                @if (count($q) > 0)
                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <thead>
                                <tr class='list-header'>
                                    <th class = 'text-center'>{{ trans('langName') }}</th>
                                    <th>{{ trans('langBBBEnabled') }}</th>
                                    <th>{{ trans('langUsers') }}</th>
                                    <th>{{ trans('langActiveRooms') }}</th>
                                    <th>{{ trans('langBBBMics') }} / {{ trans('langBBBCameras') }}</th>
                                    <th>{{ trans('langBBBServerOrderP') }} / {{ trans('langBBBServerLoad') }}</th>
                                    <th aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
                                </tr>
                                </thead>

                                {!! $bbb_cnt !!}    
                            </table>
                        </div>
                    </div>
                    <div class='col-12 mt-5'>
                        {!! $html_enabled_rooms !!}
                    </div>
                @else
                    <div class='col-12'>
                       <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoAvailableBBBServers') }}</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
