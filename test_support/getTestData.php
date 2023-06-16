<?php

function getTestData(int $quan): array
{
    $res = [];
    //
    for ($i = 0; $i < $quan; $i++){
        $res[$i] = [
            'username' => (string)$i,
            'email' => $i.'@example.com',
            'checked' => ((mt_rand(0,99) < 75 ? 1 : 0))
        ];
    }
    //
    return $res;
}

