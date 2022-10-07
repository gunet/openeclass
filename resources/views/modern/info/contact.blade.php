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
                        <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langContact')}}</div>
                        {!! $action_bar !!}
                    </div>

                    
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='panel shadow-lg p-3 bg-body rounded'>
                            <div class='panel-body'>
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langPostMail') }}</strong>&nbsp;{{ $Institution }}
                                </div>
                                @if(!empty($postaddress))
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langInstitutePostAddress') }}: </strong>&nbsp;{!! $postaddress !!}
                                </div>
                                @endif

                                @if (empty($phone))
                                    <div class='p-1'>
                                        <strong class='control-label-notes'>{{ trans('langPhone') }}:</strong>
                                        <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                    </div>
                                @else
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langPhone') }}:&nbsp;</strong>
                                    {{ $phone }}
                                </div>
                                @endif

                                @if (empty($fax))
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langFax') }}</strong>
                                    <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                </div>
                                @else
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langFax') }}&nbsp;</strong>
                                    {{ $fax }}
                                </div>
                                @endif

                                @if (empty($emailhelpdesk))
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langEmail') }}:</strong>
                                    <span class='not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                </div>
                                @else
                                <div class='p-1'>
                                    <strong class='control-label-notes'>{{ trans('langEmail') }}: </strong>
                                    <a href='mailto:$emailhelpdesk'>{{ $emailhelpdesk }}</a>
                                </div>
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
