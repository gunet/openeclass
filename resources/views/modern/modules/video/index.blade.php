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


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                    @endif



                    {!! isset($action_bar)? $action_bar: '' !!}

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

                    @if ($count_video > 0 or $count_video_links > 0)
                        @if (count($items))
                        <div class='col-sm-12'>
                            <div class='table-responsive'>
                                <table class='table-default nocategory-links'>
                                    <tr class='list-header'>
                                        <th>{!! headlink($GLOBALS['langVideoDirectory'], 'title') !!}</th>
                                        <th>{!! headlink($GLOBALS['langDate'], 'date') !!}</th>
                                        @if (!$is_in_tinymce)
                                            <th class='text-center'>{!! icon('fa-cogs') !!}</th>
                                        @endif
                                    </tr>
                                    @include('modules.video.common.videoList')
                                </table>
                            </div>
                        </div>
                                
                        @endif

                        @if ($num_of_categories > 0)

                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <tr class='list-header'>
                                            <th>{{ trans('langCatVideoDirectory') }}&nbsp;&nbsp;&nbsp;
                                            @if ($expand_all)
                                                {!! icon('fa-folder-open text-white', $GLOBALS['langViewHide'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=0") !!}
                                            @else
                                                {!! icon('fa-folder text-white', $GLOBALS['langViewShow'], $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code . "&amp;d=1") !!}
                                            @endif
                                            </th>
                                            <th>{{ trans('langDate') }}</th>
                                            @if (!$is_in_tinymce)
                                                <th class='text-center'>{!! icon('fa-cogs') !!}</th>
                                            @endif
                                        </tr>
                                    @foreach ($categories as $myrow)
                                        <?php
                                            $description = standard_text_escape($myrow->description);
                                            if ((isset($_GET['d']) and $_GET['d'] == 1) or ( isset($_GET['cat_id']) and $_GET['cat_id'] == $myrow->id)) {
                                                $folder_icon = icon('fa-folder-open-o', $GLOBALS['langViewHide']);
                                            } else {
                                                $folder_icon = icon('fa-folder-o', $GLOBALS['langViewShow']);
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
                                                <td class='option-btn-cell text-center'>
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
                                
                        @endif
                    @else
                        <div class='col-sm-12'><div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div></div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
