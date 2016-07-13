<?php
    $display_tools = $is_editor && !$is_in_tinymce;
    $colspan = $display_tools ? 2 : 3;
    $embedParam = (isset($_REQUEST['embedtype'])) ? '&amp;embedtype=' . q($_REQUEST['embedtype']) : '';
    $expand_all = isset($_GET['d']) && $_GET['d'] == '1';
    
    if ($display_tools) {
        $actionBarArray = array(
            array('title' => $GLOBALS['langAddV'],
                  'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=file",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $GLOBALS['langAddVideoLink'],
                  'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=url",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $GLOBALS['langCategoryAdd'],
                  'url' => $urlAppend . "modules/video/editCategory.php?course=" . $course_code,
                  'icon' => 'fa-plus-circle'),
            array('title' => $GLOBALS['langQuotaBar'],
                  'url' => $urlAppend . "modules/video/showQuota.php?course=" . $course_code,
                  'icon' => 'fa-pie-chart')
        );
        if (isDelosEnabled()) {
            $actionBarArray[] = getDelosButton($course_code, $urlAppend);
        }
        $action_bar = action_bar($actionBarArray);
    }
?>

@extends('layouts.default')

@section('content')
    {!! $action_bar or '' !!}

    @if ($count_video > 0 or $count_video_links > 0)
        @if (count($items))
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='table-responsive'>
                        <table class='table-default nocategory-links'>
                            <tr class='list-header'>
                                <th>{!! headlink($GLOBALS['langVideoDirectory'], 'title') !!}</th>
                                <th class='text-center' style='width:134px'>{!! headlink($GLOBALS['langDate'], 'date') !!}</th>
                                @if (!$is_in_tinymce)
                                    <th class='text-center'>{!! icon('fa-gears') !!}</th>
                                @endif
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
                                @if ($expand_all)
                                    {!! icon('fa-folder-open', $GLOBALS['shownone'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=0") !!}
                                @else
                                    {!! icon('fa-folder', $GLOBALS['showall'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=1") !!}
                                @endif
                                </th>
                                <th class='text-center' style='width:100px;'>{{ trans('langDate') }}</th>
                                @if (!$is_in_tinymce)
                                    <th class='text-center'>{!! icon('fa-gears') !!}</th>
                                @endif
                            </tr>
                        @foreach ($categories as $myrow)
                            <?php
                                $description = standard_text_escape($myrow->description);
                                if ((isset($_GET['d']) and $_GET['d'] == 1) or ( isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)) {
                                    $folder_icon = icon('fa-folder-open-o', $GLOBALS['shownone']);
                                } else {
                                    $folder_icon = icon('fa-folder-o', $GLOBALS['showall']);
                                }
                            ?>
                            <tr class='link-subcategory-title'><th class='category-link' colspan='{{ $colspan }}'>{!! $folder_icon !!}&nbsp;
                            @if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)
                                <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                            @else
                                <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;cat_id=" . $myrow->id . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                            @endif
                            @if (!empty($description))
                                <br><span class='link-description'>{{ $description }}</span>
                            @endif
                                </th>
                                @if ($display_tools)
                                    <td class='option-btn-cell'>
                                        {!!
                                        action_button(array(
                                            array('title' => $GLOBALS['langEditChange'],
                                                'icon' => 'fa-edit',
                                                'url' => $urlAppend . "modules/video/editCategory.php?course=" . $course_code . "&amp;id=" . $myrow->id),
                                            array('title' => $GLOBALS['langDelete'],
                                                'icon' => 'fa-times',
                                                'class' => 'delete',
                                                'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code . "&amp;id=" . $myrow->id . "&amp;delete=delcat",
                                                'confirm' => $GLOBALS['langCatDel'])))
                                        !!}
                                    </td>
                                @endif
                            </tr>
                            @if ($expand_all or (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id))
                                <?php
                                    $currentcatresults = getLinksOfCategory($myrow->id, $is_editor, $filterv, $order, $course_id, $filterl, $is_in_tinymce, $compatiblePlugin);
                                ?>
                                @include('modules.video.common.videoList', ['items' => $currentcatresults])
                            @endif
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