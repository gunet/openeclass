@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container-fluid py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    <div class='alphabetic_index'>
                        {!! $prefixes !!}
                    </div>
                    <div class='table-responsive glossary-categories'>
                        <table class='table-default'>
                            <thead>
                            <tr class='list-header'>
                                <th>{{ trans('langGlossaryTerm') }}</th>
                                <th>{{ trans('langGlossaryDefinition') }}</th>
                            </tr></thead>
                            <tbody>
                            @foreach ($glossary as $data)
                                <tr>
                                    <td>
                                        <strong>{{ $data->term }}</strong><br>
                                        @if (!empty($data->category_id))
                                            <span>
                                                <small>
                                                    <span class='text-muted'>{{ trans('langCategory') }}: {{ $categories[$data->category_id] }}</span>
                                                </small>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!empty($data->definition))
                                            <em>{{ $data->definition }}</em>
                                        @endif
                                        @if (!empty($data->url))
                                            <div>
                                                <span class='term-url'>
                                                    <small>
                                                        <a href='{{ $data->url }}' target='_blank' aria-label='(opens in a new tab)'>{{ $data->url }}&nbsp;&nbsp;
                                                        <i class='fa fa-external-link' style='color:#444;'></i></a>
                                                    </small>
                                                </span>
                                            </div>
                                        @endif
                                        @if (!empty($data->notes))
                                            <br><u>{{ trans('langComments') }}:</u>
                                            <div class='text-muted'>{!! standard_text_escape($data->notes) !!}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
</div>
</div>
@endsection