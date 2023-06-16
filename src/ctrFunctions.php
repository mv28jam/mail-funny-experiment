<?php
/**
 * Load control functions
 */

/**
 * @return int
 */
function getMyProcessCount(): int
{
    return (int)shell_exec('ps aux | grep '.PROC_MARKER.' | wc -l');
}

/**
 * @param $pid
 * @return int
 */
function checkMyPid($pid): int
{
    return (int)shell_exec('ps aux -p '.$pid.' | grep '.PROC_MARKER.' | wc -l');
}

/**
 * @param int $i
 * @param callable $log
 * @return void
 */
function loadWait(int $i, callable $log)
{
    //
    if($i % 500 == 0 and (!laControl($log) or !pidCapturedControl($log))){
        user_error('Can not perform mail sending by this server with current params', E_USER_ERROR);
    }
}


/**
 *
 * @param callable $log
 * @return bool
 */
function laControl(Callable $log): bool
{
    $control = 0;
    $to_sleep = 2;
    //
    while (sys_getloadavg()[0] >= LA_CUT) {
        echo '.';
        if($control > WAIT_LIMIT_SEC/2) $log('Long LA cut waiting was.');
        sleep($to_sleep);
        $control += $to_sleep;
        if ($control > WAIT_LIMIT_SEC) {
            $log('Minute la is too hi for '.WAIT_LIMIT_SEC.' seconds.');
            return false;
        }
    }
    //
    return true;
}
/**
 *
 * @param callable $log
 * @return bool
 */
function pidCapturedControl(Callable $log): bool
{
    $control = 0;
    $to_sleep = 2;
    $pid_captured = getMyProcessCount();
    //
    while($pid_captured >= PID_CUT){
        echo '!';
        if($control > WAIT_LIMIT_SEC/2) $log('Long wait of lowering PID cut limit was.');
        sleep($to_sleep);
        $control += $to_sleep;
        $pid_captured = getMyProcessCount();
        if($control > WAIT_LIMIT_SEC){
            $log('Percent of mail process is too hi for '.WAIT_LIMIT_SEC.' seconds.');
            return false;
        }
    };
    //
    return true;
}
