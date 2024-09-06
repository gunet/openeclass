<body>
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
            <div class="col-12 nav-container pt-3 pb-0">
                <a href='' class="navbar-brand">
                    <img style="margin-top: 25px; max-width: 350px;" class="img-responsive hidden-md hidden-lg ms-2" src="../resources/img/eclass-new-logo.svg" alt=''>
                </a>
            </div>
        </div>
    </div>


    <div class="p-2">
        <div class="container-fluid main-container p-0 bg-light">
            <div class="row m-auto">
                <div class="body_container p-lg-5 p-md-3 p-2">
                    <div class='panel panel-info'>
                        <div class='panel-heading'>
                            <h3 class='mt-2'>
                                {{ trans('langInstallError') }}!
                            </h3>
                        </div>
                        <div class='panel-body'>
                            <ul class='list-group list-group-flush'>
                                <li class='list-group-item element text-start'>
                                    <i class="fa-solid fa-hand-point-right"></i> {{ trans('langWarnConfig1') }} {{ trans('langWarnConfig2') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
