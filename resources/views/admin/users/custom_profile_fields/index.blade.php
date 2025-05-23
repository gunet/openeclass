@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    @if ($result)
                        <!--container for sorting-->
                        <div class='col-12' id='multi'>

                        @foreach ($result as $res)
                            <div id='cat_{{ getIndirectReference($res->id) }}' class='table-responsive'>
                                <table class='table-default table-custom-profile'>
                                <caption class='tile__name ps-1 pe-1'>
                                    <strong>{{ trans('langCategory') }} :</strong> {{ $res->name }}
                                    <div class='float-end'>
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
                                                    'icon' => 'fa-xmark',
                                                    'class' => 'delete',
                                                    'confirm' => trans('langCPFConfirmCatDelete')
                                            )
                                        )) !!}

                                    </div>
                                </caption>
                                <thead>
                                    <tr class='list-header'>
                                        <td class='bg-header-table TextBold'>{{ trans('langName') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langCPFShortName') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langDescription') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langCPFFieldDatatype') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langCPFFieldRequired') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langCPFFieldRegistration') }}</td>
                                        <td class='bg-header-table TextBold'>{{ trans('langCPFFieldVisibility') }}</td>
                                        <td class='bg-header-table TextBold'></td>
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
                                            <td>{{ $visibility[$f->visibility] }}</td>
                                            <td class='text-end'>
                                                {!! action_button(array(
                                                    array('title' => trans('langModify'),
                                                        'url' => "$_SERVER[SCRIPT_NAME]?edit_field=" . getIndirectReference($f->id),
                                                        'icon' => 'fa-edit',
                                                    ),
                                                    array('title' => trans('langDelete'),
                                                        'url' => "$_SERVER[SCRIPT_NAME]?del_field=" . getIndirectReference($f->id),
                                                        'icon' => 'fa-xmark',
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
                                            <td colspan='9'>
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
                        <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langCPFNoCats') }}</span></div></div>
                    @endif

        </div>
</div>
</div>
@endsection
