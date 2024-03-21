@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<style>
    .panel-move .form-content-modules, .panel-move .col-12.d-flex.justify-content-end.align-items-center.gap-2.mt-4 {
        display: none!important;
    }
</style>

<script type='text/javascript'>

    $(document).ready(function(){

        let checkboxStates = [];

        $('li.bulk-processing a').on('click', function(event) {
            event.preventDefault();
            $('.bulk-processing-box').toggleClass('d-none');
            $('.checkbox_th').toggleClass('d-none');
            $('.checkbox_td').toggleClass('d-none');
            if ($(this).find('span.fa.fa-check').length) {
                $(this).find('span.fa.fa-check').remove();
            } else {
                $(this).append('<span class=\'fa fa-check text-success\' style=\'margin-left: 5px;\'></span>');
            }
        });

        $('#source_path').val($('select[name=\"moveTo\"] option:first').val());
        $('select[name=\"moveTo\"]').change(function(){
            $('#source_path').val($(this).val());
        });

        $('.table-default').on('change', 'input[type=checkbox]', function() {
            let cbid = $(this).attr('cbid');
            let filepath = $(this).attr('filepath');
            checkboxStates[cbid] = this.checked;

            let selectedCbidValues = $('#selectedcbids').val().split(',');
            let filepaths = $('#filepaths').val().split(',');

            let cbidIndex = selectedCbidValues.indexOf(cbid.toString());
            let filepathIndex = filepaths.indexOf(filepath);

            if (this.checked && cbidIndex === -1) {
                selectedCbidValues.push(cbid);
                filepaths.push(filepath);

            } else if (!this.checked && cbidIndex !== -1) {
                selectedCbidValues.splice(cbidIndex, 1);
                filepaths.splice(filepathIndex, 1);
            }
            $('#selectedcbids').val(selectedCbidValues.filter(Boolean).join(','));
            $('#filepaths').val(filepaths.filter(Boolean).join(','));

        });


        $('select[name=\"bulk_action\"]').change(function(){
            var selectedOption = $(this).val();
            if(selectedOption === 'move') {
                $('.panel-move form .form-group:eq(1)').remove();
                $('.panel-move').removeClass('d-none');
                $('.checkbox_td input[type="checkbox"]').each(function() {
                    if ($(this).attr('isdir') === '1') {
                        $(this).prop('checked', false);
                        $(this).prop('disabled', true);

                        let cbid = $(this).attr('cbid');
                        let filepath = $(this).attr('filepath');
                        checkboxStates[cbid] = this.checked;

                        let selectedCbidValues = $('#selectedcbids').val().split(',');
                        let filepaths = $('#filepaths').val().split(',');

                        let cbidIndex = selectedCbidValues.indexOf(cbid.toString());
                        let filepathIndex = filepaths.indexOf(filepath);

                        if (this.checked && cbidIndex === -1) {
                            selectedCbidValues.push(cbid);
                            filepaths.push(filepath);

                        } else if (!this.checked && cbidIndex !== -1) {
                            selectedCbidValues.splice(cbidIndex, 1);
                            filepaths.splice(filepathIndex, 1);
                        }
                        $('#selectedcbids').val(selectedCbidValues.filter(Boolean).join(','));
                        $('#filepaths').val(filepaths.filter(Boolean).join(','));

                    }
                });
            } else {
                $('.panel-move').addClass('d-none');
                $('.checkbox_td input[type="checkbox"]').each(function() {
                    if ($(this).attr('isdir') === '1') {
                        $(this).prop('disabled', false);
                    }
                });
            }
        });


        $('#bulk_actions').submit(function(e) {
            var selectedOption = $('select[name="bulk_action"]').val();
            if (selectedOption === null) {
                e.preventDefault(); // Prevent the default form submission
            }
        });


    })

</script>

