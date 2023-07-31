@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @if(!$is_in_tinymce)

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                    @endif
                    

                    {!! isset($action_bar) ?  $action_bar : '' !!}

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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                  

                    <div class='col-sm-12'>
                        <div class='table-responsive mb-4'>
                            <table class='table-default nocategory-links'>
                            @if (count($general_category->links) > 0)
                                <tr class='list-header'>
                                    <th class='text-start'>{{ trans('langNoCategory') }}</th>
                                    @if ($display_tools)
                                    <th class='text-center'>{!! icon('fa-cogs') !!}</th>
                                    @endif
                                <tr>
                                @include('modules.link.common.linkList', ['category' => $general_category])
                            @else
                                <tr class='list-header'>
                                    <th class='text-start'>{{ trans('langNoCategory') }}</th>
                                </tr>
                                <tr>
                                    <td class='text-dark text-start not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                </tr>
                            @endif
                            </table>
                        </div>
                    </div>
                           
                    @if ($social_bookmarks_enabled == 1)
                        <div class='col-sm-12'>
                            <div class='table-responsive mb-4'>
                                <table class='table-default nocategory-links'>
                                @if (count($social_category->links) > 0)
                                    <tr class='list-header'>
                                        <th class='text-start'>
                                            {{ trans('langSocialCategory')."   " }}
                                            @if (!$socialview)
                                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}&amp;socialview'>{!! icon('fa-folder', trans('langViewShow')) !!}</a>
                                            @else
                                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>{!! icon('fa-folder-open', trans('langViewHide')) !!}</a>
                                            @endif
                                        </th>
                                        @if (isset($_SESSION['uid']) && !$is_in_tinymce)
                                            <th class='text-center'>{!! icon('fa-gears') !!}</th>
                                        @endif
                                    </tr>
                                    @if ($socialview)
                                        @include('modules.link.common.linkList', ['category' => $social_category])
                                    @endif
                                @else
                                    <tr class='list-header'>
                                        <th class='text-start'>
                                            {{ trans('langSocialCategory') }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <td class='not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                    </tr>
                                @endif
                                </table>
                            </div>
                        </div>
                            
                    @endif

                    <div class='col-sm-12'>
                        <div class='table-responsive mb-4'>
                            <table class='table-default category-links'>
                            <tr class='list-header'>
                                    <th>{{ trans('langCategorisedLinks').'   ' }}
                                    @if ($categories)    
                                        @if (isset($urlview) && abs($urlview) == 0)
                                            <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('1', count($categories)) . $tinymce_params . $socialview_param }}'>
                                                {!! icon('fa-folder', trans('langViewShow')) !!}
                                            </a>
                                        @else
                                            <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('0', count($categories)) . $tinymce_params . $socialview_param }}'>
                                                {!! icon('fa-folder-open', trans('langViewHide')) !!}
                                            </a>
                                        @endif
                                    @endif
                                    </th>
                                    @if ($categories && $display_tools)
                                        <th class='text-center' style='width:109px;'>{!! icon('fa-cogs') !!}</th>
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
                                            <th class = 'text-start category-link'> {!! icon('fa-folder-open', trans('langViewHide')) !!}
                                                <a style='padding-left:15px;' href='index.php?course={{ $course_code }}&amp;urlview={{ $newurlview.$tinymce_params.$socialview_param }}' class='open-category'>
                                                    {{ $category->name }}
                                                </a>
                                                @if (!empty($description))
                                                    <br>
                                                    <span class='link-description'>{{ $description }}</span>
                                                @endif
                                            </th>
                                            @if ($display_tools)
                                                <td class='option-btn-cell text-center'>
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
                                                                'icon' => 'fa-xmark',
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
                                            <td class='text-start not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                            @if ($display_tools)
                                                <td></td>
                                            @endif                                
                                        <tr>
                                        @endif                            
                                    @else
                                        <tr class='link-subcategory-title'>
                                            <th class = 'text-start category-link'>{!! icon('fa-folder-open', trans('langViewShow')) !!}
                                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ empty($urlview) ? makedefaultviewcode($key) : substr_replace($urlview, '1', $key, 1) }}{{ $tinymce_params }}' class='open-category'>
                                                    {{ $category->name }} 
                                                </a>
                                                @if (!empty($description))
                                                    <br>
                                                    <span class='link-description'>{!! standard_text_escape($category->description) !!}</span</th>
                                                @endif
                                            </th>
                                            @if ($display_tools)
                                                <td class='option-btn-cell text-center'>
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
                                                                'icon' => 'fa-xmark',
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
                                    <td class='text-start not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                                </tr>                    
                            @endif
                            </table>
                        </div>
                    </div>
                           

                </div>
            </div>

        </div>
    
</div>
</div>
@endsection
