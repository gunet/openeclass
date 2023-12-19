<?php

$require_login = true;
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'documents';
$helpSubTopic = 'rec_audio';
require_once '../../include/baseTheme.php';

$toolName = $langUploadRecAudio;
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
            <button class='btn btn-default' id='button-release-microphone' disabled>$langReleaseMic</button>
            <button class='btn btn-success' id='button-download-recording' disabled>$langSaveInDoc</button>
        </div>
        <span class='help-block'>$langMaxRecAudioTime</span>
    </div>
    <div class='form-wrapper'>
        <audio controls autoplay playsinline></audio>
    </div>
    <script src='{$urlAppend}node_modules/recordrtc/RecordRTC.min.js'></script>
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
                    title: '" . js_escape($langEnterFile) . "',
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
                            window.location.href = '{$urlServer}modules/document/index.php?course=$course_code';
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
    </script>";

draw($tool_content, 1, null, $head_content);
