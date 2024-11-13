@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                <div class="row">

                    @if(!$is_in_tinymce)
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
                    @endif

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    @if ($count_video > 0 or $count_video_links > 0)
                        @if (count($items))
                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table class='table-default nocategory-links'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th>{!! headlink($GLOBALS['langVideoDirectory'], 'title') !!}</th>
                                                <th style='width:15%;'>{!! headlink($GLOBALS['langDate'], 'date') !!}</th>
                                                @if (!$is_in_tinymce)
                                                    <th style='width:10%;'></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        @include('modules.video.common.videoList')
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if ($num_of_categories > 0)

                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th>{{ trans('langCatVideoDirectory') }}
                                                    @if (isset($expand_all))
                                                        {!! icon('fa-folder-open', $GLOBALS['langViewHide'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=0") !!}
                                                    @else
                                                        {!! icon('fa-folder', $GLOBALS['langViewShow'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=1") !!}
                                                    @endif
                                                </th>
                                                <th aria-label="{{ trans('langSettingSelect') }}"></th>
                                                @if (!$is_in_tinymce)
                                                    <th aria-label="{{ trans('langSettingSelect') }}"></th>
                                                @endif
                                            </tr>
                                        </thead>
                                    @foreach ($categories as $myrow)
                                        <?php
                                            $description = standard_text_escape($myrow->description);
                                            if ((isset($_GET['d']) and $_GET['d'] == 1) or ( isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)) {
                                                $folder_icon = icon('fa-folder-open', $GLOBALS['langViewHide']);
                                            } else {
                                                $folder_icon = icon('fa-folder', $GLOBALS['langViewShow']);
                                            }
                                        ?>
                                        <tr class='link-subcategory-title'>
                                            <td class='category-link p-3' style='line-height: 16px;'>
                                                {!! $folder_icon !!}&nbsp;
                                                @if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)
                                                    <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                                                @else
                                                    <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;cat_id=" . $myrow->id . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                                                @endif
                                                @if (!empty($description))
                                                    <br><br><span class='link-description'>{{ $description }}</span>
                                                @endif
                                            </td>
                                            <td></td>
                                            @if ($display_tools)
                                                <td class='option-btn-cell text-end'>
                                                    {!!
                                                    action_button(array(
                                                        array('title' => $GLOBALS['langEditChange'],
                                                            'icon' => 'fa-edit',
                                                            'url' => $urlAppend . "modules/video/editCategory.php?course=" . $course_code . "&amp;id=" . $myrow->id),
                                                        array('title' => $GLOBALS['langDelete'],
                                                            'icon' => 'fa-xmark',
                                                            'class' => 'delete',
                                                            'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code . "&amp;id=" . $myrow->id . "&amp;delete=delcat",
                                                            'confirm' => $GLOBALS['langCatDel'])))
                                                    !!}
                                                </td>
                                            @else
                                                <td></td>
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
                        @endif
                    @else
                        <div class='col-sm-12'><div class='alert alert-warning' role='alert'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoVideo') }}</span></div></div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
