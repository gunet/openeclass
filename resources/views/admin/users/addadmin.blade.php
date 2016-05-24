@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' name='makeadmin' action='{{ $_SERVER['SCRIPT_NAME']  }}'>
        <fieldset>
                <div class='form-group'>
                    <label for='username' class='col-sm-2 control-label'>{{ trans('langUsername') }}</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='username' size='30' maxlength='30' placeholder='{{ trans('langUsername') }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>{{ trans('langAddRole') }}</label>
                        <div class='col-sm-10'>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='admin' checked>{{ trans('langAdministrator') }}
                                <span class='help-block'>
                                    <small>{{ trans('langHelpAdministrator') }}</small>
                                </span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='poweruser'>{{ trans('langPowerUser') }}
                                <span class='help-block'>
                                    <small>{{ trans('langHelpPowerUser') }}&nbsp;</small>
                                </span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='manageuser'>{{ trans('langManageUser') }}
                                <span class='help-block'>
                                    <small>{{ trans('langHelpManageUser') }}</small>
                                </span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='managedepartment'>{{ trans('langManageDepartment') }}
                                <span class='help-block'>
                                    <small>{{ trans('langHelpManageDepartment') }}</small>
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                    </div>
                </div>       
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>    
    <table class='table-default'>
        <tr>
            <th class='center'>ID</th>
            <th>{{ trans('langSurnameName') }}</th>
            <th>{{ trans('langUsername') }}</th>
            <th class='text-center'>{{ trans('langRole') }}</th>
            <th class='text-center'>{!! icon('fa-gears') !!}</th>
        </tr>
        @foreach ($admins as $admin)
            <tr>
                <td>{{ $admin->id }}</td>
                <td>{{ $admin->givenname }} {{ $admin->surname }}</td>
                <td>{{ $admin->username }}</td>
                <td>
                @if ($admin->privilege == 0)
                    {{ trans('langAdministrator') }}
                @elseif ($admin->privilege == 1)
                    {{ trans('langPowerUser') }}
                @elseif ($admin->privilege == 2)
                    {{ trans('langManageUser') }}
                @elseif ($admin->privilege == 3)
                    {{ trans('langManageDepartment') }}
                @endif
                </td>
                <td class='text-center'>
                @if ($admin->id != 1)
                    {!! action_button([
                            [
                                'title' => trans('langDelete'),
                                'url' => "$_SERVER[SCRIPT_NAME]?delete=1&amp;aid=" . getIndirectReference($admin->id),
                                'class' => 'delete',
                                'icon' => 'fa-times'
                            ]
                        ]) !!}
                @endif
                </td>
            </tr>
        @endforeach
    </table>
@endsection