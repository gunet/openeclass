@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='table-responsive'>
                        <table class='table-default'>
                            <tr>
                                <td>
                                    <a href='../usage/displaylog.php?from_other=TRUE'>{{ trans('langSystemActions') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=failurelogin'>{{ trans('langLoginFailures') }}</a>
                                    <small> ({{ trans('langLast15Days') }})</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=musers'>{{ trans('langMultipleUsers') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=memail'>{{ trans('langMultipleAddr') }} e-mail</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=mlogins'>{{ trans('langMultiplePairs') }} LOGIN - PASS</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=cusers'>{{ trans('langMultipleCourseUsers') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=vmusers'>{{ trans('langMailVerification') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=unregusers'>{{ trans('langUnregUsers') }}</a>
                                    <small> ({{ trans('langLastMonth') }})</small>
                                </td>
                            </tr>
                        </table>            
                    </div>


                    @if (isset($_GET['stats']))
                        @if (in_array($_GET['stats'], ['failurelogin', 'unregusers']))
                                {!! $extra_info !!}
                        @elseif ($_GET['stats'] == 'musers')
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class='list-header'>
                                        <th>
                                            <b class='text-white'>{{ trans('langMultipleUsers') }}</b>
                                        </th>
                                        <th class='text-end'>
                                            <strong class='text-white'>{{ trans('langResult') }}</strong>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble, 'username') !!}
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <b>
                                                    <span class='text-dark'>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif ($_GET['stats'] == 'memail')
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class='list-header'>
                                        <th><b class='text-white'>{{ trans('langMultipleAddr') }} e-mail</b></th>
                                        <th class='right'>
                                            <strong class='text-white'>{{ trans('langResult') }}</strong>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                       {!! tablize($loginDouble,'email') !!}  
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <b>
                                                    <span class='text-dark'>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif ($_GET['stats'] == 'mlogins')
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class='list-header'>
                                        <th>
                                            <b class='text-white'>{{ trans('langMultiplePairs') }} LOGIN - PASS</b>
                                        </th>
                                        <th class='text-end'>
                                            <b class='text-white'>{{ trans('langResult') }}</b>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble) !!}
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <b>
                                                    <span class='text-dark'>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class='text-end' colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif  ($_GET['stats'] == 'vmusers')
                            
                                <div class='col-sm-12 mt-5'>
                                    <div class='shadow-sm p-3 rounded'>
                                        <div class='control-label-notes pb-3'>
                                            {{ trans('langUsers') }}
                                        </div>
                                        <ul class='list-group'>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=1'>{{ trans('langMailVerificationYes') }}</a>
                                                </label>          
                                                <span class='badge btn btn-secondary btn-sm pe-none float-end'>{{ $verifiedEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a>
                                                </label>                            
                                                <span class='badge btn btn-secondary btn-sm pe-none float-end'>{{ $unverifiedEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a>
                                                </label>
                                                <span class='badge btn btn-secondary btn-sm pe-none float-end'>{{ $verificationRequiredEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes'>{{ trans('langTotal') }}</a>
                                                </label>
                                                <span class='badge btn btn-secondary btn-sm pe-none float-end'>{{ $totalUserCnt }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                        @elseif  ($_GET['stats'] == 'cusers')
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <tr class='list-header'>
                                    <th class='text-white'>{{ trans('langUsers') }}</th>
                                    <th class='text-center text-white'>{{ trans('langResult') }}</th>
                                </tr>
                                @foreach ($q as $data)
                                   @php $link = "{{$urlServer}}modules/admin/edituser.php?u={{$data->user_id}}"; @endphp 
                                   <tr>
                                        <td>
                                            {{$data->surname}} {{$data->givenname}} (<a href='$link'>{{$data->username}}</a>) 
                                        </td>
                                        <td class='text-center'>{{$data->num_of_courses}}</td>
                                   </tr>
                                @endforeach
                            </table>
                        </div>
                               
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


    
@endsection