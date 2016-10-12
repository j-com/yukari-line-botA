<?php
require_once 'lib/push_class.php';

$URL = "http://www.tamurayukari.com/";
$old_url = file_get_contents('old_url');

/* LINE */
$CHANNEL_ACCESS_TOKEN = '';

$yows = new Push($URL, $CHANNEL_ACCESS_TOKEN);

$latest_content = $yows->crawler->filter('div#news_table td a')->text();
$latest_url = $yows->crawler->filter('div#news_table td a')->attr('href');

if($latest_url !== $old_url) {

    $msg = "【サイト更新通知BOTよりお知らせ】\r\n公式サイトが更新されました！\r\n\r\n【タイトル】\r\n{$latest_content}\r\n\r\n詳しくはこちらへ！\r\n${latest_url}";

    foreach ($yows->getUserid() as $userid) {
        $yows->pushMessage($userid, $msg);
    }

    file_put_contents('old_url', $latest_url);
}