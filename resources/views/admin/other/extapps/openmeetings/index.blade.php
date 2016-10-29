@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (count($om_servers) > 0)
        <div class='table-responsive'>
            <table class='table-default'>
                <thead>
                    <tr>
                        <th class='text-center'>{{ trans('langOpenMeetingsServer') }}</th>
                        <th class='text-center'>{{ trans('langOpenMeetingsPort') }}</th>
                        <th class='text-center'>{{ trans('langOpenMeetingsAdminUser') }}</th>
                        <th class='text-center'>{{ trans('langOpenMeetingsWebApp') }}</th>
                        <th class='text-center'>{{ trans('langBBBEnabled') }}</th>
                        <th class='text-center'>{!! icon('fa-gears') !!}</th>
                    </tr>
                </thead>
        @foreach ($om_servers as $om_server)
            <tr>
                <td>{{ $om_server->hostname }}</td>
                <td>{{ $om_server->port }}</td>
                <td>{{ $om_server->username }}</td>                
                <td>{{ $om_server->webapp }}</td>               
                <td class='text-center'>{{ $om_server->enabled == 'true' ? trans('langYes') : trans('langNo') }}</td>
                <td class='option-btn-cell'>
                {!! action_button([
                    [
                        'title' => trans('langEditChange'),
                        'url' => "$_SERVER[SCRIPT_NAME]?edit_server=" . getIndirectReference($om_server->id),
                        'icon' => 'fa-edit'
                    ],
                    [
                        'title' => trans('langDelete'),
                        'url' => "$_SERVER[SCRIPT_NAME]?delete_server=" . getIndirectReference($om_server->id),
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