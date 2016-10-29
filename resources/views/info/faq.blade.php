@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel'>
                <div class='panel-group faq-section' id='accordion' role='tablist' aria-multiselectable='true'>
                    @if (count($faqs) == 0)
                        <div class='panel list-group-item'>
                            <div class='text-center text-muted'><em>{{ trans('langFaqNoEntries') }}</em> <br><br> <em>{{ trans('langFaqAddNew') }}</em></div>
                        </div>
                    @else
                        @foreach ($faqs as $key => $faq)
                            <div class='panel'>
                                <div class='panel-heading' role='tab' id='heading{{ $faq->id }}'>
                                    <h4 class='panel-title'>
                                        <a role='button' data-toggle='collapse' data-parent='#accordion' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                            <span>{{ $key+1 }}.</span>{!! $faq->title !!} <span class='caret'></span>
                                        </a>
                                    </h4>
                                </div>
                                <div id='faq-{{ $faq->id }}' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading{{ $faq->id }}'>
                                    <div class='panel-body'>
                                        <p><strong><u>{{ trans('langFaqAnswer') }}:</u></strong></p>
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

@endsection
