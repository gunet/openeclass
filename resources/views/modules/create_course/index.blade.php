@extends('layouts.default')

@push('head_styles')
    <link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>

    <script type='text/javascript'>
        var lang = {
            pwStrengthTooShort: "{{ js_escape(trans('langPwStrengthTooShort')) }}",
            pwStrengthWeak: "{{ js_escape(trans('langPwStrengthWeak')) }}",
            pwStrengthGood: "{{ js_escape(trans('langPwStrengthGood')) }}",
            pwStrengthStrong: "{{ js_escape(trans('langPwStrengthStrong')) }}"
        }

        function deactivate_input_password () {
            $('#coursepassword, #faculty_users_registration').attr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').addClass('invisible');
        }

        function activate_input_password () {
            $('#coursepassword, #faculty_users_registration').removeAttr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').removeClass('invisible');
        }

        function displayCoursePassword() {
            if ($('#courseclose, #courseiactive').is(":checked")) {
                deactivate_input_password ();
            } else {
                activate_input_password ();
            }
        }

        $(document).ready(function() {

            // Check for existing syllabus sections on page load
            const existingSyllabusData = $('#ai_syllabus_sections').val();
            if (existingSyllabusData) {
                try {
                    const syllabusObj = JSON.parse(existingSyllabusData);
                    const sectionCount = Object.keys(syllabusObj).length;
                    updateSyllabusIndicator(true, sectionCount);
                } catch (e) {
                    // Invalid JSON, remove the field
                    $('#ai_syllabus_sections').remove();
                }
            }

            $('#coursepassword').keyup(function() {
                $('#result').html(checkStrength($('#coursepassword').val()))
            });

            displayCoursePassword();

            $('#courseopen, #coursewithregistration').click(function(event) {
                activate_input_password();
            });

            $('#courseclose, #courseinactive').click(function(event) {
                deactivate_input_password();
            });

            $('input[name=l_radio]').change(function () {
                if ($('#cc_license').is(":checked")) {
                    $('#cc').show();
                } else {
                    $('#cc').hide();
                }
            }).change();

            $('.chooseCourseImage').on('click',function(){
                var id_img = this.id;
                alert('{{ js_escape(trans('langImageSelected')) }}!');
                document.getElementById('choose_from_list').value = id_img;
                $('#CoursesImagesModal').modal('hide');
                document.getElementById('selectedImage').value = '{{ trans('langSelect') }}:'+id_img;
            });

            if ($("#radio_collaborative_helper").length > 0) {
                if(document.getElementById("radio_collaborative_helper").value == 0){
                    document.getElementById("radio_collaborative").style.display="none";
                }
            }
            $('#type_collab').on('click',function(){
                if($('#type_collab').is(":checked")){
                    document.getElementById("radio_flippedclassroom").style.display="none";
                    document.getElementById("radio_activity").style.display="none";
                    document.getElementById("radio_wall").style.display="none";
                    document.getElementById("radio_collaborative").style.display="block";
                }else{
                    document.getElementById("radio_flippedclassroom").style.display="block";
                    document.getElementById("radio_activity").style.display="block";
                    document.getElementById("radio_wall").style.display="block";
                    document.getElementById("radio_collaborative").style.display="none";
                }
            });

            // AI Assistant functionality
            let currentAIData = null;

            // Handle input method change for syllabus extraction
            $('input[name="input_method"]').change(function() {
                if ($(this).val() === 'url') {
                    $('#upload_section').hide();
                    $('#url_section').show();
                    $('#syllabus_pdf').prop('required', false);
                    $('#syllabus_url').prop('required', true);
                } else {
                    $('#upload_section').show();
                    $('#url_section').hide();
                    $('#syllabus_pdf').prop('required', true);
                    $('#syllabus_url').prop('required', false);
                }
            });

            // Toggle AI Assistant
            $('#toggleAIAssistant').on('click', function() {
                const body = $('#aiAssistantBody');
                const icon = $(this).find('i');

                if (body.is(':visible')) {
                    body.slideUp();
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    body.slideDown();
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            });

            // Handle Syllabus Form Submission
            $('#aiSyllabusForm').on('submit', function(e) {
                e.preventDefault();

                const inputMethod = $('input[name="input_method"]:checked').val();

                // Validate input based on method
                if (inputMethod === 'url') {
                    const url = $('#syllabus_url').val().trim();
                    if (!url) {
                        alert('Please enter a URL');
                        return;
                    }
                    if (!isValidURL(url)) {
                        alert('Please enter a valid URL');
                        return;
                    }
                    showAILoading('{{ js_escape(trans('langAIDownloading')) }}');
                } else {
                    const fileInput = $('#syllabus_pdf')[0];
                    if (!fileInput.files[0]) {
                        alert('Please select a PDF file');
                        return;
                    }
                    showAILoading('{{ js_escape(trans('langAIExtracting')) }}');
                }

                const formData = new FormData(this);

                $.ajax({
                    url: 'ai_extract_syllabus.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 90000, // 90 seconds timeout for URL downloads
                    success: function(response) {
                        hideAILoading();
                        if (response.success) {
                            currentAIData = response.data;
                            displayAIResults(response.data);
                        } else {
                            showAIError(response.error || 'Extraction failed');
                        }
                    },
                    error: function(xhr) {
                        hideAILoading();
                        let errorMsg = 'Request failed';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.status === 0) {
                            errorMsg = 'Network error - please check your connection';
                        } else if (xhr.status === 504) {
                            errorMsg = 'Request timed out - the file may be too large or the server is busy';
                        }
                        showAIError(errorMsg);
                    }
                });
            });

            // Handle Prompt Form Submission
            $('#aiPromptForm').on('submit', function(e) {
                e.preventDefault();

                const prompt = $('#course_prompt').val().trim();
                if (prompt.length < 10) {
                    alert('{{ js_escape(trans('langPromptTooShort')) }}');
                    return;
                }

                showAILoading('{{ js_escape(trans('langAIGenerating')) }}');

                $.ajax({
                    url: 'ai_generate_course.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        hideAILoading();
                        if (response.success) {
                            currentAIData = response.data;
                            displayAIResults(response.data);
                        } else {
                            showAIError(response.error || 'Generation failed');
                        }
                    },
                    error: function(xhr) {
                        hideAILoading();
                        let errorMsg = 'Request failed';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        showAIError(errorMsg);
                    }
                });
            });

            // Apply AI Data to Form
            $('#applyAIData').on('click', function() {
                if (!currentAIData) return;

                // Apply data to form fields
                if (currentAIData.title) $('#title').val(currentAIData.title);
                if (currentAIData.public_code) $('#public_code').val(currentAIData.public_code);
                if (currentAIData.prof_names) $('#prof_names').val(currentAIData.prof_names);
                if (currentAIData.language) $('#lang_selected').val(currentAIData.language);
                if (currentAIData.description) {
                    // Handle rich text editor - check for TinyMCE first, then CKEditor fallback
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('description')) {
                        tinyMCE.get('description').setContent(currentAIData.description);
                    } else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.description) {
                        CKEDITOR.instances.description.setData(currentAIData.description);
                    } else {
                        $('textarea[name="description"]').val(currentAIData.description);
                    }
                }

                // Store syllabus sections in hidden form field for processing after course creation
                if (currentAIData.syllabus_sections) {
                    // Create or update hidden field with syllabus sections JSON
                    let existingField = $('#ai_syllabus_sections');
                    if (existingField.length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'ai_syllabus_sections',
                            name: 'ai_syllabus_sections'
                        }).appendTo('form[name="createform"]');
                    }
                    $('#ai_syllabus_sections').val(JSON.stringify(currentAIData.syllabus_sections));

                    // Show indicator
                    updateSyllabusIndicator(true, Object.keys(currentAIData.syllabus_sections).length);
                }

                // Set course format
                if (currentAIData.view_type) {
                    $('input[name="view_type"][value="' + currentAIData.view_type + '"]').prop('checked', true);
                }

                // Set course visibility
                if (currentAIData.formvisible !== undefined) {
                    $('input[name="formvisible"][value="' + currentAIData.formvisible + '"]').prop('checked', true);
                }

                // Set course license
                if (currentAIData.course_license !== undefined) {
                    if (currentAIData.course_license === 0) {
                        $('input[name="l_radio"][value="0"]').prop('checked', true);
                    } else if (currentAIData.course_license === 10) {
                        $('input[name="l_radio"][value="10"]').prop('checked', true);
                    } else {
                        $('input[name="l_radio"][value="cc"]').prop('checked', true);
                        $('#course_license_id').val(currentAIData.course_license);
                    }
                }

                // Hide AI results and show success message
                $('#aiResults').slideUp();

                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#title').offset().top - 100
                }, 500);

                // Highlight applied fields briefly
                $('#title, #public_code, #prof_names').addClass('bg-success bg-opacity-25');
                setTimeout(function() {
                    $('#title, #public_code, #prof_names').removeClass('bg-success bg-opacity-25');
                }, 2000);
            });

            // Clear AI Data
            $('#clearAIData').on('click', function() {
                currentAIData = null;
                $('#aiResults').slideUp();
            });

            // Retry AI Generation
            $('#retryAI').on('click', function() {
                $('#aiResults').slideUp();
                // Trigger the active tab's form
                if ($('#ai-syllabus-tab').hasClass('active')) {
                    $('#aiSyllabusForm').trigger('submit');
                } else {
                    $('#aiPromptForm').trigger('submit');
                }
            });

            function showAILoading(text) {
                $('#loadingText').text(text);
                $('#aiLoading').show();
                $('#aiResults').hide();
                $('#extractBtn, #generateBtn').prop('disabled', true);
            }

            function hideAILoading() {
                $('#aiLoading').hide();
                $('#extractBtn, #generateBtn').prop('disabled', false);
            }

            function displayAIResults(data) {
                let preview = '<div class="row">';

                if (data.title) {
                    preview += '<div class="col-md-6 mb-2"><strong>{{ js_escape(trans('langAIPreviewTitle')) }}:</strong> ' + escapeHtml(data.title) + '</div>';
                }
                if (data.public_code) {
                    preview += '<div class="col-md-6 mb-2"><strong>{{ js_escape(trans('langAIPreviewCode')) }}:</strong> ' + escapeHtml(data.public_code) + '</div>';
                }
                if (data.prof_names) {
                    preview += '<div class="col-md-6 mb-2"><strong>{{ js_escape(trans('langAIPreviewInstructor')) }}:</strong> ' + escapeHtml(data.prof_names) + '</div>';
                }
                if (data.language) {
                    preview += '<div class="col-md-6 mb-2"><strong>{{ js_escape(trans('langAIPreviewLanguage')) }}:</strong> ' + escapeHtml(data.language) + '</div>';
                }
                if (data.view_type) {
                    preview += '<div class="col-md-6 mb-2"><strong>{{ js_escape(trans('langAIPreviewFormat')) }}:</strong> ' + escapeHtml(data.view_type) + '</div>';
                }
                if (data.keywords) {
                    preview += '<div class="col-12 mb-2"><strong>{{ js_escape(trans('langAIPreviewKeywords')) }}:</strong> ' + escapeHtml(data.keywords) + '</div>';
                }

                // Show source information
                if (data.extraction_method) {
                    let sourceInfo = '';
                    if (data.extraction_method === 'web_url' && data.source_url) {
                        sourceInfo = '{{ js_escape(trans('langAISourceWebURL')) }}: ' + escapeHtml(data.source_url);
                    } else if (data.extraction_method === 'pdf_url' && data.source_url) {
                        sourceInfo = '{{ js_escape(trans('langAISourceDownloaded')) }}: ' + escapeHtml(data.source_url);
                    } else if (data.extraction_method === 'pdf_upload' && data.file_name) {
                        sourceInfo = '{{ js_escape(trans('langAISourceUploaded')) }}: ' + escapeHtml(data.file_name);
                    }
                    if (sourceInfo) {
                        preview += '<div class="col-12 mb-2"><small class="text-muted"><strong>{{ js_escape(trans('langAIPreviewSource')) }}:</strong> ' + sourceInfo + '</small></div>';
                    }
                }

                if (data.description) {
                    const shortDesc = data.description.length > 200 ? data.description.substring(0, 200) + '...' : data.description;
                    const needsExpansion = data.description.length > 200;

                    preview += '<div class="col-12">';
                    preview += '<strong>{{ js_escape(trans('langAIPreviewDescription')) }}:</strong><br>';
                    preview += '<div id="descriptionShort">' + shortDesc + '</div>';

                    if (needsExpansion) {
                        preview += '<div id="descriptionFull" style="display: none;">' + data.description + '</div>';
                        preview += '<button type="button" class="btn btn-link btn-sm p-0 mt-1" id="toggleDescription">';
                        preview += '<i class="fa-solid fa-chevron-down me-1"></i>{{ js_escape(trans('langAIShowFullDescription')) }}';
                        preview += '</button>';
                    }

                    preview += '</div>';
                }

                // Show structured syllabus sections if available
                if (data.syllabus_sections && Object.keys(data.syllabus_sections).length > 0) {
                    preview += '<div class="col-12 mt-3">';
                    preview += '<strong>{{ js_escape(trans('langAISyllabusStructured')) }}:</strong>';
                    preview += '<div class="row mt-2">';

                    const sectionLabels = {
                        'objectives': '{{ js_escape(trans('langGoals')) }}',
                        'bibliography': '{{ js_escape(trans('langAISyllabusBibliography')) }}',
                        'teaching_method': '{{ js_escape(trans('langAISyllabusTeachingMethod')) }}',
                        'assessment_method': '{{ js_escape(trans('langAISyllabusAssessmentMethod')) }}',
                        'prerequisites': '{{ js_escape(trans('langAISyllabusPrerequisites')) }}',
                        'instructors': '{{ js_escape(trans('langAISyllabusInstructors')) }}',
                        'target_group': '{{ js_escape(trans('langAISyllabusTargetGroup')) }}',
                        'textbooks': '{{ js_escape(trans('langAISyllabusTextbooks')) }}',
                        'additional_info': '{{ js_escape(trans('langAISyllabusAdditionalInfo')) }}'
                    };

                    let sectionCounter = 0;
                    for (const [sectionKey, sectionContent] of Object.entries(data.syllabus_sections)) {
                        if (sectionContent && sectionContent.trim().length > 0) {
                            const label = sectionLabels[sectionKey] || sectionKey;
                            // Create a temporary element to strip HTML tags for preview
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = sectionContent;
                            const textContent = tempDiv.textContent || tempDiv.innerText || '';
                            const needsExpansion = textContent.length > 100;
                            const shortContent = needsExpansion ? textContent.substring(0, 100) + '...' : textContent;
                            const sectionId = 'section_' + sectionCounter++;

                            preview += '<div class="col-md-6 mb-3">';
                            preview += '<small><strong>' + escapeHtml(label) + ':</strong><br>';
                            preview += '<span id="' + sectionId + '_short">' + escapeHtml(shortContent) + '</span>';

                            if (needsExpansion) {
                                preview += '<span id="' + sectionId + '_full" style="display: none;">' + escapeHtml(textContent) + '</span>';
                                preview += '<br><button type="button" class="btn btn-link btn-sm p-0 mt-1 section-toggle" data-target="' + sectionId + '">';
                                preview += '<i class="fa-solid fa-chevron-down me-1"></i>{{ js_escape(trans('langAIShowFullDescription')) }}';
                                preview += '</button>';
                            }

                            preview += '</small></div>';
                        }
                    }

                    preview += '</div></div>';
                }

                preview += '</div>';

                $('#aiDataPreview').html(preview);

                // Add click handler for description toggle
                $('#toggleDescription').on('click', function() {
                    const $short = $('#descriptionShort');
                    const $full = $('#descriptionFull');
                    const $button = $(this);
                    const $icon = $button.find('i');

                    if ($full.is(':visible')) {
                        $full.hide();
                        $short.show();
                        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $button.html('<i class="fa-solid fa-chevron-down me-1"></i>{{ js_escape(trans('langAIShowFullDescription')) }}');
                    } else {
                        $short.hide();
                        $full.show();
                        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $button.html('<i class="fa-solid fa-chevron-up me-1"></i>{{ js_escape(trans('langAIHideDescription')) }}');
                    }
                });

                // Add click handlers for syllabus section toggles
                $(document).on('click', '.section-toggle', function() {
                    const $button = $(this);
                    const targetId = $button.data('target');
                    const $short = $('#' + targetId + '_short');
                    const $full = $('#' + targetId + '_full');
                    const $icon = $button.find('i');

                    if ($full.is(':visible')) {
                        $full.hide();
                        $short.show();
                        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $button.html('<i class="fa-solid fa-chevron-down me-1"></i>{{ js_escape(trans('langAIShowFullDescription')) }}');
                    } else {
                        $short.hide();
                        $full.show();
                        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $button.html('<i class="fa-solid fa-chevron-up me-1"></i>{{ js_escape(trans('langAIHideDescription')) }}');
                    }
                });

                $('#aiResults').slideDown();
            }

            function showAIError(message) {
                alert('AI Error: ' + message);
            }

            function isValidURL(string) {
                try {
                    const url = new URL(string);
                    return url.protocol === 'http:' || url.protocol === 'https:';
                } catch (_) {
                    return false;
                }
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function updateSyllabusIndicator(hasSections, sectionCount) {
                let indicator = $('#syllabusIndicator');

                if (hasSections && sectionCount > 0) {
                    if (indicator.length === 0) {
                        // Create indicator if it doesn't exist
                        const indicatorHtml = `
                            <div id="syllabusIndicator" class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fa-solid fa-file-text me-2"></i>
                                    <strong>${sectionCount} AI δομημένα τμήματα συλλάβου</strong> θα προστεθούν στο μάθημα
                                </div>
                                <button type="button" class="btn deleteAdminBtn" id="clearSyllabusData">
                                    Καθαρισμός
                                </button>
                            </div>
                        `;
                        $(indicatorHtml).insertBefore('.form-wrapper');
                    } else {
                        // Update existing indicator
                        indicator.find('strong').text(sectionCount + ' AI δομημένα τμήματα συλλάβου');
                        indicator.show();
                    }
                } else {
                    indicator.hide();
                }
            }

            // Handle clear syllabus data
            $(document).on('click', '#clearSyllabusData', function() {
                if (confirm('Είστε βέβαιος ότι θέλετε να καθαρίσετε τα δομημένα τμήματα συλλάβου;')) {
                    $('#ai_syllabus_sections').remove();
                    updateSyllabusIndicator(false, 0);
                }
            });

        });
    </script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

              @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

              @include('layouts.partials.legend_view')

              {!! $action_bar !!}

              @include('layouts.partials.show_alert')

             <div class='col-12'>
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>{{ trans('langFieldsOptionalNote') }}</span>
                </div>
             </div>

             @if($ai_available)
                 <div class='col-12 mb-4'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3 class='mb-0'>
                                <i class='fa-solid fa-robot me-2'></i>{{ trans('langAIGenerateCourse') }}
                            </h3>
                            <button type='button' class='btn submitAdminBtnDefault' id='toggleAIAssistant'>
                                 {{ trans('langAIToggleAssistant') }} <i class='fa-solid fa-chevron-down'></i>
                            </button>
                        </div>
                        <div class='card-body' id='aiAssistantBody' style='display: none;'>
                            <ul class='nav nav-tabs mb-3' id='aiTabs' role='tablist'>
                                <li class='nav-item' role='presentation'>
                                    <a class='nav-link active' id='ai-syllabus-tab' data-bs-toggle='tab' href='#ai-syllabus' type='button' role='tab'>
                                        <i class='fa-solid fa-file-pdf me-2'></i>{{ trans('langAIExtractFromSyllabus') }}
                                    </a>
                                </li>
                                <li class='nav-item' role='presentation'>
                                    <a class='nav-link' id='ai-prompt-tab' data-bs-toggle='tab' href='#ai-prompt' type='button' role='tab'>
                                        <i class='fa-solid fa-keyboard me-2'></i>{{ trans('langAIGenerateFromPrompt') }}
                                    </a>
                                </li>
                            </ul>

                            <div class='tab-content' id='aiTabContent'>
                                <!-- Syllabus Upload Tab -->
                                <div class='tab-pane fade show active' id='ai-syllabus' role='tabpanel'>
                                    <form id='aiSyllabusForm' enctype='multipart/form-data'>

                                        <!-- Input Method Selection -->
                                        <div class='form-group'>
                                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAISyllabusInputMethod') }}</div>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' name='input_method' id='upload_method' value='upload' checked>
                                                    {{ trans('langAIUploadFile') }}
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' name='input_method' id='url_method' value='url'>
                                                    {{ trans('langAIDownloadFromURL') }}
                                                </label>
                                            </div>
                                        </div>


                                        <div class='col-12 mt-4'>
                                            <!-- File Upload Section -->
                                            <div class='mb-3' id='upload_section'>
                                                <label for='syllabus_pdf' class='form-label'>{{ trans('langAIUploadSyllabus') }}</label>
                                                <input type='file' class='form-control' id='syllabus_pdf' name='syllabus_pdf' accept='application/pdf'>
                                                <div class='form-text'>{{ trans('langAIMaxFileSize') }}</div>
                                            </div>

                                            <!-- URL Download Section -->
                                            <div class='mb-3' id='url_section' style='display: none;'>
                                                <label for='syllabus_url' class='form-label'>{{ trans('langAISyllabusURL') }}</label>
                                                <input type='url' class='form-control' id='syllabus_url' name='syllabus_url'
                                                        placeholder='https://example.com/course-page or https://example.com/syllabus.pdf'>
                                                <div class='form-text'>
                                                    {{ trans('langAIURLDescription') }}
                                                </div>
                                            </div>
                                        </div>

                                        <button type='submit' class='btn btn-primary mt-4' id='extractBtn'>
                                            <i class='fa-solid fa-magic-wand-sparkles me-2'></i>{{ trans('langAIExtractButton') }}
                                        </button>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>

                                <!-- Manual Prompt Tab -->
                                <div class='tab-pane fade' id='ai-prompt' role='tabpanel'>
                                    <form id='aiPromptForm'>
                                        <div class='row'>
                                            <div class='col-12'>
                                                <div class='mb-3'>
                                                    <label for='course_prompt' class='form-label'>{{ trans('langAICoursePrompt') }}</label>
                                                    <textarea class='form-control' id='course_prompt' name='course_prompt' rows='4'
                                                              placeholder='{{ trans('langAIPromptPlaceholder') }}' required></textarea>
                                                    <div class='form-text'>{{ trans('langAIPromptDescription') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type='submit' class='btn btn-primary' id='generateBtn'>
                                            <i class='fa-solid fa-magic-wand-sparkles me-2'></i>{{ trans('langAIGenerateCourse') }}
                                        </button>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>

                            <!-- AI Results -->
                            <div id='aiResults' class='mt-4' style='display: none;'>
                                <div class='panel panel-success'>
                                    <div class='panel-heading text-white'>
                                        <i class='fa-solid fa-check-circle me-2'></i>{{ trans('langAIPreviewData') }}
                                    </div>
                                    <div class='panel-body'>
                                        <div id='aiDataPreview'></div>
                                        <div class='mt-3 d-flex flex-wrap gap-2'>
                                            <button type='button' class='btn successAdminBtn' id='applyAIData'>
                                                {{ trans('langAIApplyData') }}
                                            </button>
                                            <button type='button' class='btn deleteAdminBtn' id='clearAIData'>
                                                {{ trans('langAIClearForm') }}
                                            </button>
                                            <button type='button' class='btn warningAdminBtn' id='retryAI'>
                                                {{ trans('langAIRetry') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading indicator -->
                            <div id='aiLoading' class='text-center mt-4' style='display: none;'>
                                <div class='spinner-border text-primary' role='status'>
                                    <span class='visually-hidden'>Loading...</span>
                                </div>
                                <div class='mt-2'>
                                    <span id='loadingText'>{{ trans('langAIGenerating') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
             @endif

              <div class='col-lg-8 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>
                  <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data" onsubmit=\"return validateNodePickerForm();\">
                    <fieldset>
                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                    <div class='form-group'>
                        <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-12'>
                          <input name='title' id='title' type='text' class='form-control' value="{{ $title }}" placeholder="{{ trans('langCourseTitle') }}">
                            <span class='help-block Accent-200-cl'>{{ Session::getError('title') }}</span>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='public_code' class='col-12 control-label-notes'>{{ trans('langCode') }}</label>
                        <div class='col-sm-12'>
                          <input name='public_code' id='public_code' type='text' class='form-control' value = "{{ $public_code }}"  placeholder="{{ trans('langOptional') }}">
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-sm-12'>
                          {!! $buildusernode !!}
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='prof_names' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                        <div class='col-sm-12'>
                              <input class='form-control' type='text' name='prof_names' id='prof_names' value= "{{ $prof_names }}">
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='lang_selected' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                        <div class='col-sm-12'>
                              {!! $lang_select_options !!}
                        </div>
                    </div>

                    <div id='image_field' class='row form-group mt-4'>
                        <label for='course_image' class='col-12 control-label-notes'>{{ trans('langCourseImage') }}</label>
                        <div class='col-12'>
                            {!! fileSizeHidenInput() !!}
                            <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                                <li class='nav-item' role='presentation'>
                                    <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'> {{ trans('langUpload') }}</button>
                                </li>
                                <li class='nav-item' role='presentation'>
                                    <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>{{ trans('langAddPicture') }}</button>
                                </li>
                            </ul>
                            <div class='tab-content mt-3' id='tabs-tabContent'>
                                <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                                    <input type='file' name='course_image' id='course_image'>
                                </div>
                                <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#CoursesImagesModal'>
                                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;{{ trans('langSelect') }}
                                    </button>
                                    <input type='hidden' id='choose_from_list' name='choose_from_list'>
                                    <label for='selectedImage'>{{ trans('langImageSelected')}}:</label>
                                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='description' class='col-sm-12 control-label-notes'>
                            {{ trans('langDescrInfo') }}
                            <small>{{trans('langOptional')}}</small>
                        </label>
                        <div class='col-sm-12'>
                              {!! $rich_text_editor !!}
                        </div>
                    </div>

                    @if(get_config('show_collaboration') && !get_config('show_always_collaboration'))
                        <div class='form-group mt-4'>
                            <div class='col-sm-12'>
                                <label class='control-label-notes' for='type_collab'>{!! trans('langWhatTypeOfCourse') !!}</label>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                        <input type='checkbox' id='type_collab' name='is_type_collaborative'>
                                        <span class='checkmark'></span>
                                        {!! trans('langTypeCollaboration') !!}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class='form-group mt-4'>
                       <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseFormat') }}</div>
                        <div class="radio mb-2">
                          <label>
                              <input type='radio' name='view_type' value='simple' id='simple'>
                              {{ trans('langCourseSimpleFormat') }}
                          </label>
                        </div>
                        <div class="radio mb-2">
                          <label>
                            <input type='radio' name='view_type' value='units' id='units' checked>
                            {{ trans('langWithCourseUnits') }}
                            </label>
                        </div>
                        <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_activity">
                          <label>
                            <input type="radio" name="view_type" value="activity" id="activity">
                            {{trans('langCourseActivityFormat') }}
                          </label>
                        </div>
                        <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_wall">
                          <label>
                            <input type='radio' name='view_type' value='wall' id='wall'>
                            {{ trans('langCourseWallFormat') }}
                          </label>
                        </div>
                        <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_flippedclassroom">
                            <label>
                                <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom'>
                                {{ trans('langFlippedClassroom') }}
                            </label>
                        </div>

                        <div class="radio
                            @if(!get_config('show_collaboration') and !get_config('show_always_collaboration'))
                                d-none
                            @elseif(is_module_disable(MODULE_ID_SESSION))
                                d-none
                            @endif" id="radio_collaborative">
                            <label>
                                <input type='radio' name='view_type' value='sessions' id='sessions'>
                                {{ trans('langSessionType') }}
                            </label>
                        </div>

                        @if(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                            <input type="hidden" id="radio_collaborative_helper" value="0">
                        @endif
                    </div>

                    <div class='form-group mt-4'>
                      <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</div>

                      <div class='radio mb-2'>
                        <label>
                          <input type='radio' name='l_radio' value='0' checked>
                          {{ $license_0 }}
                        </label>
                      </div>

                      <div class='radio mb-2'>
                        <label>
                          <input type='radio' name='l_radio' value='10'>
                          {{ $license_10 }}
                        </label>
                      </div>

                      <div class='radio'>
                        <label>
                          <input id='cc_license' type='radio' name='l_radio' value='cc'>
                          {{ trans("langCMeta['course_license']") }}
                        </label>
                      </div>

                    </div>

                    <div class='form-group mt-4' id='cc'>
                        <div class='col-sm-12 col-sm-offset-2'>
                            <label class='mb-0' for='course_license_id' aria-label="{{ trans('langOpenCoursesLicense') }}"></label>
                              {!! $selection_license !!}
                        </div>
                    </div>

                    <div class='form-group mt-4'>

                           <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2'
                                    @if ($default_access === COURSE_OPEN) checked @endif>
                                <label for="courseopen" aria-label="{{ trans('langOpenCourse') }}">{!! $icon_course_open !!}</label>
                                {{ trans('langOpenCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langPublic') }}</div>
                            </div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1'
                                    @if ($default_access === COURSE_REGISTRATION) checked @endif>
                                <label for="coursewithregistration" aria-label="{{ trans('langRegCourse') }}">{!! $icon_course_registration !!}</label>
                                {{ trans('langRegCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langPrivOpen') }}</div>
                            </div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0'
                                  @if ($default_access === COURSE_CLOSED) checked @endif>
                                <label for="courseclose" aria-label="{{ trans('langClosedCourse') }}">{!! $icon_course_closed !!}</label>
                                {{ trans('langClosedCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langClosedCourseShort') }}</div>
                            </div>

                            <div class='radio'>
                              <label>
                                  <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3'
                                    @if ($default_access === COURSE_INACTIVE) checked @endif>
                                  <label for="courseinactive" aria-label="{{ trans('langInactiveCourse') }}">{!! $icon_course_inactive !!}</label>
                                  {{ trans('langInactiveCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langCourseInactive') }}</div>
                            </div>
                      </div>

                     <div class='form-group mt-3'>
                         <div class='checkbox mb-2 mt-4'>
                             <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                 <input type='checkbox' id='faculty_users_registration' name='faculty_users_registration'>
                                 <span class='checkmark'></span>{{ trans('langFacultyUsersRegistrationLegend') }}
                             </label>
                         </div>
                        <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                        <div class='col-sm-12'>
                              <input class='form-control' id='coursepassword' type='text' name='password' value='{{ trans('password') }}' autocomplete='off'>
                        </div>
                        <div class='col-sm-12' text-center padding-thin>
                            <span id='result'></span>
                        </div>
                     </div>

                     <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                          <input class='btn submitAdminBtn text-nowrap' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>
                          <a href='{{ $cancel_link }}' class='btn cancelAdminBtn text-nowrap'>{{ trans('langCancel') }}</a>
                      </div>

                    <div class='modal fade' id='CoursesImagesModal' tabindex='-1' aria-labelledby='CoursesImagesModalLabel' aria-hidden='true'>
                        <div class='modal-dialog modal-lg'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <div class='modal-title' id='CoursesImagesModalLabel'>{{ trans('langCourseImage') }}</div>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
                                </div>
                                <div class='modal-body'>
                                    <div class='row row-cols-1 row-cols-md-2 g-4'>
                                        {!! $image_content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                {!! generate_csrf_token_form_field() !!}
                @if(!empty($ai_syllabus_sections))
                <input type="hidden" id="ai_syllabus_sections" name="ai_syllabus_sections" value="{{ $ai_syllabus_sections }}">
                @endif
                </fieldset>
              </form>
            </div>
          </div>
          <div class='col-lg-4 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
          </div>
        </div>
    </div>
</div>
@endsection
