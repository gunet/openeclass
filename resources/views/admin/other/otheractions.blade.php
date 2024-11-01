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

                    @include('layouts.partials.show_alert') 

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
                                                    <span>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
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
                                                    <span>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
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
                                                    <span>{{ trans('langExist') }} @php print_r(count($loginDouble)); @endphp</span>
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
                                    
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item list-group-item-action'>
                                                <div>{{ trans('langUsers') }}</div>
                                            </li>
                                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                <a class='TextBold' href='listusers.php?search=yes&verified_mail=1'>{{ trans('langMailVerificationYes') }}</a>
                                                <div>{{ $verifiedEmailUserCnt }}</div>
                                            </li>
                                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                <a class='TextBold' href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a>
                                                <div>{{ $unverifiedEmailUserCnt }}</div>
                                            </li>
                                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                <a class='TextBold' href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a>
                                                <div>{{ $verificationRequiredEmailUserCnt }}</div>
                                            </li>
                                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                <a class='TextBold' href='listusers.php?search=yes'>{{ trans('langTotal') }}</a>
                                                <div>{{ $totalUserCnt }}</div>
                                            </li>
                                        </ul>
                                    
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
                                                <p>{{ $data->surname }} {{ $data->givenname }}</p> (<a href='{{ $urlServer }}modules/admin/edituser.php?u={{ $data->user_id }}'>{{ $data->username }}</a>)
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
                                        <th class='text-center'>{{ trans('langUsers') }}</th>
                                    </tr></thead>
                                    @foreach ($popularcourses as $data)
                                        <tr class = '{{ ($data->visible == COURSE_INACTIVE)? 'not_visible': ''  }}'>
                                            <td><a href='{{ $urlServer }}courses/{{ $data->code }}/'>{{ $data->title }}</a> <small>({{ $data->public_code }})</small> <br> <em>{{ $data->prof_names }}</em></td>
                                            <td class='text-center'>{{ $data->num_of_users }}</td>
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
