@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (count($wc_servers) > 0)
        <div class='table-responsive'>
            <table class='table-default'>
                <thead>
                    <tr>
                        <th class = 'text-center'>{{ trans('langWebConfServer') }}</th>
                        <th class = 'text-center'>{{ trans('langBBBEnabled') }}</th>
                        <th class = 'text-center'>{!! icon('fa-gears') !!}</th>
                    </tr>
                </thead>
                @foreach ($wc_servers as $wc_server)
                    <tr>
                        <td>{{ $wc_server->hostname }}</td>
                        <td class='text-center'>
                            {{ $wc_server->enabled=='true' ? trans('langYes') : trans('langNo') }}
                        </td>
                        <td class='option-btn-cell'>
                            {!! action_button([
                                        [
                                            'title' => trans('langEditChange'),
                                            'url' => "$_SERVER[SCRIPT_NAME]?edit_server=$wc_server->id",
                                            'icon' => 'fa-edit'
                                        ],
                                        [
                                            'title' => trans('langDelete'),
                                            'url' => "$_SERVER[SCRIPT_NAME]?delete_server=$wc_server->id",
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
        <div class='alert alert-warning'>Δεν υπάρχουν διαθέσιμοι εξυπηρετητές.</div>
    @endif    
@endsection