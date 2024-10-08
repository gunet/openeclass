<!-- BEGIN mainBlock -->
<!DOCTYPE HTML>
<html style="height: 100%;">
    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>{{ $pageTitle }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- jQuery -->
        <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="{{ $urlAppend }}/js/bootstrap.min.js"></script>

        <!-- BootBox -->
        <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js"></script>
        <script>
            bootbox.setDefaults({
                locale: "{{ $language }}"
            });
        </script>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="{{ $template_base }}/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ $template_base }}/css/sidebar.css"/>
        <link rel="stylesheet" href="{{ $template_base }}/css/default.css"/>

        <!-- Font Awesome - A font of icons -->
        <link href="{{ $template_base }}/css/font-awesome-6.4.0/css/font-awesome.css" rel="stylesheet">

        {!! $head_content !!}

    </head>
    <body style="height: 100%;">
        <div class='container'>
            <div class='row'>
                <div class='col-xs-12 text-center'>
                    <div style='padding-top: 10px; padding-bottom: 10px;'>
                        <img style="margin-top: 25px; max-width: 350px;" class="img-responsive hidden-md hidden-lg ms-2" src="../../resources/img/eclass-new-logo.svg" alt=''>
                    </div>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='alert alert-warning'>
                                {!! $tool_content !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

