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
                                    <label for='langDropdown' class='col-sm-12 control-label-notes'>{{trans('langSelectedLang')}}</label>
                                    <select class="form-select" name="langDropdown" id="langDropdown">
                                        {!! implode(' ', $sel) !!}
                                    </select>
                                </div>

                                <input id="langswitch" type="hidden" value="{{$_SESSION['langswitch']}}">

                                <style>
                                    .flash-border {
                                        animation: flash 400ms;
                                    }

                                    @keyframes flash {
                                        0% { border-color: #EFF2FB; }
                                        50% { border-color: #0072FF; }
                                        100% { border-color: #EFF2FB; }
                                    }
                                </style>

                                @foreach ($selectable_langs as $langCode => $langName)
                                    <div class="d-none mt-4 border-card p-3 rounded-2" data-lang="{{$langCode}}">

                                        <div class='form-group'>
                                            <label for='defaultHomepageTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}} - {{$langName}}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='homepage_title_{{$langCode}}' id='defaultHomepageTitle' value="{!! q(get_config('homepage_title_'.$langCode, trans('langEclass'))) !!}">
                                                <p class='help-block mt-1'>{{trans('langHomePageTitleHelpText')}}</p>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='defaultHomepageTestimonialTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}}&nbsp(Testimonials) - {{$langName}}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='homepage_testimonial_title_{{$langCode}}' id='defaultHomepageTestimonialTitle' value="{!! q(get_config('homepage_testimonial_title_'.$langCode, trans('langSaidForUs'))) !!}">
                                                <p class='help-block mt-1'>{{trans('langHomePageTitleHelpText')}}</p>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='defaultHomepageBcrmp' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroBcrmp')}} - {{$langName}}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='homepage_name_{{$langCode}}' id='defaultHomepageBcrmp' value="{!! q(get_config('homepage_name_'.$langCode, trans('langHomePage'))) !!}">
                                                <p class='help-block mt-1'>{{trans('langHomePageNavTitleHelp')}}</p>
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <label for='homepage_intro' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroText')}} - {{$langName}}</label>
                                            <div class='col-sm-12'>
                                                {!! rich_text_editor('homepage_intro_'.$langCode, 5, 20, get_config('homepage_intro_'.$langCode)) !!}
                                                <p class='help-block mt-1'>{{trans('langHomePageIntroTextHelp')}}</p>
                                            </div>
                                        </div>

                                    </div>
                                @endforeach

                                <div class='form-group mt-4'>
                                    <label for='link_banner' class='col-sm-12 control-label-notes'>{{ trans('langLinkBanner') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='url' name='link_banner' id='link_banner' value="{!! get_config('banner_link') !!}">
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>
                                        {{ trans('lang_login_form') }}:
                                    </div>
                                    <div class='col-sm-12'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='display_login_form' value='0' {!! $selected_dont_display_login_form !!}>
                                                {{ trans('lang_dont_display_login_form') }}
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='display_login_form' value='1' {!! $selected_display_only_login_form !!}>
                                                {{ trans('langShowOnlyLoginScreen') }}
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='display_login_form' value='2' {!! $selected_display_login_form_and_image !!}>
                                                {{ trans('lang_display_login_form_and_image') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-1'>
                                        {{ trans('langOtherOptions') }}:
                                    </div>
                                    <div class='col-sm-12'>

                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                <input type='checkbox' name='dont_display_login_link' {{ $cbox_dont_display_login_link }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_login_link') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='dont_display_courses_menu' value='1' {{ $cbox_dont_display_courses_menu }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_courses_menu') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='dont_display_contact_menu' value='1' {{ $cbox_dont_display_contact_menu }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_contact_menu') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='dont_display_about_menu' value='1' {{ $cbox_dont_display_about_menu }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_about_menu') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='dont_display_manual_menu' value='1' {{ $cbox_dont_display_manual_menu }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_manual_menu') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input  type='checkbox' name='dont_display_faq_menu' value='1' {{ $cbox_dont_display_faq_menu }}>
                                                <span class='checkmark'></span>
                                                {{ trans('lang_dont_display_faq_menu') }}
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
                                    <div class='card panelCard card-default px-lg-4 py-lg-3 p-3 mb-4' data-id='{{ $p->id }}'>
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

        let initialLang = $('#langswitch').val();
        $('#langDropdown').val(initialLang);
        $('[data-lang]').each(function() {
            if ($(this).data('lang') === initialLang) {
                $(this).removeClass('d-none');
            }
        });

        $('#langDropdown').on('change', function() {
            var selectedLang = $(this).val();
            $('[data-lang]').each(function() {
                if ($(this).data('lang') === selectedLang) {
                    $(this).removeClass('d-none').addClass('flash-border');
                    setTimeout(() => {
                        $(this).removeClass('flash-border');
                    }, 400); // Duration of the flash animation
                } else {
                    $(this).addClass('d-none');
                }
            });
        });

    });
</script>

@endsection
