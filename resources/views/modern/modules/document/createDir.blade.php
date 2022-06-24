<div class='row p-2'>
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
        <div class='form-wrapper'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='form-group'>
                    <label for='newDirName' class='col-sm-6 control-label-notes'>{{ trans('langNameDir') }}:</label>
                    <div class='col-xxl-2 col-lg-3 col-md-10 col-sm-12 col-12'>
                        <input type='text' class='form-control' id='newDirName' name='newDirName'>
                    </div>
                </div>
                <div class="row p-2"></div>
                <div class='form-group'>
                    <div class='col-offset-2 col-10'>
                        <button class='btn btn-primary' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>
</div>
