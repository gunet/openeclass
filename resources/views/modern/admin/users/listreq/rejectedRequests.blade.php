@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

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
@endsection
