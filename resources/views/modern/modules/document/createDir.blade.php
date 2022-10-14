
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <div class='form-wrapper form-edit p-3 mt-2 mb-3 rounded'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='form-group'>
                    <label for='newDirName' class='col-sm-12 control-label-notes'>{{ trans('langNameDir') }}</label>
                    <div class='col-12'>
                        <input type='text' class='form-control' placeholder="{{ trans('langNameDir') }}..." id='newDirName' name='newDirName'>
                    </div>
                </div>

                <div class='form-group mt-3'>
                    @if($menuTypeID == 3 or $menuTypeID == 1)
                    <div class='col-12'>
                        <div class='row'>
                            <div class='col-6'>
                                <button class='btn btn-primary btn-sm submitAdminBtn w-100' type='submit'>{{ trans('langCreate') }}</button>
                            </div>
                            <div class='col-6'>
                                <a class='btn btn-secondary btn-sm cancelAdminBtn w-100' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class='col-offset-2 col-10'>
                        <button class='btn btn-primary btn-sm' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn btn-secondary btn-sm' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                    @endif
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>

