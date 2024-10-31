@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @include('layouts.partials.show_alert')

                    <div class='row row-cols-lg-2 row-cols-1 g-4 mt-0'>
                        <div class='col'>
                            <div class='alert alert-warning mt-0'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langExpl2Upgrade') }}</span>
                            </div>
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langUpgToSee') }} <a href='{{ $link_changes_file }}' target=_blank>{{ trans('langHere') }}</a>.
                                    {{ trans('langUpgRead') }} <a href='{{ $upgrade_info_file }}' target='_blank' aria-label='{{ trans('langOpenNewTab') }}'>{{ trans('langUpgMan') }}</a> {{ trans('langUpgLastStep') }}
                              </span>
                            </div>
                            <div class='form-wrapper form-edit'>
                                <form role='form' action='upgrade.php' method='post'>
                                    <fieldset>
                                        <div class='text-heading-h2 mb-4'>{{ trans('langUpgDetails') }}</div>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='admin_username' class='control-label-notes'>{{ trans('langUsername') }}</label>
                                            <input id='admin_username' class='form-control' name='login' placeholder='{{ trans('langUsername') }}' type='text'>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='admin_password' class='control-label-notes'>{{ trans('langPass') }}</label>
                                            <input id='admin_password' class='form-control' name='password' placeholder='{{ trans('langPass') }}' type='password'>
                                        </div>
                                        <div class='form-group mt-5'>
                                            <button class='btn submitAdminBtn w-100 mb-2' type='submit' name='submit_1' value='{{ trans('langUpgrade') }}'>{{ trans('langUpgrade') }}</button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='col d-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>

            </div>
        </div>
    </div>
@endsection
