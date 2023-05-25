
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

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    

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

                    <div class='col-12'>
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langActivateCourseTools') }}</div>
                            </div>
                            <div class='card-body'>
                                <form name="courseTools" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method="post" enctype="multipart/form-data">
                                    <div class="table-responsive mt-0">
                                        <table class="table-default rounded-2">
                                            <tr class='list-header'>
                                            <th width="45%" class="text-center">{{ trans('langInactiveTools') }}</th>
                                            <th width="10%" class="text-center">{{ trans('langMove') }}</th>
                                            <th width="45%" class="text-center">{{ trans('langActiveTools') }}</th>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <select class="form-select h-100 rounded-0" name="toolStatInactive[]" id='inactive_box' size='17' multiple>
                                                        @foreach($toolSelection[0] as $item)
                                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn submitAdminBtn m-auto d-block" onClick="move('inactive_box','active_box')"><span class="fa fa-arrow-right"></span></button><br><br>
                                                    <button type="button" class="btn submitAdminBtn m-auto d-block" onClick="move('active_box','inactive_box')"><span class="fa fa-arrow-left"></span></button>
                                                </td>
                                                <td class="text-center">
                                                    <select class="form-select h-100 rounded-0" name="toolStatActive[]" id='active_box' size='17' multiple>
                                                        @foreach($toolSelection[1] as $item)
                                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <input type="submit" class="btn submitAdminBtn m-auto d-block" value="{{ trans('langSubmit') }}" name="toolStatus" onClick="selectAll('active_box',true)" />
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    {!! $csrf !!}
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class='col-12 mt-5'>
                        <div class='card panelCard px-lg-4 py-lg-3'>
                            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                
                                <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                    {{ trans('langOperations') }}
                                </div>
                                <div>
                                    <a class='btn submitAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;action=true'><span class='fa fa-plus-circle'></span> <span class='hidden-xs hidden-lg'>{{ trans('langAddExtLink') }}</span></a>
                                </div>
                                
                            </div>
                            <div class='card-body'>
                                @if(count($q) > 0)
                                    <table class='table-default mb-2 bg-light'>
                                        @foreach($q as $externalLinks)
                                            <tr class='bg-body'>
                                                <td class='text-start'>
                                                    <div class='row'>
                                                        <div class='col-10'>
                                                            <strong>{{  $externalLinks->title }}</strong></br>
                                                            <small class='text-muted'>{{ $externalLinks->url }}</small>
                                                        </div>
                                                        <div class='col-2 text-end'>
                                                            <a class='text-danger' href='?course={{ $course_code }}&amp;delete={{ getIndirectReference($externalLinks->id) }}'><span class='fa fa-times'></span></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    {{ trans('langNoInfoAvailable')}}
                                @endif
                            </div>
                            
                        </div>
                    </div>

                    <div class='col-12 mt-5'>
                        <div class='card panelCard px-lg-4 py-lg-3'>
                            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                
                                <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                    {{ trans('langLtiConsumer') }}
                                </div>
                                   
                                <div>
                                    <a class='btn submitAdminBtn' href='../lti_consumer/index.php?course={{ $course_code }}&amp;add=1'>
                                        <span class='fa fa-plus-circle pe-1'></span><span class='hidden-xs hidden-lg'>{{ trans('langNewLTITool') }}</span>
                                    </a>
                                </div>
                                
                            </div>
                            <div class='card-body'>
                                @php 
                                    load_js('trunk8');
                                    $activeClause = ($is_editor) ? '' : "AND enabled = 1";
                                    $result = Database::get()->queryArray("SELECT * FROM lti_apps
                                        WHERE course_id = ?s $activeClause AND is_template = 0 ORDER BY title ASC", $course_id);
                                @endphp

                                @if($result)
                                        <div class='table-responsive mt-0'>
                                            <table class='table-default rounded-2'>
                                                <tr class='list-header'>
                                                    <th style='width:30%'>{{ trans('langTitle') }}</th>
                                                    <th class='text-start'>{{ trans('langUnitDescr') }}</th>
                                                    <th class='text-start'>{{ trans('langLTIAppActions') }}</th>
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
                                                                <td class='text-start'>{!! $title !!}</td>
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
                                            </table>
                                        
                                        </div>
                                    @endif

                                    @if (!$is_editor and !$headingsSent)
                                        {{trans('langNoLTIApps')}}
                                    @endif
                                @else
                                    {{trans('langNoLTIApps')}}
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