<?php
require_once 'out.inc.php';
require_once 'consts.inc.php';
require_once 'binData.inc.php';
require_once 'structs.inc.php';
require_once 'crypto.inc.php';
require_once 'timer.inc.php';

class Keychain
{
    function Keychain($fname)
    {
        if (!is_readable($fname)) dodie("File $fname is not readable");
        $this->fname = $fname;

        $fsize = filesize($fname);
        $fp = fopen($fname, 'rb');
        $this->buf = fread($fp, $fsize);
        fclose($fp);
    }

    function preloadData() {
        $this->readHeader();
        $this->readSchema();
        $this->readSalt();
        $this->readCipher();
        $this->getKeyList();
    }

    function readHeader()
    {
        $this->header = $this->readStruct('db_header');
        if ($this->header['signature'] != KC_FILE_SIG) dodie("File $fname is not a valid keychain");
    }

    function readSchema()
    {
        $tables = array();

        $schema = $this->readStruct('apple_db_schema', $this->header['schemaOffset']);
        if (empty($schema['tableCount'])) dodie("No tables in schema");

        $base = $_SERVER['structs']['db_header']['size'] + $_SERVER['structs']['apple_db_schema']['size'];
        $uintSize = getFieldSize('uint');

        for ($i = 0; $i < $schema['tableCount']; ++$i) {
            $offset = $base + $i * $uintSize;
            $val = readBinUint($this->buf, $offset);
            $tables[] = array('offset' => $val);
        }

        $this->schema = $schema;
        $this->tables = $tables;

        foreach ($this->tables as $k => $v) {
            $this->tables[$k] = $this->readTable($k);
        }
    }

    function readTable($key)
    {
        if (!isset($this->tables[$key])) dodie("No offset for table with key: " . $key);
        $table = $this->tables[$key];
        $offset = $table['offset'];

        $base = $_SERVER['structs']['db_header']['size'] + $offset;
        $meta = $this->readStruct('table_header', $base);

        $recordBase = $base + $_SERVER['structs']['table_header']['size'];
        $uintSize = getFieldSize('uint');

        $records = array();
        $recordsReadCount = 0;
        $i = 0;

        while ($recordsReadCount < $meta['recordCount']) {
            $offset = $recordBase + $uintSize * $i;
            $recOffset = readBinUint($this->buf, $offset);

            //got offset that seems to be incorrect - skip it
            if ($recOffset != 0 && $recOffset % 4 == 0) {
                $records[] = $recOffset;
                ++$recordsReadCount;
            }
            ++$i;
        }

        $table['meta'] = $meta;
        $table['records'] = $records;
        return $table;
    }

    function getTableById($tid)
    {
        foreach ($this->tables as $t) {
            if ($t['meta']['tableId'] == $tid) return $t;
        }

        return null;
    }



    function readSalt()
    {
        $table = $this->getTableById(CSSM_DL_DB_RECORD_METADATA);
        if (!$table) dodie("Cant get meta table");

        $base = $_SERVER['structs']['db_header']['size'] + $table['offset'] + 0x38; //MAGIC
        $saltSize = 20;
        $offset = $base + 44; //MAGIC

        $salt = getBinData($this->buf, $offset, $saltSize);
        $this->salt = $salt;
    }

    function getMasterKey($pwd, $salt = '')
    {
        if (empty($salt)) $salt = $this->salt;

        $key = getPbkdfKey($pwd, $salt);

        return $key;
    }

    function readCipher()
    {

        $table = $this->getTableById(CSSM_DL_DB_RECORD_METADATA);
        if (!$table) dodie("Cant get meta table");

        $base = $_SERVER['structs']['db_header']['size'] + $table['offset'] + 0x38; //MAGIC
        $cipherBase = $base + 8;
        $cipherTextOff = readBinUint($this->buf, $cipherBase);

        $totalLenBase = $base + 12;
        $totalLen = readBinUint($this->buf, $totalLenBase);
        $lenToRead = $totalLen - $cipherTextOff;

        $ivBase = $base + 64;
        $ivLen = 8;
        $this->iv = getBinData($this->buf, $ivBase, $ivLen);

        $this->cipher = getBinData($this->buf, $base + $cipherTextOff, $lenToRead);

    }

    function getWrapKey($masterKey)
    {
        $this->readCipher();
        $wKey = $this->decryptWrapKey($masterKey);
    }

    function decryptWrapKey($key)
    {
        $iv = $this->iv;
        $cipher = $this->cipher;

        if (empty($cipher)) return false;
        if (binSize($cipher) % BLOCKSIZE != 0) return false;

        $data = decryptDes($key, $cipher, $iv);
        if ($data === false) return false;

        $data = getBinData($data, 0, DBKEY_LEN);
        return $data;
    }

    function decryptKeys($wkey)
    {
        $res = true;
        foreach ($this->keys as $key) {
            $dec = decryptDes($wkey, $key['cipher'], magicCms4);
            if ($dec === false) {
                $res = false;
                break;
            }
            $dec = getBinData($dec, 0, 32);
            $dec = binInvert($dec);


            $dec = decryptDes($wkey, $dec, $key['iv']);
            if ($dec === false) {
                $res = false;
                break;
            }

            $dec = getBinData($dec, 4, binSize($dec) - 4);
            if (binSize($dec) != DBKEY_LEN) {
                $res = false;
                break;
            }

        }

        return $res;
    }

    function getKeyList()
    {
        $this->keys = array();
        $table = $this->getTableById(CSSM_DL_DB_RECORD_SYMMETRIC_KEY);
        foreach ($table['records'] as $rec) {
            $blob = $this->readKeyblobRec($table['offset'], $rec);
            if ($blob === false) dodie('bad BLOB');
            $this->keys[] = $blob;
        }
        if (empty($this->keys)) dodie("Failed to fetch keys list");
    }

    function readKeyblobRec($base, $offset)
    {
        $base = $_SERVER['structs']['db_header']['size'] + $base + $offset;
        $header = $this->readStruct('key_blob_header', $base);

        $baseRec = $base + $_SERVER['structs']['key_blob_header']['size'];
        $recRaw = getBinData($this->buf, $baseRec, $header['recSize']);

        $rec = readStruct($recRaw, 0, 'key_blob_rec');
        $sig = getBinData($recRaw, $rec['totalLen'] + 8, 4);

        if ($sig != SEC_STORAGE_GROUP) return false;

        $cipherLen = $rec['totalLen'] - $rec['cipherOffset'];
        if ($cipherLen % BLOCKSIZE != 0) return false;

        $res = array(
            'iv' => getBinData($recRaw, 16, 8),
            'cipher' => getBinData($recRaw, $rec['cipherOffset'], $cipherLen),
            'keyblob' => getBinData($recRaw, $rec['totalLen'] + 8, 20)
        );

        return $res;

    }

    function checkPassword($p) {
        $mkey = $this->getMasterKey($p);
        $wkey = $this->decryptWrapKey($mkey);
        if ($wkey !== false) {
            $keyDecode = $this->decryptKeys($wkey);
            return $keyDecode;
        }
        return false;
    }



    function readStruct($struct, $offset = 0)
    {
        if (empty($_SERVER['structs'][$struct])) dodie('Unknown structure: ' . $struct);
        return readStruct($this->buf, $offset, $_SERVER['structs'][$struct]);
    }


}

?>