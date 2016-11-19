<?php

class Log {

    public static function logToErrFile($err) {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $file = __DIR__ . '/' . $date . '.txt';

        $logTxt = '[' . $time . '] >> ' . $err;

        file_put_contents($file, $logTxt, FILE_APPEND | LOCK_EX);
    }

    public static function logToFile($filename, $msg) {
        if (CONFIG_DISABLE_LOGGING) {
            return;
        }
        
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $file = __DIR__ . '/' . $filename . '.txt';

        $logTxt = '[' . $date . ' ' . $time . '] >> ' . $msg . "\n";

        file_put_contents($file, $logTxt, FILE_APPEND | LOCK_EX);
    }

}
