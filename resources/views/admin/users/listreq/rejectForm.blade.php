@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='table-responsive'>
        <table id = 'requests_table' class='table-default'>
            {!! table_header(1, trans('langDateReject_small')) !!}   
            <tbody>
            @foreach ($user_requests as $user_request)
                <tr>
                    <td>{{ $user_request->givenname }} {{ $user_request->surname }}</td>
                    <td>{{ $user_request->username }}</td>
                    <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                    <td>
                        <small>{{ nice_format(date('Y-m-d', strtotime($user_request->date_open))) }}</small>
                    </td>
                    <td>
                        <small>{{ nice_format(date('Y-m-d', strtotime($user_request->date_closed))) }}</small>
                    </td>
                    <td class='option-btn-cell'>";
                        {!! action_button(array(
                                        array('title' => $langRestore,
                                              'url' => "$_SERVER[SCRIPT_NAME]?id=$user_request->id&amp;show=closed$reqtype",
                                              'icon' => 'fa-retweet'))) !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection