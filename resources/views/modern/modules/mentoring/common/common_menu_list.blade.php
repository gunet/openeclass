
<div class='d-none d-lg-block'>
        <ul class="container-items nav">
            
            @if (isset($_SESSION['uid']))
            <li class="nav-item">
                <a href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php" class="home_menu nav-link menu-item mx-lg-2">
                    {{ trans('langHomeMentoringPlatform')}}
                </a>
            </li>
            @else
            <li class="nav-item mentoring_program_nav_item rounded-2" role="presentation">
                <a href="{{ $urlAppend }}?goToMentoring=true" class="home_menu nav-link menu-item mx-lg-2">
                    {{ trans('langHomeMentoringPlatform')}}
                </a>
            </li>
            @endif

            @if((get_config('mentoring_platform') and get_config('mentoring_always_active')))
                @if(!isset($_SESSION['uid']))
                <li class="nav-item mentoring_program_nav_item rounded-2" role="presentation">
                    <a id="reg_menu" href="{{ $urlAppend }}modules/auth/registration.php" class="@if(get_config('registration_link')=='hide') d-none @endif register_menu nav-link menu-item mx-lg-2">
                        {{ trans('langRegister')}}
                    </a>
                </li>
                @endif
            @endif

            <li class="nav-item mentoring_program_nav_item rounded-2" role="presentation">
                <a href="{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php" class="mentors_menu nav-link menu-item mx-lg-2">
                    {{ trans('langOurMentors') }}
                </a>
            </li>

            <li class="nav-item mentoring_program_nav_item rounded-2" role="presentation">
                <a href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php" class="program_menu nav-link menu-item mx-lg-2">
                    {{ trans('langOurMentoringPrograms')}}
                </a>
            </li>

            @if (isset($_SESSION['uid']))
            <li class="nav-item mentoring_program_nav_item rounded-2" role="presentation">
                <a href="{{ $urlAppend }}modules/mentoring/profile/user_profile.php" class="profile_menu nav-link menu-item mx-lg-2">
                    {{ trans('langMyProfile')}}
                </a>
            </li>
            @endif
        </ul>
</div>

<script>
    $(document).ready(function() {

        $('#imgEclassBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","home");
        });

        $('#reg_menu').on('click',function(){
            localStorage.setItem("MenuMentoring","register");
        });

        if(localStorage.getItem("MenuMentoring") == "register"){
            $('.register_menu').addClass('active');
        }

        $('.no_uid_menu').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });
        
    });
</script>

