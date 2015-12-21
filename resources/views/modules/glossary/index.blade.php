@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($expand_glossary && $total_glossary_terms > $max_glossary_terms)
        <div class='alert alert-warning'>{!! trans('langGlossaryOverLimit',["<b>$max_glossary_terms</b>"]) !!}</div>
    @endif        
    @if ($glossary_index && count($prefixes) > 1)
    <nav>
        <ul class="pagination">
        @foreach ($prefixes as $key => $letter)
            <li {!! (!isset($_GET['prefix']) && !$cat_id && !$key) ||
                    (isset($_GET['prefix']) && $_GET['prefix'] == $letter)? " class='active'" : "" !!} ><a href="{{ $base_url."&amp;prefix=" . urlencode($letter) }}">{{ $letter }}</a></li>
        @endforeach
        </ul>
    </nav>
    @endif
    @if ($glossary_terms)
        <div class='table-responsive glossary-categories'>
            <table class='table-default'>
                <tr class='list-header'>
                    <th class='text-left'>{{ trans('langGlossaryTerm') }}</th>
                    <th class='text-left'>{{ trans('langGlossaryDefinition') }}</th>
                    @if ($is_editor)
                    <th class='text-center'>{!! icon('fa-gears') !!}</th>
                    @endif
                </tr>
                @foreach ($glossary_terms as $glossary_term)
                <tr>
                    <td width='150'>
                        <a href='{{ $base_url."&amp;id=" . getIndirectReference($glossary_term->id) }}'>
                            <strong>{{ $glossary_term->term }}</strong>
                        </a>
                        <br>
                        <span>
                            <small>
                            @if ($glossary_term->category_id)
                               <span class='text-muted'> 
                                   {{ trans('langCategory') }}: 
                                   <a href='{{ $base_url }}&amp;cat={{ getIndirectReference($glossary_term->category_id) }}'> 
                                       {{ $categories[$glossary_term->category_id] }}
                                   </a>
                               </span>
                            @endif
                            </small>
                        </span>                            
                    </td>
                    <td>
                        <em>
                            {{ $glossary_term->definition ?: ""}}
                        </em>
                        @if ($glossary_term->url)
                            <div>
                                <span class='term-url'>
                                    <small>
                                        <a href='{{ $glossary_term->url }}' target='_blank'>
                                            {{ $glossary_term->url }}&nbsp;&nbsp;<i class='fa fa-external-link' style='color:#444;'></i>
                                        </a>
                                    </small>
                                </span>
                            </div>                        
                        @endif
                        @if ($glossary_term->notes)
                            <br>
                            <u>{{ trans("langComments") }}:</u>
                            <div class='text-muted'>
                                {!! standard_text_escape($glossary_term->notes) !!}
                            </div>
                        @endif
                    </td>
                    @if ($is_editor)
                    <td class='option-btn-cell'>
                        {!! 
                            action_button(array(
                                array('title' => trans('langEditChange'),
                                      'url' => $base_url ."&amp;edit=". getIndirectReference($glossary_term->id),
                                      'icon' => 'fa-edit'),
                                array('title' => trans('langDelete'),
                                      'url' => $base_url ."&amp;delete=". getIndirectReference($glossary_term->id),
                                      'icon' => 'fa-times',
                                      'class' => 'delete',
                                      'confirm' => trans('langConfirmDelete'))
                                )
                            ) 
                        !!}                          
                    </td>                  
                    @endif
                @endforeach
            </table>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langNoResult') }}</div>
    @endif
@endsection