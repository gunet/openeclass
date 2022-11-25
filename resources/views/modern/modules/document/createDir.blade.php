
    <div class='col-12'>
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
                    @if($menuTypeID == 3 or $menuTypeID == 1)
                    <div class='col-12 d-flex justify-content-center align-items-center'>
                       
                         
                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langCreate') }}</button>
                         
                           
                                <a class='btn btn-outline-secondary cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                           
                       
                    </div>
                    @else
                    <div class='col-offset-2 col-10 d-flex justify-content-start align-items-center'>
                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn btn-outline-secondary cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                    @endif
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>

