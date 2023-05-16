
@extends('layouts.default')

@section('content')

<script>
    $(function () {
        var langEmptyGroupName = '{{ js_escape(trans('langEmptyAnTitle')) }}';

        //$('input[name=startdate_active]').prop('checked') ? $('input[name=startdate_active]').parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', true);
        //$('input[name=enddate_active]').prop('checked') ? $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', false) : $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', true);

        if($('input[name=startdate_active]').prop('checked')){
            $('input[name=startdate]').attr('disabled', false);
        }else{
            $('input[name=startdate]').attr('disabled', true);
        }

        if($('input[name=enddate_active]').prop('checked')){
            $('input[name=enddate]').attr('disabled', false);
        }else{
            $('input[name=enddate]').attr('disabled', true);
        }

        $('input[name=startdate_active]').on('click', function() {
            if ($('input[name=startdate_active]').prop('checked')) {
                $('input[name=enddate_active]').prop('disabled', false);
            } else {
                $('input[name=enddate_active]').prop('disabled', true);
                $('input[name=enddate_active]').prop('checked', false);
                $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', true);
            }
        });

        $('.input-group-addon input[type=checkbox]').on('click', function(){
            var prop = $(this).parents('.input-group').children('input').prop('disabled');
            if(prop){
                $(this).parents('.input-group').children('input').prop('disabled', false);
            } else {
                $(this).parents('.input-group').children('input').prop('disabled', true);
            }
        });

        $('#select-recipients').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-recipients').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-recipients').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-recipients').val(stringVal).trigger('change');
        });

        $('#startdate, #enddate').datetimepicker({
            format: 'dd-mm-yyyy hh:ii',
            pickerPosition: 'bottom-right',
            language: '{{ $language }}',
            autoclose: true
        });

    });
</script>


    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                   
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                                <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                                    <div class="offcanvas-header">
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                    </div>
                                </div>
                                
                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                                @if($is_editor)
                                    {!! isset($action_bar) ?  $action_bar : '' !!}
                                @endif
                        
                                <div class='col-12'>
                                    <div class='form-wrapper form-edit rounded'>    
                                            <form class="form-horizontal" role="form" method="post" action="{{$urlAppend}}modules/announcements/submit.php?course={{$course_code}}">
                                                    
                                                    <div class="row mt-4 form-group {{ $antitle_error }}">
                                                        <label for="AnnTitle" class="col-md-3 col-12 control-label-notes">{{ trans('langAnnTitle') }}:</label>
                                                        <div class="col-md-9 col-12 ">
                                                            <input class="form-control" placeholder="{{ trans('langAnnTitle') }}..." type="text" name="antitle" value="{{ $titleToModify }}"/>
                                                            <span class='help-block'>{{ Session::getError('antitle') }}</span>
                                                        </div>
                                                    </div>

                                        

                                                    <div class='row mt-4 form-groum'>
                                                        <label for='AnnBody' class='col-md-3 col-12 control-label-notes'>{{ trans('langAnnBody') }}:</label>
                                                        <div class='col-md-9 col-12'>{!! $contentToModify !!}</div>
                                                    </div>

                                                  

                                                    
                                                       
                                                           
                                                    <div class='row mt-4 form-group'>
                                                        <label for='Email' class='col-md-3 col-12 control-label-notes'>{{ trans('langEmailOption') }}:</label>
                                                        <div class='col-md-9 col-12'>
                                                            <select class='form-select' name='recipients[]' multiple='multiple' id='select-recipients'>
                                                                <option value='-1' selected>{{ trans('langAllUsers') }}</option>
                                                                @foreach ($course_users as $cu)
                                                                    <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                                                @endforeach
                                                            </select>
                                                            <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                                        </div>
                                                    </div>
                                                
                                                
                                                    
                                                    {!! $tags !!}
                                                       
                                                    
                                                    
                                                   
                                                       
                                                    <div class='row mt-4 form-group {{ $startdate_error }}'>
                                                        <label for='startdate' class='col-md-3 col-12 control-label-notes'>{{ trans('langStartDate') }} :</label>
                                                        <div class='col-md-9 col-12'>
                                                            <div class='input-group'> 
                                                                <span class='input-group-addon'>
                                                                    <input type='checkbox' name='startdate_active' {{ $start_checkbox }}>
                                                                </span>
                                                                <input class='form-control' name='startdate' id='startdate' type='text' value = '{{ $showFrom }}'>
                                                            </div>
                                                            <span class='help-block'>{{ $startdate_error }}</span>
                                                        </div>
                                                    </div>
                                                
                                                
                                                    <div class='row mt-4 form-group {{ $enddate_error }}'>
                                                        <label for='enddate' class='col-md-3 col-12 control-label-notes'>{{ trans('langEndDate') }} :</label>
                                                        <div class='col-md-9 col-12'>
                                                            <div class='input-group'>
                                                                <span class='input-group-addon'>
                                                                    <input type='checkbox' name='enddate_active' {{ $end_checkbox}} {{ $end_disabled}}>
                                                                </span>
                                                                <input class='form-control' name='enddate' id='enddate' type='text' value = '{{ $showUntil }}'>
                                                            </div>
                                                            <span class='help-block'>{{ $enddate_error }}</span>
                                                        </div>
                                                    </div>
                                                       
                                                  

                                                  

                                                    <div class='row mt-4 form-group'>
                                                        <div class='col-md-9 offset-md-3'>
                                                            <div class='checkbox'>
                                                                <label>
                                                                    <input type='checkbox' name='show_public' {{  $checked_public }}> {{ trans('langViewShow') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                          

                                                    <div class='row mt-4 form-group'>
                                                        <input type='hidden' name='id' value='{{ $announce_id }}'>
                                                        <input type='hidden' name='course' value='{{ $course_code }}'>
                                                        <input type='hidden' name='editorFromCreateEditAnnouncement' value='{{$is_editor}}'>
                                                        <div class='col-md-9 offset-md-3'>
                                                            <div class='col-12 d-flex justify-content-center align-items-center'>
                                                                <button type="submit" class="btn submitAdminBtn" name="submitAnnouncement" value="{{ trans('langAdd') }}">{{ trans('langSubmit') }}</button>
                                                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" class="btn cancelAdminBtn ms-1">{{ trans('langCancel') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                
                                            </form>
                                        </div>
                                    </div>
                            
                        
                    </div> 
                    
                </div>
            </div>
        </div>
    </div>

@endsection


    
