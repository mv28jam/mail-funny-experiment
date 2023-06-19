<?php
/**
 * Load control functions
 */

/**
 * Count processes with our marker
 * @return int
 */
function getMyProcessCount(): int
{
    return (int)shell_exec('ps aux | grep '.PROC_MARKER.' | wc -l');
}

/**
 * Get sum of used memory with our marker by ps
 * php memory_get_usage() show memory allocated by script except php itself and libs = not real
 * @return int
 */
function getMyProcessMemUsage() : int
{
    $control = 0;
    //min value > 0
    // we grep our grep with our marker
    foreach (explode(PHP_EOL, shell_exec('ps aux | grep '.PROC_MARKER)) as $val){
        $tmp = array_values(array_filter(explode(' ', $val)));
        if(!empty($tmp[5])) $control += $tmp[5];
    }
    return intval($control / 1024);
}

/**
 * Check my pid with my marker exists
 * @param $pid
 * @return int
 */
function checkMyPid($pid): int
{
    return (int)shell_exec('ps aux -p '.$pid.' | grep '.PROC_MARKER.' | wc -l');
}

/**
 * Check for load params
 * - memory
 * - la
 * - count of processes
 * sleep if overlooad
 * 16 is not calculated scale
 * @param int $i
 * @param callable $log
 * @return void
 */
function loadWait(int $i, callable $log): void
{
    //
    if($i % 16 == 0 and (!laControl($log) or !memoryControl($log) or !pidCapturedControl($log))){
        user_error('Can not perform mail sending by this server with current params', E_USER_ERROR);
    }
}

/**
 * Check memory usage by process of mail sending
 * and sleep for 2 sec if more than configurated
 * @param callable $log
 * @return bool
 */
function memoryControl(Callable $log) : bool
{
    $control = 0;
    $to_sleep = 2;
    //
    while (getMyProcessMemUsage() >= MEMORY_USE_MB) {
        if($control > WAIT_LIMIT_SEC/2) $log('Long memory free waiting was.');
        sleep($to_sleep);
        $control += $to_sleep;
        if ($control > WAIT_LIMIT_SEC) {
            $log('Memory usage is too hi for '.WAIT_LIMIT_SEC.' seconds.');
            return false;
        }
    }
    //
    return true;

}

/**
 * Check for LA overload
 * Sleep for 2 sec if more than configurated
 * @param callable $log
 * @return bool
 */
function laControl(Callable $log): bool
{
    $control = 0;
    $to_sleep = 2;
    //
    while (sys_getloadavg()[0] >= LA_CUT) {
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
 * Check for processes count of mailing - too many is bad
 * Sleep for 2 sec if more than configurated
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
