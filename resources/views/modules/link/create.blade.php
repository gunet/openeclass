@extends('layouts.default')

@section('content')
        {!! isset($action_bar) ?  $action_bar : '' !!}
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>
                @if ($action == 'editlink')
                    <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
                @endif
                <fieldset>
                    <div class='form-group{{ $urlLinkError ? " has-error" : "" }}'>
                        <label for='urllink' class='col-sm-2 control-label'>URL:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' id='urllink' name='urllink' value="{{ isset($link) ? $link->url : "" }}">
                                {!! Session::getError('urllink', "<span class='help-block'>:message</span>") !!}
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='title' class='col-sm-2 control-label'>{{ trans('langLinkName') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' id='title' name='title' value="{{ isset($link) ? $link->title : "" }}">
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='description' class='col-sm-2 control-label'>{{ trans('langInfoabout') }}:</label>
                            <div class='col-sm-10'>{!! $description_textarea !!}</div>
                        </div>
                        <div class='form-group'>
                            <label for='selectcategory' class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                            <div class='col-sm-3'>
                                <select class='form-control' name='selectcategory' id='selectcategory'>
                                    @if ($is_editor)
                                        <option value='{{ getIndirectReference(0) }}'>--</option>
                                    @endif
                                    @if ($social_bookmarks_enabled)
                                        <option value='{{ getIndirectReference(-2) }}'{{ isset($category) && $category == -2 ? " selected": "" }}>{{ trans('langSocialCategory') }}</option>
                                    @endif
                                    @if ($is_editor)
                                        @foreach ($categories as $row)
                                            <option value='{{ getIndirectReference($row->id) }}'{{ isset($category) && $category == $row->id ? " selected": "" }}>{{ $row->name }}</option>
                                        @endforeach
                                    @endif
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

