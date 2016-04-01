<?php
/*
 * structures, you can take a look at:
 * http://www.opensource.apple.com/source/libsecurity_cssm/libsecurity_cssm-55005.5/lib/
 */
$structs = array();

//big endian ints
$structs['db_header'] = array(
    'signature' => array('type' => 'char', 'size' => 4),
    'version' => array('type' => 'int'),
    'headerSize' => array('type' => 'int'),
    'schemaOffset' => array('type' => 'int'),
    'authOffset' => array('type' => 'int'),
);
$structs['apple_db_schema'] = array(
    'schemaSize' => array('type' => 'int'),
    'tableCount' => array('type' => 'int'),
);
$structs['table_header'] = array(
    'tableSize' => array('type' => 'uint'),
    'tableId' => array('type' => 'uint'),
    'recordCount' => array('type' => 'uint'),
    'records' => array('type' => 'uint'),
    'indexesOffset' => array('type' => 'uint'),
    'freeListHead' => array('type' => 'uint'),
    'recordNumbersCount' => array('type' => 'uint'),
);
$structs['key_blob_header'] = array(
    'recSize' => array('type' => 'uint'),
    'recCount' => array('type' => 'uint'),
    'dummy' => array('type' => 'char', 'size' => 0x7c),
);
$structs['key_blob_rec'] = array(
    'signature' => array('type' => 'uint'),
    'version' => array('type' => 'uint'),
    'cipherOffset' => array('type' => 'uint'),
    'totalLen' => array('type' => 'uint'),
);



$_SERVER['structs'] = calculateStructSizes($structs);

function calculateStructSizes($structs)
{
    $res = array();

    foreach ($structs as $k => $fields) {
        $size = 0;
        foreach ($fields as $f) {
            $size += getFieldSize($f);

        }

        $res[$k] = array('size' => $size, 'fields' => $fields);
    }

    return $res;
}




function getFieldSize($f)
{

    if (!is_array($f)) $f = array('type' => $f);
    if (!empty($f['size'])) return $f['size'];

    switch ($f['type']) {
        case 'int':
            return INT_SIZE;
            break;

        case 'uint':
            return UINT_SIZE;
            break;

        default:
            dodie('Unknown type: ' . $f['type']);
            break;
    }

}

function getFieldVal($data, $f)
{
    $res = null;

    switch ($f['type']) {
        case 'char':
            return $data;
            break;

        case 'int':
            $res = getInt($data);
            break;

        case 'uint':
            $res = getUint($data);
            break;

        default:
            dodie('Unknown type: ' . $f['type']);
            break;
    }

    return $res;
}




?>