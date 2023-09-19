
<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
        <div class='form-wrapper form-edit mt-2 mb-3 rounded'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='form-group'>
                    <label for='newDirName' class='col-sm-12 control-label-notes'>{{ trans('langNameDir') }}</label>
                    <div class='col-12'>
                        <input type='text' class='form-control' placeholder="{{ trans('langNameDir') }}..." id='newDirName' name='newDirName'>
                    </div>
                </div>

                <div class='form-group mt-4'>
                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                        <button class='btn submitAdminBtn' type='submit'>
                            {{ trans('langCreate') }}
                        </button>
                        <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>
    <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='{{ $urlAppend }}template/modern/img/form-image.png' alt='form-image'>
    </div>
</div>

