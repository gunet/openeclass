@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='col-12'>
                        @if (count($faqs) > 0){!! $action_bar !!} @endif
                    </div>

                    <div class='col-12'>
                        <div class='panel'>
                            <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                @if (count($faqs) == 0)
                                    <div class='alert alert-warning'>
                                        <div class='text-center'>{{ trans('langFaqNoEntries') }}</div>
                                    </div>
                                @else


                                    <ul class="list-group list-group-flush">
                                        @foreach ($faqs as $key => $faq)
                                        
                                                <li class="list-group-item border-0 Shadow-cols p-3 mb-4">
                                                    <a class='d-flex align-items-start control-label-notes' role='button' data-bs-toggle='collapse' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                        <span class='pe-2'>{{ $key+1 }}.</span>
                                                        <span>{!! $faq->title !!}</span>
                                                        
                                                    </a>

                                                    <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse border-0 bg-light rounded-0' role='tabpanel' aria-labelledby='heading{{ $faq->id }}' data-bs-parent='#accordion'>
                                                        <div class='panel-body px-5'>
                                                            {!! $faq->body !!}
                                                        </div>
                                                    </div>
                                                </li>
                                               
                                                
                                        @endforeach
                                    </ul>

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
