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

    {!! $action_bar !!}

    {{-- h5p editor form --}}

        <div class='col-12'>
            <form id='coolh5peditor' autocomplete='off' action='{{ $urlAppend }}modules/h5p/create.php?course={{ $course_code }}' method='post' accept-charset='utf-8' class='mform'>
                <div style='display: none;'>
                    <input name='library' type='hidden' value='{{ $library }}' />
                    <input name='h5plibrary' type='hidden' value='{{ $library }}' />
                    <input name='h5pparams' type='hidden' value='{{ $h5pparams }}' />
                    <input name='h5paction' type='hidden' value='' />
                    <input name='id' type='hidden' value='{{ $id }}' />
                    <input name='h5pcorecommonpath' type='hidden' value='{{ $h5pcorecommonpath }}' />
                </div>

                {!! $formActionButtons !!}

                <div class='h5p-editor-wrapper' id='h5p-editor-region'>
                    <div class='h5p-editor'>
                        <span class='loading-icon icon-no-margin'><i class='icon fa fa-circle-o-notch fa-spin fa-fw' title='$langLoading' aria-label='$langLoading'></i></span>
                    </div>
                </div>

                <div class='h5p-editor-upload'></div>

                {!! $formActionButtons !!}

            </form>
        </div>


@endsection
