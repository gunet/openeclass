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

                    @if (isset($_GET['stats']))
                        @if (in_array($_GET['stats'], ['failurelogin', 'unregusers']))
                                {!! $extra_info !!}
                        @elseif ($_GET['stats'] == 'musers')
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class='list-header'>
                                        <th>
                                            {{ trans('langMultipleUsers') }}
                                        </th>
                                        <th class='text-end'>
                                            {{ trans('langResult') }}
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
                                        <th>{{ trans('langMultipleAddr') }} e-mail</th>
                                        <th class='right'>
                                            {{ trans('langResult') }}
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
                                            {{ trans('langMultiplePairs') }} LOGIN - PASS
                                        </th>
                                        <th class='text-end'>
                                            {{ trans('langResult') }}
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
                                <div class='col-12'>
                                    <div class='shadow-sm p-3 rounded'>
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
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class='list-header'>
                                        <th>{{ trans('langUsers') }}</th>
                                        <th class='text-center'>{{ trans('langResult') }}</th>
                                    </tr>
                                    @foreach ($q as $data)
                                       <tr>
                                            <td>
                                                {{ $data->surname }} {{ $data->givenname }} (<a href='{{ $urlServer }}modules/admin/edituser.php?u={{ $data->user_id }}'>{{ $data->username }}</a>)
                                            </td>
                                            <td class='text-center'>{{ $data->num_of_courses }}</td>
                                       </tr>
                                    @endforeach
                                </table>
                            </div>
                        @elseif ($_GET['stats'] == 'popularcourses')
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <tr class="list-header">
                                        <th class='list-header'>{{ trans('langPopularCourses') }}</th>
                                        <th class='list-header text-center'>{{ trans('langUsers') }}</th>
                                    </tr>
                                    @foreach ($popularcourses as $data)
                                        <tr class = '{{ ($data->visible == COURSE_INACTIVE)? 'not_visible': ''  }}'>
                                            <td><a href='{{ $urlServer }}courses/{{ $data->code }}/'>{{ $data->title }}</a> <small>({{ $data->public_code }})</small> <br> <em>{{ $data->prof_names }}</em></td>
                                            <td class='text-center'>{{ $data->num_of_users }}</td>
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
