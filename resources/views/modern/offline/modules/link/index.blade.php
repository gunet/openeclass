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
                    <div class='row'>
                        <div class='col-sm-12'>
                            <div class='table-responsive'>
                                <table class='table-default nocategory-links'>
                                    @if ($numberofzerocategory !== 0)
                                        <tr class='list-header'>
                                            <th class='text-left'>{{ trans('langNoCategory') }}</th>
                                        </tr>
                                        @include('modules.link.common.linkList', ['category' => $result_zero_category])
                                    @else
                                        <tr class='list-header'>
                                            <th class='text-left list-header'> {{ trans('langNoCategory') }}</th>
                                        </tr>
                                        <tr>
                                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($social_bookmarks_enabled)
                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table class='table-default nocategory-links'>
                                        @if ($numberofsocialcategory !== 0)
                                            <tr class='list-header'>
                                                <th class='text-left'>{{ trans('langSocialCategory') }}</th>
                                            </tr>
                                            @include('modules.link.common.linkList', ['category' => $result_social_category])
                                        @else
                                            <tr class='list-header'>
                                                <th class='text-left list-header'>{{ trans('langSocialCategory') }}</th>
                                            </tr>
                                            <tr>
                                                <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class='row'>
                        <div class='col-sm-12'>
                            <div class='table-responsive'>
                                <table class='table-default category-links'>
                                    @if ($aantalcategories > 0)
                                        <tr class='list-header'><th>{{ trans('langCategorisedLinks') }} </th></tr>
                                    @else
                                        <tr>
                                            <th>{{ trans('langCategorisedLinks') }} </th>
                                        </tr>
                                        <tr>
                                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                                        <tr>
                                    @endif

                                    @foreach ($resultcategories as $data)
                                        <tr class='link-subcategory-title'>
                                            <th class = 'text-left category-link'>
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
</div>

@endsection