@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($user_requests)
        <div class='table-responsive'>
            <table id='requests_table' class='table-default'>
                {!! table_header() !!}
                <tbody>
                    @foreach ($user_requests as $user_request)
                        <tr>
                            <td>{{ $user_request->givenname }} {{ $user_request->surname }}</td>
                            <td>{{ $user_request->username }}</td>
                            <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                            <td data-sort='{{ date("Y-m-d H:i", strtotime($user_request->date_open)) }}'>
                                <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                            </td>
                            <td class='option_btn_cell'>
                            @if ($user_request->password == 'pop3')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaPop').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=2")
                                )) !!}
                            @elseif ($user_request->password == 'imap')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaImap').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=3")
                                )) !!}
                            @elseif ($user_request->password == 'ldap')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaLdap').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=4")
                                )) !!}
                            @elseif ($user_request->password == 'db')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaDB').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=5")
                                )) !!}
                            @elseif ($user_request->password == 'shibboleth')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaShibboleth').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=6")
                                )) !!}
                            @elseif ($user_request->password == 'cas')
                                {!! action_button(array(
                                    array('title' => trans('langEditChange').' ('. trans('langViaCAS').')',
                                          'icon' => 'fa-edit',
                                          'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=7")
                                )) !!}
                            @else
                                 {!! action_button(array(
                                    array('title' => trans('langEditChange'),
                                          'icon' => 'fa-edit',
                                          'url' => "newuseradmin.php?id=$user_request->id")
                                )) !!}
                            @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langUserNoRequests') }}</div>
    @endif
@endsection
