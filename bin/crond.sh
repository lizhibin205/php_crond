#!/bin/bash

PHP_HOME=${PHP_HOME}

if [ -z PHP_HOME ]; then
    echo "Error: PHP_HOME is not set."
    exit 1
fi
echo "PHP_HOME: ${PHP_HOME}."

PHP_EXEC=${PHP_HOME}/bin/php

if [ ! -x $PHP_EXEC ]; then
    echo "Error: PHP_HOME is incorrectly set: ${PHP_HOME}."
    echo "Expected to find php here: ${PHP_EXEC}."
    #exit 1
fi

PID_FILE=$(dirname $(pwd))/logs/crond.pid
echo "try to find pid at: ${PID_FILE}."
PID=0
if [ -r $PID_FILE ]; then
    PID=$(cat $PID_FILE)

fi
echo "find pid ${PID}."

START(){
    if [ $1 -gt 0 ]; then
        echo "php crond is running at pid $1, exit."
        return 1
    fi
    echo "starting php crond..."
    echo "nohup $PHP_EXEC crond.php > /dev/null 2>&1 &"
    nohup $PHP_EXEC crond.php > /dev/null 2>&1 &
    return 0
}

STOP(){
    if [ $1 -eq 0 ]; then
        echo "php crond is not running."
        return 1
    fi
    echo "kill -USR1 $1"
    kill -USR1 $1
    return 0
}

RELOAD(){
    if [ $1 -eq 0 ]; then
        echo "php crond is not running."
        return 1
    fi
    echo "kill -USR2 $1"
    kill -USR2 $1
    return 0
}

case $1 in
    start)
        START $PID
    ;;
    stop)
        STOP $PID
    ;;
    reload)
        RELOAD $PID
    ;;
    *)
    echo "usage crond.sh (start|stop|reload)."
    ;;
esac

