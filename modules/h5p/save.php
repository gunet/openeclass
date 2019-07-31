<?php

require_once 'test.php';

echo "this is save.php";


if(upload_content()){
	header("location: index.php");
}

?>