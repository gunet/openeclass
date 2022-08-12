@extends('layouts.default')

@section('content')
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">
    <div class="container-fluid main-container">
        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar or '' !!}

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
                </div>
            </div>
        </div>
    </div>
</div>
    
@endsection
