<?php
//control file
$file = $argv[2].'/'.getmypid();
file_put_contents($file, $argv[3]);

/**
 * Mok send mail function
 * rand wait - like real send
 * 1% send fail
 * @param $from
 * @param $email
 * @param $text
 * @return bool
 */
function send_mail($from, $email, $text): bool{
    sleep(mt_rand(1,10));
    return (mt_rand(0,99) < 99);
}

//send mail
if(send_mail('warning@example.com', $argv[1], $argv[3].', your subscription is expiring soon.')){
    unlink($file);
}
//



