<?php

function getCon(): PDO
{
    return new PDO('mysql:host=localhost;port=3306;dbname=test', '', '');
}

function getDataFromDB() :array{
    getCon();
    return [];
}

function setNotValidForMails(array $emails) :bool{
    getCon();
    return true;
}