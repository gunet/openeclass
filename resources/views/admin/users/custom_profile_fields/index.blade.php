@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($result)
        <!--container for sorting-->
        <div id='multi'>
        
        @foreach ($result as $res)
            <div id='cat_{{ getIndirectReference($res->id) }}' class='table-responsive tile' style='margin-bottom:30px;'>
                <table class='table-default'>
                <caption class='tile__name'>
                    <strong>{{ trans('langCategory') }} :</strong> {{ $res->name }}
                    <div class='pull-right'>
                        {!! action_button(array(
                            array(
                                    'title' => trans('langCPFNewField'),
                                    'url' => "$_SERVER[SCRIPT_NAME]?add_field=" . getIndirectReference($res->id),
                                    'icon' => 'fa-plus-circle',
                                    'level' => 'primary'
                            ),
                            array('title' => trans('langModify'),
                                    'url' => "$_SERVER[SCRIPT_NAME]?edit_cat=" . getIndirectReference($res->id),
                                    'icon' => 'fa-edit',
                                    'level' => 'primary'
                            ),
                            array('title' => trans('langDelete'),
                                    'url' => "$_SERVER[SCRIPT_NAME]?del_cat=" . getIndirectReference($res->id),
                                    'icon' => 'fa-times',
                                    'class' => 'delete',
                                    'confirm' => trans('langCPFConfirmCatDelete')
                            )
                        )) !!}
            
                    </div>
                </caption>            
                <thead>
                    <tr class='list-header'>
                        <td>{{ trans('langName') }}</td>
                        <td>{{ trans('langCPFShortName') }}</td>
                        <td>{{ trans('langDescription') }}</td>
                        <td>{{ trans('langCPFFieldDatatype') }}</td>
                        <td>{{ trans('langCPFFieldRequired') }}</td>
                        <td>{{ trans('langCPFFieldRegistration') }}</td>
                        <td>{{ trans('langCPFFieldUserType') }}</td>
                        <td>{{ trans('langCPFFieldVisibility') }}</td>
                        <td>{!! icon('fa-gears') !!}</td>
                    </tr>
                </thead>
            
                @if (count($form_data_array[$res->id]))                
                    <tbody class='tile__list'>
                    @foreach ($form_data_array[$res->id] as $f)
                        <tr id='field_{{ getIndirectReference($f->id) }}'>
                            <td>{{ $f->name }}</td>
                            <td>{{ $f->shortname }}</td>
                            <td>{!! standard_text_escape($f->description) !!}</td>
                            <td>{{ $field_types[$f->datatype] }}</td>
                            <td>{{ $yes_no[$f->required] }}</td>
                            <td>{{ $yes_no[$f->registration] }}</td>
                            <td>{{ $user_type[$f->user_type] }}</td>
                            <td>{{ $visibility[$f->visibility] }}</td>
                            <td>
                                {!! action_button(array(
                                    array('title' => trans('langModify'),
                                          'url' => "$_SERVER[SCRIPT_NAME]?edit_field=" . getIndirectReference($f->id),
                                          'icon' => 'fa-edit',
                                    ),
                                    array('title' => trans('langDelete'),
                                          'url' => "$_SERVER[SCRIPT_NAME]?del_field=" . getIndirectReference($f->id),
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => trans('langCPFConfirmFieldDelete')
                                    )
                                )) !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                @else
                    <tbody class='tile__list'>
                        <tr class='ignore-item'>
                            <td colspan='9' class='text-center'>
                                <span class='not_visible'>{{ trans('langCPFNoFieldinCat') }}</span>
                            </td>
                        </tr>
                    </tbody>           
                @endif
            
                </table>
            </div>
        @endforeach
        </div>
        <form name='sortOrderForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            {!! generate_csrf_token_form_field() !!}
        </form>
        <script src='custom_profile_fields.js'></script>
    @else
        <div class='alert alert-warning'>{{ trans('langCPFNoCats') }}</div> 
    @endif
@endsection