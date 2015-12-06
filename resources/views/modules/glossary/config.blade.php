@extends('layouts.default')

@section('content')
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='{{ $base_url }}' method='post'>
            <div class='form-group'>
                <div class='col-sm-12'>            
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='index' value='yes'{{ $checked_index }}>{{ trans('langGlossaryIndex') }}                               
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-12'>            
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='expand' value='yes'{{ $checked_expand }}>{{ trans('langGlossaryExpand') }}                               
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-12'>{!! $form_buttons !!}</div>
            </div>   
            {!! generate_csrf_token_form_field() !!}                
        </form>
    </div>
@endsection

