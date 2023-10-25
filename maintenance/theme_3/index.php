<!DOCTYPE html>

    <head>
        <meta charset="utf-8">
        <!-- Bootstrap  -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" >
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js"></script>
        <!-- Theme style  -->
    </head>

    <body style="background-color: #009BDA;">

        <div style=" background: linear-gradient(180deg, rgba(95,0,181,1) 25%, rgba(0,155,218,1) 100%); height: 100vh; background-color: #009BDA;">

            <img class="img-fluid mx-auto d-block"  style="padding-bottom: 20px; padding-top: 100px; padding-left: 12%; padding-right: 12%;"  src="images/20-years-openclass-white.png">
            <img class="img-fluid mx-auto d-block"  style="padding-bottom: 20px; padding-top: 50px; padding-left: 20%; padding-right: 20%;" src="images/maintence3.png">
            <h1 style="text-align: center; color:#fff; padding-left: 4%; padding-right: 4%;">η πλατφόρμα είναι σε λειτουργία συντήρησης...</h1>
            <h2 style="text-align: center; color:#fff; padding-bottom: 20px;">Ευχαριστούμε για την κατανόηση</h2>

            <div style="text-align: center; color:#fff; padding-left: 20%; padding-right: 20%;">
                <?php echo $maintenance_text;?>
            </div>

        </div>

    </body>
</html>
