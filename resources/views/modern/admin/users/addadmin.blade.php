@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    

                    @if($showFormAdmin)
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>

                        <div class='form-wrapper form-edit rounded'>
                        
                            <form class='form-horizontal' role='form' method='post' name='makeadmin' action='{{ $_SERVER['SCRIPT_NAME']  }}'>
                                <fieldset>
                                    <div class='form-group'>
                                        <label for='username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' type='text' name='username' size='30' maxlength='30' placeholder="{{ trans('langUsername') }}..." {!! $usernameValue !!}>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langAddRole') }}</label>
                                            <div class='col-sm-12'>
                                                <div class='radio mb-4'>
                                                    <label>
                                                        <input type='radio' name='adminrights' value='admin' {{$checked['admin']}}> 
                                                        {{ trans('langAdministrator') }}
                                                    </label>
                                                     <div class='help-block ps-4 ms-3'>{{ trans('langHelpAdministrator') }}</div>
                                                </div>

                                                <div class='radio mb-4'>
                                                    <label>
                                                        <input type='radio' name='adminrights' value='poweruser' {{$checked['poweruser']}}> 
                                                        {{ trans('langPowerUser') }} 
                                                    </label>  
                                                    <div class='help-block ps-4 ms-3'>{{ trans('langHelpPowerUser') }}</div>
                                                </div>

                                               
                                                <div class='radio mb-4'>
                                                    <label>
                                                        <input type='radio' name='adminrights' value='manageuser' {{$checked['manageuser']}}> 
                                                        {{ trans('langManageUser') }}    
                                                    </label>     
                                                    <div class='help-block ps-4 ms-3'>{{ trans('langHelpManageUser') }}</div>
                                                </div>
                                               
                                              
                                                <div class='radio'>
                                                    <label>
                                                        <input type='radio' name='adminrights' value='managedepartment' id='managedepartmentradio' {{$checked['managedepartment']}}> 
                                                        {{ trans('langManageDepartment') }}
                                                    </label>
                                                    <div class='help-block ps-4 ms-3'>{{ trans('langHelpManageDepartment') }}</div>
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
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                                        </div>
                                    </div>       
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div> 
                    @endif 

                    <div class='col-12 mt-3'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <tr class='list-header'>
                                    <th class='center'>ID</th>
                                    <th>{{ trans('langSurnameName') }}</th>
                                    <th>{{ trans('langUsername') }}</th>
                                    <th>{{ trans('langRole') }}</th>
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
                                            {!! $message !!}
                                        @endif
                                        </td>
                                        <td class='text-center'>
                                        @if ($admin->user_id != 1)
                                            {!! action_button([
                                                    [   'title' => trans('langEditPrivilege'),
                                                        'url' => "{$urlAppend}modules/admin/addadmin.php?edit=$indirect". getIndirectReference($admin->user_id),
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
                                                        'icon' => 'fa-times'
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
    
</div>
<script>
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
@endsection