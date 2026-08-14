<?php

require_once 'answer.class.php';

class UploadFileAnswer extends QuestionType
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
        global $webDir, $urlAppend, $language, $course_code, $uid, 
               $head_content, $urlServer, $course_id, $langDelete, 
               $langAnswer, $langConfirmDeletePermantly, $eurid;
            
        $exerciseId = intval($_GET['exerciseId']) ?? 0;
        $questionId = $this->question_id;
        $token = $_SESSION['csrf_token'];
        $fileLink = '';
        $html_content = '';
        $uploadedFileName = '';
        $uploadedFilePath = '';
        $oldFilePath = '';
        $oldFileId = 0;

        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            $uploadedFilePath = $exerciseResult[$questionId];
            $uploadedFile = Database::get()->querySingle("SELECT id,`filename`,`path` FROM document WHERE course_id = ?d AND subsystem = ?d AND subsystem_id = ?d AND path = ?s", $course_id, UPLOAD_FILE_QUESTION, $questionId, $uploadedFilePath);
            if ($uploadedFile && file_exists("$webDir/courses/$course_code/exercise/$uid/$exerciseId/$questionId$uploadedFile->path")) {
                $oldFileId = $uploadedFile->id;
                $oldFilePath = $uploadedFile->path;
                $uploadedFileName = $uploadedFile->filename;
                $urlLink = $urlServer . "courses/$course_code/exercise/$uid/$exerciseId/$questionId$oldFilePath";
                $fileLink .= "<div class='col-12 d-flex align-items-center gap-2 mb-4'>
                                <strong class='text-decoration-underline'>$langAnswer:</strong>
                                <a id='uploadedFile_{$questionId}' class='linkColor TextBold' target='_blank' href='{$urlLink}'>$uploadedFileName</a>
                                <a id='delFile_{$questionId}' href='#' class='Accent-200-cl' data-bs-toggle='tooltip' title='$langDelete'><i class='fa-solid fa-xmark'></i></a>
                              </div>";
            }
        }

        $head_content .= "<link href='{$urlAppend}js/bundle/uppy.min.css' rel='stylesheet'>";
        $html_content .= "<div class='form-group margin-bottom-fat'>
                            <div class='col-sm-12 margin-top-thin QuestionNumber_{$questionId}'>
                                $fileLink
                                <input type='hidden' id='choice_{$questionId}' name='choice[$questionId]' value='$uploadedFilePath'>
                                <div id='uppy_{$questionId}'></div>
                                <div class='text-success mt-4' id='answerFile_{$questionId}'></div>
                            </div>
                          </div>";

        $head_content .= "
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    let isUppyLoaded = false;

                    async function loadUppy() {
                        try {
                            console.log('Uppy loaded');
                            const { Uppy, Dashboard, XHRUpload, English, French, German, Italian, Spanish, Greek } = await import('{$urlAppend}js/bundle/uppy.js');

                            const locale_map = {
                                'de': German,
                                'el': Greek,
                                'en': English,
                                'es': Spanish,
                                'fr': French,
                                'it': Italian,
                            }

                            const uppy = new Uppy({
                                autoProceed: true,
                                restrictions: {
                                    maxFileSize: '" . parseSize(ini_get('upload_max_filesize')) . "',
                                    maxNumberOfFiles: 1,
                                }
                            })

                            uppy.use(Dashboard, {
                                target: '#uppy_{$questionId}',
                                inline: true,
                                showProgressDetails: true,
                                proudlyDisplayPoweredByUppy: false,
                                height: 500,
                                thumbnailWidth: 100,
                                locale: locale_map['{$language}'] || English,
                                hideUploadButton: true
                            });

                            uppy.use(XHRUpload, {
                                endpoint: '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&exerciseId={$exerciseId}&questionId={$questionId}&u={$uid}&token={$token}&oldFilePath={$oldFilePath}',
                                fieldName: 'new_upload_file',
                                formData: true,
                                getResponseData: (responseText, response) => {
                                    try {
                                        const data = JSON.parse(responseText.responseText);
                                        if (data.success) {
                                            $.ajax({
                                                url: '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&exerciseId={$exerciseId}',
                                                method: 'POST',
                                                data: { file_uploaded_done: 1, file_name: data.fileInfo.basename, file_path: data.filePath, question_id: $questionId, current_user: $uid, old_file_id: $oldFileId, ex_user_record_id: $eurid },
                                                success: function(res) {
                                                    if (res.upload_success) {
                                                        setInterval(() => {
                                                            $('#choice_{$questionId}').val(res.filePath);
                                                            $('#uploadedFile_{$questionId}').addClass('text-decoration-line-through text-danger');
                                                        }, 500);
                                                    }
                                                }
                                            });
                                        }
                                        return { url: '' };
                                    } catch(e) {
                                        console.error('Failed to parse response:', e); 
                                        return { url: '' };
                                    }
                                }
                            });

                            isUppyLoaded = true;

                        } catch (error) {
                            console.log('Uppy not loaded', error);
                            isUppyLoaded = false;
                        }
                    }

                    loadUppy();

                    $('#delFile_{$questionId}').on('click', function (e) {
                        e.preventDefault();
                        if (confirm('$langConfirmDeletePermantly')) {
                            $.ajax({
                                url: '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&exerciseId={$exerciseId}&token={$token}',
                                method: 'POST',
                                data: { 
                                    file_uploaded_remove: 1,
                                    old_file_id: '{$oldFileId}',
                                    old_file_path: '{$oldFilePath}',
                                    question_id: '{$questionId}',
                                    current_user: '{$uid}'
                                },
                                success: function(response) {
                                    $('#uploadedFile_{$questionId}').remove();
                                    $('#delFile_{$questionId}').remove();
                                    $('#choice_{$questionId}').val('');
                                },
                            });
                        }
                    });

                });
            </script>";

        return $html_content;
    }


    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $questionScore, $question_weight,
               $urlServer, $course_code, $webDir, $course_id, $is_editor;

        $exerciseId = Database::get()->querySingle("SELECT eid FROM exercise_user_record WHERE eurid = ?d", $eurid)->eid;
        $objExercise = new Exercise();
        $objExercise->read($exerciseId);
        $results = $objExercise->get_attempt_results_array($eurid);

        $questionId = $this->question_id;
        $questionScore = $question_weight;

        $html_content = $fileLink = '';
        $user_record = Database::get()->querySingle("SELECT `uid`,eid FROM exercise_user_record WHERE eurid = ?d", $eurid);
        $userId = $user_record->uid;
        $exerciseId = $user_record->eid;
        $uploadedFile = Database::get()->querySingle("SELECT `path`,`filename` FROM document 
                                                        WHERE course_id = ?d 
                                                        AND subsystem = ?d 
                                                        AND subsystem_id = ?d 
                                                        AND path = ?s 
                                                        AND lock_user_id = ?d", $course_id, UPLOAD_FILE_QUESTION, $questionId, $results[$questionId], $eurid);

        if ($uploadedFile && file_exists("$webDir/courses/$course_code/exercise/$userId/$exerciseId/$questionId$uploadedFile->path")) {
            $urlLink = $urlServer . "courses/$course_code/exercise/$userId/$exerciseId/$questionId$uploadedFile->path";
            $fileLink .= "<a class='linkColor TextBold' target='_blank' href='{$urlLink}'>$uploadedFile->filename</a>";
        }
        $html_content .= "<tr><td>$fileLink</td></tr>";

        return $html_content;
    }
}
