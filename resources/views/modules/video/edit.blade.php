<?php
    $form_enctype = 'application/x-www-form-urlencoded';
    if ($_GET['form_input'] === 'file') {
        $form_enctype = 'multipart/form-data';
    }
?>

@extends('layouts.default')

@section('content')
    {!!
    action_bar(array(
        array('title' => $GLOBALS['langBack'],
              'url' => $backPath,
              'icon' => 'fa-reply',
              'level' => 'primary-label')
        )
    )
    !!}
    
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' 
                      role='form' 
                      method='POST' 
                      action='{{ $_SERVER["SCRIPT_NAME"] . "?course=" . $course_code }}' 
                      enctype='{{ $form_enctype }}' 
                      onsubmit="return checkrequired(this, 'title');">
                    <fieldset>
                        <div class='form-group'>
                            @if ($pendingCloudUpload)
                                <label for='fileCloudName' class='col-sm-2 control-label'>{{ trans('langCloudFile') }}</label>
                                <div class='col-sm-10'>
                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                </div>
                            @elseif ($_GET['form_input'] === 'file')
                                <label for='FileName' class='col-sm-2 control-label'>{{ trans('langWorkFile') }}:</label>
                                <div class='col-sm-10'>
                                    <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                    {!! CloudDriveManager::renderAsButtons() !!}
                                    <input type='file' name='userFile'>
                                </div>
                            @elseif ($_GET['form_input'] === 'url')
                                <label for='Url' class='col-sm-2 control-label'>{{ trans('langURL') }}:</label>
                                <div class='col-sm-10'>
                                    <input class='form-control' type='text' name='URL'>
                                </div>
                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'videolink')
                                <label for='Url' class='col-sm-2 control-label'>{{ trans('langURL') }}:</label>
                                <div class='col-sm-10'>
                                    <input class='form-control' type='text' name='url' value='{{ $edititem->url }}'>
                                </div>
                            @elseif (isset($_GET['id']) && isset($_GET['table_edit']) && $table_edit == 'video')
                                <input type='hidden' name='url' value='{{ $edititem->url }}'>
                                <label class='col-sm-2 control-label'>{{ trans('langWorkFile') }}:</label>
                                <div class='col-sm-10 margin-top-thin'>{{ $edititem->url }}</div>
                            @endif
                        </div>
                    
                        <div class='form-group'>
                            <label for='Title' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                            <div class='col-sm-10'>
                                @if (isset($_GET['form_input']))
                                    <input class='form-control' type='text' name='title' size='55'>
                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                    <input class='form-control' type='text' name='title' value='{{ $edititem->title }}'>
                                @endif
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='Desc' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                            <div class='col-sm-10'>
                                <textarea class='form-control' rows='3' name='description'>@if (isset($_GET['id']) && isset($_GET['table_edit'])){{ $edititem->description }}@endif</textarea>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='Creator' class='col-sm-2 control-label'>{{ trans('langcreator') }}:</label>
                            <div class='col-sm-10'>
                                @if (isset($_GET['form_input']))
                                    <input class='form-control' type='text' name='creator' value='{{ $nick }}'>
                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                    <input class='form-control' type='text' name='creator' value='{{ $edititem->creator }}'>
                                @endif
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='Publisher' class='col-sm-2 control-label'>{{ trans('langpublisher') }}:</label>
                            <div class='col-sm-10'>
                                @if (isset($_GET['form_input']))
                                    <input class='form-control' type='text' name='publisher' value='{{ $nick }}'>
                                @elseif (isset($_GET['id']) && isset($_GET['table_edit']))
                                    <input class='form-control' type='text' name='publisher' value='{{ $edititem->publisher }}'>
                                @endif
                            </div>
                        </div>
                        @if (isset($_GET['form_input']))
                            <div class='form-group'>
                                <label for='Date' class='col-sm-2 control-label'>{{ trans('langDate') }}:</label>
                                <div class='col-sm-10'><input class='form-control' type='text' name='date' value='{{ date('Y-m-d G:i') }}'></div>
                            </div>
                        @endif
                        <div class='form-group'>
                            <label for='Category' class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                            <div class='col-sm-10'>
                                <select class='form-control' name='selectcategory'>
                                    <option value='0'>--</option>
                                @foreach ($resultcategories as $cat)
                                    <?php
                                        if (isset($_GET['form_input'])) {
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
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                @if ($_GET['form_input'] === 'file')
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
                                @elseif ($_GET['form_input'] === 'url')
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
                    @if ($_GET['form_input'] === 'file')
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
@endsection