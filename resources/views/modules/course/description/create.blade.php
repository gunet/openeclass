@extends('layouts.default')

@section('content')
        {!! $action_bar !!}
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='index.php?course={{ $course_code }}' method='post'>
                @if ($editId)
                <input type='hidden' name='editId' value='{{ getIndirectReference($editId) }}'>";
                @endif              
                <div class='form-group'>
                     <label for='editType' class='col-sm-2 control-label'>{{ trans('langType') }}: </label>
                     <div class='col-sm-10'>
                         {!! selection($types, 'editType', $defaultType, 'class="form-control" id="typSel"') !!}
                     </div>
                </div>
                <div class='form-group{{ $titleError ? " form-error" : ""}}'>
                    <label for='titleSel' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                    <div class='col-sm-10'>
                        <input type='text' name='editTitle' class='form-control' value='{{ $cdtitle }}' size='40' id='titleSel'>
                        {!! Session::getError('editTitle', "<span class='help-block'>:message</span>") !!}                                    
                    </div>
                </div>                
                <div class='form-group'>
                    <label for='editComments' class='col-sm-2 control-label'>{{ trans('langContent') }}:</label>
                    <div class='col-sm-10'>
                     {!! $text_area_comments !!}
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