<div class="col-12 main-section">
<div class='{{ $container }} @if($course_code) module-container py-lg-0 @else main-container @endif'>
        <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

            @if($course_code)
            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
            @endif

            @if($course_code)
            <div class="col_maincontent_active">
            @else
            <div class="col-12">
            @endif

                <div class="row">

                    @if(!$is_in_tinymce)
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif


                    @if($course_code and !$is_in_tinymce)
                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>
                    @endif

                    @if(!$is_in_tinymce)
                        @include('layouts.partials.legend_view')
                    @endif

                    {!! $actionBar !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



                    @if ($dialogBox)
                        <div class='col-12'>
                            @include("modules.document.$dialogBox", ['menuTypeID' => $menuTypeID])
                        </div>
                    @endif

                    @if($metaDataBox)
                        <div class='col-12'>
                            {!! $metaDataBox !!}
                        </div>
                    @endif

                    @if (count($fileInfo) or $curDirName)

                        <div class='col-12  @if($dialogBox or $metaDataBox) mt-4 @endif'>
                            
                                
                                    <div class='d-flex justify-content-between gap-5'>
                                        <div class='d-flex justify-content-start align-items-center flex-wrap'>
                                            {!! make_clickable_path($curDirPath) !!}
                                            @if ($downloadPath)
                                                &nbsp;&nbsp;{!! icon('fa-download', trans('langDownloadDir'), $downloadPath) !!}
                                            @endif
                                            @if ($curDirName and $dirComment)
                                                {{ $dirComment }}
                                            @endif
                                        </div>
                                        <div>
                                            @if ($curDirName)
                                                <a href='{{$parentLink}}' type='button' class='btn submitAdminBtn'>
                                                    <span class='fa fa-level-up'></span><span class='hidden-xs TextBold'>{{ trans('langUp') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="bulk-processing-box d-none" style="margin-top: 20px;">
                                        <div class='col-md-12'>
                                            <div class='panel'>
                                                <div class=''>
                                                    <strong>
                                                        <i class='fa fa-edit'></i>
                                                        {{ trans('langBulkProcessing') }}
                                                    </strong>
                                                    <form id='bulk_actions' method='post' action='' style='display: flex;gap: 5px;margin: 10px 0'>
                                                        <select class='form-select mt-0' name='bulk_action' class='px-3' style='max-width:250px;'>
                                                            <option value='av_actions' disabled selected hidden>{{ trans('langActions') }}</option>
                                                            <option value='move'>{{ trans('langMove') }}</option>
                                                            <option value='delete'>{{ trans('langDelete') }}</option>
                                                            <option value='visible'>{{ trans('langNewBBBSessionStatus') }}: {{ trans('langVisible') }}</option>
                                                            <option value='invisible'>{{ trans('langNewBBBSessionStatus') }}: {{ trans('langInvisible') }}</option>
                                                        </select>
                                                        <input type='submit' class='btn-submit px-3' name='bulk_submit' value='{{ trans('langSubmit') }}'>
                                                        <input type='hidden' id='selectedcbids' name='selectedcbids' value=''>
                                                        <input type='hidden' id='filepaths' name='filepaths' value=''>
                                                        <input type='hidden' id='source_path' name='source_path' value=''>
                                                    </form>
                                                    <div class='panel-move d-none'>
                                                        @include("modules.document.$dialogBoxBulk", ['menuTypeID' => $menuTypeID])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            
                        </div>


                        <div class='col-12'>
                            <div class='table-responsive mt-4'>
                                <table class='table-default' id="document_table">

                                    <thead>
                                        <tr class="list-header">
                                            <th style='width:5%;' class='checkbox_th d-none'></th>
                                            <th style='width:50%;'>{!! headlink(trans('langName'), 'name') !!}</th>
                                            
                                            <th style='width:15%;'>{{ trans('langSize') }}</th>
                                            <th style='width:15%;'>{!! headlink(trans('langDate'), 'date') !!}</th>

                                            @unless ($is_in_tinymce)
                                                <th style='width:10%;'>{!! icon('fa-cogs', trans('langCommands')) !!}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>

                                    @forelse ($fileInfo as $file)

                                        @if($file->visible == 1 or $can_upload)
                                            <tr class="{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}">
                                                <td class='text-center checkbox_td d-none'>
                                                    <div class='checkbox'>
                                                        <label class='label-container'>
                                                            <input type='checkbox' isDir='{{$file->is_dir}}' filepath='{{$file->path}}' cbid='{{$file->id}}' value='{{$file->id}}'>
                                                            <span class='checkmark'></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td style='width:50%;'>
                                                    @php $downloadfile = $base_url . "download=" . getIndirectReference($file->path); @endphp
                                                    <input type='hidden' value={!!$downloadfile!!}>

                                                    @if($file->visible == 1)
                                                        @if ($file->is_dir)
                                                            <span class='visibleFile pe-2'>{!! icon(choose_image('.' . $file->format), trans('langDirectory')) !!} </span>
                                                        @else
                                                            <span class='visibleFile pe-2'>{!! icon(choose_image('.' . $file->format), trans('langFileName') . " " . $file->format) !!} </span>
                                                        @endif
                                                    @else
                                                        @if ($file->is_dir)
                                                            <span class='invisibleFile pe-2'>{!! icon(choose_image('.' . $file->format), trans('langDirectory')) !!} </span>
                                                        @else
                                                            <span class='invisibleFile pe-2'>{!! icon(choose_image('.' . $file->format), trans('langFileName') . " " . $file->format) !!} </span>
                                                       @endif
                                                    @endif

                                                    @if ($file->is_dir)
                                                        @if($file->visible == 1)
                                                            <a href='{!! $file->url !!}'>{{ $file->filename }}</a>
                                                        @else
                                                            <a class="opacity-50 pe-none" href='{!! $file->url !!}'>{{ $file->filename }}</a>
                                                        @endif
                                                    @else
                                                        {!! $file->link !!}
                                                    @endif
                                                    @if ($can_upload)
                                                        @if ($file->extra_path)
                                                            @if ($file->common_doc_path)
                                                                @if ($file->common_doc_visible)
                                                                    {!! icon('common', trans('langCommonDocLink')) !!}
                                                                @else
                                                                    {!! icon('common-invisible', trans('langCommonDocLinkInvisible')) !!}
                                                                @endif
                                                            @else
                                                                {!! icon('fa-external-link', trans('langExternalFile')) !!}
                                                            @endif
                                                        @endif
                                                        @if (!$file->public)
                                                            {!! icon('fa-lock', trans('langNonPublicFile')) !!}
                                                        @endif
                                                        @if ($file->editable)
                                                            {!! icon('fa-edit', trans('langEdit'), $file->edit_url) !!}
                                                        @endif
                                                    @endif
                                                    @if ($file->copyrighted)
                                                        {!! icon($file->copyright_icon, $file->copyright_title, $file->copyright_link,
                                                            'target="_blank" style="color:#555555;"') !!}
                                                    @endif
                                                    @if ($file->comment)
                                                        <br>
                                                        <span class='comment text-muted'>
                                                            <small>
                                                                {!! nl2br(e($file->comment)) !!}
                                                            </small>
                                                        </span>
                                                    @endif

                                                    @if($file->updated_message)
                                                        @if($file->visible == 1)
                                                            <span class="badge bg-success">{{ $file->updated_message }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $file->updated_message }}</span>
                                                        @endif
                                                    @endif
                                                </td>

                                                

                                                @if ($file->is_dir)
                                                    <td style='width:20%;'>&nbsp;</td>
                                                    @if($file->visible == 1)
                                                        <td style='width:20%;'>{{ format_locale_date(strtotime($file->date), 'short', false) }}</td>
                                                    @else
                                                        <td style='width:20%;'><span class="opacity-50">{{ format_locale_date(strtotime($file->date), 'short', false) }}</span></td>
                                                    @endif

                                                @elseif ($file->format == '.meta')
                                                    @if($file->visible == 1)
                                                        <td style='width:20%;'>{{ format_file_size($file->size) }}</td>
                                                        <td style='width:15%;'>{{ $file->date }}</td>
                                                    @else
                                                        <td style='width:20%;'><span class="opacity-50">{{ format_file_size($file->size) }}</span></td>
                                                        <td style='width:20%;'><span class="opacity-50">{{ format_locale_date(strtotime($file->date), 'short', false) }}</span></td>
                                                    @endif

                                                @else
                                                    @if($file->visible == 1)
                                                        <td style='width:20%;'>{{ format_file_size($file->size) }}</td>
                                                        <td style='width:20%;' title='{{ format_locale_date(strtotime($file->date), 'short', false) }}' class='center'>{{ format_locale_date(strtotime($file->date), 'short') }}</td>
                                                    @else
                                                        <td style='width:20%;'><span style="opacity-50">{{ format_file_size($file->size) }}</span></td>
                                                        <td style='width:20%;' title='{{ format_locale_date(strtotime($file->date), 'short', false) }}' class='center'><span class="opacity-50">{{ format_locale_date(strtotime($file->date), 'short') }}</span></td>
                                                    @endif

                                                @endif

                                                @unless ($is_in_tinymce)
                                                    <td style='width:10%;' class='text-end {{ $can_upload? 'option-btn-cell': '' }}'>
                                                        {!! $file->action_button !!}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif


                                    @empty
                                        <tr>
                                            <td colspan='5'>
                                                <p class='not_visible'> - {{ trans('langNoDocuments') }} - </p>
                                            </td>
                                        </tr>

                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                <span>{{ trans('langNoDocuments') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

</div>
</div>



<script>
    $('.fileModal').click(function (e)
    {
        e.preventDefault();
        var fileURL = $(this).attr('href');
        var downloadURL = $(this).prev('input').val();
        var fileTitle = $(this).attr('title');

        // BUTTONS declare
        var bts = {
            download: {
                label: '<i class="fa fa-download"></i> {{ trans('langDownload') }}',
                className: 'submitAdminBtn gap-1',
                callback: function (d) {
                    window.location = downloadURL;
                }
            },
            print: {
                label: '<i class="fa fa-print"></i> {{ trans('langPrint') }}',
                className: 'submitAdminBtn gap-1',
                callback: function (d) {
                    var iframe = document.getElementById('fileFrame');
                    iframe.contentWindow.print();
                }
            }
        };
        if (screenfull.enabled) {
            bts.fullscreen = {
                label: '<i class="fa fa-arrows-alt"></i> {{ trans('langFullScreen') }}',
                className: 'submitAdminBtn gap-1',
                callback: function() {
                    screenfull.request(document.getElementById('fileFrame'));
                    return false;
                }
            };
        }
        bts.newtab = {
            label: '<i class="fa fa-plus"></i> {{ trans('langNewTab') }}',
            className: 'submitAdminBtn gap-1',
            callback: function() {
                window.open(fileURL);
                return false;
            }
        };
        bts.cancel = {
            label: '{{ trans('langCancel') }}',
            className: 'cancelAdminBtn'
        };

        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class="row">'+
                        '<div class="col-12">'+
                            '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: bts
        });
    });

</script>

@endsection
