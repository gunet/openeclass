<body>
    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="../template/modern/css/bootstrap.min.css?v={{ CACHE_SUFFIX }}"/>
    <script type="text/javascript" src="../js/bootstrap.bundle.min.js?v={{ CACHE_SUFFIX }}"></script>
    <link href="../template/modern/css/font-Manrope/css/Manrope.css?v={{ CACHE_SUFFIX }}" rel="stylesheet"/>
    <link href="../template/modern/css/font-awesome-6.4.0/css/all.css?v={{ CACHE_SUFFIX }}" rel="stylesheet"/>

    <div class="d-flex flex-column min-vh-100 container-lg bg-light">
        <!-- BEGIN headerBlock -->
        <div class="header_container bg-light d-flex justify-content-center align-items-center">
            <div id="header_section" class="row">
                <div class="col-12 nav-container">
                    <a href='' class="navbar-brand">
                        <img style="margin-top: 25px; max-width: 350px;" class="img-responsive hidden-md hidden-lg ms-2" src="../resources/img/eclass-new-logo.svg" alt=''>
                    </a>
                </div>
            </div>
        </div>
        <div class="p-0">
            <div class="container-fluid main-container p-0 bg-light">
                <div class="row m-auto">
                    <div class="col-12 justify-content-center bg-light col_maincontent_active_Install">
                        <div class=" body_container p-lg-5 p-md-3 p-2">
                            <div id="main-content" class="col-12">
                                <div class="row row-main">
                                    <div class="col-md-12 add-gutter">
                                        <div class='col-sm-12 text-center'>
                                            <h3 class='mt-3'>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης <strong>Open eClass</strong> δεν λειτουργεί.</h3>
                                            <div class='col-12 col-md-10 m-auto d-block mt-3'>
                                                <div class='card panelCard px-lg-4 py-lg-3'>
                                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                                        <h4>Πιθανοί λόγοι:</h4>
                                                    </div>
                                                    <div class='card-body'>
                                                        <ul class='list-group list-group-flush'>
                                                            @if (isset($err_config))
                                                                <li class='list-group-item element text-start'>Το αρχείο ρυθμίσεων της πλατφόρμας δεν υπάρχει.
                                                                    <p><i class="fa-solid fa-hand-point-right"></i><small>
                                                                    Σε περίπτωση που χρησιμοποιείτε την πλατφόρμα <strong>για πρώτη</strong> φορά,
                                                                    επιλέξτε τον <a href='../install/'><b>Οδηγό Εγκατάστασης</b></a> για να ξεκινήσετε το πρόγραμμα εγκατάστασης.
                                                                    </small>
                                                                    </p>
                                                                </li>
                                                                <li class='list-group-item element text-start'>
                                                                    Το αρχείο ρυθμίσεων της πλατφόρμας δεν μπορεί να διαβαστεί.
                                                                    <p><i class="fa-solid fa-hand-point-right"></i> <small>Ελέγξτε τα δικαιώματα πρόσβασης</small></p>
                                                                </li>
                                                            @endif
                                                            @if (isset($err_db))
                                                                <li class='list-group-item element text-start'>
                                                                    <i class="fa-solid fa-hand-point-right"></i> Η βάση δεδομένων δεν λειτουργεί.
                                                                </li>
                                                                <li class='list-group-item element text-start'>
                                                                    <i class="fa-solid fa-hand-point-right"></i> Τα στοιχεία σύνδεσης δεν είναι σωστά.
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
