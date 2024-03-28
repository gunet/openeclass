@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }}  @if($course_code) module-container py-lg-0 @else main-container @endif'>
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
                                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                                    @if($course_code)
                                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                            <div class="offcanvas-header">
                                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                            </div>
                                        </div>
                                    @endif
                                    @include('layouts.partials.legend_view')
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
                                    {!! $backButton !!}
                                
                                    <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                        <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                            <div class='col-12'>
                                                <div class='form-wrapper form-edit'>
                                                    <div class='col-12 d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                        <button class='btn submitAdminBtnDefault' id='button-open-camera'>{{ trans('langOpenCamera') }}</button>
                                                        <button class='btn submitAdminBtn' id='button-start-recording' disabled>{{ trans('langStart') }}</button>
                                                        <button class='btn deleteAdminBtn' id='button-stop-recording' disabled>{{ trans('langStopRecording') }}</button>
                                                        <button class='btn submitAdminBtnDefault' id='button-download-recording' disabled>{{ trans('langSaveInDoc') }}</button>
                                                    </div>
                                                    <div class='col-12 d-flex justify-content-start align-items-center mt-2'>
                                                        <span class='help-block'>{{ trans('langMaxRecVideoTime') }}</span>
                                                    </div>
                                                    <div class='col-12 d-flex justify-content-start align-item-center mt-4'>
                                                        <video controls autoplay playsinline></video>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
            </div>
        </div>
    </div>

    <script src='{{ $urlAppend }}node_modules/recordrtc/RecordRTC.min.js'></script>
    <script type='text/javascript'>
        $(document).ready(function() {

            var video = document.querySelector('video');
            var recorder;
            var video_camera;
            var btnOpenCamera = document.getElementById('button-open-camera');
            var btnStartRecording = document.getElementById('button-start-recording');
            var btnStopRecording = document.getElementById('button-stop-recording');
            var btnDownloadRecording = document.getElementById('button-download-recording');

            btnOpenCamera.onclick = function() {
                captureCamera(function(camera) {
                    video_camera = camera;
                    video.muted = true;
                    video.volume = 0;
                    video.srcObject = camera;
                });
                btnStartRecording.disabled = false;
            }

            function captureCamera(callback) {
                navigator.mediaDevices.getUserMedia({ audio: true, video: true }).then(function(camera) {
                    callback(camera);
                }).catch(function(error) {
                    alert('Unable to capture your camera. Please check console logs.');
                    console.error(error);
                });
            }

            btnStartRecording.onclick = function() {
                this.disabled = true;
                recorder = RecordRTC(video_camera, {
                    type: 'video',
                    audioBitsPerSecond: 128000,
                    videoBitsPerSecond: 2097152, // 2 MBps
                    disableLogs: true,
                });

                // max duration recording = 2 min
                recorder.setRecordingDuration(120000).onRecordingStopped(stopRecordingCallback);
                recorder.startRecording();

                // release camera on stopRecording
                recorder.camera = video_camera;

                btnStopRecording.disabled = false;
                btnOpenCamera.disabled = true;
                btnDownloadRecording.disabled = true;
            };

            btnStopRecording.onclick = function() {
                this.disabled = true;
                recorder.stopRecording(stopRecordingCallback);
            };

            function stopRecordingCallback() {

                btnDownloadRecording.disabled = false;

                video.src = video.srcObject = null;
                video.muted = false;
                video.volume = 1;
                video.src = URL.createObjectURL(recorder.getBlob());
                recorder.camera.stop();
            }

            btnDownloadRecording.onclick = function() {

                bootbox.prompt({
                    title: '{{ js_escape(trans('langEnterFile')) }}',
                    callback: function(result) {
                        this.disabled = true;
                        if(!recorder || !recorder.getBlob()) return;

                        var blob = recorder.getBlob();
                        var recfilename = result + '.webm';
                        var file = new File([blob], recfilename, {
                            mimeType: 'video/webm'
                        });

                        var formData = new FormData();
                        // recorded data
                        formData.append('video-blob', file);
                        // file name
                        formData.append('userFile', file.name);

                        var upload_url = '{{ $urlAppend }}modules/document/index.php?course={{ $course_code }}';

                        $.ajax({
                            url: upload_url,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            type: 'POST'
                        })
                        .done(function(data) {
                            window.location.href = '{{ $urlAppend }}modules/document/index.php?course={{ $course_code }}';
                        })
                    }
                });
            };
        })
    </script>

@endsection

