<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$langcode = $session->language;
$maintenance_text = get_config('maintenance_text_' . $langcode);

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">



    <!-- <link href='https://fonts.googleapis.com/css?family=Work+Sans:400,300,600,400italic,700' rel='stylesheet' type='text/css'> -->
    <link href="https://fonts.googleapis.com/css?family=Space+Mono" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="css/animate.css">
    <!-- Icomoon Icon Fonts-->
    <link rel="stylesheet" href="css/icomoon.css">
    <!-- Bootstrap  -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <!-- Theme style  -->
    <link rel="stylesheet" href="css/style.css">



</head>
<body>



<div id="page">

    <div id="fh5co-container" class="js-fullheight">
        <div class="countdown-wrap js-fullheight">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="display-t js-fullheight">
                        <div class="display-tc animate-box">
                            <img style="padding-bottom: 20px;" width="400px" src="images/20-years-openclass-white.png">
                            <h1>η πλατφόρμα είναι σε λειτουργία συντήρησης...</h1>
                            <h2>Ευχαριστούμε για την κατανόηση</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-cover" style="background-image:url(images/background.jpg);">

        </div>
    </div>
</div>



<!-- jQuery -->
<script src="js/jquery.min.js"></script>
<!-- jQuery Easing -->
<script src="js/jquery.easing.1.3.js"></script>
<!-- Bootstrap -->
<script src="js/bootstrap.min.js"></script>
<!-- Waypoints -->
<script src="js/jquery.waypoints.min.js"></script>


<!-- Main -->
<script src="js/main.js"></script>



</body>
</html>

