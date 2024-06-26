@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class='row m-auto'>
                <h1>{{ trans('langUserLogin') }}</h1>
                <div class='padding-default mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-4'>
                        <div class='col {!! $Position !!}'>
                            @if($auth_enabled_method == 1)
                                @if (count($authLink) > 0)
                                <div class='card form-homepage-login border-card h-100 px-lg-4 py-lg-3 p-3'>
                                    <div class='card-body d-flex justify-content-center align-items-center'>
                                        <div class='w-100 h-100'>
                                            <div class='col-12 container-pages d-flex align-items-center h-100'>

                                                @foreach($authLink as $authInfo)

                                                    @if ($loop->first)
                                                        <div class='col-12 page slide-page h-100'>
                                                    @else
                                                        <div class='col-12 page next-page-{{ $loop->iteration-1 }} h-100'>
                                                    @endif

                                                    <div class='row h-100'>
                                                        <div class='col-12 align-self-start'>
                                                            <div class='d-flex justify-content-between align-items-center flex-wrap gap-2'>
                                                                <h2 class='mb-3'>
                                                                    {{ $authInfo[2] }} {{-- Auth method title --}}
                                                                </h2>
                                                                @if (!empty($authInfo[3])) {{-- Optional auth instructions --}}
                                                                    <a href='#' class='text-decoration-underline mb-3' data-bs-toggle='modal' data-bs-target='#authInstruction{{ $loop->index }}'>
                                                                        {{ trans('langInstructionsAuth') }}
                                                                    </a>
                                                                    <div class='modal fade' id='authInstruction{{ $loop->index }}' tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel' aria-hidden='true'>
                                                                        <div class='modal-dialog'>
                                                                            <div class='modal-content'>
                                                                                <div class='modal-header'>
                                                                                    <div class='modal-title' id='authInstructionLabel'>{{ trans('langInstructionsAuth') }}</div>
                                                                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                                </div>
                                                                                <div class='modal-body'>
                                                                                    <div class='col-12'>
                                                                                        <div class='alert alert-info'>
                                                                                            <i class='fa-solid fa-circle-info fa-lg'></i>
                                                                                            <span>{{ $authInfo[3] }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class='col-12 align-self-center'>
                                                            <div class='text-center'>{!! $authInfo[1] !!}</div>
                                                        </div>

                                                        <div class='col-12 align-self-end'>
                                                            @if (count($authLink) == 2)
                                                                <div id='or' class='ms-auto me-auto mb-2'>
                                                                    {{ trans('langOr') }}
                                                                </div>
                                                                <div class='d-flex justify-content-center align-items-center gap-3 flex-wrap'>
                                                                    @if ($loop->first)
                                                                        <button class='btn submitAdminBtn firstNext next'>
                                                                            <span class='TextBold'>{{ $authLink[1][2] }}</span>
                                                                    @else
                                                                        <button class='btn submitAdminBtn prev-{{ $loop->index }} next'>
                                                                            <span class='TextBold'>{{ $authLink[0][2] }}</span>
                                                                    @endif
                                                                    </button>
                                                                </div>
                                                            @elseif (count($authLink) > 2)
                                                                <div id='or' class='ms-auto me-auto mb-2'>
                                                                    {{ trans('langOr') }}
                                                                </div>
                                                                <div class='d-flex justify-content-md-between justify-content-center align-items-center gap-3 flex-wrap'>
                                                                    @if ($loop->first)
                                                                        <button class='btn submitAdminBtn firstNext next'>
                                                                            {!! $authLink[1][2] !!}
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-1 next'>
                                                                            {!! $authLink[2][2] !!}
                                                                        </button>
                                                                    @elseif ($loop->index == 1)
                                                                        <button class='btn submitAdminBtn prev-{{ $loop->index }} next'>
                                                                            {!! $authLink[$loop->index-1][2] !!}
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-{{ $loop->index+1 }} next'>
                                                                            {!! $authLink[$loop->index+1][2] !!}
                                                                        </button>
                                                                    @elseif ($loop->index == 2)
                                                                        <button class='btn submitAdminBtn prev-{{ $loop->index }} next'>
                                                                            {!! $authLink[$loop->index-1][2] !!}
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-{{ $loop->index+1 }} next'>
                                                                            {!! $authLink[$loop->index-2][2] !!}
                                                                        </button>
                                                                    @endif
                                                                    @if(count($authLink) > 3)
                                                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                                                            <div class='modal fade' id='LoginFormAnotherOption-{{ $loop->index }}' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='LoginFormAnotherOptionLabel-{{ $loop->index }}' aria-hidden='true'>
                                                                                <div class='modal-dialog'>
                                                                                    <div class='modal-content'>
                                                                                        <div class='modal-header'>
                                                                                            <div class='modal-title' id='LoginFormAnotherOptionLabel-{{ $loop->index }}'>
                                                                                                {{ $authLink[count($authLink)-1][2] }}
                                                                                            </div>
                                                                                            <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                                        </div>
                                                                                        <div class='modal-body d-flex justify-content-center align-items-center'>
                                                                                            <div>
                                                                                                {{ $authLink[count($authLink)-1][1] }}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (Session::has('login_error') and $authInfo[0])
                                                    <div class='col-12'>
                                                        <input id='showWarningModal2' type='hidden' value='1'>
                                                        <div class='modal fade' id='WarningModal2' aria-hidden='true' tabindex='-1' data-bs-backdrop='static' data-bs-keyboard='false'>
                                                            <div class='modal-dialog modal-dialog-centered'>
                                                                <div class='modal-content border-0 p-0'>
                                                                    <div class='modal-header d-flex justify-content-between align-items-center'>
                                                                        <div class='modal-title'>{{ trans('langError') }}</div>
                                                                        <button aria-label='Close' type='button' class='close close-error' data-bs-dismiss='modal'></button>
                                                                    </div>
                                                                    <div class='modal-body'>
                                                                        {!! Session::get('login_error') !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @else
                                <div class='card cardLogin h-100 p-3'>
                                    <div class='card-body py-1'>
                                        <h2>{{ trans('langUserLogin') }}</h2>
                                        <div class='col-12 mt-3'>
                                            <div class='alert alert-danger'>
                                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                {{ trans('langAllAuthMethodsAreDisabled') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class='col d-none {!! $PositionForm !!}'>
                            <div class='card h-100 border-0 p-0'>
                                <div class='card-body d-flex justify-content-center align-items-center p-0'>
                                    <img class='jumbotron-image-default {!! $class_login_img !!}' src='{!! $login_img !!}' alt='{{ trans('langLogin') }}'>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type='text/javascript'>
    $(document).ready(function() {
        if($('#showWarningModal2').val() == 1){
            var ModalWarning = document.getElementById('WarningModal2');
            var Modal_W = new bootstrap.Modal(ModalWarning);
            Modal_W.show();
        }
        $('.close-error').on('click',function(){
            window.location.reload();
        });
        $('#revealPass').mousedown(function () {
            $('#password_id').attr('type', 'text');
        }).mouseup(function () {
            $('#password_id').attr('type', 'password');
        })
    });
</script>

@endsection
