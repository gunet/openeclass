@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active col_maincontent_active_module">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if ($count_video > 0 or $count_video_links > 0)
                        @if (count($items))

                                <div class='col-sm-12'>
                                    <div class='table-responsive'>
                                        <table class='table-default nocategory-links'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>{{ trans('langVideoDirectory') }}</th>
                                                    <th>{{ trans('langDate') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @include('modules.video.common.videoList')
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                        @endif

                        @if ($num_of_categories > 0)

                                <div class='col-sm-12'>
                                    <div class='table-responsive'>
                                        <table class='table-default category-links'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>{{ trans('langCatVideoDirectory') }}&nbsp;&nbsp;&nbsp;
                                                        {!! icon('fa-folder-open', $GLOBALS['langViewShow']) !!}
                                                    </th>
                                                    <th>{{ trans('langDate') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                        @foreach ($categories as $myrow)
                                            <?php
                                                $description = standard_text_escape($myrow->description);
                                                $folder_icon = icon('fa-folder-open', $GLOBALS['langViewShow']);
                                            ?>
                                            <tr class='link-subcategory-title'><th class='category-link px-2' colspan='2'>{!! $folder_icon !!}&nbsp;{{ $myrow->name }}
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
                                            </tbody>
                                        </table>
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
