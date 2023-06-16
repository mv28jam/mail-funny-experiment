<?php

/**
 * Const ini block
 * define const for script
 */
define('TEST', (!empty($argv[1]) and $argv[1]==='test'));
//marker for mail sending process in ps
const PROC_MARKER = 'SenMailPrcS';
//processor quantity
define('PRC_COUNT', (int)shell_exec('grep -c processor /proc/cpuinfo'));
//max pid in system
define('PID_MAX', (int)shell_exec('cat /proc/sys/kernel/pid_max'));
//load average 1 max
const LA_PERCENT = 0.9;
//PID percent to capture
const PID_PERCENT = 0.001;
//la cut value
const LA_CUT = LA_PERCENT * PRC_COUNT;
//PID percent
define('PID_CUT', (int)(PID_MAX * PID_PERCENT));
//wait to load drop down before termination
const WAIT_LIMIT_SEC = 120;

/**
 * Require block
 */
require_once('./src/log.php');
require_once('./src/ctrFunctions.php');
require_once('./src/dbFunctions.php');
require_once('./test_support/getTestData.php');


/**
 * Exec ini block
 */
$log = dummyLog(...);
$pid = getmypid();
mkdir('./'.$pid);
$s_time = microtime(true);
$data = null;
if(TEST){
    $data = getTestData(isset($argv[2]) ? (int)$argv[2] : 10000);
}else{
    $data = getDataFromDB();
}
$count = count($data);

/**
 * @param array $data
 * @param callable $log
 * @param int $pid
 * @return array
 */
function checkActionsIter(array $data, callable $log, int $pid) : array
{
    $dir = './'.$pid.'/check';
    mkdir($dir);
    //
    for ($i = 0; $i < count($data); $i++) {
        //
        loadWait($i, $log);
        //
        if ($data[$i]['checked']) {
            if (exec('php ./src/separated/check.php '.$data[$i]['email'].' '.$dir.' '.PROC_MARKER.' > /dev/null 2> /dev/null &') === false) {
                user_error('Can not start check process', E_USER_ERROR);
            }
        }
    }
    //have to check process is live by pid, and process with pid is our process
    //to many actions for check, so set max action time in sleep
    //all closed process is fine, all in dir - with problem
    sleep(60);
    //
    return harvestResults($dir);
}

/**
 * @param array $data
 * @param callable $log
 * @param int $pid
 * @return array
 */
function sendActionsIter(array $data, callable $log, int $pid) : array
{
    $dir = './'.$pid.'/send';
    mkdir($dir);
    //
    for ($i = 0; $i<count($data); $i++){
        //
        loadWait($i, $log);
        //
        if(exec('php ./src/separated/send.php '.$data[$i]['email'].' '.$dir.' '.$data[$i]['username'].' '.PROC_MARKER.' > /dev/null 2> /dev/null &') === false){
            user_error('Can not start check process', E_USER_ERROR);
        }
    }
    //see comment line 69
    sleep(10);
    //
    return harvestResults($dir);
}

/**
 * @param string $dir
 * @return array
 */
function harvestResults(string $dir): array
{
    $res = [];
    //
    foreach (scandir($dir) as $file){
        if(is_file($dir.'/'.$file)){
            $res[] = file_get_contents($dir.'/'.$file);
            unlink($dir.'/'.$file);
        }
    }
    rmdir($dir);
    //
    return $res;
}

/**
 * @param array $data
 * @param array $bad_mails
 * @return array
 */
function newListForm(array $data, array $bad_mails): array
{
    //flip array to avoid cycle comparison
    $bad_mails = array_flip($bad_mails);
    $send = [];
    foreach ($data as $val){
        if(!isset($bad_mails[$val['email']]))$send[] = $val;
    }
    return $send;
}

/**
 * actions
 */
$bad_mails = checkActionsIter($data, $log, $pid);
//set bad mails in db
if(!TEST){
    setNotValidForMails($bad_mails);
}
//
$fail_sends = sendActionsIter(newListForm($data, $bad_mails), $log, $pid);
//
echo 'Sent '.$count.' in '.gmdate("H:i:s",(microtime(true) - $s_time)).' Bad '.count($bad_mails).' Failed '.count($fail_sends)."\n";