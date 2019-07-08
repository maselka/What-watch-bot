<?php

require_once('vendor/autoload.php');

const DB_HOST = 'eu-cdbr-west-02.cleardb.net';
const DB_USER = 'b1597e3a08d730';
const DB_PASS = '9a13c73f';
const DB_NAME = 'heroku_34fcf0748940255';


function initDB(): MysqliDb{
    $db = new MysqliDb (DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->autoReconnect = true;
    return $db;
}

function getResponse(MysqliDb $db, $request){
    $db->where('request', $request);
    $response = $db->getValue('cach_requests', 'response');
    $date_request =  $db->getValue('cach_requests', 'date');
    $date_now = date('Y-m-d');
    $date_diff = date_diff($date_now, $date_request, true);
    error_log(var_export($date_request, true));
    error_log(var_export($date_now, true));
    error_log(var_export($date_diff, true));
    if ($date_diff < 1) {
        return json_decode($response);
    } else {
        return NULL;
    }
}

function insertRow(MysqliDb $db, $request, $response){
    $row = [
        'request' => $request,
        'response' => json_encode($response),
        'date' => date('Y-m-d')
    ];
    $id = $db->insert('cach_requests', $row);
}
