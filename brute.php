<?php
//osxKeychainBruteforcePhp
require_once 'inc/keychain.inc.php';

if ((empty($argv[1]) || empty($argv[2])) && $argv[1] != 'test') showUsage();


if ($argv[1] == "test") {
    $kcFile = 'test/test.keychain';
    $pwdFile = 'test/pwds.txt';
} else {
    $kcFile = $argv[1];
    $pwdFile = $argv[2];

}
//uncomment following lines to setup files from the script

//$kcFile = '/Users/alex/Library/Keychains/testKC2.keychain';
//$pwdFile = 'pwds/10k_most_common.txt';
//$pwd = '123'; //override passwordList to check single pwd

$keychain = new Keychain($kcFile);
$keychain->preloadData();


sout("Keychain file: $kcFile");

if (!empty($pwd)) {
    $pwds = array($pwd);
} else {
    if (!is_readable($pwdFile)) dodie("password file is not readable");
    $pwds = file($pwdFile);
    sout("Pwd file: $pwdFile");
}
$nPasswords = count($pwds);
sout("Passwords to check: $nPasswords");

$timer = new wTimer($nPasswords);
foreach ($pwds as $p) {
    $p = trim($p);
    $isValid = $keychain->checkPassword($p);
    $timer->incChecked();

    if ($isValid) {
        $timer->dumpStat();
        sout("[+] $p seems to be valid" );
        die();
    }
}

sout("no luck :(");
$timer->dumpStat();





?>