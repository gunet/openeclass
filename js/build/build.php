<?php

chdir(get_base_path());
require_once 'include/main_lib.php';

$webDir = '.';

$command_line = (php_sapi_name() == 'cli' && !isset($_SERVER['REMOTE_ADDR']));
if (!$command_line) {
    exit;
}

if (exec('bun build js/build/uppy.js --outdir js/bundle --minify', $output) === false) {
    die("Unable to execute bun:\n\n" . implode("\n", $output));
}
if (file_exists('js/bundle/uppy.min.css')) {
    unlink('js/bundle/uppy.min.css');
}
file_put_contents('js/bundle/uppy.min.css',
    implode("\n", array_map(function ($file) {
        return file_get_contents($file);
    }, glob('node_modules/@uppy/*/dist/*.min.css'))) . "\n");

if (!is_dir('js/recordrtc')) {
    mkdir('js/recordrtc');
}
copy('node_modules/recordrtc/RecordRTC.min.js', 'js/recordrtc/RecordRTC.min.js');

removeDir('js/h5p-standalone');
mkdir('js/h5p-standalone');
foreach(['frame.bundle.js', 'main.bundle.js', 'fonts', 'images', 'styles'] as $file) {
    $path = 'node_modules/h5p-standalone/dist/' . $file;
    $dest = 'js/h5p-standalone/' . $file;
    if (is_dir($path)) {
        recurse_copy($path, $dest);
    } else {
        copy($path, $dest);
    }
}

removeDir('js/mathjax');
mkdir('js/mathjax');
recurse_copy('node_modules/mathjax', 'js/mathjax');

removeDir('js/video.js');
mkdir('js/video.js');
foreach(['video.min.js', 'video-js.min.css', 'font', 'lang'] as $file) {
    $path = 'node_modules/video.js/dist/' . $file;
    $dest = 'js/video.js/' . $file;
    if (is_dir($path)) {
        recurse_copy($path, $dest);
    } else {
        copy($path, $dest);
    }
}

function get_base_path() {
    $path = dirname(dirname(dirname(__FILE__)));
    if (DIRECTORY_SEPARATOR !== '/') {
        return(str_replace(DIRECTORY_SEPARATOR, '/', $path));
    } else {
        return $path;
    }
}
