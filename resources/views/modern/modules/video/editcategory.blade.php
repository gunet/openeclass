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
                            <div class='form-wrapper form-edit rounded'> 
                                
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'>
                                    @if (isset($_GET['id']))
                                    <input type='hidden' name='id' value='{{ $_GET["id"] }}' />
                                    @endif
                                    <fieldset>
                                        
                                        <div class='form-group{{ Session::getError("categoryname") ? " has-error" : "" }}'>
                                            <label for='CatName' class='col-sm-6 control-label-notes'>{{ trans('langCategoryName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='categoryname' size='53'{!! $form_name !!} />
                                                <span class='help-block'>{{ Session::getError('categoryname') }}</span>
                                            </div>
                                        </div>

                                  

                                        <div class='form-group mt-4'>
                                            <label for='CatDesc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-sm-12'><textarea class='form-control' rows='5' name='description'>{{ $form_description }}</textarea></div>
                                        </div>

                                        
                                        
                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-center align-items-center'>
                                              
                                                 
                                                  {!!
                                                    form_buttons(array(
                                                        array(
                                                            'class' => ' submitAdminBtn',
                                                            'text'  =>  $GLOBALS['langSave'],
                                                            'name'  =>  'submitCategory',
                                                            'value' =>  $form_legend
                                                        )
                                                    ))
                                                    !!}
                                               
                                               
                                                  {!!
                                                form_buttons(array(
                                                    array(
                                                        'class' => 'cancelAdminBtn ms-1',
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