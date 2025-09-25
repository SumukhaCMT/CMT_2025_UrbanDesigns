<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set a log file for errors
$logFile = 'error_log.txt';
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
