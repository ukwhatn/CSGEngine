<?php
require_once __DIR__ . "/Database.php";
try {
    $DB = new DB();
} catch (InitialConnectException $e) {
# PDO接続に失敗したら500を返して終わる
# @todo
#   エラーページ追加
    http_response_code(500);
    exit;
}

if (!array_key_exists("id", $_GET)) {
    http_response_code(404);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $file = $DB->getFileData((int)$_GET["id"]);
    if ($file === null) {
        http_response_code(404);
        echo "File not found";
        exit;
    } else {
        header("Content-Type: " . $file["mimetype"]);
        print $file["content"];
        exit;
    }
}
