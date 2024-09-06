<body>
    <!-- jQuery -->
    <script type="text/javascript" src="../js/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="../template/modern/css/bootstrap.min.css?v=4.0-dev"/>
    <script type="text/javascript" src="../js/bootstrap.bundle.min.js"></script>
    <link href="../template/modern/css/font-Manrope/css/Manrope.css?v=4.0-dev" rel="stylesheet"/>
    <link href="../template/modern/css/font-awesome-6.4.0/css/all.css?v=4.0-dev" rel="stylesheet"/>
    <link rel="stylesheet" href="../template/modern/css/default.css">
    <!-- fav icons -->
    <link rel="shortcut icon" href="../resources/favicon/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" href="../resources/favicon/openeclass_128x128.png" />
    <link rel="icon" type="image/png" href="../resources/favicon/openeclass_128x128.png" />

    <div class="d-flex flex-column min-vh-100 container-lg bg-light">
        <div class="header_container bg-light d-flex justify-content-center align-items-center">
            <div id="header_section" class="row">
                <div class="col-12 nav-container pt-3 pb-4">
                    <a href='' class="navbar-brand">
                        <img style="margin-top: 25px; max-width: 350px;" class="img-responsive hidden-md hidden-lg ms-2" src="../resources/img/eclass-new-logo.svg" alt=''>
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid main-container p-0 bg-light">
            <div class="row m-auto">
                @if (isset($_SESSION['step']) and isset($StepTitle))
                    <nav role="navigation" class="col-12">
                        <ol class="breadcrumb">
                            <li><a href='#'>{{ trans('langStep') }} {{ $_SESSION['step'] }} {{ trans('langFrom2') }} 8</a>: </li>
                            <li class="ms-2 text-secondary">{{ $StepTitle }}</li>
                        </ol>
                    </nav>
                @endif
                <div class="col-12 justify-content-center bg-light col_maincontent_active_Install">
                    <div class="body_container p-lg-3 p-md-3 p-2">
                        <div id="Frame" class="row">
                            <div id="main-content" class="col-12 col-md-7 col-lg-8">
                                <div class="row row-main">

                                    @if (isset($_POST['install1']))
                                        @include('step_1')
                                    @elseif (isset($_POST['install2']))
                                        @include('step_2')
                                    @elseif (isset($_POST['install3']))
                                        @include('step_3')
                                    @elseif (isset($_POST['install4']))
                                        @include('step_4')
                                    @elseif (isset($_POST['install5']))
                                        @include('step_5')
                                    @elseif (isset($_POST['install6']))
                                        @include('step_6')
                                    @elseif (isset($_POST['install7']))
                                        @include('step_7')
                                    @elseif (isset($_POST['install8']))
                                        @include('step_8')
                                    @else
                                        <div class='col-sm-12 text-center'>
                                            <h3 class='mt-3'>
                                                {{ trans('langWelcomeWizard') }}
                                            </h3>
                                            <div class='col-12 col-md-6 m-auto d-block mt-3'>
                                                <div class='card panelCard px-lg-4 py-lg-3'>
                                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                                        <h3>
                                                            {{ trans('langThisWizard') }}
                                                        </h3>
                                                    </div>
                                                    <div class='card-body'>
                                                        <ul class='list-group list-group-flush'>
                                                            <li class='list-group-item element text-start'>
                                                                <i class="fa-solid fa-hand-point-right"></i>
                                                                {{ trans('langWizardHelp1') }}
                                                            </li>
                                                            <li class='list-group-item element text-start'>
                                                                <i class="fa-solid fa-hand-point-right"></i>
                                                                {{ trans('langWizardHelp2') }}
                                                            </li>
                                                            <li class='list-group-item element text-start'>
                                                                <i class="fa-solid fa-hand-point-right"></i>
                                                                {{ trans('langWizardHelp3') }} <em>config.php</em>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-12 col-md-6 m-auto d-block mt-3'>
                                                <div class='card panelCard px-lg-4 py-lg-3'>
                                                    <div class='card-body'>
                                                        <form class='form-horizontal form-wrapper' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                                            <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                                            <div class='form-group'>
                                                                <label for='lang' class='col-sm-12 control-label-notes text-start'>{{ trans('langChooseLang') }}:</label>
                                                                <div class='col-sm-12'>
                                                                    {!! $lang_selection !!}
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <div class='col-12'>
                                                                    <input type='submit' class='btn w-100' name='install1' value='{{ trans('langNextStep') }} &raquo;'>
                                                                    <input type='hidden' name='welcomeScreen' value='true'>
                                                                </div>
                                                            </div>
                                                            {!! hidden_vars($all_vars) !!}
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       @endif
                                </div>
                            </div>

                            @include('menu')

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
