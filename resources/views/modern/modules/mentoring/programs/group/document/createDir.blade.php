
    <div class='col-12'>
        <div class='form-wrapper form-edit mb-3 rounded-2 p-3 solidPanel'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='col-md-6 col-12 form-group p-3'>
                    <label for='newDirName' class='col-sm-12 control-label-notes'>{{ trans('langNameDir') }}</label>
                    <div class='col-12'>
                        <input type='text' class='form-control' placeholder="{{ trans('langNameDir') }}..." id='newDirName' name='newDirName'>
                    </div>
                </div>

                <div class='form-group mt-0 p-3'>
                    <div class='col-12 d-flex justify-content-start align-items-center'>
                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>

