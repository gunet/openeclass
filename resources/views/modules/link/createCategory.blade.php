@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class = 'form-wrapper'>
        <form class = 'form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}&urlview={{ $urlview }}'>
            @if ($action == 'editcategory')
                <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
            @endif
            <fieldset>
                <div class='form-group{{ $categoryNameError ? ' has-error' : ''}}'>
                   <label for='CatName' class='col-sm-2 control-label'>{{ trans('langCategoryName') }}:</label>
                   <div class='col-sm-10'>
                       <input class='form-control' type='text' name='categoryname' size='53' placeholder='{{ trans('langCategoryName') }}' value='{{ isset($category) ? $category->name : "" }}'>
                       {!! Session::getError('categoryname', "<span class='help-block'>:message</span>") !!}
                   </div>
                </div>
                <div class='form-group'>
                    <label for='CatDesc' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' rows='5' name='description'>{{ isset($category) ? $category->description : "" }}</textarea>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input type='submit' class='btn btn-primary' name='submitCategory' value='{{ $form_legend }}' />
                        <a href='index.php?course={{ $course_code }}' class='btn btn-default'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection