@extends('layouts.default')

@section('content')
<div class="p-xl-5 py-lg-3 pb-0 pt-0">
    <div class="container-fluid main-container">
        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='alphabetic_index'>
                        {!! $prefixes !!}
                    </div>
                    <div class='table-responsive glossary-categories'>
                        <table class='table-default'>
                            <tr class='list-header'>
                                <th class='text-start'>{{ trans('langGlossaryTerm') }}</th>
                                <th class='text-start'>{{ trans('langGlossaryDefinition') }}</th>
                            </tr>
                            @foreach ($glossary as $data)
                                <tr>
                                    <td width='150'>
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
                                                        <a href='{{ $data->url }}' target='_blank'>{{ $data->url }}&nbsp;&nbsp;
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
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection