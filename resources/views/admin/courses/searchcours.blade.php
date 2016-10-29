@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
    <form role='form' class='form-horizontal' action='listcours.php?search=yes' method='get'>
    <fieldset>      
        <div class='form-group'>
            <label for='formsearchtitle' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' id='formsearchtitle' name='formsearchtitle' value=''>
            </div>
        </div>
        <div class='form-group'>
            <label for='formsearchcode' class='col-sm-2 control-label'>{{ trans('langCourseCode') }}:</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' name='formsearchcode' value=''>           
            </div>
        </div>
        <div class='form-group'>
            <label for='formsearchtype' class='col-sm-2 control-label'>{{ trans('langCourseVis') }}:</label>
            <div class='col-sm-10'>
                <select class='form-control' name='formsearchtype'>
                    <option value='-1'>{{ trans('langAllTypes') }}</option>
                    <option value='2'>{{ trans('langTypeOpen') }}</option>
                    <option value='1'>{{ trans('langTypeRegistration') }}</option>
                    <option value='0'>{{ trans('langTypeClosed') }}</option>
                    <option value='3'>{{ trans('langCourseInactiveShort') }}</option>
                </select>
            </div>
        </div>
        
        <div class='form-group'>
            <label class='col-sm-2 control-label'>{{ trans('langCreationDate') }}:</label>      
            <div class='col-sm-5'>
                {!! selection($reg_flag_data, 'reg_flag', '', 'class="form-control"') !!}
            </div>
            <div class='col-sm-5'>
                <input class='form-control' id='id_date' name='date' type='text' value='' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langCreationDate') }}'>                    
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
            <div class='col-sm-10'>
                {!! $html !!}
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='search_submit' value='{{ trans('langSearch') }}'>
                <a href='index.php' class='btn btn-default'>{{ trans('langCancel') }}</a>        
            </div>
        </div>                
    </fieldset>
    </form>
    </div>
@endsection