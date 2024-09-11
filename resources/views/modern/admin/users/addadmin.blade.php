@push('head_scripts')
    <script type="text/javascript">
        $(function () {
            if ($('#managedepartmentradio').is(':checked')) {
                $('#departmentPicker').removeClass('hidden').show();
            } else {
                $('#departmentPicker').removeClass('hidden').hide();
            }
            $('input[name=adminrights]').change(function (e) {
                $('#departmentPicker').removeClass('hidden');
                if ($('#managedepartmentradio').is(':checked')) {
                    $('#departmentPicker').slideDown('fast');
                } else {
                    $('#departmentPicker').slideUp('fast');
                }
            });
        });
    </script>
@endpush

@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert') 

            @if($showFormAdmin)

            <div class='col-lg-6 col-12'>

                <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' role='form' method='post' name='makeadmin' action='{{ $_SERVER['SCRIPT_NAME']  }}'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }} <span class='Accent-200-cl'>(*)</span></label>
                                <div class='col-sm-12'>
                                    <input id='username' class='form-control' type='text' name='username' size='30' maxlength='30' placeholder="{{ trans('langUsername') }}..." {!! $usernameValue !!}>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAddRole') }} <span class='Accent-200-cl'>(*)</span></div>
                                    <div class='col-sm-12'>
                                        <div class='radio mb-4'>
                                            <label>
                                                <input type='radio' name='adminrights' value='admin' {{$checked['admin']}}>
                                                {{ trans('langAdministrator') }}
                                            </label>
                                             <div class='help-block'>{{ trans('langHelpAdministrator') }}</div>
                                        </div>

                                        <div class='radio mb-4'>
                                            <label>
                                                <input type='radio' name='adminrights' value='poweruser' {{$checked['poweruser']}}>
                                                {{ trans('langPowerUser') }}
                                            </label>
                                            <div class='help-block'>{{ trans('langHelpPowerUser') }}</div>
                                        </div>


                                        <div class='radio mb-4'>
                                            <label>
                                                <input type='radio' name='adminrights' value='manageuser' {{$checked['manageuser']}}>
                                                {{ trans('langManageUser') }}
                                            </label>
                                            <div class='help-block'>{{ trans('langHelpManageUser') }}</div>
                                        </div>


                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='adminrights' value='managedepartment' id='managedepartmentradio' {{$checked['managedepartment']}}>
                                                {{ trans('langManageDepartment') }}
                                            </label>
                                            <div class='help-block'>{{ trans('langHelpManageDepartment') }}</div>
                                        </div>

                                    </div>
                                </label>
                            </div>

                            <div class='form-group hidden' id='departmentPicker'>
                                <div class='col-sm-12 mt-2'>
                                    {!! $pickerHtml !!}
                                </div>
                            </div>

                            {!! showSecondFactorChallenge() !!}

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                                </div>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
            @endif

            <div class='col-12 mt-3'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        <thead><tr class='list-header'>
                            <th class='count-col'>ID</th>
                            <th>{{ trans('langSurnameName') }}</th>
                            <th>{{ trans('langUsername') }}</th>
                            <th>{{ trans('langRole') }}</th>
                            <th aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-gears') !!}</th>
                        </tr></thead>

                        @foreach ($admins as $admin)
                            <tr>
                                <td class='count-col'>{{ $admin->id }}</td>
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
                                    {!! $message[$admin->user_id] !!}
                                @endif
                                </td>
                                <td class='text-end'>
                                @if ($admin->user_id != 1)
                                    {!! action_button([
                                            [   'title' => trans('langEditPrivilege'),
                                                'url' => "{$urlAppend}modules/admin/addadmin.php?edit=". getIndirectReference($admin->user_id),
                                                'icon' => 'fa-edit'
                                            ],
                                            [   'title' => trans('langEditUser'),
                                                'url' => "{$urlAppend}modules/admin/edituser.php?u={$admin->user_id}",
                                                'icon' => 'fa-edit'
                                            ],
                                            [
                                                'title' => trans('langDelete'),
                                                'url' => "$_SERVER[SCRIPT_NAME]?delete=" . getIndirectReference($admin->user_id),
                                                'class' => 'delete',
                                                'icon' => 'fa-xmark'
                                            ]
                                        ]) !!}
                                @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
