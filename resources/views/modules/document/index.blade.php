@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<script type='text/javascript'>
    $(document).ready(function(){

        let isUppyLoaded = false;

        async function loadUppy() {
            try {
                const { Uppy, Dashboard, XHRUpload, English, French, German, Italian, Spanish, Greek } = await import("{{ $urlAppend }}js/bundle/uppy.js");

                const locale_map = {
                  'de': German,
                  'el': Greek,
                  'en': English,
                  'es': Spanish,
                  'fr': French,
                  'it': Italian,
                }

                const uppy = new Uppy({
                    autoProceed: false,
                    restrictions: {
                        maxFileSize: {{ parseSize(ini_get('upload_max_filesize')) }},
                        maxTotalFileSize: {{ $diskQuotaDocument-$diskUsed }},
                    }
                })

                uppy.use(Dashboard, {
                    target: '#uppy',
                    inline: true,
                    showProgressDetails: true,
                    proudlyDisplayPoweredByUppy: false,
                    height: 500,
                    thumbnailWidth: 100,
                    locale: locale_map['{{ $language }}'] || English,
                })

                let uploadPath = '{{ $curDirPath }}';
                let fileCreator = document.querySelector('input[name="file_creator"]').value;

                let uncompressInput = $('input[name="uncompress"]');
                let uncompress = uncompressInput.val();

                uncompressInput.change(function() {
                    uncompress = $(this).is(':checked') ? '1' : '0';
                    $(this).val(uncompress);
                    uppy.setMeta({
                        uncompress: uncompress,
                    });
                });

                let replaceInput = $('input[name="replace"]');
                let replace = replaceInput.val();
                replaceInput.change(function() {
                    replace = $(this).is(':checked') ? '1' : '0';
                    $(this).val(replace);
                    uppy.setMeta({
                        replace: replace,
                    });
                });

                let fileCopyrighted = 0;

                uppy.setMeta({
                    uploadPath: uploadPath,
                    file_creator: fileCreator,
                    file_copyrighted: fileCopyrighted,
                    replace: replace,
                    uncompress: uncompress,
                });

                uppy.use(XHRUpload, {
                    endpoint: '{!! $backUrl !!}',
                    formData: true,
                    fieldName: 'userFile',
                    method: 'POST',
                    headers: {

                    },
                    allowedMetaFields: [
                        'XHRUpload',
                        'uploadPath',
                        'file_creator',
                        'file_copyrighted',
                        'replace',
                        'uncompress',
                    ],
                    shouldRetry: () => false,
                })

                uppy.setMeta({
                    uploadPath: '{{ $curDirPath }}',
                    XHRUpload: true,
                });

                uppy.on('file-added', (file) => {
                    console.log('File added:', file)
                })

                uppy.on('complete', (result) => {
                    window.location.href = '{!! $backUrl !!}';

                })
                isUppyLoaded = true;
            } catch (error) {

                isUppyLoaded = false;
            }
        }

        loadUppy();

        // Drag and drop
        $('.uploadBTN').on('click', function(event) {

            if (!isUppyLoaded) {
                console.log('Uppy not loaded');
            } else {
                event.preventDefault();
                $('.drag_and_drop_container').toggleClass('d-none');
            }
        });

        // Bulk processing
        let checkboxStates = [];

        $('li.bulk-processing a').on('click', function(event) {
            event.preventDefault();
            $('.dialog_box').toggleClass('d-none');
            $('.bulk-processing-box').toggleClass('d-none');
            $('.checkbox_th').toggleClass('d-none');
            $('.checkbox_td').toggleClass('d-none');
            if ($(this).find('span.fa.fa-check').length) {
                $(this).find('span.fa.fa-check').remove();
            } else {
                $(this).append('<span class=\'fa fa-check text-success\' style=\'margin-left: 5px;\'></span>');
            }
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

                if ($('#moveTo').length == 0) {

                    let url = new URL(window.location.href);
                    let course = url.searchParams.get('course');
                    let openDir = url.searchParams.get('openDir');
                    let dirUrl = `directory_selection.php?course=${course}`;
                    if (openDir && openDir !== '/') {
                        dirUrl += `&openDir=${openDir}`;
                    }

                    $.ajax({
                        url: dirUrl,
                        type: 'GET',
                        success: function(data) {
                            $('.moveToDiv').html(data);
                            $('#source_path').val($('select[name=\"moveTo\"] option:first').val());
                            $('select[name=\"moveTo\"]').change(function(){
                                $('#source_path').val($(this).val());
                            });
                        }
                    });
                }

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
<div class='{{ $container }} @if($course_code) module-container document-index py-lg-0 @else main-container @endif'>
        <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

            @if($course_code)
                @include('layouts.partials.left_menu')
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
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                    @endif

                    @if(!$is_in_tinymce)
                        @include('layouts.partials.legend_view')
                    @endif

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

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
                    @if ($can_upload)
                        <div class="col-12 drag_and_drop_container d-none mb-3">
                            <input type="hidden" name="uploadPath" value="{{ $curDirPath }}">
                            <input type='hidden' name='file_creator' value='{{ $_SESSION['givenname'] . ' ' . $_SESSION['surname'] }}' size='40'>
                            @push('head_styles')
                                <link href="{{ $urlAppend }}js/bundle/uppy.min.css" rel="stylesheet">
                            @endpush

                            <div class='border-card p-2 rounded-2'>

                                <div id="uppy"></div>

                                <div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langUncompress')}}">
                                                    <input type='checkbox' name='uncompress' value='0'>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langUncompress') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-3'>
                                        <div class='col-sm-offset-2 col-sm-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langReplaceSameName')}}">
                                                    <input type='checkbox' name='replace' value='0'>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langReplaceSameName') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endif

                    @if (count($fileInfo) or $curDirName)


                        <div class='col-12  @if($dialogBox or $metaDataBox) mt-4 @endif'>

                            <div class='d-flex justify-content-between gap-lg-5 gap-3 flex-wrap'>
                                        <div class='d-flex justify-content-start align-items-center flex-wrap'>
                                            {!! make_clickable_path($curDirPath) !!}
                                            @if ($downloadPath)
                                                &nbsp;&nbsp;{!! icon('fa-download', trans('langDownloadDir'), $downloadPath) !!}
                                            @endif
                                            @if ($curDirName and $dirComment)
                                                <small>&nbsp;&nbsp;{{ $dirComment }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            @if ($curDirName)
                                                <a href='{{$parentLink}}' type='button' class='btn submitAdminBtn'>
                                                    <span class='fa fa-level-up'></span><span class='hidden-xs TextBold text-nowrap'>{{ trans('langUp') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="bulk-processing-box d-none my-4">
                                        <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                            <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                                <div class='form-wrapper form-edit'>
                                                    <div class='panel'>
                                                        <form id='bulk_actions' class='form-horizontal' method='post' action=''>

                                                            <label for='bulk-actions' class='control-label-notes mb-2'>{{ trans('langBulkProcessing') }}</label>
                                                            <select class='form-select' name='bulk_action' id='bulk-actions'>
                                                                <option value='av_actions' disabled selected hidden>{{ trans('langActions') }}</option>
                                                                <option value='move'>{{ trans('langMove') }}</option>
                                                                <option value='delete'>{{ trans('langDelete') }}</option>
                                                                <option value='visible'>{{ trans('langNewBBBSessionStatus') }}: {{ trans('langVisible') }}</option>
                                                                <option value='invisible'>{{ trans('langNewBBBSessionStatus') }}: {{ trans('langInvisible') }}</option>
                                                            </select>

                                                            <div class='panel-move d-none'>
                                                                {!! $group_hidden_input !!}
                                                                <div class='form-group mt-4'>
                                                                    <label for='moveTo' class='col-sm-12 control-label-notes'>{{ trans('langMove') }} {{ trans('langTo') }}:</label>
                                                                    <div class='col-12 moveToDiv'>
                                                                        {{-- directories load from ajax --}}
                                                                    </div>
                                                                </div>
                                                                {!! generate_csrf_token_form_field() !!}
                                                            </div>

                                                            <div class='d-flex justify-content-end align-items-center gap-2 mt-4'>
                                                                <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                                                <input type='submit' class='btn btn-submit submitAdminBtn' name='bulk_submit' value='{{ trans('langSubmit') }}'>
                                                                <input type='hidden' id='selectedcbids' name='selectedcbids' value=''>
                                                                <input type='hidden' id='filepaths' name='filepaths' value=''>
                                                                <input type='hidden' id='source_path' name='source_path' value=''>
                                                            </div>

                                                        </form>
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
                                            <th style='width:5%;' class='checkbox_th d-none' aria-label='{{ trans('langIcon') }}'></th>
                                            <th style='width:50%;'>{!! headlink(trans('langName'), 'name') !!}</th>

                                            <th style='width:15%;'>{{ trans('langSize') }}</th>
                                            <th style='width:15%;'>{!! headlink(trans('langDate'), 'date') !!}</th>

                                            @unless ($is_in_tinymce)
                                                <th style='width:10%;' aria-label='{{ trans('langCommands') }}'>{!! icon('fa-cogs', trans('langCommands')) !!}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>

                                    @forelse ($fileInfo as $file)

                                        @if($file->visible == 1 or $can_upload)
                                            <tr class="{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}">
                                                <td class='text-center checkbox_td d-none'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect')}}">
                                                            <input type='checkbox' isDir='{{$file->is_dir}}' filepath='{{$file->path}}' cbid='{{$file->id}}' value='{{$file->id}}'>
                                                            <span class='checkmark'></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class='fileURL-th' style='width:50%;'>
                                                    <input type='hidden' value='{!! $base_url !!}download={{ getIndirectReference($file->path) }}'>

                                                    <div class='d-flex justify-content-start align-items-start gap-3'>
                                                        @if($file->visible == 1)
                                                            @if ($file->is_dir)
                                                                <span class='visibleFile file-icon'>{!! icon('fa-regular fa-folder-open', trans('langDirectory')) !!} </span>
                                                            @else
                                                                <span class='visibleFile file-icon'>{!! icon(choose_image('.' . $file->format), trans('langFileName') . " " . $file->format) !!} </span>
                                                            @endif
                                                        @else
                                                            @if ($file->is_dir)
                                                                <span class='invisibleFile file-icon'>{!! icon('fa-regular fa-folder-open', trans('langDirectory')) !!} </span>
                                                            @else
                                                                <span class='invisibleFile file-icon'>{!! icon(choose_image('.' . $file->format), trans('langFileName') . " " . $file->format) !!} </span>
                                                        @endif
                                                        @endif

                                                        @if ($file->is_dir)
                                                            <a class="fileURL-link @if(!$file->visible) opacity-50 @endif" href='{!! $file->url !!}'>{{ $file->filename }}</a>
                                                        @else
                                                            @if(get_config('enable_prevent_download_url') && $file->format == 'pdf' && $file->prevent_download == 1)
                                                                <a class='fileURL-link' href="{{ $urlAppend }}main/prevent_pdf.php?urlPr={{ urlencode($file->url) }}" target="_blank">{{ $file->title !== ''? $file->title: $file->filename }}</a>
                                                                {!! icon('fa-shield', trans('langDownloadPdfNotAllowed')) !!}
                                                            @else
                                                                {!! $file->link !!}
                                                            @endif

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
                                                            <span>{!! copyright_info($file->id, 1, 'documents') !!}</span>
                                                        @endif


                                                        @if($file->updated_message)
                                                            @if($file->visible == 1)
                                                                <span class="badge bg-success">{{ $file->updated_message }}</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ $file->updated_message }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    @if ($file->comment)
                                                        <div class='comment text-muted mt-1'>
                                                            <small>
                                                                {!! nl2br(e($file->comment)) !!}
                                                            </small>
                                                        </div>
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
        var downloadURL = $(this).closest('tr').find('input[type=hidden]').first().val();
        var fileTitle = $(this).attr('title');

        // BUTTONS declare
        var bts = {
            download: {
                label: '<i class="fa fa-download"></i> {{ trans('langDownload') }}',
                className: 'submitAdminBtn gap-1',
                callback: function (d) {
                    var anchor = document.createElement('a');
                    anchor.href = downloadURL;
                    anchor.target = '_blank';
                    anchor.download = fileTitle;
                    anchor.click();
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
            onEscape: function() {},
            backdrop: true,
            message: '<div class="row">'+
                        '<div class="col-12">'+
                            '<div class="iframe-container" style="height:500px;"><iframe title="'+fileTitle+'" id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: bts
        });
    });

</script>

@endsection
