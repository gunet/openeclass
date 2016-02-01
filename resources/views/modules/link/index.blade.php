@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($countlinks > 0)
        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                    <table class='table-default nocategory-links'>
                    @if (count($general_category->links) !== 0)
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
                            @if ($display_tools)
                            <th class='text-center' style='width:109px;'>{{ icon('fa-gears') }}</th>
                            @endif
                        </tr>
                        <tr>
                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>";
                            @if ($display_tools)
                            <td></td>
                            @endif
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
                        @if (count($social_category->links) !== 0)
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
                                <th class='text-left list-header'>{{ trans('langSocialCategory') }}</th>
                            @if (isset($_SESSION['uid']) && !$is_in_tinymce)
                                <th class='text-center'>{!! icon('fa-gears') !!}</th>
                            @endif
                            </tr>
                            <tr>
                                <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                            @if (isset($_SESSION['uid']) && !$is_in_tinymce)
                                <td></td>
                            @endif
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
                    @if (count($categories) > 0)
                       <tr class='list-header'>
                            <th>{{ trans('langCategorisedLinks').'   ' }}
                            @if (isset($urlview) and abs($urlview) == 0)
                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('1', $aantalcategories) . $tinymce_params . $socialview_param }}'>
                                    {!! icon('fa-folder', trans('showall')) !!}
                                </a>
                            @else
                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('0', $aantalcategories) . $tinymce_params . $socialview_param }}'>
                                    {!! icon('fa-folder-open', trans('shownone')) !!}
                                </a>
                            @endif
                            </th>
                            @if ($display_tools)
                                <th class='text-center' style='width:109px;'>{!! icon('fa-gears') !!}</th>
                            @endif
                        </tr>
                    @else
                        <tr>
                            <th> {{ trans('langCategorisedLinks')." " }}
                            @if (isset($urlview) and abs($urlview) == 0)
                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('1', $aantalcategories) . $tinymce_params . $socialview_param }}'>&nbsp;&nbsp;{!! icon('fa-folder', trans('showall')) !!}</a>
                            @else
                                <a href='index.php?course={{ $course_code }}&amp;urlview={{ str_repeat('0', $aantalcategories) . $tinymce_params . $socialview_param }}'>&nbsp;&nbsp;{!! icon('fa-folder-open', trans('shownone')) !!}</a>
                            @endif
                            </th>
                            @if ($display_tools)
                                <th class='text-center' style='width:109px;'>{!! icon('fa-gears') !!}</th>
                            @endif
                        </tr>
                        <tr>
                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                            @if ($display_tools)
                                <td></td>
                            @endif
                        <tr>
                    @endif
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
                                        {{ showcategoryadmintools($category->id) }}
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
                                            {!! showcategoryadmintools($category->id) !!}
                                        </td>
                                    @endif
                                </tr>                        
                            @endif
                        @endforeach
                    @endif
                    </table>
                </div>
            </div>
        </div>
    @else
    <div class='alert alert-warning'>{{ trans('langNoLinksExist') }}</div>
    @endif
@endsection