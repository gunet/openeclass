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

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif

            @if ($announcements)
            <div class='col-12'>
                <div class='table-responsive mt-0'>
                    <table id='adminAnnouncementsTable' class='table-default'><thead>
                        <tr class='list-header'>
                            <th>{{ trans('langAnnouncement') }}</th>
                            <th>{{ trans('langDate') }}</th>
                            <th>{{ trans('langNewBBBSessionStatus') }}</th>
                            <th></th>
                        </tr></thead>
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
                                                    <span class='fa fa-clock'></span> {{ trans('langExpired') }}
                                                </li>
                                            @elseif ( !is_null($announcement->begin) && ($announcement->begin >= date("Y-m-d H:i:s") ))
                                                <li class='text-success'>
                                                    <span class='fa fa-clock'></span> {{ trans('langAdminWaiting') }}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                                <td class="option-btn-cell text-end">{!!
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
                                            'icon' => 'fa-xmark'
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
                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoAnnounce') }}</span>
                    </div>
                </div>

            @endif
        </div>
    </div>
</div>
@endsection
