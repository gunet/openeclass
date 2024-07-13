@extends('layouts.default')


@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')

                    {!! isset($action_bar) ?  $action_bar : '' !!}<div class="row p-2"></div>

                    @include('layouts.partials.show_alert') 

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}'>
                                <fieldset>                 
                                             
                                    <div class='form-group'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langSocialBookmarksFunct') }}</label>
                                        <div class='col-sm-12'> 
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='settings_radio'{{ $social_enabled ? " checked" : "" }}> {{ trans('langActivate') }}
                                                </label>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='settings_radio' {{ $social_enabled ? "" : " checked" }}> {{ trans('langDeactivate') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                            
                                    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-start align-items-center gap-2'>
                                            <input type='submit' class='btn submitAdminBtn' name='submitSettings' value='{{ trans('langSubmit') }}' />
                                            <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                        </div>
                </div>
            </div>

        </div>
    
</div>
</div>
@endsection