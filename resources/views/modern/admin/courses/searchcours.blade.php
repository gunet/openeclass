@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                            
                            <form role='form' class='form-horizontal' action='listcours.php?search=yes' method='get'>
                                <fieldset>      
                                    <div class='form-group mt-3'>
                                        <label for='formsearchtitle' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' placeholder="{{ trans('langTitle') }}..." class='form-control' id='formsearchtitle' name='formsearchtitle' value=''>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='formsearchcode' class='col-sm-12 control-label-notes'>{{ trans('langCourseCode') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' placeholder="{{ trans('langCourseCode') }}..." class='form-control' name='formsearchcode' value=''>           
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='formsearchtype' class='col-sm-12 control-label-notes'>{{ trans('langCourseVis') }}</label>
                                        <div class='col-sm-12'>
                                            <select class='form-select' name='formsearchtype'>
                                                <option value='-1'>{{ trans('langAllTypes') }}</option>
                                                <option value='2'>{{ trans('langTypeOpen') }}</option>
                                                <option value='1'>{{ trans('langTypeRegistration') }}</option>
                                                <option value='0'>{{ trans('langTypeClosed') }}</option>
                                                <option value='3'>{{ trans('langCourseInactiveShort') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langCreationDate') }}</label>      
                                        <div class='row'>
                                            <div class='col-6'>
                                                {!! selection($reg_flag_data, 'reg_flag', '', 'class="form-control"') !!}
                                            </div>
                                            <div class='col-6'>
                                                <input class='form-control' id='id_date' name='date' type='text' value='' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langCreationDate') }}'>                    
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $html !!}
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12'>
                                            <div class='row'>
                                                <div class='col-6'>
                                                    <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='search_submit' value='{{ trans('langSearch') }}'> 
                                                </div>
                                                <div class='col-6'>
                                                   <a href='index.php' class='btn btn-sm btn-secondary cancelAdminBtn w-100'>{{ trans('langCancel') }}</a>   
                                                </div>
                                            </div>     
                                        </div>
                                    </div>                
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection