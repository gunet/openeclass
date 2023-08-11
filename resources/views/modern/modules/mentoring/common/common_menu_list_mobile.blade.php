
<div class='d-block d-lg-none'>
    <div style='width:250px;' class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class='offcanvas-body'>
            <div class='col-12 d-flex justify-content-center align-items-center mt-0'>
                <a class='backToEclass'><img class="mentoring_logo_img" src="{{ $logo_img }}"/></a>
            </div>
            <div class='col-12 d-flex justify-content-center align-items-center mt-0'>
                <ul class="mobileMenuUl list-group rounded-0">
                    
                    @if (isset($_SESSION['uid']))
                        <a href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php" class="home_menu list-group-item bg-transparent TextSemiBold small-text">
                            <span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform')}}
                        </a>
                    @else
                        <a id='goToMentoringPlatformBtnMobile' href="{{ $urlAppend }}?goToMentoring=true" class="home_menu list-group-item bg-transparent TextSemiBold small-text">
                            <span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform')}}
                        </a>
                    @endif

                    @if((get_config('mentoring_platform') and get_config('mentoring_always_active')))
                        @if(!isset($_SESSION['uid']))
                            <a id="reg_menuMobile" href="{{ $urlAppend }}modules/auth/registration.php" class="@if(get_config('registration_link')=='hide') d-none @endif register_menu list-group-item bg-transparent TextSemiBold small-text">
                                <span class='fa fa-pencil'></span>&nbsp{{ trans('langRegister')}}
                            </a>
                        @endif
                    @endif
                
                
                    <a href="{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php" class="mentors_menu list-group-item bg-transparent TextSemiBold small-text">
                        <span class='fa fa-magic'></span>&nbsp{{ trans('langOurMentors') }}
                    </a>
                
                
                    <a href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php" class="program_menu list-group-item bg-transparent TextSemiBold small-text">
                        <span class='fa fa-tasks'></span>&nbsp{{ trans('langOurMentoringPrograms')}}
                    </a>
                
                    @if (isset($_SESSION['uid']))
                        <a href="{{ $urlAppend }}modules/mentoring/profile/user_profile.php" class="profile_menu list-group-item bg-transparent TextSemiBold small-text">
                            <span class='fa fa-user'></span>&nbsp{{ trans('langMyProfile')}}
                        </a>
                    @endif
                   
                </ul>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {

        $('.profile_menu').on('click',function(){
            localStorage.setItem("MenuMentoring","profile");
        });
        $('.program_menu').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
        $('.mentors_menu').on('click',function(){
            localStorage.setItem("MenuMentoring","mentors");
        });
        $('.home_menu,#goToMentoringPlatformBtnMobile').on('click',function(){
            localStorage.setItem("MenuMentoring","home");
        });
        $('#reg_menuMobile').on('click',function(){
            localStorage.setItem("MenuMentoring","register");
        });

        if(localStorage.getItem("MenuMentoring") == "profile"){
            $('.profile_menu').addClass('active');
        }else if(localStorage.getItem("MenuMentoring") == "program"){
            $('.program_menu').addClass('active');
        }else if(localStorage.getItem("MenuMentoring") == "mentors"){
            $('.mentors_menu').addClass('active');
        }else if(localStorage.getItem("MenuMentoring") == "home"){
            $('.home_menu').addClass('active');
        }else if(localStorage.getItem("MenuMentoring") == "registr"){
            $('.register_menu').addClass('active');
        }

    });
</script>
