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
                    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <div class='alert alert-warning'>
                            {{ $user_request->status == 5 ? trans('langWarnReject') : trans('langGoingRejectRequest') }}
                        </div>
                        <div class='table-responsive'>
                        <table class='table-default'>
                            <tr>
                                <th class='ps-1'>{{ trans('langName') }}</th>
                                <td>{{ $user_request->givenname }}</td>
                            </tr>
                            <tr>
                                <th class='ps-1'>{{ trans('langSurname') }}</th>
                                <td>{{ $user_request->surname }}</td>
                            </tr>
                            <tr>
                                <th class='ps-1'>{{ trans('langEmail') }}</th>
                                <td>{{ $user_request->email }}</td>
                            </tr>
                            <tr>
                                <th class='left ps-1'>{{ trans('langComments') }}</th>
                                <td>
                                    <input type='hidden' name='id' value='{{ $id }}'>
                                    <input type='hidden' name='close' value='2'>
                                    <input type='hidden' name='prof_givenname' value='{{ $user_request->givenname }}'>
                                    <input type='hidden' name='prof_surname' value='{{ $user_request->surname }}'>
                                    <textarea class='auth_input' name='comment' rows='5' cols='60'>{{ $user_request->comment }}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <th class='left ps-1'>{{ trans('langRequestSendMessage') }}</th>
                                <td>
                                    &nbsp;<input type='text' class='auth_input' name='prof_email' value='{{ $user_request->email }}'>
                                    <input type='checkbox' name='sendmail' value='1' checked='yes'> 
                                    <small>({{ trans('langGroupValidate') }})</small>
                                </td>
                            </tr>
                            <tr>
                                <th class='left'>&nbsp;</th>
                                <td>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRejectRequest') }}'>
                                    &nbsp;&nbsp;
                                    <small>({{ trans('langRequestDisplayMessage') }})</small>
                                </td>
                            </tr>
                        </table>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection