@extends('layouts.default')

@section('content')

    {{--  utilize bootstrap-select for Add/Create dropdown button --}}
    {{-- override default bootstrap-select style because we want truly white color (alpha of 1 instead of default 0.5) --}}
    <link rel='stylesheet' href='{{ $urlAppend }}js/bootstrap-select/bootstrap-select.min.css'>
    <script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-select/bootstrap-select.min.js'></script>
    <style>
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:active {
            color: rgba(255, 255, 255, 1);
        }
    </style>
    <script type='text/javascript'>
		$(document).ready(function() {
			$('#createpicker').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
				window.location.href = '{{ $urlAppend }}modules/h5p/create.php?course={{ $course_code }}&library=' + $('#createpicker').val();
			});
		});
    </script>

@if($is_editor)
    {{-- custom action bar --}}
    <div class='row action_bar'>
        <div class='col-sm-12 clearfix'>
            <div class='margin-top-thin margin-bottom-fat pull-right'>
                {{-- Dropdown select for Creating H5P Content --}}
                @if ($h5pcontenttypes)
                    <div class='btn-group'>
                        <select id='createpicker' class='selectpicker' title='{{ trans('langCreate') }}' data-style='btn-primary' data-width='fit'>
                            <optgroup label='{{ trans('langH5pInteractiveContent') }}'>
                                @foreach ($h5pcontenttypes as $h5pcontenttype)
                                    @if ($h5pcontenttype->enabled)
                                        <?php
                                        $typeTitle = $h5pcontenttype->title;
                                        $typeVal = $h5pcontenttype->machine_name . " " . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                                        $typeFolder = $h5pcontenttype->machine_name . "-" . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                                        $typeIconPath = $webDir . "/courses/h5p/libraries/" . $typeFolder . "/icon.svg";
                                        $typeIconUrl = (file_exists($typeIconPath))
                                            ? $urlAppend . "courses/h5p/libraries/" . $typeFolder . "/icon.svg"  // expected icon
                                            : $urlAppend . "js/h5p-core/images/h5p_library.svg"; // fallback icon
                                        $dataContent = "data-content=\"<img src='$typeIconUrl' alt='$typeTitle' width='24px' height='24px'>$typeTitle\"";
                                        ?>
                                        <option {!! $dataContent !!}>{{ $typeVal }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                @endif
                <div class='btn-group'>
                    {{-- Update --}}
                    <a class='btn btn-success' href='update.php?course={{ $course_code }}' data-placement='bottom' data-toggle='tooltip'  title='{{ trans('langMaj') }}'>
                        <span class='fa fa-refresh space-after-icon'></span>
                        <span class='hidden-xs'>{{ trans('langMaj') }}</span>
                    </a>

                    {{-- Import --}}
                    <a class='btn btn-success' href='upload.php?course={{ $course_code }}' data-placement='bottom' data-toggle='tooltip'  title='{{ trans('langImport') }}'>
                        <span class='fa fa-upload space-after-icon'></span>
                        <span class='hidden-xs'>{{ trans('langImport') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($content)
    <table class="table-default">
        <thead>
            <tr class="list-header">
                <th class="text-left">H5P</th>
                <th class="text-center" style="width:109px;">
                    <span class="fa fa-gears"></span>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($content as $item)
                <tr>
                    <td>
                        <a href='view.php?course={{ $course_code }}&amp;id={{ $item->id }}'>{{ $item->title }}</a>
                    </td>
                    <td class='text-center'>
                        @if ($is_editor)
                            {!! action_button([
                                [ 'icon' => 'fa-times',
                                  'title' => trans('langDelete'),
                                  'url' => "delete.php?course=$course_code&amp;id=$item->id",
                                  'class' => 'delete',
                                  'confirm' => trans('langConfirmDelete') ]
                                ], false) !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class='alert alert-warning'>
        {{ trans('langNoH5PContent') }}
    </div>
@endif

@endsection