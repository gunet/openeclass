@extends('layouts.default')

@section('content')

    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                
                        <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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



                            <div class="col-12 bg-white">{!! isset($action_bar) ?  $action_bar : '' !!}</div>

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

                            @if (count($categories))
                            <div class='col-12'>
                                <div class='table-responsive'>    
                                    <table class='table-default' id="glossary_table">
                                            
                                        <tr class='list-header'>
                                            <th>{{ trans('langName') }}</th>
                                            <th>Περιγραφή</th>
                                            @if($is_editor)
                                                <th class='text-center' scope="col"><span class="notes_th_comment"><i class='fas fa-cogs'></i></span></th>
                                            @endif
                                        </tr>
                                        <tbody>
                                            @foreach ($categories as $category)
                                            <tr>
                                                <td>
                                                    <a href='{{ $base_url }}&amp;cat={{ getIndirectReference($category->id) }}'>
                                                        <strong> {{ $category->name }}</strong>
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! $category->description !!}
                                                </td>
                                                @if($is_editor)
                                                <td class='text-center'>
                                                    {!! action_button(array(
                                                        array('title' => trans('langCategoryMod'),
                                                            'url' => "$cat_url&amp;edit=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-edit'),
                                                        array('title' => trans('langCategoryDel'),
                                                            'url' => "$cat_url&amp;delete=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-times',
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
                                <div class='col-12'><div class='alert alert-warning'>{{trans('langAnalyticsNotAvailable')}} {{trans('langGlossary')}}.</div></div>
                            @endif
                                   
                            
                        </div>
                   
                </div>
            </div>
        </div>
    </div>
@endsection

