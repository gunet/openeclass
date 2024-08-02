<div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4 dialog_box'>
    <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
        <div class='form-wrapper form-edit p-0 mt-2 mb-3'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>
                {!! $group_hidden_input !!}
                <input type='hidden' name='newDirPath' value='{{ $curDirPath }}'>
                <div class='form-group'>
                    <label for='newDirName' class='col-sm-12 control-label-notes'>{{ trans('langNameDir') }}</label>
                    <div class='col-12'>
                        <input type='text' class='form-control' placeholder="{{ trans('langName') }} ..." id='newDirName' name='newDirName'>
                    </div>
                </div>

                <div class='form-group mt-4'>
                    <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
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
    <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
    </div>
</div>

