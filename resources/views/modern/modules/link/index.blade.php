@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                        
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>

                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-folder-open" aria-hidden="true"></i> {{$toolName}} @if($course_code)του μαθήματος <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>@endif
                            <div class="manage-course-tools"style="float:right">
                                @if($is_editor == 1)
                                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])              
                                @endif
                            </div>
                        </legend>
                    </div>
                    
                    @if($course_code)
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <span class="control-label-notes ms-1">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                        <div class="row p-2"></div><div class="row p-2"></div>
                    @endif

                    

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                     <div class='row p-2'></div>

                        
                                <div class='table-responsive'>
                                    <table class='announcements_table nocategory-links'>
                                    @if (count($general_category->links) > 0)
                                        <tr class='notes_thead' style='height:45px;'>
                                            <th class='text-white text-left' style='padding-left:15px;'>{{ trans('langNoCategory') }}</th>
                                            @if ($display_tools)
                                            <th class='text-white text-center' style='width:109px;'>{!! icon('fa-cogs') !!}</th>
                                            @endif
                                        <tr>
                                        @include('modules.link.common.linkList', ['category' => $general_category])
                                    @else
                                        <tr class='notes_thead' style='height:45px'>
                                            <th class='text-white text-left list-header' style='padding-left:15px;'>{{ trans('langNoCategory') }}</th>
                                        </tr>
                                        <tr>
                                            <td class='text-white text-center not_visible nocategory-link' style='padding-left:15px;'> - {{ trans('langNoLinkInCategory') }} - </td>
                                        </tr>
                                    @endif
                                    </table>
                                </div>
                           
                        @if ($social_bookmarks_enabled == 1)
                            
                                    <div class='table-responsive'>
                                        <table class='announcements_table nocategory-links'>
                                        @if (count($social_category->links) > 0)
                                            <tr class='notes_thead' style='height:45px;'>
                                                <th class='text-white text-left' style='padding-left:15px;'>
                                                    {{ trans('langSocialCategory')."   " }}
                                                    @if (!$socialview)
                                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}&amp;socialview'>{!! icon('fa-folder', trans('langViewShow')) !!}</a>
                                                    @else
                                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>{!! icon('fa-folder-open', trans('langViewHide')) !!}</a>
                                                    @endif
                                                </th>
                                                @if (isset($_SESSION['uid']) && !$is_in_tinymce)
                                                    <th class='text-white text-center'>{!! icon('fa-gears') !!}</th>
                                                @endif
                                            </tr>
                                            @if ($socialview)
                                                @include('modules.link.common.linkList', ['category' => $social_category])
                                            @endif
                                        @else
                                            <tr class='notes_thead' style='height:45px;'>
                                                <th class='text-white text-left list-header' style='padding-left:15px;'>
                                                    {{ trans('langSocialCategory') }}
                                                </th>
                                            </tr>
                                            <tr>
                                                <td style='margin-left:15px;'class='text-center not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                            </tr>
                                        @endif
                                        </table>
                                    </div>
                               
                        @endif

                      
                                <div class='table-responsive'>
                                    <table class='announcements_table category-links'>
                                    <tr class='notes_thead' style='height:45px;'>
                                            <th class='text-white' style='padding-left:15px;'>{{ trans('langCategorisedLinks').'   ' }}
                                            @if ($categories)    
                                                @if (isset($urlview) && abs($urlview) == 0)
                                                    <a style='padding-left:15px;' href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('1', count($categories)) . $tinymce_params . $socialview_param }}'>
                                                        {!! icon('fa-folder', trans('langViewShow')) !!}
                                                    </a>
                                                @else
                                                    <a style='padding-left:15px;' href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('0', count($categories)) . $tinymce_params . $socialview_param }}'>
                                                        {!! icon('fa-folder-open', trans('langViewHide')) !!}
                                                    </a>
                                                @endif
                                            @endif
                                            </th>
                                            @if ($categories && $display_tools)
                                                <th class='text-white text-center' style='width:109px;'>{!! icon('fa-cogs') !!}</th>
                                            @endif
                                        </tr>                        
                                    @if ($categories)
                                        @foreach ($categories as $key => $category)
                                            @if ((isset($urlview[$key]) and $urlview[$key] == '1'))
                                                <?php 
                                                    $newurlview = $urlview;
                                                    $newurlview[$key] = '0';
                                                ?>
                                                <tr class='link-subcategory-title'>
                                                    <th class = 'text-left category-link'> {!! icon('fa-folder-open-o', trans('langViewHide')) !!}
                                                        <a style='padding-left:15px;' href='index.php?course={{ $course_code }}&amp;urlview={{ $newurlview.$tinymce_params.$socialview_param }}' class='open-category'>
                                                            {{ $category->name }}
                                                        </a>
                                                        @if (!empty($description))
                                                            <br>
                                                            <span class='link-description'>{{ $description }}</span>
                                                        @endif
                                                    </th>
                                                    @if ($display_tools)
                                                        <td class='option-btn-cell'>
                                                        {!! action_button(array(
                                                                array('title' => trans('langEditChange'),
                                                                    'icon' => 'fa-edit',
                                                                    'url' => "index.php?course=$course_code&amp;id=" . getIndirectReference($category->id) . "&amp;urlview=$urlview&amp;action=editcategory"),
                                                                array('title' => trans('langUp'),
                                                                    'level' => 'primary',
                                                                    'icon' => 'fa-arrow-up',
                                                                    'disabled' => $key == 0,
                                                                    'url' => "index.php?course=$course_code&amp;urlview=$urlview&amp;cup=" . getIndirectReference($category->id)),
                                                                array('title' => trans('langDown'),
                                                                    'level' => 'primary',
                                                                    'icon' => 'fa-arrow-down',
                                                                    'disabled' => $key == count($categories)- 1,
                                                                    'url' => "index.php?course=$course_code&amp;urlview=$urlview&amp;cdown=" . getIndirectReference($category->id)),
                                                                array('title' => trans('langDelete'),
                                                                        'icon' => 'fa-times',
                                                                        'url' => "index.php?course=$course_code&amp;id=" . getIndirectReference($category->id) . "&amp;urlview=$urlview&amp;action=deletecategory",
                                                                        'class' => 'delete',
                                                                        'confirm' => trans('langCatDel'))
                                                                ))  !!}
                                                        </td>
                                                    @endif
                                                </tr>

                                                @include('modules.link.common.linkList')

                                                @if (count($category->links) == 0)
                                                <tr>
                                                    <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                                    @if ($display_tools)
                                                        <td></td>
                                                    @endif                                
                                                <tr>
                                                @endif                            
                                            @else
                                                <tr class='link-subcategory-title'>
                                                    <th class = 'text-left category-link'>{!! icon('fa-folder-o', trans('langViewShow')) !!}
                                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ empty($urlview) ? makedefaultviewcode($key) : substr_replace($urlview, '1', $key, 1) }}{{ $tinymce_params }}' class='open-category'>
                                                            {{ $category->name }} 
                                                        </a>
                                                        @if (!empty($description))
                                                            <br>
                                                            <span class='link-description'>{!! standard_text_escape($category->description) !!}</span</th>
                                                        @endif
                                                    </th>
                                                    @if ($display_tools)
                                                        <td class='option-btn-cell'>
                                                            {!! action_button(array(
                                                                array('title' => trans('langEditChange'),
                                                                    'icon' => 'fa-edit',
                                                                    'url' => "index.php?course=$course_code&amp;id=" . getIndirectReference($category->id) . "&amp;urlview=$urlview&amp;action=editcategory"),
                                                                array('title' => trans('langUp'),
                                                                    'level' => 'primary',
                                                                    'icon' => 'fa-arrow-up',
                                                                    'disabled' => $key == 0,
                                                                    'url' => "index.php?course=$course_code&amp;urlview=$urlview&amp;cup=" . getIndirectReference($category->id)),
                                                                array('title' => trans('langDown'),
                                                                    'level' => 'primary',
                                                                    'icon' => 'fa-arrow-down',
                                                                    'disabled' => $key == count($categories)- 1,
                                                                    'url' => "index.php?course=$course_code&amp;urlview=$urlview&amp;cdown=" . getIndirectReference($category->id)),
                                                                array('title' => trans('langDelete'),
                                                                        'icon' => 'fa-times',
                                                                        'url' => "index.php?course=$course_code&amp;id=" . getIndirectReference($category->id) . "&amp;urlview=$urlview&amp;action=deletecategory",
                                                                        'class' => 'delete',
                                                                        'confirm' => trans('langCatDel'))
                                                                ))  !!}
                                                        </td>
                                                    @endif
                                                </tr>                        
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                                        </tr>                    
                                    @endif
                                    </table>
                                </div>
                           

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
