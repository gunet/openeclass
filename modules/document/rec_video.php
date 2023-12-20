<?php

$require_login = true;
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'documents';
$helpSubTopic = 'rec_video';
require_once '../../include/baseTheme.php';

if (!get_config('allow_rec_video')) {
    redirect_to_home_page();
}

$toolName = $langUploadRecVideo;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langDoc);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php?course=$course_code",
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )));

$tool_content .= "
    <div class='form-wrapper'>
        <div class='form-group'>
            <button class='btn btn-success' id='button-open-camera'>$langOpenCamera</button>
            <button class='btn btn-info' id='button-start-recording' disabled>$langStart</button>
            <button class='btn btn-danger' id='button-stop-recording' disabled>$langStopRecording</button>
            <button class='btn btn-success' id='button-download-recording' disabled>$langSaveInDoc</button>
        </div>
        <span class='help-block'>$langMaxRecVideoTime</span>
    </div>
    <div class='form-wrapper'>
        <video controls autoplay playsinline></video>
    </div>
    <script src='{$urlAppend}node_modules/recordrtc/RecordRTC.min.js'></script>
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
                    title: '" . js_escape($langEnterFile) . "',
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

                        var upload_url = '{$urlAppend}modules/document/index.php?course=$course_code';

                        $.ajax({
                                url: upload_url,
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                type: 'POST'
                            })
                        .done(function(data) {
                            window.location.href = '{$urlAppend}modules/document/index.php?course=$course_code';
                        })
                    }
                });
            };
        })
    </script>";

draw($tool_content, 1, null, $head_content);
