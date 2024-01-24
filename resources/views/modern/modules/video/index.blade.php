<?php
    $display_tools = $is_editor && !$is_in_tinymce;
    $colspan = $display_tools ? 2 : 3;
    $embedParam = ((isset($_REQUEST['embedtype'])) ? '&amp;embedtype=' . urlencode($_REQUEST['embedtype']) : '') .
        ((isset($_REQUEST['docsfilter'])) ? '&amp;docsfilter=' . urlencode($_REQUEST['docsfilter']) : '');
    $expand_all = isset($_GET['d']) && $_GET['d'] == '1';

    if ($display_tools) {
        $actionBarArray = array(
            array('title' => $GLOBALS['langAddV'],
                  'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=file",
                  'icon' => 'fa-regular fa-file-video',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $GLOBALS['langAddVideoLink'],
                  'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=url",
                  'icon' => 'fa-link',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $GLOBALS['langCategoryAdd'],
                  'url' => $urlAppend . "modules/video/editCategory.php?course=" . $course_code,
                  'icon' => 'fa-plus-circle'),
            array('title' => $GLOBALS['langQuotaBar'],
                  'url' => $urlAppend . "modules/video/index.php?course=" . $course_code . "&amp;showQuota=true",
                  'icon' => 'fa-pie-chart')
        );
        if (isDelosEnabled()) {
            $actionBarArray[] = array('title' => $GLOBALS['langAddOpenDelosVideoLink'],
                'url' => $urlAppend . "modules/video/edit.php?course=" . $course_code . "&amp;form_input=opendelos",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success');
        }
        $action_bar = action_bar($actionBarArray);
    }
?>

@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

                <div class="row">

                    @if(!$is_in_tinymce)

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.legend_view')
                    @endif



                    {!! isset($action_bar)? $action_bar: '' !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

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
                                                <th>{{ trans('langCatVideoDirectory') }}&nbsp;&nbsp;&nbsp;
                                                @if ($expand_all)
                                                    {!! icon('fa-folder-open', $GLOBALS['langViewHide'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=0") !!}
                                                @else
                                                    {!! icon('fa-folder', $GLOBALS['langViewShow'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=1") !!}
                                                @endif
                                                </th>
                                                <th></th>
                                                @if (!$is_in_tinymce)
                                                    <th></th>
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
                                            <th class='category-link p-3' colspan='{{ $colspan }}'>
                                                {!! $folder_icon !!}&nbsp;
                                                @if (isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)
                                                    <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                                                @else
                                                    <a href='{!! $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;cat_id=" . $myrow->id . $embedParam !!}' class='open-category'>{{ $myrow->name }}</a>
                                                @endif
                                                @if (!empty($description))
                                                    <br><br><span class='link-description'>{{ $description }}</span>
                                                @endif
                                            </th>
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
