@extends('layouts.default')

@section('content')

    <main id="main" class="col-12 main-section">
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
                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                        <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            @foreach($appParams as $param)
                                @if ($param->name() == CobyApp::ENABLEDCOURSES)
                                    <div class='form-group mt-4' id='courses-list'>
                                        <label for='select-users' class='col-12 control-label-notes'>{{ trans('langUseOfService') }}&nbsp;&nbsp;
                                            <span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langUseOfServiceInfo') }}'></span>
                                        </label>
                                        <div class='col-12'>
                                            <select id='select-users' class='form-control' name='coby_users[]'
                                                    multiple>
                                                {!! $users_content !!}
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @foreach($appParams as $param)
                                @if ($param->getType() == ExtParam::TYPE_BOOLEAN)
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-offset-3 col-sm-9'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                    <input type='checkbox' name='{{ $param->name() }}' value='1'
                                                           @if ($param->value() == 1) checked @endif>
                                                    <span class='checkmark'></span>{{ $param->display() }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end'>
                                    <button class='btn submitAdminBtn me-2' type='submit'
                                            name='submit'>{{ trans('langSubmit') }}</button>
                                    <a href='extapp.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>
            </div>
        </div>
    </main>

    <script type="text/javascript">
        $(document).ready(function () {
            slimSelectFun(
                '#select-users',
                '{{ trans('langSearch') }}',
                '{{ trans('langWelcomeSelect') }}',
                '{{ trans('langSelectAll') }}',
                '{{ trans('langListChoices') }}'
            );
        });
    </script>
@endsection

