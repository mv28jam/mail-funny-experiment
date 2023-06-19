<?php
/**
 * Starting process of mail check in separate proc by exec - no wait
 * harvest results of check - invalid emails
 * @param array $data
 * @param callable $log
 * @param int $pid
 * @return array
 */
function checkActionsIter(array $data, callable $log, int $pid) : array
{
    $dir = './'.$pid.'/check';
    mkdir($dir);
    $sent_to_check = [];
    //
    for ($i = 0; $i < count($data); $i++) {
        //
        loadWait($i, $log);
        //
        if (!$data[$i]['checked'] and !isset($sent_to_check[$data[$i]['checked']])) {
            //register emails
            $sent_to_check[$data[$i]['email']] = 1;
            //
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
 * Starting process of mail send in separate proc by exec - no wait
 * harvest results of send - error of sending
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
    //see comment in checkActionsIter
    sleep(10);
    //
    return harvestResults($dir);
}

/**
 * Harvest results of sending in dir of process
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
 * Form new array of users to send mail
 * @param array $data
 * @param array $bad_mails
 * @return array
 */
function newListForm(array $data, array $bad_mails): array
{
    //flip array to avoid cycle comparison to hash
    $bad_mails = array_flip($bad_mails);
    $send = [];
    foreach ($data as $val){
        if(!isset($bad_mails[$val['email']]))$send[] = $val;
    }
    return $send;
}