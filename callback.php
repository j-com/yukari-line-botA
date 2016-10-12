<?php
require_once './lib/callback_class.php';

/* LINE */
$CHANNEL_SECRET = '';
$CHANNEL_ACCESS_TOKEN = '';

/* TWITTER */
$CONSUMER_KEY = '';
$CONSUMER_SECRET = "";
$ACCESS_TOKEN = "";
$ACCESS_TOKEN_SECRET = "";

$json_input = file_get_contents('php://input');
$yows = new YOWS($CHANNEL_SECRET, $CHANNEL_ACCESS_TOKEN, $json_input);

if ($yows->type === "unfollow") {
    $yows->unRegister($yows->userid);

} elseif ($yows->type === "follow" && $yows->checkRegister($yows->userid) === 0) {
    $profile = $yows->getProfile($yows->userid);
    $yows->register($yows->userid);
    $yows->pushMessage($yows->userid, "登録ありがとうございます 􀄃􀆰3 hearts􏿿\n\n・田村ゆかり公式サイト\n・ファンクラブサイト\nの更新をお知らせ致します􀐂􀄝light bulb􏿿\n\nなお、当アカウントをブロックすることで利用の停止ができます 􀄃􀆐content􏿿");

} elseif ($yows->type === "follow" && $yows->checkRegister($yows->userid) === 1) {
    $yows->pushMessage($yows->userid, "既に登録済みです。");

} else {
    die();
}