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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    

                    @if($user_request)
                        <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            @if($warning)
                                <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                    {!! $warning !!}</span>
                                </div>
                            @endif
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr>
                                        <th class='text-start'>{{trans('langName')}}:</th>
                                        <td>{!!  q($user_request->givenname) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langSurname') }}:</th>
                                        <td>{!! q($user_request->surname) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langEmail') }}:</th>
                                        <td>{!! q($user_request->email) !!}</td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>{{ trans('langComments') }}:</th>
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
                                            <input type='text' class='auth_input form-control' name='prof_email' value='{!! q($user_request->email) !!}'>
                                            <label class='label-container mt-3'>
                                                <input type='checkbox' name='sendmail' value='1' checked='yes'> 
                                                <span class='checkmark'></span>
                                                {{ trans('langGroupValidate') }}
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='text-start'>&nbsp;</th>
                                        <td>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value="{{trans('langRejectRequest')}}">&nbsp;&nbsp;<small>{{ trans('langRequestDisplayMessage') }}</small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    @endif
                
        </div>
   
</div>
</div>
@endsection
