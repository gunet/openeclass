@extends('layouts.default_old')

@section('content')
    <div class='row'>
        <div class='col-sm-12'>
            <div class='table-responsive'>
                <table class='table-default nocategory-links'>
                    @if ($numberofzerocategory !== 0)
                        <tr class='list-header'>
                            <th class='text-left'>{{ trans('langNoCategory') }}</th>
                        </tr>
                        @include('modules.link.common.linkList', ['category' => $result_zero_category])
                    @else
                        <tr class='list-header'>
                            <th class='text-left list-header'> {{ trans('langNoCategory') }}</th>
                        </tr>
                        <tr>
                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkInCategory') }} - </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if ($social_bookmarks_enabled)
        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                    <table class='table-default nocategory-links'>
                        @if ($numberofsocialcategory !== 0)
                            <tr class='list-header'>
                                <th class='text-left'>{{ trans('langSocialCategory') }}</th>
                            </tr>
                            @include('modules.link.common.linkList', ['category' => $result_social_category])
                        @else
                            <tr class='list-header'>
                                <th class='text-left list-header'>{{ trans('langSocialCategory') }}</th>
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
                    @if ($aantalcategories > 0)
                        <tr class='list-header'><th>{{ trans('langCategorisedLinks') }} </th></tr>
                    @else
                        <tr>
                            <th>{{ trans('langCategorisedLinks') }} </th>
                        </tr>
                        <tr>
                            <td class='text-left not_visible nocategory-link'> - {{ trans('langNoLinkCategories') }} - </td>
                        <tr>
                    @endif

                    @foreach ($resultcategories as $data)
                        <tr class='link-subcategory-title'>
                            <th class = 'text-left category-link'>
                                <span class='fa fa-folder-open'></span>
                                {{ $data->name }}
                                @if (!empty($data->description))
                                    <br><span class='link-description'> {!! standard_text_escape($data->description) !!} </span>
                                @endif
                            </th>
                        </tr>
                        @include('modules.link.common.linkList', ['category' => $result_link_category[$data->id]])
                    @endforeach
                </table>
            </div>
        </div>
    </div>

@endsection