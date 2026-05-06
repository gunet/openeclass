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

                    @include('layouts.partials.show_alert')

                    {!! $action_bar !!}
                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>

                                <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>

                                    {{-- Hidden field for edit mode --}}
                                    @if($isEdit && $topic)
                                    <input type='hidden' name='topic_id' value='{{ $topic->id }}'>
                                    @endif

                                    <div class='form-group'>
                                        <div class='col-sm-12'>
                                            <div class='control-label-notes'>{{ trans('langCreator') }}:
                                                <span>{{ $creatorName }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='topicTitle' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' placeholder="{{ trans('langTitle') }}..."
                                                id='topicTitle' name='topicTitle' required
                                                value='{{ $isEdit && $topic ? e($topic->title) : "" }}'>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='topicDescription' class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                        <div class='col-sm-12'>
                                            <textarea class='form-control' placeholder="{{ trans('langDescription') }}..."
                                                id='topicDescription' name='topicDescription'>{{ $isEdit && $topic ? e($topic->description) : "" }}</textarea>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='allow_edit' value='on'
                                                        {{ (!$isEdit || ($topic && $topic->allow_edit)) ? 'checked' : '' }}>
                                                    <span class='checkmark'></span> {{ trans('langStickyNotesAllowEdit') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class='col-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='allow_delete' value='on'
                                                        {{ (!$isEdit || ($topic && $topic->allow_delete)) ? 'checked' : '' }}>
                                                    <span class='checkmark'></span> {{ trans('langStickyNotesAllowDelete') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='perPage' class='col-sm-12 control-label-notes'>
                                            {{ trans('langStickyNotesPerPage') }}
                                        </label>
                                        <div class='col-sm-12 col-md-3'>
                                            <select class='form-select' id='perPage' name='per_page'>
                                                @foreach([10, 20, 30, 50] as $opt)
                                                <option value='{{ $opt }}'
                                                    {{ ($isEdit && $topic && $topic->per_page == $opt) || (!$isEdit && $opt == 20) ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-12'>
                                            <div class='d-flex align-items-center gap-3'>
                                                <label class='control-label-notes mb-0'>{{ trans('langStickyNotesHasCategories') }}</label>
                                                <div class='form-check form-switch'>
                                                    <input class='form-check-input' type='checkbox' role='switch'
                                                        id='hasCategories' name='has_categories' value='on'
                                                        {{ ($isEdit && $topic && $topic->has_categories) ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            <small class='text-muted'>{{ trans('langStickyNotesCategoriesHint') }}</small>
                                        </div>
                                    </div>

                                    <div class='form-group mt-3' id='categoriesSection' style='display:none;'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langStickyNotesCategories') }}</label>
                                        <div class='col-sm-12'>
                                            <div id='categoriesList' class='d-flex flex-column gap-2'>
                                                @if($isEdit && $topic && $topic->has_categories)
                                                @foreach($categories as $cat)
                                                <div class='category-row d-flex align-items-center gap-2' draggable="true">
                                                    <span class='drag-handle' style='cursor:grab; color:#aaa; padding: 0 4px;'>
                                                        <i class='fa fa-bars'></i>
                                                    </span>
                                                    <input type='text' class='form-control' name='category_title[]'
                                                        placeholder="{{ trans('langStickyNotesCategoryName') }}..."
                                                        value='{{ e($cat->title) }}'>
                                                    <input type='hidden' name='category_id[]' value='{{ $cat->id }}'>
                                                    <input type='hidden' name='category_sort[]' value='{{ $cat->sort_order }}'>
                                                    <button type='button' class='btn btn-sm btn-outline-danger remove-category'>
                                                        <i class='fa fa-times'></i>
                                                    </button>
                                                </div>
                                                @endforeach
                                                @endif
                                            </div>
                                            <button type='button' class='btn submitAdminBtn btn-sm mt-2 d-flex gap-2' id='addCategory'>
                                                <i class='fa fa-plus'></i> {{ trans('langAdd') }}
                                            </button>

                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='is_active' value='on'
                                                        {{ (!$isEdit || ($topic && $topic->is_active)) ? 'checked' : '' }}>
                                                    <span class='checkmark'></span> {{ trans('langStickyNotesIsActive') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>


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
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection