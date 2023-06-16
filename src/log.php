<?php

/**
 * Dummy log instance
 * @param string $txt
 * @return bool
 */
function dummyLog(string $txt): bool{
    echo $txt."\n";
    return true;
}
