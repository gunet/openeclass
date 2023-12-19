<?php

$require_login = true;
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'documents';
$helpSubTopic = 'rec_video';

require_once '../../include/baseTheme.php';

$head_content .= "<script src='https://www.WebRTC-Experiment.com/RecordRTC.js'></script>";

$head_content .= "
<script type='text/javascript'>
    $(document).ready(function() {
        var video = document.querySelector('video');        
        
        function captureCamera(callback) {
            navigator.mediaDevices.getUserMedia({ audio: true, video: true }).then(function(camera) {
                callback(camera);
            }).catch(function(error) {
                alert('Unable to capture your camera. Please check console logs.');
                console.error(error);
            });
        }                                
        
        function stopRecordingCallback() {            
                                                                
            btnDownloadRecording.disabled = false;
                                                                   
            video.src = video.srcObject = null;
            video.muted = false;
            video.volume = 1;
            video.src = URL.createObjectURL(recorder.getBlob());
            
            recorder.camera.stop();            
        }
                        
        var recorder; // globally accessible                
        
        var btnStartRecording = document.getElementById('button-start-recording');
        var btnStopRecording = document.getElementById('button-stop-recording');        
        var btnDownloadRecording = document.getElementById('button-download-recording');
                       
        btnStartRecording.onclick = function() {
            
            this.disabled = true;
            captureCamera(function(camera) {
                video.muted = true;
                video.volume = 0;
                video.srcObject = camera;            
        
                recorder = RecordRTC(camera, {
                    type: 'video',
                    audioBitsPerSecond: 128000,
                    videoBitsPerSecond: 2097152, // 2 MBps
                    disableLogs: true,
                });
        
                // max duration recording = 2 min
                recorder.setRecordingDuration(120000).onRecordingStopped(stopRecordingCallback);
                recorder.startRecording();
        
                // release camera on stopRecording
                recorder.camera = camera;
        
                btnStopRecording.disabled = false;
                btnDownloadRecording.disabled = true;
            });
                        
        };
        
        btnStopRecording.onclick = function() {
            this.disabled = true;
            recorder.stopRecording(stopRecordingCallback);
        };
                        
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
                    
                    var upload_url = '" . $urlServer . "/modules/document/index.php?course=" . $course_code . "';
                    
                    $.ajax({
                            url: upload_url,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            type: 'POST'
                        })
                    .done(function(data) {
                        window.location.href = '" . $urlServer . "/modules/document/index.php?course=" . $course_code . "';
                    })
                }
            });
        };
    })
</script>";

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
            <button class='btn btn-success' id='button-start-recording'>$langStart</button>
            <button class='btn btn-danger' id='button-stop-recording' disabled>$langPause</button>
            <button class='btn btn-success' id='button-download-recording' disabled>$langSaveInDoc</button>
        </div>
        <span class='help-block'>$langMaxRecVideoTime</span>
    </div>
    <div class='form-wrapper'>
        <video controls autoplay playsinline></video>
    </div>
";

draw($tool_content, 1, null, $head_content);

