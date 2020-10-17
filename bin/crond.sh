#!/bin/bash

PHP_HOME=${PHP_HOME}

if [ -z PHP_HOME ]; then
    echo "Error: PHP_HOME is not set."
    exit
fi
echo "PHP_HOME: ${PHP_HOME}"

PHP_EXEC=${PHP_HOME}/bin/php
if [ -x PHP_EXEC ]; then
    echo "PHP_EXEC: ${PHP_EXEC}"
    nohup ${PHP_EXEC} crond.php > /dev/null 2>&1 &
else 
    echo "Error: PHP_HOME is incorrectly set: ${PHP_HOME}"
    echo "Expected to find php here: ${PHP_EXEC}"
fi