@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            {!! action_bar([
                [ 'title' => trans('langBack'),
                  'url' => "tenants.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label' ],
            ]) !!}

            @include('layouts.partials.show_alert')

            <div class='col-lg-6 col-12'>

                <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' role='form' name='edit-tenant' method='post' action='tenant_edit.php' onsubmit='return validateNodePickerForm();'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='name' class='col-sm-12 control-label-notes'>{{ trans('langName') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                <div class='col-sm-12'>
                                    <input id='name' class='form-control' type='text' name='name' required>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='description' class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                <div class='col-sm-12'>
                                    {!! $description_editor !!}
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>Συνδεδεμένη κατηγορία</label>
                                <div class="col-sm-12">
                                  <div class="radio mb-2">
                                    <label>
                                      <input type="radio" value="0" name="tenant_category" checked>
                                      Αυτόματη δημιουργία νέας κατηγορίας
                                    </label>
                                  </div>
                                  <div class="radio">
                                    <label>
                                      <input type="radio" value="1" name="tenant_category">
                                      Επιλογή υπάρχουσας κατηγορίας
                                    </label>
                                    <div class="help-block">Όλα τα μαθήματα και οι χρήστες του ενοίκου βρίσκονται στην κατηγορία αυτή και τις υποκατηγορίες της</div>
                                  </div>
                                </div>
                            </div>
                            <div class='form-group mt-4 collapse' id="category-select">
                                <label for='category' class='col-sm-12 control-label-notes'>{{ trans('langCategory') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <select class='form-select' name='category' id='category'>
                                        @foreach ($categories as $category)
                                            <option value='{{ $category->id }}'>{{ getSerializedMessage($category->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='url' class='col-sm-12 control-label-notes'>Διεύθυνση πλατφόρμας (URL)</span></label>
                                <div class='col-sm-12'>
                                    <input id='url' class='form-control' type='text' name='url' placeholder='https://eclass.example.com/'>
                                </div>
                            </div>
                            <div class='form-group mt-2'>
                                <div class='col-sm-12 mt-5 d-flex justify-content-end align-items-center gap-2'>
                                  <button class='btn btn-primary' type='submit'>{{ trans('langAdd') }}</button>
                                  <a class='btn btn-default' href='tenants.php'>{{ trans('langCancel') }}</a>
                              </div>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom_scripts')
<script>
  $(function () {
    var categories = $('#category-select');
    $('input[type=radio][name=tenant_category]').change(function() {
      if (this.value == '1') {
        categories.addClass('show');
      } else if (this.value == '0') {
        categories.removeClass('show');
      }
    });
  });
</script>
@endpush
