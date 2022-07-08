@extends('layouts.default')

@section('content')

<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>

<script type="text/javascript" src="{{ $urlAppend }}template/modern/js/my_courses_color_header.js"></script>

<div class="pb-5 mobile_width">

    <div class="container-fluid my_course_info_container" style="width:95%;">

        <div class="row">

                <div id="background-cheat-leftnav" class="col-xxl-2 col-xl-2 col-lg-4 col-md-0 col-sm-12 col-xs-12 col_sidebar_active" >

                    @include('layouts.partials.sidebar')

                </div>


                <div class="col-xxl-10 col-xl-10 col-lg-8 col-md-12 col-sm-12 col-xs-12 col_maincontent_active">

                        <div class="container-fluid container_courses_active">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col_info_courses">

                                    <div class="row mb-3 user-details justify-content-center">


                                        <div class="col-md-12 py-5">

                                              <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                                                    <button type="button" id="menu-btn" class="btn btn-primary menu_btn_button">
                                                        <i class="fas fa-align-left"></i>
                                                        <span></span>
                                                    </button>
                                                </nav>


                                                <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                                                    <ol class="breadcrumb">
                                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">Χαρτοφυλάκιο</a></li>
                                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">Τα μαθήματά μου</a></li>
                                                        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
                                                    </ol>
                                                    @if($is_editor == 1)
                                                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,$coursePrivateCode => $coursePrivateCode])
                                                    @endif

                                                </nav>

                                            <?php

                                                $course_code = Database::get()->querySingle("SELECT code,prof_names FROM `course` WHERE `course`.`title`='{$title}'; ");

                                            ?>

                                            <h3>{{$title}} ({{$course_code->code}})</h3>
                                            <p>{{$course_code->prof_names}}</p>
                                            <div class="d-flex col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 my-courses-diastimata border-bottom border-secondary">
                                                <i class="fas fa-star text-primary"></i>
                                                <i class="fas fa-star text-primary"></i>
                                                <i class="fas fa-star text-primary"></i>
                                                <i class="fas fa-star text-primary"></i>
                                                <i class="fas fa-star text-secondary"> </i>
                                                <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-10 col-sm-6 col-6">
                                                    <span class="text-secondary me-4">(9 αξιολογήσεις)</span>
                                                    <i class="fas fa-users text-primary"> </i>
                                                    <span class="text-secondary me-4">(12 εγγεγραμμένοι)</span>
                                                    <i class="fas fa-message text-primary"> </i>
                                                    <span class="text-secondary me-4">(12 σχόλια)</span>
                                                </div>
                                            </div>

                                            @if($is_editor == 1)
                                                <div class="d-flex flex-row my-courses-diastimata">
                                                    <i class="fas fa-pen text-primary me-2"></i> <a class="processing_course_a" href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}&editor={{$is_editor}}">ΕΠΕΞΕΡΓΑΣΙΑ ΜΑΘΗΜΑΤΟΣ</a>
                                                    <i class="fas fa-gear text-primary me-2"></i><a href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}"><i class="fas fa-cogs" aria-hidden="true"></i> Ρυθμίσεις</a>
                                                </div>
                                            @endif

                                            @if(!empty($courseImage))
                                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                    <figure>
                                                        <picture>
                                                            <img class="uploadImageCourse" src='{{$urlAppend}}courses/{{$coursePrivateCode}}/image/{{$courseImage}}'>
                                                        </picture>
                                                    </figure>

                                                </div>
                                            @else
                                                <div class="row p-2"></div>
                                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 uploadImageCourseCol">
                                                    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-6 NouploadImageCourseCol"></div>
                                                </div>
                                            @endif


                                        </div>
                                        <div class="col-md-12 pb-5 col_description_course">
                                            <div class="d-flex justify-content-between ">

                                                <p class="my-courses-maindesc">Το Διαδικτυακό Ραδιόφωνο(Web radio) είναι η συνεχής μετάδοση ήχου(audio,streaming audio) μέσου του Διαδικτύου(Internet) στον υπολογιστή σας ή κινητό.Αυτή η τεχνική της μετάδοσης
                                                    αρχείων ήχου,χρησιμοποιώντας μετάδοση δεδομένων είναι παρόμοια με το επίγειο ραδιόφωνο αλλά το βασικό του πλεονέκτημα είναι οτι μπορεί να ακουστεί σε οποιοδποτε γεωγραφικό σημείο
                                                    της γής.</p>

                                                <p class="my-courses-maindesc">Μια σειρά απο υπηρεσίες Web και ελεύθερο λογισμικό επιτρέπουν στους χρήστες να δημιουργήσουν το δικό τους κόστος.Οι διαδικτυακοί ραδιοφωνικοί σταθμοί είναι παρομοιοι με τους
                                                    κανονικούς,επιτρέποντας σε ένα άτομο ή οργανισμό να μεταδώσει ζωντανά μουσική μέσω του διαδικτύου.Οι διαδικτυακοί ραδιοφωνικοί σταθμοί μπορεί να είναι μια ταυτόχρονη ροή
                                                    ενός κανονικού ραδιοφωνικού σταθμού,ένας ερασιτέχνης που μεταδίδει το δικό του σταθμό ή εμπορικοί διαδικτυακοί σταθμοί με παραγωγούς ζωντανό πρόγραμμα και διαφημίσεις.
                                                </p>
                                            </div>

                                            <div>


                                                <div class="border-bottom border-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                                    <span class="text-primary">

                                                        ΠΛΗΡΟΦΟΡΙΕΣ ΜΑΘΗΜΑΤΟΣ
                                                    </span>
                                                    <i class="ms auto fas fa-angle-down text-primary"></i>
                                                </div>

                                                <div class="collapse" id="collapseExample">
                                                    <div class="card card-body">
                                                        Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="row p-2"style="background-color:#f5f5f5; margin-left:-30px;"></div >
                                <div class="row p-2"style="background-color:#f5f5f5; margin-left:30px; margin-top:-16px;"></div >

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col_units_course">

                                        <div class="row mb-3 user-details justify-content-center">
                                            <div class="col-md-12 py-2">

                                                @if($is_editor == true)
                                                    <a class="add_unit_btn" href="/">Προσθήκη ενότητας</a></br><br>
                                                @endif
                                                <b>Ενότητες μαθήματος(12)</b>
                                                <div class="col-md-12 py-5 my-courses-block">



                                                    <h5 class="text-primary">
                                                        Δημιουργία διαδικτυακής ραδιοφωνικής εκπομπής με τα Shoutcast και Winamp εργαλεία
                                                    </h5>

                                                    <p>Η υποενότητα περιγράφει την χρήση των εργαλείων όπως το Winamp και SHOUTcast ως βασικά εργαλεία για την υλοποίηση
                                                        ενός διαδικτυακού ραδιοφώνου
                                                    </p>

                                                    <a class="key_words_duration_a" href="#">ΛΕΞΕΙΣ-ΚΛΕΙΔΙΑ</a>
                                                    <p class="text-start">ραδιόφωνο στο Διαδίκτυο,το Winamp,SHOUTcast</p>
                                                    <a class="key_words_duration_a" href="#">Διάρκεια</a>

                                                    <p>2 ώρες</p>
                                                    <div class="d-flex border-bottom border-secondary my-courses-diastimata">
                                                        <i class="fas fa-up-down-left-right text-primary"></i>
                                                        <i class="fas fa-eye-slash text-secondary"></i>
                                                        <i class="fas fa-pen text-primary"></i>
                                                        <i class="ms-auto fas fa-trash text-warning"></i> </div>
                                                    <h5 class="text-primary my-courses-diastimata">Εισαγωγή στο Διαδικτυακό Ραδιόφωνο και τηλεόραση</h5>

                                                    <p>Μια σειρά απο υπηρεσίες Web και ελεύθερο λογισμικό επιτρέπουν στους χρήστες να δημιουργήσουν το δικό τους διαδικτυακό ραδιόφωνο ή τηλεοπτικό
                                                        σταθμό με παγκόσμια εμβέλεια και πολύ χαμηλό κόστος.
                                                    </p>

                                                    <a class="key_words_duration_a" href="#">ΛΕΞΕΙΣ-ΚΛΕΙΔΙΑ</a>
                                                    <p>Διαδικτυακό ραδιόφωνο,τηλεόραση,Διαδίκτυο</p>

                                                    <a class="key_words_duration_a" href="#">Διάρκεια</a>
                                                    <p>1 ώρα</p>
                                                    <div class="d-flex border-bottom border-secondary my-courses-diastimata">
                                                        <i class="fas fa-arrows-up-down-left-right text-primary"></i>
                                                        <i class="fas fa-eye-slash text-secondary"></i>
                                                        <i class="fas fa-pen text-primary"></i>
                                                        <i class="ms-auto fas fa-trash text-warning"></i></div>



                                                    <h5 class="text-primary my-courses-diastimata">Θέματα Internet/Web TV</h5>


                                                    <p>Σε αυτή την υποενότητα,η ιδέα του Internet ή Web TV.Περιγράψτε τα βασικά χαρακτηριστικά αυτών των συστημάτων και των υποκειμένων αρχιτεκτονικές και τεχνολογίες που εφαρμόζονται.

                                                    </p>

                                                    <a class="key_words_duration_a" href="#">ΛΕΞΕΙΣ-ΚΛΕΙΔΙΑ</a>
                                                    <p>τηλεόραση μέσω του Διαδικτύου,Video on Demand,Internet Video</p>

                                                    <a class="key_words_duration_a" href="#">Διάρκεια</a>
                                                    <p>1 ώρα</p>
                                                    <div class="d-flex border-bottom border-secondary my-courses-diastimata">
                                                        <i class="fas fa-up-down-left-right text-primary"></i>
                                                        <i class="fas fa-eye-slash text-secondary"></i>
                                                        <i class="fas fa-pen text-primary"></i>
                                                        <i class="ms-auto fas fa-trash text-warning"></i>
                                                    </div>

                                                    <div class="text-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample1" aria-expanded="false" aria-controls="collapseExample">
                                                        <p class="text-end text-primary">
                                                            ΟΛΕΣ ΟΙ ΕΝΟΤΗΤΕΣ
                                                            <i class="ms auto fas fa-angle-down text-primary"></i>
                                                        </p>

                                                    </div>

                                                    <div class="collapse" id="collapseExample1">
                                                        <div class="card card-body">
                                                            Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
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



@endsection
