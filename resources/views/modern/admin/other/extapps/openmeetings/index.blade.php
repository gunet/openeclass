@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    

                    @if (count($om_servers) > 0)
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <thead>
                                    <tr class='list-header'>
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
                        <div class='col-12'>
                            <div class='alert alert-warning'>{{ trans('langNoAvailableBBBServers') }}</div>
                        </div>
                    @endif  
                </div> 
            </div>
        </div>
</div>
@endsection