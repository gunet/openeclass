<?php

require_once 'answer.class.php';

class FreeTextAnswer extends QuestionType
{
    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        // TODO: Implement PreviewQuestion() method.
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langFreeText, $langOral, $langStart, $langStopRecording, $head_content, 
               $langStart, $langStopRecording, $urlAppend, $langReleaseMic, $langSaveInDoc, 
               $langMaxRecAudioTime, $course_code, $urlServer, $langEnterFile, $langSave, 
               $langSaveOralMsg, $langOk, $uid, $webDir, $course_id, $course_code, 
               $langDeleteRecordingOk, $langListenToRecordingAudio, $langFileUploadingOkReplaceWithNew, $eurid;

        $questionId = $this->question_id;
        $text = '';
        $html_content = '';
        $url = '';
        $filename = '';
        $filenameRecording = '';
        $displayItems = 'd-none';
        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            if (strpos($exerciseResult[$questionId], ':::') !== false) {
                $arr = explode(':::', $exerciseResult[$questionId]);
                if (count($arr) == 2) {
                    $text = $arr[0]; // plain text
                    $filenameRecording = $arr[1];
                    $filenameWithoutExtension = str_replace('.mp3', '', $arr[1]);
                    $tempFile = explode('-', $filenameWithoutExtension);
                    if (count($tempFile) == 4) {
                        $subSystemId = $tempFile[2];
                        $UserRecordId = $tempFile[3]; // eurid
                        $file = Database::get()->querySingle("SELECT `filename`,`path` FROM document WHERE course_id = ?d
                                                                    AND subsystem = ?d AND subsystem_id = ?d
                                                                    AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $subSystemId, $UserRecordId);
                        if ($file) {
                            $filename = $file->filename; // recording filename
                            $filePath = $file->path;
                            $url = $urlServer . "courses/$course_code/image" . $filePath;
                        }
                    }
                }
                $displayItems = 'd-block';
            } else {
                $text = $exerciseResult[$questionId];
            }
        } 

        $html_content .= "
        <ul class='nav nav-tabs' id='myTab_{$questionId}' role='tablist'>
            <li class='nav-item' role='presentation'>
                <button class='nav-link active' id='freetext-tab_{$questionId}' data-bs-toggle='tab' data-bs-target='#freetext_{$questionId}' type='button' role='tab' aria-controls='freetext_{$questionId}' aria-selected='true'>$langFreeText</button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='oral-tab_{$questionId}' data-bs-toggle='tab' data-bs-target='#oral_{$questionId}' type='button' role='tab' aria-controls='oral_{$questionId}' aria-selected='false'>$langOral</button>
            </li>
        </ul>
        <div class='tab-content fade mt-4' id='myTabContent_{$questionId}'>
            <div class='tab-pane fade show active' id='freetext_{$questionId}' role='tabpanel' aria-labelledby='freetext-tab_{$questionId}'>
                " . rich_text_editor("choice[$questionId]", 14, 90, $text, options: $options) . "
            </div>
            <div class='tab-pane fade' id='oral_{$questionId}' role='tabpanel' aria-labelledby='oral-tab_{$questionId}'>
                <input type='hidden' name='choice_recording[$questionId]' id='hidden-recording-{$questionId}' value='{$filenameRecording}'>
                <div class='col-12 d-flex gap-3'>
                    <button class='btn submitAdminBtnDefault' id='button-start-recording-{$questionId}'>$langStart</button>
                    <button class='btn submitAdminBtn' id='button-release-microphone-{$questionId}' disabled><i class='fa-solid fa-microphone-slash'></i></button>
                    <button class='btn deleteAdminBtn' id='button-stop-recording-{$questionId}' disabled>$langStopRecording</button>
                    <button class='btn successAdminBtn' id='button-save-recording-{$questionId}' disabled>$langSave</button>
                </div>
                <div class='col-12 d-flex justify-content-start align-items-center mt-2'>
                    <span class='help-block'>$langMaxRecAudioTime</span>
                </div>
                <div class='col-12 d-flex justify-content-start align-item-center mt-4'>
                    <audio class='audio-{$questionId}' controls autoplay playsinline></audio>
                </div>";
