@extends('layouts.default')

@section('content')
        {!! $action_bar !!}
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='{{ $edit_url }}' method='post'>
                @if(isset($glossary_item))
                <input type='hidden' name='id' value='{{ getIndirectReference($glossary_item->id) }}'>                
                @endif
                <div class='form-group{{ Session::getError('term') ? " has-error" : "" }}'>
                     <label for='term' class='col-sm-2 control-label'>{{ trans('langGlossaryTerm') }}: </label>
                     <div class='col-sm-10'>
                         <input type='text' class='form-control' id='term' name='term' placeholder='{{ trans('langGlossaryTerm') }}' value='{{ $term }}'>
                         <span class='help-block'>{{ Session::getError('term') }}</span>
                     </div>
                </div>                
                <div class='form-group{{ Session::getError('definition') ? " has-error" : "" }}'>
                     <label for='term' class='col-sm-2 control-label'>{{ trans('langGlossaryDefinition') }}: </label>
                     <div class='col-sm-10'>
                         <textarea name="definition" rows="4" cols="60" class="form-control">{{ $definition }}</textarea>
                         <span class='help-block'>{{ Session::getError('definition') }}</span>    
                     </div>
                </div>
                <div class='form-group{{ Session::getError('url') ? " has-error" : "" }}'>
                     <label for='url' class='col-sm-2 control-label'>{{ trans('langGlossaryUrl') }}: </label>
                     <div class='col-sm-10'>
                         <input type='text' class='form-control' id='url' name='url' value='{{ $url }}'>
                         <span class='help-block'>{{ Session::getError('url') }}</span>     
                     </div>
                </div>                
                <div class='form-group'>
                     <label for='notes' class='col-sm-2 control-label'>{{ trans('langCategoryNotes') }}: </label>
                     <div class='col-sm-10'>
                         {!! $notes_rich !!}
                     </div>
                </div>
                {!! isset($category_selection) ? $category_selection : "" !!}
               <div class='form-group'>    
                   <div class='col-sm-10 col-sm-offset-2'>
                       {!! $form_buttons !!}
                   </div>
                </div>
            {!! generate_csrf_token_form_field() !!}                              
            </form>
        </div>                       
@endsection

