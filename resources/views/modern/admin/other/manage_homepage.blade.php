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

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                <div class='form-group'>
                                    <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langCourses') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='number' name='total_courses' value="{{ get_config('total_courses') }}"/>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='answer' class='col-sm-12 control-label-notes'>{{trans('langUserLogins')}}/{{trans('langWeek')}}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='number' name='visits_per_week' value="{{ get_config('visits_per_week') }}"/>
                                    </div>
                                </div>

                                {{--<div class='form-group mt-4'>
                                    <div class='col-sm-12 checkbox'>
                                        <label class='label-container'>
                                            <input id='showOnlyLoginScreen' type='checkbox' name='show_only_loginScreen' {!! get_config('show_only_loginScreen') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langShowOnlyLoginScreen') }}
                                        </label>
                                        
                                    </div>
                                </div>--}}









                                <div class='form-group mt-4'>
                                    <label for='defaultHomepageTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='homepage_title' id='defaultHomepageTitle' value="{!! q(get_config('homepage_title', trans('langEclass'))) !!}">
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
                                    <label for='defaultHomepageIntro' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroText')}}:</label>
                                    <div class='col-sm-12'>
                                        {!! $homepage_intro !!}
                                        <p class='help-block mt-1'>{{trans('langHomePageIntroTextHelp')}}</p>
                                    </div>
                                </div>


                                <div class='form-group mt-4'>
                                    <label for='theme' class='col-sm-12 control-label-notes mb-1'>{{trans('lang_login_form')}}: </label>
                                    <div class='col-sm-12'>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input id='showOnlyLoginScreen' type='checkbox' name='show_only_loginScreen' {!! get_config('show_only_loginScreen') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langShowOnlyLoginScreen') }}
                                                </label>
                                            </div>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input id='hide_login_check' type='checkbox' name='dont_display_login_form' {!! get_config('dont_display_login_form') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{trans('lang_dont_display_login_form')}}
                                                </label>
                                            </div>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input id='hide_login_link_check' type='checkbox' name='hide_login_link' {!! get_config('hide_login_link') ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{trans('lang_hide_login_link')}}
                                                </label>
                                            </div>
                                    </div>
                                </div>

                                
                                <div class='form-group mt-4'>
                                    <label for='theme' class='col-sm-12 control-label-notes mb-1'>{{trans('lang_testimonials')}}: </label>
                                    <div class='col-sm-12'>
                                        <div class='checkbox'>
                                            <label class='label-container'>
                                                <input id='dont_display_testimonials' type='checkbox' name='dont_display_testimonials' {!! get_config('dont_display_testimonials') ? 'checked' : '' !!}>
                                                <span class='checkmark'></span>
                                                {{trans('lang_dont_display_login_testimonials')}}
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
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                    </div>




                    @if($priorities)
                        <div class='col-12 mt-5'>
                            <div id='orderTexts'>
                                @foreach($priorities as $p)
                                    <div class='card panelCard px-lg-4 py-lg-3 p-3 mb-4' data-id='{{ $p->id }}'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center p-0 gap-3 flex-wrap'>
                                            <h3 class='mb-0'>
                                                @if($p->title == 'announcements')
                                                    {{ trans('langAnnouncements')}}
                                                @elseif($p->title == 'popular_courses')
                                                    {{ trans('langPopularCourse')}}
                                                @elseif($p->title == 'texts')
                                                    {{ trans('langHomepageTexts')}}
                                                @elseif($p->title == 'testimonials')
                                                    {{ trans('langSaidForUs')}}
                                                 @elseif($p->title == 'statistics')
                                                    {{ trans('langViewStatics')}}
                                                @else
                                                    {{ trans('langOpenCourses')}}
                                                @endif
                                            </h3>
                                                
                                            <div>
                                                <a href='javascript:void(0);' aria-label="{{ trans('langReorder') }}"><span class='fa fa-arrows pe-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
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

        $('#dont_display_testimonials').on('click',function(){
            if($('#dont_display_testimonials').is(":checked")){
                document.getElementById('dont_display_testimonials').value = 1;
            }else{
                document.getElementById('dont_display_testimonials').value = 0;
            }
        });
    });
</script>

@endsection
