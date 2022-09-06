@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='table-responsive'>
        <table id = 'requests_table' class='table-default'>
            {!! table_header(1) !!}
            <tbody>
            @foreach ($user_requests as $user_request)
                <tr>
                    <td>{{ $user_request->givenname }} {{ $user_request->surname }} </td>
                    <td>{{ $user_request->username }}</td>
                    <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                    <td class='text-center' data-sort='{{ date("Y-m-d H:i", strtotime($req->date_open)) }}'>
                        <small>{{ format_locale_date(strtotime($req->date_open), 'short', false) }}</small>
                    </td>
                    <td <td class='text-center' data-sort='{{ date("Y-m-d H:i", strtotime($req->date_closed)) }}'>
                        <small>{{ format_locale_date(strtotime($req->date_closed), 'short', false) }}</small>
                    </td>
                    <td class='option-btn-cell'>
                        {!! action_button(array(
                            array('title' => trans('langRestore'),
                                    'url' => "$_SERVER[SCRIPT_NAME]?id=$user_request->id&amp;show=closed$reqtype",
                                    'icon' => 'fa-retweet'))) !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
