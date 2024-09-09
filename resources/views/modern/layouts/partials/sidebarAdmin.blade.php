
@if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
    <div class='col-12 mb-4'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3>{{ trans('langNewEclassVersion') }}</h3>
            </div>
            <div class='card-body'>
                {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>", "<a href='https://www.openeclass.org/' aria-label='trans('langOpenNewTab')' target='_blank'>www.openeclass.org</a>") !!}
            </div>
        </div>
    </div>
@endif


<div class='col-12 d-flex justify-content-md-start justify-content-center align-items-start gap-3 flex-wrap pb-4'>

    <a class='quickLink' href="search_user.php">
        <i class="fa-solid fa-user settings-icon"></i>{{ trans('langSearchUser') }}
    </a>

    @if($is_admin or $is_departmentmanage_user or $is_power_user)
        <a  href="searchcours.php" class='quickLink'>
            <i class="fa-solid fa-book-open settings-icon"></i>{{ trans('langSearchCourse') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-book-open settings-icon"></i>{{ trans('langSearchCourse') }}
        </a>
    @endif

    @if($is_admin)
        <a href="hierarchy.php" class='quickLink'>
            <i class="fa-solid fa-sitemap settings-icon"></i>{{ trans('langHierarchy') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-sitemap settings-icon"></i>{{ trans('langHierarchy') }}
        </a>
    @endif

    @if($is_admin)
        <a href="eclassconf.php" class='quickLink'>
            <i class="fa-solid fa-gear settings-icon"></i>{{ trans('langConfig') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-gear settings-icon"></i>{{ trans('langConfig') }}
        </a>
    @endif

    @if($is_admin)
        <a href="theme_options.php" class='quickLink'>
            <i class="fa-solid fa-display settings-icon"></i>{{ trans('langThemeSettings') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-display settings-icon"></i>{{ trans('langThemeSettings') }}
        </a>
    @endif

    @if($is_admin)
        <a href="extapp.php" class='quickLink'>
            <i class="fa-solid fa-wrench settings-icon"></i>{{ trans('langExternalTools') }}
        </a>
    @else
        <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" title="{{trans('langForbidden')}}" class='quickLink opacity-help'>
            <i class="fa-solid fa-wrench Primary-500-cl settings-icon"></i>{{ trans('langExternalTools') }}
        </a>
    @endif

</div>



@php
    $col_size = '';
    if($is_admin) {
        $col_size = '3';
    }
    else if($is_power_user or $is_departmentmanage_user) {
        $col_size = '2';
    } else if($is_usermanage_user) {
        $col_size = '1';
    }
@endphp

<div class='col-12 mt-4'>
    <div class="row row-cols-1 row-cols-lg-{{ $col_size }} g-3 g-lg-4">
        <div class='col'>
            <div class='card panelCard p-0 card-transparent m-auto h-100 border-0'>
                <div class='card-body px-0'>
                    <ul class='list-group list-group-flush'>
                        <li class="list-group-item list-group-item-action border-0 pb-3">
                            <i class="fa-solid fa-user-group settings-icons-lg"></i>
                            {!! trans('langUsers') !!}
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/search_user.php">
                                {!! trans('langSearchUser') !!}
                            </a>
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/newuseradmin.php">
                                {!! trans('langNewAccount') !!}
                            </a>
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/listreq.php">
                                {!! trans('langUserRequests') !!}
                            </a>
                        </li>
                        @if ($is_admin)
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/auth.php">
                                    {!! trans('langUserAuthentication') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/mail_ver_settings.php">
                                    {!! trans('langMailVerification') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/change_user.php">
                                    {!! trans('langChangeUser') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/custom_profile_fields.php">
                                    {!! trans('langCPFAdminSideMenuLink') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/eportfolio_fields.php">
                                    {!! trans('langEPFAdminSideMenuLink') !!}
                                </a>
                            </li>
                        @endif
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/multireguser.php">
                                {!! trans('langMultiRegUser') !!}
                            </a>
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/multicourseuser.php">
                                {!! trans('langMultiRegCourseUser') !!}
                            </a>
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/multiedituser.php">
                                {!! trans('langMultiDelUser') !!}
                            </a>
                        </li>
                        <li class="list-group-item element">
                            <a class='TextBold' href="{{$urlAppend}}modules/admin/mailtoprof.php">
                                {!! trans('langInfoMail') !!}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @if ($is_power_user or $is_departmentmanage_user)
            <div class='col'>
                <div class='card panelCard p-0 card-transparent m-auto h-100 border-0'>
                    <div class='card-body px-0'>
                        <ul class='list-group list-group-flush'>
                            <li class="list-group-item list-group-item-action border-0 pb-3">
                                <i class="fa-solid fa-book-open settings-icons-lg"></i>
                                {!! trans('langCourses') !!}
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/searchcours.php">
                                    {!! trans('langSearchCourse') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/course_info/restore_course.php">
                                    {!! trans('langRestoreCourse') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/multicourse.php">
                                    {!! trans('langMultiCourse') !!}
                                </a>
                            </li>
                            @if ($is_admin)
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/autoenroll.php">
                                        {!! trans('langAutoEnroll') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/modules.php">
                                        {!! trans('langDisableModules') !!}
                                    </a>
                                </li>
                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/certbadge.php">
                                        {!! trans('langCertBadge') !!}
                                    </a>
                                </li>
                                @endif
                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/activity.php">
                                        {!! trans('langActivityCourse') !!}
                                    </a>
                                </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @if ($is_admin)
            <div class='col'>
                <div class='card panelCard p-0 border-0 card-transparent h-100'>
                    <div class='card-body px-0'>
                        <ul class='list-group list-group-flush'>
                            <li class="list-group-item list-group-item-action border-0 pb-3">
                                <i class="fa-solid fa-gear settings-icons-lg"></i>
                                {!! trans('langAdminTool') !!}
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/eclassconf.php">
                                    {!! trans('langConfig') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/extapp.php">
                                    {!! trans('langExtAppConfig') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/collaboration_enable.php">
                                    {!! trans('langCollaborationPlatform') !!}
                                </a>
                            </li>
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/widgets.php">
                                    {!! trans('langWidgets') !!}
                                </a>
                            </li>
                            @endif
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/coursecategory.php">
                                    {!! trans('langCourseCategoryActions') !!}
                                </a>
                            </li>
                            @endif
                            @if (get_config('phpMyAdminURL'))
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{ get_config('phpMyAdminUrl') }}">
                                        {!! trans('langDBaseAdmin') !!}
                                    </a>
                                </li>
                            @endif
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/usage/index.php?t=a">
                                    {!! trans('langUsage') !!}
                                </a>
                            </li>
                            @if (get_config('enable_common_docs'))
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/commondocs.php">
                                        {!! trans('langCommonDocs') !!}
                                    </a>
                                </li>
                            @endif
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/cleanup.php">
                                    {!! trans('langCleanUp') !!}
                                </a>
                            </li>
                            @if (get_config('phpSysInfoURL'))
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{ get_config('phpSysInfoURL') }}">
                                        {!! trans('langSysInfo') !!}
                                    </a>
                                </li>
                            @endif
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlAppend}}modules/admin/phpInfo.php">
                                    {!! trans('langPHPInfo') !!}
                                </a>
                            </li>
                            <li class="list-group-item element">
                                <a class='TextBold' href="{{$urlServer}}upgrade/index.php">
                                    {!! trans('langUpgradeBase') !!}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>



@if ($is_admin)
    <div class='col-12 mt-4'>
        <div class="row row-cols-1 row-cols-lg-3 g-3 g-lg-4">
            <div class='col'>
                <div class='card panelCard p-0 border-0 card-transparent h-100'>
                    <div class='card-body px-0'>
                        <ul class='list-group list-group-flush'>
                            <li class="list-group-item list-group-item-action border-0 pb-3">
                                <i class="fa-solid fa-toolbox settings-icons-lg"></i>
                                {!! trans('langAdministratorTools') !!}
                            </li>
                            @if ($is_admin)
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/addadmin.php">
                                        {!! trans('langAdmins') !!}
                                    </a>
                                </li>
                            @endif
                            @if (isset($is_admin) and $is_admin)
                                <li class="list-group-item element">
                                    <a class='TextBold' href="{{$urlAppend}}modules/admin/adminannouncements.php">
                                        {!! trans('langAnnouncements') !!}
                                    </a>
                                </li>
                                @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                                <li class="list-group-item element">
                                    <a class='TextBold' href="http://docs.openeclass.org/{{$manual_language}}/admin">
                                        {!! trans('langAdminManual') !!}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class='col'>
                @if($is_admin)
                    <div class='card panelCard p-0 border-0 card-transparent h-100'>
                        <div class='card-body px-0'>
                            <ul class='list-group list-group-flush'>
                                <li class="list-group-item list-group-item-action border-0 pb-3">
                                    <i class="fa-solid fa-list-ul settings-icons-lg"></i>
                                    {!! trans('langFaculties') !!}
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='TextBold'>
                                        {!! trans('langHierarchy') !!}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
            <div class='col'>
                @if($is_admin)
                    <div class='card panelCard p-0 border-0 card-transparent h-100'>
                        <div class='card-body px-0'>
                            <ul class='list-group list-group-flush'>
                                <li class="list-group-item list-group-item-action border-0 pb-3">
                                    <i class="fa-solid fa-sitemap settings-icons-lg"></i>
                                    {!! trans('langEclassThemes') !!}
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/theme_options.php" class='TextBold'>
                                        {!! trans('langThemeSettings') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/manage_home.php" class='TextBold'>
                                        {!! trans('langAdminManageHomepage') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/manage_footer.php" class='TextBold'>
                                        {!! trans('langAdminManageFooter') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/faq_create.php" class='TextBold'>
                                        {!! trans('langAdminCreateFaq') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/privacy_policy_conf.php" class='TextBold'>
                                        {!! trans('langPrivacyPolicy') !!}
                                    </a>
                                </li>
                                <li class="list-group-item element">
                                    <a href="{{$urlAppend}}modules/admin/contact_info.php" class='TextBold'>
                                        {!! trans('langUpgContact') !!}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif



