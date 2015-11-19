<?php

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERROR 404</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="/template/default/CSS/bootstrap-custom.css">

    <!-- Font Awesome - A font of icons -->
    <link href="/template/default/CSS/font-awesome-4.2.0/css/font-awesome.css" rel="stylesheet">
    <style>
#error404 {
padding-top: 100px;
            text-align: center;
        }

        .error404_icon {
    color: #dddddd;
    font-size: 210px;
            padding-bottom: 10px;
        }

        #error404_line1 {
            color: #dddddd;
            font-size: 23px;
        }

        #error404_line2 {
            font-size: 30px;
            padding-bottom: 30px;
            padding-top: 10px;
        }

        #error404 .btn {
            padding: 15px 25px;
        }
    </style>
</head>
<body>
<div id="error404">
    <div class="fa fa-maps-sign error404_icon"></div>

    <div id="error404_line1">ERROR 404</div>

    <div id="error404_line2">Αυτό που ψάχνεις δεν βρίσκεται εδώ!</div>

    <a class="btn btn-primary" href="/" data-placement="bottom" title="">
        <span class="fa fa-arrow-left space-after-icon"></span>
        <span class="hidden-xs">Επιστροφή στην αρχική σελίδα</span>
    </a>


</div>
</body>
</html>';