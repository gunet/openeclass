<?php

require_once 'answer.class.php';

class OralAnswer extends QuestionType
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
        global $langOral, $langStart, $head_content,
               $langStopRecording, $urlAppend, $langMaxRecAudioTimeTmp, $langminutes,
               $langMaxRecAudioTimeInExericses, $course_code, $urlServer,
               $course_id, $langCancel, $langAnalyticsConfirm,
               $langDeleteRecordingOk, $langListenToRecordingAudio,
               $langFileUploadingOkReplaceWithNew, $eurid, $langConfirmDelete;


        $questionId = $this->question_id;
        $html_content = '';
        $url = '';
        $filename = '';
        $filenameRecording = '';
        $displayItems = 'd-none';
        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            $filenameRecording = $exerciseResult[$questionId];
            $filenameWithoutExtension = str_replace('.mp3', '', $exerciseResult[$questionId]);
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

            $displayItems = 'd-block';
        }

        // Calculate the duration of the exercise to add it to the maximum recorded audio range.
        $diffMinutes = '';
        $milliseconds = 1200000; // 20 min
        $ex_res = Database::get()->querySingle("SELECT `start_date`,`end_date` FROM exercise 
                                                WHERE course_id = ?d 
                                                AND id IN (SELECT eid FROM exercise_user_record WHERE eurid = ?d)", $course_id, $eurid);

        if ($ex_res && !is_null($ex_res->start_date) && !is_null($ex_res->end_date)) {
            $dt1 = new DateTime($ex_res->start_date);
            $dt2 = new DateTime($ex_res->end_date);
            if ($dt1->format('Y-m-d') === $dt2->format('Y-m-d') && $dt2->getTimestamp() > $dt1->getTimestamp()) {
                // Calculate the difference in seconds
                $diffSeconds = abs($dt1->getTimestamp() - $dt2->getTimestamp());
                // Convert seconds to minutes
                $diffMinutes = $diffSeconds / 60;
                $diffMinutes = $diffMinutes - 1; // One minute before the exercise expires.
                $milliseconds = $diffMinutes * 60000;
            }
        }



        $html_content .= "
        <ul class='nav nav-tabs' id='myTab_{$questionId}' role='tablist'>
            <li class='nav-item' role='presentation'>
                <button class='nav-link active' id='oral-tab_{$questionId}' data-bs-toggle='tab' data-bs-target='#oral_{$questionId}' type='button' role='tab' aria-controls='oral_{$questionId}' aria-selected='false'>$langOral</button>
            </li>
        </ul>
        <div class='tab-content fade mt-4' id='myTabContent_{$questionId}'>
            <div class='tab-pane fade show active' id='oral_{$questionId}' role='tabpanel' aria-labelledby='oral-tab_{$questionId}'>
                <input type='hidden' name='choice[$questionId]' id='hidden-recording-{$questionId}' value='{$filenameRecording}'>
                <div class='col-12 d-flex gap-3'>
                    <button class='btn submitAdminBtnDefault' id='button-start-recording-{$questionId}'>$langStart</button>                    
                    <button class='btn deleteAdminBtn' id='button-stop-recording-{$questionId}' disabled>$langStopRecording</button>                    
                </div>
                <div class='col-12 d-flex justify-content-start align-items-center mt-2'>
                    <span class='help-block'>" . ($milliseconds == 1200000 ? $langMaxRecAudioTimeInExericses : $langMaxRecAudioTimeTmp.$diffMinutes.' '.$langminutes) . "</span>
                </div>
                <div class='col-12 d-flex justify-content-start align-item-center mt-4'>
                    <audio class='audio-{$questionId}' controls autoplay playsinline></audio>
                </div>";
$html_content .= "<div id='recording_file_container_{$questionId}' class='col-12 $displayItems d-flex align-items-center gap-3 mt-4'>
                    <span>$langListenToRecordingAudio</span>
                    <a id='filename-link-{$questionId}' class='TextBold' href='#' data-bs-toggle='modal' data-bs-target='#audioModal_{$questionId}'>($question_number) $filename</a>
                    <a id='deleteRecording-{$questionId}' class='deleteRecording' data-id='{$questionId}' onclick='updateListenerDeleteOral({$question_number}, {$questionId})'><i class='fa-solid fa-circle-xmark fa-lg Accent-200-cl'></i></a>
                    <div class='modal fade' id='audioModal_{$questionId}' tabindex='-1' aria-labelledby='audioModalLabel_{$questionId}'>
                        <div class='modal-dialog modal-dialog-centered'>
                            <div class='modal-content'>
                                <div class='modal-body'>
                                    <audio id='audio_{$questionId}' controls>";
                                    if (!empty($url)) {
                                        $html_content .= "<source id = 'audioSource_{$questionId}' src = " . htmlspecialchars($url) . " type = 'audio/mpeg'>";
                                    }
                                    $html_content .= "</audio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
$html_content .= "</div>
        </div>";

        $head_content .= "
        <style>.fade:not(.show) {opacity: 1;}</style>
        <script src='{$urlAppend}js/recordrtc/RecordRTC.min.js'></script>
        <script type='text/javascript'>
            $(document).ready(function() {

                $('#deleteRecording-{$questionId}').on('click', function () {
                    if (confirm('" . js_escape($langConfirmDelete) . "')) {
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
                            alert('" . js_escape($langDeleteRecordingOk) . "');

                            // Set empty the src of current audio and load it again.
                            $('body').find('#audioSource_{$questionId}').attr('src', '');
                            $('#audio_{$questionId}')[0].load();

                            // Hide recording link and change its value.
                            $('#recording_file_container_{$questionId}').removeClass('d-block').addClass('d-none');
                            $('#hidden-recording-{$questionId}').val('');
                        });
                    }
                });
                

                var audio = document.querySelector('audio.audio-{$questionId}');
                function captureMicrophone(callback) {                                        
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
                
                btnStartRecording.onclick = function() {
                    this.disabled = true;
                    this.style.border = '';
                    this.style.fontSize = '';
                    //saveBtn.disabled = true;
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
                    // max duration recording = 20 min
                    recorder.setRecordingDuration($milliseconds).onRecordingStopped(stopRecordingCallback);
                    recorder.startRecording();
                    btnStopRecording.disabled = false;
                };

                function click(el) {
                    el.disabled = false; // make sure that element is not disabled
                    var evt = document.createEvent('Event');
                    evt.initEvent('click', true, true);
                    el.dispatchEvent(evt);
                }

                btnStopRecording.onclick = function(e) {
                    this.disabled = true;                    
                    recorder.stopRecording(stopRecordingCallback);                
                                                
                    e.preventDefault();

                    bootbox.confirm({
                        message: '$langFileUploadingOkReplaceWithNew',
                        title: '<div class=\'modal-title-default text-center mb-0\'>$langAnalyticsConfirm</div>',
                        buttons: {
                            cancel: {
                                label: '$langCancel',
                                className: 'cancelAdminBtn position-center'
                            },
                            confirm: {
                                label: '$langAnalyticsConfirm',
                                className: 'submitAdminBtn position-center',
                            }
                        },
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
                                var newFilePath = data.newFilePath;
                                $('#recording_file_container_{$questionId}').removeClass('d-none').addClass('d-block');

                                // Create and load new audio sourse
                                $('body').find('#audioSource_{$questionId}').attr('src', newFilePath);
                                $('#audio_{$questionId}')[0].load();

                                // Show the recordinf link file and change its text. Disable save button after clicking it.
                                $('#filename-link-{$questionId}').text('($question_number) recording-file.mp3');
                                $('#hidden-recording-{$questionId}').val('recording-file-{$questionId}-{$eurid}.mp3');
                                $('#button-save-recording-{$questionId}').prop('disabled', true);

                                // Check the answer as answered
                                $('#qPanel{$questionId}  #qCheck{$question_number}').addClass('fa fa-check');
                                
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

        global $questionScore, $question_weight, $course_id, $course_code, $urlServer;

        $questionId = $this->question_id;
        $questionScore = $question_weight;

        $html_content = '';
        $url = '';
        $filename = '';

        $oral = $choice;
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
        return $html_content;
    }
}
