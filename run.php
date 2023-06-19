<?php

/**
 * Const ini block
 * define const for script
 */
define('TEST', (!empty($argv[1]) and $argv[1]==='test'));
//marker for mail sending process in ps
const PROC_MARKER = 'SenMailPrcS';
//wait to load drop down before termination
const WAIT_LIMIT_SEC = 120;
/**
 * Load settings block
 */
//processor quantity
define('PRC_COUNT', (int)shell_exec('grep -c processor /proc/cpuinfo'));
//max pid in system
define('PID_MAX', (int)shell_exec('cat /proc/sys/kernel/pid_max'));
//MAX memory use by sending scriptS
const MEMORY_USE_MB = 3000;
//load average 1 max
const LA_PERCENT = 0.9;
//PID percent to capture
const PID_PERCENT = 0.001;
//la cut value
const LA_CUT = LA_PERCENT * PRC_COUNT;
//PID percent cut value
define('PID_CUT', (int)(PID_MAX * PID_PERCENT));


/**
 * Require block
 */
require_once('./runFunctions.php');
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
 * Check for mail to send multiprocess
 */
$bad_mails = checkActionsIter($data, $log, $pid);
/**
 * Save result of check
 */
if(!TEST){
    setCheckAndNotValidForMails($data, $bad_mails);
}
/**
 * Send checked and valid mails
 */
$fail_sends = sendActionsIter(newListForm($data, $bad_mails), $log, $pid);

//echo res for me
echo 'Processed '.$count.' in '.gmdate("H:i:s",(microtime(true) - $s_time)).' Bad '.count($bad_mails).' Failed '.count($fail_sends)."\n";