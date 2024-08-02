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
                
                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit rounded'>
                         <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                             <div class='form-group mt-4'>
                                 <h3>{{ trans('langBBBLBMethod') }}</h3>
                                 <div class='col-sm-12'>
                                     <div class='radio mb-2'>
                                         <label>
                                            <input type='radio' name='bbb_lb_algo' value='wo' {{ $bbb_lb_wo_checked }}>{{ trans('langBBBLBMethodWO') }}
                                         </label>
                                        <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWOInfo') }}'></span>
                                     </div>
                                     <div class='radio mb-2'>
                                        <label>
                                             <input type='radio' name='bbb_lb_algo' value='wll' {{ $bbb_lb_wll_checked }}> {{ trans('langBBBLBMethodWLL') }}
                                        </label>
                                         <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWLLInfo') }}'></span>
                                     </div>
                                     <div class='radio mb-2'>
                                         <label>
                                             <input type='radio' name='bbb_lb_algo' value='wlr' {{ $bbb_lb_wlr_checked }}> {{ trans('langBBBLBMethodWLR') }}
                                         </label>
                                         <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWLRInfo') }}'></span>
                                     </div>
                                     <div class='radio mb-2'>
                                         <label>
                                             <input type='radio' name='bbb_lb_algo' value='wlc' {{ $bbb_lb_wlc_checked }}> {{ trans('langBBBLBMethodWLC') }}
                                         </label>
                                         <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWLCInfo') }}'></span>
                                     </div>
                                     <div class='radio mb-2'>
                                         <label>
                                             <input type='radio' name='bbb_lb_algo' value='wlm' {{ $bbb_lb_wlm_checked }}> {{ trans('langBBBLBMethodWLM') }}
                                         </label>
                                         <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWLMInfo') }}'></span>
                                     </div>
                                     <div class='radio mb-2'>
                                         <label>
                                             <input type='radio' name='bbb_lb_algo' value='wlv' {{ $bbb_lb_wlv_checked }}> {{ trans('langBBBLBMethodWLV') }}
                                         </label>
                                         <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBMethodWLVInfo') }}'></span>
                                     </div>
                                 </div>
                             </div>

                             <div class='form-group mt-4'>
                                 <label class='col-12 control-label-notes mb-2'>{{ trans('langBBBLBWeights') }}</label>
                                 <div class='form-group mt-4'>
                                     <label class='col-12 control-label-notes'>{{ trans('langBBBLBWeightParticipant') }}
                                        <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBWeightParticipantInfo') }}'></span>
                                     </label>
                                         <div class='col-12'>
                                             <input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_part' name='bbb_lb_weight_part' value='{{ $bbb_lb_weight_part }}'>
                                        </div>
                                 </div>
                                 <div class='form-group mt-4'>
                                     <label class='col-12 control-label-notes'>{{ trans('langBBBLBWeightMic') }}
                                        <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBWeightMicInfo') }}'></span>
                                     </label>
                                    <div class='col-12'>
                                        <input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_mic' name='bbb_lb_weight_mic' value='{{ $bbb_lb_weight_mic }}'>
                                    </div>
                                 </div>
                                 <div class='form-group mt-4'>
                                     <label class='col-12 control-label-notes'>{{ trans('langBBBLBWeightCamera') }}
                                        <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBWeightCameraInfo') }}'></span>
                                     </label>
                                     <div class='col-12'>
                                         <input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_camera' name='bbb_lb_weight_camera' value='{{ $bbb_lb_weight_camera }}'>
                                     </div>
                                 </div>
                                 <div class='form-group mt-4'>
                                     <label class='col-12 control-label-notes'>{{ trans('langBBBLBWeightRoom') }}
                                        <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBLBWeightRoomInfo') }}'></span>
                                     </label>
                                     <div class='col-12'>
                                         <input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_room' name='bbb_lb_weight_room' value='{{ $bbb_lb_weight_room }}'>
                                     </div>
                                 </div>
                             </div>

                             <div class='form-group mt-4'>
                                 <h3>{{ trans('langBBBDefaultNewRoom') }}</h3>
                             </div>

                             <div class='checkbox'>
                                 <label class='label-container'>
                                     <input type='checkbox' name='bbb_recording' $checked_recording value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBRecord') }}
                                 </label>
                             </div>

                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_muteOnStart' $checked_muteOnStart value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBmuteOnStart') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                     <input type='checkbox' name='bbb_DisableMic' $checked_DisabledMic value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsDisableMic') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_DisableCam' $checked_DisabledCam value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsDisableCam') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_webcamsOnlyForModerator' $checked_webcamsOnlyForModerator value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBwebcamsOnlyForModerator') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_DisablePrivateChat' $checked_DisablePrivateChat value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsDisablePrivateChat') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_DisablePublicChat' $checked_DisablePublicChat value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsDisablePublicChat') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                     <input type='checkbox' name='bbb_DisableNote' $checked_DisableNote value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsDisableNote') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_HideUserList' $checked_HideUserList value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBlockSettingsHideUserList') }}
                                 </label>
                             </div>
                             <div class='checkbox'>
                                 <label class='label-container'>
                                    <input type='checkbox' name='bbb_hideParticipants' $checked_hideParticipants value='1'>
                                     <span class='checkmark'></span>{{ trans('langBBBHideParticipants') }}
                                 </label>
                             </div>

                             <div class='form-group mt-4'>
                                 <h3>{{ trans('langOtherOptions') }}</h3>
                             </div>

                             <div class='form-group mt-4'>
                                 <label class='col-12 control-label-notes'>{{ trans('langBBBMaxDuration') }}
                                     <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langInMinutes') }}'></span>
                                 </label>
                                 <div class='col-12'>
                                     <input class='form-control' type='number' min='1' max='10000' step='10' pattern='\d+' id='bbb_max_duration' name='bbb_max_duration' value='{{ $bbb_max_duration }}'>
                                 </div>
                             </div>

                             <div class='form-group mt-4'>
                                 <label class='col-12 control-label-notes'>{{ trans('langBBBMaxPartPerRoom') }}
                                     <span class='fa fa-info-circle p-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langBBBMaxPartPerRoomInfo') }}'></span>
                                 </label>
                                 <div class='col-12'>
                                     <input class='form-control' type='number' min='1' max='1000' step='10' pattern='\d+' id='bbb_max_part_per_room' name='bbb_max_part_per_room' value='{{ $bbb_max_part_per_room }}'>
                                 </div>
                             </div>

                             <div class='form-group mt-4'>
                                <div class='col-sm-offset-3'>
                                    <input class='btn btn-primary' type='submit' name='submit_config' value='{{ trans('langSubmit') }}'>
                                </div>
                            </div>
                         </form>
                    </div>
                </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>
            </div>
        </div>
    </div>
@endsection
