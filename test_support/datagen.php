<?php
/**
 * Testing data gen
 * ADJUST DATE!!!
 */

require_once('../src/data/dbActions.php');

function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'): string
{
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}
//---

getCon()->exec('
CREATE TABLE `mail_users` (
`username` varchar(255) NOT NULL,
`email` varchar(320) NOT NULL,
`validts` datetime NOT NULL,
`confirmed` tinyint(4) NOT NULL DEFAULT 0,
`checked` tinyint(4) NOT NULL DEFAULT 0,
`valid` tinyint(4) NOT NULL DEFAULT 0,
PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
');

for ($i=1; $i < 6000000; $i++){

    $username = randString(mt_rand(5,250));
    $email = randString(mt_rand(10,50)).'@example.com';
    $validts = (mt_rand(0,99) < 80 ? '2023-01-01' : '2023-06-'.mt_rand(1,30));
    $confirmed = (mt_rand(0,99) < 75 ? 1 : 0);

    getCon()->exec(
        "REPLACE INTO mail_users
        (username, email, validts, confirmed, checked, valid)
        VALUES('$username' , '$email' , '$validts', $confirmed, 0, 0);
    ");
}