@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @if (isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert')

                    @if (!$checkAuth)
                        <div class='alert alert-warning' role='alert'><i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            {{ trans('langOpenDelosRequireAuth') }}
                        </div>
                    @endif

                    @if ($jsonPublicObj == null and $jsonPrivateObj == null)
                        <div class='alert alert-warning' role='alert'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoVideo') }}</span>
                        </div>
                    @else
                        <div class='alert alert-info' role='alert'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>{{ trans('langOpenDelosPrivateNote') }}</span>
                        </div>
                        <form class='form-horizontal' method='post' action='{!! $urlAppend . "modules/video/edit.php?course=" . $course_code !!}'>
                            <div class='col-12'>
                                <div class='table-responsive mt-4'>
                                    <table class="table-default" id="delos_videos_table_{{ $course_code }}">
                                        <thead>
                                            <tr class="list-header">
                                                <th style='width:85%;'>{{ trans('langTitle') }}</th>
                                                <th style='width:10%;'>{{ trans('langDate') }}</th>
                                                <th style='width:5%;'>&nbsp;</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">
                                                    {{ trans('langOpenDelosPublicVideos') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($jsonPublicObj !== null && property_exists($jsonPublicObj, "resources") && count($jsonPublicObj->resources) > 0)
                                                @foreach ($jsonPublicObj->resources as $resource)
                                                    @php
                                                        $url = $jsonPublicObj->playerBasePath . '?rid=' . $resource->resourceID;
                                                        $urltoken = '&token=' . getDelosSignedTokenForVideo($resource->resourceID);
                                                    @endphp
                                                    <tr>
                                                        <td style='width:85%;'>
                                                            <a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a>
                                                            @if (isset($currentVideoLinks[$url]) && strtotime($resource->videoLecture->date) > strtotime($currentVideoLinks[$url]))
                                                                <span class='fa-solid fa-exclamation ps-2' style='color: red' title data-bs-original-title='{{ trans('langDelosNewFileVersion') }}' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                            @endif
                                                            <div class="help-block">{{ $resource->videoLecture->description }}</div>
                                                            <div class="help-block mt-2">{{ trans('langCreator') }}: {{ $resource->videoLecture->rights->creator->name }}</div>
                                                        </td>
                                                        <td style='width:10%;'>{{ format_locale_date(strtotime($resource->videoLecture->date), 'short', false) }}</td>
                                                        <td style='width:5%;'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input name='delosResources[]' type='checkbox' value='{{ $resource->resourceID }}' @if (isset($currentVideoLinks[$url])) checked @endif>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            @if ($checkAuth)
                                                @if ($jsonPrivateObj !== null && property_exists($jsonPrivateObj, "resources") && count($jsonPrivateObj->resources) > 0)
                                                    @foreach ($jsonPrivateObj->resources as $resource)
                                                        @php
                                                            $url = $jsonPrivateObj->playerBasePath . '?rid=' . $resource->resourceID;
                                                            $urltoken = '&token=' . getDelosSignedTokenForVideo($resource->resourceID);
                                                        @endphp
                                                        <tr>
                                                            <td style='width:85%;'>
                                                                <a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a>
                                                                @if (isset($currentVideoLinks[$url]) && strtotime($resource->videoLecture->date) > strtotime($currentVideoLinks[$url]))
                                                                    <span class='fa-solid fa-exclamation ps-2' style='color: red' title data-bs-original-title='{{ trans('langDelosNewFileVersion') }}' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                                @endif
                                                                <div class="help-block">{{ $resource->videoLecture->description }}</div>
                                                                <div class="help-block mt-2">{{ trans('langCreator') }}: {{ $resource->videoLecture->rights->creator->name }}</div>
                                                            </td>
                                                            <td>
                                                                {{ format_locale_date(strtotime($resource->videoLecture->date), 'short', false) }}
                                                            </td>
                                                            <td class="center">
                                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                                    <input name='delosResources[]' type='checkbox' value='{{ $resource->resourceID }}' @if (isset($currentVideoLinks[$url])) checked @endif>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class='form-group mt-5'>
                                        <label for='Category' class='col-sm-6 control-label-notes'>{{ trans('langCategory') }}</label>
                                        <div class='col-sm-12'>
                                            <select class='form-select' name='selectcategory' id='Category'>
                                                <option value='0'>--</option>
                                                <option value='0'>--</option>
                                                @foreach ($resultcategories as $category)
                                                    <option value='{{ $category->id }}'>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <input class='btn submitAdminBtn' type='submit' name='add_submit_delos' value='{{ trans('langAddModulesButton') }}'>
                                            <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                   @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script type='text/javascript'>
    $(document).ready(function() {
        $('#delos_videos_table_{{ $course_code }}').DataTable ({
            'stateSave': true,
            'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
            'lengthMenu': [10, 20, 30 , -1],
            'sPaginationType': 'full_numbers',
            'bAutoWidth': true,
            'searchDelay': 1000,
            'order' : [ [1, 'asc'] ],
            'oLanguage': {
                'lengthLabels': {
                    '-1': '{{ trans('langAllOfThem') }}'
                },
                'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                'zeroRecords': '{{ trans('langNoResult') }}',
                'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                'sInfoEmpty': '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                'sInfoFiltered': '',
                'sInfoPostFix': '',
                'sSearch': '',
                'sUrl': '',
                'oPaginate': {
                    'sFirst': '&laquo;',
                    'sPrevious': '&lsaquo;',
                    'sNext': '&rsaquo;',
                    'sLast': '&raquo;'
                }
            }
        });
        $('.dt-search input').attr({
            'class': 'form-control input-sm ms-0 mb-3',
            'placeholder': '{{ trans('langSearch') }}...'
        });
        $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
    });
</script>

@endsection
