@extends('layouts.default')

@push('head_scripts')
    <script src="{{ $urlServer }}/js/sortable/Sortable.min.js"></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            Sortable.create(orderTexts, {
                handle: '.fa-arrows',
                animation: 150,
                onEnd: function (evt) {

                    var itemEl = $(evt.item);

                    var idReorder = itemEl.attr('data-id');
                    var prevIdReorder = itemEl.prev().attr('data-id');

                    $.ajax({
                        type: 'post',
                        dataType: 'text',
                        data: {
                            toReorder: idReorder,
                            prevReorder: prevIdReorder,
                        },
                        success: function(data) {
                            $('.indexing').each(function (i){
                                $(this).html(i+1);
                            });
                        }
                    })
                }

            });
        });
    </script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                <div class='form-group'>
                                    <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langCourses') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='question' class='form-control' type='number' name='total_courses' value="{{ get_config('total_courses') }}"/>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='answer' class='col-sm-12 control-label-notes'>{{trans('langUserLogins')}}/{{trans('langWeek')}}</label>
                                    <div class='col-sm-12'>
                                        <input id='answer' class='form-control' type='number' name='visits_per_week' value="{{ get_config('visits_per_week') }}"/>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='users_registered' class='col-sm-12 control-label-notes'>{{ trans('langRegisteredUsers') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='users_registered' class='form-control' type='text' name='users_registered' value="{!! !empty(get_config('users_registered')) ? get_config('users_registered') : 0 !!}"/>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='defaultHomepageTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='homepage_title' id='defaultHomepageTitle' value="{!! q(get_config('homepage_title', trans('langEclass'))) !!}">
                                        <p class='help-block mt-1'>{{trans('langHomePageTitleHelpText')}}</p>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='defaultHomepageTestimonialTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}}&nbsp(Testimonials)</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='homepage_testimonial_title' id='defaultHomepageTestimonialTitle' value="{!! q(get_config('homepage_testimonial_title', trans('langSaidForUs'))) !!}">
                                        <p class='help-block mt-1'>{{trans('langHomePageTitleHelpText')}}</p>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='defaultHomepageBcrmp' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroBcrmp')}}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='homepage_name' id='defaultHomepageBcrmp' value="{!! q(get_config('homepage_name', trans('langHomePage'))) !!}">
                                        <p class='help-block mt-1'>{{trans('langHomePageNavTitleHelp')}}</p>
                                    </div>
                                </div>


                                <div class='form-group mt-4'>
                                    <label for='homepage_intro' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroText')}}</label>
                                    <div class='col-sm-12'>
                                        {!! $homepage_intro !!}
                                        <p class='help-block mt-1'>{{trans('langHomePageIntroTextHelp')}}</p>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='link_banner' class='col-sm-12 control-label-notes'>{{ trans('langLinkBanner') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='url' name='link_banner' id='link_banner' value="{!! get_config('banner_link') !!}">
                                    </div>
                                </div>


                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-1'>{{trans('lang_login_form')}}: </div>
                                    <div class='col-sm-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                    <input id='showOnlyLoginScreen' type='checkbox' name='show_only_loginScreen' {!! get_config('show_only_loginScreen') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langShowOnlyLoginScreen') }}
                                                </label>
                                            </div>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                    <input id='hide_login_check' type='checkbox' name='dont_display_login_form' {!! get_config('dont_display_login_form') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{trans('lang_dont_display_login_form')}}
                                                </label>
                                            </div>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                    <input id='hide_login_link_check' type='checkbox' name='hide_login_link' {!! get_config('hide_login_link') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{trans('lang_hide_login_link')}}
                                                </label>
                                            </div>
                                    </div>
                                </div>
                                
                                
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                        <button type="submit" class="btn submitAdminBtn" name="submit">{{ trans('langSave') }}</button>
                                    </div>
                                </div>

                            </form>
                            
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>



                    
                    @if($priorities)
                        <div class='col-12 mt-5'>
                            <div id='orderTexts'>
                                @foreach($priorities as $p)
                                    @php $urlEdit = ''; @endphp
                                    <div class='card panelCard px-lg-4 py-lg-3 p-3 mb-4' data-id='{{ $p->id }}'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center p-0 gap-3 flex-wrap'>
                                            <h3 class='mb-0'>
                                                @if($p->title == 'announcements')
                                                    {{ trans('langAnnouncements')}}
                                                    @php $urlEdit = $urlServer . 'modules/admin/adminannouncements.php'; @endphp
                                                @elseif($p->title == 'popular_courses')
                                                    {{ trans('langPopularCourse')}}
                                                    @php $urlEdit = $urlServer . 'modules/admin/listcours.php'; @endphp
                                                @elseif($p->title == 'texts')
                                                    {{ trans('langHomepageTexts')}}
                                                    @php $urlEdit = $urlServer . 'modules/admin/homepageTexts_create.php'; @endphp
                                                @elseif($p->title == 'testimonials')

                                                    @if(get_config('homepage_testimonial_title'))
                                                        {!! get_config('homepage_testimonial_title') !!}
                                                    @else
                                                        {{ trans('langSaidForUs') }}
                                                    @endif

                                                    @php $urlEdit = $urlServer . 'modules/admin/homepageTexts_create.php'; @endphp
                                                 @elseif($p->title == 'statistics')
                                                    {{ trans('langViewStatics')}}
                                                    @php $urlEdit = $urlServer . 'modules/admin/manage_home.php'; @endphp
                                                @else
                                                    {{ trans('langOpenCourses')}}
                                                    @php $urlEdit = $urlServer . 'modules/admin/eclassconf.php'; @endphp
                                                @endif
                                            </h3>
                                                
                                            @php ($p->visible==1 ? $vis=0 : $vis=1); @endphp

                                            <div class='d-flex gap-3'>
                                                <a data-bs-toggle='tooltip' data-bs-placement='top' title="@if($vis==1) {{ trans('lang_visible_in_homepage') }} @else {{ trans('lang_invisible_in_homepage') }} @endif" 
                                                    href="{{ $_SERVER['SCRIPT_NAME'] }}?edit_priority=1&amp;edit={{ $p->id }}&amp;val={{ $vis }}&amp;titleEdit={{ $p->title }}" aria-label="@if($vis==1) {{ trans('lang_visible_in_homepage') }} @else {{ trans('lang_invisible_in_homepage') }} @endif">
                                                    <span class='fa-solid @if($vis==1) fa-eye-slash @else fa-eye @endif fa-lg'></span>
                                                </a>
                                                <a data-bs-toggle='tooltip' data-bs-placement='top' title="{{ trans('langReorder') }}" 
                                                    href='javascript:void(0);' aria-label="{{ trans('langReorder') }}">
                                                    <span class='fa fa-arrows'></span>
                                                </a>
                                                <a data-bs-toggle='tooltip' data-bs-placement='top' title="{{ trans('langEditChange') }}" 
                                                    href='{{ $urlEdit }}' aria-label="{{ trans('langEditChange') }}">
                                                    <span class='fa-solid fa-edit fa-lg'></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    
                    @else
                    <div id='orderTexts'></div>      
                    @endif

                
        </div>
    </div>
  
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#showOnlyLoginScreen').on('click',function(){
            if($('#showOnlyLoginScreen').is(":checked")){
                document.getElementById('showOnlyLoginScreen').value = 1;
            }else{
                document.getElementById('showOnlyLoginScreen').value = 0;
            }
        });

        $('#hide_login_check').on('click',function(){
            if($('#hide_login_check').is(":checked")){
                document.getElementById('hide_login_check').value = 1;
            }else{
                document.getElementById('hide_login_check').value = 0;
            }
        });

        $('#hide_login_link_check').on('click',function(){
            if($('#hide_login_link_check').is(":checked")){
                document.getElementById('hide_login_link_check').value = 1;
            }else{
                document.getElementById('hide_login_link_check').value = 0;
            }
        });

    });
</script>

@endsection
