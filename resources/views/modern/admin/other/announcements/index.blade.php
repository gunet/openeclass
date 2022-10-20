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

                    @if ($announcements)
                    <div class='col-sm-12'>
                        <div class='table-responsive'>
                            <table class='announcements_table'>
                                <tr class='notes_thead'>
                                    <th class='text-white' style='width: 70%;'>{{ trans('langAnnouncement') }}</th>
                                    <th class='text-white'>{{ trans('langDate') }}</th>
                                    <th class='text-white'>{{ trans('langNewBBBSessionStatus') }}</th>
                                    <th class="text-white text-center">{!! icon('fa-gears') !!}</th>
                                </tr>
                                @foreach ($announcements as $announcement)
                                    <tr{!! !$announcement->visible
                                        || !is_null($announcement->end) && $announcement->end <= date("Y-m-d H:i:s")
                                        || !is_null($announcement->begin) && $announcement->begin >= date("Y-m-d H:i:s")
                                        ? " class='not_visible'" : "" !!}>
                                        <td>
                                            <div class='table_td'>
                                                <div class='table_td_header clearfix'>
                                                    <a href='adminannouncements.php?ann_id={{ $announcement->id }}'>{{ $announcement->title }}</a>
                                                </div>
                                                <div class='table_td_body' data-id='{{ $announcement->id }}'>
                                                    {!! standard_text_escape($announcement->body) !!}
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ format_locale_date(strtotime($announcement->date), 'short') }}</td>
                                        <td>
                                            <div>
                                                <ul class='list-unstyled'>
                                                    <li>
                                                        @if ($announcement->visible == 1)
                                                            <span class='fa fa-eye'></span> {{ trans('langAdminAnVis') }}
                                                        @else
                                                            <span class='fa fa-eye-slash'></span> {{ trans('langInvisible') }}
                                                        @endif
                                                    </li>
                                                    @if (!is_null($announcement->end) && ($announcement->end <= date("Y-m-d H:i:s") ))
                                                        <li class='text-danger'>
                                                            <span class='fa fa-clock-o'></span> {{ trans('langExpired') }}
                                                        </li>
                                                    @elseif ( !is_null($announcement->begin) && ($announcement->begin >= date("Y-m-d H:i:s") ))
                                                        <li class='text-success'>
                                                            <span class='fa fa-clock-o'></span> {{ trans('langAdminWaiting') }}
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="option-btn-cell">{!!
                                            action_button([
                                                [
                                                    'title' => trans('langEditChange'),
                                                    'url' => "$_SERVER[SCRIPT_NAME]?modify=$announcement->id",
                                                    'icon' => 'fa-edit'
                                                ],
                                                [
                                                    'title' => $announcement->visible ? trans('langViewHide') : trans('langViewShow'),
                                                    'url' => "$_SERVER[SCRIPT_NAME]?id=$announcement->id&amp;vis=$announcement->visible",
                                                    'icon' => $announcement->visible ? 'fa-eye-slash' : 'fa-eye'
                                                ],
                                                [
                                                    'title' => trans('langUp'),
                                                    'url' => "$_SERVER[SCRIPT_NAME]?up=$announcement->id",
                                                    'icon' => 'fa-arrow-up',
                                                    'level' => 'primary',
                                                    'disabled' => $announcement->order == count($announcements)
                                                ],
                                                [
                                                    'title' => trans('langDown'),
                                                    'url' => "$_SERVER[SCRIPT_NAME]?down=$announcement->id",
                                                    'icon' => 'fa-arrow-down',
                                                    'level' => 'primary',
                                                    'disabled' => $announcement->order == 1
                                                ],
                                                [
                                                    'title' => trans('langDelete'),
                                                    'class' => 'delete',
                                                    'url' => "$_SERVER[SCRIPT_NAME]?delete=$announcement->id",
                                                    'confirm' => trans('langConfirmDelete'),
                                                    'icon' => 'fa-times'
                                                ]
                                            ]) !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    @else

                        <div class='col-12'>
                            <div class='alert alert-warning'>
                                {{ trans('langNoAnnounce') }}
                            </div>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
