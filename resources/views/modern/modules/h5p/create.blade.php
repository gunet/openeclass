@extends('layouts.default')

@section('content')

<script type='text/javascript'>
    var H5PIntegration = {!! $h5pIntegrationObject !!};

    $(document).ready(function() {
        const editorwrapper = $('#h5p-editor-region');
        const editor = $('.h5p-editor');
        const mform = editor.closest('form');
        const editorupload = $('h5p-editor-upload');
        const h5plibrary = $('input[name=\"h5plibrary\"]');
        const h5pparams = $('input[name=\"h5pparams\"]');
        const inputname = $('input[name=\"name\"]');
        const h5paction = $('input[name=\"h5paction\"]');

        // Cancel validation and submission of form if clicking cancel button.
        const cancelSubmitCallback = function(button) {
            return button.is('[name=\"cancel\"]');
        };

        h5paction.val('create');

        H5PEditor.init(
            mform,
            h5paction,
            editorupload,
            editorwrapper,
            editor,
            h5plibrary,
            h5pparams,
            '',
            inputname,
            cancelSubmitCallback
        );
        document.querySelector('#h5p-editor-region iframe').setAttribute('name', 'h5p-editor');
    });
</script>

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

			<div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


					@include('layouts.partials.legend_view')



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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                   

                    {{-- h5p editor form --}}
                    
                    <div class='col-12 mt-4'>
                        <form id='coolh5peditor' autocomplete='off' action='{{ $urlAppend }}modules/h5p/create.php?course={{ $course_code }}' method='post' accept-charset='utf-8' class='mform'>
                            <div class='d-none'>
                                <input name='library' type='hidden' value='{{ $library }}' />
                                <input name='h5plibrary' type='hidden' value='{{ $library }}' />
                                <input name='h5pparams' type='hidden' value='{{ $h5pparams }}' />
                                <input name='h5paction' type='hidden' value='' />
                                <input name='id' type='hidden' value='{{ $id }}' />
                                <input name='h5pcorecommonpath' type='hidden' value='{{ $h5pcorecommonpath }}' />
                            </div>

                            <div class='h5p-editor-wrapper' id='h5p-editor-region'>
                                <div class='h5p-editor'>
                                    <span class='loading-icon icon-no-margin'><i class='icon fa fa-circle-o-notch fa-spin fa-fw' title="{{trans('langLoading')}}" aria-label="{{trans('langLoading')}}"></i></span>
                                </div>
                            </div>

                            <div class='h5p-editor-upload'></div>

                            <div class='mt-4 float-end'>
                               {!! $formActionButtons !!}
                            </div>

                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    
</div>
</div>

@endsection
