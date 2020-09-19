@echo off

setlocal

if not defined PHP_HOME (
  echo Error: PHP_HOME is not set.
  goto :eof
)

set PHP_HOME=%PHP_HOME:"=%

if not exist "%PHP_HOME%"\php.exe (
  echo Error: PHP_HOME is incorrectly set: %PHP_HOME%
  echo Expected to find php.exe here: %PHP_HOME%\php.exe
  goto :eof
)

call "%PHP_HOME%\php.exe" crond.php

endlocal