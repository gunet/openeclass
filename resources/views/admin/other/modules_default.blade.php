@extends('layouts.default')

@section('content')
    @isset($action_bar)
        {!! $action_bar !!}
    @endisset
    <div class='alert alert-warning'>
        {{ trans('langDefaultModulesHelp') }}
    </div>
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='modules_default.php' method='post'>
        @foreach ($modules as $mid => $minfo)
           <div class='form-group'>
             <div class='col-xs-12 checkbox'>
                 <label @if (in_array($mid, $disabled)) class='not_visible' @endif>
                 <input type='checkbox' name='module[{{ getIndirectReference($mid) }}]' value='1'
                    @if (in_array($mid, $default)) checked @endif
                    @if (in_array($mid, $disabled)) disabled @endif>
                 {!! icon($minfo['image']) !!} &nbsp; {{ $minfo['title'] }}
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
