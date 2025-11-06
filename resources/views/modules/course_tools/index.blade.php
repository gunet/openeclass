
@extends('layouts.default')

@push('head_scripts')
    <script>
        var langEmptyGroupName = '{{ trans('langNoPgTitle') }}'
    </script>
@endpush

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
                            @include('layouts.partials.sidebar', ['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert')

                    <div class='col-12'>
                        <div class="card panelCard card-default px-lg-4 py-lg-3 mt-3">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>{{ trans('langActivateCourseTools') }}</h3>
                            </div>
                            <div class='card-body'>
                                <form name="courseTools" action="{{ $post_url }}" method="post" enctype="multipart/form-data">
                                    <div class="table-responsive mt-0">
                                        <table class="table-default rounded-2">
                                            <thead><tr class='list-header'>
                                            <th width="45%" class="text-center">{{ trans('langInactiveTools') }}</th>
                                            <th width="10%" class="text-center">{{ trans('langMove') }}</th>
                                            <th width="45%" class="text-center">{{ trans('langActiveTools') }}</th>
                                            </tr></thead>
                                            <tr>
                                                <td class="text-center">
                                                    <select aria-label="{{ trans('langInactiveTools') }}" class="form-select h-100 rounded-0" name="toolStatInactive[]" id='inactive_box' size='17' multiple>
                                                        @foreach($toolSelection[0] as $item)
                                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn submitAdminBtn m-auto d-block" onClick="move('inactive_box','active_box')" aria-label="{{ trans('langMove') }}"><span class="fa fa-arrow-right"></span></button><br><br>
                                                    <button type="button" class="btn submitAdminBtn m-auto d-block" onClick="move('active_box','inactive_box')" aria-label="{{ trans('langMove') }}"><span class="fa fa-arrow-left"></span></button>
                                                </td>
                                                <td class="text-center">
                                                    <select aria-label="{{ trans('langActiveTools') }}" class="form-select h-100 rounded-0" name="toolStatActive[]" id='active_box' size='17' multiple>
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
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class='col-12 mt-4'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                <h3>
                                    {{ trans('langOperations') }}
                                </h3>
                                <div>
                                    <a class='btn submitAdminBtn' href='{{ $post_url }}&amp;add=link'><span class='fa fa-plus-circle'></span> <span class='hidden-xs hidden-lg ps-2'>{{ trans('langAddExtLink') }}</span></a>
                                </div>

                            </div>
                            <div class='card-body'>
                                @if(count($q) > 0)
                                    <table class='table-default mb-2'>
                                        @foreach($q as $externalLinks)
                                            <tr>
                                                <td class='text-start'>
                                                    <div class='row'>
                                                        <div class='col-10'>
                                                            <strong>{{  $externalLinks->title }}</strong></br>
                                                            <small class='text-muted'>{{ $externalLinks->url }}</small>
                                                        </div>
                                                        <div class='col-2 d-flex justify-content-end align-items-center'>
                                                            <a class='text-danger' href='{{ $post_url }}&amp;delete={{ getIndirectReference($externalLinks->id) }}'><span class='fa-solid fa-xmark Accent-200-cl'></span></a>
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

                    @if ((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course))

                    <div class='col-12 mt-4'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                <h3>
                                    {{ trans('langLtiConsumer') }}
                                </h3>

                                <div>
                                    <a class='btn submitAdminBtn' href='../lti_consumer/index.php?course={{ $course_code }}&amp;add=1'>
                                        <span class='fa fa-plus-circle pe-1'></span><span class='hidden-xs hidden-lg ps-2'>{{ trans('langNewLTITool') }}</span>
                                    </a>
                                </div>

                            </div>
                            <div class='card-body'>
                                @if ($lti_apps)
                                    <div class='table-responsive mt-0'>
                                        <table class='table-default rounded-2'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th style='width:30%'>{{ trans('langTitle') }}</th>
                                                    <th class='text-start'>{{ trans('langUnitDescr') }}</th>
                                                    <th class='text-start'>{{ trans('langLTIAppActions') }}</th>
                                                    @if ($is_editor)
                                                        <th class='text-center' aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
                                                    @endif
                                                </tr>
                                            </thead>

                                            @foreach ($lti_apps as $app)
                                                @if ($is_editor)
                                                    <tr {!!($app->is_active_for_course ? '' : " class='not_visible'")!!}>
                                                        <td class='text-start'><p>{!! $app->title !!}</p></td>
                                                        <td><p>{!! $app->description !!}</p></td>
                                                        <td class='text-nowrap'>{!! $app->joinLink !!}</td>
                                                        <td class='option-btn-cell text-center'>
                                                            @if (!empty($app->is_template_panopto))
                                                                {!! action_button([
                                                                    [ 'title' => (isset($app->course_visible) ? intval($app->course_visible) : 1) ? trans('langDeactivate') : trans('langActivate'),
                                                                      'url' => $app->templateEnableUrl,
                                                                      'icon' => (isset($app->course_visible) ? intval($app->course_visible) : 1) ? 'fa-eye' : 'fa-eye-slash' ],
                                                                    ])
                                                                !!}
                                                            @else
                                                                {!! action_button([
                                                                    [ 'title' => trans('langEditChange'),
                                                                      'url' => $app->editUrl,
                                                                      'icon' => 'fa-edit' ],
                                                                    [ 'title' => $app->enabled? trans('langDeactivate') : trans('langActivate'),
                                                                      'url' => $app->enableUrl,
                                                                      'icon' => $app->enabled? 'fa-eye': 'fa-eye-slash' ],
                                                                    [ 'title' => trans('langDelete'),
                                                                      'url' => $app->deleteUrl,
                                                                      'icon' => 'fa-xmark',
                                                                      'class' => 'delete',
                                                                      'confirm' => trans('langConfirmDelete') ],
                                                                    ])
                                                                !!}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class='text-center'><p>{!! $app->title !!}</p></td>
                                                        <td><p>{!! $app->description !!}</p></td>
                                                        <td class='text-center text-nowrap'>{!! $app->joinLink !!}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @else
                                    {{ trans('langNoLTIApps') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class='col-12 mt-4'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                <h3>
                                    {{ trans('langLtiPublishTool') }}
                                </h3>

                                <div>
                                    <a class='btn submitAdminBtn' href='editpublish.php?course={{ $course_code }}'>
                                        <span class='fa fa-plus-circle pe-1'></span><span class='hidden-xs hidden-lg ps-2'>{{ trans('langAdd') }}</span>
                                    </a>
                                </div>

                            </div>
                            <div class='card-body'>
                                @if ($lti_providers)
                                    <div class='table-responsive mt-0'>
                                        <table class='table-default rounded-2'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th style='width:30%'>{{ trans('langTitle') }}</th>
                                                    <th class='text-start'>{{ trans('langUnitDescr') }}</th>
                                                    <th class='text-start'>{{ trans('langNewLTIAppSessionDesc') }}</th>
                                                    @if ($is_editor)
                                                        <th class='text-center' aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
                                                    @endif
                                                </tr>
                                            </thead>

                                            @foreach ($lti_providers as $provider)
                                                @if ($is_editor)
                                                    <tr {!!($provider->enabled? '': " class='not_visible'")!!}>
                                                        <td class='text-start'><p>{!! $provider->title !!}</p></td>
                                                        <td><p>{!! $provider->description !!}</p></td>
                                                        <td class='option-btn-cell text-center'>
                                                            {!! action_button([
                                                                [ 'title' => trans('langEditChange'),
                                                                  'url' => $provider->editUrl,
                                                                  'icon' => 'fa-edit' ],
                                                                [ 'title' => trans('langViewShow'),
                                                                  'url' => $provider->showUrl,
                                                                  'icon' => 'fa-archive' ],
                                                                [ 'title' => $provider->enabled? trans('langDeactivate') : trans('langActivate'),
                                                                  'url' => $provider->enableUrl,
                                                                  'icon' => $provider->enabled? 'fa-eye': 'fa-eye-slash' ],
                                                                [ 'title' => trans('langDelete'),
                                                                  'url' => $provider->deleteUrl,
                                                                  'icon' => 'fa-xmark',
                                                                  'class' => 'delete',
                                                                  'confirm' => trans('langConfirmDelete') ],
                                                                ])
                                                            !!}
                                                        </td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class='text-center'><p>{!! $provider->title !!}</p></td>
                                                        <td><p>{!! $provider->description !!}</p></td>
                                                        <td class='text-center'>
                                                            <a href='{{ $provider->showUrl }}'>{{trans('langViewShow') }}</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @else
                                    {{ trans('langNoPUBLTIApps') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    @endif
                </div>
            </div>

        </div>

  </div>
</div>

@endsection
