<?php
echo 'process a run...', PHP_EOL;
//sleep(10);
echo 'a finish.', PHP_EOL;
file_put_contents(__DIR__ .'/a.log', date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);
