<?php

$dateTime = date('Y-m-d H:i:s');

// Prepare the error log message
$errorMessage = sprintf(
    "%s [%d] - %s in %s:%d",
    $dateTime,
    $e->getCode(),
    $e->getMessage(),
    $e->getFile(),
    $e->getLine()
) . PHP_EOL;
error_log($errorMessage, 3, '../error.log');
