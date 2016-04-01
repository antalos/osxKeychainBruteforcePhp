<?php

function getPbkdfKey($pwd, $salt)
{
    return hash_pbkdf2(MKEY_ALGO, $pwd, $salt, MKEY_ITERS, MKEY_LEN, true);
}


function decryptDes($key, $cipher, $iv)
{
    $data = mcrypt_decrypt('tripledes', $key, $cipher, 'cbc', $iv);
    $data = applyDesPadding($data);
    return $data;
}


function applyDesPadding($data)
{
    $dataSize = binSize($data);

    //check padding
    $padByte = ord(getBinData($data, $dataSize - 1, 1));
    if ($padByte > 8) return false;


    $tail = getBinData($data, $dataSize - $padByte, $padByte);
    $tail = binSplit($tail);
    foreach ($tail as $t) {
        if (ord($t) != $padByte) return false;
    }

    $data = getBinData($data, 0, $dataSize - $padByte);
    return $data;
}

?>