$html_content .= "<div id='recording_file_container_{$questionId}' class='col-12 $displayItems d-flex align-items-center gap-3 mt-4'>
                    <span>$langListenToRecordingAudio</span>
                    <a id='filename-link-{$questionId}' class='TextBold' href='#' data-bs-toggle='modal' data-bs-target='#audioModal_{$questionId}'>($question_number) $filename</a>
                    <a id='deleteRecording-{$questionId}' class='deleteRecording' data-id='{$questionId}'><i class='fa-solid fa-circle-xmark fa-lg Accent-200-cl'></i></a>
                    <div class='modal fade' id='audioModal_{$questionId}' tabindex='-1' aria-labelledby='audioModalLabel_{$questionId}'>
                        <div class='modal-dialog modal-dialog-centered'>
                            <div class='modal-content'>
                                <div class='modal-body'>
                                    <audio id='audio_{$questionId}' controls>
                                        <source id='audioSource_{$questionId}' src=" . htmlspecialchars($url) . " type='audio/mpeg'>
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
$html_content .= "</div>
        </div>";

        $head_content .= "
        <style>.fade:not(.show) {opacity: 1;}</style>
        <script src='{$urlAppend}node_modules/recordrtc/RecordRTC.min.js'></script>
        <script type='text/javascript'>
            $(document).ready(function() {

                $('#deleteRecording-{$questionId}').on('click', function (){
                    var qID = $(this).data('id');
                    var deleteData = new FormData();
                    deleteData.append('delete-recording', qID);
                    var del_url = '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&eurid={$eurid}';
                    $.ajax({
                        url: del_url,
                        data: deleteData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        type: 'POST'
                    }).done(function(data) {
                        alert('$langDeleteRecordingOk');

                        // Set empty the src of current audio and load it again.
                        $('body').find('#audioSource_{$questionId}').attr('src', '');
                        $('#audio_{$questionId}')[0].load();

                        // Hide recording link and change its value.
                        $('#recording_file_container_{$questionId}').removeClass('d-block').addClass('d-none');
                        $('#hidden-recording-{$questionId}').val('');
                    })

                });
                

                var audio = document.querySelector('audio.audio-{$questionId}');
                function captureMicrophone(callback) {
                    btnReleaseMicrophone.disabled = false;
                    $('#button-release-microphone-{$questionId} i').removeClass('fa-solid fa-microphone-slash').addClass('fa-solid fa-microphone');
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
                    if(isSafari) {
                        click(btnReleaseMicrophone);
                    }
                }

                var isEdge = navigator.userAgent.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob);
                var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                var recorder; // globally accessible
                var microphone;
                var btnStartRecording = document.getElementById('button-start-recording-{$questionId}');
                var btnStopRecording = document.getElementById('button-stop-recording-{$questionId}');
                var btnReleaseMicrophone = document.querySelector('#button-release-microphone-{$questionId}');
                var saveBtn = document.querySelector('#button-save-recording-{$questionId}');

                btnStartRecording.onclick = function() {
                    this.disabled = true;
                    this.style.border = '';
                    this.style.fontSize = '';
                    saveBtn.disabled = true;
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
                };

                btnStopRecording.onclick = function() {
                    this.disabled = true;
                    saveBtn.disabled = false;
                    recorder.stopRecording(stopRecordingCallback);
                };

                btnReleaseMicrophone.onclick = function() {
                    this.disabled = true;
                    $('#button-release-microphone-{$questionId} i').removeClass('fa-solid fa-microphone').addClass('fa-solid fa-microphone-slash');
                    btnStartRecording.disabled = false;

                    if(microphone) {
                        microphone.stop();
                        microphone = null;
                    }

                    if(recorder) {
                        /* click(btnStopRecording); */
                    }
                };

                function click(el) {
                    el.disabled = false; // make sure that element is not disabled
                    var evt = document.createEvent('Event');
                    evt.initEvent('click', true, true);
                    el.dispatchEvent(evt);
                }

                saveBtn.onclick = function(e) {

                    e.preventDefault();

                    bootbox.confirm({
                        message: '$langSaveOralMsg',
                        callback: function(result) {
                            if (!result) {
                                // User clicked Cancel, do nothing
                                return;
                            }

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
                            // for question id
                            formData.append('questionId', $questionId);

                            var save_url = '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&eurid={$eurid}';

                            $.ajax({
                                url: save_url,
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                type: 'POST',
                                dataType: 'json' // Expect JSON response
                            }).done(function(data) {
                                alert('$langFileUploadingOkReplaceWithNew');
                                var newFilePath = data.newFilePath;
                                $('#recording_file_container_{$questionId}').removeClass('d-none').addClass('d-block');

                                // Create and load new audio sourse
                                $('body').find('#audioSource_{$questionId}').attr('src', newFilePath);
                                $('#audio_{$questionId}')[0].load();

                                // Show the recordinf link file and change its text. Disable save button after clicking it.
                                $('#filename-link-{$questionId}').text('($question_number) recording-file.mp3');
                                $('#hidden-recording-{$questionId}').val('recording-file-{$questionId}-{$eurid}.mp3');
                                $('#button-save-recording-{$questionId}').prop('disabled', true);
                            })
                        }
                    });

                };
            })
        </script>";

        $html_content .= "</div>"; 

        return $html_content;
    }


    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $questionScore, $question_weight, $course_id, $course_code, $uid, $urlServer, $is_editor;

        $questionId = $this->question_id;
        $questionScore = $question_weight;
        
        $html_content = '';
        $text = '';
        $oral = '';
        $recording = '';
        $url = '';
        $filename = '';

        if (strpos($choice, '::') !== false) { // choice contains oral answer apart from text
            $user_answer = explode('::', $choice);
            if (count($user_answer) == 1) { // only oral
                $oral = $user_answer[0];
            } elseif (count($user_answer) == 2) { // oran and text together
                $text = $user_answer[0];
                $oral = $user_answer[1];
            }
            $filename_without_extension = str_replace('.mp3', '', $oral);
            $user_recording = explode('-', $filename_without_extension);
            if (count($user_recording) == 4) {
                $eurID = $user_recording[3];
                $userfile = Database::get()->querySingle("SELECT `path`,`filename` FROM document 
                                                          WHERE course_id = ?d AND subsystem = ?d 
                                                          AND subsystem_id = ?d AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $questionId, $eurID);
                $url = $urlServer. "courses/$course_code/image" . ($userfile->path ?? '');
                $filename = $userfile->filename ?? '';
            }
            if (!empty($text)) {
                $html_content .= "<tr><td>" . purify($text). "</td></tr>";
            }
            if (!empty($oral) && !empty($url) && !empty($filename)) {
                $html_content .= "<tr><td><a id='recording-link-{$questionId}' class='TextBold' href='#' data-bs-toggle='modal' data-bs-target='#recording_AudioModal_{$questionId}'>$filename</a></td></tr>";
                $html_content .= "<div class='modal fade' id='recording_AudioModal_{$questionId}' tabindex='-1'>
                                    <div class='modal-dialog modal-dialog-centered'>
                                        <div class='modal-content'>
                                            <div class='modal-body'>
                                                <audio controls>
                                                    <source src=" . htmlspecialchars($url) . " type='audio/mpeg'>
                                                </audio>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
            }
        } else {
            $text = $choice; // plain text
            $html_content .= "<tr><td>" . purify($text). "</td></tr>";
        }

        return $html_content;

    }
}
