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

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

			<div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

				    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                       
                        <a class="btn btn-primary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

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
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
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
