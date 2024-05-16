<?php

use App\Utils\Redis\QueueAdapter;

$pid = pcntl_fork();

if($pid) exit();
posix_setsid();

require_once "vendor/autoload.php";

$adapter = new QueueAdapter();

while(true) {
    $msg = $adapter->consume();
    if(!empty($msg)) {
        switch($msg['action']) {
            case "bash":
                shell_exec($msg['content']);
                break;
            default: 
                break;
        }
    }
    sleep(5);
}