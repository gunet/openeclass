@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active p-lg-5">

                <div class="row">

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
                                                $folder_icon = icon('fa-folder-open', $GLOBALS['langViewShow']);
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
                        <div class='alert alert-warning' role='alert'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoVideo') }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    
</div>
</div>
    
@endsection
