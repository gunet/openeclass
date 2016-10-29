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
    
    @if ($jsonObj !== null && property_exists($jsonObj, "resources"))
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
                        @foreach ($jsonObj->resources as $resource)
                            <?php 
                                $url = $jsonObj->playerBasePath . '?rid=' . $resource->resourceID;
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
                                <td align="left"><a href="{!! $url !!}" class="fileURL" target="_blank" title="{{ $resource->videoLecture->title }}">{{ $resource->videoLecture->title }}</a></td>
                                <td>{{ $resource->videoLecture->description }}</td>
                                <td>{{ $resource->videoLecture->rights->creator->name }}</td>
                                <td>{{ $resource->videoLecture->organization->name }}</td>
                                <td>{{ $resource->videoLecture->date }}</td>
                                <td class="center" width="10">
                                    <input name="delosResources[]" value="{{ $resource->resourceID }}" type="checkbox"/> {!! $alreadyAdded !!}
                                </td>
                            </tr>
                        @endforeach
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
        <div class='alert alert-warning' role='alert'>{!! $GLOBALS['langOpenDelosReplaceInfo'] !!}</div>
    @else
        <div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div>
    @endif
    
@endsection
