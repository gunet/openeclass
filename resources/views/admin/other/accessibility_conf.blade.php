@extends('layouts.default')

@section('content')
<main id="main" class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                @include('layouts.partials.legend_view')

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit'>
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <div class='landing-default'>
                                    @foreach ($selectable_langs as $langCode => $langName)
                                        <div class='form-group mb-4'>
                                            <label for='accessibility_text_{{ $langCode }}' class='col-sm-12 control-label-notes'>{{ trans('langText') }}: <span>({{ $langName }})</span></label>
                                            <div class='col-sm-12'>
                                                {!! rich_text_editor('accessibility_text_'.$langCode, 5, 20, $policyText[$langCode], options: array('id' => 'accessibility_text_'.$langCode)) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langViewShow') }}: </div>
                                    <div class='col-sm-12'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                <input id='accessibilityLink' type='checkbox' name='activate_accessibility_text' {{ $cbox_activate_accessibility_text }} >
                                                <span class='checkmark'></span>
                                                {{ trans('langViewShow') }} {{ trans('langAccessibility') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        <a class='btn cancelAdminBtn' href='index.php'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>

                        </form>
                    </div>
                </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>

            </div>
        </div>
</main>
@endsection
