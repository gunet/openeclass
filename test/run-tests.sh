#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

$DIR/../vendor/phpunit/phpunit/phpunit \
--bootstrap $DIR/../vendor/autoload.php \
--include-path $DIR/../ \
$DIR
