  <div class='form-group mt-4'>
    <label class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}:</label>
    <div class='col-sm-12'>
      <input class='form-control' type='text' name='username' value='{{ $invitation->email }}' disabled>
      <span class='help-text'>({{ trans('langSameAsYourEmail') }})</span>
    </div>
  </div>
  <div class='form-group pw-group mt-4'>
    <label for='password_field' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}:</label >
    <div class='col-sm-12' >
      <input class='form-control' type='password' name='password1' maxlength='30' autocomplete='off' id='password_field' placeholder='{{ trans('langUserNotice') }}' required>
      <span id='result'></span>
    </div>
  </div >
  <div class='form-group pw-group mt-4'>
    <label for='password_field_2' class='col-sm-12 control-label-notes'>{{ trans('langConfirmation') }}:</label >
    <div class='col-sm-12' >
      <input id='password_field_2' class='form-control' type='password' name='password' maxlength='30' autocomplete='off' required>
    </div >
  </div>
  <div class='form-group mt-4'>
    <label for='name_field' class='col-sm-12 control-label-notes'>{{ trans('langName') }}:</label>
    <div class='col-sm-12'>
      <input id='name_field' class='form-control' type='text' name='givenname_form' maxlength='100' value='{{ $invitation->givenname }}' placeholder='{{ trans('langName') }}' required>
    </div>
  </div>
  <div class='form-group mt-4'>
    <label for='surname_field' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}:</label>
    <div class='col-sm-12'>
      <input id='surname_field' class='form-control' type='text' name='surname_form' maxlength='100' value = '{{ $invitation->surname }}' placeholder='{{ trans('langSurname') }}' required>
    </div>
  </div>
  <div class='form-group mt-5'>
    <div class='col-sm-12 text-center'>
      <button type='submit' name='no_cas' class='btn btn-primary'>{{ trans('langRegisterAsVisitor') }}</button>
    </div>
  </div>
