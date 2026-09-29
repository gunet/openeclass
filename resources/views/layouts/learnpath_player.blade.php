<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={{ $charset }}">
    <title>{{ $pageTitle }}</title>

    <script type="text/javascript" src="{{ $urlAppend }}js/jquery{{ $jquery_version }}.min.js"></script>
    <script src="{{ $urlAppend }}js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?v={{ $cache_suffix }}">
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-6.4.0/css/all.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css?v={{ $cache_suffix }}">
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css?v={{ $cache_suffix }}">
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/fonts_all/typography.css?v={{ $cache_suffix }}">
    @if ($theme_id > 0)
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}courses/theme_data/{{ $theme_id }}/style_str.css?v={{ $cache_suffix }}">
    @endif

    @stack('head_styles')
</head>
<body class="lp-fullscreen">

@yield('content')

@stack('body_scripts')
</body>
</html>
