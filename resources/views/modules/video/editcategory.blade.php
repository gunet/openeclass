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
    {!!
    action_bar(array(
        array('title' => $GLOBALS['langBack'],
              'url' => $backPath,
              'icon' => 'fa-reply',
              'level' => 'primary-label')
        )
    )
    !!}
    
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'>
                    @if (isset($_GET['id']))
                    <input type='hidden' name='id' value='{{ $_GET["id"] }}' />
                    @endif
                    <fieldset>
                        <div class='form-group{{ Session::getError("categoryname") ? " has-error" : "" }}'>
                            <label for='CatName' class='col-sm-2 control-label'>{{ trans('langCategoryName') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='categoryname' size='53'{!! $form_name !!} />
                                <span class='help-block'>{{ Session::getError('categoryname') }}</span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='CatDesc' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                            <div class='col-sm-10'><textarea class='form-control' rows='5' name='description'>{{ $form_description }}</textarea></div>
                        </div>
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
@endsection