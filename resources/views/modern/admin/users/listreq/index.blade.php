@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    @if($message2) <div class='alert alert-info'>{!! $message2 !!}</div> @endif


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
                                        <td class='text-center'>
                                            <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                                        </td>
                                        <td class='option_btn_cell'>
                                        @if ($user_request->password == 'pop3')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaPop').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=2")
                                            )) !!}
                                        @elseif ($user_request->password == 'imap')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaImap').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=3")
                                            )) !!}
                                        @elseif ($user_request->password == 'ldap')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaLdap').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=4")
                                            )) !!}
                                        @elseif ($user_request->password == 'db')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaDB').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=5")
                                            )) !!}
                                        @elseif ($user_request->password == 'shibboleth')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaShibboleth').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=6")
                                            )) !!}
                                        @elseif ($user_request->password == 'cas')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaCAS').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=7")
                                            )) !!}
                                        @else
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration'),
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
                        <div class='col-sm-12'><div class='alert alert-warning'>{{ trans('langUserNoRequests') }}</div></div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
