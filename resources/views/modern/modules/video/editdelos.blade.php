@extends('layouts.default')

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


                    {!!
                    action_bar(array(
                        array('title' => $GLOBALS['langBack'],
                            'url' => $backPath,
                            'icon' => 'fa-reply',
                            'level' => 'primary-label')
                        )
                    )
                    !!}
                    
                    <div class='col-sm-12'>
                        <form method='POST' action='{!! $urlAppend . "modules/video/edit.php?course=" . $course_code !!}'>
                            <div class="table-responsive">
                                <table class="table-default">
                                    <tbody>
                                        <tr class="list-header">
                                            <th class='ps-3'>{{ trans('langTitle') }}</th>
                                            <th>{{ trans('langDescription') }}</th>
                                            <th>{{ trans('langcreator') }}</th>
                                            <th>{{ trans('langpublisher') }}</th>
                                            <th>{{ trans('langDate') }}</th>
                                            <th>{{ trans('langSelect') }}</th>
                                        </tr>
                                        <tr class="bg-light">
                                            <th class='p-3' colspan="6">{{ trans('langOpenDelosPublicVideos') }}</th>
                                        </tr>
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
                                                    <td align="left"><a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a></td>
                                                    <td>{{ $resource->videoLecture->description }}</td>
                                                    <td>{{ $resource->videoLecture->rights->creator->name }}</td>
                                                    <td>{{ $resource->videoLecture->organization->name }}</td>
                                                    <td>{{ $resource->videoLecture->date }}</td>
                                                    <td class="center" width="10">
                                                        <input name="delosResources[]" value="{{ $resource->resourceID }}" type="checkbox"/> {!! $alreadyAdded !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan='6'><div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div></td></tr>
                                        @endif
                                        <tr class="list-header">
                                            <th class='ps-3' colspan="6">{{ trans('langOpenDelosPrivateVideos') }}</th>
                                        </tr>
                                        @if (!$checkAuth)
                                            <?php
                                                $authUrl = (isCASUser()) ? getDelosURL() . getDelosRLoginCASAPI() : getDelosURL() . getDelosRLoginAPI();
                                                $authUrl .= "?token=" . getDelosSignedToken();
                                            ?>
                                            <tr><td colspan='6'><div class='alert alert-warning' role='alert'>
                                                {{ trans('langOpenDelosRequireAuth') }}&nbsp;<a href='{{ $authUrl }}' title='{{ trans('langOpenDelosAuth') }}'>{{ trans('langOpenDelosRequireAuthHere') }}</a>
                                            </div></td></tr>
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
                                                        <td align="left"><a href="{!! $url . $urltoken !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a></td>
                                                        <td>{{ $resource->videoLecture->description }}</td>
                                                        <td>{{ $resource->videoLecture->rights->creator->name }}</td>
                                                        <td>{{ $resource->videoLecture->organization->name }}</td>
                                                        <td>{{ $resource->videoLecture->date }}</td>
                                                        <td class="center" width="10">
                                                            <input name="delosResources[]" value="{{ $resource->resourceID }}" type="checkbox"/> {!! $alreadyAdded !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan='6'><div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div></td></tr>
                                            @endif
                                        @endif
                                        <tr><td colspan='6'><div class='alert alert-warning' role='alert'>{{ trans('langOpenDelosPrivateNote') }}</div></td></tr>
                                        <tr>
                                            <th colspan="4">
                                                <div class='form-group'>
                                                    <label for='Category' class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                                                    <div class='col-sm-10'>
                                                        <select class='form-select' name='selectcategory'>
                                                            <option value='0'>--</option>
                                                            @foreach ($resultcategories as $category)
                                                                <option value='{{ $category->id }}'>{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </th>
                                            <th colspan="2">
                                                <div class="float-end">
                                                    <input class="btn submitAdminBtn" name="add_submit_delos" value="{{ trans('langAddModulesButton') }}" type="submit">
                                                </div>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div class='col-sm-12'>
                        <div class='alert alert-warning' role='alert'>{!! trans('langOpenDelosReplaceInfo') !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
