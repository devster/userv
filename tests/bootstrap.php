<?php

require __DIR__.'/../vendor/autoload.php';

function detached_process(Closure $callback)
{
    $pid = pcntl_fork();
    switch($pid) {
        // fork errror
        case -1 :
            return false;
        break;

        // this code runs in child process
        case 0 :
            // obtain a new process group
            posix_setsid();
            $callback();
            return;
        break;

        // return the child pid in father
        default:
            return $pid;
        break;
    }
}

function kill_process($pid)
{
    posix_kill($pid, SIGINT);
    pcntl_waitpid($pid, $status);

    return $status;
}