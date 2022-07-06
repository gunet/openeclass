<?php
    if (isset($_GET['id'])) {
        if ($currentcat) {
            $form_name = ' value="' . q($currentcat->name) . '"';
            $form_description = standard_text_escape($currentcat->description);
        } else {
            $form_name = $form_description = '';
        }
        $form_legend = $GLOBALS['langCategoryMod'];
    } else {
        $form_name = $form_description = '';
        $form_legend = $GLOBALS['langCategoryAdd'];
    }
?>

@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
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




                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif
                        
                        {!!
                        action_bar(array(
                            array('title' => $GLOBALS['langBack'],
                                'url' => $backPath,
                                'icon' => 'fa-reply',
                                'level' => 'primary-label')
                            )
                        )
                        !!}
                        <div class='col-12'>
                            <div class='form-wrapper shadow-sm p-3 mt-5 rounded'> 
                                
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'>
                                    @if (isset($_GET['id']))
                                    <input type='hidden' name='id' value='{{ $_GET["id"] }}' />
                                    @endif
                                    <fieldset>
                                        <div class='row p-2'></div>
                                        <div class='form-group{{ Session::getError("categoryname") ? " has-error" : "" }}'>
                                            <label for='CatName' class='col-sm-6 control-label-notes'>{{ trans('langCategoryName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='categoryname' size='53'{!! $form_name !!} />
                                                <span class='help-block'>{{ Session::getError('categoryname') }}</span>
                                            </div>
                                        </div>

                                        <div class='row p-2'></div>

                                        <div class='form-group'>
                                            <label for='CatDesc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-sm-12'><textarea class='form-control' rows='5' name='description'>{{ $form_description }}</textarea></div>
                                        </div>

                                        <div class='row p-2'></div>
                                        
                                        <div class='form-group'>
                                            <div class='col-sm-offset-2 col-sm-10'>
                                                {!!
                                                form_buttons(array(
                                                    array(
                                                        'text'  =>  $GLOBALS['langSave'],
                                                        'name'  =>  'submitCategory',
                                                        'value' =>  $form_legend
                                                    ),
                                                    array(
                                                        'href'  =>  $backPath
                                                    )
                                                ))
                                                !!}
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        

                    </div>
                </div>


        </div>
    </div>
</div>
@endsection