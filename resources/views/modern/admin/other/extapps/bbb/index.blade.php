@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    @if (count($bbb_servers) > 0)
                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <thead>
                                <tr class='list-header'>
                                    <th class = 'text-center'>API URL</th>
                                    <th class = 'text-center'>{{ trans('langBBBEnabled') }}</th>
                                    <th class = 'text-center'>{{ trans('langOnlineUsers') }}</th>
                                    <th class = 'text-center'>{{ trans('langMaxRooms') }}</th>
                                    <th class = 'text-center'>{{ trans('langBBBServerOrderP') }}</th>
                                    <th class = 'text-center'>{!! icon('fa-gears') !!}</th>
                                </tr>
                                </thead>
                        @foreach ($bbb_servers as $bbb_server)
                            <tr>
                                <td>{{ $bbb_server->api_url }}</td>
                                <td class='text-center'>{{ $bbb_server->enabled == 'true' ? trans('langYes') : trans('langNo') }}</td>
                                <td class='text-center'>{{ get_connected_users($bbb_server->server_key, $bbb_server->api_url, $bbb_server->ip) }}</td>
                                <td class='text-center'>{{ $bbb_server->max_rooms }}</td>
                                <td class='text-center'>{{ $bbb_server->weight }}</td>
                                <td class='option-btn-cell'>
                                {!! action_button([
                                    [
                                        'title' => trans('langEditChange'),
                                        'url' => "$_SERVER[SCRIPT_NAME]?edit_server=" . getIndirectReference($bbb_server->id),
                                        'icon' => 'fa-edit'
                                    ],
                                    [
                                        'title' => trans('langDelete'),
                                        'url' => "$_SERVER[SCRIPT_NAME]?delete_server=" . getIndirectReference($bbb_server->id),
                                        'icon' => 'fa-xmark',
                                        'class' => 'delete',
                                        'confirm' => trans('langConfirmDelete')
                                    ]
                                ]) !!}
                                </td>
                            </tr>
                        @endforeach            	
                        </table></div>
                    </div>
                    @else
                        <div class='col-12'>
                           <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoAvailableBBBServers') }}</span></div>
                        </div>
                    @endif   
                </div>
            </div>
        </div>
</div>
@endsection