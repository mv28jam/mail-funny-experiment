<?php
//control file
$file = $argv[2].'/'.getmypid();
file_put_contents($file, $argv[1]);

/**
 * Mok check mail function
 * rand wait - like real check
 * 10% check fail
 * @param $email
 * @return bool
 */
function check_mail($email): bool{
    sleep(mt_rand(1,60));
    return (mt_rand(0,9)<9);
}

/**
 * Check mail if OK exit form script with 0 success code
 */
if(check_mail($argv[1])){
    unlink($file);
}