<?php

include('vendor/autoload.php');
use Telegram\Bot\Api;

const DB_HOST = 'eu-cdbr-west-02.cleardb.net';
const DB_USER = 'b1597e3a08d730';
const DB_PASS = '9a13c73f';
const DB_NAME = 'heroku_34fcf0748940255';
const TMBD_API_TOKEN = '951aefe4839143b19cb846c5002fb7a9';
const TELEGRAM_API_TOKEN = '854559704:AAFfCPdSB-SfwwX-QNWIplVUmeV8cd-VjHk';

$db  = new MysqliDb( DB_HOST , DB_USER , DB_PASS , DB_NAME );

$telegram = new Api(TELEGRAM_API_TOKEN);
$result = $telegram -> getWebhookUpdates();
$text = $result["message"]["text"];
$chat_id = $result["message"]["chat"]["id"];
$name = $result["message"]["from"]["username"];

$token = new Tmdb\ApiToken(TMBD_API_TOKEN);
$client = new Tmdb\Client ($token);

function getUrlPoster($arrayWithInfo) {
    return "http://image.tmdb.org/t/p/w300_and_h450_bestv2" . $arrayWithInfo['poster_path'];
}

function getTextUnderPoster($arrayWithInfo) {
    return $arrayWithInfo['original_title'] . '
    ' . $arrayWithInfo['overview'];
}

function caсhResponse ($db, $response, $request) {
    $row = [
        'request' => $request,
        'response' =>  json_encode($response),
        'date' => new DateTime()
    ];
    return $db->insert('cach_requests', $row);
}

function getResponse($db, $request) {
    $db->where('request', $request);
    $response  =  $db -> getValue('cach_requests', 'response');
    return  json_decode($response);
}

if($text) {
    if ($text == "/start") {
        if(!$name) {
          $name = 'Незнакомец';
        }
        $reply = "Привет " . $name . ", если ты напишешь какую нибудь фразу или слово, то я покажу тебе до трех фильмов связанных с этим выражением";
        $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text) {
        $result = getResponse($db, $text);
        if (!$result) {
            $result = $client->getSearchApi()->searchMovies($text);
            caсhResponse ($db, $result, $text);
        }
        for($i=0; $i<3; $i++) {
            if (!$result['results'][$i]) {
                break;
            }
            $telegram->sendPhoto(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'photo' => getUrlPoster($result['results'][$i]), 'caption' => getTextUnderPoster($result['results'][$i])]);
        }
    }
}

register_shutdown_function(function () {
    http_response_code(200);
});