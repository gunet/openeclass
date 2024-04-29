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

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif

            @if ($user_requests)
            <div class='col-12'>
                <div class='table-responsive'>
                    <table id='requests_table' class='table-default'>
                        {!! table_header() !!}
                        <tbody>
                        @foreach ($user_requests as $user_request)
                            <tr>
                                <td>{{ $user_request->givenname }} {{ $user_request->surname }}</td>
                                <td>{{ $user_request->username }}</td>
                                <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                                <td>{{ ($user_request->status == USER_TEACHER)?  trans('langCourseCreate'):  trans('langRegistration')  }} </td>
                                <td>
                                    <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                                </td>
                                <td class='option_btn_cell text-end'>
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
                                                'url' => "newuseradmin.php?id=$user_request->id" . (($user_request->status == USER_TEACHER)?  "&type=prof" : ""))
                                        )) !!}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langUserNoRequests') }}</span></div></div>
            @endif

        </div>
    </div>
</div>
@endsection
