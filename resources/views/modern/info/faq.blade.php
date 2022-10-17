@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langFaq')}}</div>
                        @if (count($faqs) != 0){!! $action_bar !!} @endif
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='panel'>
                            <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                @if (count($faqs) == 0)
                                    <div class='alert alert-warning'>
                                        <div class='text-center'>{{ trans('langFaqNoEntries') }}</div>
                                    </div>
                                @else
                                    @foreach ($faqs as $key => $faq)
                                        <div class='panel panel-default mt-3 rounded-0 overflow-auto'>
                                            <div class='panel-heading rounded-0' role='tab' id='heading{{ $faq->id }}' style='border-bottom-right-radius:15px; border-bottom-left-radius:15px;'>
                                                
                                                <a class='control-label-notes' role='button' data-bs-toggle='collapse' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                    <span>{{ $key+1 }}.</span>{!! $faq->title !!}
                                                </a>
                                                <span class='fa fa-arrow-down orangeText ps-2 fs-6'></span>
                                                
                                            </div>
                                            <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse' role='tabpanel' aria-labelledby='heading{{ $faq->id }}' data-bs-parent='#accordion'>
                                                <div class='panel-body ps-3 rounded-0'>
                                                    {!! $faq->body !!}
                                                </div>
                                            </div>
                                        </div>
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
