@extends('layouts.default')

@section('content')
        {!! isset($action_bar) ?  $action_bar : '' !!}
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>
                @if ($action == 'editlink')
                    <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
                @endif
                <fieldset>
                    <div class='form-group'>
                        <label for='urllink' class='col-sm-2 control-label'>URL:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' id='urllink' name='urllink' {{ $form_url }} >
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='title' class='col-sm-2 control-label'>{{ trans('langLinkName') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' id='title' name='title'{{ $form_title }} >
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='description' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                            <div class='col-sm-10'>{!! $description_textarea !!}</div>
                        </div>
                        <div class='form-group'>
                            <label for='selectcategory' class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                            <div class='col-sm-3'>
                                <select class='form-control' name='selectcategory' id='selectcategory'>
                                    <option value='-2'>{{ trans('langSocialCategory') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <input type='submit' class='btn btn-primary' name='submitLink' value='{{ $submit_label }}' />
                                <a href='index.php?course={{ $course_code }}' class='btn btn-default'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>                    
@endsection

