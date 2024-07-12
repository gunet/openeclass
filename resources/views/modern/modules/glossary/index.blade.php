@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col_maincontent_active">

                        <div class="row">

                                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                                <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                    <div class="offcanvas-header">
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                    </div>
                                </div>

                                @include('layouts.partials.legend_view')


                                @if($is_editor)
                                    {!! isset($action_bar) ?  $action_bar : '' !!}
                                @endif

                                @include('layouts.partials.show_alert') 

                                @if ($is_editor == 1 && $expand_glossary && $total_glossary_terms > $max_glossary_terms)
                                    <div class='col-12'>
                                        <div class='alert alert-warning'>
                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{!! trans('langGlossaryOverLimit',["<b>$max_glossary_terms</b>"]) !!}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($glossary_index && count($prefixes) > 1)
                                    <div class="col-12 mb-3">
                                        <nav aria-label="...">
                                            <ul class="pagination p-0 pagination-glossary gap-2 flex-wrap" id="myPag">
                                                @foreach ($prefixes as $key => $letter)
                                                    <li class="page-item {!! (!isset($_GET['prefix']) && !$cat_id && !$key) ||
                                                            (isset($_GET['prefix']) && $_GET['prefix'] == $letter)? " active" : "" !!}">
                                                        <a class="page-link rounded-2" tabindex="-1" aria-disabled="true" href="{!! $base_url."&amp;prefix=" . urlencode($letter)  !!}">{{ $letter }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </nav>
                                    </div>
                                @endif

                                @if ($glossary_terms)
                                    <div class='col-12'>
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
                                            @foreach($glossary_terms as $glossary_term)

                                                <div class="col">
                                                    <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                                        <div class="card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap">

                                                                <a class='ViewGroup TextBold' href='{{ $base_url."&id=" . $glossary_term->id }}'>
                                                                    {{ $glossary_term->term }}
                                                                </a>

                                                            @if($is_editor)
                                                                <div>
                                                                    {!!
                                                                        action_button(array(
                                                                            array('title' => trans('langEditChange'),
                                                                                'url' => $base_url ."&amp;edit=". getIndirectReference($glossary_term->id),
                                                                                'icon' => 'fa-edit'),
                                                                            array('title' => trans('langDelete'),
                                                                                'url' => $base_url ."&amp;delete=". getIndirectReference($glossary_term->id),
                                                                                'icon' => 'fa-xmark',
                                                                                'class' => 'delete',
                                                                                'confirm' => trans('langConfirmDelete'))
                                                                            )
                                                                        )
                                                                    !!}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text">
                                                                <p class='mb-0 TextBold'>{{ trans('langGlossaryDefinition') }}</p>
                                                                <p class='small-text'>
                                                                    {{ $glossary_term->definition }}
                                                                </p>
                                                            </p>
                                                            <p class="card-text mt-3">
                                                                <p class='mb-0 TextBold'>{{ trans('langCategory') }}</p>
                                                                <p class='small-text'>
                                                                    @if ($glossary_term->category_id)
                                                                        <a href='{{ $base_url }}&amp;cat={{ $glossary_term->category_id }}'>
                                                                            {{ $categories[$glossary_term->category_id] }}
                                                                        </a>
                                                                    @else
                                                                        {{ trans('langNoInfoAvailable') }}
                                                                    @endif
                                                                </p>
                                                            </p>
                                                            <p class="card-text mt-3">
                                                                <p class='mb-0 TextBold'>URL</p>
                                                                <p class='small-text'>
                                                                    @if ($glossary_term->url)
                                                                        <a href='{{ $glossary_term->url }}' target='_black'>
                                                                            {{ $glossary_term->url }}
                                                                        </a>
                                                                    @else
                                                                        {{ trans('langNoInfoAvailable') }}
                                                                    @endif
                                                                </p>
                                                            </p>
                                                            <p class="card-text mt-3">
                                                                <p class='mb-0 TextBold'>{{ trans('langComments') }}</p>
                                                                <p class='small-text'>
                                                                    @if ($glossary_term->notes)
                                                                        {!! standard_text_escape($glossary_term->notes) !!}
                                                                    @else
                                                                        {{ trans('langNoInfoAvailable') }}
                                                                    @endif
                                                                </p>
                                                            </p>
                                                        </div>
                                                        <div class='card-footer d-flex justify-content-center align-items-center border-0 mb-2'>

                                                        </div>
                                                    </div>
                                                </div>

                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoGlossary') }}</span></div></div>
                                @endif
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
