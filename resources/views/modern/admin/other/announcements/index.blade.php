@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if ($announcements)
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
                                        <td>{{ claro_format_locale_date(trans('dateTimeFormatShort'), strtotime($announcement->date)) }}</td>
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
                    @else
                    
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
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