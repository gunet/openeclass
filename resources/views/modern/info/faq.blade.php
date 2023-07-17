@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                
                <div class="row">
                   
                    <div class='col-12'>
                        <h1>{{ trans('langFaq')}}</h1>
                        <div class='row rowMargin row-cols-1 row-cols-lg-2 g-lg-5'>
                            <div class='col-lg-6 col-12'>
                                <div class='panel'>
                                    <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                    
                                        @if (count($faqs) == 0)
                                            <div class='alert alert-warning mt-5'>
                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                                {{ trans('langFaqNoEntries') }}</span>
                                            </div>
                                        @else


                                            <ul class="list-group list-group-flush mt-5">
                                                @foreach ($faqs as $key => $faq)
                                                
                                                        <li class="list-group-item px-0 mb-4 bg-transparent">
                                                            <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#faq-{{ $faq->id }}' aria-expanded='false' aria-controls='#{{ $faq->id }}'>
                                                                <span class='fa-solid fa-chevron-down'></span>
                                                                {!! $faq->title !!}
                                                                
                                                            </a>

                                                            <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' aria-labelledby='heading{{ $faq->id }}' data-bs-parent='#accordion'>
                                                                <div class='panel-body bg-transparent Neutral-900-cl px-4'>
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
                            <div class='col-lg-6 col-12 mt-lg-0 mt-4'>
                                <img src='{{ $urlAppend }}template/modern/img/faqImg.png' />
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
   
</div>

@endsection
