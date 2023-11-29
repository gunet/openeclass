@extends('layouts.default')

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



                            <div class="col-12 bg-transparent">{!! isset($action_bar) ?  $action_bar : '' !!}</div>

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

                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif

                            @if (count($categories))
                            <div class='col-12'>
                                <div class='table-responsive'>
                                    <table class='table-default' id="glossary_table">
                                         <thead>
                                            <tr class='list-header'>
                                            <th>{{ trans('langName') }}</th>
                                            <th>{{ trans('langDescription')}}</th>
                                            @if($is_editor)
                                                <th></th>
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

