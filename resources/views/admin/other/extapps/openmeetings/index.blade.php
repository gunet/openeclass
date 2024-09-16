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

                    @if (count($om_servers) > 0)
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langOpenMeetingsServer') }}</th>
                                        <th>{{ trans('langOpenMeetingsPort') }}</th>
                                        <th>{{ trans('langOpenMeetingsAdminUser') }}</th>
                                        <th>{{ trans('langOpenMeetingsWebApp') }}</th>
                                        <th>{{ trans('langBBBEnabled') }}</th>
                                        <th aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
                                    </tr>
                                </thead>
                        @foreach ($om_servers as $om_server)
                            <tr>
                                <td>{{ $om_server->hostname }}</td>
                                <td>{{ $om_server->port }}</td>
                                <td>{{ $om_server->username }}</td>                
                                <td>{{ $om_server->webapp }}</td>               
                                <td>{{ $om_server->enabled == 'true' ? trans('langYes') : trans('langNo') }}</td>
                                <td class='option-btn-cell text-end'>
                                {!! action_button([
                                    [
                                        'title' => trans('langEditChange'),
                                        'url' => "$_SERVER[SCRIPT_NAME]?edit_server=" . getIndirectReference($om_server->id),
                                        'icon' => 'fa-edit'
                                    ],
                                    [
                                        'title' => trans('langDelete'),
                                        'url' => "$_SERVER[SCRIPT_NAME]?delete_server=" . getIndirectReference($om_server->id),
                                        'icon' => 'fa-xmark',
                                        'class' => 'delete',
                                        'confirm' => trans('langConfirmDelete')
                                    ]
                                ]) !!}
                                </td>
                            </tr>
                        @endforeach            	
                        </table>
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