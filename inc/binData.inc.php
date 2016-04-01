<?php
/*
 * functions to work with binary data
 */

define('INT_SIZE', 4);
define('UINT_SIZE', 4);

//check if string funcs is binary safe
if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING ) {
    dodie("String funcs are overloaded by mbstring. We're using string funcs to read binary data, mbstring may be not binary safe.");
}

//we're using machine dependent unpack - so we need to check if the machine is using BE or LE
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));


function getBinData($data, $offset, $size)
{
    return substr($data, $offset, $size);
}

function readBinUint($buf, $offset) {
    return getUint(getBinData($buf, $offset, getFieldSize('uint')));
}

function binSize($buf) {
    return strlen($buf);
}

function binInvert($buf) {
    return strrev($buf);
}
function binSplit($buf) {
    return str_split($buf);
}
function getInt($buf) {
    if (!BIG_ENDIAN) $buf = binInvert($buf);
    //l	signed long (always 32 bit, machine byte order)
    $res = unpack('l', $buf);
    return $res[1];
}

function getUint($buf) {
    //unsigned long (always 32 bit, big endian byte order)
    $res = unpack('N', $buf);
    return $res[1];
}



//expects
function readStruct($buf, $offset, $struct) {
    
    if (empty($struct['size'])) {
        if (empty($_SERVER['structs'][$struct])) dodie('Unknown structure: ' . $struct);
        $struct = $_SERVER['structs'][$struct];
    }
    $res = array();
    $data = getBinData($buf, $offset, $struct['size']);
    //hex_dump($data);

    $offset = 0;
    foreach ($struct['fields'] as $fName => $f) {
        $size = getFieldSize($f);
        $val = getBinData($data, $offset, $size);
        $offset += $size;

        $res[$fName] = getFieldVal($val, $f);
    }


    return $res;
}
?>