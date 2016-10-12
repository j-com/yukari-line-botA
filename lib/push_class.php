<?php
require_once 'goutte/vendor/autoload.php';

use Goutte\Client;

class Push
{
    private $CHANNEL_ACCESS_TOKEN, $url = '';
    public $client, $crawler = '';

    public function __construct($url, $CHANNEL_ACCESS_TOKEN)
    {
        $this->client = new Client();

        $this->url = $url;
        $this->CHANNEL_ACCESS_TOKEN = $CHANNEL_ACCESS_TOKEN;
        $this->crawler = $this->client->request('GET', $this->url);
    }

    public function getUserid() {
        try {
            $array_userid = [];

            $pdo = new PDO('mysql:dbname=yukarinotification; host=database.cma0jldtuyey.us-west-2.rds.amazonaws.com', 'luis', 'luism8526',
                array(PDO::ATTR_EMULATE_PREPARES => false));

            $stmt = $pdo->query("SELECT * FROM line");

            foreach ($stmt as $row) {
                $array_userid[] = $row['userid'];
            }

            return $array_userid;

        } catch (PDOException $e) {
            /** ERROR HANDLING  */
            die();
        }
    }

    public function pushMessage($USERID, $msg)
    {
        $format_text = [
            "type" => "text",
            "text" => $msg
        ];

        $post_data = [
            "to" => $USERID,
            "messages" => [$format_text]
        ];

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->CHANNEL_ACCESS_TOKEN
        );

        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function toGetShortUrl($url)
    {
        $api = 'AIzaSyA0ksR6vpBgQHfiktO4imsdwiz91OFIltU';

        $data = array(
            'longUrl' => $url
        );
        $data = json_encode($data);

        $header = array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($data)
        );

        $context = array(
            "http" => array(
                "method" => "POST",
                "header" => implode("\r\n", $header),
                "content" => $data
            )
        );

        $result = file_get_contents("https://www.googleapis.com/urlshortener/v1/url?key=${api}", false,
            stream_context_create($context));
        $result = json_decode($result);

        return $result->id;
    }
}