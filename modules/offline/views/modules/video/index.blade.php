@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}

    @if ($count_video > 0 or $count_video_links > 0)
        @if (count($items))
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='table-responsive'>
                        <table class='table-default nocategory-links'>
                            <tr class='list-header'>
                                <th>{{ trans('langVideoDirectory') }}</th>
                                <th class='text-center' style='width:134px'>{{ trans('langDate') }}</th>
                            </tr>
                            @include('modules.video.common.videoList')
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($num_of_categories > 0)
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='table-responsive'>
                        <table class='table-default category-links'>
                            <tr class='list-header'>
                                <th>{{ trans('langCatVideoDirectory') }}&nbsp;&nbsp;&nbsp;
                                    {!! icon('fa-folder-open', $GLOBALS['langViewShow']) !!}
                                </th>
                                <th class='text-center' style='width:100px;'>{{ trans('langDate') }}</th>
                            </tr>
                        @foreach ($categories as $myrow)
                            <?php
                                $description = standard_text_escape($myrow->description);
                                $folder_icon = icon('fa-folder-open-o', $GLOBALS['langViewShow']);
                            ?>
                            <tr class='link-subcategory-title'><th class='category-link' colspan='2'>{!! $folder_icon !!}&nbsp;{{ $myrow->name }}
                            @if (!empty($description))
                                <br><span class='link-description'>{{ $description }}</span>
                            @endif
                                </th>
                            </tr>
                            <?php
                                $currentcatresults = getLinksOfCategory($myrow->id, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin);
                            ?>
                            @include('modules.video.common.videoList', ['items' => $currentcatresults])
                        @endforeach
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div>
    @endif

@endsection
