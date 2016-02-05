@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                    <table class='table-default nocategory-links'>
                    @if (count($general_category->links) > 0)
                        <tr class='list-header'>
                            <th class='text-left'>{{ trans('langNoCategory') }}</th>
                            @if ($display_tools)
                            <th class='text-center' style='width:109px;'>{!! icon('fa-gears') !!}</th>
                            @endif
                        <tr>
                        @include('modules.link.common.linkList', ['category' => $general_category])
                    @else
                        <tr class='list-header'>
                            <th class='text-left list-header'>{{ trans('langNoCategory') }}</th>
                        </tr>
                        <tr>
                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                        </tr>
                    @endif
                    </table>
                </div>
            </div>
        </div>
        @if ($social_bookmarks_enabled == 1)
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='table-responsive'>
                        <table class='table-default nocategory-links'>
                        @if (count($social_category->links) > 0)
                            <tr class='list-header'>
                                <th class='text-left'>
                                    {{ trans('langSocialCategory')."   " }}
                                    @if (!$socialview)
                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}&amp;socialview'>{!! icon('fa-folder', trans('showall')) !!}</a>
                                    @else
                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>{!! icon('fa-folder-open', trans('shownone')) !!}</a>
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
                                <th class='text-left list-header'>
                                    {{ trans('langSocialCategory') }}
                                </th>
                            </tr>
                            <tr>
                                <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                            </tr>
                        @endif
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                    <table class='table-default category-links'>
                       <tr class='list-header'>
                            <th>{{ trans('langCategorisedLinks').'   ' }}
                            @if ($categories)    
                                @if (isset($urlview) && abs($urlview) == 0)
                                    <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('1', count($categories)) . $tinymce_params . $socialview_param }}'>
                                        {!! icon('fa-folder', trans('showall')) !!}
                                    </a>
                                @else
                                    <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('0', count($categories)) . $tinymce_params . $socialview_param }}'>
                                        {!! icon('fa-folder-open', trans('shownone')) !!}
                                    </a>
                                @endif
                            @endif
                            </th>
                            @if ($categories && $display_tools)
                                <th class='text-center' style='width:109px;'>{!! icon('fa-gears') !!}</th>
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
                                    <th class = 'text-left category-link'> {!! icon('fa-folder-open-o', trans('shownone')) !!}
                                        <a href='index.php?course={{ $course_code }}&amp;urlview={{ $newurlview.$tinymce_params.$socialview_param }}' class='open-category'>
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

                                @if (count($category->links) == 1)
                                <tr>
                                    <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                                    @if ($display_tools)
                                        <td></td>
                                    @endif                                
                                <tr>
                                @endif                            
                            @else
                                <tr class='link-subcategory-title'>
                                    <th class = 'text-left category-link'>{!! icon('fa-folder-o', trans('showall')) !!}
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
@endsection