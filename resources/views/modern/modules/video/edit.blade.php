<?php
    $form_enctype = 'application/x-www-form-urlencoded';
    if ($form_input === 'file') {
        $form_enctype = 'multipart/form-data';
    }
?>

@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(function() {
            $('#videodate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });
        });
        function checkrequired(which, entry) {
            var pass=true;
            if (document.images) {
                for (i = 0; i < which.length; i++) {
                    var tempobj = which.elements[i];
                    if (tempobj.name == entry) {
                        if (tempobj.type == "text" && tempobj.value == '') {
                            pass=false;
                            break;
                        }
                    }
                }
            }
            if (!pass) {
                alert('{{ trans('langEmptyVideoTitle') }}');
                return false;
            } else {
                return true;
            }
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
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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

                                <form class='form-horizontal' role='form' method='POST' action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}' enctype='{{ $form_enctype }}' onsubmit="return checkrequired(this, 'title');">
                                    <fieldset>

                                        <div class='form-group'>
                                            @if (isset($pendingCloudUpload))
                                                <label for='fileCloudInfo' class='col-sm-12 control-labe-notes'>{{ trans('langCloudFile') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                                </div>
                                            @elseif ($form_input === 'file')
                                                <label for='FileName' class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                                    {!! CloudDriveManager::renderAsButtons() !!}
                                                    <input type='file' name='userFile' id='FileName'>
                                                </div>
                                            @elseif ($form_input === 'url')
                                                <label for='Url' class='col-sm-12 control-label-notes'>{{ trans('langURL') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='URL' id='Url'>
                                                </div>
                                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'videolink')
                                                <label for='Url' class='col-sm-12 control-label-notes'>{{ trans('langURL') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='url' value='{{ $edititem->url }}' id='Url'>
                                                </div>
                                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'video')
                                                <input type='hidden' name='url' value='{{ $edititem->url }}'>
                                                <div class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</div>
                                                <div class='col-sm-12 margin-top-thin'>{{ $edititem->url }}</div>
                                            @endif
                                        </div>


                                        <div class='form-group mt-4'>
                                            <label for='Title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-sm-12'>
                                                @if (isset($edititem))
                                                    <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='title' value='{{ $edititem->title }}' id='Title'>
                                                @else
                                                    <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='title' size='55' id='Title'>
                                                @endif
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <label for='Desc' class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                            <div class='col-sm-12'>
                                                <textarea id='Desc' class='form-control' placeholder="{{ trans('langGiveText') }}..." rows='3' name='description'>@if (isset($_GET['id']) && isset($_GET['table_edit'])){{ $edititem->description }}@endif</textarea>
                                            </div>
                                        </div>





                                                <div class='form-group mt-4'>
                                                    <label for='Creator' class='col-sm-12 control-label-notes'>{{ trans('langCreator') }}</label>
                                                    <div class='col-sm-12'>
                                                        @if (isset($form_input))
                                                            <input class='form-control' type='text' name='creator' value='{{ $nick }}' id='Creator'>
                                                        @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                            <input class='form-control' type='text' name='creator' value='{{ $edititem->creator }}' id='Creator'>
                                                        @endif
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label for='Publisher' class='col-sm-12 control-label-notes'>{{ trans('langpublisher') }}</label>
                                                    <div class='col-sm-12'>
                                                        @if (isset($form_input))
                                                            <input class='form-control' type='text' name='publisher' value='{{ $nick }}' id='Publisher'>
                                                        @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                            <input class='form-control' type='text' name='publisher' value='{{ $edititem->publisher }}' id='Publisher'>
                                                        @endif
                                                    </div>
                                                </div>


                                            @if (isset($form_input))

                                                <div class="form-group mt-4 @if(isset($_GET['table_edit'])) d-none @endif">
                                                    <label for='videodate' class='col-sm-6 control-label-notes'>{{ trans('langDate') }}</label>
                                                    <div class='col-sm-12'><input id='videodate' class='form-control' type='text' name='date' value='{{ date('Y-m-d G:i') }}'></div>
                                                </div>

                                            @endif

                                                <div class='form-group mt-4'>
                                                    <label for='Category' class='col-sm-6 control-label-notes'>{{ trans('langCategory') }}</label>
                                                    <div class='col-sm-12'>
                                                        <select class='form-select' name='selectcategory' id='Category'>
                                                            <option value='0'>--</option>
                                                        @foreach ($resultcategories as $cat)
                                                            <?php
                                                                if (isset($form_input)) {
                                                                    $selected = '';
                                                                } else if (isset($_GET['id']) && isset($_GET['table_edit'])) {
                                                                    $selected = '';
                                                                    if (isset($edititem->category) && $edititem->category == $cat->id) {
                                                                        $selected = " selected='selected'";
                                                                    }
                                                                }
                                                            ?>
                                                            <option value='{{ $cat->id }}' {{ $selected }}>{{ $cat->name }}</option>
                                                        @endforeach
                                                        </select>
                                                    </div>
                                                </div>



                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end align-items-center'>

                                                    @if ($form_input === 'file')

                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'submitAdminBtn',
                                                                    'text'  =>  $GLOBALS['langUpload'],
                                                                    'name'  =>  'add_submit',
                                                                    'value' =>  $GLOBALS['langUpload']
                                                                )
                                                            ))
                                                            !!}

                                                        {!! form_buttons(array(
                                                                array(
                                                                    'class' => 'cancelAdminBtn ms-1',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                            !!}

                                                    @elseif ($form_input === 'url')

                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'submitAdminBtn',
                                                                    'text'  =>  $GLOBALS['langSave'],
                                                                    'name'  =>  'add_submit',
                                                                    'value' =>  $GLOBALS['langAdd']
                                                                )
                                                            ))
                                                            !!}

                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'cancelAdminBtn ms-1',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                        !!}

                                                    @elseif (isset($_GET['id']) && isset($_GET['table_edit']))

                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'submitAdminBtn',
                                                                    'text'  =>  $GLOBALS['langSave'],
                                                                    'name'  =>  'edit_submit',
                                                                    'value' =>  $GLOBALS['langEditChange']
                                                                )
                                                            ))
                                                            !!}

                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'cancelAdminBtn ms-1',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                            !!}

                                                        <input type='hidden' name='id' value='{{ $edititem->id }}'>
                                                        <input type='hidden' name='table' value='{{ $table_edit }}'>
                                                    @endif

                                            </div>
                                        </div>
                                    </fieldset>
                                    @if ($form_input === 'file')

                                        <div class='form-group mt-3'>
                                            <div class='col-sm-offset-2 col-sm-10'>
                                                <div class='smaller right'>{{ trans('langMaxFileSize') }} {{ ini_get('upload_max_filesize') }}</div>
                                            </div>
                                        </div>
                                    @endif
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
