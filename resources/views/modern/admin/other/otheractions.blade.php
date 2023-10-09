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

                    @if (isset($_GET['stats']))
                        @if (in_array($_GET['stats'], ['failurelogin', 'unregusers']))
                                {!! $extra_info !!}
                        @elseif ($_GET['stats'] == 'musers')
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead><tr class='list-header'>
                                        <th>
                                            {{ trans('langMultipleUsers') }}
                                        </th>
                                        <th>
                                            {{ trans('langResult') }}
                                        </th>
                                    </tr></thead>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble, 'username') !!}
                                        <tr>
                                            <td colspan='2'>
                                                <b>
                                                    <span class='text-dark'>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan='2'>
                                                <div class='not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        @elseif ($_GET['stats'] == 'memail')
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead><tr class='list-header'>
                                        <th>{{ trans('langMultipleAddr') }} e-mail</th>
                                        <th class='right'>
                                            {{ trans('langResult') }}
                                        </th>
                                    </tr></thead>
                                    @if (count($loginDouble) > 0)
                                       {!! tablize($loginDouble,'email') !!}
                                        <tr>
                                            <td colspan='2'>
                                                <b>
                                                    <span class='text-dark'>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan='2'>
                                                <div class='not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        @elseif ($_GET['stats'] == 'mlogins')
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead><tr class='list-header'>
                                        <th>
                                            {{ trans('langMultiplePairs') }} LOGIN - PASS
                                        </th>
                                        <th class='text-end'>
                                            {{ trans('langResult') }}
                                        </th>
                                    </tr></thead>
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
                                                <div class='not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        @elseif  ($_GET['stats'] == 'vmusers')
                                <div class='col-12'>
                                    <div class='shadow-sm p-3 Borders bg-white'>
                                        <div class='control-label-notes pb-3'>
                                            {{ trans('langUsers') }}
                                        </div>
                                        <ul class='list-group list-group-flush'>
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
                                                <span class='badge btn btn-success btn-sm pe-none float-end'>{{ $totalUserCnt }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                        @elseif  ($_GET['stats'] == 'cusers')
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead><tr class='list-header'>
                                        <th>{{ trans('langUsers') }}</th>
                                        <th>{{ trans('langResult') }}</th>
                                    </tr></thead>
                                    @foreach ($q as $data)
                                       <tr>
                                            <td>
                                                {{ $data->surname }} {{ $data->givenname }} (<a href='{{ $urlServer }}modules/admin/edituser.php?u={{ $data->user_id }}'>{{ $data->username }}</a>)
                                            </td>
                                            <td>{{ $data->num_of_courses }}</td>
                                       </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        @elseif ($_GET['stats'] == 'popularcourses')
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead><tr class="list-header">
                                        <th>{{ trans('langPopularCourses') }}</th>
                                        <th>{{ trans('langUsers') }}</th>
                                    </tr></thead>
                                    @foreach ($popularcourses as $data)
                                        <tr class = '{{ ($data->visible == COURSE_INACTIVE)? 'not_visible': ''  }}'>
                                            <td><a href='{{ $urlServer }}courses/{{ $data->code }}/'>{{ $data->title }}</a> <small>({{ $data->public_code }})</small> <br> <em>{{ $data->prof_names }}</em></td>
                                            <td>{{ $data->num_of_users }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        @endif
                   @endif
                
        </div>
</div>
</div>

@endsection
