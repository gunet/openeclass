<?php
    $form_enctype = 'application/x-www-form-urlencoded';
    if ($form_input === 'file') {
        $form_enctype = 'multipart/form-data';
    }
?>

@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">


                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                       
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
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
                            <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                                
                                <form class='form-horizontal'
                                    role='form'
                                    method='POST'
                                    action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}'
                                    enctype='{{ $form_enctype }}'
                                    onsubmit="return checkrequired(this, 'title');">
                                    <fieldset>
                                        <div class='row p-2'></div>
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
                                                <label for='Url' class='col-sm-6 control-label-notes'>{{ trans('langURL') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='url' value='{{ $edititem->url }}'>
                                                </div>
                                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'video')
                                                <input type='hidden' name='url' value='{{ $edititem->url }}'>
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                <div class='col-sm-12 margin-top-thin'>{{ $edititem->url }}</div>
                                            @endif
                                        </div>

                                        <div class='row p-2'></div>

                                        <div class='form-group'>
                                            <label for='Title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                            <div class='col-sm-12'>
                                                @if (isset($edititem))
                                                    <input class='form-control' type='text' name='title' value='{{ $edititem->title }}'>
                                                @else
                                                    <input class='form-control' type='text' name='title' size='55'>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row p-2'></div>


                                        <div class='form-group'>
                                            <label for='Desc' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-sm-12'>
                                                <textarea class='form-control' rows='3' name='description'>@if (isset($_GET['id']) && isset($_GET['table_edit'])){{ $edititem->description }}@endif</textarea>
                                            </div>
                                        </div>

                                        <div class='row p-2'></div>


                                        <div class='form-group'>
                                            <label for='Creator' class='col-sm-6 control-label-notes'>{{ trans('langcreator') }}:</label>
                                            <div class='col-sm-12'>
                                                @if (isset($form_input))
                                                    <input class='form-control' type='text' name='creator' value='{{ $nick }}'>
                                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                    <input class='form-control' type='text' name='creator' value='{{ $edititem->creator }}'>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row p-2'></div>


                                        <div class='form-group'>
                                            <label for='Publisher' class='col-sm-6 control-label-notes'>{{ trans('langpublisher') }}:</label>
                                            <div class='col-sm-12'>
                                                @if (isset($form_input))
                                                    <input class='form-control' type='text' name='publisher' value='{{ $nick }}'>
                                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                    <input class='form-control' type='text' name='publisher' value='{{ $edititem->publisher }}'>
                                                @endif
                                            </div>
                                        </div>


                                        @if (isset($form_input))
                                        <div class='row p-2'></div>
                                            <div class='form-group'>
                                                <label for='Date' class='col-sm-6 control-label-notes'>{{ trans('langDate') }}:</label>
                                                <div class='col-sm-12'><input class='form-control' type='text' name='date' value='{{ date('Y-m-d G:i') }}'></div>
                                            </div>
                                        @endif

                                        <div class='row p-2'></div>

                                        <div class='form-group'>
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

                                        <div class='row p-2'></div>


                                        <div class='form-group'>
                                            <div class='col-sm-offset-2 col-sm-10'>
                                                @if ($form_input === 'file')
                                                    {!!
                                                    form_buttons(array(
                                                        array(
                                                            'text'  =>  $GLOBALS['langUpload'],
                                                            'name'  =>  'add_submit',
                                                            'value' =>  $GLOBALS['langUpload']
                                                        ),
                                                        array(
                                                            'href'  =>  $backPath
                                                        )
                                                    ))
                                                    !!}
                                                @elseif ($form_input === 'url')
                                                    {!!
                                                    form_buttons(array(
                                                        array(
                                                            'text'  =>  $GLOBALS['langSave'],
                                                            'name'  =>  'add_submit',
                                                            'value' =>  $GLOBALS['langAdd']
                                                        ),
                                                        array(
                                                            'href'  =>  $backPath
                                                        )
                                                    ))
                                                    !!}
                                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                                    {!!
                                                    form_buttons(array(
                                                        array(
                                                            'text'  =>  $GLOBALS['langSave'],
                                                            'name'  =>  'edit_submit',
                                                            'value' =>  $GLOBALS['langEditChange']
                                                        ),
                                                        array(
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
                                    <div class='row p-2'></div>
                                        <div class='form-group'>
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
