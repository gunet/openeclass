@extends('layouts.default')

@section('content')
        {!! $action_bar !!}
        <div class='form-wrapper'><form class='form-horizontal' role='form' action='{{ $cat_url }}' method='post'>
                    @if(isset($glossary_cat))
                    <input type='hidden' name='category_id' value='{{ getIndirectReference($glossary_cat->id) }}'>
                    @endif
                    <div class='form-group{{ Session::getError('name') ? " has-error" : "" }}'>
                         <label for='name' class='col-sm-2 control-label'>{{ trans('langCategoryName') }}: </label>
                         <div class='col-sm-10'>
                             <input type='text' class='form-control' id='term' name='name' placeholder='{{ trans('langCategoryName') }}' value='{{ $name }}'>
                             <span class='help-block'>{{ Session::getError('name') }}</span>    
                         </div>
                    </div>
                    <div class='form-group'>
                         <label for='description' class='col-sm-2 control-label'>{{ trans('langDescription') }}</label>
                         <div class='col-sm-10'>
                             {!! $description_rich !!}
                         </div>
                    </div>
                   <div class='form-group'>    
                       <div class='col-sm-10 col-sm-offset-2'>
                           {!! $form_buttons !!}
                       </div>
                    </div>
                {!! generate_csrf_token_form_field() !!}                              
                </form>
        </div>                       
@endsection

