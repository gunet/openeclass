@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

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
                    
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default nocategory-links'>
                                    @if ($numberofzerocategory !== 0)
                                        <thead>
                                            <tr class='list-header'>
                                                <th class='text-start'>{{ trans('langNoCategory') }}</th>
                                            </tr>
                                        </thead>
                                        @include('modules.link.common.linkList', ['category' => $result_zero_category])
                                    @else
                                        <thead>
                                            <tr class='list-header'>
                                                <th class='text-start list-header'> {{ trans('langNoCategory') }}</th>
                                            </tr>
                                        </thead>
                                        <tr>
                                            <td class='text-start not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                   

                    @if ($social_bookmarks_enabled)
                       
                            <div class='col-12 mt-4'>
                                <div class='table-responsive'>
                                    <table class='table-default nocategory-links'>
                                        @if ($numberofsocialcategory !== 0)
                                            <thead>
                                                <tr class='list-header'>
                                                    <th class='text-start'>{{ trans('langSocialCategory') }}</th>
                                                </tr>
                                            </thead>
                                            @include('modules.link.common.linkList', ['category' => $result_social_category])
                                        @else
                                            <thead>
                                                <tr class='list-header'>
                                                    <th class='text-start list-header'>{{ trans('langSocialCategory') }}</th>
                                                </tr>
                                            </thead>
                                            <tr>
                                                <td class='text-start not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                       
                    @endif

                    
                        <div class='col-12 mt-4'>
                            <div class='table-responsive'>
                                <table class='table-default category-links'>
                                    @if ($aantalcategories > 0)
                                        <thead><tr class='list-header'><th>{{ trans('langCategorisedLinks') }} </th></tr></thead>
                                    @else
                                        <thead>
                                            <tr class='list-header'>
                                                <th>{{ trans('langCategorisedLinks') }} </th>
                                            </tr>
                                        </thead>
                                        <tr>
                                            <td class='text-start not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                                        <tr>
                                    @endif

                                    @foreach ($resultcategories as $data)
                                        <tr class='link-subcategory-title'>
                                            <th class = 'text-start category-link'>
                                                <span class='fa fa-folder-open'></span>
                                                {{ $data->name }}
                                                @if (!empty($data->description))
                                                    <br><span class='link-description'> {!! standard_text_escape($data->description) !!} </span>
                                                @endif
                                            </th>
                                        </tr>
                                        @include('modules.link.common.linkList', ['category' => $result_link_category[$data->id]])
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    
                </div>
            </div>
        </div>
   
</div>
</div>

@endsection