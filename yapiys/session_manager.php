<?php

session_set_save_handler('_open','_close','_read','_write', '_destroy', '_clean');

$GLOBALS['_sess_db'] = null;

$GLOBALS['_sess_dbs'] = 'a';

function _open()
{

    StoreDb::$db = mysqli_connect('localhost', 'root', '#inOvatiOn2014#*', 'dashboard');

    if (mysqli_connect_errno())
    {
        return false;
    }

    return true;
}


function _close()
{

    return mysqli_close(StoreDb::$db);

}


function _read($id){

    $id = mysqli_real_escape_string(StoreDb::$db, $id);

    $sql = "SELECT data FROM sessions WHERE  id = '$id'";

    if ($result = mysqli_query(StoreDb::$db, $sql)) {

        if ($result->num_rows) {

            $record = mysqli_fetch_assoc($result);

            return $record['data'];

        }
    }

    return '';
}


function _write($id, $data){

    $access = time();

    $id = mysqli_real_escape_string(StoreDb::$db, $id);

    $access = mysqli_real_escape_string(StoreDb::$db, $access);

    $data = mysqli_real_escape_string(StoreDb::$db, $data);

    $sql = "REPLACE INTO sessions VALUES ('$id', '$access', '$data')";

    return mysqli_query(StoreDb::$db, $sql);

}

function _destroy($id){

    $id = mysqli_real_escape_string(StoreDb::$db, $id);

    $sql = "DELETE FROM sessions WHERE id = '$id'";

    return mysqli_query(StoreDb::$db, $sql);

}

function _clean($max)
{

    $old = time() - $max;
    $old = mysqli_real_escape_string(StoreDb::$db, $old);

    $sql = "DELETE FROM sessions WHERE access < '$old'";

    return mysqli_query(StoreDb::$db, $sql);

}

class StoreDb{
    public static $db;
}

ini_set('session.cookie_domain', '.domain.com');

if ( empty(session_id()) ) session_start();