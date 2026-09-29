@php
    $colors  = ['#fff9c4','#c8f7c5','#aed6f1','#f9d5d3','#e8daef','#fce5cd'];
    $bg      = $post->color ?? $colors[array_rand($colors)];
    $isLong  = mb_strlen($post->content) > 120;
    $preview = $isLong ? mb_substr($post->content, 0, 100) : $post->content;
    $isKanban = isset($kanban) && $kanban;
@endphp

<div class="sticky-note {{ $isKanban ? 'kanban-card' : '' }}"
     style="background-color: {{ $bg }};"
     data-post-id="{{ $post->id }}"
     @if($isKanban) draggable="{{ $isDraggable }}" @endif>

    <div class="sticky-note-body">
        <span class="note-preview">{{ $preview }}@if($isLong)...@endif</span>
        @if($isLong)
        <a href="#"
            class="note-read-more d-block mt-1"
            data-bs-toggle="modal"
            data-bs-target="#noteModal"
            data-content="{{ e($post->content) }}"
            data-color="{{ $bg }}"
            data-author="{{ $post->givenname }} {{ $post->surname }}"
            data-date="{{ date('d/m/Y H:i', strtotime($post->created_at)) }}">
            {{ trans('langReadHelp') }}
        </a>
        @endif
    </div>

    <div class="sticky-note-footer">
        <small><i class="fa fa-user"></i> {{ $post->givenname }} {{ $post->surname }}</small>
        <small>{{ date('d/m/Y H:i', strtotime($post->created_at)) }}</small>

        @if($allowEdit || $allowDelete)
        <div class="note-actions">
            @if($allowEdit && ($is_editor || $post->user_id == $uid))
            <a href="new_post.php?course={{ $course_code }}&topic={{ $topic->id }}&id={{ $post->id }}"
                class="note-action-btn" title="{{ trans('langEdit') }}">
                <i class="fa fa-pencil"></i>
            </a>
            @endif
            @if($allowDelete && ($is_editor || $post->user_id == $uid))
            <a href="#"
                class="note-action-btn note-delete-btn"
                data-post-id="{{ $post->id }}"
                title="{{ trans('langDelete') }}">
                <i class="fa fa-trash"></i>
            </a>
            @endif
        </div>
        @endif
    </div>

</div>