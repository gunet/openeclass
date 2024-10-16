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

                    @include('layouts.partials.show_alert')

                    <form class='form-horizontal' method='post' action='{!! $urlAppend . "modules/video/edit.php?course=" . $course_code !!}'>
                        <div class='col-12'>
                            <div class='table-responsive mt-4'>
                                <table class="table-default">
                                    <thead>
                                        <tr class="list-header">
                                            <th style='width:70%;'>{{ trans('langTitle') }}</th>
                                            <th style='width:10%;'>{{ trans('langCreator') }}</th>
                                            <th style='width:10%;'>{{ trans('langpublisher') }}</th>
                                            <th style='width:10%;'>{{ trans('langDate') }}</th>
                                            <th style='width:5%;'>&nbsp;</th>
                                        </tr>
                                        <tr>
                                            <th colspan="5">{{ trans('langOpenDelosPublicVideos') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($jsonPublicObj !== null && property_exists($jsonPublicObj, "resources") && count($jsonPublicObj->resources) > 0)
                                            @foreach ($jsonPublicObj->resources as $resource)
                                                <?php
                                                    $url = $jsonPublicObj->playerBasePath . '?rid=' . $resource->resourceID;
                                                    $urltoken = '&token=' . getDelosSignedTokenForVideo($resource->resourceID);
                                                    $alreadyAdded = '';
                                                    if (isset($currentVideoLinks[$url])) {
                                                        $alreadyAdded = '<span style="color:red">*';
                                                        if (strtotime($resource->videoLecture->date) > strtotime($currentVideoLinks[$url])) {
                                                            $alreadyAdded .= '*';
                                                        }
                                                        $alreadyAdded .= '</span>';
                                                    }
                                                ?>
                                                <tr>
                                                    <td style='width:70%;'>
                                                        <a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a>
                                                        <div class="help-block">{{ $resource->videoLecture->description }}</div>
                                                    </td>
                                                    <td style='width:10%;'>{{ $resource->videoLecture->rights->creator->name }}</td>
                                                    <td style='width:10%;'>{{ $resource->videoLecture->organization->name }}</td>
                                                    <td style='width:10%;'>{{ format_locale_date(strtotime($resource->videoLecture->date), 'short', false) }}</td>
                                                    <td style='width:5%;'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input name="delosResources[]" value="{{ $resource->resourceID }}" type="checkbox"><span class='checkmark'></span> {!! $alreadyAdded !!}
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan='5'>
                                                    <div class='alert alert-warning' role='alert'>
                                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoVideo') }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="list-header">
                                            <th colspan="5">{{ trans('langOpenDelosPrivateVideos') }}
                                                <span class="help-block">({{ trans('langOpenDelosRequireAuth') }})</span>
                                            </th>
                                        </tr>
                                        @if (!$checkAuth)
                                            <?php
                                                $authUrl = (isCASUser()) ? getDelosURL() . getDelosRLoginCASAPI() : getDelosURL() . getDelosRLoginAPI();
                                                $authUrl .= "?token=" . getDelosSignedToken();
                                            ?>
                                            <tr>
                                                <td colspan='5'>
                                                    <div class='alert alert-warning' role='alert'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                                        {{ trans('langOpenDelosRequireAuth') }}&nbsp;<a href='{{ $authUrl }}' title='{{ trans('langOpenDelosAuth') }}'>{{ trans('langOpenDelosRequireAuthHere') }}</a></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            @if ($jsonPrivateObj !== null && property_exists($jsonPrivateObj, "resources") && count($jsonPrivateObj->resources) > 0)
                                                @foreach ($jsonPrivateObj->resources as $resource)
                                                    <?php
                                                        $url = $jsonPrivateObj->playerBasePath . '?rid=' . $resource->resourceID;
                                                        $urltoken = '&token=' . getDelosSignedTokenForVideo($resource->resourceID);
                                                        $alreadyAdded = '';
                                                        if (isset($currentVideoLinks[$url])) {
                                                            $alreadyAdded = '<span style="color:red">*';
                                                            if (strtotime($resource->videoLecture->date) > strtotime($currentVideoLinks[$url])) {
                                                                $alreadyAdded .= '*';
                                                            }
                                                            $alreadyAdded .= '</span>';
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td align="left">
                                                            <a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a>
                                                            <div class="help-block">{{ $resource->videoLecture->description }}</div>
                                                        </td>
                                                        <td>{{ $resource->videoLecture->rights->creator->name }}</td>
                                                        <td>{{ $resource->videoLecture->organization->name }}</td>
                                                        <td>{{ format_locale_date(strtotime($resource->videoLecture->date), 'short', false) }}</td>
                                                        <td class="center">
                                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                                <input name="delosResources[]" value="{{ $resource->resourceID }}" type="checkbox"/><span class='checkmark'></span> {!! $alreadyAdded !!}
                                                            </label>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan='5'>
                                                        <div class='alert alert-warning' role='alert'>
                                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                            <span>{{ trans('langNoVideo') }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                        <tr>
                                            <td colspan='5'>
                                                <div class='alert alert-info' role='alert'>
                                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                    <span>{{ trans('langOpenDelosPrivateNote') }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="list-header">
                                            <th colspan="4">
                                                <div class='form-group'>
                                                    <label for='Category' class='col-sm-12 control-label-notes'>{{ trans('langCategory') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <select class='form-select' name='selectcategory'>
                                                            <option value='0'>--</option>
                                                            @foreach ($resultcategories as $category)
                                                                <option value='{{ $category->id }}'>{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="float-end">
                                                    <input class="btn submitAdminBtn" name="add_submit_delos" value="{{ trans('langAddModulesButton') }}" type="submit">
                                                </div>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>

                    <div class='col-12'>
                        <div class='alert alert-warning' role='alert'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>{!! trans('langOpenDelosReplaceInfo') !!}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
