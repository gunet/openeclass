@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (count($bbb_servers) > 0)
        <div class='table-responsive'>
            <table class='table-default'>
                <thead>
                <tr>
                    <th class = 'text-center'>{{ trans('langHost') }}</th>
                    <th class = 'text-center'>IP</th>
                    <th class = 'text-center'>{{ trans('langBBBEnabled') }}</th>
                    <th class = 'text-center'>{{ trans('langOnlineUsers') }}</th>
                    <th class = 'text-center'>{{ trans('langMaxRooms') }}</th>
                    <th class = 'text-center'>{{ trans('langBBBServerOrderP') }}</th>
                    <th class = 'text-center'>{!! icon('fa-gears') !!}</th>
                </tr>
                </thead>
        @foreach ($bbb_servers as $bbb_server)
            <tr>
                <td>{{ $bbb_server->hostname }}</td>
                <td>{{ $bbb_server->ip }}</td>
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
                        'icon' => 'fa-times',
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
        <div class='alert alert-warning'>{{ trans('langNoAvailableBBBServers') }}</div>
    @endif   
@endsection