<?php
require_once 'include/baseTheme.php';

ignore_user_abort(true);
date_default_timezone_set('Europe/Athens');

$response = "response"; 
header("Connection: close");
header("Content-Length: " . mb_strlen($response));
header('Location: '. $urlServer);
echo $response;
custom_flush();

register_shutdown_function('cronjob');


function cronjob() {
  //$file = '/tmp/koko.txt';
  //file_put_contents($file, "run1: ". date('G:i:s') ."\n", FILE_APPEND);

  sleep(7);
  //for ($i=0; $i <= 100000000; $i++)
  //  $k=5+5;

  //file_put_contents($file, "run2: ". date('G:i:s') ."\n", FILE_APPEND);

}

function custom_flush() {
    echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){           
        @ob_flush();
        @flush();
        @ob_end_flush();
    }   
    @ob_start();
}
