@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($announcements)
        <div class='table-responsive'>
            <table class='table-default'>
                <tr class='list-header'>
                    <th style='width: 70%;'>{{ trans('langAnnouncement') }}</th>
                    <th>{{ trans('langDate') }}</th>
                    <th>{{ trans('langNewBBBSessionStatus') }}</th>
                    <th class="text-center">{!! icon('fa-gears') !!}</th>
                </tr>
                @foreach ($announcements as $announcement)
                    <tr{!! !$announcement->visible 
                           || !is_null($announcement->end) && $announcement->end <= date("Y-m-d H:i:s") 
                           || !is_null($announcement->begin) && $announcement->begin >= date("Y-m-d H:i:s") 
                           ? " class='not_visible'" : "" !!}>
                        <td>
                            <div class='table_td'>
                                <div class='table_td_header clearfix'>
                                    <a href='adminannouncements_single.php?ann_id={{ $announcement->id }}'>{{ $announcement->title }}</a>
                                </div>
                                <div class='table_td_body' data-id='{{ $announcement->id }}'>
                                    {!! standard_text_escape($announcement->body) !!}
                                </div>
                            </div>
                        </td>
                        <td>{{ claro_format_locale_date(trans('dateTimeFormatShort'), strtotime($announcement->date)) }}</td>
                        <td>
                            <div>
                                <ul class='list-unstyled'>
                                    <li>
                                        @if ($announcement->visible == 1)
                                            <span class='fa fa-eye'></span> {{ trans('langAdminAnVis') }}
                                        @else
                                            <span class='fa fa-eye-slash'></span> {{ trans('langAdminAnNotVis') }}
                                        @endif
                                    </li>
                                    @if (!is_null($announcement->end) && ($announcement->end <= date("Y-m-d H:i:s") ))
                                        <li class='text-danger'>
                                            <span class='fa fa-clock-o'></span> {{ trans('langAdminExpired') }}
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
    @else
    <div class='row'>
        <div class='col-xs-12'>
            <div class='alert alert-warning'>
                {{ trans('langNoAnnounce') }}
            </div>
        </div>
    </div>
    @endif
@endsection