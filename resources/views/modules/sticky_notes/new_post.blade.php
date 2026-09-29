@extends('layouts.default')

@section('content')

<div class='{{ $container }} module-container py-lg-0'>
    <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>

                                <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>

                                    @if($isEdit && $post)
                                    <input type='hidden' name='post_id' value='{{ $post->id }}'>
                                    @endif
                                    <input type='hidden' name='topic_id' value='{{ $topicId }}'>

                                    {{-- Creator --}}
                                    <div class='form-group'>
                                        <div class='col-sm-12'>
                                            <div class='control-label-notes'>{{ trans('langCreator') }}:
                                                <span>{{ $creatorName }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class='form-group mt-4'>
                                        <label for='content' class='col-sm-12 control-label-notes'>
                                            {{ trans('langContent') }} <span class='asterisk Accent-200-cl'>(*)</span>
                                        </label>
                                        <div class='col-sm-12'>
                                            <textarea class='form-control' id='content' name='content'
                                                maxlength="500"
                                                rows='5' required
                                                placeholder="{{ trans('langStickyNotesContentPlaceholder') }}...">{{ $isEdit && $post ? e($post->content) : '' }}</textarea>
                                        </div>

                                        {{-- Character counter --}}
                                        <div class='d-flex justify-content-end mt-1'>
                                            <small id='content-counter' class='text-muted'>
                                                <span id='content-char-count'>0</span> / 500
                                            </small>
                                        </div>
                                    </div>

                                    <script>
                                        (function() {
                                            const textarea = document.getElementById('content');
                                            const counter = document.getElementById('content-char-count');
                                            const maxLen = parseInt(textarea.getAttribute('maxlength'), 10);

                                            function updateCounter() {
                                                const current = textarea.value.length;
                                                counter.textContent = current;

                                                // Visual feedback as the user approaches the limit
                                                const indicator = counter.closest('small');
                                                if (current >= maxLen) {
                                                    indicator.classList.add('text-danger');
                                                    indicator.classList.remove('text-warning', 'text-muted');
                                                } else if (current >= maxLen * 0.85) {
                                                    indicator.classList.add('text-warning');
                                                    indicator.classList.remove('text-danger', 'text-muted');
                                                } else {
                                                    indicator.classList.add('text-muted');
                                                    indicator.classList.remove('text-danger', 'text-warning');
                                                }
                                            }

                                            updateCounter();

                                            textarea.addEventListener('input', updateCounter);
                                        })();
                                    </script>

                                    {{-- Color Picker --}}
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langStickyNotesColor') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='sticky-color-picker'>
                                                @foreach($availableColors as $hex => $label)
                                                @php
                                                $selected = ($isEdit && $post)
                                                ? $post->color === $hex
                                                : $hex === '#fff9c4';
                                                @endphp
                                                <label class='color-swatch-label {{ $selected ? "selected" : "" }}'
                                                    title='{{ $label }}'>
                                                    <input type='radio' name='color' value='{{ $hex }}'
                                                        {{ $selected ? 'checked' : '' }}>
                                                    <span class='color-swatch' style='background-color: {{ $hex }};'></span>
                                                </label>
                                                @endforeach
                                            </div>
                                            <small class='text-muted mt-2 d-block'>
                                                <i class='fa fa-info-circle'></i> {{ trans('langStickyNotesColorHint') }}
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Category (αν το topic έχει κατηγορίες) --}}
                                    @if($topic->has_categories && count($categories) > 0)
                                    <div class='form-group mt-4'>
                                        <label for='category_id' class='col-sm-12 control-label-notes'>
                                            {{ trans('langStickyNotesCategory') }}
                                        </label>
                                        <div class='col-sm-12 col-md-4'>
                                            <select class='form-select' id='category_id' name='category_id' required>
                                                <option value=''>— {{ trans('langUncategorized') }} —</option>
                                                @foreach($categories as $cat)
                                                <option value='{{ $cat->id }}'
                                                    {{ ($isEdit && $post && $post->category_id == $cat->id) ? 'selected' : '' }}>
                                                    {{ $cat->title }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Buttons --}}
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <button class='btn submitAdminBtn' type='submit'>
                                                {{ $isEdit ? trans('langSave') : trans('langSubmit') }}
                                            </button>
                                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>

                                    {!! generate_csrf_token_form_field() !!}
                                </form>

                            </div>
                        </div>

                        {{-- Preview post-it --}}
                        <div class='d-none d-lg-block'>
                            <p class='control-label-notes mb-2'>{{ trans('langStickyNotesPreview') }}</p>
                            <div id='notePreview' class='sticky-note' style='background-color: #fff9c4; width: 220px;'>
                                <div class='sticky-note-body' id='previewContent'>
                                    {{ trans('langStickyNotesPreviewPlaceholder') }}
                                </div>
                                <div class='sticky-note-footer'>
                                    <small><i class='fa fa-user'></i> {{ $creatorName }}</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

@endsection