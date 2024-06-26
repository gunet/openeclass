@extends('layouts.default')

@section('content')


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
                    
                    {!! $action_bar !!}

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



                    <div class='col-12'>
                        @if(count($docs) > 0)
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead>
                                        <tr>
                                            <th>{{ trans('langName') }}</th>
                                            <th>{{ trans('langFrom') }}</th>
                                            <th>{{ trans('langReferencedObject') }}</th>
                                            <th>{{ trans('langType') }}</th>
                                            <th>{{ trans('langDate') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($docs as $doc)
                                            <tr>
                                                <td>{!! $doc->link !!}</td>
                                                <td>{{ $doc->creator }}</td>
                                                <td>{{ $doc->refers_to }}</td>
                                                <td>{{ $doc->format }}</td>
                                                <td>{{ format_locale_date(strtotime($doc->date), 'short') }}</td>
                                                <td class='text-end'>
                                                {!! 
                                                    action_button(array(
                                                        array(
                                                            'title' => trans('langDownload'),
                                                            'url' => $doc->download_url,
                                                            'icon' => 'fa-download',
                                                            'icon-class' => 'download-doc'
                                                            
                                                        ),
                                                        array('title' => trans('langDelete'),
                                                            'url' => '#',
                                                            'icon' => 'fa-xmark',
                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#docDelete{$doc->id}'",
                                                            'icon-class' => 'doc-delete')
                                                    ))
                                                !!}
                                                </td>
                                            </tr>


                                            <div class='modal fade' id='docDelete{{ $doc->id }}' tabindex='-1' aria-labelledby='docDeleteLabel{{ $doc->id }}' aria-hidden='true'>
                                                <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $sessionID }}&del={{ $doc->id }}">
                                                    <div class='modal-dialog modal-md'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <div class='modal-title'>
                                                                    <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                                                                    <h3 class="modal-title-default text-center mb-0 mt-2" id="docDeleteLabel{{ $doc->id }}">{!! trans('langDelete') !!}</h3>
                                                                </div>
                                                            </div>
                                                            <div class='modal-body text-center'>
                                                                {{ trans('langContinueToDelSession') }}
                                                            </div>
                                                            <div class='modal-footer d-flex justify-content-center align-items-center'>
                                                                <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn deleteAdminBtn">
                                                                    {{ trans('langDelete') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoInfoAvailable') }}</span>
                            </div>
                        @endif
                     
                    </div>

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
