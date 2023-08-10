
@extends('layouts.default')

@section('content')

<link rel='stylesheet' href='{{ $urlAppend }}js/bootstrap-select/bootstrap-select.min.css'>
<script src="{{ $urlAppend }}js/bootstrap-select/bootstrap5-select.min.js"></script>

<script type='text/javascript'>
    $(document).ready(function() {
        $('#createpicker').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            window.location.href = '{{ $urlAppend }}modules/h5p/create.php?course={{ $course_code }}&library=' + $('#createpicker').val();
        });
    });
</script>

<style>
   .dropdown-menu.show{
      max-height:400px;
   }
</style>

<div class="col-12 main-section">
<div class='{{ $container }} py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

			<div class="col_maincontent_active">
                    
                <div class="row">

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

                    @if($is_editor)

                        
                        <div class='col-12'>
                            <div class='margin-top-thin margin-bottom-fat'>
                                {{-- Dropdown select for Creating H5P Content --}}
                                @if ($h5pcontenttypes)
                                    <div class='btn-group'>
                                        <select id='createpicker' class='selectpicker' title="{{ trans('langCreate') }}" data-style='btn-primary' data-width='fit'>
                                            
                                            <optgroup label="{{ trans('langH5pInteractiveContent') }}">
                                                <?php $counter = 0; ?>
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
                                                        @if($counter == 0)
                                                        <option selected>{{ trans('langCreate') }}</option>
                                                        @endif
                                                        <option {!! $dataContent !!}>{{ $typeVal }}</option>
                                                    @endif
                                                    <?php $counter++; ?>
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
                        
                    @endif

                    @if ($content)
                        <div class='col-12 mt-4'>
                            <div class='table-responsive'>
                                <table class="table-default">
                                    <thead>
                                        <tr class="list-header">
                                            <th>{{ trans('langH5pInteractiveContent') }}</th>
                                            <th>{{ trans('langAttendanceType') }} HP5</th>
                                            <th style="width:109px;">
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
                                                <td>
                                                    <img src='{{$typeIconUrl}}' alt='{{$h5p_content_type_title}}' width='50px' height='50px'> <em>{!! $h5p_content_type_title !!}</em>
                                                </td>
                                                <td class='text-end'>
                                                    @if ($is_editor)
                                                        {!! action_button([
                                                            [ 'icon' => 'fa-edit',
                                                            'title' => trans('langEditChange'),
                                                            'url' => "create.php?course=$course_code&amp;id=$item->id"
                                                            ],
                                                            [ 'icon' => 'fa-xmark',
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
                            </div>
                        </div>
                    @else
                        <div class='col-12 mt-5'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langNoH5PContent') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    
</div>
</div>

@endsection