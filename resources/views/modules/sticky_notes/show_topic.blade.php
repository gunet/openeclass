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

                    <div class='col-sm-12'>

                        <h4 class="mb-1">{{ $topic->title }}</h4>
                        @if($topic->description)
                        <p class="text-muted mb-4">{{ $topic->description }}</p>
                        @endif

                
                        {{-- KANBAN VIEW --}}
                        @if($hasCategories)

                        <div class="kanban-board" id="kanban-board-wrapper" data-draggable="{{ $isDraggable }}">

                            @foreach($categories as $cat)
                            <div class="kanban-column" data-category-id="{{ $cat->id }}">
                                <div class="kanban-column-header">
                                    <span class="kanban-column-title">{{ $cat->title }}</span>
                                    <span class="kanban-column-count">{{ count($cat->posts) }}</span>
                                </div>
                                <div class="kanban-cards" data-category-id="{{ $cat->id }}">
                                    @foreach($cat->posts as $post)
                                    @include('modules.sticky_notes.partials.post_card', ['kanban' => true])
                                    @endforeach

                                    <div class="kanban-empty {{ count($cat->posts) > 0 ? 'd-none' : '' }}">
                                        <i class="fa fa-sticky-note-o"></i>
                                        <span>{{ trans('langNoStickyNotes') }}</span>
                                    </div>
                                </div>

                            </div>
                            @endforeach

                            @if(count($uncategorized) > 0)
                            <div class="kanban-column" data-category-id="0">
                                <div class="kanban-column-header">
                                    <span class="kanban-column-title">{{ trans('langUncategorized') }}</span>
                                    <span class="kanban-column-count">{{ count($uncategorized) }}</span>
                                </div>
                                <div class="kanban-cards" data-category-id="0">
                                    @foreach($uncategorized as $post)
                                    @include('modules.sticky_notes.partials.post_card', ['kanban' => true])
                                    @endforeach
                                    <div class="kanban-empty d-none">
                                        <i class="fa fa-sticky-note-o"></i>
                                        <span>{{ trans('langNoStickyNotes') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>

                        {{-- GRID VIEW --}}

                        @else

                        <div class="sticky-notes-grid">
                            @forelse($posts as $post)
                            @include('modules.sticky_notes.partials.post_card')
                            @empty
                            <p class="text-muted">{{ trans('langNoStickyNotes') }}</p>
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        @if($totalPages > 1)
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">

                                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $paginationUrl }}&page={{ $currentPage - 1 }}">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>

                                @for($i = 1; $i <= $totalPages; $i++)
                                    @if($i==1 || $i==$totalPages || abs($i - $currentPage) <=2)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $paginationUrl }}&page={{ $i }}">{{ $i }}</a>
                                    </li>
                                    @elseif(abs($i - $currentPage) == 3)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    @endfor

                                    <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $paginationUrl }}&page={{ $currentPage + 1 }}">
                                            <i class="fa fa-chevron-right"></i>
                                        </a>
                                    </li>

                            </ul>
                        </nav>

                        <p class="pagination-info">
                            {{ trans('langPage') }} {{ $currentPage }} από {{ $totalPages }}
                            &nbsp;·&nbsp;
                            {{ $totalPosts }} {{ trans('langStickyNotes') }}
                        </p>
                        @endif

                        @endif

                    </div>

                    {{-- Note Modal --}}
                    <div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                            <div class="modal-content">
                                <div id="noteModalContent">
                                    <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close">
                                    </button>
                                    <div id="noteModalText"></div>
                                    <div class="modal-footer-note">
                                        <span><i class="fa fa-user"></i> <span id="noteModalAuthor"></span></span>
                                        <span id="noteModalDate"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirm Delete Modal --}}
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                <div class="modal-body text-center p-4">
                                    <h4 class="fw-bold mb-2">{{ trans('langStickyNotesConfirmDelete') }}</h4>
                                    <p class="text-muted small mb-4">{{ trans('langStickyNotesConfirmDeleteSub') }}</p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                                            {{ trans('langCancel') }}
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm px-4" id="confirmDeleteBtn">
                                            {{ trans('langDelete') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>


<script>
    window.stickyNotesAjaxUrl = '{{ $ajaxUrl }}';
</script>

@endsection