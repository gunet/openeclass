@extends('layouts.default')

@section('content')
    {!!
    action_bar(array(
        array('title' => $GLOBALS['langBack'],
              'url' => $backPath,
              'icon' => 'fa-reply',
              'level' => 'primary-label')
        )
    )
    !!}
    
    <form method='POST' action='{!! $urlAppend . "modules/video/edit.php?course=" . $course_code !!}'>
        <div class="table-responsive">
            <table class="table-default">
                <tbody>
                    <tr class="list-header">
                        <th>{{ trans('langTitle') }}</th>
                        <th>{{ trans('langDescription') }}</th>
                        <th>{{ trans('langcreator') }}</th>
                        <th>{{ trans('langpublisher') }}</th>
                        <th>{{ trans('langDate') }}</th>
                        <th>{{ trans('langSelect') }}</th>
                    </tr>
                    <tr class="list-header">
                        <th colspan="6">{{ trans('langOpenDelosPublicVideos') }}</th>
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
                        <th colspan="6">{{ trans('langOpenDelosPrivateVideos') }}</th>
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
                                    <select class='form-control' name='selectcategory'>
                                        <option value='0'>--</option>
                                        @foreach ($resultcategories as $category)
                                            <option value='{{ $category->id }}'>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </th>
                        <th colspan="2">
                            <div class="pull-right">
                                <input class="btn btn-primary" name="add_submit_delos" value="{{ trans('langAddModulesButton') }}" type="submit">
                            </div>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
    <div class='alert alert-warning' role='alert'>{{ trans('langOpenDelosReplaceInfo') }}</div>

@endsection
