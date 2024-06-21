<?php
require '../vendor/autoload.php';

$viewsDir = '../resources/views/modern/install';
$cacheDir = '../storage/views/';

use Jenssegers\Blade\Blade;
$blade = new Blade($viewsDir, $cacheDir);

if (isset($_GET['err_config'])) {
    $data['err_config'] = true;
} else if (isset($_GET['err_db'])) {
    $data['err_db'] = true;
} else {
    exit;
}
echo $blade->make('not_installed', $data)->render();
