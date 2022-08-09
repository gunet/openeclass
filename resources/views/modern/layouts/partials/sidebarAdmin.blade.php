<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!-- Το αρχείο αυτό θα περιέχει τα panels του admin -->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->
<!---------------------------------------------------->

@php $countNewPanel = 0; @endphp
@foreach ($toolArr as $key => $tool_group)
<div class='@if(count($toolArr) == 1) and ($is_power_user) col-lg-12 col-12 mt-3 @else col-lg-6 col-12 mt-3 @endif'>
    
    @if($countNewPanel == 2)
    <div class='panel panel-admin mb-3'>
        <div class='panel-heading text-center'>
            <span class='colorPalette'>{{trans('langAdministratorTools')}}</span>
        </div>
        <div class='panel-body NoBorderTop'>
            <ul class="list-group list-group-flush">
                @if ($is_power_user or $is_departmentmanage_user)
                    @if ($is_admin)
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="{{$urlAppend}}modules/admin/addadmin.php" class='list-group-item bg-light'>
                                <span class='toolAdminText'>{!!  $GLOBALS['langAdmins'] !!}</span>      
                            </a>
                        </li>
                    @endif
                    @if (isset($is_admin) and $is_admin)
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="{{$urlAppend}}modules/admin/adminannouncements.php" class='list-group-item bg-light'>
                                <span class='toolAdminText'>{!!  $GLOBALS['langAdminAn'] !!}</span>      
                            </a>
                        </li>
                        @php $manual_language = ($language == 'el')? $language: 'en'; @endphp
                        <li class="list-group-item border-0 admin-list-group">
                            <a href="http://docs.openeclass.org/{{$manual_language}}/admin" class='list-group-item bg-light'>
                                <span class='toolAdminText'>{!!  $GLOBALS['langAdminManual'] !!}</span>      
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
    @endif

    <div class='panel panel-admin m-auto'>
        <div class='panel-heading text-center'>
            <span class='colorPalette'>{{ $tool_group[0]['text'] }}</span>
        </div>
        <div class='panel-body NoBorderTop'>
            <ul class="list-group list-group-flush">
                @foreach ($tool_group[1] as $key2 => $tool)
                    <li class="list-group-item border-0 admin-list-group">
                        <a href="{{ $tool_group[2][$key2] }}" class='list-group-item bg-light{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            
                            <span class='toolAdminText'>{!! $tool !!}</span>
                               
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if($countNewPanel == 1)
        @if($is_power_user or $is_departmentmanage_user)
            @if($is_admin)
                <div class='panel panel-admin mt-3'>
                    <div class='panel-heading text-center'>
                        <span class='colorPalette'>{{trans('langFaculties')}}</span>
                    </div>
                    <div class='panel-body NoBorderTop'>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 admin-list-group">
                                <a href="{{$urlAppend}}modules/admin/hierarchy.php" class='list-group-item bg-light'>
                                    <span class='toolAdminText'>{!!  $GLOBALS['langHierarchy'] !!}</span>      
                                </a>
                            </li>
                            <li class="list-group-item border-0 admin-list-group">
                                <a href="{{$urlAppend}}modules/admin/coursecategory.php" class='list-group-item bg-light'>
                                    <span class='toolAdminText'>{!!  $GLOBALS['langCourseCategoryActions'] !!}</span>      
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        @endif
    @endif

</div>

@php $countNewPanel++; @endphp
@endforeach



{{--
<div class="panel bg-transparent">
    @foreach ($toolArr as $key => $tool_group)
        <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
            <div class="panel-sidebar-heading">
                <div class="panel-title h3">
                    <div class='row'>
                        <div class='col-1'>
                            <span class="fa fa-chevron-right text-warning ms-0 mt-1" style='font-size:12px;'></span>
                        </div>
                        <div class='col-10'>
                            <span class='text-wrap text-white fs-6'>{{ $tool_group[0]['text'] }}</span>
                        </div>
                    </div>
                    
                    
                </div><hr class='text-white'>
            </div>
        </a>
        <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
            @foreach ($tool_group[1] as $key2 => $tool)
            <a href="{{ $tool_group[2][$key2] }}" class='list-group-item bg-transparent{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                    
                    <div class='row'>
                        <div class='col-xxl-1 col-xl-1 col-2'>
                            <span class="fa {{ $tool_group[3][$key2] }} fa-fw mt-1 text-warning toolSidebarTxt"></span>
                        </div>
                        <div class='col-xxl-10 col-xl-10 col-9'>
                            <span class='text-white toolSidebarTxt'>{!! $tool !!}</span>
                        </div>
                        
                    </div>

            </a>
            @endforeach
        </div>
        <div class='p-3'></div>
    @endforeach
</div>
{{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}

--}}




