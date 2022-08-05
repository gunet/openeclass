<?php 

?>
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
                           
                            <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                                <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                                    <i class="fas fa-align-left"></i>
                                    <span></span>
                                </button>
                                
                            
                                <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                                    <i class="fas fa-tools"></i>
                                </a>
                            </nav>

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



                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 bg-white">{!! isset($action_bar) ?  $action_bar : '' !!}</div>
                            <div class="row p-2"></div>

                            @if(Session::has('message'))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    @if(is_array(Session::get('message')))
                                        @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                        @foreach($messageArray as $message)
                                            {!! $message !!}
                                        @endforeach
                                    @else
                                        {!! Session::get('message') !!}
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </p>
                            </div>
                            @endif

                            @if (count($categories))
                                <div class='table-responsive glossary-categories'>    
                                    <table class='table' id="glossary_table" style="overflow: inherit">
                                        <thead class="notes_thead text-light">
                                            
                                            <tr>
                                                <th scope="col"><span class="notes_th_comment">#</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{ trans('langName') }}</span></th>
                                                <th scope="col"><span class="notes_th_comment">Περιγραφή</span></th>
                                                @if($is_editor)
                                                
                                                        <th class='text-center' scope="col"><span class="notes_th_comment"><i class='fas fa-cogs'></i></span></th>
                                                    
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=0; ?>
                                            
                                            @foreach ($categories as $category)
                                                <?php $i++; ?>
                                                <?php $tmp_edit_id = Database::get()->querySingle("SELECT * FROM `glossary_category` WHERE `glossary_category`.`name`='{$category->name}' ");?>
                                                <?php $edit_id = $tmp_edit_id->id; ?>

                                            <tr>
                                                
                                                <th scope="row">{{$i}}</th>
                                                <td>
                                                    <a href='{{ $base_url }}&amp;cat={{ getIndirectReference($category->id) }}'>
                                                        <strong> {{ $category->name }}</strong>
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! $category->description !!}
                                                </td>
                                                <td class='text-center'>
                                                    @if($is_editor)
                                                      
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
                                                      
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else 
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><div class='alert alert-warning'>{{trans('langAnalyticsNotAvailable')}} {{trans('langGlossary')}}.</div></div>
                            @endif
                                   
                            
                        </div>
                   
                </div>
            </div>
        </div>
    </div>
@endsection

