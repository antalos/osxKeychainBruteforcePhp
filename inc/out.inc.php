<?php
function sout($s)
{
    $s = date("[H:i:s] ") . $s . "\r\n";
    echo $s;
}

function dodie($s)
{
    $s = "[FATAL] $s";
    sout($s);
    die;
}

function hexDump($data, $newline = "\r\n")
{
    static $from = '';
    static $to = '';

    static $width = 16; # number of bytes per line

    static $pad = '.'; # padding for non-visible characters

    if ($from === '') {
        for ($i = 0; $i <= 0xFF; $i++) {
            $from .= chr($i);
            $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
        }
    }

    $hex = str_split(bin2hex($data), $width * 2);
    $chars = str_split(strtr($data, $from, $to), $width);

    $offset = 0;
    foreach ($hex as $i => $line) {
        $line = strtoupper($line);
        $strHex = str_split($line, 16);
        foreach ($strHex as $k => $v) {
            $strHex[$k] = implode(' ', str_split($v, 2));
        }
        echo sprintf('%6X', $offset) . ' : ' . implode('  ', $strHex) . '  [' . $chars[$i] . ']' . $newline;
        $offset += $width;
    }
}

function showUsage() {
    echo "Usage:\r\n";
    echo "php brute.php [keyChain file] [passwordList file]\r\n";
    die;
}

?>