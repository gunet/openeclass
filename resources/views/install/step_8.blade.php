
@if ($config_error)
    <div class='alert alert-danger'>
        {{ trans('langErrorConfig') }}
    </div>
@else
    <div class='alert alert-success'>
        <i class='fa-solid fa-circle-check fa-lg'></i>
        <span>
            {{ trans('langInstallSuccess') }}
        </span>
    </div>
    <br>

    <form action='../'>
        <input class='btn btn-sm btn-primary submitAdminBtn w-100 text-white' type='submit' value='{{ trans('langEnterFirstTime') }}'>
    </form>

    <div class="help-block pt-2">
        {{ trans('langProtect') }}
    </div>
@endif
