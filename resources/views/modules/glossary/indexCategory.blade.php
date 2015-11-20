@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (count($categories))
        <div class='table-responsive glossary-categories'>    
            <table class='table-default'>
                <tr class='list-header'>
                    <th class='text-left'>{{ trans('langName') }}</th>
                    @if ($is_editor)
                        <th class='text-center'>{!! icon('fa-gears') !!}</th>
                    @endif
                <tr>
                @foreach ($categories as $category)
                    <tr>
                        <td class='space-left'>
                            <a href='{{ $base_url }}&amp;cat={{ getIndirectReference($category->id) }}'>
                                <strong> {{ $category->name }}</strong>
                            </a>
                            @if ($category->description)
                            <br>
                            <small class='text-muted'>
                                {!! standard_text_escape($category->description) !!}
                            </small>
                            @endif
                        </td>
                        @if ($is_editor)
                            <td class='option-btn-cell'>
                            {!! action_button(array(
                                    array('title' => trans('langCategoryMod'),
                                          'url' => "$cat_url&amp;edit=" . getIndirectReference($category->id),
                                          'icon' => 'fa-edit'),
                                    array('title' => trans('langCategoryDel'),
                                          'url' => "$cat_url&amp;delete=" . getIndirectReference($category->id),
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => trans('langConfirmDelete')
                                          )
                                    )
                                ) !!}                                
                            </td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @else 
        <div class='alert alert-warning'>{{ trans('langNoResult') }}</div>
    @endif
@endsection

