<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='form-group'>
                    <label for='newDirName' class='col-sm-2 control-label'>{{ trans('langNameDir') }}:</label>
                    <div class='col-xs-10'>
                        <input type='text' class='form-control' id='newDirName' name='newDirName'>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-xs-offset-2 col-xs-10'>
                        <button class='btn btn-primary' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>
</div>
