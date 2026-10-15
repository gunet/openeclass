@extends('layouts.default')

@section('content')

    <main id="main" class="col-12 main-section login-main-section">
        <div class='{{ $container }} main-container main-container-login'>

            @include('layouts.partials.show_alert')

            @if (Session::has('login_error'))
                <div class='modal show' id='warning-modal' tabindex='0'>
                    <div class='modal-dialog modal-dialog-centered'>
                        <div class='modal-content border-0 p-0'>
                            <div class='modal-header d-flex justify-content-between align-items-center'>
                                <h2 tabindex="0" class='modal-title' id='modal-title-id'>{{ trans('langError') }}</h2>
                                <button aria-label="{{ trans('langClose') }}" type='button' class='close' data-bs-dismiss='modal' id="closeBtn"></button>
                            </div>
                            <div class='modal-body'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{!! Session::get('login_error') !!}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @push('bottom_scripts')
                    <script type="text/javascript">
                        $(document).ready(function() {
                            var warningModalElement = document.getElementById('warning-modal');
                            var warningModal = new bootstrap.Modal(warningModalElement, {});
                            var focusTarget = document.getElementById('modal-title-id');
                            warningModal.show();
                            warningModalElement.addEventListener('shown.bs.modal', function () {
                                focusTarget.focus();
                            });
                            document.body.addEventListener('keydown', function(e) {
                                if (e.key === "Escape") {
                                    warningModal.hide();
                                }
                            });
                        });
                    </script>
                @endpush
            @endif

            <div class='row m-auto'>
                <h1>{{ trans('langUserLogin') }}</h1>
                <div class='padding-default mt-4'>
                    <div class='row row-cols-1 @if(count($authLink) > 0) row-cols-lg-2 @else row-cols-lg-1 @endif g-4 align-items-start'>
                        <div class='col {!! $Position !!}'>
                            @if($auth_enabled_method == 1)
                                @if (count($authLink) > 0)
                                    <div class='card form-homepage-login border-card px-lg-4 py-lg-3 p-3'>
                                        <div class='card-body p-1 p-md-2'>
                                            <div class='w-100'>

                                                {{-- 1η μέθοδος πιστοποίησης (πάντα επάνω, ανοιχτή, χωρίς accordion) --}}
                                                <div class='col-12 login-method-first'>
                                                    <div class='d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3'>
                                                        <h2 class='mb-0'>
                                                            {{ $authLink[0][2] }}
                                                        </h2>
                                                        @if (!empty($authLink[0][3])) {{-- Optional auth instructions --}}
                                                            <a href='#' class='text-decoration-underline' data-bs-toggle='modal' data-bs-target='#authInstruction0'>
                                                                {{ trans('langInstructionsAuth') }}
                                                            </a>
                                                            <div class='modal fade' id='authInstruction0' tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel0' aria-hidden='true'>
                                                                <div class='modal-dialog'>
                                                                    <div class='modal-content'>
                                                                        <div class='modal-header'>
                                                                            <h2 class='modal-title' id='authInstructionLabel0'>{{ trans('langInstructionsAuth') }}</h2>
                                                                            <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
                                                                        </div>
                                                                        <div class='modal-body'>
                                                                            <div class='col-12'>
                                                                                <div class='alert alert-info'>
                                                                                    <i class='fa-solid fa-circle-info fa-lg'></i>
                                                                                    <span>{{ $authLink[0][3] }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class='text-center w-100'>
                                                        {!! $authLink[0][1] !!}
                                                    </div>
                                                </div>

                                                {{-- Υπόλοιπες μέθοδοι πιστοποίησης σε accordion --}}
                                                @if (count($authLink) > 1)
                                                    <div class='col-12 my-3'>
                                                        <div id='or' class='ms-auto me-auto mb-2'>
                                                            {{ trans('langOr') }}
                                                        </div>
                                                    </div>

                                                    <div class='col-12'>
                                                        <div class='accordion' id='accordionLoginMethods'>
                                                            @foreach($authLink as $index => $authInfo)
                                                                @if (!$loop->first)
                                                                    <div class='accordion-item'>
                                                                        <h2 class='accordion-header' id='heading-{{ $index }}'>
                                                                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse-{{ $index }}' aria-expanded='false' aria-controls='collapse-{{ $index }}'>
                                                                                {{ $authInfo[2] }}
                                                                            </button>
                                                                        </h2>
                                                                        <div id='collapse-{{ $index }}' class='accordion-collapse collapse' aria-labelledby='heading-{{ $index }}' data-bs-parent='#accordionLoginMethods'>
                                                                            <div class='accordion-body'>
                                                                                @if (!empty($authInfo[3])) {{-- Optional auth instructions --}}
                                                                                    <div class='d-flex justify-content-end mb-2'>
                                                                                        <a href='#' class='text-decoration-underline' data-bs-toggle='modal' data-bs-target='#authInstruction{{ $index }}'>
                                                                                            {{ trans('langInstructionsAuth') }}
                                                                                        </a>
                                                                                    </div>
                                                                                    <div class='modal fade' id='authInstruction{{ $index }}' tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel{{ $index }}' aria-hidden='true'>
                                                                                        <div class='modal-dialog'>
                                                                                            <div class='modal-content'>
                                                                                                <div class='modal-header'>
                                                                                                    <h2 class='modal-title' id='authInstructionLabel{{ $index }}'>{{ trans('langInstructionsAuth') }}</h2>
                                                                                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
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
                                                                                <div class='text-center w-100'>
                                                                                    {!! $authInfo[1] !!}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class='col-12 mt-3'>
                                        <div class='alert alert-danger'>
                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                            <span>{{ trans('langAllAuthMethodsAreDisabled') }}</span>
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
                                                <span>{{ trans('langAllAuthMethodsAreDisabled') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if (count($authLink) > 0)
                            <div class='col card-login-img d-none {!! $PositionForm !!} align-self-start' role="img" aria-label="{{ trans('langLoginImg') }}" style="background-image: url('{{ $login_img }}'); background-position: center top !important; min-height: 400px;"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

<script type='text/javascript'>
    $(document).ready(function() {
        $('#revealPass').mousedown(function () {
            $('#password_id').attr('type', 'text');
        }).mouseup(function () {
            $('#password_id').attr('type', 'password');
        })
        $('#revealPass').keydown(function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                $('#password_id').attr('type', 'text');
            }
        });
    });
</script>

@endsection
