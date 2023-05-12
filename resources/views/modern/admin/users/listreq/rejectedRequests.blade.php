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

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id = 'requests_table' class='table-default'>
                            {!! table_header(2) !!}
                            @foreach ($user_requests as $user_request)
                                <tr>
                                    <td>{{ $user_request->givenname }} {{ $user_request->surname }} </td>
                                    <td>{{ $user_request->username }}</td>
                                    <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                                    <td>
                                        <small>{{ format_locale_date(strtotime($user_request->date_open), 'short', false) }}</small>
                                    </td>
                                    <td>
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
    </div>
</div>
@endsection
