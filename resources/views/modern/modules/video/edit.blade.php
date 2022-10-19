<?php
    $form_enctype = 'application/x-www-form-urlencoded';
    if ($form_input === 'file') {
        $form_enctype = 'multipart/form-data';
    }
?>

@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
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


                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    
                    
                        {!!
                        action_bar(array(
                            array('title' => $GLOBALS['langBack'],
                                'url' => $backPath,
                                'icon' => 'fa-reply',
                                'level' => 'primary-label')
                            )
                        )
                        !!}
                        
                        <div class='col-12'>
                            <div class='form-wrapper form-edit p-3 rounded'>
                                
                                <form class='form-horizontal'
                                    role='form'
                                    method='POST'
                                    action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'
                                    enctype='{{ $form_enctype }}'
                                    onsubmit="return checkrequired(this, 'title');">
                                    <fieldset>
                                       
                                        <div class='form-group'>
                                            @if (isset($pendingCloudUpload))
                                                <label for='fileCloudName' class='col-sm-12 control-labe-notes'>{{ trans('langCloudFile') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                                </div>
                                            @elseif ($form_input === 'file')
                                                <label for='FileName' class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                                    {!! CloudDriveManager::renderAsButtons() !!}
                                                    <input type='file' name='userFile'>
                                                </div>
                                            @elseif ($form_input === 'url')
                                                <label for='Url' class='col-sm-12 control-label-notes'>{{ trans('langURL') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='URL'>
                                                </div>
                                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'videolink')
                                                <label for='Url' class='col-sm-12 control-label-notes'>{{ trans('langURL') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='url' value='{{ $edititem->url }}'>
                                                </div>
                                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'video')
                                                <input type='hidden' name='url' value='{{ $edititem->url }}'>
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                <div class='col-sm-12 margin-top-thin'>{{ $edititem->url }}</div>
                                            @endif
                                        </div>

                                        

                                        <div class='form-group mt-3'>
                                            <label for='Title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-sm-12'>
                                                @if (isset($edititem))
                                                    <input class='form-control' placeholder="{{ trans('langTitle') }}..." type='text' name='title' value='{{ $edititem->title }}'>
                                                @else
                                                    <input class='form-control' placeholder="{{ trans('langTitle') }}..." type='text' name='title' size='55'>
                                                @endif
                                            </div>
                                        </div>

                                      


                                        <div class='form-group mt-3'>
                                            <label for='Desc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-sm-12'>
                                                <textarea class='form-control' placeholder="{{ trans('langGiveText') }}..." rows='3' name='description'>@if (isset($_GET['id']) && isset($_GET['table_edit'])){{ $edititem->description }}@endif</textarea>
                                            </div>
                                        </div>

                                     

                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-3'>
                                                    <label for='Creator' class='col-sm-12 control-label-notes'>{{ trans('langCreator') }}:</label>
                                                    <div class='col-sm-12'>
                                                        @if (isset($form_input))
                                                            <input class='form-control' type='text' name='creator' value='{{ $nick }}'>
                                                        @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                            <input class='form-control' type='text' name='creator' value='{{ $edititem->creator }}'>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-3'>
                                                    <label for='Publisher' class='col-sm-12 control-label-notes'>{{ trans('langpublisher') }}:</label>
                                                    <div class='col-sm-12'>
                                                        @if (isset($form_input))
                                                            <input class='form-control' type='text' name='publisher' value='{{ $nick }}'>
                                                        @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                            <input class='form-control' type='text' name='publisher' value='{{ $edititem->publisher }}'>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class='row'>
                                            @if (isset($form_input))
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-3'>
                                                    <label for='Date' class='col-sm-6 control-label-notes'>{{ trans('langDate') }}:</label>
                                                    <div class='col-sm-12'><input class='form-control' type='text' name='date' value='{{ date('Y-m-d G:i') }}'></div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-3'>
                                                    <label for='Category' class='col-sm-6 control-label-notes'>{{ trans('langCategory') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <select class='form-select' name='selectcategory'>
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
                                            </div>
                                        </div>

                                     


                                        <div class='form-group mt-5'>
                                            <div class='col-12'>
                                                <div class='row'>
                                                    @if ($form_input === 'file')
                                                        <div class='col-6'>
                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                                                    'text'  =>  $GLOBALS['langUpload'],
                                                                    'name'  =>  'add_submit',
                                                                    'value' =>  $GLOBALS['langUpload']
                                                                )
                                                            ))
                                                            !!}
                                                        </div>
                                                        <div class='col-6'>
                                                        {!! form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                            !!}
                                                        </div>
                                                    @elseif ($form_input === 'url')
                                                        <div class='col-6'>
                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                                                    'text'  =>  $GLOBALS['langSave'],
                                                                    'name'  =>  'add_submit',
                                                                    'value' =>  $GLOBALS['langAdd']
                                                                )
                                                            ))
                                                            !!}
                                                        </div>
                                                        <div class='col-6'>
                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                        !!}
                                                        </div>
                                                    @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                        <div class='col-6'>
                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                                                    'text'  =>  $GLOBALS['langSave'],
                                                                    'name'  =>  'edit_submit',
                                                                    'value' =>  $GLOBALS['langEditChange']
                                                                )
                                                            ))
                                                            !!}
                                                        </div>
                                                        <div class='col-6'>
                                                            {!!
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                                                    'href'  =>  $backPath
                                                                )
                                                            ))
                                                            !!}
                                                        </div>
                                                        <input type='hidden' name='id' value='{{ $edititem->id }}'>
                                                        <input type='hidden' name='table' value='{{ $table_edit }}'>
                                                    @endif
                                                </div>
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
                        </div>
                   
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
