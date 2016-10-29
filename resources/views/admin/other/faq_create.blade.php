@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    @if ($modify || $new)
        <div class='row'>
            <div class='col-xs-12'>
                <div class='form-wrapper'>
                    <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                    <input type='hidden' name='id' value='{{ $id or '' }}'>
                        <div class='form-group'>
                            <label for='question' class='col-sm-2 control-label'>{{ trans('langFaqQuestion') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='question' value='{{ $faq_mod->title or '' }}' />
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='answer' class='col-sm-2 control-label'>{{ trans('langFaqAnswer') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                            <div class='col-sm-10'>{!! $editor !!}</div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>{{ trans('langCPFFieldRequired') }}</small>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <button type="submit" class="btn btn-primary" name="{{ $new? "submitFaq" : "modifyFaq" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn btn-default">{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
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
                                <div class='panel list-group-item' data-id='{{ $faq->id }}'>
                                    <div class='panel-heading' role='tab' id='heading-{{ $faq->id }}'>
                                        <h4 class='panel-title'>
                                            <a role='button' data-toggle='collapse' data-parent='#accordion' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                <span class="indexing">{{ $key+1 }}.</span>{!! $faq->title !!} <span class='caret'></span>
                                            </a>
                                            <a class='forDelete' href='javascript:void(0);' data-id='{{ $faq->id }}' data-order='{{ $faq->order }}'><span class='fa fa-times text-danger pull-right' data-toggle='tooltip' data-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                            <a href='javascript:void(0);'><span class='fa fa-arrows pull-right' data-toggle='tooltip' data-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                            <a href='{{ $_SERVER['SCRIPT_NAME'] }}?faq=modify&id={{ $faq->id }}'><span class='fa fa-pencil-square pull-right' data-toggle='tooltip' data-placement='top' title='{{ trans('langEdit') }}'></span></a>
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
    @endif

@endsection
