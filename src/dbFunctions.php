<?php

/**
 * Get Connection to DB
 * @return PDO
 */
function getCon(): PDO
{
    return new PDO('mysql:host=localhost;port=3306;dbname=test', '', '');
}

/**
 * Get users with mail to check
 * @return array
 *
 * NOTE ---
 * IF email is not unique and there is no "valid" already check when inserted, we can join valid mail
 * to user with other login (have to check!)
 * query assumption ! very slow (
SELECT one.username, one.email, (MAX(one.valid) OR MAX(one.confirmed) OR MAX(two.valid) OR MAX(two.confirmed)) as checked
FROM mail_users as one
LEFT JOIN mail_users as two ON (one.email = two.email and (two.confirmed=1 OR two.valid=1))
WHERE (one.confirmed = 1 OR one.valid = 1 OR (one.checked = 0 AND one.valid = 0)) AND (date(one.validts) = date(NOW() + INTERVAL 1 DAY) OR date(one.validts) = date(NOW() + INTERVAL 3 DAY))
GROUP BY username, email
 */
function getDataFromDB() :array{
    return getCon()->query('
SELECT username, email, (checked OR confirmed) as checked
FROM mail_users
WHERE 
    (confirmed = 1 OR valid = 1 OR (checked = 0 AND valid = 0)) 
    AND 
    (date(validts) = date(NOW() + INTERVAL 1 DAY) OR date(validts) = date(NOW() + INTERVAL 3 DAY))'
    )->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Save check results
 * @param array $all
 * @param array $bad
 * @return bool
 */
function setCheckAndNotValidForMails(array $all, array $bad) :bool{
    $logins=[];
    //
    foreach ($all as $item) {
        if(!$item['checked']) {
            $logins[] = $item['username'];
        }
    }
    $con = getCon();
    //to avoid inconsistency
    //assuming REPEATABLE_READ
    $con->beginTransaction();
    $con->exec('UPDATE mail_users SET checked=1 WHERE username IN(\''.implode(' \',\' ', $logins).'\')');
    $con->exec('UPDATE mail_users SET valid=0, checked=1 WHERE email IN(\''.implode(' \',\' ', $bad).'\')');
    $con->commit();
    return true;
}