@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-warning'>
        {{ trans('langDisableModulesHelp') }}
    </div>
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='modules.php' method='post'>
        @foreach ($modules as $mid => $minfo)
        <div class='form-group'>
          <div class='col-xs-12 checkbox'>
            <label>
              <input type='checkbox' name='moduleDisable[{{ getIndirectReference($mid) }}]' value='1'{{ in_array($mid, $disabled)? ' checked': '' }}>
                 {!! icon($minfo['image']) !!} &nbsp;
                 {{ $minfo['title'] }}
            </label>
          </div>
        </div>
        @endforeach  
        {!! showSecondFactorChallenge() !!}
        <div class='form-group'>
          <div class='col-xs-12'>
            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmitChanges') }}'>
          </div>
        </div>
        {!! generate_csrf_token_form_field() !!}
      </form>
    </div>    
@endsection