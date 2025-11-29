@extends('layouts.default')

@section('content')
    <style>
        :root {
            --panel: #ffffff;
            --border: #d9e2ec;
            --muted: #486581;
            --accent: #2680c2;
            --accent-hover: #186faf;
            --active: #0b5cab;
        }

        .page {
            padding: 1.5rem;
        }

        .page__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .page__header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-form__input {
            min-width: 320px;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-form__submit {
            padding: 0.5rem 1rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .search-form__submit:hover {
            background: var(--accent-hover);
        }

        .page__content {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
        }

        .filters {
            background: var(--panel);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid var(--border);
        }

        .filters h2 {
            margin-top: 0;
            font-size: 1.2rem;
        }

        .filters__active {
            margin: 0 0 0.75rem;
            font-size: 0.9rem;
        }

        .filters__clear {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .filters__clear:hover {
            text-decoration: underline;
        }

        .filters__section h3 {
            margin: 0 0 0.5rem;
            font-size: 1rem;
        }

        .filters ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.25rem;
        }

        .filters a {
            color: var(--accent);
            text-decoration: none;
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s ease;
        }

        .filters a:hover {
            background: rgba(38, 128, 194, 0.1);
        }

        .results {
            background: var(--panel);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            border: 1px solid var(--border);
        }

        .results__filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .results__filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: rgba(38, 128, 194, 0.12);
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .results__filter-chip:hover {
            background: rgba(38, 128, 194, 0.18);
        }

        .results__summary {
            margin-top: 0;
            color: var(--muted);
        }

        .results__list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 1rem;
        }

        .results__item {
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .results__item:last-child {
            border-bottom: none;
        }

        .results__item h2 {
            margin: 0 0 0.5rem;
            font-size: 1.25rem;
        }

        .results__item p {
            margin: 0 0 0.5rem;
            color: var(--muted);
        }

        .results__item dl {
            margin: 0;
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 0.25rem 1rem;
        }

        .results__item dt {
            font-weight: 600;
            color: var(--muted);
        }

        .results__item dd {
            margin: 0;
        }

        .pagination {
            margin-top: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .pagination__link {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--accent);
            transition: background 0.2s ease;
        }

        .pagination__link:hover {
            background: rgba(38, 128, 194, 0.1);
        }
    </style>

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        <div class="page">
                            <header class="page__header">
                                <h1>Solr Sandbox Search</h1>
                                <form class="search-form" action="" method="get">
                                    <input
                                        type="text"
                                        name="search_terms"
                                        value="{{ $search_terms }}"
                                        placeholder="Search..."
                                        class="search-form__input"
                                    >
                                    @foreach ($results['appliedFacets'] as $facetName => $facetValues)
                                        @foreach ($facetValues as $facetValue)
                                            <input type="hidden" name="facets[{{ $facetName }}][]" value="{{ $facetValue }}">
                                        @endforeach
                                    @endforeach
                                    <button type="submit" class="search-form__submit">Search</button>
                                </form>
                            </header>

                            <div class="page__content">
                                <aside class="filters">
                                    <h2>Filters</h2>
                                    @if (!empty($results['appliedFacets']))
                                        <p class="filters__active">
                                            <a class="filters__clear" href="?{{ http_build_query(['$search_terms' => $search_terms], '', '&', PHP_QUERY_RFC3986) }}">Clear all</a>
                                        </p>
                                    @endif

                                    @foreach ($results['facets'] as $facetField => $facetBuckets)
                                        @if (empty($facetBuckets))
                                            @continue
                                        @endif
                                        <?php
                                            $facetLabel = $results['facetLabels'][$facetField] ?? $facetField;
                                        ?>
                                        <section class="filters__section">
                                            <h3>{{ $facetLabel }}</h3>
                                            <ul>
                                                @foreach ($facetBuckets as $bucket)
                                                    <?php
                                                        $isActive = in_array($bucket['value'], $results['appliedFacets'][$facetField] ?? [], true);
                                                        $facetParams = '';//$service->toggleFacet($results['appliedFacets'], $facetField, $value);
                                                    ?>
                                                    <li>
                                                        <a
                                                            href="?<?= http_build_query(['$search_terms' => $search_terms, 'page' => 1, 'facets' => $facetParams], '', '&', PHP_QUERY_RFC3986) ?>"
                                                            class="<?= $isActive ? 'filters__link--active' : '' ?>"
                                                        >
                                                            {{ $bucket['value'] }} ({{ $bucket['count'] }})
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </section>
                                    @endforeach
                                </aside>

                                <main class="results">
                                    @if ($results['numFound'] === 0)
                                        <p>No results found.</p>
                                    @else
                                        @if (!empty($results['appliedFacets']))
                                            <div class="results__filters">
                                                @foreach ($results['appliedFacets'] as $facetField => $values)
                                                    <?php
                                                        $facetLabel = $facetLabels[$facetField] ?? $facetField;
                                                    ?>
                                                    @foreach ($values as $value)
                                                        <?php
                                                            $facetParams = '';//$service->toggleFacet($results['appliedFacets'], $facetField, $value);
                                                        ?>
                                                        <a
                                                            href="?<?= http_build_query(['$search_terms' => $search_terms, 'page' => 1, 'facets' => $facetParams], '', '&', PHP_QUERY_RFC3986) ?>"
                                                            class="results__filter-chip"
                                                        >
                                                            {{ $facetLabel }}:
                                                            {{ $value }}
                                                            <span aria-hidden="true">&times;</span>
                                                        </a>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        @endif
                                        <p class="results__summary">
                                            Showing {{ $results['start'] + 1 }} - {{ min($results['start'] + $results['rows'], $results['numFound']) }} of {{ $results['numFound'] }} results
                                        </p>
                                        <ul class="results__list">
                                            @foreach ($results['docs'] as $doc)
                                                <li class="results__item">
                                                    <?php
                                                        $title = (string)($doc['title'] ?? '[Untitled]');
                                                        $url = isset($doc['url']) && is_string($doc['url']) ? $doc['url'] : null;
                                                        $summary = null;
                                                        foreach (['description', 'content', 'units'] as $field) {
                                                            if (!empty($doc[$field]) && is_string($doc[$field])) {
                                                                $summary = $doc[$field];
                                                                break;
                                                            }
                                                        }
                                                        $skipFields = ['title', 'description', 'content', 'units'];
                                                        if ($url !== null) {
                                                            $skipFields[] = 'url';
                                                        }
                                                        $skipList = array_merge($skipFields, ['_version_', '_root_']);
                                                    ?>
                                                    <h2>
                                                        @if ($url !== null)
                                                            <a href="{{ $url  }}" target="_blank" rel="noreferrer">{{ $title }}</a>
                                                        @else
                                                            {{ $title }}
                                                        @endif
                                                    </h2>
                                                    @if ($summary !== null)
                                                        {{ $summary }}
                                                    @endif
                                                    <dl>
                                                        @foreach ($doc as $field => $value)
                                                            @if (in_array($field, $skipList, true))
                                                                @continue
                                                            @endif
                                                            <dt>{{ $field  }}</dt>
                                                            <dd>
                                                                @if (is_array($value))
                                                                    {{ implode(', ', $value) }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </dd>
                                                        @endforeach
                                                    </dl>
                                                </li>
                                            @endforeach
                                        </ul>

                                        @if ($results['totalPages'] > 1)
                                            <nav class="pagination">
                                                @if ($page > 1)
                                                    <a href="?{{ http_build_query(['$search_terms' => $search_terms, 'page' => $page - 1, 'facets' => $results['appliedFacets']], '', '&', PHP_QUERY_RFC3986) }}" class="pagination__link">Previous</a>
                                                @endif

                                                @for ($i = 1; $i <= $results['totalPages']; $i++)
                                                    <a
                                                        href="?{{ http_build_query(['$search_terms' => $search_terms, 'page' => $i, 'facets' => $results['appliedFacets']], '', '&', PHP_QUERY_RFC3986) }}"
                                                        class="pagination__link <?= $i === $page ? 'pagination__link--current' : '' ?>"
                                                    >
                                                        {{ $i }}
                                                    </a>
                                                @endfor

                                                @if ($page < $results['totalPages'])
                                                    <a href="?{{ http_build_query(['$search_terms' => $search_terms, 'page' => $page + 1, 'facets' => $results['appliedFacets']], '', '&', PHP_QUERY_RFC3986) }}" class="pagination__link">Next</a>
                                                @endif
                                            </nav>
                                        @endif
                                    @endif
                                </main>
                            </div> {{-- page__content --}}
                        </div> {{-- page --}}

                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection

