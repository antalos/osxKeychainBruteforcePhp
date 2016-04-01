<?php
/*
 * some constants, you can take a look at:
 * http://www.opensource.apple.com/source/libsecurity_cssm/libsecurity_cssm-55005.5/lib/
 * http://www.opensource.apple.com/source/securityd/securityd-55137.1/
 */


define('CSSM_DB_RECORDTYPE_APP_DEFINED_START', 0x80000000);
define('CSSM_DL_DB_RECORD_METADATA', CSSM_DB_RECORDTYPE_APP_DEFINED_START + 0x8000);

define('CSSM_DB_RECORDTYPE_OPEN_GROUP_START', 0x0000000A);
define('CSSM_DL_DB_RECORD_SYMMETRIC_KEY', CSSM_DB_RECORDTYPE_OPEN_GROUP_START + 7);


define('KC_FILE_SIG', 'kych');
define('SEC_STORAGE_GROUP', 'ssgp');

// http://www.opensource.apple.com/source/Security/Security-28/AppleCSP/AppleCSP/wrapKeyCms.cpp
define('magicCms4', hex2bin('4adda22c79e82105'));

//master key
define('MKEY_ALGO', 'sha1');
define('MKEY_ITERS', 1000);
define('MKEY_LEN', 24);


define('BLOCKSIZE', 8);
define('DBKEY_LEN', 24);
?>