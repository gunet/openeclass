@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => [0 => ['bread_href' => 'about.php', 'bread_text' => trans('langFaq') ]]])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>

                            {!! $action_bar !!}
                        </div>
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='panel shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                @if (count($faqs) == 0)
                                    <div class='panel list-group-item'>
                                        <div class='text-center text-muted'><em>{{ trans('langFaqNoEntries') }}</em> <br><br> <em>{{ trans('langFaqAddNew') }}</em></div>
                                    </div>
                                @else
                                    @foreach ($faqs as $key => $faq)
                                        <div class='panel'>
                                            <div class='panel-heading' role='tab' id='heading{{ $faq->id }}'>
                                                <h5 class='text-secondary'>
                                                    <a class='control-label-notes' role='button' data-bs-toggle='collapse' data-bs-parent='#accordion' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                        <span>{{ $key+1 }}.</span>{!! $faq->title !!} <span class='caret'></span>
                                                    </a>
                                                    <span class='fa fa-arrow-down text-warning ps-2 fs-6'></span>
                                                </h5>
                                            </div>
                                            <div id='faq-{{ $faq->id }}' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading{{ $faq->id }}'>
                                                <div class='panel-body ps-3'>
                                                    <p><strong><u>{{ trans('langFaqAnswer') }}:</u></strong></p>
                                                    {!! $faq->body !!}
                                                </div>
                                            </div>
                                        </div><hr>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
