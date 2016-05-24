@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}'>
        <fieldset>                               
            <div class='form-group'>
                <label class='col-sm-3'>{{ trans('langSocialBookmarksFunct') }}</label>
                <div class='col-sm-9'> 
                    <div class='radio'>
                        <label>
                            <input type='radio' value='1' name='settings_radio'{{ $social_enabled ? " checked" : "" }}>{{ trans('langActivate') }}
                        </label>
                    </div>
                    <div class='radio'>
                        <label>
                            <input type='radio' value='0' name='settings_radio' {{ $social_enabled ? "" : " checked" }}>{{ trans('langDeactivate') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-9 col-sm-offset-3'>
                    <input type='submit' class='btn btn-primary' name='submitSettings' value='{{ trans('langSubmit') }}' />
                    <a href='index.php?course={{ $course_code }}' class='btn btn-default'>{{ trans('langCancel') }}</a>
                </div>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection