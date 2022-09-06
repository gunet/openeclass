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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($user_request)
                        <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            @if($warning)
                                <div class='alert alert-warning'>
                                    {!! $warning !!}
                                </div>
                            @endif
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr>
                                        <th class='text-start'>{{trans('langName')}}</th>
                                        <td>{!!  q($user_request->givenname) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langSurname') }}</th>
                                        <td>{!! q($user_request->surname) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langEmail') }}</th>
                                        <td>{!! q($user_request->email) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langComments') }}</th>
                                        <td>
                                            <input type='hidden' name='id' value='{{$id}}'>
                                            <input type='hidden' name='close' value='2'>
                                            <input type='hidden' name='prof_givenname' value='{!! q($user_request->givenname) !!}'>
                                            <input type='hidden' name='prof_surname' value='{!! q($user_request->surname) !!}'>
                                            <textarea class='auth_input' name='comment' rows='5' cols='60'>{!! q($user_request->comment) !!}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langRequestSendMessage') }}</th>
                                        <td>
                                            &nbsp;<input type='text' class='auth_input' name='prof_email' value='{!! q($user_request->email) !!}'>
                                            <input type='checkbox' name='sendmail' value='1' checked='yes'> <small>{{ trans('langGroupValidate') }}</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>&nbsp;</th>
                                        <td>
                                            <input class='btn btn-primary' type='submit' name='submit' value="{{trans('langRejectRequest')}}">&nbsp;&nbsp;<small>{{ trans('langRequestDisplayMessage') }}</small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    @endif

                    @if($user_requests)
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
                                        <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ format_locale_date(strtotime($user_request->date_closed), 'short', false) }}</small>
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
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
