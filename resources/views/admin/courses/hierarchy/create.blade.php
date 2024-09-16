@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript'>

    $(document).ready(function() {

        $('.chooseFacultyImage').on('click', function () {
            var id_img = this.id;
            alert('{{ js_escape(trans('langImageSelected')) }}!');
            document.getElementById('choose_from_list').value = id_img;
            $('#FacultiesImagesModal').modal('hide');
            document.getElementById('selectedImage').value = '{{ trans('langSelect') }}:' + id_img;
        });

    });

</script>
@endpush


@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert')

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>

                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}{{ isset($mynode) ? '?action=edit' : '?action=add' }}' enctype='multipart/form-data' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <div class='form-group'>
                                    <label for='code' class='col-sm-12 control-label-notes'>{{ trans('langNodeCode1') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                    <div class='col-sm-12'>
                                        <input id='code' class='form-control' placeholder="{{ trans('langNodeCode1') }}" type='text' name='code' value='{{ isset($mynode) ? $mynode->code : "" }}' maxlength="30">
                                        &nbsp;<i>{{ trans('langCodeFaculte2') }}</i>
                                    </div>
                                </div>
                                @foreach ($session->active_ui_languages as $key => $langcode)
                                    <div class='form-group mt-4'>
                                        <label for='nodename-{{ $key }}' class='col-sm-12 control-label-notes'>{{ trans('langNodeName') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                        <div class='col-sm-12'>
                                                @if (isset($is_serialized) && $is_serialized && isset($names[$langcode]))
                                                    <input id='nodename-{{ $key }}' class='form-control' type='text' name='name-{{ $langcode }}' value='{{ $names[$langcode] }}' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>
                                                @elseif (isset($is_serialized) && !$is_serialized && $key == 0)
                                                    <input id='nodename-{{ $key }}' class='form-control' type='text' name='name-{{ $langcode }}' value='{{ $mynode->name }}' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>
                                                @else
                                                    <input id='nodename-{{ $key }}' class='form-control' type='text' name='name-{{ $langcode }}' value='' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>
                                                @endif
                                        </div>
                                    </div>
                                @endforeach

                                <div id='image_field' class='row form-group mt-4'>
                                    <div for='faculty_image' class='col-12 control-label-notes'>{{ trans('langFacultyImage') }}</div>
                                    <div class='col-12'>

                                        @if (isset($faculty_image))
                                            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                                                <img style="max-height:100px;max-width:150px;" src='{{ $urlAppend }}courses/facultyimg/{{ $faculty_code }}/image/{{ $faculty_image }}' alt="Faculty image"> &nbsp;&nbsp;
                                                <a class='btn deleteAdminBtn' href='{{$_SERVER['SCRIPT_NAME'] }}?action=edit&id={{ $id }}&delete_image=true&{!! generate_csrf_token_link_parameter() !!}'>
                                                    {{ trans('langDelete') }}
                                                </a>
                                            </div>
                                            <input type='hidden' name='faculty_image' value='{{ $faculty_image }}'>
                                        @else
                                            {!! fileSizeHidenInput() !!}
                                            <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                                                <li class='nav-item' role='presentation'>
                                                    <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'> {{ trans('langUpload') }}</button>
                                                </li>
                                                <li class='nav-item' role='presentation'>
                                                    <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>{{ trans('langAddPicture') }}</button>
                                                </li>
                                            </ul>
                                            <div class='tab-content mt-3' id='tabs-tabContent'>
                                                <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                                                    <label for='faculty_image'>{{ trans('langImageSelected') }}</label>
                                                    <input type='file' name='faculty_image' id='faculty_image'>
                                                </div>
                                                <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                                                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#FacultiesImagesModal'>
                                                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;{{ trans('langSelect') }}
                                                    </button>
                                                    <input type='hidden' id='choose_from_list' name='choose_from_list'>
                                                    <label for='selectedImage'>{{ trans('langImageSelected') }}:</label>
                                                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @foreach ($session->active_ui_languages as $key => $langcode)
                                    <div class='form-group mt-4'>
                                        <label for='description-{{ $langcode }}' class='col-sm-12 control-label-notes'>{{ trans('langNodeDescription') }}</label>
                                        <div class='col-sm-12'>
                                            {!! rich_text_editor('description-' . $langcode, 8, 20, $descriptions[$langcode]) !!}
                                        </div>
                                    </div>
                                @endforeach
                                <div class='form-group mt-4'>
                                    <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langNodeParent') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                    <div class='col-sm-12'>
                                        {!! $html !!}
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeParent2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langNodeAllowCourse') }}</div>
                                    <div class='col-sm-12 checkbox'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='allow_course' value='1'{!! isset($mynode) && $mynode->allow_course == 1 ? " checked" : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langNodeAllowCourse2') }}
                                        </label>

                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langNodeAllowUser') }}</div>
                                    <div class='col-sm-12 checkbox'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='allow_user' value='1'{!! isset($mynode) && $mynode->allow_user == 1 ? " checked" : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langNodeAllowUser2') }}
                                        </label>

                                    </div>
                                </div>
                            <div class='form-group mt-4'>
                                <label for='order_priority' class='col-sm-12 control-label-notes'>{{ trans('langNodeOrderPriority') }}</label>
                                <div class='col-sm-12'>

                                        <input id='order_priority' class='form-control' type='text' name='order_priority' value='{{ isset($mynode) ? $mynode->order_priority : '' }}'>
                                        <div class='help-block'>{{ trans('langNodeOrderPriority2') }}</div>

                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</div>
                                <div class='col-sm-12'>
                                    <div class='radio mb-3'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodeopen' type='radio' name='visible' value='2' {{ $visibleChecked[2] }}>
                                            <label for="nodeopen" aria-label="{{ trans('langNodePublic') }}">
                                                <span class='fa fa-unlock fa-lg fa-fw'></span>
                                            </label>
                                            {{ trans('langNodePublic') }}
                                        </label>
                                        <div class='help-block'>{{ trans('langNodePublic2') }}</div>
                                    </div>


                                    <div class='radio mb-3'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodeforsubscribed' type='radio' name='visible' value='1' {{ $visibleChecked[1] }}>
                                            <label for="nodeforsubscribed" aria-label="{{ trans('langNodeSubscribed') }}">
                                                <span class='fa fa-lock fa-lg fa-fw'></span>
                                                <span class='fa fa-pencil text-danger fa-custom-lock pen-hierarchy'></span>
                                            </label>
                                            {{ trans('langNodeSubscribed') }}
                                        </label>
                                        <div class='help-block'>{{ trans('langNodeSubscribed2') }}</div>
                                    </div>


                                    <div class='radio'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodehidden' type='radio' name='visible' value='0' {{ $visibleChecked[0] }}>
                                            <label for='nodehidden' aria-label="{{ trans('langViewHide') }}"><span class='fa fa-lock fa-lg fa-fw'></span></label>
                                            {{ trans('langViewHide') }}
                                        </label>
                                         <div class='help-block'>{{ trans('langNodeHidden2') }}</div>
                                    </div>

                                </div>
                            </div>

                            @if (isset($mynode))
                                <input type='hidden' name='id' value='{{ $id }}'>
                                <input type='hidden' name='oldparentid' value='{{ $formOPid }}'>
                                <input type='hidden' name='lft' value='{{ $mynode->lft }}'>
                                <input type='hidden' name='rgt' value='{{ $mynode->rgt }}'>
                            @endif
                            {!! showSecondFactorChallenge() !!}
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center'>


                                        {!! form_buttons([
                                            [
                                                'class' => 'submitAdminBtn',
                                                'text' => trans('langSave'),
                                                'name' => isset($mynode) ? 'edit' : 'add',
                                                'value'=> isset($mynode) ? trans('langAcceptChanges') : trans('langAdd')
                                            ]
                                        ]) !!}


                                        {!! form_buttons([
                                            [
                                                'class' => 'cancelAdminBtn ms-1',
                                                'href' => $_SERVER['SCRIPT_NAME']
                                            ]
                                        ]) !!}


                                </div>
                            </div>
                            </fieldset>

                            <div class='modal fade' id='FacultiesImagesModal' tabindex='-1' aria-labelledby='FacultiesImagesModalLabel' aria-hidden='true'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <div class='modal-title' id='FacultiesImagesModalLabel'>{{ trans('langFacultyImage') }}</div>
                                            <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
                                        </div>
                                        <div class='modal-body'>
                                            <div class='row row-cols-1 row-cols-md-2 g-4'>
                                                {!! $image_content !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>


        </div>
    </div>
</div>
@endsection
