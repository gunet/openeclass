@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }}  @if($course_code) module-container py-lg-0 @else main-container @endif'>
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
                            @include('layouts.partials.show_alert') 

                            <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                    <div class='col-12'>
                                        <div class='form-wrapper form-edit'>
                                            <div class='col-12 d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                <button class='btn submitAdminBtnDefault' id='button-start-recording'>{{ trans('langStart') }}</button>
                                                <button class='btn deleteAdminBtn' id='button-stop-recording' disabled>{{ trans('langStopRecording') }}</button>
                                                <button class='btn submitAdminBtn' id='button-release-microphone' disabled>{{ trans('langReleaseMic') }}</button>
                                                <button class='btn submitAdminBtnDefault' id='button-download-recording' disabled>{{ trans('langSaveInDoc') }}</button>
                                            </div>
                                            <div class='col-12 d-flex justify-content-start align-items-center mt-2'>
                                                <span class='help-block'>{{ trans('langMaxRecAudioTime') }}</span>
                                            </div>
                                            <div class='col-12 d-flex justify-content-start align-item-center mt-4'>
                                                <audio controls autoplay playsinline></audio>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
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
            var audio = document.querySelector('audio');

            function captureMicrophone(callback) {
                btnReleaseMicrophone.disabled = false;

                if(microphone) {
                    callback(microphone);
                    return;
                }

                if(typeof navigator.mediaDevices === 'undefined' || !navigator.mediaDevices.getUserMedia) {
                    alert('This browser does not supports WebRTC getUserMedia API.');

                    if(!!navigator.getUserMedia) {
                        alert('This browser seems supporting deprecated getUserMedia API.');
                    }
                }

                navigator.mediaDevices.getUserMedia({
                    audio: isEdge ? true : {
                        echoCancellation: false
                    }
                }).then(function(mic) {
                    callback(mic);
                }).catch(function(error) {
                    alert('Unable to capture your microphone. Please check console logs.');
                    console.error(error);
                });
            }

            function replaceAudio(src) {
                var newAudio = document.createElement('audio');
                newAudio.controls = true;
                newAudio.autoplay = true;

                if(src) {
                    newAudio.src = src;
                }

                var parentNode = audio.parentNode;
                parentNode.innerHTML = '';
                parentNode.appendChild(newAudio);

                audio = newAudio;
            }

            function stopRecordingCallback() {
                replaceAudio(URL.createObjectURL(recorder.getBlob()));

                btnStartRecording.disabled = false;

                setTimeout(function() {
                    if(!audio.paused) return;

                    setTimeout(function() {
                        if(!audio.paused) return;
                        audio.play();
                    }, 1000);

                    audio.play();
                }, 300);

                audio.play();

                btnDownloadRecording.disabled = false;

                if(isSafari) {
                    click(btnReleaseMicrophone);
                }
            }

            var isEdge = navigator.userAgent.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob);
            var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

            var recorder; // globally accessible
            var microphone;

            var btnStartRecording = document.getElementById('button-start-recording');
            var btnStopRecording = document.getElementById('button-stop-recording');
            var btnReleaseMicrophone = document.querySelector('#button-release-microphone');
            var btnDownloadRecording = document.getElementById('button-download-recording');

            btnStartRecording.onclick = function() {
                this.disabled = true;
                this.style.border = '';
                this.style.fontSize = '';

                if (!microphone) {
                    captureMicrophone(function(mic) {
                        microphone = mic;

                        if(isSafari) {
                            replaceAudio();

                            audio.muted = true;
                            audio.srcObject = microphone;

                            btnStartRecording.disabled = false;
                            btnStartRecording.style.border = '1px solid red';
                            btnStartRecording.style.fontSize = '150%';

                            alert('Please click startRecording button again. First time we tried to access your microphone. Now we will record it.');
                            return;
                        }

                        click(btnStartRecording);
                    });
                    return;
                }

                replaceAudio();

                audio.muted = true;
                audio.srcObject = microphone;

                var options = {
                    type: 'audio',
                    numberOfAudioChannels: isEdge ? 1 : 2,
                    checkForInactiveTracks: true,
                    bufferSize: 16384,
                    audioBitsPerSecond: 128000,
                    disableLogs: true,
                };

                if(isSafari || isEdge) {
                    options.recorderType = StereoAudioRecorder;
                }

                if(navigator.platform && navigator.platform.toString().toLowerCase().indexOf('win') === -1) {
                    options.sampleRate = 48000; // or 44100 or remove this line for default
                }

                if(isSafari) {
                    options.sampleRate = 44100;
                    options.bufferSize = 4096;
                    options.numberOfAudioChannels = 2;
                }

                if(recorder) {
                    recorder.destroy();
                    recorder = null;
                }

                recorder = RecordRTC(microphone, options);
                // max duration recording = 5 min
                recorder.setRecordingDuration(300000).onRecordingStopped(stopRecordingCallback);
                recorder.startRecording();

                btnStopRecording.disabled = false;
                btnDownloadRecording.disabled = true;
            };

            btnStopRecording.onclick = function() {
                this.disabled = true;
                recorder.stopRecording(stopRecordingCallback);
            };

            btnReleaseMicrophone.onclick = function() {
                this.disabled = true;
                btnStartRecording.disabled = false;

                if(microphone) {
                    microphone.stop();
                    microphone = null;
                }

                if(recorder) {
                    /* click(btnStopRecording); */
                }
            };

            btnDownloadRecording.onclick = function() {

                bootbox.prompt({
                    title: '{{ js_escape(trans('langEnterFile')) }}',
                    callback: function(result) {
                        this.disabled = true;
                        if(!recorder || !recorder.getBlob()) return;

                        if(isSafari) {
                            var recfilename = result + '.mp3';
                            recorder.getDataURL(function(dataURL) {
                                SaveToDisk(dataURL, recfilename);
                            });
                            return;
                        }

                        var blob = recorder.getBlob();
                        var recfilename = result + '.mka';
                        var file = new File([blob], recfilename, {
                            mimeType: 'audio/webm'
                        });

                        var formData = new FormData();
                        // recorded data
                        formData.append('audio-blob', file);
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
                                window.location.href = '{{ $urlServer }}modules/document/index.php?course={{ $course_code }}';
                            })
                    }
                });

            };

            function click(el) {
                el.disabled = false; // make sure that element is not disabled
                var evt = document.createEvent('Event');
                evt.initEvent('click', true, true);
                el.dispatchEvent(evt);
            }

            function SaveToDisk(fileURL, fileName) {
                // for non-IE
                if (!window.ActiveXObject) {
                    var save = document.createElement('a');
                    save.href = fileURL;
                    save.download = fileName || 'unknown';
                    save.style = 'display:none;opacity:0;color:transparent;';
                    (document.body || document.documentElement).appendChild(save);

                    if (typeof save.click === 'function') {
                        save.click();
                    } else {
                        save.target = '_blank';
                        var event = document.createEvent('Event');
                        event.initEvent('click', true, true);
                        save.dispatchEvent(event);
                    }

                    (window.URL || window.webkitURL).revokeObjectURL(save.href);
                }

                // for IE
                else if (!!window.ActiveXObject && document.execCommand) {
                    var _window = window.open(fileURL, '_blank');
                    _window.document.close();
                    _window.document.execCommand('SaveAs', true, fileName || fileURL)
                    _window.close();
                }
            }
        })
    </script>

@endsection
