<?php
require_once 'twitteroauth/twitteroauth.php';

class YOWS
{
    private $CHANNEL_SECRET = '';
    private $CHANNEL_ACCESS_TOKEN = '';
    public $header = [];
    public $data, $userid, $type = '';

    public function __construct($CHANNEL_SECRET, $CHANNEL_ACCESS_TOKEN, $data)
    {
        $this->CHANNEL_SECRET = $CHANNEL_SECRET;
        $this->CHANNEL_ACCESS_TOKEN = $CHANNEL_ACCESS_TOKEN;
        $this->data = $data;
        $this->getallheaders();
        $this->checkSignature();
        $this->setVariables();
    }

    private function getallheaders()
    {
        $header = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header[strtoupper(str_replace(' ', '-', ucwords(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        $this->header = $header;
    }

    public function checkSignature()
    {
        if (base64_decode($this->header["X-LINE-SIGNATURE"]) === hash_hmac('sha256', $this->data, $this->CHANNEL_SECRET, true)) {
            $this->data = json_decode($this->data, true);
        } else {
            die();
        }
    }

    private function setVariables()
    {
        foreach ($this->data as $row) {
            $this->type = $row[0]['type'];
            $this->userid = $row[0]['source']['userId'];
        }
    }

    /**
     * @param $USERID
     * @return int 0=not register / 1=registerd
     */
    public function checkRegister($USERID)
    {
        try {
            $pdo = new PDO('mysql:dbname=; host=', '', '',
                array(PDO::ATTR_EMULATE_PREPARES => false));

            $sql = "SELECT * FROM line WHERE userid='${USERID}'";
            $stmt = $pdo->query($sql);

            foreach ($stmt as $row) {
                $query_result = $row['id'];
            }

            empty($query_result) === true ? $result = 0 : $result = 1;

            return $result;

        } catch (PDOException $e) {
            $this->debug('Error:' . $e->getMessage());
            die();
        }
    }

    public function register($USERID)
    {
        try {
            $pdo = new PDO('mysql:dbname=; host=', '', '',
                array(PDO::ATTR_EMULATE_PREPARES => false));

            $stmt = $pdo->prepare('INSERT INTO line (userid, date) VALUES (:register_userid, :date)');
            $stmt->execute(array(':register_userid' => $USERID, ':date' => $this->getDate()));

        } catch (PDOException $e) {
            $this->debug('Error:' . $e->getMessage());
            die();
        }
    }

    public function unRegister($USERID)
    {
        try {
            $pdo = new PDO('mysql:dbname=; host=', '', '',
                array(PDO::ATTR_EMULATE_PREPARES => false));

            $stmt = $pdo->prepare('DELETE FROM line WHERE userid = :delete_userid');
            $stmt->execute(array(':delete_userid' => $USERID));

        } catch (PDOException $e) {
            $this->debug('Error:' . $e->getMessage());
            die();
        }
    }

    private function getDate()
    {
        return date("Y-m-d H:i:s");
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

    public function debug($string)
    {
        error_log(print_r($string, true), 3, '/var/log/nginx/error.log');
    }
}
