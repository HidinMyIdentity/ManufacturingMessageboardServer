<?php


session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

function GenerateTripcode($input) { // from https://github.com/NoneGiven/tripcode/blob/master/tripcode.php
    if ($input === NULL || $input === "") {
        return "";
    }
    $pass = htmlspecialchars($input); // don't replace apostrophes
    $num_chars = iconv_strlen($pass, "UTF-8");
    $conv = "";
    for ($i = 0; $i < $num_chars; $i++) {
        try {
            $conv .= iconv("UTF-8", "CP932", iconv_substr($pass, $i, 1, "UTF-8"));
        }
        catch (Exception $ex) {
            $conv .= "?";
        }
    }
    $salt = strtr(preg_replace("/[^\.-z]/", ".", substr($conv . "H.", 1, 2)), ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
    $trip = substr(crypt($conv, $salt), -10);
    return "!" . $trip;
}

function CauseError($message) {
    echo json_encode(["error"=>$message, "response"=>[]]);
    die();
}

function WrapJson($data, $error=null) {
    $arr = [
        "response" => $data
    ];
    if (!empty($error)) {
        $arr["error"] = $error;
    }
    echo json_encode($arr);
    die();
}


class DB
{
    public $pdo;


    function __construct()
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO("sqlite:database.sqlite", "", "", $options);
        $this->InitializeTables();
    }

    function InitializeTables() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS posts (content TEXT, hash TEXT, posttype int, reply INT)");
    }

    function GetPost($postid) {
        $query = $this->pdo->prepare("SELECT rowid, * FROM posts WHERE rowid = ?");
        $query->execute([$postid]);
        return $query->fetch();
    }

    function GetReplies($postid) {
        $query = $this->pdo->prepare("SELECT rowid, * FROM posts WHERE reply = ? ORDER BY `rowid`");
        $query->execute([$postid]);
        return $query->fetchAll();
    }

    function GetTopLevelPosts($offset) {
        $query = $this->pdo->prepare("SELECT rowid, * FROM posts WHERE reply = -1 ORDER BY `rowid` DESC LIMIT 20 OFFSET ? ");
        $query->execute([$offset]);
        return $query->fetchAll();
    }

    function CreatePost($content, $hash) {
        $query = $this->pdo->prepare("INSERT INTO posts VALUES(?, ?, ?, ?)");
        $query->execute([$content, GenerateTripcode($hash), 0, -1]);
        return $this->pdo->lastInsertId();
    }
    function CreateReply($content, $reply, $hash) {
        $query = $this->pdo->prepare("INSERT INTO posts VALUES(?, ?, ?, ?)");
        $query->execute([$content, GenerateTripcode($hash), 0, $reply]);
        return $this->pdo->lastInsertId();
    }
}

$DB = new DB();