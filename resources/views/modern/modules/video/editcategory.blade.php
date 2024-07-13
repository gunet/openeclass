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


<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

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

                    @include('layouts.partials.show_alert') 

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded py-0'>

                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'>
                                    @if (isset($_GET['id']))
                                    <input type='hidden' name='id' value='{{ $_GET["id"] }}' />
                                    @endif
                                    <fieldset>

                                        <div class='form-group{{ Session::getError("categoryname") ? " has-error" : "" }}'>
                                            <label for='CatName' class='col-sm-6 control-label-notes'>{{ trans('langCategoryName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='categoryname' size='53'{!! $form_name !!} />
                                                <span class='help-block Accent-200-cl'>{{ Session::getError('categoryname') }}</span>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='CatDesc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-sm-12'><textarea class='form-control' rows='5' name='description'>{{ $form_description }}</textarea></div>
                                        </div>



                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end align-items-center'>


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
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                        </div>


                    </div>
                </div>


        </div>

</div>
</div>
@endsection
