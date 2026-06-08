@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @include('layouts.partials.show_alert')

            <div class='col-12 mt-4'>
                <div class='card panelCard px-lg-4 py-lg-3 p-3'>
                    <div class='card-header border-0'>
                        <h3>{{ trans('langInsertExternalRepo') }}</h3>
                    </div>
                    <div class='card-body'>
                        @if (count($repositories) > 0)
                            <form id='extrepo-search-form'>
                                <div class='row mb-4'>
                                    {{-- Repository Selector --}}
                                    <div class='col-md-4 mb-3'>
                                        <label for='repository_id' class='form-label'>
                                            {{ trans('langSelectRepository') }}
                                        </label>
                                        <select class='form-select' id='repository_id' name='repository_id' required>
                                            <option value=''>{{ trans('langChoose') }}...</option>
                                            @foreach ($repositories as $repo)
                                                @php
                                                    $typeInfo = $repositoryTypes[$repo->type] ?? ['name' => $repo->type, 'icon' => 'fa-database'];
                                                @endphp
                                                <option value='{{ $repo->id }}' data-type='{{ $repo->type }}'>
                                                    {{ $repo->name }} ({{ $typeInfo['name'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Search Input --}}
                                    <div class='col-md-6 mb-3'>
                                        <label for='search_query' class='form-label'>
                                            {{ trans('langSearch') }}
                                        </label>
                                        <input type='text' 
                                               class='form-control' 
                                               id='search_query' 
                                               name='query' 
                                               placeholder='{{ trans('langSearchPlaceholder') }}'
                                               required>
                                    </div>

                                    {{-- Search Button --}}
                                    <div class='col-md-2 mb-3 d-flex align-items-end'>
                                        <button type='submit' class='btn submitAdminBtn w-100' id='search-btn'>
                                            <i class='fa fa-search me-2'></i>{{ trans('langSearch') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Loading Indicator --}}
                            <div id='search-loading' class='text-center py-5' style='display: none;'>
                                <i class='fa fa-spinner fa-spin fa-3x text-primary'></i>
                                <p class='mt-3'>{{ trans('langLoading') }}...</p>
                            </div>

                            {{-- Search Results --}}
                            <div id='search-results' style='display: none;'>
                                <div class='d-flex justify-content-between align-items-center mb-3'>
                                    <h5 class='mb-0'>
                                        {{ trans('langSearchResults') }} 
                                        (<span id='results-count'>0</span>)
                                    </h5>
                                    <button type='button' class='btn btn-outline-secondary btn-sm' id='clear-results'>
                                        <i class='fa fa-times me-1'></i>{{ trans('langClear') }}
                                    </button>
                                </div>

                                {{-- Results Grid --}}
                                <div id='results-grid' class='row'></div>

                                {{-- Pagination --}}
                                <nav id='results-pagination' class='mt-4' style='display: none;'>
                                    <ul class='pagination justify-content-center'>
                                    </ul>
                                </nav>
                            </div>

                            {{-- No Results Message --}}
                            <div id='no-results' class='alert alert-info' style='display: none;'>
                                <i class='fa fa-info-circle me-2'></i>
                                {{ trans('langNoResults') }}
                            </div>

                            {{-- Selected Resources Form --}}
                            <form id='selected-resources-form' method='post' action='{{ $urlAppend }}modules/units/insert.php?course={{ $course_code }}&id={{ $unit_id }}&type=extrepo' style='display: none;'>
                                {!! generate_csrf_token_form_field() !!}
                                
                                <div class='card bg-light mt-4'>
                                    <div class='card-header'>
                                        <h5 class='mb-0'>
                                            <i class='fa fa-check-circle me-2'></i>
                                            {{ trans('langSelectedResources') }}
                                            (<span id='selected-count'>0</span>)
                                        </h5>
                                    </div>
                                    <div class='card-body'>
                                        <div id='selected-list' class='mb-3'></div>
                                        <div class='d-flex gap-2'>
                                            <button type='submit' name='submit_extrepo' class='btn submitAdminBtn'>
                                                <i class='fa fa-plus me-2'></i>{{ trans('langAddToUnit') }}
                                            </button>
                                            <button type='button' class='btn btn-outline-secondary' id='clear-selection'>
                                                <i class='fa fa-times me-2'></i>{{ trans('langClearSelection') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        @else
                            <div class='alert alert-warning'>
                                <i class='fa fa-exclamation-triangle me-2'></i>
                                {{ trans('langNoExternalRepos') }}
                                @if ($is_admin)
                                    <a href='{{ $urlAppend }}modules/admin/externalreposconf.php' class='alert-link'>
                                        {{ trans('langConfigureRepositories') }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Back Button --}}
                        <div class='mt-4'>
                            <a href='{{ $backUrl }}' class='btn cancelAdminBtn'>
                                <i class='fa fa-arrow-left me-2'></i>{{ trans('langBack') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Result Item Template --}}
<template id='result-item-template'>
    <div class='col-md-6 col-lg-4 mb-4 result-item'>
        <div class='card h-100'>
            <div class='card-img-top result-thumbnail' style='height: 150px; background-color: #f5f5f5; overflow: hidden;'>
                <i class='fa fa-file fa-3x text-muted' style='display: flex; align-items: center; justify-content: center; height: 100%;'></i>
            </div>
            <div class='card-body'>
                <h6 class='card-title result-title text-truncate' title=''></h6>
                <p class='card-text result-description small text-muted' style='max-height: 60px; overflow: hidden;'></p>
                <div class='d-flex justify-content-between align-items-center'>
                    <span class='badge bg-secondary result-type'></span>
                    <small class='text-muted result-source'></small>
                </div>
            </div>
            <div class='card-footer bg-transparent'>
                <div class='form-check form-switch mb-2'>
                    <input class='form-check-input rich-preview-toggle' type='checkbox' role='switch'>
                    <label class='form-check-label small text-muted'>{{ trans('langExtRepoRichPreview') }}</label>
                </div>
                <div class='d-flex gap-2'>
                    <a href='#' target='_blank' class='btn btn-outline-primary btn-sm result-link'>
                        <i class='fa fa-external-link'></i>
                    </a>
                    <button type='button' class='btn submitAdminBtn btn-sm flex-grow-1 select-resource-btn'>
                        <i class='fa fa-plus me-1'></i>{{ trans('langSelect') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Selected Item Template --}}
<template id='selected-item-template'>
    <div class='selected-item d-flex justify-content-between align-items-center p-2 bg-white rounded mb-2'>
        <div class='d-flex align-items-center'>
            <i class='fa fa-check-circle text-success me-2'></i>
            <span class='selected-title'></span>
            <span class='badge bg-secondary ms-2 selected-type'></span>
        </div>
        <button type='button' class='btn btn-link text-danger remove-selected'>
            <i class='fa fa-times'></i>
        </button>
        <input type='hidden' name='extrepo[]' class='selected-data'>
    </div>
</template>

<script>
$(document).ready(function() {
    var config = window.extRepoConfig || {};
    var selectedResources = {};
    var currentPage = 1;
    var currentQuery = '';
    var currentRepoId = 0;
    
    // Search form submission
    $('#extrepo-search-form').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        performSearch();
    });
    
    // Perform search
    function performSearch() {
        currentRepoId = $('#repository_id').val();
        currentQuery = $('#search_query').val().trim();
        
        if (!currentRepoId || !currentQuery) {
            return;
        }
        
        // Show loading
        $('#search-loading').show();
        $('#search-results').hide();
        $('#no-results').hide();
        
        $.post(config.searchUrl, {
            repository_id: currentRepoId,
            query: currentQuery,
            page: currentPage,
            per_page: 12,
            token: config.csrfToken
        }, function(response) {
            // Log the full JSON response to console for debugging
            console.log('External Repo Search Response:', JSON.stringify(response, null, 2));
            
            $('#search-loading').hide();
            
            if (response.success && response.items && response.items.length > 0) {
                displayResults(response);
            } else if (response.error) {
                showError(response.error);
            } else {
                $('#no-results').show();
            }
        }, 'json').fail(function(xhr, status, error) {
            $('#search-loading').hide();
            console.error('External Repo Search Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            showError(config.lang.error);
        });
    }
    
    // Display search results
    function displayResults(data) {
        var $grid = $('#results-grid').empty();
        var template = document.getElementById('result-item-template');
        
        $.each(data.items, function(i, item) {
            var $item = $(template.content.cloneNode(true));
            
            // Set data
            $item.find('.result-title').text(item.title).attr('title', item.title);
            $item.find('.result-description').text(item.description || '');
            $item.find('.result-type').text(item.type || 'resource');
            $item.find('.result-source').text(item.repository_name || '');
            $item.find('.result-link').attr('href', item.url);
            
            // Set thumbnail - always show image if available, otherwise use generic placeholder
            var $thumbnail = $item.find('.result-thumbnail');
            var icon = getTypeIcon(item.type);
            var bgGradient = getTypeGradient(item.type);
            
            if (item.thumbnail && item.thumbnail.trim() !== '') {
                // Show actual thumbnail image with fallback to placeholder on error
                var $img = $('<img>', {
                    src: item.thumbnail,
                    class: 'img-fluid',
                    css: {
                        'height': '150px',
                        'width': '100%',
                        'object-fit': 'cover',
                        'display': 'block'
                    }
                });
                
                // Handle image load error - replace with placeholder
                $img.on('error', function() {
                    var placeholder = $('<div>', {
                        css: {
                            'background': bgGradient,
                            'height': '150px',
                            'display': 'flex',
                            'align-items': 'center',
                            'justify-content': 'center',
                            'border-radius': '4px 4px 0 0'
                        }
                    }).html('<i class="fa ' + icon + ' fa-3x text-white"></i>');
                    $thumbnail.html(placeholder);
                });
                
                $thumbnail.html($img);
            } else {
                // Show generic placeholder with icon
                var placeholder = $('<div>', {
                    css: {
                        'background': bgGradient,
                        'height': '150px',
                        'display': 'flex',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'border-radius': '4px 4px 0 0'
                    }
                }).html('<i class="fa ' + icon + ' fa-3x text-white"></i>');
                $thumbnail.html(placeholder);
            }
            
            // Store item data
            $item.find('.result-item').data('resource', item);
            
            // Check if already selected
            if (selectedResources[item.id]) {
                $item.find('.select-resource-btn')
                    .removeClass('submitAdminBtn')
                    .addClass('btn-success disabled')
                    .html('<i class="fa fa-check me-1"></i>{{ trans('langSelected') }}');
            }
            
            $grid.append($item);
        });
        
        // Update count
        $('#results-count').text(data.total || data.items.length);
        $('#search-results').show();
        
        // Update pagination
        updatePagination(data);
    }
    
    // Get icon for resource type
    function getTypeIcon(type) {
        var icons = {
            'video': 'fa-video',
            'image': 'fa-image',
            'article': 'fa-newspaper',
            'document': 'fa-file-alt',
            'audio': 'fa-music',
            'learning_object': 'fa-graduation-cap'
        };
        return icons[type] || 'fa-file';
    }
    
    // Get background gradient for resource type
    function getTypeGradient(type) {
        var gradients = {
            'video': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'image': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'article': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'document': 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'audio': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
            'learning_object': 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)'
        };
        return gradients[type] || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }
    
    // Update pagination
    function updatePagination(data) {
        var $pagination = $('#results-pagination ul').empty();
        var totalPages = data.totalPages || 1;
        
        if (totalPages <= 1) {
            $('#results-pagination').hide();
            return;
        }
        
        // Previous button
        $pagination.append(
            '<li class="page-item ' + (currentPage <= 1 ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">&laquo;</a></li>'
        );
        
        // Page numbers
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        
        for (var i = startPage; i <= endPage; i++) {
            $pagination.append(
                '<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>'
            );
        }
        
        // Next button
        $pagination.append(
            '<li class="page-item ' + (currentPage >= totalPages ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">&raquo;</a></li>'
        );
        
        $('#results-pagination').show();
    }
    
    // Pagination click
    $('#results-pagination').on('click', '.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page && page !== currentPage) {
            currentPage = page;
            performSearch();
        }
    });
    
    // Select resource
    $('#results-grid').on('click', '.select-resource-btn', function() {
        var $item = $(this).closest('.result-item');
        var resource = $item.data('resource');
        
        if (!resource || selectedResources[resource.id]) {
            return;
        }

        // Capture this card's rich-preview toggle state
        resource.rich_preview = $item.find('.rich-preview-toggle').is(':checked') ? 1 : 0;

        // Add to selected
        selectedResources[resource.id] = resource;
        
        // Update button
        $(this).removeClass('submitAdminBtn')
               .addClass('btn-success disabled')
               .html('<i class="fa fa-check me-1"></i>{{ trans('langSelected') }}');
        
        // Add to selected list
        addToSelectedList(resource);
    });

    // Rich-preview toggle on a result card
    $('#results-grid').on('change', '.rich-preview-toggle', function() {
        var $item = $(this).closest('.result-item');
        var resource = $item.data('resource');
        if (!resource) {
            return;
        }
        resource.rich_preview = this.checked ? 1 : 0;
        $item.data('resource', resource);
        // If this resource is already selected, keep the queued data in sync
        if (selectedResources[resource.id]) {
            selectedResources[resource.id].rich_preview = resource.rich_preview;
            $('#selected-list .selected-item[data-id="' + resource.id + '"] .selected-data')
                .val(JSON.stringify(resource));
        }
    });

    // Add to selected list
    function addToSelectedList(resource) {
        var template = document.getElementById('selected-item-template');
        var $item = $(template.content.cloneNode(true));
        
        $item.find('.selected-title').text(resource.title);
        $item.find('.selected-type').text(resource.type || 'resource');
        $item.find('.selected-data').val(JSON.stringify(resource));
        $item.find('.selected-item').attr('data-id', resource.id);
        
        $('#selected-list').append($item);
        updateSelectedCount();
        $('#selected-resources-form').show();
    }
    
    // Remove from selected
    $('#selected-list').on('click', '.remove-selected', function() {
        var $item = $(this).closest('.selected-item');
        var id = $item.data('id');
        
        delete selectedResources[id];
        $item.remove();
        
        // Update button in results
        $('#results-grid .result-item').each(function() {
            var resource = $(this).data('resource');
            if (resource && resource.id === id) {
                $(this).find('.select-resource-btn')
                    .removeClass('btn-success disabled')
                    .addClass('submitAdminBtn')
                    .html('<i class="fa fa-plus me-1"></i>{{ trans('langSelect') }}');
            }
        });
        
        updateSelectedCount();
        
        if (Object.keys(selectedResources).length === 0) {
            $('#selected-resources-form').hide();
        }
    });
    
    // Update selected count
    function updateSelectedCount() {
        var count = Object.keys(selectedResources).length;
        $('#selected-count').text(count);
    }
    
    // Clear selection
    $('#clear-selection').on('click', function() {
        selectedResources = {};
        $('#selected-list').empty();
        $('#selected-resources-form').hide();
        
        // Reset all buttons in results
        $('#results-grid .select-resource-btn')
            .removeClass('btn-success disabled')
            .addClass('submitAdminBtn')
            .html('<i class="fa fa-plus me-1"></i>{{ trans('langSelect') }}');
        
        updateSelectedCount();
    });
    
    // Clear results
    $('#clear-results').on('click', function() {
        $('#results-grid').empty();
        $('#search-results').hide();
        $('#no-results').hide();
        $('#search_query').val('').focus();
    });
    
    // Show error
    function showError(message) {
        $('#no-results').html('<i class="fa fa-exclamation-circle me-2"></i>' + message).show();
    }
});
</script>

@endsection

