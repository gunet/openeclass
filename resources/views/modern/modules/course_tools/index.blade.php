
@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        var langEmptyGroupName = '{{ trans('langNoPgTitle') }}'
    </script>
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                       
                        <a class="btn btn-primary btn-sm d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    

                    
                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-sm-12'>
                        <div class="panel panel-default">
                            <div class='panel-heading'>
                                <h3 class='panel-title text-center'>{{ trans('langActivateCourseTools') }}</h3>
                            </div>
                            <div class='panel-body'>
                                <form name="courseTools" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method="post" enctype="multipart/form-data">
                                    <div class="table-responsive">
                                        <table class="announcements_table">
                                            <tr class='notes_thead'>
                                            <th width="45%" class="text-white text-center">{{ trans('langInactiveTools') }}</th>
                                            <th width="10%" class="text-white text-center">{{ trans('langMove') }}</th>
                                            <th width="45%" class="text-white text-center">{{ trans('langActiveTools') }}</th>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <select class="form-select" name="toolStatInactive[]" id='inactive_box' size='17' multiple>
                                                        @foreach($toolSelection[0] as $item)
                                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center" style="vertical-align: middle;">
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="move('inactive_box','active_box')"><span class="fa fa-arrow-right"></span></button><br><br>
                                                    <button type="button" class="btn btn-sm btn-secondary" onClick="move('active_box','inactive_box')"><span class="fa fa-arrow-left"></span></button>
                                                </td>
                                                <td class="text-center">
                                                    <select class="form-select" name="toolStatActive[]" id='active_box' size='17' multiple>
                                                        @foreach($toolSelection[1] as $item)
                                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <input type="submit" class="btn btn-primary" value="{{ trans('langSubmit') }}" name="toolStatus" onClick="selectAll('active_box',true)" />
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    {!! $csrf !!}
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class='col-sm-12 mt-3'>
                        <div class='panel panel-default panel-action-btn-default'>
                            <div class='panel-heading'>
                                <div class='row'>
                                    <div class='col-md-6 col-10 text-start pt-md-1 pt-0'>
                                        <span class='panel-title'> {{ trans('langOperations') }}</span>
                                    </div>
                                    <div class='col-md-6 col-2 text-end'>
                                        <a class='btn btn-success btn-sm' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;action=true'><span class='fa fa-plus-circle'></span> <span class='hidden-xs'>{{ trans('langAddExtLink') }}</span></a>
                                    </div>
                                </div>
                            </div>
                            <div class='panel-body'>
                                <table class='announcements_table mb-2 bg-light'>
                                @foreach($q as $externalLinks)
                                    <tr class='bg-body'>
                                        <td class='text-start'>
                                            <div style='display:inline-block; width: 80%;'>
                                                <strong>{{  $externalLinks->title }}</strong>
                                                <div style='padding-top:8px;'><small class='text-muted'>{{ $externalLinks->url }}</small></div>
                                            </div>
                                            <div class='float-end'>
                                                <a class='text-danger' href='?course={{ $course_code }}&amp;delete={{ getIndirectReference($externalLinks->id) }}'><span class='fa fa-times'></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </table>
                            </div>
                            
                        </div>
                    </div>

                    <div class='col-sm-12 mt-3'>
                        <div class='panel panel-default'>
                            <div class='panel-heading'>
                                <div class='row'>
                                    <div class='col-md-6 col-10 text-start pt-md-1 pt-0'>
                                        <span class='panel-title'>{{ trans('langLtiConsumer') }}</span>
                                    </div>
                                    <div class='col-md-6 col-2 text-end'>
                                        <a class='btn btn-success btn-sm' href='../lti_consumer/index.php?course={{ $course_code }}&amp;add=1'>
                                            <span class='fa fa-plus-circle'></span><span class='hidden-xs'>{{ trans('langNewLTITool') }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class='panel-body'>
                                @php 
                                    load_js('trunk8');
                                    $activeClause = ($is_editor) ? '' : "AND enabled = 1";
                                    $result = Database::get()->queryArray("SELECT * FROM lti_apps
                                        WHERE course_id = ?s $activeClause AND is_template = 0 ORDER BY title ASC", $course_id);
                                @endphp

                                @if($result)
            
                                        <div class='col-sm-12'>
                                            <div class='table-responsive mt-0'>
                                            <table class='table-default'>
                                                <tr class='list-header'>
                                                    <th style='width:30%'>{{ trans('langTitle') }}</th>
                                                    <th class='text-center'>{{ trans('langUnitDescr') }}</th>
                                                    <th class='text-center'>{{ trans('langLTIAppActions') }}</th>
                                                    @if ($is_editor)
                                                        <th class='text-center'>{!! icon('fa-gears') !!}</th>
                                                    @endif
                                            </tr>

                                            @foreach ($result as $row)
                                                    @php 
                                                        $id = $row->id;
                                                        $title = $row->title;
                                                        $desc = isset($row->description)? $row->description: '';
                                                        $canJoin = ($row->enabled == 1 || $is_editor);
                                                    @endphp

                                                    @if ($canJoin)
                                                        @if ($row->launchcontainer == LTI_LAUNCHCONTAINER_EMBED)
                                                            @php $joinLink = create_launch_button($row->id); @endphp
                                                        @else
                                                            @php
                                                                $joinLink = create_join_button(
                                                                    $row->lti_provider_url,
                                                                    $row->lti_provider_key,
                                                                    $row->lti_provider_secret,
                                                                    $row->id,
                                                                    "lti_tool",
                                                                    $row->title,
                                                                    $row->description,
                                                                    $row->launchcontainer
                                                                );
                                                            @endphp
                                                        @endif
                                                    @else
                                                        @php $joinLink = q($title); @endphp
                                                    @endif

                                                    @if ($is_editor)
                                                        @if (!$headingsSent)
                                                        @php $headingsSent = true; @endphp
                                                        @endif
                                                        <tr {!!($row->enabled? '': " class='not_visible'")!!}>
                                                            <td class='text-left'>{!! $title !!}</td>
                                                            <td>{!! $desc !!}</td>
                                                            <td class='text-center'>{!! $joinLink !!}</td>
                                                            <td class='option-btn-cell text-center'>
                                                            {!! action_button(array(
                                                                    array(  'title' => trans('langEditChange'),
                                                                            'url' => "../lti_consumer/index.php?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=edit",
                                                                            'icon' => 'fa-edit'),
                                                                    array(  'title' => $row->enabled? trans('langDeactivate') : trans('langActivate'),
                                                                            'url' => "../lti_consumer/index.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_".
                                                                                    ($row->enabled? 'disable' : 'enable'),
                                                                            'icon' => $row->enabled? 'fa-eye': 'fa-eye-slash'),
                                                                    array(  'title' => trans('langDelete'),
                                                                            'url' => "../lti_consumer/index.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_delete",
                                                                            'icon' => 'fa-times',
                                                                            'class' => 'delete',
                                                                            'confirm' => trans('langConfirmDelete'))
                                                                    )) !!}
                                                            </td></tr>
                                                    @else
                                                        @if (!$headingsSent)
                                                            @php $headingsSent = true; @endphp
                                                        @endif
                                                        <tr>
                                                            <td class='text-center'>{!! $title !!}</td>
                                                            <td>{!! $desc !!}</td>
                                                            <td class='text-center'>{!! $joinLink !!}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                @if ($headingsSent)
                                                    </table></div></div>
                                                @endif

                                                @if (!$is_editor and !$headingsSent)
                                                    <div class='col-sm-12'><div class='alert alert-warning'>{{trans('langNoLTIApps')}}</div></div>
                                                @endif
                                @else
                                    <div class='col-sm-12'><div class='alert alert-warning'>{{trans('langNoLTIApps')}}</div></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection