
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

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col_maincontent_active">

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.legend_view')

                        <div class='d-lg-flex gap-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class="form-horizontal" role="form" method="post" action="{{$urlAppend}}modules/announcements/submit.php?course={{$course_code}}">

                                        <div class="row mt-4 form-group {{ $antitle_error }}">
                                            <label for="AnnTitle" class="col-12 control-label-notes">{{ trans('langAnnTitle') }}</label>
                                            <div class="col-12 ">
                                                <input class="form-control" placeholder="{{ trans('langAnnTitle') }}..." type="text" name="antitle" value="{{ $titleToModify }}"/>
                                                <span class='help-block Accent-200-cl'>{{ Session::getError('antitle') }}</span>
                                            </div>
                                        </div>

                                        <div class='row mt-4 form-groum'>
                                            <label for='AnnBody' class='col-12 control-label-notes'>{{ trans('langAnnBody') }}</label>
                                            <div class='col-12'>{!! $contentToModify !!}</div>
                                        </div>

                                        <div class='row mt-4 form-group'>
                                            <label for='Email' class='col-12 control-label-notes'>{{ trans('langEmailOption') }}</label>
                                            <div class='col-12'>
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
                                            <label for='startdate' class='col-12 control-label-notes'>{{ trans('langStartDate') }}</label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                    <span class='input-group-addon'>
                                                        <label class='label-container'>
                                                            <input class='mt-0' id='start_date_active' type='checkbox' name='startdate_active' {{ $start_checkbox }}>
                                                            <span class='checkmark'></span>
                                                        </label>
                                                    </span>
                                                    <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0 border-start-0' name='startdate' id='startdate' type='text' value = '{{ $showFrom }}'>
                                                </div>
                                                <span class='help-block'>{{ $startdate_error }}</span>
                                            </div>
                                        </div>


                                        <div class='row mt-4 form-group {{ $enddate_error }}'>
                                            <label for='enddate' class='col-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                    <span class='input-group-addon'>
                                                        <label class='label-container'>
                                                            <input class='mt-0' id='end_date_active' type='checkbox' name='enddate_active' {{ $end_checkbox}} {{ $end_disabled}}>
                                                            <span class='checkmark'></span>
                                                        </label>
                                                    </span>
                                                    <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0 border-start-0' name='enddate' id='enddate' type='text' value = '{{ $showUntil }}'>
                                                </div>
                                                <span class='help-block'>{{ $enddate_error }}</span>
                                            </div>
                                        </div>

                                        <div class='row mt-4 form-group'>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container'>
                                                        <input class='mt-0' type='checkbox' name='show_public' {{  $checked_public }}> {{ trans('langViewShow') }}
                                                        <span class='checkmark'></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row mt-4 form-group'>
                                            @if (isset($_GET['modify']))
                                                <input type='hidden' name='id' value='{{ $announce_id }}'>
                                            @endif
                                            <input type='hidden' name='course' value='{{ $course_code }}'>
                                            <input type='hidden' name='editorFromCreateEditAnnouncement' value='{{$is_editor}}'>
                                            <div class='col-12'>
                                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                                    <button type="submit" class="btn submitAdminBtn" name="submitAnnouncement" value="{{ trans('langAdd') }}">{{ trans('langSubmit') }}</button>
                                                    <a href="index.php?course={{ $course_code }}" class="btn cancelAdminBtn">{{ trans('langCancel') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                        @if (isset($_GET['copy_ann']))
                                            <input type='hidden' name='copy_ann'>
                                        @endif
                                    </form>
                                </div>
                            </div>

                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
