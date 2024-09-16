@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">

                        <div class="row">

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

                            <div class="col-12 bg-transparent">{!! isset($action_bar) ?  $action_bar : '' !!}</div>

                            @include('layouts.partials.show_alert') 

                            @if (count($categories))
                            <div class='col-12'>
                                <div class='table-responsive'>
                                    <table class='table-default' id="glossary_table">
                                         <thead>
                                            <tr class='list-header'>
                                            <th>{{ trans('langName') }}</th>
                                            <th>{{ trans('langDescription')}}</th>
                                            @if($is_editor)
                                                <th aria-label="{{ trans('langSettingSelect') }}"></th>
                                            @endif
                                        </tr></thead>
                                        <tbody>
                                            @foreach ($categories as $category)
                                            <tr>
                                                <td>
                                                    <a href='{{ $base_url }}&amp;cat={{ $category->id }}'>
                                                         {{ $category->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! $category->description !!}
                                                </td>
                                                @if($is_editor)
                                                <td class='text-end'>
                                                    {!! action_button(array(
                                                        array('title' => trans('langCategoryMod'),
                                                            'url' => "$cat_url&amp;edit=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-edit'),
                                                        array('title' => trans('langCategoryDel'),
                                                            'url' => "$cat_url&amp;delete=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-xmark',
                                                            'class' => 'delete',
                                                            'confirm' => trans('langConfirmDelete')
                                                            )
                                                        )
                                                    ) !!}
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                                <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{trans('langAnalyticsNotAvailable')}} {{trans('langGlossary')}}.</span></div></div>
                            @endif


                        </div>

                </div>
            </div>

    </div>
    </div>
@endsection

