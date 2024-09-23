@extends('layouts.default')

@section('content')

  <div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
      <div class='row m-auto'>
        <div class='row m-auto'>

          <div class='col-12'>
            <h2>{{ $toolName }}</h2>
          </div>

          <div class='card panelCard card-default px-lg-4 py-lg-3 mt-3'>
            <div class='card-body'>
              <form class='register_form form-horizontal' method='post' action='invite.php?id={{ $invitation->identifier }}'>
                @if (!$cas)
                  {!! generate_csrf_token_form_field() !!}
                @endif
                <div class='form-group'>
                  <div class='col-sm-12 control-label-notes'>{{ trans('langCourse') }}:</div>
                  <div class='col-sm-12'>
                    <p class='form-control-static'>{{ $course->title }}</p>
                  </div>
                </div>
                <div class='form-group mt-3'>
                  <div class='col-sm-12 control-label-notes'>{{ trans('langTeacher') }}:</div>
                  <div class='col-sm-12'>
                    <p class='form-control-static'>{{ $course->prof_names }}</p>
                  </div>
                </div>
                <div class='form-group mt-3'>
                  <div class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</div>
                  <div class='col-sm-12'>
                    <p class='form-control-static'>{!! $departments !!}</p>
                  </div>
                </div>
                <div class='form-group mt-3'>
                  <div class='col-sm-12 control-label-notes'>{{ trans('langCode') }}:</div>
                  <div class='col-sm-12'>
                    <p class='form-control-static'>{{  $course->public_code }}</p>
                  </div>
                </div>
                <div class='form-group mt-3'>
                  <div class='col-sm-8 col-sm-offset-2'>
                    <p class='form-control-static'>
                    {{ trans('langCourseInvitationReceived') }}
                    {!! $message !!}
                    </p>
                  </div>
                </div>

                @if ($cas)
                  <div class='form-group mt-3'>
                    <div class='col-sm-12 text-center'>
                      <button type='submit' name='submit' class='btn btn-primary'>{{ trans('langLoginAndRegister') }}</button>
                    </div>
                  </div>
                @else
                  @include('main.invitation.eclass_register')
                @endif

              </form>
            </div>
          </div>

          @if ($cas)
            <div class='card panelCard card-default px-lg-4 py-lg-3 my-4'>
              <div class='card-body'>
                <form class='register_form form-horizontal' method='post' action='invite.php?id={{ $invitation->identifier }}'>
                  {!! generate_csrf_token_form_field() !!}
                  <div class='form-group'>
                    <div class='col-sm-8 col-sm-offset-2'>
                      <p class='form-control-static'>
                        {!! trans('langInviteEclassLoginAlt') !!}
                      </p>
                    </div>
                  </div>
                  @include('main.invitation.eclass_register')
                </form>
              </div>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
     var lang = @json($pwMessages),
         form_error = false;

      $(document).ready(function() {
          $('#password_field, #password_field_2').keyup(function() {
              var pass = $('#password_field').val(),
                  pass2 = $('#password_field_2').val();
              if (pass && pass2 && pass != pass2) {
                  $('.pw-group').addClass('has-error');
                  form_error = true;
                  $('#result').html('<span id="result" class="label label-error">{{ trans('langPassTwice') }}</span>');
              } else {
                  $('.pw-group').removeClass('has-error');
                  form_error = false;
                  $('#result').html(checkStrength(pass));
              }
          });
          $('.register_form').on('submit',function(e) {
              if (form_error) {
                  e.preventDefault();
              }
          });
      });
  </script>

@endsection
