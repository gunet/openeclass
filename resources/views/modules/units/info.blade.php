@extends('layouts.default')


@push('head_styles')
    <link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
    <link href="{{ $urlAppend }}template/modern/css/new_calendar.css?v={{ CACHE_SUFFIX }}" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js?v={{ CACHE_SUFFIX }}'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v={{ CACHE_SUFFIX }}'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/locales/bootstrap-datepicker.{{ $language }}.min.js?v={{ CACHE_SUFFIX }}'></script>

    <script type='text/javascript'>
            $(function() {
                $('#unitdurationfrom, #unitdurationto').datepicker({
                    format: 'dd-mm-yyyy',
                    pickerPosition: 'bottom-right',
                    language: '{{ $language }}',
                    autoclose: true
                });
                $('#assign_button_all').click(hideAssignees);
                $('#assign_button_user, #assign_button_group').click(ajaxAssignees);
            });

            function ajaxAssignees()
            {
                $('#assignees_tbl').removeClass('hide');
                var type = $(this).val();
                $.post('',
                    {
                        assign_type: type
                    },
                    function(data,status){
                        var index;
                        var parsed_data = JSON.parse(data);
                        var select_content = '';
                        if(type==1){
                            for (index = 0; index < parsed_data.length; ++index) {
                                select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname'] + '<\/option>';
                            }
                        } else {
                            for (index = 0; index < parsed_data.length; ++index) {
                                select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                            }
                        }
                        $('#assignee_box').find('option').remove();
                        $('#assign_box').find('option').remove().end().append(select_content);
                    });
            }

            function hideAssignees()
            {
                $('#assignees_tbl').addClass('hide');
                $('#assignee_box').find('option').remove();
            }

    </script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert')

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>

                                <form class='form-horizontal' action='{{ $postUrl }}' method='post' onsubmit="return checkrequired(this, 'unittitle')">
                                    @if ($unitId)
                                        <input type='hidden' name='unit_id' value='{{ $unitId }}'>
                                    @endif

                                    <div class='row form-group'>
                                        <label for='unitTitle' class='col-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                        <div class='col-12'>
                                            <input type='text' class='form-control' id='unitTitle' name='unittitle' value='{{ $unitTitle }}'>
                                        </div>
                                    </div>

                                    <div class='row form-group mt-4'>
                                        <label for='unitdescr' class='col-12 control-label-notes'>{{ trans('langUnitDescr') }}</label>
                                        <div class='col-12'>
                                            {!! $descriptionEditor !!}
                                        </div>
                                    </div>

                                    <div class='row form-group mt-4'>
                                        <div class='col-12 control-label-notes mb-1'>{{ trans('langDuration') }}
                                            <span class='help-block'>{{ trans('langOptional') }}:</span>
                                        </div>
                                        <div class='col-12'>
                                            <div class="row">

                                                <div class="col-lg-6 col-12">
                                                    <div class="input-group mb-4">
                                                        <span class="input-group-text h-40px input-border-color bg-input-default border-end-0" id="basic-addon1"><i class='fa-regular fa-calendar'></i></span>
                                                        <input type="text" class="form-control mt-0 border-start-0" id='unitdurationfrom' name='unitdurationfrom' value='{{ $start_week }}' aria-label="{{ trans('langStart') }}" aria-describedby="basic-addon1">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-12 mt-lg-0 mt-4">
                                                    <div class="input-group mb-4">
                                                        <span class="input-group-text h-40px input-border-color bg-input-default border-end-0" id="basic-addon2"><i class='fa-regular fa-calendar'></i></span>
                                                        <input type="text" class="form-control mt-0 border-start-0" id='unitdurationto' name='unitdurationto' value='{{ $finish_week }}' aria-label="{{ trans('langEnd') }}" aria-describedby="basic-addon2">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='row form-group mt-4'>
                                        <div class='control-label-notes mb-1'>{{ trans('langWorkAssignTo') }}</div>
                                        <div class='col-12'>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' @if ($unitAssignToSpecific == 0) checked @endif>
                                                    {{ trans('langWorkToAllUsers') }}
                                                </label>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' id='assign_button_user' name='assign_to_specific' value='1' @if ($unitAssignToSpecific == 1) checked @endif>
                                                    {{ trans('langWorkToUser') }}
                                                </label>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' id='assign_button_group' name='assign_to_specific' value='2' @if ($unitAssignToSpecific == 2) checked @endif>
                                                    {{ trans('langWorkToGroup') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='row form-group mt-4'>
                                        <div class='col-12'>
                                            <div class='table-responsive mt-0'>
                                                <table id='assignees_tbl' class='table-default @if (!in_array($unitAssignToSpecific, [1, 2])) hide @endif'>
                                                <thead>
                                                <tr class='title1 list-header'>
                                                    <td class='form-label' id='assignees'>{{ trans('langStudents') }}</td>
                                                    <td class='form-label text-center'>{{ trans('langMove') }}</td>
                                                    <td class='form-label'>{{ trans('langWorkAssignTo') }}</td>
                                                </tr>
                                                </thead>
                                                <tr>
                                                    <td>
                                                        <select aria-label='{{ trans('langStudents') }}' class='form-select h-100' id='assign_box' size='10' multiple>
                                                            {!! $unassigned_options !!}
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class='d-flex align-items-center flex-column gap-2'>
                                                            <input aria-label='{{ trans('langMove') }}' class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick="move('assign_box','assignee_box')" value='   &gt;&gt;   ' />
                                                            <input aria-label='{{ trans('langMove') }}' class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick="move('assignee_box','assign_box')" value='   &lt;&lt;   ' />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select aria-label='{{ trans('langWorkAssignTo') }}' class='form-select h-100' id='assignee_box' name='ingroup[]' size='10' multiple>
                                                            {!! $assignee_options !!}
                                                        </select>
                                                    </td>
                                                </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4"></div>

                                    {!! $tagInput !!}

                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                           <button class='btn submitAdminBtn' type='submit' name='edit_submit' onclick="selectAll('assignee_box',true)">{{ trans('langSubmit') }}</button>
                                           <a class='btn cancelAdminBtn' href='{{ $postUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
