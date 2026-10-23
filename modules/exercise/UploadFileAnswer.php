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
        global $webDir, $urlAppend, $language, $course_code, 
               $head_content, $urlServer, $course_id, $langDelete, 
               $langAnswer, $langConfirmDeletePermantly, $eurid;
            
        $exerciseId = intval($_GET['exerciseId']) ?? 0;
        $questionId = $this->question_id;
        $token = $_SESSION['csrf_token'];
        $fileLink = '';
        $html_content = '';
        $uploadedFileName = '';
        $uploadedFilePath = '';
        $valueFile ='';

        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            $valueFile = $exerciseResult[$questionId];
            $fileInfo = unserialize($exerciseResult[$questionId], ['allowed_classes' => false]);
            $uploadedFileName = $fileInfo['filename'] ?? '';
            $uploadedFilePath = $fileInfo['filepath'] ?? '';
            if (file_exists("$webDir/courses/$course_code/exercise/{$exerciseId}{$uploadedFilePath}")) {
                $urlLink = $urlServer . "courses/$course_code/exercise/{$exerciseId}{$uploadedFilePath}";
                $fileLink .= "<strong class='text-decoration-underline'>$langAnswer:</strong>
                              <a id='uploadedFile_{$questionId}' class='linkColor TextBold' target='_blank' href='" . q($urlLink) . "'>" . q($uploadedFileName) . "</a>
                              <a id='delFile_{$questionId}' href='#' class='Accent-200-cl' data-filepath='" . q($uploadedFilePath) . "' data-bs-toggle='tooltip' title='$langDelete'><i class='fa-solid fa-xmark'></i></a>";
            }
        }

        $head_content .= "<link href='{$urlAppend}js/bundle/uppy.min.css' rel='stylesheet'>";
        $html_content .= "<div class='form-group margin-bottom-fat'>
                            <div class='col-sm-12 margin-top-thin QuestionNumber_{$questionId}'>
                                <div id='fileAnswer_{$questionId}' class='col-12 d-flex align-items-center gap-2 mb-4" . ($fileLink ? '' : ' d-none') . "'>$fileLink</div>
                                <input type='hidden' id='choice_{$questionId}' name='choice[$questionId]' value='" . q($valueFile) . "'>
                                <div id='uppy_{$questionId}'></div>
                                <div class='text-success mt-4' id='answerFile_{$questionId}'></div>
                            </div>
                          </div>";

        $head_content .= "
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    let isUppyLoaded = false;

                    // Show the uploaded file (link + delete button) without reloading the page
                    function showUploadedFile_{$questionId}(fileName, fileUrl, filePath) {
                        const container = $('#fileAnswer_{$questionId}');
                        container.empty()
                            .append($('<strong>', { 'class': 'text-decoration-underline', text: '" . js_escape($langAnswer) . ":' }))
                            .append($('<a>', { id: 'uploadedFile_{$questionId}', 'class': 'linkColor TextBold', target: '_blank', href: fileUrl, text: fileName }))
                            .append($('<a>', { id: 'delFile_{$questionId}', href: '#', 'class': 'Accent-200-cl', title: '" . js_escape($langDelete) . "', 'data-filepath': filePath })
                                .append($('<i>', { 'class': 'fa-solid fa-xmark' })))
                            .removeClass('d-none');
                    }

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
                                endpoint: '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&exerciseId={$exerciseId}&questionId={$questionId}&exrecid={$eurid}&token={$token}',
                                fieldName: 'new_upload_file',
                                formData: true,
                                getResponseData: (responseText, response) => {
                                    try {
                                        const data = JSON.parse(responseText.responseText);
                                        if (data.success) {
                                            $('#choice_{$questionId}').val(data.fileInfo);
                                            showUploadedFile_{$questionId}(data.fileName, data.fileUrl, data.filePath);
                                            // Clear the dashboard so that the user can upload another file
                                            setTimeout(() => uppy.cancelAll(), 1500);
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

                    $(document).on('click', '#delFile_{$questionId}', function (e) {
                        e.preventDefault();
                        const filePath = $(this).data('filepath');
                        if (confirm('" . js_escape($langConfirmDeletePermantly) . "')) {
                            $.ajax({
                                url: '{$urlAppend}modules/exercise/exercise_submit.php?course={$course_code}&exerciseId={$exerciseId}&token={$token}',
                                method: 'POST',
                                data: { 
                                    file_uploaded_remove: 1,
                                    u_record_id: '{$eurid}',
                                    question_id: '{$questionId}',
                                    old_file_path: filePath
                                },
                                success: function(response) {
                                    $('#fileAnswer_{$questionId}').empty().addClass('d-none');
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

        global $questionScore, $question_weight, $urlServer, $course_code, $webDir;

        $exerciseId = Database::get()->querySingle("SELECT eid FROM exercise_user_record WHERE eurid = ?d", $eurid)->eid;
        $questionScore = $question_weight;
        $html_content = $fileLink = '';
        $fileName = '';
        $filePath = '';
        if (isset($choice)) {
            $fileInfo = unserialize($choice, ['allowed_classes' => false]);
            $fileName = $fileInfo['filename'] ?? '';
            $filePath = $fileInfo['filepath'] ?? '';
        }

        if (file_exists("$webDir/courses/$course_code/exercise/{$exerciseId}{$filePath}")) {
            $urlLink = $urlServer . "courses/$course_code/exercise/{$exerciseId}{$filePath}";
            $fileLink .= "<a class='linkColor TextBold' target='_blank' href='" . q($urlLink) . "'>" . q($fileName) . "</a>";
        }
        $html_content .= "<tr><td>$fileLink</td></tr>";

        return $html_content;
    }
}
