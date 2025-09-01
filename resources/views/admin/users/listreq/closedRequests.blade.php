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

                <div class='col-12'>
                    <div class='table-responsive'>
                        <table id='requests_table' class='table-default'>
                        {!! table_header(1) !!}
                        @foreach ($user_requests as $user_request)
                            <tr>
                                <td>{{ $user_request->givenname }} {{ $user_request->surname }} </td>
                                <td>{{ $user_request->username }}</td>
                                <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                                <td>{{ ($user_request->status == USER_TEACHER)?  trans('langCourseCreate'): ' - ' }} </td>
                                <td>
                                    <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                                </td>
                                <td data-sort="{{ date("Y-m-d H:i", strtotime($user_request->date_closed)) }}">
                                    <small>{{ format_locale_date(strtotime($user_request->date_closed), 'short', false) }}</small>
                                </td>
                                <td class='option-btn-cell'>
                                    {!! action_button(array(
                                        array('title' => trans('langRestore'),
                                                'url' => "$_SERVER[SCRIPT_NAME]?id=$user_request->id&amp;show=closed$reqtype",
                                                'icon' => 'fa-retweet'))) !!}
                                </td>
                            </tr>
                        @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
